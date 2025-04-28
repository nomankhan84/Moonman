<?php
session_start();
include 'db_conn.php'; // Database connection
// Run bonus logic automatically
include 'check_bonus.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$stmt = $conn->prepare("SELECT username, status, referral_code FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $status, $referral_code);
$stmt->fetch();
$stmt->close();

// Get wallet balance (Main wallet)
$result = $conn->query("SELECT balance FROM wallet WHERE user_id = $user_id");
$wallet = $result->fetch_assoc();
$wallet_balance = $wallet['balance'] ?? 0;

// Get bonus wallet balance
$bonus_balance = 0.00;
$bonus_stmt = $conn->prepare("SELECT balance FROM user_bonus_wallet WHERE user_id = ?");
$bonus_stmt->bind_param("i", $user_id);
$bonus_stmt->execute();
$bonus_stmt->bind_result($bonus_balance);
$bonus_stmt->fetch();
$bonus_stmt->close();

// Get total income amount
$total_income = 0.00;
$sum_stmt = $conn->prepare("SELECT SUM(amount) FROM income_history WHERE user_id = ?");
$sum_stmt->bind_param("i", $user_id);
$sum_stmt->execute();
$sum_stmt->bind_result($total_income);
$sum_stmt->fetch();
$sum_stmt->close();

$total_inc = $total_income + $bonus_balance;

// Get payment history
$result = $conn->query("SELECT received_from, amount, level, created_at FROM income_history WHERE user_id = $user_id ORDER BY created_at DESC");
$payment_history = $result->fetch_all(MYSQLI_ASSOC);

// Function to fetch downline users for each level
function getDownlineUsers($user_id, $conn) {
    $query = $conn->prepare("
        SELECT u.id, u.username, u.email, u.status, u.referral_code
        FROM users u
        WHERE u.sponsor_id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch downline users dynamically until no data exists
$downline = [];
$current_users = [$user_id];

for ($lvl = 1; $lvl <= 7; $lvl++) {
    $next_level_users = [];
    $downline[$lvl] = [];
    foreach ($current_users as $u_id) {
        $users = getDownlineUsers($u_id, $conn);
        if (!empty($users)) {
            $downline[$lvl] = array_merge($downline[$lvl], $users);
            foreach ($users as $user) {
                $next_level_users[] = $user['id'];
            }
        }
    }
    if (empty($downline[$lvl])) {
        unset($downline[$lvl]); // Remove empty levels
        break; // Stop fetching beyond empty levels
    }
    $current_users = $next_level_users;
}

$amount = 0; // default
$query = "SELECT setting_value FROM settings WHERE setting_key = 'amount' LIMIT 1";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $amount = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Moonman Tours - Dashboard</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="../images2/logo.jpg" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

  <!-- Icons. Uncomment required icon fonts -->
  <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="../assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />

  <!-- Helpers -->
  <script src="../assets/vendor/js/helpers.js"></script>
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
          <!-- div under the navbar -->
          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
              <div class="col-lg-8 mb-4 order-0">
              <?php if ($status == 'inactive'): ?>
                <div class="card border-warning">
    <div class="d-flex align-items-end row">
        <div class="col-sm-7">
            <div class="card-body">
                <h5 class="card-title text-warning">Activate Your Account ⚠️</h5>

                <p class="mb-4">
                    Your account is not active. Activate your account for just <span class="fw-bold">₹<?php echo htmlspecialchars($amount); ?></span> per booking.
                </p>

                <div class="mb-3">
                    <label for="number_of_bookings" class="form-label">Enter the number of bookings:</label>
                    <div class="d-flex align-items-center">
                        <span class="fw-bold me-2">₹<?php echo htmlspecialchars($amount); ?></span>
                        <span class="fw-bold me-2">×</span>
                        <input type="number" id="number_of_bookings" name="number_of_bookings" class="form-control form-control-sm w-25 me-2" min="1" value="1">
                        <span class="fw-bold me-2">=</span>
                        <span id="total_amount" class="fw-bold">₹<?php echo htmlspecialchars($amount); ?></span>
                    </div>
                </div>

                <form action="../payment.php" method="post">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <input type="hidden" id="hidden_total_amount" name="total_amount" value="<?php echo htmlspecialchars($amount); ?>">
                    <input type="hidden" id="hidden_number_of_bookings" name="number_of_bookings" value="1">
                    <button type="submit" class="btn btn-sm btn-outline-warning">Pay Now</button>
                </form>

            </div>
        </div>
    </div>
</div>
<?php else: ?>
    <div class="card border-success">
        <div class="d-flex align-items-end row">
            <div class="col-sm-7">
            <div class="card-body">
    <h5 class="card-title text-success">Your Account is Active! ✅</h5>
    <p class="mb-4">
        <strong>Your Referral Code: <?php echo htmlspecialchars($referral_code); ?></strong>
    </p>
    <a href="javascript:;" 
       class="btn btn-sm btn-outline-success" 
       id="shareReferralBtn"
       onclick="copyReferralLink('<?php echo $referral_code; ?>')">
       Share Referral Code
    </a>
</div>
            </div>
            
        </div>
    </div>
<?php endif; ?>
              </div>
              <div class="col-lg-4 col-md-4 order-1">
                <div class="row">
                  <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                      <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                          <div class="avatar flex-shrink-0">
                            <img src="../assets/img/icons/unicons/wallet.png" alt="chart success"
                              class="rounded" />
                          </div>
                          
                        </div>
                        <span class="fw-semibold d-block mb-1" style="margin-bottom: 20px !important;">Main
                          Wallet</span>
                        <h3 class="card-title mb-2">₹<?php echo number_format($wallet_balance, 2); ?></h3>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                      <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                          <div class="avatar flex-shrink-0">
                            <img src="../assets/img/icons/unicons/wallet-info.png" alt="Credit Card" class="rounded" />
                          </div>
                          
                        </div>
                        <span class="fw-semibold d-block mb-1" style="margin-bottom: 20px !important;">Bonus
                          Wallet</span>
                        <!-- <h3 class="card-title text-nowrap mb-1">₹4,679</h3> -->
                        <h3 class="card-title mb-2">₹<?php echo number_format($bonus_balance, 2); ?></h3>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                      <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                          <div class="avatar flex-shrink-0">
                            <img src="../assets/img/icons/unicons/wallet-info.png" alt="Credit Card" class="rounded" />
                          </div>
                          <div class="dropdown">
                            
                            
                          </div>
                        </div>
                        <span class="fw-semibold d-block mb-1" style="margin-bottom: 20px !important;">Total Income</span>
                        <!-- <h3 class="card-title text-nowrap mb-1">₹4,679</h3> -->
                        <h3 class="card-title mb-2">₹<?php echo number_format($total_inc, 2); ?></h3>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Total Revenue -->
            </div>
            <div class="row">
            </div>
          </div>
          <!-- /div under the navbar -->

          <!-- Basic Bootstrap Table -->
          <div class="margin-of-table">
          <h5 class="card-header">Your Network</h5>
          <?php foreach ($downline as $lvl => $users): ?>
    <div class="card mb-4">
        <h5 class="card-header">Level <?php echo $lvl; ?></h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Referral Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <i class="bx bx-user-circle text-primary me-2"></i> 
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php echo ($user['status'] == 'active') ? 'bg-label-success' : 'bg-label-danger'; ?>">
                                    <?php echo ($user['status'] == 'active') ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user['referral_code']); ?></td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="edit.php?id=<?php echo $user['id']; ?>">
                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                        </a>
                                        <a class="dropdown-item" href="delete.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure?');">
                                            <i class="bx bx-trash me-1"></i> Delete
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
<?php endforeach; ?>

          </div>
          <!--/ Basic Bootstrap Table -->
          <!-- / Content -->

          <!-- Footer -->
          <footer class="content-footer footer bg-footer-theme">
            <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column" style="margin-top: 30px;">
              <div class="mb-2 mb-md-0">
                ©
                <script>
                  document.write(new Date().getFullYear());
                </script>
                <a href="https://themeselection.com" target="_blank" class="footer-link fw-bolder"></a>
              </div>
              <div>
                <a href="https://moonmantours.online" class="footer-link me-4" target="_blank">Visit Our Webisite to
                  book a tour</a>
              </div>
            </div>
          </footer>
          <!-- / Footer -->

          <div class="content-backdrop fade"></div>
        </div>
        <!-- /Content wrapper -->
        
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
  <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>

  <!-- Main JS -->
  <script src="../assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="../assets/js/dashboards-analytics.js"></script>

  <!-- Place this tag in your head or just before your close body tag. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script>
    function copyReferralLink(referralCode) {
        const referralLink = `/localhost/moonman/index.html?ref=${referralCode}`;
        const shareBtn = document.getElementById('shareReferralBtn');

        navigator.clipboard.writeText(referralLink).then(() => {
            // Change button text to "Copied!"
            const originalText = shareBtn.innerText;
            shareBtn.innerText = "Copied!";
            shareBtn.disabled = true;

            // Revert back after 5 seconds
            setTimeout(() => {
                shareBtn.innerText = originalText;
                shareBtn.disabled = false;
            }, 5000);
        });
    }
</script>
<script>
    const amountPerBooking = <?php echo (int)$amount; ?>;
    const bookingInput = document.getElementById('number_of_bookings');
    const totalAmountSpan = document.getElementById('total_amount');
    const hiddenTotalAmount = document.getElementById('hidden_total_amount');
    const hiddenNumberOfBookings = document.getElementById('hidden_number_of_bookings');

    bookingInput.addEventListener('input', function() {
        let bookings = parseInt(this.value) || 0;
        let total = bookings * amountPerBooking;
        totalAmountSpan.textContent = '₹' + total;
        hiddenTotalAmount.value = total;
        hiddenNumberOfBookings.value = bookings;
    });
</script>
</body>

</html>