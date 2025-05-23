<?php
session_start();
include 'db_conn.php'; // your mysqli $conn

// 1) Handle approval
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $withdrawal_id = (int) $_GET['approve'];

    // Fetch the pending withdrawal
    $stmt = $conn->prepare("
      SELECT user_id, amount, wallet_type 
        FROM withdrawal_requests 
       WHERE id = ? 
         AND status = 'pending'
    ");
    $stmt->bind_param("i", $withdrawal_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows) {
        $stmt->bind_result($user_id, $amount, $wallet_type);
        $stmt->fetch();
        $stmt->close();

        // Determine the correct wallet balance based on wallet_type
        if ($wallet_type == 'bonus_wallet') {
            // Fetch bonus wallet balance
            $stmt = $conn->prepare("
              SELECT balance 
                FROM user_bonus_wallet 
               WHERE user_id = ?
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($bonus_balance);
            $stmt->fetch();
            $stmt->close();

            if ($bonus_balance >= $amount) {
                // Deduct from bonus wallet
                $upd = $conn->prepare("
                  UPDATE user_bonus_wallet 
                     SET balance = balance - ? 
                   WHERE user_id = ?
                ");
                $upd->bind_param("di", $amount, $user_id);
                $upd->execute();
                $upd->close();

                $_SESSION['success'] = "Withdrawal approved from bonus wallet.";
            } else {
                $_SESSION['error'] = "Insufficient bonus wallet balance.";
            }
        } else {
            // Fetch main wallet balance
            $stmt = $conn->prepare("
              SELECT balance 
                FROM wallet 
               WHERE user_id = ?
            ");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($main_balance);
            $stmt->fetch();
            $stmt->close();

            if ($main_balance >= $amount) {
                // Deduct from main wallet
                $upd = $conn->prepare("
                  UPDATE wallet 
                     SET balance = balance - ? 
                   WHERE user_id = ?
                ");
                $upd->bind_param("di", $amount, $user_id);
                $upd->execute();
                $upd->close();

                $_SESSION['success'] = "Withdrawal approved from main wallet.";
            } else {
                $_SESSION['error'] = "Insufficient main wallet balance.";
            }
        }

        // Mark the withdrawal request as completed
        $upd = $conn->prepare("
          UPDATE withdrawal_requests 
             SET status = 'completed' 
           WHERE id = ?
        ");
        $upd->bind_param("i", $withdrawal_id);
        $upd->execute();
        $upd->close();

    } else {
        $_SESSION['error'] = "Request not found or already processed.";
    }

    header("Location: manage-withdrawals.php");
    exit;
}

// 2) Fetch pending withdrawals, including wallet balances
$pending = $conn->query("
  SELECT
    wr.id,
    wr.user_id,
    wr.amount,
    wr.wallet_type,
    u.username,
    -- per-row subqueries for wallet balances
    (SELECT balance FROM wallet WHERE user_id = wr.user_id) AS main_balance,
    (SELECT balance FROM user_bonus_wallet WHERE user_id = wr.user_id) AS bonus_balance
  FROM withdrawal_requests wr
  JOIN users u ON wr.user_id = u.id
  WHERE wr.status = 'pending'
  ORDER BY wr.id DESC
");

// 3) Fetch all history with wallet balances
$history = $conn->query("
  SELECT
    wr.id,
    wr.user_id,
    wr.amount,
    wr.status,
    wr.created_at,
    wr.wallet_type,
    u.username,
    -- per-row subqueries for wallet balances
    (SELECT balance FROM wallet WHERE user_id = wr.user_id) AS main_balance,
    (SELECT balance FROM user_bonus_wallet WHERE user_id = wr.user_id) AS bonus_balance
  FROM withdrawal_requests wr
  JOIN users u ON wr.user_id = u.id
  ORDER BY wr.created_at DESC
");

?>


<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>Admin Users</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

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
            id="layout-navbar"
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                  <i class="bx bx-search fs-4 lh-0"></i>
                  <input
                    id="searchInput"
                    type="text"
                    class="form-control border-0 shadow-none"
                    placeholder="Search..."
                    aria-label="Search..."
                  />
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- Place this tag where you want the button to render. -->
                

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
                            <span class="fw-semibold d-block">John Doe</span>
                            <small class="text-muted">Admin</small>
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
                      <a class="dropdown-item" href="#">
                        <i class="bx bx-cog me-2"></i>
                        <span class="align-middle">Settings</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#">
                        <span class="d-flex align-items-center align-middle">
                          <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                          <span class="flex-grow-1 align-middle">Billing</span>
                          <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>
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
            <div class="container mt-5">
  <h2 class="mb-4">Manage Withdrawal Requests</h2>

  <!-- Alerts -->
  <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
      <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <!-- Pending Withdrawals -->
  <div class="card mb-5">
    <h5 class="card-header">Pending Withdrawals</h5>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>User</th>
            <th>Wallet Info</th>
            <th>Amount</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <?php if ($pending && $pending->num_rows): ?>
            <?php while ($r = $pending->fetch_assoc()): ?>
            <tr>
              <td>
                <i class="fab fa-user fa-sm text-primary me-2"></i>
                <?= htmlspecialchars($r['username']) ?>
              </td>
              <td>
                <span class="d-block">
                  <strong>Wallet:</strong>
                  <?= number_format($r['main_balance']??0,2) ?>
                </span>
                <span class="d-block">
                  <strong>Bonus:</strong>
                  <?= number_format($r['bonus_balance']??0,2) ?>
                </span>
              </td>
              <td><?= number_format($r['amount'],2) ?></td>
              <td>
                <a
                  href="?approve=<?= $r['id'] ?>"
                  class="btn btn-sm btn-success"
                  onclick="return confirm('Approve this withdrawal?')"
                >
                  <i class="bx bx-check-circle me-1"></i> Approve
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-muted">
                No pending requests.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Withdrawal History -->
  <div class="card">
    <h5 class="card-header">Withdrawal History</h5>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>User</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Requested At</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <?php if ($history && $history->num_rows): ?>
            <?php while ($h = $history->fetch_assoc()):
              $cls = match(strtolower($h['status'])) {
                'completed' => 'bg-label-success',
                'pending'   => 'bg-label-warning',
                default     => 'bg-label-secondary'
              };
            ?>
            <tr>
              <td>
                <i class="fab fa-user fa-sm text-primary me-2"></i>
                <?= htmlspecialchars($h['username']) ?>
              </td>
              
              <td><?= number_format($h['amount'],2) ?></td>
              <td>
                <span class="badge <?= $cls ?> me-1">
                  <?= ucfirst($h['status']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($h['created_at']) ?></td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center text-muted">
                No withdrawal history.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
              
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
                    class="footer-link me-4"
                    >Documentation</a
                  >

                  <a
                    href="https://github.com/themeselection/sneat-html-admin-template-free/issues"
                    target="_blank"
                    class="footer-link me-4"
                    >Support</a
                  >
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
