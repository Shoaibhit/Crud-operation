
<?php
session_start();
include 'connection.php';

$successMessage = '';
$errorMessage = '';

if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}

$search = trim($_GET['search'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate = trim($_GET['end_date'] ?? '');
$category = trim($_GET['category'] ?? '');
$result = null;
$conditions = [];
$params = [];
$types = '';

$expenseCategories = [];
$catQuery = "SELECT name FROM expense_categories ORDER BY name ASC";
$catResult = mysqli_query($conn, $catQuery);
if ($catResult) {
    while ($catRow = mysqli_fetch_assoc($catResult)) {
        $expenseCategories[] = $catRow['name'];
    }
}

if ($startDate !== '') {
    $conditions[] = 'date >= ?';
    $params[] = $startDate;
    $types .= 's';
}

if ($endDate !== '') {
    $conditions[] = 'date <= ?';
    $params[] = $endDate;
    $types .= 's';
}

if ($search !== '') {
    $conditions[] = '(date LIKE ? OR amount LIKE ? OR reference_no LIKE ? OR expence_category LIKE ? OR payment_method LIKE ? OR note LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ssssss';
}

if ($category !== '') {
    $conditions[] = 'expence_category = ?';
    $params[] = $category;
    $types .= 's';
}

$query = "SELECT id, date, amount, expence_category, payment_method, note, reference_no FROM expence";
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
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['category'] = $row['expence_category'];
            $row['notes'] = $row['note'] ?? '';
            $reportRows[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>All Expense Records</title>
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
        .search-btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            min-width: 46px;
        }
        .clear-search-btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            min-width: 42px;
            padding: 0.5rem 0.7rem;
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
                        <h1 class="h3 mb-1">All Expense Records</h1>
                        <p class="text-muted mb-0">Your expense entries are listed here with category, amount and payment method.</p>
                    </div>
                    <div>
                        <a href="expence_form.php" class="btn btn-primary btn-sm rounded-pill px-4">
                            <i class="bi bi-plus-circle me-2"></i> Add Expense
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill ms-2 openAddCategory">
                            <i class="bi bi-folder-plus me-2"></i> Add Category
                        </button>
                    </div>
                </div>
                <div class="card page-card bg-white border-0 p-4">
                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($successMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($errorMessage)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($errorMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form id="expenseFilterForm" method="GET" class="mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-2">
                                <label class="form-label small mb-1">From Date</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>" />
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label small mb-1">To Date</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>" />
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label small mb-1">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($expenseCategories as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat); ?>"<?php echo ($category === $cat) ? ' selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 ms-auto">
                                <label class="form-label small mb-1">Search</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search by date, amount or reference-no" value="<?php echo htmlspecialchars($search); ?>" />
                                    <button class="btn btn-primary search-btn" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <?php if ($search !== '' || $startDate !== '' || $endDate !== '' || $category !== ''): ?>
                                        <a class="btn btn-outline-secondary clear-search-btn" href="all_expence.php" title="Clear filters">
                                            <i class="bi bi-x-lg"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-dark">
                                <tr>
                                    <th scope="col">Reference</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Payment Method</th>
                                    <th scope="col">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportRows)): ?>
                                    <?php foreach ($reportRows as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['reference_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                                            <td><?php echo number_format($row['amount'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                            <td><?php echo htmlspecialchars($row['notes']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No expense records found.
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('expenseFilterForm');
            if (!form) return;

            const startDate = form.querySelector('input[name="start_date"]');
            const endDate = form.querySelector('input[name="end_date"]');
            const searchInput = form.querySelector('input[name="search"]');
            let debounceTimer;

            const debounceSubmit = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => form.submit(), 300);
            };

            if (startDate) startDate.addEventListener('change', debounceSubmit);
            if (endDate) endDate.addEventListener('change', debounceSubmit);

            if (searchInput) {
                searchInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        clearTimeout(debounceTimer);
                        form.submit();
                    }
                });
            }
        });
    </script>
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Expense Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="newCategoryName" class="form-control" />
                        <div id="newCategoryError" class="text-danger small mt-2 d-none"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="saveNewCategory" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const openLinks = document.querySelectorAll('.openAddCategory');
            const addModalEl = document.getElementById('addCategoryModal');
            const bsAddModal = addModalEl ? new bootstrap.Modal(addModalEl) : null;
            const saveBtn = document.getElementById('saveNewCategory');
            const nameInput = document.getElementById('newCategoryName');
            const errorDiv = document.getElementById('newCategoryError');
            const categorySelect = document.querySelector('select[name="category"]');

            if (openLinks.length > 0 && bsAddModal) {
                openLinks.forEach(function(openLink) {
                    openLink.addEventListener('click', function (e) {
                        e.preventDefault();
                        nameInput.value = '';
                        errorDiv.classList.add('d-none');
                        bsAddModal.show();
                        setTimeout(() => nameInput.focus(), 200);
                    });
                });
            }

            if (saveBtn) {
                saveBtn.addEventListener('click', function () {
                    const name = nameInput.value.trim();
                    if (!name) {
                        errorDiv.textContent = 'Name is required';
                        errorDiv.classList.remove('d-none');
                        return;
                    }
                    saveBtn.disabled = true;
                    fetch('add_expense_category.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'name=' + encodeURIComponent(name)
                    }).then(r => r.json()).then(function (data) {
                        saveBtn.disabled = false;
                        if (data.success) {
                            // add new option to select and select it
                            if (categorySelect) {
                                const opt = document.createElement('option');
                                opt.value = data.name;
                                opt.textContent = data.name;
                                opt.selected = true;
                                categorySelect.appendChild(opt);
                            }
                            bsAddModal.hide();
                        } else {
                            errorDiv.textContent = data.message || 'Save failed';
                            errorDiv.classList.remove('d-none');
                        }
                    }).catch(function (err) {
                        saveBtn.disabled = false;
                        errorDiv.textContent = 'Request failed';
                        errorDiv.classList.remove('d-none');
                    });
                });
            }
        })();
    </script>
</body>
</html>