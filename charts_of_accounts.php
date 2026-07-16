<?php
require 'connection.php';

$incomeCategories = [];
$expenseCategories = [];
$incomeCategoryList = [];
$expenseCategoryList = [];

$selectedIncomeCategory = trim($_GET['income_category_filter'] ?? '');
$selectedExpenseCategory = trim($_GET['expense_category_filter'] ?? '');

$totalIncome = 0;
$totalExpense = 0;

$incomeListQuery = "SELECT name FROM income_categories ORDER BY name ASC";
$incomeListResult = mysqli_query($conn, $incomeListQuery);
if ($incomeListResult) {
    while ($row = mysqli_fetch_assoc($incomeListResult)) {
        $incomeCategoryList[] = $row['name'];
    }
}

$expenseListQuery = "SELECT name FROM expense_categories ORDER BY name ASC";
$expenseListResult = mysqli_query($conn, $expenseListQuery);
if ($expenseListResult) {
    while ($row = mysqli_fetch_assoc($expenseListResult)) {
        $expenseCategoryList[] = $row['name'];
    }
}

$incomeFilterWhere = '';
if ($selectedIncomeCategory !== '') {
    $incomeFilter = mysqli_real_escape_string($conn, $selectedIncomeCategory);
    $incomeFilterWhere = "WHERE ic.name = '$incomeFilter'";
}

$expenseFilterWhere = '';
if ($selectedExpenseCategory !== '') {
    $expenseFilter = mysqli_real_escape_string($conn, $selectedExpenseCategory);
    $expenseFilterWhere = "WHERE ec.name = '$expenseFilter'";
}

$incomeQuery = "SELECT ic.id, ic.name,
    COALESCE((SELECT COUNT(*) FROM income i WHERE i.income_category = ic.name), 0) AS entries,
    COALESCE((SELECT SUM(i.amount) FROM income i WHERE i.income_category = ic.name), 0) AS total
FROM income_categories ic
$incomeFilterWhere
ORDER BY ic.name ASC";
$incomeResult = mysqli_query($conn, $incomeQuery);
if ($incomeResult) {
    while ($row = mysqli_fetch_assoc($incomeResult)) {
        $incomeCategories[] = $row;
    }
}

$expenseQuery = "SELECT ec.id, ec.name,
    COALESCE((SELECT COUNT(*) FROM expence e WHERE e.expence_category = ec.name), 0) AS entries,
    COALESCE((SELECT SUM(e.amount) FROM expence e WHERE e.expence_category = ec.name), 0) AS total
FROM expense_categories ec
$expenseFilterWhere
ORDER BY ec.name ASC";
$expenseResult = mysqli_query($conn, $expenseQuery);
if ($expenseResult) {
    while ($row = mysqli_fetch_assoc($expenseResult)) {
        $expenseCategories[] = $row;
    }
}

$sumIncomeSql = "SELECT COALESCE(SUM(amount), 0) AS total FROM income";
if ($selectedIncomeCategory !== '') {
    $sumIncomeSql .= " WHERE income_category = '" . mysqli_real_escape_string($conn, $selectedIncomeCategory) . "'";
}
$sumIncomeResult = mysqli_query($conn, $sumIncomeSql);
$totalIncome = 0;
if ($sumIncomeResult) {
    $row = mysqli_fetch_assoc($sumIncomeResult);
    $totalIncome = $row['total'] ?? 0;
}

$sumExpenseSql = "SELECT COALESCE(SUM(amount), 0) AS total FROM expence";
if ($selectedExpenseCategory !== '') {
    $sumExpenseSql .= " WHERE expence_category = '" . mysqli_real_escape_string($conn, $selectedExpenseCategory) . "'";
}
$sumExpenseResult = mysqli_query($conn, $sumExpenseSql);
$totalExpense = 0;
if ($sumExpenseResult) {
    $row = mysqli_fetch_assoc($sumExpenseResult);
    $totalExpense = $row['total'] ?? 0;
}

$netBalance = $totalIncome - $totalExpense;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charts of Accounts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #eef2ff 0%, #f8fbff 100%);
        }
        .sidebar {
            min-height: 100vh;
            background: #1f2937;
            color: #e5e7eb;
        }
        .sidebar a {
            color: #d1d5db;
            text-decoration: none;
        }
        .sidebar a:hover,
        .sidebar a.active {
            color: #ffffff;
            background: rgba(255,255,255,0.08);
        }
        .sidebar .nav-link {
            padding: 0.85rem 1rem;
            border-radius: 0.6rem;
        }
        .page-card {
            border-radius: 1.2rem;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }
        .table-responsive {
            overflow: hidden;
            border-radius: 1rem;
        }
        .table thead th {
            border-bottom: 2px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row gx-0">
            <?php include 'sidebar.php'; ?>
            <main class="col-12 col-md-9 col-xl-9 p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
                    <div>
                        <h1 class="h3 mb-1">Income & Expense Reports</h1>
                        <p class="text-muted mb-0">Track income and expense categories reports with totals and balances.</p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-4">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Total Income</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo number_format($totalIncome, 2); ?></h3>
                                </div>
                                <i class="bi bi-currency-dollar fs-2 text-success"></i>
                            </div>
                            <p class="text-muted mb-0"><?php echo $selectedIncomeCategory ? 'Income recorded in ' . htmlspecialchars($selectedIncomeCategory) . ' category.' : 'Income recorded in all income categories.'; ?></p>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Total Expense</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo number_format($totalExpense, 2); ?></h3>
                                </div>
                                <i class="bi bi-wallet2 fs-2 text-danger"></i>
                            </div>
                            <p class="text-muted mb-0"><?php echo $selectedExpenseCategory ? 'Expense recorded in ' . htmlspecialchars($selectedExpenseCategory) . ' category.' : 'Expense recorded in all expense categories.'; ?></p>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="card page-card border-0 p-4 bg-white h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="text-uppercase text-muted small">Net Balance</span>
                                    <h3 class="mt-2 mb-0">Rs <?php echo number_format($netBalance, 2); ?></h3>
                                </div>
                                <i class="bi bi-graph-up-arrow fs-2 text-primary"></i>
                            </div>
                            <p class="text-muted mb-0">Income minus expense available across all accounts.</p>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-xl-6">
                        <div class="card page-card bg-white border-0 p-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-3">
                                <div>
                                    <h2 class="h5 mb-1">Income Accounts</h2>
                                    <p class="text-muted mb-0">Your income categories with transaction totals.</p>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success"><?php echo count($incomeCategories); ?> categories</span>
                            </div>
                            <form class="row row-cols-auto g-2 align-items-end mb-3" method="GET">
                                <div class="col">
                                    <label class="form-label small mb-1" for="incomeCategoryFilter">Filter Category</label>
                                    <select id="incomeCategoryFilter" name="income_category_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">All income</option>
                                        <?php foreach ($incomeCategoryList as $incomeCat): ?>
                                            <option value="<?php echo htmlspecialchars($incomeCat); ?>" <?php echo $selectedIncomeCategory === $incomeCat ? 'selected' : ''; ?>><?php echo htmlspecialchars($incomeCat); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($selectedExpenseCategory !== ''): ?>
                                    <input type="hidden" name="expense_category_filter" value="<?php echo htmlspecialchars($selectedExpenseCategory); ?>" />
                                <?php endif; ?>
                                <?php if ($selectedIncomeCategory !== ''): ?>
                                    <div class="col align-self-end">
                                        <a href="?<?php echo $selectedExpenseCategory ? 'expense_category_filter=' . urlencode($selectedExpenseCategory) : ''; ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
                                    </div>
                                <?php endif; ?>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-dark">
                                        <tr>
                                            <th scope="col">Category</th>
                                            <th scope="col" class="text-end">Entries</th>
                                            <th scope="col" class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($incomeCategories)): ?>
                                            <?php foreach ($incomeCategories as $category): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                    <td class="text-end"><?php echo intval($category['entries']); ?></td>
                                                    <td class="text-end">Rs <?php echo number_format($category['total'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No income categories found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-6">
                        <div class="card page-card bg-white border-0 p-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-3">
                                <div>
                                    <h2 class="h5 mb-1">Expense Accounts</h2>
                                    <p class="text-muted mb-0">Your expense categories with transaction totals.</p>
                                </div>
                                <span class="badge bg-danger bg-opacity-10 text-danger"><?php echo count($expenseCategories); ?> categories</span>
                            </div>
                            <form class="row row-cols-auto g-2 align-items-end mb-3" method="GET">
                                <div class="col">
                                    <label class="form-label small mb-1" for="expenseCategoryFilter">Filter Category</label>
                                    <select id="expenseCategoryFilter" name="expense_category_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">All expense</option>
                                        <?php foreach ($expenseCategoryList as $expenseCat): ?>
                                            <option value="<?php echo htmlspecialchars($expenseCat); ?>" <?php echo $selectedExpenseCategory === $expenseCat ? 'selected' : ''; ?>><?php echo htmlspecialchars($expenseCat); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($selectedIncomeCategory !== ''): ?>
                                    <input type="hidden" name="income_category_filter" value="<?php echo htmlspecialchars($selectedIncomeCategory); ?>" />
                                <?php endif; ?>
                                <?php if ($selectedExpenseCategory !== ''): ?>
                                    <div class="col align-self-end">
                                        <a href="?<?php echo $selectedIncomeCategory ? 'income_category_filter=' . urlencode($selectedIncomeCategory) : ''; ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
                                    </div>
                                <?php endif; ?>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-dark">
                                        <tr>
                                            <th scope="col">Category</th>
                                            <th scope="col" class="text-end">Entries</th>
                                            <th scope="col" class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($expenseCategories)): ?>
                                            <?php foreach ($expenseCategories as $category): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                    <td class="text-end"><?php echo intval($category['entries']); ?></td>
                                                    <td class="text-end">Rs <?php echo number_format($category['total'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No expense categories found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>