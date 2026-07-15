
<?php
session_start();
include 'connection.php';

$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$query = "
SELECT
    d.date,
    'Daily Profit/Loss' AS description,
    COALESCE(i.total_income,0) AS amount,
    COALESCE(e.total_expense,0) AS expence_amount,
    (COALESCE(i.total_income,0)-COALESCE(e.total_expense,0)) AS profit_loss,
    '-' AS notes
FROM
(
    SELECT date FROM income
    UNION
    SELECT date FROM expence
) d

LEFT JOIN
(
    SELECT date, SUM(amount) AS total_income
    FROM income
    GROUP BY date
) i ON d.date = i.date

LEFT JOIN
(
    SELECT date, SUM(amount) AS total_expense
    FROM expence
    GROUP BY date
) e ON d.date = e.date
";

$conditions = [];
$params = [];
$types = '';

if ($startDate !== '') {
    $conditions[] = 'd.date >= ?';
    $params[] = $startDate;
    $types .= 's';
}

if ($endDate !== '') {
    $conditions[] = 'd.date <= ?';
    $params[] = $endDate;
    $types .= 's';
}

if (!empty($conditions)) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= ' ORDER BY d.date DESC';

$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profit/Loss Details</title>
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
        background: rgba(255, 255, 255, 0.08);
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
      .table-striped > tbody > tr:nth-of-type(odd) {
        background-color: rgba(15, 23, 42, 0.03);
      }
    </style>
  </head>
  <body>
    <div class="container-fluid">
      <div class="row gx-0">
        <?php include 'sidebar.php'; ?>
        <main class="col-12 col-md-9 col-xl-9 p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h1 class="h3 mb-1">Profit / Loss Details</h1>
              <p class="text-muted mb-0">
                Review and manage your profit and loss entries.
              </p>
            </div>
          </div>
          <div class="card page-card bg-white border-0 p-4">
            <form id="profitLossFilterForm" method="GET" class="mb-3">
              <div class="row g-2 align-items-end">
                <div class="col-12 col-md-2">
                  <label class="form-label small mb-1">From Date</label>
                  <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>" />
                </div>
                <div class="col-12 col-md-2">
                  <label class="form-label small mb-1">To Date</label>
                  <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>" />
                </div>
               
                <div class="col-12 col-md-2 ms-auto text-end">
                  <label class="form-label small mb-1 opacity-0">PDF</label>
                   <button id="exportPdfBtn" type="button" class="btn btn-outline-danger w-100">
                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                  </button>
                </div>
                <div class="col-12 col-md-2 text-end">
                  <label class="form-label small mb-1 opacity-0">Export</label>
                  <button id="exportCsvBtn" type="button" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Export
                  </button>
                </div>
              </div>
            </form>
            <div class="table-responsive">
              <table class="table table-bordered table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th scope="col">Date</th>
                    <th scope="col">Description</th>
                    <th scope="col">Income</th>
                    <th scope="col">Expense</th>
                    <th scope="col">Profit / Loss</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo number_format($row['expence_amount'], 2); ?></td>
                        <td class="<?php echo ($row['profit_loss'] >= 0) ? 'text-success fw-bold' : 'text-danger fw-bold'; ?>">
                          <?php echo number_format($row['profit_loss'], 2); ?>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="5" class="text-center text-muted py-4">
                        No profit / loss entries found.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </main>
      </div>
    </div>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
      crossorigin="anonymous"
    ></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('profitLossFilterForm');
        if (!form) return;

        const startDate = form.querySelector('input[name="start_date"]');
        const endDate = form.querySelector('input[name="end_date"]');
        const exportPdfBtn = document.getElementById('exportPdfBtn');
        const exportCsvBtn = document.getElementById('exportCsvBtn');
        let debounceTimer;

        const submitForm = () => {
          clearTimeout(debounceTimer);
          debounceTimer = setTimeout(() => form.submit(), 250);
        };

        if (startDate) startDate.addEventListener('change', submitForm);
        if (endDate) endDate.addEventListener('change', submitForm);

        if (exportPdfBtn) {
          exportPdfBtn.addEventListener('click', function () {
            window.print();
          });
        }

        if (exportCsvBtn) {
          exportCsvBtn.addEventListener('click', function () {
            const rows = Array.from(document.querySelectorAll('table tbody tr'));
            if (!rows.length) {
              alert('No data available to export.');
              return;
            }

            const csvHeaders = ['Date', 'Description', 'Income', 'Expense', 'Profit / Loss'];
            const csvRows = [csvHeaders.join(',')];

            rows.forEach((row) => {
              const cells = Array.from(row.querySelectorAll('td'));
              const cellValues = cells.map((cell) => '"' + cell.innerText.replace(/"/g, '""') + '"');
              csvRows.push(cellValues.join(','));
            });

            const csvContent = csvRows.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const now = new Date();
            const filename = `profit_loss_${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}.csv`;

            if (navigator.msSaveBlob) {
              navigator.msSaveBlob(blob, filename);
            } else {
              const url = URL.createObjectURL(blob);
              link.setAttribute('href', url);
              link.setAttribute('download', filename);
              link.style.visibility = 'hidden';
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
              URL.revokeObjectURL(url);
            }
          });
        }
      });
    </script>
  </body>
</html>
