<?php
require 'auth.php';
include 'connection.php';

$search = trim($_GET['search'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');

$conditions = [];
$params = [];
$types = '';

if ($startDate !== '') {
    $conditions[] = 'entry_date >= ?';
    $params[] = $startDate;
    $types .= 's';
}
if ($endDate !== '') {
    $conditions[] = 'entry_date <= ?';
    $params[] = $endDate;
    $types .= 's';
}
if ($search !== '') {
    $conditions[] = '(reference_no LIKE ? OR debit_account LIKE ? OR credit_account LIKE ? OR description LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ssss';
}

$query = 'SELECT id, reference_no, entry_date, debit_account, credit_account, amount, description FROM journal_entries';
if (!empty($conditions)) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}
$query .= ' ORDER BY id DESC';

$stmt = mysqli_prepare($conn, $query);
if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $query);
}

$totalQuery = 'SELECT COALESCE(SUM(amount),0) AS total_amount FROM journal_entries';
if (!empty($conditions)) {
    $totalQuery .= ' WHERE ' . implode(' AND ', $conditions);
}
$stmtTotal = mysqli_prepare($conn, $totalQuery);
if ($stmtTotal) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmtTotal, $types, ...$params);
    }
    mysqli_stmt_execute($stmtTotal);
    $totalResult = mysqli_stmt_get_result($stmtTotal);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalAmount = $totalRow['total_amount'] ?? 0;
} else {
    $totalAmount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Journal Entries</title>
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
            <main class="col-12 col-md-9 col-xl-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Journal Entries</h1>
                        <p class="text-muted mb-0">View all journal entries with search and date filters.</p>
                    </div>
                    <a href="journal_form.php" class="btn btn-primary btn-sm rounded-pill px-4">
                        <i class="bi bi-plus-circle me-2"></i> New Journal Entry
                    </a>
                </div>

                <div class="card page-card bg-white border-0 p-4 mb-4">
                    <form class="row g-3 align-items-end" method="GET">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">From Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>" />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">To Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Reference, account or description" value="<?php echo htmlspecialchars($search); ?>" />
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Filter</button>
                        </div>
                    </form>
                </div>

                <div class="card page-card bg-white border-0 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="h5 mb-1">Journal List</h2>
                            <p class="text-muted mb-0">Total amount: Rs <?php echo number_format($totalAmount, 2); ?></p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-dark">
                                <tr>
                                    <th scope="col">Reference</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Debit</th>
                                    <th scope="col">Credit</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['entry_date']); ?></td>
                                            <td><?php echo htmlspecialchars($row['debit_account'] === '' || $row['debit_account'] === '0' ? '-' : $row['debit_account']); ?></td>
                                            <td><?php echo htmlspecialchars($row['credit_account'] === '' || $row['credit_account'] === '0' ? '-' : $row['credit_account']); ?></td>
                                            <td class="text-end">Rs <?php echo number_format($row['amount'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                                            <td>
                                                <a href="edit_journal_entry.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No journal entries found.</td>
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
