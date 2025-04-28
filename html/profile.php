<?php
session_start();
require_once 'db_conn.php';  // makes $conn

// 1) Ensure user is logged in
$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    header('Location: login.php');
    exit;
}

// 2) Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize
    $mobile  = trim($_POST['mobile']  ?? '');
    $address = trim($_POST['address'] ?? '');
    $dob     = trim($_POST['dob']     ?? '');

    // Only set mobile if it was empty before
    if ($mobile) {
        $stmt = $conn->prepare("
            UPDATE users 
               SET mobile_number = ? 
             WHERE id = ? 
               AND mobile_number IS NULL
        ");
        $stmt->bind_param('si', $mobile, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Always update address & dob
    $stmt = $conn->prepare("
        UPDATE users 
           SET address       = ?, 
               date_of_birth = ? 
         WHERE id = ?
    ");
    $stmt->bind_param('ssi', $address, $dob, $user_id);
    $stmt->execute();
    $stmt->close();

    $msg = 'Profile updated successfully.';
}

// 3) Fetch current user data
$stmt = $conn->prepare("
    SELECT username, email, mobile_number, address, date_of_birth 
      FROM users 
     WHERE id = ?
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result(
    $username, 
    $email, 
    $mobile_number, 
    $address_val, 
    $dob_val
);
$stmt->fetch();
$stmt->close();
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
    <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Helpers -->
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>

    <style>
        .margin-01 {
            margin: 10px 25px;
        }

        @media(max-width:991px) {
            .margin-01 {
                margin: 10px 15px;
            }
        }
    </style>

    <style>
        /* Account Settings Styling */
        .account-settings {
            flex: 3;
            padding: 30px;
        }

        .account-settings h2 {
            margin-bottom: 30px;
            font-size: 24px;
        }

        .roww {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .roww>div {
            flex: 1;
            min-width: 250px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .account-settings input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="date"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 0;
            font-size: 14px;
            box-sizing: border-box;
        }

        .geneder-head {
            display: flex;
            gap: 50px;
        }

        .gender-options {
            display: flex;
            gap: 20px;
        }

        .gender-options label {
            margin-right: 15px;
        }

        .account-settings button {
            background-color: black;
            color: white;
            border: none;
            cursor: pointer;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            width: 20% !important;
        }

        @media (max-width: 576px) {
            .account-settings button {
                width: 30% !important;
            }
        }

        .account-settings button:hover {
            background-color: #444;
        }

        /* Main Content Styling */
        .order-content {
            flex: 3;
            padding: 20px;
        }
    </style>

</head>

<body>

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->

            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="index.php" class="app-brand-link">
                        <img src="/images2/logo.jpg" alt="" style="max-width: 50px;">
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">Moonman Tours</span>
                    </a>

                    <a href="javascript:void(0);"
                        class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                        <i class="bx bx-chevron-left bx-sm align-middle"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                <ul class="menu-inner py-1">

                    <li class="menu-header small text-uppercase"><span class="menu-header-text">All Pages</span></li>
                    <!-- Dashboard -->
                    <li class="menu-item active">
                        <a href="index.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="Analytics">Dashboard</div>
                        </a>
                    </li>

                    <!-- My profile -->
                    <!-- <li class="menu-item active">
                        <a href="profile.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bxs-user"></i>
                            <div data-i18n="Analytics">My Profile</div>
                        </a>
                    </li> -->

                    <!-- Wallets -->
                    <li class="menu-item">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-wallet"></i>
                            <div data-i18n="Account Settings">My Wallet</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item">
                                <a href="main-histo.php" class="menu-link">
                                    <div data-i18n="Account">Main Wallet History</div>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a href="bonus-histo.php" class="menu-link">
                                    <div data-i18n="Notifications">Bonus Wallet History</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Refferals -->
                    <li class="menu-item">
                        <a href="referals.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-share"></i>
                            <div data-i18n="Analytics">Referals</div>
                        </a>
                    </li>

                    <!-- Withdrawls -->
                    <li class="menu-item">
                        <a href="user-with.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-wallet"></i>
                            <div data-i18n="Analytics">Request Withdrawl</div>
                        </a>
                    </li>

                    <!-- Link Account -->
                    <li class="menu-item">
                        <a href="link-account.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user"></i>
                            <div data-i18n="Analytics">Link My Account</div>
                        </a>
                    </li>

                    <!-- Offer page -->
                    <li class="menu-item">
                        <a href="offer.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-rupee"></i>
                            <div data-i18n="Analytics">Achievements</div>
                        </a>
                    </li>

                    <!-- Dashboard -->
                    <li class="menu-item">
                        <a href="layouts-blank.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="Analytics">Blank</div>
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
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
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
                                <span class="nav-span-name">Welcome <?php echo htmlspecialchars($username); ?></span>
                            </div>
                        </div>
                        <!-- /Search -->

                        <ul class="navbar-nav flex-row align-items-center ms-auto">

                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="../assets/img/avatars/1.png" alt
                                            class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="../assets/img/avatars/1.png" alt
                                                            class="w-px-40 h-auto rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block">Mohammed Anas Tuwar</span>
                                                    <small class="text-muted">Admin</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="profile.php">
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
                                                <span
                                                    class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="auth-login-basic.php">
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
                    <div class="account-settings">
                    <h2 class="mb-4">Personal Details</h2>

<?php if (!empty($msg)): ?>
  <div class="alert alert-success"><?php echo $msg; ?></div>
<?php endif; ?>

<form id="profileForm" method="post">
  <!-- Full Name (readonly) -->
  <div class="mb-3">
    <label for="firstName" class="form-label">Full Name*</label>
    <input 
      type="text" 
      id="firstName" 
      name="full_name" 
      class="form-control" 
      value="<?php echo htmlspecialchars($username); ?>" 
      readonly
    >
  </div>

  <!-- Email (readonly) -->
  <div class="mb-3">
    <label for="email" class="form-label">Email*</label>
    <input 
      type="email" 
      id="email" 
      name="email" 
      class="form-control" 
      value="<?php echo htmlspecialchars($email); ?>" 
      readonly
    >
  </div>

  <!-- Mobile (editable only if not set) -->
  <div class="mb-3">
    <label for="mobile" class="form-label">Mobile Number*</label>
    <input 
      type="tel" 
      id="mobile" 
      name="mobile" 
      class="form-control" 
      value="<?php echo htmlspecialchars($mobile_number); ?>"
      <?php echo $mobile_number ? 'readonly' : 'required'; ?>
    >
    <?php if ($mobile_number): ?>
      <div class="form-text">Mobile number cannot be changed.</div>
    <?php endif; ?>
  </div>

  <!-- Address (always editable) -->
  <div class="mb-3 ">
    <label for="address" class="form-label">Address</label>
    <textarea 
      id="address" 
      name="address" 
      class="form-control" 
      rows="2"
    ><?php echo htmlspecialchars($address_val); ?></textarea>
  </div>

  <!-- Date of Birth (always editable) -->
  <div class="mb-3 ">
    <label for="dob" class="form-label">Date of Birth</label>
    <input 
      type="date" 
      id="dob" 
      name="dob" 
      class="form-control" 
      value="<?php echo htmlspecialchars($dob_val); ?>"
    >
  </div>

  <button type="submit" id="saveButton" class="btn btn-primary w-100">Save</button>
</form>
                    </div>
                    <!-- / div under the navbar -->
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column"
                            style="margin-top: 30px;">
                            <div class="mb-2 mb-md-0">
                                ©
                                <script>
                                    document.write(new Date().getFullYear());
                                </script>
                                <a href="https://themeselection.com" target="_blank" class="footer-link fw-bolder"></a>
                            </div>
                            <div>
                                <a href="https://moonmantours.online" class="footer-link me-4" target="_blank">Visit Our
                                    Webisite to
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
</body>

</html>