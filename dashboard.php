<?php
require 'auth.php';
include 'connection.php';

function fetchScalar($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row[array_key_first($row)];
    }
    return 0;
}

$totalIncome = (float) fetchScalar($conn, "SELECT COALESCE(SUM(amount), 0) AS total FROM income");
$totalExpense = (float) fetchScalar($conn, "SELECT COALESCE(SUM(amount), 0) AS total FROM expence");
$netBalance = $totalIncome - $totalExpense;

$totalAccounts = (int) fetchScalar($conn, "SELECT COUNT(*) AS total FROM accounts");
$totalJournalEntries = (int) fetchScalar($conn, "SELECT COUNT(*) AS total FROM journal_entries");
$totalDebtors = (int) fetchScalar($conn, "SELECT COUNT(*) AS total FROM investors WHERE type = 'Debtor'");
$totalCreditors = (int) fetchScalar($conn, "SELECT COUNT(*) AS total FROM investors WHERE type = 'Creditor'");
$totalLoans = (int) fetchScalar($conn, "SELECT COUNT(*) AS total FROM loan_entries");

$incomeCategories = [];
$incomeCatQuery = "SELECT income_category AS category, COALESCE(SUM(amount), 0) AS total FROM income GROUP BY income_category ORDER BY total DESC LIMIT 6";
$incomeCatResult = mysqli_query($conn, $incomeCatQuery);
if ($incomeCatResult) {
    while ($row = mysqli_fetch_assoc($incomeCatResult)) {
        $incomeCategories[] = $row;
    }
}

$expenseCategories = [];
$expenseCatQuery = "SELECT expence_category AS category, COALESCE(SUM(amount), 0) AS total FROM expence GROUP BY expence_category ORDER BY total DESC LIMIT 6";
$expenseCatResult = mysqli_query($conn, $expenseCatQuery);
if ($expenseCatResult) {
    while ($row = mysqli_fetch_assoc($expenseCatResult)) {
        $expenseCategories[] = $row;
    }
}

$recentIncome = [];
$recentIncomeResult = mysqli_query($conn, "SELECT date, amount, income_category, payment_method, reference_no FROM income ORDER BY id DESC LIMIT 5");
if ($recentIncomeResult) {
    while ($row = mysqli_fetch_assoc($recentIncomeResult)) {
        $recentIncome[] = $row;
    }
}

$recentExpense = [];
$recentExpenseResult = mysqli_query($conn, "SELECT date, amount, expence_category AS category, payment_method, note AS notes, reference_no FROM expence ORDER BY id DESC LIMIT 5");
if ($recentExpenseResult) {
    while ($row = mysqli_fetch_assoc($recentExpenseResult)) {
        $recentExpense[] = $row;
    }
}

$profitLossRows = [];
$profitLossQuery = "SELECT d.date AS date, COALESCE(i.total_income, 0) AS income, COALESCE(e.total_expense, 0) AS expense, (COALESCE(i.total_income, 0) - COALESCE(e.total_expense, 0)) AS profit_loss FROM (SELECT date FROM income UNION SELECT date FROM expence) d LEFT JOIN (SELECT date, SUM(amount) AS total_income FROM income GROUP BY date) i ON i.date = d.date LEFT JOIN (SELECT date, SUM(amount) AS total_expense FROM expence GROUP BY date) e ON e.date = d.date ORDER BY d.date ASC";
$profitLossResult = mysqli_query($conn, $profitLossQuery);
if ($profitLossResult) {
    while ($row = mysqli_fetch_assoc($profitLossResult)) {
        $profitLossRows[] = $row;
    }
}

function formatAmount($value) {
    return number_format((float)$value, 2);
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
      crossorigin="anonymous"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
    />
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
      .stat-card {
        min-height: 160px;
      }
      .category-list {
        max-height: 260px;
        overflow-y: auto;
      }
      .summary-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.25rem;
      }
      .summary-item {
        flex: 1 1 calc(14.285% - 1rem);
        min-width: 140px;
      }
      .summary-item .card-body {
        padding: 1rem 1rem 0.9rem;
      }
      .summary-item .stat-value {
        font-size: 1.45rem;
        line-height: 1.1;
      }
      .chart-card {
        border-radius: 1.2rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.04);
        background: transparent;
        border: 1px solid rgba(148, 163, 184, 0.18);
      }
      .chart-canvas {
        min-height: 360px;
        padding: 1rem;
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
        <main class="col-12 col-md-9 col-xl-10 p-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
              <h1 class="h3 mb-1">Admin Dashboard</h1>
              <p class="text-muted mb-0">Summary of income, expense, accounts and financial performance.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="income_form.php" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-wallet me-2"></i>Add Income
              </a>
              <a href="expence_form.php" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-wallet2 me-2"></i>Add Expense
              </a>
            </div>
          </div>

          <div class="summary-row">
            <div class="summary-item card page-card border-0 bg-white h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span class="text-uppercase text-muted small">Balance</span>
                    <h4 class="mt-2 mb-1 stat-value">Rs <?php echo formatAmount($netBalance); ?></h4>
                  </div>
                  <i class="bi bi-cash-stack text-primary fs-3"></i>
                </div>
                <p class="text-muted mb-0">Available Balance</p>
              </div>
            </div>
            <div class="summary-item card page-card border-0 bg-white h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span class="text-uppercase text-muted small">Income</span>
                    <h4 class="mt-2 mb-1 stat-value">Rs <?php echo formatAmount($totalIncome); ?></h4>
                  </div>
                  <i class="bi bi-wallet2 text-success fs-3"></i>
                </div>
                <p class="text-muted mb-0">Total income.</p>
              </div>
            </div>
            <div class="summary-item card page-card border-0 bg-white h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span class="text-uppercase text-muted small">Expense</span>
                    <h4 class="mt-2 mb-1 stat-value">Rs <?php echo formatAmount($totalExpense); ?></h4>
                  </div>
                  <i class="bi bi-credit-card-2-back text-danger fs-3"></i>
                </div>
                <p class="text-muted mb-0">Total expense.</p>
              </div>
            </div>
            <div class="summary-item card page-card border-0 bg-white h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span class="text-uppercase text-muted small">Journal entries</span>
                    <h4 class="mt-2 mb-1"><?php echo $totalJournalEntries; ?></h4>
                  </div>
                  <i class="bi bi-journal-text text-info fs-3"></i>
                </div>
                <p class="text-muted mb-0">Journal records</p>
              </div>
            </div>
            <div class="summary-item card page-card border-0 bg-white h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span class="text-uppercase text-muted small">Accounts</span>
                    <h4 class="mt-2 mb-1"><?php echo $totalAccounts; ?></h4>
                  </div>
                  <i class="bi bi-bookmarks text-warning fs-3"></i>
                </div>
                <p class="text-muted mb-0">Total Accounts</p>
              </div>
            </div>
            <div class="summary-item card page-card border-0 bg-white h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span class="text-uppercase text-muted small">Debtors</span>
                    <h4 class="mt-2 mb-1"><?php echo $totalDebtors; ?></h4>
                  </div>
                  <i class="bi bi-people-fill text-secondary fs-3"></i>
                </div>
                <p class="text-muted mb-0">Total Debtor</p>
              </div>
            </div>
            <div class="summary-item card page-card border-0 bg-white h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <span class="text-uppercase text-muted small">Creditors</span>
                    <h4 class="mt-2 mb-1"><?php echo $totalCreditors; ?></h4>
                  </div>
                  <i class="bi bi-people text-secondary fs-3"></i>
                </div>
                <p class="text-muted mb-0">Total Creditor</p>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-12 col-lg-6">
              <div class="card page-card border-0 overflow-hidden chart-card">
                <div class="p-4">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h2 class="h5 mb-1">Income by Category</h2>
                      <p class="text-muted mb-0">Top income categories visualized.</p>
                    </div>
                    <span class="badge rounded-pill bg-success text-light bg-opacity-15 text-success py-2 px-3">Income</span>
                  </div>
                </div>
                <div id="incomePieChart" class="chart-canvas"></div>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="card page-card border-0 overflow-hidden chart-card">
                <div class="p-4">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h2 class="h5 mb-1">Expense by Category</h2>
                      <p class="text-muted mb-0">Top expense categories visualized.</p>
                    </div>
                    <span class="badge rounded-pill bg-danger bg-opacity-15 text-danger py-2 px-3 text-light">Expense</span>
                  </div>
                </div>
                <div id="expensePieChart" class="chart-canvas"></div>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-12">
              <div class="card page-card border-0 overflow-hidden chart-card">
                <div class="p-4">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h2 class="h5 mb-1">Profit / Loss Trend</h2>
                      <p class="text-muted mb-0">Income expense over time.</p>
                    </div>
                    <span class="badge rounded-pill bg-secondary text-light bg-opacity-15 text-secondary py-2 px-3">Trend</span>
                  </div>
                </div>
                <div id="profitLossChart" class="chart-canvas"></div>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-12 col-lg-6">
              <div class="card page-card border-0 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <h2 class="h5 mb-1">Recent Income</h2>
                    <p class="text-muted mb-0">Latest 5 income transactions.</p>
                  </div>
                </div>
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th class="text-end">Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($recentIncome) > 0): ?>
                        <?php foreach ($recentIncome as $row): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                            <td><?php echo htmlspecialchars($row['income_category']); ?></td>
                            <td class="text-end">Rs <?php echo formatAmount($row['amount']); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No recent income records.</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="card page-card border-0 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <h2 class="h5 mb-1">Recent Expense</h2>
                    <p class="text-muted mb-0">Latest 5 expense transactions.</p>
                  </div>
                </div>
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th class="text-end">Amount</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (count($recentExpense) > 0): ?>
                        <?php foreach ($recentExpense as $row): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td class="text-end">Rs <?php echo formatAmount($row['amount']); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">No recent expense records.</td></tr>
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


    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawCharts);

      function drawCharts() {
        var incomeData = google.visualization.arrayToDataTable([
          ['Category', 'Amount'],
          <?php foreach ($incomeCategories as $cat): ?>
            ['<?php echo htmlspecialchars(addslashes($cat['category'])); ?>', <?php echo (float)$cat['total']; ?>],
          <?php endforeach; ?>
        ]);

        var expenseData = google.visualization.arrayToDataTable([
          ['Category', 'Amount'],
          <?php foreach ($expenseCategories as $cat): ?>
            ['<?php echo htmlspecialchars(addslashes($cat['category'])); ?>', <?php echo (float)$cat['total']; ?>],
          <?php endforeach; ?>
        ]);

        var commonOptions = {
          pieHole: 0.54,
          legend: { position: 'bottom', alignment: 'center', textStyle: { color: '#6b7280', fontSize: 12 } },
          chartArea: { width: '90%', height: '80%' },
          backgroundColor: 'transparent',
          tooltip: { textStyle: { fontSize: 13 }, showColorCode: true },
          pieSliceBorderColor: '#ffffff',
          slices: {
            0: { color: '#0d6efd' },
            1: { color: '#198754' },
            2: { color: '#dc3545' },
            3: { color: '#fd7e14' },
            4: { color: '#6f42c1' },
            5: { color: '#0dcaf0' }
          }
        };

        var incomeOptions = Object.assign({}, commonOptions, {
          title: '',
        });
        var expenseOptions = Object.assign({}, commonOptions, {
          title: '',
        });

        var incomeChart = new google.visualization.PieChart(document.getElementById('incomePieChart'));
        var expenseChart = new google.visualization.PieChart(document.getElementById('expensePieChart'));

        var profitLossData = google.visualization.arrayToDataTable([
          ['Date', 'Profit / Loss'],
          <?php foreach ($profitLossRows as $row): ?>
            ['<?php echo htmlspecialchars(addslashes($row['date'])); ?>', <?php echo (float)$row['profit_loss']; ?>],
          <?php endforeach; ?>
        ]);

        var profitLossOptions = {
          title: '',
          curveType: 'function',
          legend: { position: 'bottom', textStyle: { color: '#6b7280', fontSize: 12 } },
          chartArea: { width: '90%', height: '75%' },
          backgroundColor: 'transparent',
          hAxis: { textStyle: { color: '#475569', fontSize: 11 }, title: 'Date', titleTextStyle: { color: '#475569' } },
          vAxis: { textStyle: { color: '#475569', fontSize: 11 }, title: 'Profit / Loss', titleTextStyle: { color: '#475569' } },
          colors: ['#0d6efd'],
          lineWidth: 3,
          pointSize: 5,
          tooltip: { textStyle: { fontSize: 13 } }
        };

        var profitLossChart = new google.visualization.LineChart(document.getElementById('profitLossChart'));

        incomeChart.draw(incomeData, incomeOptions);
        expenseChart.draw(expenseData, expenseOptions);
        profitLossChart.draw(profitLossData, profitLossOptions);
      }
    </script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
      crossorigin="anonymous"
    ></script>
  </body>
</html>
