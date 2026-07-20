
<?php
require 'auth.php';
include 'connection.php';

$successMessage = '';
$errorMessage = '';
$errors = [
    'loan_type' => '',
    'person_name' => '',
    'loan_amount' => '',
    'loan_date' => '',
];
$old = [
    'loan_type' => '',
    'person_name' => '',
    'loan_amount' => '',
    'loan_date' => '',
];

if (isset($_SESSION['successMessage'])) {
    $successMessage = $_SESSION['successMessage'];
    unset($_SESSION['successMessage']);
}

if (isset($_POST['save_loan'])) {
    $old['loan_type'] = trim($_POST['loan_type'] ?? '');
    $old['person_name'] = trim($_POST['person_name'] ?? '');
    $old['loan_amount'] = trim($_POST['loan_amount'] ?? '');
    $old['loan_date'] = trim($_POST['loan_date'] ?? '');
    $entryType = (!empty($_POST['is_repay']) && $_POST['is_repay'] === '1') ? 'repayment' : 'loan';

    if ($old['loan_type'] === '') {
        $errors['loan_type'] = 'Please select debtor or creditor.';
    }
    if ($old['person_name'] === '') {
        $errors['person_name'] = 'Person name is required.';
    }
    if ($old['loan_amount'] === '' || !is_numeric($old['loan_amount'])) {
        $errors['loan_amount'] = 'Valid amount is required.';
    }
    if ($old['loan_date'] === '') {
        $errors['loan_date'] = 'Date is required.';
    }

    if (!array_filter($errors)) {
        $query = "INSERT INTO loan_entries (entry_type, loan_type, person_name, loan_amount, loan_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            $loanAmount = (float) $old['loan_amount'];
            mysqli_stmt_bind_param(
                $stmt,
                "sssds",
                $entryType,
                $old['loan_type'],
                $old['person_name'],
                $loanAmount,
                $old['loan_date']
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

$investorNames = [];
$investorQuery = "SELECT name, type FROM investors ORDER BY type ASC, name ASC";
$investorResult = mysqli_query($conn, $investorQuery);
if ($investorResult) {
    while ($investorRow = mysqli_fetch_assoc($investorResult)) {
        $investorNames[] = [
            'name' => $investorRow['name'],
            'type' => $investorRow['type']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Loans</title>
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
            <main class="col-12 col-md-9 col-xl-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1" id="formTitle">Loan Entry</h1>
                        <p class="text-muted mb-0" id="formSubtitle">Add a new loan record quickly.</p>
                    </div>
                </div>
                <div class="card form-card bg-white border-0 p-4">
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
                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="repayToggle" name="is_repay" value="1" />
                                    <label class="form-check-label fw-semibold" for="repayToggle">Repay</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="loanType" class="form-label">Select Type</label>
                                <select id="loanType" name="loan_type_display" class="form-select form-select-lg rounded-4" disabled>
                                    <option value="">Select type</option>
                                    <option value="Debtor"<?php echo $old['loan_type'] === 'Debtor' ? ' selected' : ''; ?>>Debtor</option>
                                    <option value="Creditor"<?php echo $old['loan_type'] === 'Creditor' ? ' selected' : ''; ?>>Creditor</option>
                                </select>
                                <input type="hidden" name="loan_type" id="loanTypeValue" value="<?php echo htmlspecialchars($old['loan_type']); ?>" />
                                <?php if ($errors['loan_type']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['loan_type']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="personName" id="personNameLabel" class="form-label">Person Name</label>
                                <select id="personName" name="person_name" class="form-select form-select-lg rounded-4">
                                    <option value="">Select name</option>
                                    <?php foreach ($investorNames as $person): ?>
                                        <option value="<?php echo htmlspecialchars($person['name']); ?>" data-type="<?php echo htmlspecialchars($person['type']); ?>"<?php echo $old['person_name'] === $person['name'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($person['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($errors['person_name']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['person_name']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="loanAmount" class="form-label">Amount</label>
                                <input type="number" id="loanAmount" name="loan_amount" class="form-control form-control-lg rounded-4" min="0" step="0.01" value="<?php echo htmlspecialchars($old['loan_amount']); ?>" />
                                <?php if ($errors['loan_amount']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['loan_amount']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="loanDate" class="form-label">Date</label>
                                <input type="date" id="loanDate" name="loan_date" class="form-control form-control-lg rounded-4" value="<?php echo htmlspecialchars($old['loan_date']); ?>" />
                                <?php if ($errors['loan_date']): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars($errors['loan_date']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5" name="save_loan" id="submitButton">Save Loan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alert .btn-close').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    const alertBox = button.closest('.alert');
                    if (alertBox) {
                        alertBox.classList.remove('show');
                        alertBox.classList.add('d-none');
                    }
                });
            });

            const loanType = document.getElementById('loanType');
            const loanTypeValue = document.getElementById('loanTypeValue');
            const personNameLabel = document.getElementById('personNameLabel');
            const personNameSelect = document.getElementById('personName');
            const repayToggle = document.getElementById('repayToggle');
            const formTitle = document.getElementById('formTitle');
            const formSubtitle = document.getElementById('formSubtitle');
            const submitButton = document.getElementById('submitButton');

            function syncLoanTypeValue() {
                if (loanTypeValue && loanType) {
                    loanTypeValue.value = loanType.value;
                }
            }

            function updateLabel() {
                if (!loanType || !personNameLabel) return;
                if (loanType.value === 'Debtor') {
                    personNameLabel.textContent = 'Debtor Name';
                } else if (loanType.value === 'Creditor') {
                    personNameLabel.textContent = 'Creditor Name';
                } else {
                    personNameLabel.textContent = 'Person Name';
                }
            }

            function updateOptions() {
                if (!loanType || !personNameSelect) return;
                const selectedType = loanType.value;
                const currentValue = personNameSelect.value;

                Array.from(personNameSelect.options).forEach(function (option) {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    const optionType = option.getAttribute('data-type');
                    const matches = !selectedType || optionType === selectedType;
                    option.hidden = !matches;
                    option.disabled = !matches;
                });

                const matchingOption = Array.from(personNameSelect.options).find(function (option) {
                    return option.value === currentValue && !option.hidden;
                });

                if (matchingOption) {
                    matchingOption.selected = true;
                } else {
                    personNameSelect.value = '';
                }
            }

            function updateMode() {
                if (!repayToggle || !formTitle || !formSubtitle || !submitButton || !loanType) return;

                if (repayToggle.checked) {
                    loanType.value = 'Debtor';
                    formTitle.textContent = 'Repay Entry';
                    formSubtitle.textContent = 'Record a repayment quickly.';
                    submitButton.textContent = 'Save Repayment';
                } else {
                    loanType.value = 'Creditor';
                    formTitle.textContent = 'Loan Entry';
                    formSubtitle.textContent = 'Add a new loan record quickly.';
                    submitButton.textContent = 'Save Loan';
                }

                syncLoanTypeValue();
                updateLabel();
                updateOptions();
            }

            if (loanType) {
                loanType.addEventListener('change', function () {
                    syncLoanTypeValue();
                    updateLabel();
                    updateOptions();
                });
                syncLoanTypeValue();
                updateLabel();
                updateOptions();
            }

            if (repayToggle) {
                repayToggle.addEventListener('change', updateMode);
                updateMode();
            }
        });
    </script>
</body>
</html>
