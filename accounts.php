<?php
session_start();
include 'connection.php';



$selectedAccount = trim($_GET['account'] ?? '');
$selectedType = trim($_GET['transaction_type'] ?? 'all');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$search = trim($_GET['search'] ?? '');  

$accounts = [];

$select = "SELECT * FROM accounts";
$result = mysqli_query($conn, $select);

while ($row = mysqli_fetch_assoc($result)) {
    $accounts[] = $row['name'];
}

$accountBalances = [];
$balanceQuery = "SELECT account, COALESCE(SUM(debit_amount),0) AS total_debit, COALESCE(SUM(credit_amount),0) AS total_credit FROM (SELECT debit_account AS account, amount AS debit_amount, 0 AS credit_amount FROM journal_entries WHERE debit_account <> '0' AND debit_account <> '' UNION ALL SELECT credit_account AS account, 0 AS debit_amount, amount AS credit_amount FROM journal_entries WHERE credit_account <> '0' AND credit_account <> '') AS t GROUP BY account";
$balanceResult = mysqli_query($conn, $balanceQuery);
if ($balanceResult) {
    while ($row = mysqli_fetch_assoc($balanceResult)) {
        $accountBalances[$row['account']] = [
            'total_debit' => (float) $row['total_debit'],
            'total_credit' => (float) $row['total_credit'],
        ];
    }
}

$accountSummaries = [];
$totalDebit = 0;
$totalCredit = 0;
$allAccounts = array_keys($accountBalances);
sort($allAccounts, SORT_STRING);
foreach ($allAccounts as $accountName) {
    $debit = $accountBalances[$accountName]['total_debit'] ?? 0;
    $credit = $accountBalances[$accountName]['total_credit'] ?? 0;
    $balance = $debit - $credit;
    $accountSummaries[] = [
        'account' => $accountName,
        'total_debit' => $debit,
        'total_credit' => $credit,
        'balance' => $balance,
    ];
    $totalDebit += $debit;
    $totalCredit += $credit;
}

$debitAccounts = array_filter($accountSummaries, function ($item) {
    return $item['total_debit'] > 0;
});
$creditAccounts = array_filter($accountSummaries, function ($item) {
    return $item['total_credit'] > 0;
});

$detailConditions = [];
$detailParams = [];
$detailTypes = '';
if ($selectedAccount !== '') {
    if ($selectedType === 'debit') {
        $detailConditions[] = 'debit_account = ?';
        $detailParams[] = $selectedAccount;
        $detailTypes .= 's';
    } elseif ($selectedType === 'credit') {
        $detailConditions[] = 'credit_account = ?';
        $detailParams[] = $selectedAccount;
        $detailTypes .= 's';
    } else {
        $detailConditions[] = '(debit_account = ? OR credit_account = ?)';
        $detailParams[] = $selectedAccount;
        $detailParams[] = $selectedAccount;
        $detailTypes .= 'ss';
    }
}

if ($startDate !== '') {
    $detailConditions[] = 'entry_date >= ?';
    $detailParams[] = $startDate;
    $detailTypes .= 's';
}
if ($endDate !== '') {
    $detailConditions[] = 'entry_date <= ?';
    $detailParams[] = $endDate;
    $detailTypes .= 's';
}
if ($search !== '') {
    $detailConditions[] = '(reference_no LIKE ? OR debit_account LIKE ? OR credit_account LIKE ? OR description LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $detailParams[] = $searchTerm;
    $detailParams[] = $searchTerm;
    $detailParams[] = $searchTerm;
    $detailParams[] = $searchTerm;
    $detailTypes .= 'ssss';
}

$detailQuery = 'SELECT reference_no, entry_date, debit_account, credit_account, amount, description FROM journal_entries';
if (!empty($detailConditions)) {
    $detailQuery .= ' WHERE ' . implode(' AND ', $detailConditions);
}
$detailQuery .= ' ORDER BY entry_date DESC, id DESC';
$detailStmt = mysqli_prepare($conn, $detailQuery);
$detailResult = false;
if ($detailStmt) {
    if (!empty($detailParams)) {
        mysqli_stmt_bind_param($detailStmt, $detailTypes, ...$detailParams);
    }
    mysqli_stmt_execute($detailStmt);
    $detailResult = mysqli_stmt_get_result($detailStmt);
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
    <title>Accounts</title>
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
                        <h1 class="h3 mb-1">Accounts Ledger</h1>
                        <p class="text-muted mb-0">View debit and credit account balances with filters and journal transaction details.</p>
                    </div>
                    <a href="journal_form.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                        <i class="bi bi-journal-text me-2"></i> New Journal Entry
                    </a>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
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
                    <div class="col-12 col-md-4">
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
                    <div class="col-12 col-md-4">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Net Balance</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo formatAmount($totalDebit - $totalCredit); ?></h3>
                                </div>
                                <i class="bi bi-calculator text-primary fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Debit minus credit across journal accounts.</p>
                        </div>
                    </div>
                </div>

                <div class="card page-card bg-white border-0 p-4 mb-4">
                    <form method="GET" class="row gy-3 gx-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label small mb-1" for="accountFilter">Account</label>
                            <select id="accountFilter" name="account" class="form-select">
                                <option value="">All accounts</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?php echo htmlspecialchars($account); ?>" <?php echo $selectedAccount === $account ? 'selected' : ''; ?>><?php echo htmlspecialchars($account); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small mb-1" for="startDate">From Date</label>
                            <input type="date" id="startDate" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>" />
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small mb-1" for="endDate">To Date</label>
                            <input type="date" id="endDate" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>" />
                        </div>
                        <div class="col-12 col-md-2 mx-auto">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Apply Filters</button>
                        </div>
                    </form>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-6">
                        <div class="card page-card bg-white border-0 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h2 class="h5 mb-1">Debit Accounts</h2>
                                    <p class="text-muted mb-0">Accounts with debit activity.</p>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success"><?php echo count($debitAccounts); ?> accounts</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-dark">
                                        <tr>
                                            <th scope="col">Account</th>
                                            <th scope="col" class="text-end">Debit</th>
                                            <th scope="col" class="text-end">Credit</th>
                                            <th scope="col" class="text-end">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($debitAccounts)): ?>
                                            <?php foreach ($debitAccounts as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['account']); ?></td>
                                                    <td class="text-end">Rs <?php echo formatAmount($item['total_debit']); ?></td>
                                                    <td class="text-end">Rs <?php echo formatAmount($item['total_credit']); ?></td>
                                                    <td class="text-end">Rs <?php echo formatAmount($item['balance']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No debit accounts found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="card page-card bg-white border-0 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h2 class="h5 mb-1">Credit Accounts</h2>
                                    <p class="text-muted mb-0">Accounts with credit activity.</p>
                                </div>
                                <span class="badge bg-danger bg-opacity-10 text-danger"><?php echo count($creditAccounts); ?> accounts</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-dark">
                                        <tr>
                                            <th scope="col">Account</th>
                                            <th scope="col" class="text-end">Debit</th>
                                            <th scope="col" class="text-end">Credit</th>
                                            <th scope="col" class="text-end">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($creditAccounts)): ?>
                                            <?php foreach ($creditAccounts as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['account']); ?></td>
                                                    <td class="text-end">Rs <?php echo formatAmount($item['total_debit']); ?></td>
                                                    <td class="text-end">Rs <?php echo formatAmount($item['total_credit']); ?></td>
                                                    <td class="text-end">Rs <?php echo formatAmount($item['balance']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No credit accounts found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card page-card bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h5 mb-1">Journal Transactions</h2>
                            <p class="text-muted mb-0">Filtered list of journal entries for selected accounts.</p>
                        </div>
                        <?php if ($selectedAccount): ?>
                            <span class="badge bg-primary bg-opacity-10 text-primary"><?php echo htmlspecialchars($selectedAccount); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-dark">
                                <tr>
                                    <th scope="col">Reference</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Debit Account</th>
                                    <th scope="col">Credit Account</th>
                                    <th scope="col" class="text-end">Amount</th>
                                    <th scope="col">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($detailResult && mysqli_num_rows($detailResult) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($detailResult)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['entry_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['debit_account'] === '' || $row['debit_account'] === '0' ? '-' : $row['debit_account']); ?></td>
                                            <td><?php echo htmlspecialchars($row['credit_account'] === '' || $row['credit_account'] === '0' ? '-' : $row['credit_account']); ?></td>
                                            <td class="text-end">Rs <?php echo formatAmount($row['amount']); ?></td>
                                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No journal transactions found for the selected filters.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
