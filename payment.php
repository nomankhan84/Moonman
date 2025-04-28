<?php
session_start();
require_once 'html/db_conn.php'; // <-- make sure this defines $conn

// 0) Only allow POST requests here
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: html/index.php');
    exit;
}

// 1) Fetch per‑booking amount
function getAmountPerBooking($conn) {
    $sql = "SELECT setting_value 
              FROM settings 
             WHERE setting_key = 'amount' 
             LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) === 1) {
        $row = mysqli_fetch_assoc($res);
        return floatval($row['setting_value']);
    }
    return 0;
}

// 2) If txn form submitted, insert and redirect
if (isset($_POST['txn_submit'])) {
    $user_id            = intval($_POST['user_id']);
    $number_of_bookings = intval($_POST['number_of_bookings']);
    $amount             = floatval($_POST['amount']);
    $txn_id             = mysqli_real_escape_string($conn, $_POST['txn_id']);

    $sql = "INSERT INTO payments 
            (user_id, number_of_bookings, amount, txn_id, status, created_at)
            VALUES 
            ($user_id, $number_of_bookings, $amount, '$txn_id', 'pending', NOW())";

    if (mysqli_query($conn, $sql)) {
        header('Location: html/index.php?msg=payment_pending');
        exit;
    } else {
        die("Database error: " . mysqli_error($conn));
    }
}

// 3) Otherwise: prepare QR page
$user_id            = intval($_POST['user_id'] ?? 0);
$number_of_bookings = max(1, intval($_POST['number_of_bookings'] ?? 1));
$amt_per_booking    = getAmountPerBooking($conn);
$total_amount       = $amt_per_booking * $number_of_bookings;

// ==== UPI CONFIGURATION ====
// Your UPI ID (pa) – e.g. "yourname@bank"
$upi_id = 'ultranoobgamer@oksbi';

// Your payee name (pn) exactly as registered in your UPI app.
// For example: "Acme Furnishings Pvt Ltd" or your personal name.
$upi_name = 'ultranoobgamer@oksbi';
// =============================

// Build the UPI URI
$upi_payload = "upi://pay"
             . "?pa=" . urlencode($upi_id)
             . "&pn=" . urlencode($upi_name)
             . "&am=" . urlencode($total_amount)
             . "&cu=INR"
             . "&tn=" . urlencode("Booking Payment");

// Google Charts QR URL
$qr_google = "https://chart.googleapis.com/chart"
           . "?cht=qr"
           . "&chs=300x300"
           . "&chl=" . urlencode($upi_payload)
           . "&choe=UTF-8";

// goQR.me fallback URL
$qr_goqr = "https://api.qrserver.com/v1/create-qr-code/"
        . "?size=300x300"
        . "&data=" . urlencode($upi_payload);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Complete Your Payment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">

  <div class="card border-warning mx-auto" style="max-width: 400px;">
    <div class="card-body text-center">
      <h5 class="card-title text-warning">Scan &amp; Pay</h5>
      <p class="mb-2">Amount to pay:</p>
      <h4 class="fw-bold mb-3">₹<?php echo number_format($total_amount, 2); ?></h4>

      <!-- QR with fallback -->
      <img id="qr"
           src="<?php echo $qr_google; ?>"
           alt="UPI QR Code"
           class="mb-2"
           onerror="this.onerror=null;this.src='<?php echo $qr_goqr; ?>'">

      <p id="timer" class="text-muted">Valid for 60 seconds</p>

      <form method="post" class="mt-3">
        <input type="hidden" name="user_id"            value="<?php echo $user_id; ?>">
        <input type="hidden" name="number_of_bookings" value="<?php echo $number_of_bookings; ?>">
        <input type="hidden" name="amount"             value="<?php echo $total_amount; ?>">

        <div class="mb-3 text-start">
          <label for="txn_id" class="form-label">Enter UPI Transaction ID</label>
          <input type="text" id="txn_id" name="txn_id" class="form-control" required>
        </div>

        <button type="submit" name="txn_submit" id="submitBtn" class="btn btn-warning w-100">
          Submit &amp; Confirm
        </button>
      </form>
    </div>
  </div>

  <script>
    // 60‑second countdown → redirect when it hits zero
    let timeLeft = 60;
    const timerEl = document.getElementById('timer');
    const countdown = setInterval(() => {
      timeLeft--;
      timerEl.textContent = `Valid for ${timeLeft} second${timeLeft === 1 ? '' : 's'}`;
      if (timeLeft <= 0) {
        clearInterval(countdown);
        window.location.href = 'html/index.php';
      }
    }, 1000);
  </script>

</body>
</html>
