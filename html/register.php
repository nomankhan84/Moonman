<?php
session_start();
$register_message = '';
$redirect_url = '';

if (!empty($_SESSION['register_message'])) {
  $register_message = $_SESSION['register_message'];
  unset($_SESSION['register_message']);
}

if (!empty($_SESSION['redirect_url'])) {
  $redirect_url = $_SESSION['redirect_url'];
  unset($_SESSION['redirect_url']);
}
?>


<!DOCTYPE html>

<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>MoonMan Account registration</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <!-- Icons. Uncomment required icon fonts -->
  <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="../assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <!-- Page CSS -->
  <!-- Page -->
  <link rel="stylesheet" href="../assets/vendor/css/pages/page-auth.css" />
  <!-- Helpers -->
  <script src="../assets/vendor/js/helpers.js"></script>

  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="../assets/js/config.js"></script>

  <style>
    .logo-image{
      max-width: 100px
    }
    .app-brand-link{
      flex-direction: column;
    }
  </style>

</head>

<body>
  <!-- Content -->

  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Register Card -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center">
              <a href="index.html" class="app-brand-link gap-2">
                <img class="logo-image" src="../images/logo.jpg" alt="">
                <span class="app-brand-text demo text-body fw-bolder">Your income starts here 🚀</span>
              </a>
            </div>
            <!-- /Logo -->
            <!-- <h4 class="mb-2">Your income starts here 🚀</h4> -->
            <p class="mb-4"><?php if (!empty($register_message)): ?>
            <div class="alert alert-success">
              <?php echo $register_message; ?>
            </div>
          <?php endif; ?></p>

          <form id="formAuthentication" class="mb-3" action="registerprocess.php" method="POST">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input
                type="text"
                class="form-control"
                id="username"
                name="username"
                placeholder="Enter your username"
                autofocus />
            </div>

            <div class="mb-3 form-password-toggle">
              <label class="form-label" for="password">Password</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control"
                  name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password" />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
              </div>
            </div>

            <div class="mb-3">
              <label for="referral_code" class="form-label">Referral code</label>
              <input
                type="text"
                class="form-control"
                id="referral_code"
                name="referral_code"
                placeholder="Enter your Referral code"
                autofocus />
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email" />
            </div>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                <label class="form-check-label" for="terms-conditions">
                  I agree to
                  <a href="javascript:void(0);">privacy policy & terms</a>
                </label>
              </div>
            </div>
            <button class="btn btn-primary d-grid w-100">Sign up</button>
          </form>

          <p class="text-center">
            <span>Already have an account?</span>
            <a href="login.php">
              <span>Sign in</span>
            </a>
          </p>
          </div>
        </div>
        <!-- Register Card -->
      </div>
    </div>
  </div>

  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->
  <script src="../assets/vendor/libs/jquery/jquery.js"></script>
  <script src="../assets/vendor/libs/popper/popper.js"></script>
  <script src="../assets/vendor/js/bootstrap.js"></script>
  <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

  <script src="../assets/vendor/js/menu.js"></script>
  <!-- endbuild -->

  <!-- Vendors JS -->

  <!-- Main JS -->
  <script src="../assets/js/main.js"></script>

  <!-- Page JS -->

  <!-- Place this tag in your head or just before your close body tag. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script>
    // Wait for DOM to be ready
    document.addEventListener("DOMContentLoaded", function() {
      const urlParams = new URLSearchParams(window.location.search);
      const refCode = urlParams.get("ref");

      if (refCode) {
        const refInput = document.getElementById("referral_code");
        refInput.value = refCode;
        refInput.readOnly = true; // Make it non-editable
      }
    });
  </script>
  <?php if (!empty($redirect_url)): ?>
    <script>
      window.addEventListener('load', function() {
        setTimeout(function() {
          window.location.href = '<?php echo $redirect_url; ?>';
        }, 2000); // 2 seconds wait
      });
    </script>
  <?php endif; ?>
</body>

</html>