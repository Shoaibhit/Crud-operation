
<?php
session_start();
include 'connection.php';

$successMessage = '';
$errorMessage = '';
$errors = [
    'person_name' => '',
    'phone_no' => '',
    'cnic' => '',
    'adress' => '',
    'loan_amount' => '',
    'loan_date' => '',
    'due_date' => '',
    'payment_method' => '',
    'installment_type' => '',
    'loan_notes' => '',
    'screenshot' => '',
];
$old = [
    'person_name' => '',
    'phone_no' => '',
    'cnic' => '',
    'adress' => '',
    'loan_amount' => '',
    'loan_date' => '',
    'due_date' => '',
    'payment_method' => '',
    'installment_type' => '',
    'loan_notes' => '',
];

if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}

if (isset($_POST['save_loan'])) {
    $old['person_name'] = trim($_POST['person_name'] ?? '');
    $old['phone_no'] = trim($_POST['phone_no'] ?? '');
    $old['cnic'] = trim($_POST['cnic'] ?? '');
    $old['adress'] = trim($_POST['adress'] ?? '');
    $old['loan_amount'] = trim($_POST['loan_amount'] ?? '');
    $old['loan_date'] = trim($_POST['loan_date'] ?? '');
    $old['due_date'] = trim($_POST['due_date'] ?? '');
    $old['payment_method'] = trim($_POST['payment_method'] ?? '');
    $old['installment_type'] = trim($_POST['installment_type'] ?? '');
    $old['loan_notes'] = trim($_POST['loan_notes'] ?? '');

    if ($old['person_name'] === '') {
        $errors['person_name'] = 'Person name is required.';
    }
    if ($old['phone_no'] === '') {
        $errors['phone_no'] = 'Phone number is required.';
    }
    if ($old['cnic'] === '') {
        $errors['cnic'] = 'CNIC is required.';
    }
    if ($old['adress'] === '') {
        $errors['adress'] = 'Address is required.';
    }
    if ($old['loan_amount'] === '' || !is_numeric($old['loan_amount'])) {
        $errors['loan_amount'] = 'Valid loan amount is required.';
    }
    if ($old['loan_date'] === '') {
        $errors['loan_date'] = 'Loan date is required.';
    }
    if ($old['due_date'] === '') {
        $errors['due_date'] = 'Due date is required.';
    }
    if ($old['payment_method'] === '') {
        $errors['payment_method'] = 'Payment method is required.';
    }
    if ($old['installment_type'] === '') {
        $errors['installment_type'] = 'Installment type is required.';
    }
    if ($old['loan_notes'] === '') {
        $errors['loan_notes'] = 'Notes are required.';
    }

    $screenshotPath = '';
    if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['screenshot']['error'] !== UPLOAD_ERR_OK) {
            $errors['screenshot'] = 'Screenshot upload failed.';
        } else {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($_FILES['screenshot']['type'], $allowedTypes, true)) {
                $errors['screenshot'] = 'Screenshot must be JPG, PNG, or GIF.';
            } elseif ($_FILES['screenshot']['size'] > 2 * 1024 * 1024) {
                $errors['screenshot'] = 'Screenshot file is too large.';
            } else {
                $uploadDir = __DIR__ . '/uploads/loans/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = uniqid('loan_', true) . '_' . basename($_FILES['screenshot']['name']);
                $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
                $screenshotPath = 'uploads/loans/' . $fileName;
                move_uploaded_file($_FILES['screenshot']['tmp_name'], $uploadDir . $fileName);
            }
        }
    }

    if (!array_filter($errors)) {
        $query = "INSERT INTO loans (person_name, phone_no, cnic, adress, loan_amount, loan_date, due_date, payment_method, installment_type, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssdsssss",
                $old['person_name'],
                $old['phone_no'],
                $old['cnic'],
                $old['adress'],
                $old['loan_amount'],
                $old['loan_date'],
                $old['due_date'],
                $old['payment_method'],
                $old['installment_type'],
                $old['loan_notes']
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['successMessage'] = 'Loan record saved successfully.';
                header('Location: loans.php');
                exit;
            } else {
                $errorMessage = 'Insert failed: ' . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
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
    <title>Loans Form</title>
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
                        <h1 class="h3 mb-1">Loan Entry</h1>
                        <p class="text-muted mb-0">Add a new loan record with borrower details and payment schedule.</p>
                    </div>
                </div>
                <div class="card form-card bg-white border-0 p-4">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">
                            <i class="bi bi-plus-circle me-1"></i> New Loan Entry
                        </span>
                        <a href="repay_loan.php" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-list-ul me-2"></i> Repay Loan
                        </a>
                    </div>
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
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="personName" class="form-label">Person Name</label>
                                <input type="text" id="personName" name="person_name" class="form-control form-control-lg rounded-4" value="<?php echo htmlspecialchars($old['person_name']); ?>" />
                                <?php if ($errors['person_name']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['person_name']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="phoneNo" class="form-label">Phone Number</label>
                                <input type="tel" id="phoneNo" name="phone_no" class="form-control form-control-lg rounded-4" value="<?php echo htmlspecialchars($old['phone_no']); ?>" />
                                <?php if ($errors['phone_no']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['phone_no']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="cnic" class="form-label">CNIC</label>
                                <input type="text" id="cnic" name="cnic" class="form-control form-control-lg rounded-4" value="<?php echo htmlspecialchars($old['cnic']); ?>" />
                                <?php if ($errors['cnic']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['cnic']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="loanAmount" class="form-label">Loan Amount</label>
                                <input type="number" id="loanAmount" name="loan_amount" class="form-control form-control-lg rounded-4" min="0" step="0.01" value="<?php echo htmlspecialchars($old['loan_amount']); ?>" />
                                <?php if ($errors['loan_amount']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['loan_amount']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="loanDate" class="form-label">Loan Date</label>
                                <input type="date" id="loanDate" name="loan_date" class="form-control form-control-lg rounded-4" value="<?php echo htmlspecialchars($old['loan_date']); ?>" />
                                <?php if ($errors['loan_date']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['loan_date']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="dueDate" class="form-label">Due Date</label>
                                <input type="date" id="dueDate" name="due_date" class="form-control form-control-lg rounded-4" value="<?php echo htmlspecialchars($old['due_date']); ?>" />
                                <?php if ($errors['due_date']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['due_date']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="paymentMethod" class="form-label">Payment Method</label>
                                <select id="paymentMethod" name="payment_method" class="form-select form-select-lg rounded-4">
                                    <option value="">Select payment method</option>
                                    <option value="Cash"<?php echo $old['payment_method'] === 'Cash' ? ' selected' : ''; ?>>Cash</option>
                                    <option value="Bank Transfer"<?php echo $old['payment_method'] === 'Bank Transfer' ? ' selected' : ''; ?>>Bank Transfer</option>
                                    <option value="Credit Card"<?php echo $old['payment_method'] === 'Credit Card' ? ' selected' : ''; ?>>Credit Card</option>
                                    <option value="Debit Card"<?php echo $old['payment_method'] === 'Debit Card' ? ' selected' : ''; ?>>Debit Card</option>
                                    <option value="Other"<?php echo $old['payment_method'] === 'Other' ? ' selected' : ''; ?>>Other</option>
                                </select>
                                <?php if ($errors['payment_method']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['payment_method']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="installmentType" class="form-label">Installment Type</label>
                                <select id="installmentType" name="installment_type" class="form-select form-select-lg rounded-4">
                                    <option value="">Select installment type</option>
                                    <option value="monthly"<?php echo $old['installment_type'] === 'monthly' ? ' selected' : ''; ?>>Monthly</option>
                                    <option value="half-year"<?php echo $old['installment_type'] === 'half-year' ? ' selected' : ''; ?>>Half-year</option>
                                </select>
                                <?php if ($errors['installment_type']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['installment_type']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label for="adress" class="form-label">Address</label>
                                <textarea id="adress" name="adress" class="form-control rounded-4" rows="3"><?php echo htmlspecialchars($old['adress']); ?></textarea>
                                <?php if ($errors['adress']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['adress']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label for="loanNotes" class="form-label">Notes</label>
                                <textarea id="loanNotes" name="loan_notes" class="form-control rounded-4" rows="4" placeholder="Add any important details..."><?php echo htmlspecialchars($old['loan_notes']); ?></textarea>
                                <?php if ($errors['loan_notes']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['loan_notes']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5" name="save_loan">Save Loan</button>
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