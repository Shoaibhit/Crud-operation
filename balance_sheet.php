<?php
require 'auth.php';
include 'connection.php';

$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$conditions = '';
$types = '';
$params = [];
if ($startDate !== '') {
    $conditions .= " AND entry_date >= ?";
    $types .= 's';
    $params[] = $startDate;
}
if ($endDate !== '') {
    $conditions .= " AND entry_date <= ?";
    $types .= 's';
    $params[] = $endDate;
}

// Each subquery uses its own copy of the date placeholders.
if ($conditions !== '') {
    $types .= $types;
    $params = array_merge($params, $params);
}

// Build the net balance of every account from journal entries.
// Asset accounts normally carry a debit balance; Liability & Equity normally carry a credit balance.
$journalWhere = $conditions !== '' ? "WHERE 1=1 $conditions" : '';
$balanceQuery = "
SELECT
    a.name AS account_name,
    a.account_type AS account_type,
    COALESCE(SUM(j.debit_amount), 0) AS total_debit,
    COALESCE(SUM(j.credit_amount), 0) AS total_credit
FROM accounts a
LEFT JOIN
(
    SELECT debit_account AS account, amount AS debit_amount, 0 AS credit_amount
    FROM journal_entries
    $journalWhere
    UNION ALL
    SELECT credit_account AS account, 0 AS debit_amount, amount AS credit_amount
    FROM journal_entries
    $journalWhere
) j ON j.account = a.name
GROUP BY a.id, a.name, a.account_type
ORDER BY a.account_type, a.name
";

$stmt = mysqli_prepare($conn, $balanceQuery);
if ($stmt === false) {
    die("Query prepare failed: " . mysqli_error($conn));
}
if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Group accounts by type with their net normal balance.
$accountsByType = [
    'Asset'      => [],
    'Liability'  => [],
    'Equity'     => [],
    'Uncategorized' => [],
];
$typeTotals = [
    'Asset'      => 0,
    'Liability'  => 0,
    'Equity'     => 0,
    'Uncategorized' => 0,
];

while ($row = mysqli_fetch_assoc($result)) {
    $type = $row['account_type'] ?: 'Uncategorized';
    if (!isset($accountsByType[$type])) {
        $accountsByType[$type] = [];
        $typeTotals[$type] = 0;
    }
    $debit = (float)($row['total_debit'] ?? 0);
    $credit = (float)($row['total_credit'] ?? 0);

    // Normal balance direction per account type.
    if ($type === 'Liability' || $type === 'Equity') {
        $balance = $credit - $debit; // credit normal
    } else {
        $balance = $debit - $credit; // debit normal (Asset, Uncategorized)
    }

    $accountsByType[$type][$row['account_name']] = $balance;
    $typeTotals[$type] += $balance;
}

$totalAssets = (float)($typeTotals['Asset'] ?? 0) + (float)($typeTotals['Uncategorized'] ?? 0);
$totalLiabilities = (float)($typeTotals['Liability'] ?? 0);
$totalEquity = (float)($typeTotals['Equity'] ?? 0);
$totalLiabilitiesEquity = $totalLiabilities + $totalEquity;

function formatAmount($value) {
    return number_format((float)$value, 2);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Balance Sheet</title>
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
        .section-title { font-weight: 700; letter-spacing: 0.03em; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row gx-0">
            <?php include 'sidebar.php'; ?>
            <main class="col-12 col-md-9 col-xl-10 p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="h3 mb-1">Balance Sheet</h1>
                        <p class="text-muted mb-0">Statement of financial position (Assets = Liabilities + Equity)</p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Total Assets</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo formatAmount($totalAssets); ?></h3>
                                </div>
                                <i class="bi bi-safe2 text-primary fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Total assets balance.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Total Liabilities</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo formatAmount($totalLiabilities); ?></h3>
                                </div>
                                <i class="bi bi-credit-card text-warning fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Total liabilities balance.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Total Equity</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo formatAmount($totalEquity); ?></h3>
                                </div>
                                <i class="bi bi-pie-chart text-info fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Total equity balance.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Balancing Status</span>
                                    <?php if (abs($totalAssets - $totalLiabilitiesEquity) < 0.01): ?>
                                        <h3 class="mt-2 mb-0 text-success">Balanced</h3>
                                    <?php else: ?>
                                        <h3 class="mt-2 mb-0 text-danger">Unbalanced</h3>
                                    <?php endif; ?>
                                </div>
                                <?php if (abs($totalAssets - $totalLiabilitiesEquity) < 0.01): ?>
                                    <i class="bi bi-check-circle-fill text-success fs-2"></i>
                                <?php else: ?>
                                    <i class="bi bi-exclamation-triangle-fill text-danger fs-2"></i>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted mb-0">Assets vs Liabilities + Equity.</p>
                        </div>
                    </div>
                </div>

                <div class="card page-card bg-white border-0 p-3 mb-4">
                    <form method="get" class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="startDate" class="form-label small text-muted mb-1">From Date</label>
                            <input type="date" id="startDate" name="start_date" class="form-control rounded-4" value="<?php echo htmlspecialchars($startDate); ?>" />
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="endDate" class="form-label small text-muted mb-1">To Date</label>
                            <input type="date" id="endDate" name="end_date" class="form-control rounded-4" value="<?php echo htmlspecialchars($endDate); ?>" />
                        </div>
                        <div class="col-12 col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded-4 px-4">Filter</button>
                            <a href="balance_sheet.php" class="btn btn-outline-secondary rounded-4 px-4">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="card page-card bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h5 mb-1">Balance Sheet</h2>
                            <p class="text-muted mb-0">Assets, Liabilities and Equity.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <h5 class="section-title text-primary mb-3">Assets</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Account</th>
                                            <th class="text-end">Amount (Rs)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $hasAsset = false; ?>
                                        <?php foreach (array_merge($accountsByType['Asset'] ?? [], $accountsByType['Uncategorized'] ?? []) as $accName => $bal): ?>
                                            <?php if ((float)$bal != 0): ?>
                                                <?php $hasAsset = true; ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($accName); ?></td>
                                                    <td class="text-end"><?php echo formatAmount($bal); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?php if (!$hasAsset): ?>
                                            <tr><td colspan="2" class="text-center text-muted py-3">No asset balances</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td>Total Assets</td>
                                            <td class="text-end">Rs <?php echo formatAmount($totalAssets); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <h5 class="section-title text-warning mb-3">Liabilities</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Account</th>
                                            <th class="text-end">Amount (Rs)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $hasLiab = false; ?>
                                        <?php foreach ($accountsByType['Liability'] ?? [] as $accName => $bal): ?>
                                            <?php if ((float)$bal != 0): ?>
                                                <?php $hasLiab = true; ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($accName); ?></td>
                                                    <td class="text-end"><?php echo formatAmount($bal); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?php if (!$hasLiab): ?>
                                            <tr><td colspan="2" class="text-center text-muted py-3">No liability balances</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <h5 class="section-title text-info mb-3 mt-4">Equity</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Account</th>
                                            <th class="text-end">Amount (Rs)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $hasEquity = false; ?>
                                        <?php foreach ($accountsByType['Equity'] ?? [] as $accName => $bal): ?>
                                            <?php if ((float)$bal != 0): ?>
                                                <?php $hasEquity = true; ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($accName); ?></td>
                                                    <td class="text-end"><?php echo formatAmount($bal); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?php if (!$hasEquity): ?>
                                            <tr><td colspan="2" class="text-center text-muted py-3">No equity balances</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td>Total Liabilities &amp; Equity</td>
                                            <td class="text-end">Rs <?php echo formatAmount($totalLiabilitiesEquity); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="alert <?php echo abs($totalAssets - $totalLiabilitiesEquity) < 0.01 ? 'alert-success' : 'alert-danger'; ?> mt-3 d-flex align-items-center">
                        <i class="bi <?php echo abs($totalAssets - $totalLiabilitiesEquity) < 0.01 ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
                        <?php if (abs($totalAssets - $totalLiabilitiesEquity) < 0.01): ?>
                            Assets (Rs <?php echo formatAmount($totalAssets); ?>) equal Liabilities + Equity (Rs <?php echo formatAmount($totalLiabilitiesEquity); ?>) — the balance sheet is balanced.
                        <?php else: ?>
                            Difference: Rs <?php echo formatAmount(abs($totalAssets - $totalLiabilitiesEquity)); ?>. The balance sheet does not balance.
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
