<?php
session_start();
include 'connection.php';

$reference_no = sprintf('%06d', random_int(0, 999999));
$successMessage = '';
$errorMessage = '';
$errors = [
    'income_date' => '',
    'income_amount' => '',
    'income_category' => '',
    'income_payment_method' => '',
    'income_notes' => '',
];
$old = [
    'income_date' => '',
    'income_amount' => '',
    'income_category' => '',
    'income_payment_method' => '',
    'income_notes' => '',
];

if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}

$incomeCategories = [];
$catQuery = "SELECT name FROM income_categories ORDER BY name ASC";
$catResult = mysqli_query($conn, $catQuery);
if ($catResult) {
    while ($catRow = mysqli_fetch_assoc($catResult)) {
        $incomeCategories[] = $catRow['name'];
    }
}

if(isset($_POST['save_income'])) {
    $old['income_date'] = trim($_POST['income_date'] ?? '');
    $old['income_amount'] = trim($_POST['income_amount'] ?? '');
    $old['income_category'] = trim($_POST['income_category'] ?? '');
    $old['income_payment_method'] = trim($_POST['income_payment_method'] ?? '');
    $old['income_notes'] = trim($_POST['income_notes'] ?? '');
    $reference_no = sprintf('%06d', random_int(0, 999999));

    if ($old['income_date'] === '') {
        $errors['income_date'] = 'Date is required.';
    }
    if ($old['income_amount'] === '') {
        $errors['income_amount'] = 'Amount is required.';
    }
    if ($old['income_category'] === '') {
        $errors['income_category'] = 'Category is required.';
    }
    if ($old['income_payment_method'] === '') {
        $errors['income_payment_method'] = 'Payment method is required.';
    }
    if ($old['income_notes'] === '') {
        $errors['income_notes'] = 'Notes are required.';
    }

    if (!array_filter($errors)) {
        $query = "INSERT INTO income (date, amount, income_category, payment_method, notes, reference_no) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sdssss", $old['income_date'], $old['income_amount'], $old['income_category'], $old['income_payment_method'], $old['income_notes'], $reference_no);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['successMessage'] = 'Income record saved successfully.';
                header('Location: income_form.php');
                exit;
            } else {
                $errorMessage = 'Insert failed: ' . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessage = 'Database error: ' . mysqli_error($conn);
        }
    }
}
?>














<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Income</title>
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
        .sidebar a:hover, .sidebar a.active {
            color: #ffffff;
            background: rgba(255,255,255,0.08);
        }
        .sidebar .nav-link {
            padding: 0.85rem 1rem;
            border-radius: 0.6rem;
        }
        .form-card {
            border-radius: 1.2rem;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }
        .form-label {
            font-weight: 600;
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
                        <h1 class="h3 mb-1">Income Entry</h1>
                        <p class="text-muted mb-0">Add a new income record with category, amount, and notes.</p>
                    </div>
                </div>
                <div class="card form-card bg-white border-0 p-4">
                   <div class="mb-4 d-flex justify-content-between align-items-center">
    <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">
        <i class="bi bi-plus-circle me-1"></i> New Income
    </span>

    <a href="all_income_reports.php" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
        <i class="bi bi-list-ul me-2"></i> View All Incomes
    </a>
</div>
                    <?php if(!empty($successMessage)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($successMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if(!empty($errorMessage)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($errorMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="incomeDate" class="form-label">Date</label>
                                <input type="date" id="incomeDate" name="income_date" class="form-control form-control-lg rounded-4" value="<?php echo htmlspecialchars($old['income_date']); ?>"  />
                                <?php if($errors['income_date']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['income_date']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="incomeAmount" class="form-label">Amount</label>
                                <div class="input-group input-group-lg rounded-4 overflow-hidden shadow-sm">
                                    <input type="number" id="incomeAmount" name="income_amount" class="form-control form-control-lg  rounded-4" placeholder="0.00" min="0" step="0.01" value="<?php echo htmlspecialchars($old['income_amount']); ?>" />
                                </div>
                                <?php if($errors['income_amount']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['income_amount']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="incomeCategory" class="form-label">Category</label>
                                <select id="incomeCategory" name="income_category" class="form-select form-select-lg rounded-4">
                                    <option value="">Select category</option>
                                    <?php foreach ($incomeCategories as $categoryOption): ?>
                                        <option value="<?php echo htmlspecialchars($categoryOption); ?>"<?php echo $old['income_category'] === $categoryOption ? ' selected' : ''; ?>><?php echo htmlspecialchars($categoryOption); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if($errors['income_category']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['income_category']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="incomePaymentMethod" class="form-label">Payment Method</label>
                                <select id="incomePaymentMethod" name="income_payment_method" class="form-select form-select-lg rounded-4" >
                                    <option value="">Select payment method</option>
                                    <option value="Cash"<?php echo $old['income_payment_method'] === 'Cash' ? ' selected' : ''; ?>>Cash</option>
                                    <option value="Bank Transfer"<?php echo $old['income_payment_method'] === 'Bank Transfer' ? ' selected' : ''; ?>>Bank Transfer</option>
                                    <option value="Credit Card"<?php echo $old['income_payment_method'] === 'Credit Card' ? ' selected' : ''; ?>>Credit Card</option>
                                    <option value="Debit Card"<?php echo $old['income_payment_method'] === 'Debit Card' ? ' selected' : ''; ?>>Debit Card</option>
                                    <option value="Other"<?php echo $old['income_payment_method'] === 'Other' ? ' selected' : ''; ?>>Other</option>
                                </select>
                                <?php if($errors['income_payment_method']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['income_payment_method']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label for="incomeNotes" class="form-label">Notes</label>
                                <textarea id="incomeNotes" name="income_notes" class="form-control rounded-4" rows="4" placeholder="Add any important details..."><?php echo htmlspecialchars($old['income_notes']); ?></textarea>
                                <?php if($errors['income_notes']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['income_notes']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5" name="save_income">Save Income</button>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
