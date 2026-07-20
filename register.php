<?php
session_start();
$conn=mysqli_connect("localhost","root","","user") or die("Connection Failed");

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $address = $_POST['address'];
     $slect_query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $slect_query);
    $count = mysqli_num_rows($result);
    if($count>0){
      echo "<script>alert('Email already exists. Please use a different email.');</script>";
    } else {
      $insert_query = "INSERT INTO users (name, email, password, adress) VALUES ('$name', '$email', '$password_hash', '$address')";
      mysqli_query($conn, $insert_query);
      echo "<script>alert('Registration successful. You can now log in.');</script>";
    }
}



if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $slect_query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $slect_query);
    if($result && mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        if(password_verify($password, $row['password'])){
            $_SESSION['user_id'] = $row['id'];
            echo "<script>alert('Login successful.');
            window.location.href = 'dashboard.php';
            </script>";
            exit;
        } else {
            echo "<script>alert('Invalid email or password. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Invalid email or password. Please try again.');</script>";
    }
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
  </head>
  <body style="background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%); min-height: 100vh;">
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
      <div class="w-100" style="max-width: 480px;">
        <div class="border border-1 rounded-4 shadow-lg p-4 p-md-5 bg-white">
         <form id="registerFormWrapper" action="" method="POST" novalidate>
           <div id="registerForm">
            <h1 class="text-center mb-4 fw-bold">Register</h1>
            <div class="mb-3">
              <label for="fullName" class="form-label">Full Name</label>
              <input
                type="text"
                class="form-control rounded-3 shadow-none"
                id="fullName"
                placeholder="Enter your full name"
                name="name"
                required
              />
              <span id="nameError" class="text-danger" style="font-size: 12px;"></span>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input
                type="email"
                class="form-control rounded-3 shadow-none"
                id="email"
                placeholder="Enter your email"
                name="email"
                required
              />
              <span id="emailError" class="text-danger" style="font-size: 12px;"></span>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input
                type="password"
                class="form-control rounded-3 shadow-none"
                id="password"
                placeholder="Enter your password"
                name="password"
                required
              />
              <span id="passwordError" class="text-danger" style="font-size: 12px;"></span>
            </div>
            <div class="mb-3">
              <label for="address" class="form-label">Address</label>
              <input
                type="text"
                class="form-control rounded-3 shadow-none"
                id="address"
                placeholder="Enter your address"
                name="address"
                required
              />
              <span id="addressError" class="text-danger" style="font-size: 12px;"></span>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
              <div class="d-flex align-items-center">
                <input
                  type="checkbox"
                  class="form-check-input me-2"
                  id="termsCheckbox"
                  name="term"
                />
                <label for="termsCheckbox" class="form-label mb-0" style="font-size: 12px"
                  >Agree to Terms and Conditions</label
                >
              </div>
              <a href="#" id="showLogin" class="text-decoration-none ms-3" style="font-size: 12px"
                >Already have account?</a
              >
            </div>
            <div class="mt-4">
              <button
                type="submit"
                class="btin btn-primary w-100 rounded-3 fw-semibold"
                style="background-color: #0d6efd; border: none; padding: 10px 0; color:white;"
                name="register"
              >
                Register
              </button>
            </div>
          </div>
         </form>

        <form id="loginFormWrapper" action="" method="POST" class="form2" novalidate>
            <div id="loginForm" style="display: none;">
            <h1 class="text-center mb-4 fw-bold">Login</h1>
           <div class="mb-3">
              <label for="loginEmail" class="form-label">Email</label>
              <input
                type="email"
                class="form-control rounded-3 shadow-none"
                id="loginEmail"
                placeholder="Enter your email"
                name="email"
                required
              />
              <span id="loginEmailError" class="text-danger" style="font-size: 12px;"></span>
            </div>
             <div class="mb-3">
              <label for="loginPassword" class="form-label">Password</label>
              <input
                type="password"
                class="form-control rounded-3 shadow-none"
                id="loginPassword"
                placeholder="Enter your password"
                name="password"
                required
              />
              <span id="loginPasswordError" class="text-danger" style="font-size: 12px;"></span>
            </div>
            <div class="mt-4">
              <button
                type="submit"
                class="btn btn-primary w-100 rounded-3 fw-semibold"
                style="background-color: #0d6efd; border: none; padding: 10px 0;"
                name="login"
              >
                Login
              </button>
            <div class="text-center mt-3">
              <a href="forgot_passsword.php" class="text-decoration-none" style="font-size: 14px">Forgot your password?</a>
            </div>
            <div class="text-center mt-3">
              <a href="#" id="showRegister" class="text-decoration-none" style="font-size: 14px">Create an accounts</a>
            </div>
          </div>
        </form>
        </div>
      </div>
    </div>
    <script>
      const registerFormWrapper = document.getElementById('registerFormWrapper');
      const loginFormWrapper = document.getElementById('loginFormWrapper');
      const nameInput = document.querySelector('#registerFormWrapper input[name="name"]');
      const nameError = document.getElementById('nameError');
      const passwordInput = document.querySelector('#registerFormWrapper input[name="password"]');
      const passwordError = document.getElementById('passwordError');
      const addressInput = document.querySelector('#registerFormWrapper input[name="address"]');
      const addressError = document.getElementById('addressError');
      const emailInput = document.querySelector('#registerFormWrapper input[name="email"]');
      const emailError = document.getElementById('emailError');
      const loginEmailInput = document.querySelector('#loginFormWrapper input[name="email"]');
      const loginEmailError = document.getElementById('loginEmailError');
      const loginPasswordInput = document.querySelector('#loginFormWrapper input[name="password"]');
      const loginPasswordError = document.getElementById('loginPasswordError');
      const registerForm = document.getElementById('registerForm');
      const loginForm = document.getElementById('loginForm');
      const showLogin = document.getElementById('showLogin');
      const showRegister = document.getElementById('showRegister');

      registerFormWrapper.addEventListener('submit', function (e) {
        nameError.textContent = '';
        passwordError.textContent = '';
        addressError.textContent = '';
        emailError.textContent = '';

        let hasError = false;
        if(!nameInput || nameInput.value.trim() === ""){
          hasError = true;
          nameError.textContent = "Name is required.";
        }
        if(!passwordInput || passwordInput.value.trim() === ""){
          hasError = true;
          passwordError.textContent = "Password is required.";
        }
        if(!addressInput || addressInput.value.trim() === ""){
          hasError = true;
          addressError.textContent = "Address is required.";
        }
        if(!emailInput || emailInput.value.trim() === ""){
          hasError = true;
          emailError.textContent = "Email is required.";
        }
        if(hasError){
          e.preventDefault();
          return;
        }
      });
      loginFormWrapper.addEventListener('submit', function (e) {
        loginEmailError.textContent = '';
        loginPasswordError.textContent = '';

        let hasError = false;
        if(!loginEmailInput || loginEmailInput.value.trim() === ""){
          hasError = true;
          loginEmailError.textContent = "Email is required.";
        }
        if(!loginPasswordInput || loginPasswordInput.value.trim() === ""){
          hasError = true;
          loginPasswordError.textContent = "Password is required.";
        }
        if(hasError){
          e.preventDefault();
          return;
        }
      });

      showLogin.addEventListener('click', function (e) {
        e.preventDefault();
        registerForm.style.display = 'none';
        loginForm.style.display = 'block';
      });

      showRegister.addEventListener('click', function (e) {
        e.preventDefault();
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
      });
    </script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
      crossorigin="anonymous"
    ></script>
  </body>
</html>

