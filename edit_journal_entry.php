<?php
require 'auth.php';
include 'connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: all_journal_entries.php');
    exit;
}

$errors = [
    'entry_date' => '',
    'debit_account' => '',
    'credit_account' => '',
    'amount' => '',
    'description' => '',
];

$incomeAccounts = [];
$expenseAccounts = [];
$staticAccounts = [
    'Cash',
    'Bank',
    'Accounts Receivable',
    'Accounts Payable',
    'Capital',
    'Sales',
    'Purchases',
    'Miscellaneous',
];

$incomeQuery = "SELECT name FROM income_categories ORDER BY name ASC";
$incomeResult = mysqli_query($conn, $incomeQuery);
if ($incomeResult) {
    while ($row = mysqli_fetch_assoc($incomeResult)) {
        $incomeAccounts[] = $row['name'];
    }
}

$expenseQuery = "SELECT name FROM expense_categories ORDER BY name ASC";
$expenseResult = mysqli_query($conn, $expenseQuery);
if ($expenseResult) {
    while ($row = mysqli_fetch_assoc($expenseResult)) {
        $expenseAccounts[] = $row['name'];
    }
}

$accountOptions = array_unique(array_merge($staticAccounts, $incomeAccounts, $expenseAccounts));
sort($accountOptions, SORT_STRING);

$successMessage = '';
$errorMessage = '';

$entry = null;
$stmt = mysqli_prepare($conn, 'SELECT reference_no, entry_date, debit_account, credit_account, amount, description FROM journal_entries WHERE id = ? LIMIT 1');
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $entry = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

if (!$entry) {
    header('Location: all_journal_entries.php');
    exit;
}

$old = [
    'entry_date' => $entry['entry_date'],
    'debit_account' => $entry['debit_account'],
    'credit_account' => $entry['credit_account'],
    'amount' => $entry['amount'],
    'description' => $entry['description'],
];
$reference_no = $entry['reference_no'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['entry_date'] = trim($_POST['entry_date'] ?? '');
    $old['debit_account'] = trim($_POST['debit_account'] ?? '');
    $old['credit_account'] = trim($_POST['credit_account'] ?? '');
    $old['amount'] = trim($_POST['amount'] ?? '');
    $old['description'] = trim($_POST['description'] ?? '');

    if ($old['entry_date'] === '') {
        $errors['entry_date'] = 'Date is required.';
    }
    if ($old['debit_account'] === '') {
        $errors['debit_account'] = 'Debit account is required.';
    } elseif ($old['debit_account'] === '0' || !in_array($old['debit_account'], $accountOptions, true)) {
        $errors['debit_account'] = 'Select a valid debit account.';
    }
    if ($old['credit_account'] === '') {
        $errors['credit_account'] = 'Credit account is required.';
    } elseif ($old['credit_account'] === '0' || !in_array($old['credit_account'], $accountOptions, true)) {
        $errors['credit_account'] = 'Select a valid credit account.';
    }
    if ($old['debit_account'] !== '' && $old['credit_account'] !== '' && $old['debit_account'] === $old['credit_account']) {
        $errors['credit_account'] = 'Debit and credit accounts must be different.';
    }
    if ($old['amount'] === '' || !is_numeric($old['amount']) || (float) $old['amount'] <= 0) {
        $errors['amount'] = 'Valid amount is required.';
    }
    if ($old['description'] === '') {
        $errors['description'] = 'Description is required.';
    }

    if (!array_filter($errors)) {
        $query = 'UPDATE journal_entries SET entry_date = ?, debit_account = ?, credit_account = ?, amount = ?, description = ? WHERE id = ?';
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            $amountValue = (float) $old['amount'];
            mysqli_stmt_bind_param($stmt, 'sssdss', $old['entry_date'], $old['debit_account'], $old['credit_account'], $amountValue, $old['description'], $id);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = 'Journal entry updated successfully.';
                $entry['entry_date'] = $old['entry_date'];
                $entry['debit_account'] = $old['debit_account'];
                $entry['credit_account'] = $old['credit_account'];
                $entry['amount'] = $amountValue;
                $entry['description'] = $old['description'];
            } else {
                $errorMessage = 'Update failed: ' . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMessage = 'Database error: ' . mysqli_error($conn);
        }
    }
}

function html($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <style>
        body { min-height: 100vh; background: linear-gradient(135deg, #eef2ff 0%, #f8fbff 100%); }
        .sidebar { min-height: 100vh; background: #1f2937; color: #e5e7eb; }
        .sidebar a { color: #d1d5db; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { color: #ffffff; background: rgba(255,255,255,0.08); }
        .sidebar .nav-link { padding: 0.85rem 1rem; border-radius: 0.6rem; }
        .form-card { border-radius: 1.2rem; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08); }
        .form-label { font-weight: 600; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row gx-0">
            <?php include 'sidebar.php'; ?>
            <main class="col-12 col-md-9 col-xl-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Edit Journal Entry</h1>
                        <p class="text-muted mb-0">Correct missing credit account values or update journal details.</p>
                    </div>
                    <a href="all_journal_entries.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                        <i class="bi bi-list-ul me-2"></i> All Journal Entries
                    </a>
                </div>

                <div class="card form-card bg-white border-0 p-4">
                    <?php if ($successMessage): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo html($successMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo html($errorMessage); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="entryDate" class="form-label">Entry Date</label>
                                <input type="date" id="entryDate" name="entry_date" class="form-control form-control-lg rounded-4" value="<?php echo html($old['entry_date']); ?>" />
                                <?php if ($errors['entry_date']): ?><div class="text-danger small mt-1"><?php echo html($errors['entry_date']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="referenceNo" class="form-label">Reference No</label>
                                <input type="text" id="referenceNo" class="form-control form-control-lg rounded-4" value="<?php echo html($reference_no); ?>" readonly />
                            </div>
                            <div class="col-md-6">
                                <label for="debitAccount" class="form-label">Debit Account</label>
                                <select id="debitAccount" name="debit_account" class="form-select form-select-lg rounded-4">
                                    <option value="">Select debit account</option>
                                    <?php foreach ($accountOptions as $accountOption): ?>
                                        <option value="<?php echo html($accountOption); ?>" <?php echo $old['debit_account'] === $accountOption ? 'selected' : ''; ?>><?php echo html($accountOption); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($errors['debit_account']): ?><div class="text-danger small mt-1"><?php echo html($errors['debit_account']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="creditAccount" class="form-label">Credit Account</label>
                                <select id="creditAccount" name="credit_account" class="form-select form-select-lg rounded-4">
                                    <option value="">Select credit account</option>
                                    <?php foreach ($accountOptions as $accountOption): ?>
                                        <option value="<?php echo html($accountOption); ?>" <?php echo $old['credit_account'] === $accountOption ? 'selected' : ''; ?>><?php echo html($accountOption); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($errors['credit_account']): ?><div class="text-danger small mt-1"><?php echo html($errors['credit_account']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" id="amount" name="amount" class="form-control form-control-lg rounded-4" placeholder="0.00" min="0" step="0.01" value="<?php echo html($old['amount']); ?>" />
                                <?php if ($errors['amount']): ?><div class="text-danger small mt-1"><?php echo html($errors['amount']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" id="description" name="description" class="form-control form-control-lg rounded-4" placeholder="Describe the journal entry" value="<?php echo html($old['description']); ?>" />
                                <?php if ($errors['description']): ?><div class="text-danger small mt-1"><?php echo html($errors['description']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5">Update Entry</button>
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
