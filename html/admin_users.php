<?php
include 'db_conn.php'; // Database connection

// Handle activation/deactivation
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $action = $_GET['action'] == 'deactivate' ? 'deactivate' : 'active';
    $conn->query("UPDATE users SET status = '$action' WHERE id = '$user_id'");
    header("Location: admin_users.php");
    exit;
}

// Fetch users excluding admin
$users = [];
$result = $conn->query("SELECT * FROM users WHERE username != 'admin' ORDER BY id ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch wallets
$wallets = [];
$walletResult = $conn->query("SELECT user_id, balance FROM wallet");
if ($walletResult->num_rows > 0) {
    while ($wallet = $walletResult->fetch_assoc()) {
        $wallets[$wallet['user_id']] = $wallet['balance'];
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
            <div class="card">
  <h5 class="card-header">Manage Users</h5>
  <div class="table-responsive text-nowrap">
    <table id="usersTable" class="table">
      <thead>
        <tr>
          <th>Username</th>
          <th>Email</th>
          <th>Referrals</th>
          <th>Wallet</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        <?php foreach ($users as $user): 
          $uid = $user['id'];
          // map statuses to Bootstrap label classes
          $statusClass = match($user['status']) {
            'active'    => 'success',
            'inactive'  => 'warning',
            'deactivate'=> 'danger',
            default     => 'secondary'
          };
        ?>
        <tr>
          <!-- Username -->
          <td>
            <i class="fab fa-user fa-lg text-primary me-3"></i>
            <strong><?= htmlspecialchars($user['username']) ?></strong>
          </td>

          <!-- Email -->
          <td><?= htmlspecialchars($user['email']) ?></td>

          <!-- Referrals as avatar group -->
          <td>
            <?php if (!empty($referrals[$uid])): ?>
            <ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">
              <?php foreach ($referrals[$uid] as $ref): ?>
                <li
                  data-bs-toggle="tooltip"
                  data-popup="tooltip-custom"
                  data-bs-placement="top"
                  class="avatar avatar-xs pull-up"
                  title="<?= htmlspecialchars($ref['username']) ?>"
                >
                  <img
                    src="<?= htmlspecialchars($ref['avatar_url']) ?>"
                    alt="Avatar"
                    class="rounded-circle"
                  />
                </li>
              <?php endforeach; ?>
            </ul>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>

          <!-- Wallet balance -->
          <td>
            <?= isset($wallets[$uid])
                ? number_format($wallets[$uid], 2)
                : '—' ?>
          </td>

          <!-- Status badge -->
          <td>
            <span class="badge bg-label-<?= $statusClass ?> me-1">
              <?= ucfirst($user['status']) ?>
            </span>
          </td>

          <!-- Actions dropdown -->
          <td>
            <div class="dropdown">
              <button 
                type="button"
                class="btn p-0 dropdown-toggle hide-arrow"
                data-bs-toggle="dropdown"
              >
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <?php if ($user['status'] === 'active'): ?>
                  <a class="dropdown-item" href="?action=deactivate&id=<?= $uid ?>">
                    <i class="bx bx-block me-1"></i> Deactivate
                  </a>
                <?php else: ?>
                  <a class="dropdown-item" href="?action=activate&id=<?= $uid ?>">
                    <i class="bx bx-check-circle me-1"></i> Activate
                  </a>
                <?php endif; ?>
                <a
                  class="dropdown-item"
                  href="javascript:void(0);"
                  onclick="viewReferrals(<?= $uid ?>)"
                >
                  <i class="bx bx-user-plus me-1"></i> View Referrals
                </a>
              </div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="referralsModal" tabindex="-1" aria-labelledby="referralsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Referrals</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="referralsList">
                <!-- Referrals will be loaded here -->
            </div>
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
    <script>
// View referrals
function viewReferrals(userId) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "../admin/pages/get-referrals.php?user_id=" + userId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('referralsList').innerHTML = xhr.responseText;
            var myModal = new bootstrap.Modal(document.getElementById('referralsModal'));
            myModal.show();
        }
    };
    xhr.send();
}
</script>
<script>
  document
    .getElementById('searchInput')
    .addEventListener('keyup', function() {
      const q = this.value.toLowerCase();
      document
        .querySelectorAll('#usersTable tbody tr')
        .forEach(row => {
          const uname = row.cells[0].textContent.toLowerCase();
          const email = row.cells[1].textContent.toLowerCase();
          const bal   = row.cells[3].textContent.toLowerCase();
          row.style.display =
            (uname.includes(q) || email.includes(q) || bal.includes(q))
              ? ''
              : 'none';
        });
    });
</script>
  </body>
</html>
