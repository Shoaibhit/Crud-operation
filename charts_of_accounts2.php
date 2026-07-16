<?php
require 'connection.php';

$result = mysqli_query($conn,"SELECT COUNT(*) AS total FROM accounts");
$data = mysqli_fetch_assoc($result);

$totalAccounts = $data['total'];

$result = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM accounts
WHERE account_type='Asset'
");

$data = mysqli_fetch_assoc($result);

$totalAssets = $data['total'];

$result = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM accounts
WHERE account_type='Liability'
");

$data = mysqli_fetch_assoc($result);

$totalLiabilities = $data['total'];
$result = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM accounts
WHERE account_type='Equity'
");

$data = mysqli_fetch_assoc($result);

$totalEquity = $data['total'];


$query = mysqli_query($conn,"
SELECT *
FROM accounts
ORDER BY account_code ASC
");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charts of Accounts</title>
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
            <main class="col-12 col-md-9 col-xl-9 p-4">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
              <h1 class="h3 mb-1">Charts Of Accounts</h1>
              <p class="text-muted mb-0">
                Track All runing accounts data
              </p>
            </div>
            <div>
              <button type="button"
        id="openAddAccountBtn"
        class="btn btn-outline-secondary btn-sm rounded-pill ms-2">
    <i class="bi bi-folder-plus me-2"></i>
    Add Account
</button>
            </div>
          </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-lg-3">
    <div class="card page-card border-0 p-4 bg-white h-100">
        <div class="d-flex justify-content-between align-items-start">

            <div>
                <p class="text-uppercase text-muted small mb-2">
                    Total Accounts
                </p>

                <h2 class="fw-bold mb-2"><?php echo $totalAccounts;?></h2>

                <p class="text-muted mb-0">
                    All accounts in chart
                </p>
            </div>

            <div class="d-flex align-items-center justify-content-center rounded-3"
                 style="width:60px;height:60px;background:#0d6efd;">
                <i class="bi bi-grid text-white fs-3"></i>
            </div>

        </div>
    </div>
</div>
                   <div class="col-12 col-lg-3">
    <div class="card page-card border-0 p-4 bg-white h-100">
        <div class="d-flex justify-content-between align-items-start">

            <div>
                <p class="text-uppercase text-muted small mb-2">
                    Assets
                </p>

                <h2 class="fw-bold mb-2">
                    <?php echo $totalAssets;?>
                </h2>

                <p class="text-muted mb-0">
                    Total assets accounts
                </p>
            </div>

            <div class="d-flex align-items-center justify-content-center rounded-3"
                 style="width:60px;height:60px;background:#fd7e14;">
                <i class="bi bi-wallet2 text-white fs-3"></i>
            </div>

        </div>
    </div>
</div>
                    <div class="col-12 col-lg-3">
    <div class="card page-card border-0 p-4 bg-white h-100">
        <div class="d-flex justify-content-between align-items-start">

            <div>
                <p class="text-uppercase text-muted small mb-2">
                    LIABILTIES
                </p>

                <h2 class="fw-bold mb-2">
                   <?php echo $totalLiabilities;?>
                </h2>

                <p class="text-muted mb-0">
                    Total liabilties accounts
                </p>
            </div>

            <div class="d-flex align-items-center justify-content-center rounded-3"
                 style="width:60px;height:60px;background:#0dcaf0;">
                <i class="bi bi-credit-card text-white fs-3"></i>
            </div>

        </div>
    </div>
</div>
                    <div class="col-12 col-lg-3">
    <div class="card page-card border-0 p-4 bg-white h-100">
        <div class="d-flex justify-content-between align-items-start">

            <div>
                <p class="text-uppercase text-muted small mb-2">
                    LIABILTIES
                </p>

                <h2 class="fw-bold mb-2">
                   <?php echo $totalEquity;?>
                </h2>

                <p class="text-muted mb-0">
                    Total liabilties accounts
                </p>
            </div>

            <div class="d-flex align-items-center justify-content-center rounded-3"
                 style="width:60px;height:60px;background:#0dcaf0;">
                <i class="bi bi-credit-card text-white fs-3"></i>
            </div>

        </div>
    </div>
</div>
</div>

    <div class="card page-card bg-white border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-1">All accounts details</h2>
            <p class="text-muted mb-0">Summary of all accounts.</p>
        </div>

       
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
               <tr>
    <th>#</th>
    <th>Account Code</th>
    <th>Account Name</th>
    <th>Account Type</th>
    <th>Actions</th>
</tr>
            </thead>
<tbody>

<?php
$i=1;

while($row=mysqli_fetch_assoc($query)){
?>

<tr>

    <td><?= $i++; ?></td>

    <td><?= htmlspecialchars($row['account_code']); ?></td>

    <td><?= htmlspecialchars($row['name']); ?></td>

    <td><?= htmlspecialchars($row['account_type']); ?></td>

    <td>
        <button type="button"
                class="btn btn-sm btn-primary edit-account-btn"
                data-id="<?= $row['id']; ?>"
                data-code="<?= htmlspecialchars($row['account_code'], ENT_QUOTES); ?>"
                data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES); ?>"
                data-type="<?= htmlspecialchars($row['account_type'], ENT_QUOTES); ?>">
            <i class="bi bi-pencil"></i>
        </button>

        <a href="delete_account.php?id=<?= $row['id']; ?>"
           class="btn btn-sm btn-danger"
           onclick="return confirm('Delete this account?')">
            <i class="bi bi-trash"></i>
        </a>
    </td>

</tr>

<?php } ?>

</tbody>
            

        </table>
    </div>
</div>            
            </main>
        </div>
    </div>


    <!-- add new account  -->
         <div class="modal fade" id="addAccountModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add Account</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Account Code</label>
              <input type="text" id="accountCode" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Account Name</label>
              <input type="text" id="accountName" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Account Type</label>
              <select id="accountType" class="form-select">
                <option value="">Select type</option>
                <option value="Asset">Asset</option>
                <option value="Liability">Liability</option>
                <option value="Equity">Equity</option>
                <option value="Income">Income</option>
                <option value="Expense">Expense</option>
              </select>
            </div>
            <div id="addAccountError" class="text-danger small mt-2 d-none"></div>
          </div>
          <div class="modal-footer">
            <button type="button" id="saveNewAccount" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- edit account -->
    <div class="modal fade" id="editAccountModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Account</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="editAccountId" />
            <div class="mb-3">
              <label class="form-label">Account Code</label>
              <input type="text" id="editAccountCode" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Account Name</label>
              <input type="text" id="editAccountName" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">Account Type</label>
              <select id="editAccountType" class="form-select">
                <option value="Asset">Asset</option>
                <option value="Liability">Liability</option>
                <option value="Equity">Equity</option>
                <option value="Income">Income</option>
                <option value="Expense">Expense</option>
              </select>
            </div>
            <div id="editAccountError" class="text-danger small mt-2 d-none"></div>
          </div>
          <div class="modal-footer">
            <button type="button" id="updateAccount" class="btn btn-primary">Update</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
      (function () {
        const openBtn = document.getElementById('openAddAccountBtn');
        const addModalEl = document.getElementById('addAccountModal');
        const bsAddModal = addModalEl ? new bootstrap.Modal(addModalEl) : null;
        const saveBtn = document.getElementById('saveNewAccount');
        const codeInput = document.getElementById('accountCode');
        const nameInput = document.getElementById('accountName');
        const typeSelect = document.getElementById('accountType');
        const errorDiv = document.getElementById('addAccountError');

        if (openBtn && bsAddModal) {
          openBtn.addEventListener('click', function (e) {
            e.preventDefault();
            codeInput.value = '';
            nameInput.value = '';
            typeSelect.value = '';
            errorDiv.classList.add('d-none');
            bsAddModal.show();
            setTimeout(() => codeInput.focus(), 200);
          });
        }

        if (saveBtn) {
          saveBtn.addEventListener('click', function () {
            const code = codeInput.value.trim();
            const name = nameInput.value.trim();
            const type = typeSelect.value;
            if (!code || !name || !type) {
              errorDiv.textContent = 'Account Code, Name and Type are required';
              errorDiv.classList.remove('d-none');
              return;
            }
            saveBtn.disabled = true;
            fetch('add_account.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'account_code=' + encodeURIComponent(code) + '&name=' + encodeURIComponent(name) + '&account_type=' + encodeURIComponent(type)
            }).then(r => r.json()).then(function (data) {
              saveBtn.disabled = false;
              if (data.success) {
                bsAddModal.hide();
                location.reload();
              } else {
                errorDiv.textContent = data.message || 'Save failed';
                errorDiv.classList.remove('d-none');
              }
            }).catch(function () {
              saveBtn.disabled = false;
              errorDiv.textContent = 'Request failed';
              errorDiv.classList.remove('d-none');
            });
          });
        }

        // Edit account
        const editModalEl = document.getElementById('editAccountModal');
        const bsEditModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
        const editId = document.getElementById('editAccountId');
        const editCode = document.getElementById('editAccountCode');
        const editName = document.getElementById('editAccountName');
        const editType = document.getElementById('editAccountType');
        const editError = document.getElementById('editAccountError');
        const updateBtn = document.getElementById('updateAccount');

        document.querySelectorAll('.edit-account-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            editId.value = btn.getAttribute('data-id');
            editCode.value = btn.getAttribute('data-code');
            editName.value = btn.getAttribute('data-name');
            editType.value = btn.getAttribute('data-type');
            editError.classList.add('d-none');
            if (bsEditModal) bsEditModal.show();
          });
        });

        if (updateBtn) {
          updateBtn.addEventListener('click', function () {
            const id = editId.value;
            const code = editCode.value.trim();
            const name = editName.value.trim();
            const type = editType.value;
            if (!code || !name || !type) {
              editError.textContent = 'Account Code, Name and Type are required';
              editError.classList.remove('d-none');
              return;
            }
            updateBtn.disabled = true;
            fetch('edit_account.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'id=' + encodeURIComponent(id) + '&account_code=' + encodeURIComponent(code) + '&name=' + encodeURIComponent(name) + '&account_type=' + encodeURIComponent(type)
            }).then(r => r.json()).then(function (data) {
              updateBtn.disabled = false;
              if (data.success) {
                bsEditModal.hide();
                location.reload();
              } else {
                editError.textContent = data.message || 'Update failed';
                editError.classList.remove('d-none');
              }
            }).catch(function () {
              updateBtn.disabled = false;
              editError.textContent = 'Request failed';
              editError.classList.remove('d-none');
            });
          });
        }
      })();
    </script>
</body>
</html>