<?php
require 'auth.php';
include 'connection.php';

$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$conditions = [];
$types = '';
$params = [];

if ($startDate !== '') {
    $conditions[] = 'date >= ?';
    $types .= 's';
    $params[] = $startDate;
}
if ($endDate !== '') {
    $conditions[] = 'date <= ?';
    $types .= 's';
    $params[] = $endDate;
}

$incomeWhere = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';
$expenseWhere = $incomeWhere;

$incomeQuery = "SELECT COALESCE(SUM(amount), 0) AS total FROM income $incomeWhere";
$expenseQuery = "SELECT COALESCE(SUM(amount), 0) AS total FROM expence $expenseWhere";

$stmtIncome = mysqli_prepare($conn, $incomeQuery);
if ($stmtIncome === false) {
    die("Income query prepare failed: " . mysqli_error($conn));
}
if ($types !== '') {
    mysqli_stmt_bind_param($stmtIncome, $types, ...$params);
}
mysqli_stmt_execute($stmtIncome);
$incomeResult = mysqli_stmt_get_result($stmtIncome);
$incomeRow = mysqli_fetch_assoc($incomeResult);
$totalIncome = (float)($incomeRow['total'] ?? 0);

$stmtExpense = mysqli_prepare($conn, $expenseQuery);
if ($stmtExpense === false) {
    die("Expense query prepare failed: " . mysqli_error($conn));
}
if ($types !== '') {
    mysqli_stmt_bind_param($stmtExpense, $types, ...$params);
}
mysqli_stmt_execute($stmtExpense);
$expenseResult = mysqli_stmt_get_result($stmtExpense);
$expenseRow = mysqli_fetch_assoc($expenseResult);
$totalExpense = (float)($expenseRow['total'] ?? 0);

$netIncome = $totalIncome - $totalExpense;

$periodLabel = 'All Time';
if ($startDate !== '' && $endDate !== '') {
    $periodLabel = 'From ' . $startDate . ' To ' . $endDate;
} elseif ($startDate !== '') {
    $periodLabel = 'From ' . $startDate . ' Onwards';
} elseif ($endDate !== '') {
    $periodLabel = 'Up To ' . $endDate;
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
    <title>Income Statement</title>
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
                        <h1 class="h3 mb-1">Income Statement</h1>
                        <p class="text-muted mb-0">Revenues and expenses for a selected period (<?php echo htmlspecialchars($periodLabel); ?>)</p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Total Revenue</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo formatAmount($totalIncome); ?></h3>
                                </div>
                                <i class="bi bi-currency-dollar text-success fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Total income recorded for the period.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Total Expenses</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo formatAmount($totalExpense); ?></h3>
                                </div>
                                <i class="bi bi-wallet2 text-danger fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Total expenses recorded for the period.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Net Income</span>
                                    <h3 class="mt-2 mb-0 text-<?php echo $netIncome >= 0 ? 'success' : 'danger'; ?>">Rs <?php echo formatAmount($netIncome); ?></h3>
                                </div>
                                <i class="bi bi-graph-up-arrow text-primary fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Revenue minus expenses.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Status</span>
                                    <h3 class="mt-2 mb-0 text-<?php echo $netIncome >= 0 ? 'success' : 'danger'; ?>"><?php echo $netIncome >= 0 ? 'Profit' : 'Loss'; ?></h3>
                                </div>
                                <i class="bi bi-<?php echo $netIncome >= 0 ? 'check-circle-fill text-success' : 'x-circle-fill text-danger'; ?> fs-2"></i>
                            </div>
                            <p class="text-muted mb-0">Financial performance for the selected period.</p>
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
                            <a href="income_statement.php" class="btn btn-outline-secondary rounded-4 px-4">Reset</a>
                        </div>
                    </form>
                </div>

                <div class="card page-card bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h5 mb-1">Income Statement</h2>
                            <p class="text-muted mb-0">Revenue and Expense breakdown.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <h5 class="section-title text-success mb-3">Revenue</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Account</th>
                                            <th class="text-end">Amount (Rs)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Total Income</td>
                                            <td class="text-end">Rs <?php echo formatAmount($totalIncome); ?></td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td>Total Revenue</td>
                                            <td class="text-end">Rs <?php echo formatAmount($totalIncome); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <h5 class="section-title text-danger mb-3">Expenses</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Account</th>
                                            <th class="text-end">Amount (Rs)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Total Expenses</td>
                                            <td class="text-end">Rs <?php echo formatAmount($totalExpense); ?></td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td>Total Expenses</td>
                                            <td class="text-end">Rs <?php echo formatAmount($totalExpense); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <h5 class="section-title text-primary mb-3 mt-4">Net Income</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Account</th>
                                            <th class="text-end">Amount (Rs)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Net Income</td>
                                            <td class="text-end text-<?php echo $netIncome >= 0 ? 'success' : 'danger'; ?> fw-bold">Rs <?php echo formatAmount($netIncome); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="alert <?php echo $netIncome >= 0 ? 'alert-success' : 'alert-danger'; ?> mt-3 d-flex align-items-center">
                        <i class="bi <?php echo $netIncome >= 0 ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
                        <?php if ($netIncome >= 0): ?>
                            Revenue (Rs <?php echo formatAmount($totalIncome); ?>) exceeds Expenses (Rs <?php echo formatAmount($totalExpense); ?>) with a Net Income of Rs <?php echo formatAmount($netIncome); ?>.
                        <?php else: ?>
                            Expenses (Rs <?php echo formatAmount($totalExpense); ?>) exceed Revenue (Rs <?php echo formatAmount($totalIncome); ?>) with a Net Loss of Rs <?php echo formatAmount(abs($netIncome)); ?>.
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
