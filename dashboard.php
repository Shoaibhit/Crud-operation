<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "user");

$insertErrors = ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];
$old = ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];
$showInsertModal = false;

if(isset($_POST['add_product'])){
    $old['name'] = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['address'] = trim($_POST['address'] ?? '');

    if($old['name'] === ''){
        $insertErrors['name'] = 'Name is required.';
    }
    if($old['email'] === ''){
        $insertErrors['email'] = 'Email is required.';
    }
    if($old['phone'] === ''){
        $insertErrors['phone'] = 'Phone number is required.';
    }
    if($old['address'] === ''){
        $insertErrors['address'] = 'Address is required.';
    }

    if(!array_filter($insertErrors)){
        $selectQuery = "SELECT * FROM products WHERE email = ?";
        $stmt = mysqli_prepare($conn, $selectQuery);
        mysqli_stmt_bind_param($stmt, "s", $old['email']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) > 0){
            $insertErrors['email'] = 'Product with this email already exists.';
            $showInsertModal = true;
        } else {
            $query = "INSERT INTO products (name, email, phone, address) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $old['name'], $old['email'], $old['phone'], $old['address']);
            mysqli_stmt_execute($stmt);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
        $showInsertModal = true;
    }
}

if(isset($_POST['update_product'])){
    $updateId = intval($_POST['update_id'] ?? 0);
    $updateName = trim($_POST['update_name'] ?? '');
    $updateEmail = trim($_POST['update_email'] ?? '');
    $updatePhone = trim($_POST['update_phone'] ?? '');
    $updateAddress = trim($_POST['update_address'] ?? '');

    if($updateId > 0){
        $query = "UPDATE products SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $updateName, $updateEmail, $updatePhone, $updateAddress, $updateId);
        mysqli_stmt_execute($stmt);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if(isset($_POST['delete_product'])){
    $deleteId = intval($_POST['delete_product']);
    if($deleteId > 0){
        $query = "DELETE FROM products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $deleteId);
        mysqli_stmt_execute($stmt);
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bootstrap demo</title>
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
  </head>
  <body
    style="
      background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
      min-height: 100vh;
    "
  >
    <div
      class="container m-auto mt-5 p-5 bg-white rounded shadow"
      style="max-width: 900px"
    >
      <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="fw-bold">Product details</span>
        <button
          id="insertBtn"
          class="btn btn-sm btn-success"
          data-bs-toggle="modal"
          data-bs-target="#exampleModal"
          data-bs-whatever="@mdo"
        >
          <i class="bi bi-plus-circle"></i> Insert product
        </button>
      </div>
      <table class="table table-hover">
        <thead class="bg-light text-dark border border-2 border-dark">
          <tr>
            <th class="border border-2 border-dark">NAME</th>
            <th class="border border-2 border-dark">EMAIL</th>
            <th class="border border-2 border-dark">PHONE</th>
            <th class="border border-2 border-dark">ADDRESS</th>
            <th class="border border-2 border-dark">ACTION</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $selectQuery = "SELECT * FROM products"; 
            $result = mysqli_query($conn, $selectQuery);
          while($row = mysqli_fetch_assoc($result)){ ?>
          <tr>
            <td class="border border-2 border-dark">
              <?php echo $row['name']; ?>
            </td>
            <td class="border border-2 border-dark">
              <?php echo $row['email']; ?>
            </td>
            <td class="border border-2 border-dark">
              <?php echo $row['phone']; ?>
            </td>
            <td class="border border-2 border-dark">
              <?php echo $row['address']; ?>
            </td>
            <td class="border border-2 border-dark">
              <button
                class="btn btn-sm btn-primary updateBtn"
                data-bs-toggle="modal"
                data-bs-target="#updateModal"
                data-id="<?php echo $row['id']; ?>"
                data-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>"
                data-email="<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>"
                data-phone="<?php echo htmlspecialchars($row['phone'], ENT_QUOTES); ?>"
                data-address="<?php echo htmlspecialchars($row['address'], ENT_QUOTES); ?>"
              >
                <i class="bi bi-pencil-square"></i>
              </button>
              <form method="POST" style="display:inline;">
                <button type="submit" class="btn btn-sm btn-danger" name="delete_product" value="<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">
                  <i class="bi bi-trash fw-bold"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <!-- insert product modal -->

   <form action="" method="POST" id="insertForm" novalidate>
        <div
      class="modal fade"
      id="exampleModal"
      tabindex="-1"
      aria-labelledby="exampleModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">
              Add Product details
            </h1>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                <label for="product-name" class="col-form-label"
                  >Product Name:</label
                >
                <input
                  type="text"
                  class="form-control"
                  id="product-name"
                  name="name"
                  value="<?php echo htmlspecialchars($old['name']); ?>"
                />
                <span id="nameError" class="text-danger" style="font-size: 12px;"><?php echo $insertErrors['name']; ?></span>
              </div>
              <div class="mb-3">
                <label for="product-email" class="col-form-label">EMAIL:</label>
                <input
                  type="email"
                  class="form-control"
                  id="product-email"
                  name="email"
                  value="<?php echo htmlspecialchars($old['email']); ?>"
                />
                <span id="emailError" class="text-danger" style="font-size: 12px;"><?php echo $insertErrors['email']; ?></span>
              </div>
              <div class="mb-3">
                <label for="product-phone" class="col-form-label">PHONE:</label>
                <input
                  type="text"
                  class="form-control"
                  id="product-phone"
                  name="phone"
                  value="<?php echo htmlspecialchars($old['phone']); ?>"
                />
                <span id="phoneError" class="text-danger" style="font-size: 12px;"><?php echo $insertErrors['phone']; ?></span>
              </div>
              <div class="mb-3">
                <label for="product-address" class="col-form-label"
                  >ADDRESS:</label
                >
                <input
                  type="text"
                  class="form-control"
                  id="product-address"
                  name="address"
                  value="<?php echo htmlspecialchars($old['address']); ?>"
                />
                <span id="addressError" class="text-danger" style="font-size: 12px;"><?php echo $insertErrors['address']; ?></span>
              </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
            <button type="submit" class="btn btn-primary" name="add_product">
              Add Product
            </button>
          </div>
        </div>
      </div>
    </div>
   </form>

    <!-- update product modal -->
   <form action="" method="POST" id="updateForm" novalidate>
    <div  class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="updateModalLabel">Update Product details</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="update-product-id" name="update_id" />
            <div class="mb-3">
              <label for="update-product-name" class="col-form-label">Product Name:</label>
              <input type="text" class="form-control" id="update-product-name" name="update_name" />
            </div>
            <div class="mb-3">
              <label for="update-product-email" class="col-form-label">EMAIL:</label>
              <input type="email" class="form-control" id="update-product-email" name="update_email" />
            </div>
            <div class="mb-3">
              <label for="update-product-phone" class="col-form-label">PHONE:</label>
              <input type="text" class="form-control" id="update-product-phone" name="update_phone" />
            </div>
            <div class="mb-3">
              <label for="update-product-address" class="col-form-label">ADDRESS:</label>
              <input type="text" class="form-control" id="update-product-address" name="update_address" />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              Close
            </button>
            <button type="submit" class="btn btn-primary" name="update_product">
              Update Product
            </button>
          </div>
        </div>
      </div>
    </div>
   </form>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var updateModalEl = document.getElementById('updateModal');
        if (updateModalEl) {
          updateModalEl.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var email = button.getAttribute('data-email');
            var phone = button.getAttribute('data-phone');
            var address = button.getAttribute('data-address');

            document.getElementById('update-product-id').value = id;
            document.getElementById('update-product-name').value = name;
            document.getElementById('update-product-email').value = email;
            document.getElementById('update-product-phone').value = phone;
            document.getElementById('update-product-address').value = address;
          });
        }

        var updateButtons = document.querySelectorAll('.updateBtn');
        updateButtons.forEach(function(button) {
          button.addEventListener('click', function () {
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var email = button.getAttribute('data-email');
            var phone = button.getAttribute('data-phone');
            var address = button.getAttribute('data-address');

            document.getElementById('update-product-id').value = id;
            document.getElementById('update-product-name').value = name;
            document.getElementById('update-product-email').value = email;
            document.getElementById('update-product-phone').value = phone;
            document.getElementById('update-product-address').value = address;
          });
        });

        const inputname = document.getElementById('product-name');
        const nameError = document.getElementById('nameError');
        const emailError = document.getElementById('emailError');
        const phoneError = document.getElementById('phoneError');
        const addressError = document.getElementById('addressError');
        const inputemail = document.getElementById('product-email');
        const inputphone = document.getElementById('product-phone');
        const inputaddress = document.getElementById('product-address');
        const formInsert = document.getElementById('insertForm');

        if (formInsert) {
          formInsert.addEventListener('submit', function (event) {
            let hasError = false;

            if (inputname.value.trim() === '') {
              hasError = true;
              nameError.textContent = 'Name is required.';
            } else {
              nameError.textContent = '';
            }

            if (inputemail.value.trim() === '') {
              hasError = true;
              emailError.textContent = 'Email is required.';
            } else {
              emailError.textContent = '';
            }

            if (inputphone.value.trim() === '') {
              hasError = true;
              phoneError.textContent = 'Phone number is required.';
            } else {
              phoneError.textContent = '';
            }

            if (inputaddress.value.trim() === '') {
              hasError = true;
              addressError.textContent = 'Address is required.';
            } else {
              addressError.textContent = '';
            }

            if (hasError) {
              event.preventDefault();
            }
          });
        }
      });
    </script>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
      crossorigin="anonymous"
    ></script>
     <?php if($showInsertModal): ?>
     <script>
       document.addEventListener('DOMContentLoaded', function(){
         var insertModalEl = document.getElementById('exampleModal');
         var insertModal = new bootstrap.Modal(insertModalEl);
         insertModal.show();
       });
     </script>
     <?php endif; ?>
