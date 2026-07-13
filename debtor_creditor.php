
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
    'payment_method' => '',
    'loan_notes' => '',
    'screenshot' => '',
];
$old = [
    'person_name' => '',
    'phone_no' => '',
    'cnic' => '',
    'adress' => '',
    'payment_method' => '',
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
    $old['payment_method'] = trim($_POST['payment_method'] ?? '');
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
    if ($old['payment_method'] === '') {
        $errors['payment_method'] = 'Type is required.';
    }
    if ($old['loan_notes'] === '') {
        $errors['loan_notes'] = 'Notes are required.';
    }


    if (!array_filter($errors)) {
        $query = "INSERT INTO investors (name, phone_no, cnic, address, type, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "ssssss",
                $old['person_name'],
                $old['phone_no'],
                $old['cnic'],
                $old['adress'],
                $old['payment_method'],
                $old['loan_notes'],
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION['successMessage'] = 'Record saved successfully.';
                header('Location: debtor_creditor.php');
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
    <title>Debtor / Creditor</title>
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
                        <h1 class="h3 mb-1">Debtor / Creditor Entry</h1>
                        <p class="text-muted mb-0">Add a new debtor / creditor record.</p>
                    </div>
                </div>
                <div class="card form-card bg-white border-0 p-4">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">
                            <i class="bi bi-plus-circle me-1"></i> Debtor / Creditor Entry
                        </span>
                        <a href="all_debtors_creditors.php" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-list-ul me-2"></i> View All Debtor / Creditor
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
                                <label for="paymentMethod" class="form-label">Select Type</label>
                                <select id="paymentMethod" name="payment_method" class="form-select form-select-lg rounded-4">
                                    <option value="">Select type</option>
                                    <option value="Debtor"<?php echo $old['payment_method'] === 'Debtor' ? ' selected' : ''; ?>>Debtor</option>
                                    <option value="Creditor"<?php echo $old['payment_method'] === 'Creditor' ? ' selected' : ''; ?>>Creditor</option>
                                </select>
                                <?php if ($errors['payment_method']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['payment_method']); ?></div><?php endif; ?>
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
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5" name="save_loan">Save Information</button>
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