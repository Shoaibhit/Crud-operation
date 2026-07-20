<?php
require 'auth.php';
include 'connection.php';

$search = trim($_GET['search'] ?? '');
$result = null;

if ($search !== '') {
  $searchTerm = '%' . $search . '%';
  $stmt = mysqli_prepare($conn, "SELECT name, phone_no, type FROM investors WHERE name LIKE ? OR phone_no LIKE ? OR type LIKE ? ORDER BY name ASC");
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'sss', $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
  }
} else {
  $query = "SELECT name, phone_no, type FROM investors ORDER BY name ASC";
  $result = mysqli_query($conn, $query);
}
?>







<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ALL Debtors and Creditors</title>
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
        <main class="col-12 col-md-9 col-xl-10 p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h1 class="h3 mb-1">ALL Debtors and Creditors</h1>
              <p class="text-muted mb-0">
                Review and manage your debtor and creditor entries.
              </p>
            </div>
          </div>
          <div class="card page-card bg-white border-0 p-4">
            <form method="GET" class="d-flex justify-content-end mb-3">
              <div class="input-group" style="max-width: 360px;">
                <input
                  type="text"
                  name="search"
                  class="form-control"
                  placeholder="Search by name, phone or type"
                  value="<?php echo htmlspecialchars($search); ?>"
                />
                <button class="btn btn-primary" type="submit">
                  <i class="bi bi-search"></i>
                </button>
                <?php if ($search !== ''): ?>
                  <a class="btn btn-outline-secondary" href="all_debtors_creditors.php">Clear</a>
                <?php endif; ?>
              </div>
            </form>
            <div class="table-responsive">
              <table class="table table-bordered table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Phone-no</th>
                    <th scope="col">Type</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="3" class="text-center text-muted py-4">
                        No debtor / creditor entries found.
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
