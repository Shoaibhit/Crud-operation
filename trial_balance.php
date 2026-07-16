<?php
session_start();
include 'connection.php';

$accounts = [];

$select = "SELECT * FROM accounts";
$result = mysqli_query($conn, $select);

while ($row = mysqli_fetch_assoc($result)) {
    $accounts[] = $row['name'];
}
$totalAccounts = count($accounts);

$selectedAccount = trim($_GET['account'] ?? '');

$accountBalances = [];

// Trial Balance Query
$query = "
SELECT
    t.account,
    a.account_code,
    a.account_type,
    SUM(t.debit_amount) AS total_debit,
    SUM(t.credit_amount) AS total_credit
FROM
(
    SELECT debit_account AS account,
           amount AS debit_amount,
           0 AS credit_amount
    FROM journal_entries

    UNION ALL

    SELECT credit_account AS account,
           0 AS debit_amount,
           amount AS credit_amount
    FROM journal_entries
) AS t

LEFT JOIN accounts a
ON a.name = t.account
";

if($selectedAccount != "")
{
    $query .= " WHERE t.account = '$selectedAccount' ";
}

$query .= "
GROUP BY
    t.account,
    a.account_code,
    a.account_type
ORDER BY
    a.account_code ASC
";


$result = mysqli_query($conn,$query);

while($row = mysqli_fetch_assoc($result))
{
   $accountBalances[] = [
    'code'    => $row['account_code'],
    'account' => $row['account'],
    'type'    => $row['account_type'],
    'debit'   => $row['total_debit'],
    'credit'  => $row['total_credit'],
    'balance' => $row['total_debit'] - $row['total_credit']
];
}

$totalDebit = 0;
$totalCredit = 0;

foreach($accountBalances as $row)
{
    $totalDebit += $row['debit'];
    $totalCredit += $row['credit'];
}

function formatAmount($value) {
    return number_format((float)$value, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Accounts Ledger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #eef2ff 0%, #f8fbff 100%); }
        .sidebar { min-height: 100vh; background: #1f2937; color: #e5e7eb; }
        .sidebar a { color: #d1d5db; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { color: #ffffff; background: rgba(255,255,255,0.08); }
        .sidebar .nav-link { padding: 0.85rem 1rem; border-radius: 0.6rem; }
        .page-card { border-radius: 1.2rem; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); }
        .table-responsive { overflow: hidden; border-radius: 1rem; }
        .table thead th { border-bottom: 2px solid #e5e7eb; }
        .form-label { font-weight: 600; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row gx-0">
            <?php include 'sidebar.php'; ?>
            <main class="col-12 col-md-9 col-xl-9 p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="h3 mb-1">Trial Balance</h1>
                        <p class="text-muted mb-0">Summary of all ledger accounts with debit and credit balance</p>
                    </div>
                </div>
                
                <div class="card page-card bg-white border-0 p-4 mb-4">
                    <form method="GET" class="row gy-3 gx-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label small mb-1" for="accountFilter">Account Type</label>
                            <select id="accountFilter" name="account" class="form-select">
                                <option value="">All accounts</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?php echo htmlspecialchars($account); ?>" <?php echo $selectedAccount === $account ? 'selected' : ''; ?>><?php echo htmlspecialchars($account); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Apply Filters</button>
                        </div>
                    </form>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Total Accounts</span>
                                    <h3 class="mt-2 mb-0 text-center text-primary"><?php echo $totalAccounts; ?></h3>
                                </div>
                              <i class="bi bi-wallet2 text-success fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Total ledger Accounts</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Total Debit</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo formatAmount($totalDebit); ?></h3>
                                </div>
                                <i class="bi bi-arrow-down-circle text-success fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Total debits recorded across all accounts.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Total Credit</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo formatAmount($totalCredit); ?></h3>
                                </div>
                                <i class="bi bi-arrow-up-circle text-danger fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Total credits recorded across all accounts.</p>
                        </div>
                    </div>
                   <div class="col-12 col-md-3">
    <div class="card page-card border-0 p-4 bg-white h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <span class="text-uppercase text-muted small">Balance Status</span>

                <?php if($totalDebit == $totalCredit){ ?>

                    <h3 class="mt-2 mb-0 text-center text-success">
                        Balanced
                    </h3>

                <?php } else { ?>

                    <h3 class="mt-2 mb-0 text-center text-danger">
                        Unbalanced
                    </h3>

                <?php } ?>

            </div>

            <?php if($totalDebit == $totalCredit){ ?>

                <i class="bi bi-check-circle-fill text-success fs-2"></i>

            <?php } else { ?>

                <i class="bi bi-exclamation-triangle-fill text-danger fs-2"></i>

            <?php } ?>

        </div>

        <?php if($totalDebit == $totalCredit){ ?>

            <p class="text-muted mb-0">
                Debit = Credit
            </p>

        <?php } else { ?>

            <p class="text-muted mb-0">
                Debit ≠ Credit
            </p>

        <?php } ?>

    </div>
</div>
                </div>


               

                <div class="card page-card bg-white border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-1">Trial Balance</h2>
            <p class="text-muted mb-0">Summary of all ledger accounts.</p>
        </div>

        <?php if ($selectedAccount): ?>
            <span class="badge bg-primary bg-opacity-10 text-primary">
                <?php echo htmlspecialchars($selectedAccount); ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
               <tr>
    <th>#</th>
    
    <th>Account Name</th>
    <th class="text-end">Debit</th>
    <th class="text-end">Credit</th>
    <th class="text-end">Balance</th>
</tr>
            </thead>

            <tbody>

            <?php if(!empty($accountBalances)): ?>

                <?php $i = 1; ?>

                <?php foreach($accountBalances as $row): ?>

                <tr>

                   <td><?php echo $i++; ?></td>


<td><?php echo htmlspecialchars($row['account']); ?></td>


<td class="text-end text-success">
    Rs <?php echo formatAmount($row['debit']); ?>
</td>

<td class="text-end text-danger">
    Rs <?php echo formatAmount($row['credit']); ?>
</td>

<td class="text-end fw-bold">
    <?php
    if($row['balance'] > 0){
        echo "<span class='text-success'>Rs ".formatAmount($row['balance'])." Dr</span>";
    }elseif($row['balance'] < 0){
        echo "<span class='text-danger'>Rs ".formatAmount(abs($row['balance']))." Cr</span>";
    }else{
        echo "Rs 0.00";
    }
    ?>
</td>

                </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="5" class="text-center py-5 text-muted">

                        No Accounts Found

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

            <tfoot class="table-light fw-bold">

                <tr>

                    <td colspan="2">Grand Total</td>

                    <td class="text-end text-success">

                        Rs <?php echo formatAmount($totalDebit); ?>

                    </td>

                    <td class="text-end text-danger">

                        Rs <?php echo formatAmount($totalCredit); ?>

                    </td>

                    <td class="text-end">

                        <?php if($totalDebit == $totalCredit){ ?>

                            <span class="badge bg-success">
                                Balanced
                            </span>

                        <?php } else { ?>

                            <span class="badge bg-danger">
                                Unbalanced
                            </span>

                        <?php } ?>

                    </td>

                </tr>

            </tfoot>

        </table>
    </div>
</div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
