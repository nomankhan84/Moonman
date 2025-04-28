<?php
session_start();
include 'html/db_conn.php'; // Database connection

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

// Get wallet balance
$result = $conn->query("SELECT balance FROM wallet WHERE user_id = $user_id");
$wallet = $result->fetch_assoc();
$wallet_balance = $wallet['balance'] ?? 0;

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        
        <?php if ($status == 'inactive'): ?>
            <div class="alert alert-warning">
                <p>Your account is not active. Activate your account for just ₹600.</p>
                <form action="payment.php" method="post">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    <button type="submit">Pay Now</button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <p>Your account is active!</p>
                <p><strong>Your Referral Code: <?php echo htmlspecialchars($referral_code); ?></strong></p>
            </div>

            <h3>Wallet Balance: ₹<?php echo number_format($wallet_balance, 2); ?></h3>

            <h3>Payment History</h3>
            <table border="1">
                <tr>
                    <th>Received From</th>
                    <th>Amount</th>
                    <th>Level</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($payment_history as $history): ?>
                <tr>
                    <td><?php echo htmlspecialchars($history['received_from']); ?></td>
                    <td>₹<?php echo number_format($history['amount'], 2); ?></td>
                    <td>Level <?php echo $history['level']; ?></td>
                    <td><?php echo $history['created_at']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3>Network Structure</h3>
            <?php foreach ($downline as $lvl => $users): ?>
                <h4>Level <?php echo $lvl; ?></h4>
                <table border="1">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Referral Code</th>
                    </tr>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo ($user['status'] == 'active') ? 'Active' : 'Inactive'; ?></td>
                        <td><?php echo htmlspecialchars($user['referral_code']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>

        <br><a href="logout.php">Logout</a>
    </div>
</body>
</html>
