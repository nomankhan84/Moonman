<?php
include 'db_conn.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
  header('Location: login.php');
  exit;
}

// Fetch main wallet balance
$stmt = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($main_wallet_balance);
$stmt->fetch();
$stmt->close();

// Fetch bonus wallet balance
$bonus_wallet_balance = 0;
$bonus_stmt = $conn->prepare("SELECT balance FROM user_bonus_wallet WHERE user_id = ?");
$bonus_stmt->bind_param("i", $user_id);
$bonus_stmt->execute();
$bonus_stmt->bind_result($bonus_wallet_balance);
$bonus_stmt->fetch();
$bonus_stmt->close();

// Check pending withdrawal request
$pending = false;
$check = $conn->prepare("SELECT id FROM withdrawal_requests WHERE user_id = ? AND status = 'pending'");
$check->bind_param("i", $user_id);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
  $pending = true;
}
$check->close();

// Check if user has added bank/upi details
$has_bank_details = false;
$bank_stmt = $conn->prepare("SELECT id FROM user_bank_details WHERE user_id = ?");
$bank_stmt->bind_param("i", $user_id);
$bank_stmt->execute();
$bank_stmt->store_result();
if ($bank_stmt->num_rows > 0) {
  $has_bank_details = true;
}
$bank_stmt->close();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $amount = floatval($_POST['amount']);
  $payment_method = $_POST['payment_method'] ?? '';
  $wallet_type = $_POST['wallet_type'] ?? ''; // New input to choose wallet

  if ($pending) {
    $errors[] = '❌ You already have a pending withdrawal request.';
  }
  if ($amount < 200) {
    $errors[] = '❌ Minimum withdrawal amount is ₹200.';
  }
  if ($wallet_type === 'main_wallet' && $amount > $main_wallet_balance) {
    $errors[] = '❌ You cannot withdraw more than your current balance in the Main Wallet.';
  }
  if ($wallet_type === 'bonus_wallet' && $amount > $bonus_wallet_balance) {
    $errors[] = '❌ You cannot withdraw more than your current balance in the Bonus Wallet.';
  }
  if (empty($payment_method)) {
    $errors[] = '❌ Please select a payment method.';
  }

  if (empty($errors)) {

    // Insert withdrawal request
    $ins = $conn->prepare("INSERT INTO withdrawal_requests (user_id, amount, payment_method, status, wallet_type) VALUES (?, ?, ?, 'pending', ?)");
    $ins->bind_param("idss", $user_id, $amount, $payment_method, $wallet_type);
    $ins->execute();
    $ins->close();

    $success = '✅ Your withdrawal request has been submitted!';
    $pending = true; // mark as pending to hide form
  }
}
?>
<!DOCTYPE html>

<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Tables - Basic Tables | Sneat - Bootstrap 5 HTML Admin Template - Pro</title>

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

  <!-- Helpers -->
  <script src="../assets/vendor/js/helpers.js"></script>

  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="../assets/js/config.js"></script>
</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->

      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="index.html" class="app-brand-link">
            <img src="/images2/logo.jpg" alt="" style="max-width: 50px;">
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Moonman Tours</span>
          </a>

          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
          </a>
        </div>

        <div class="menu-inner-shadow"></div>

        <ul class="menu-inner py-1">

          <li class="menu-header small text-uppercase"><span class="menu-header-text">All Pages</span></li>
          <!-- Dashboard -->
          <li class="menu-item active">
            <a href="index.html" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div data-i18n="Analytics">Dashboard</div>
            </a>
          </li>

          <!-- Wallets -->
          <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
              <i class="menu-icon tf-icons bx bx-wallet"></i>
              <div data-i18n="Account Settings">My Wallet</div>
            </a>
            <ul class="menu-sub">
              <li class="menu-item">
                <a href="income_history.php" class="menu-link">
                  <div data-i18n="Account">Main Wallet History</div>
                </a>
              </li>
              <li class="menu-item">
                <a href="track.php" class="menu-link">
                  <div data-i18n="Notifications">Bonus Wallet History</div>
                </a>
              </li>
              <li class="menu-item">
                <a href="withdrawal_history.php" class="menu-link">
                  <div data-i18n="Notifications">Withdrawal History</div>
                </a>
              </li>
            </ul>
          </li>

          <!-- Refferals -->
          <li class="menu-item">
            <a href="my_network.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-share"></i>
              <div data-i18n="Analytics">My Network</div>
            </a>
          </li>

          <!-- Dashboard -->
          <li class="menu-item">
            <a href="withdraw.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div data-i18n="Analytics">Withdraw</div>
            </a>
          </li>

          <!-- Misc -->
          <li class="menu-header small text-uppercase"><span class="menu-header-text">Misc</span></li>
          <li class="menu-item">
            <a href="https://wa.me/919799824885" target="_blank" class="menu-link">
              <i class="menu-icon tf-icons bx bx-support"></i>
              <div data-i18n="Support">Support</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="https://moonmantours.online" target="_blank" class="menu-link">
              <i class="menu-icon tf-icons bx bx-globe"></i>
              <div data-i18n="Documentation">Our Website</div>
            </a>
          </li>
        </ul>
      </aside>
      <!-- / Menu -->

      <!-- Layout container -->
      <div class="layout-page">

        <!-- Navbar -->
        <nav
          class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
          id="layout-navbar">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
              <i class="bx bx-menu bx-sm"></i>
            </a>
          </div>

          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <!-- Search -->
            <div class="navbar-nav align-items-center">
              <div class="nav-item d-flex align-items-center">
                <span class="nav-span-name">Welcome <?php echo htmlspecialchars($username); ?>!</span>
              </div>
            </div>
            <!-- /Search -->

            <ul class="navbar-nav flex-row align-items-center ms-auto">

              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="#">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <span class="fw-semibold d-block"><?php echo htmlspecialchars($username); ?></span>
                          <small class="text-muted">Tour Promoter</small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#">
                      <i class="bx bx-user me-2"></i>
                      <span class="align-middle">My Profile</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="bind-bank.php">
                      <span class="d-flex align-items-center align-middle">
                        <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                        <span class="flex-grow-1 align-middle">Link Bank Account</span>

                      </span>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="auth-login-basic.html">
                      <i class="bx bx-power-off me-2"></i>
                      <span class="align-middle">Log Out</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!--/ User -->
            </ul>
          </div>
        </nav>
        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->

          <div class="container-xxl flex-grow-1 container-p-y">

            <!-- Basic Bootstrap Table -->
            <div class="container mt-5">
              <div class="card">
                <h5 class="card-header">Request Withdrawal</h5>
                <div class="card-body">

                  <!-- Current Balance -->
                  <h6>💰 Current Balance in Main Wallet: ₹<?php echo number_format($main_wallet_balance, 2); ?></h6>
                  <h6>💰 Current Balance in Bonus Wallet: ₹<?php echo number_format($bonus_wallet_balance, 2); ?></h6>
                  <hr>

                  <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                      <?php foreach ($errors as $error): ?>
                        <div><?php echo $error; ?></div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                  <?php endif; ?>

                  <?php if (!$pending): ?>
                    <form method="post">
                      <div class="mb-3">
                        <label for="amount" class="form-label">Enter Amount to Withdraw</label>
                        <input type="number" name="amount" id="amount" class="form-control" min="200" required>
                      </div>

                      <div class="mb-3">
                        <label for="wallet_type" class="form-label">Select Wallet</label>
                        <select name="wallet_type" id="wallet_type" class="form-select" required>
                          <option value="">-- Select Wallet --</option>
                          <option value="main_wallet">Main Wallet</option>
                          <option value="bonus_wallet" <?php echo $bonus_wallet_balance <= 0 ? 'disabled' : ''; ?>>Bonus Wallet</option>
                        </select>
                        <?php if ($bonus_wallet_balance <= 0): ?>
                          <small class="text-danger">* Bonus wallet has no balance.</small>
                        <?php endif; ?>
                      </div>

                      <div class="mb-3">
                        <label for="payment_method" class="form-label">Select Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                          <option value="">-- Select --</option>
                          <option value="cash">Cash</option>
                          <option value="bank" <?php echo !$has_bank_details ? 'disabled' : ''; ?>>Bank Transfer (IMPS/NEFT)</option>
                          <option value="upi" <?php echo !$has_bank_details ? 'disabled' : ''; ?>>UPI Transfer</option>
                        </select>
                        <?php if (!$has_bank_details): ?>
                          <small class="text-danger">* Only Cash available. Please bind your bank account to enable Bank/UPI.</small>
                        <?php endif; ?>
                      </div>

                      <button type="submit" class="btn btn-primary">Submit Request</button>
                    </form>
                  <?php else: ?>
                    <div class="alert alert-info">
                      ⚡ You have already submitted a withdrawal request. Please wait for admin approval.
                    </div>
                  <?php endif; ?>

                </div>
              </div>
            </div>
            <!--/ Basic Bootstrap Table -->



            <!-- Bootstrap Dark Table -->

            <!--/ Responsive Table -->
          </div>
          <!-- / Content -->

          <!-- Footer -->
          <footer class="content-footer footer bg-footer-theme">
            <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
              <div class="mb-2 mb-md-0">
                ©
                <script>
                  document.write(new Date().getFullYear());
                </script>
                , made with ❤️ by
                <a href="https://themeselection.com" target="_blank" class="footer-link fw-bolder">ThemeSelection</a>
              </div>
              <div>
                <a href="https://themeselection.com/license/" class="footer-link me-4" target="_blank">License</a>
                <a href="https://themeselection.com/" target="_blank" class="footer-link me-4">More Themes</a>

                <a
                  href="https://themeselection.com/demo/sneat-bootstrap-html-admin-template/documentation/"
                  target="_blank"
                  class="footer-link me-4">Documentation</a>

                <a
                  href="https://github.com/themeselection/sneat-html-admin-template-free/issues"
                  target="_blank"
                  class="footer-link me-4">Support</a>
              </div>
            </div>
          </footer>
          <!-- / Footer -->

          <div class="content-backdrop fade"></div>
        </div>
        <!-- Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>
  <!-- / Layout wrapper -->

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
</body>

</html>