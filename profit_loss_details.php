
<?php
session_start();
include 'connection.php';

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

ORDER BY d.date DESC
";
$result = mysqli_query($conn, $query);
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
  </body>
</html>
