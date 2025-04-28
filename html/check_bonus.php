<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_conn.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) exit;

$now = date('Y-m-d H:i:s');

// 🟢 Step 1: Ensure wallet row exists
$wallet_check = $conn->prepare("SELECT user_id FROM user_bonus_wallet WHERE user_id = ?");
$wallet_check->bind_param("i", $user_id);
$wallet_check->execute();
$wallet_check->store_result();

if ($wallet_check->num_rows === 0) {
    $wallet_check->close();
    $create_wallet = $conn->prepare("INSERT INTO user_bonus_wallet (user_id, balance, updated_at) VALUES (?, 0.00, ?)");
    $create_wallet->bind_param("is", $user_id, $now);
    $create_wallet->execute();
    $create_wallet->close();
} else {
    $wallet_check->close();
}

// 🟢 Step 2: Define level thresholds and bonuses
$level_thresholds = [
    1 => 2250,
    2 => 8100,
    3 => 60750,
    4 => 607500,
    5 => 9112500,
    6 => 68343750,
    7 => 1025156250
];

$level_bonuses = [
    1 => 562,
    2 => 2025,
    3 => 15187,
    4 => 151875,
    5 => 911250,
    6 => 3417187,
    7 => 30754687
];

// 🟢 Step 3: Loop through all levels and apply bonus logic
for ($level = 1; $level <= 7; $level++) {
    // Get total income from level
    $stmt = $conn->prepare("SELECT SUM(amount) FROM income_history WHERE user_id = ? AND level = ?");
    $stmt->bind_param("ii", $user_id, $level);
    $stmt->execute();
    $stmt->bind_result($total_income);
    $stmt->fetch();
    $stmt->close();

    $total_income = $total_income ?? 0;

    if ($total_income >= $level_thresholds[$level]) {
        // Check if bonus is already awarded
        $check = $conn->prepare("SELECT id FROM bonus_wallet WHERE user_id = ? AND level = ?");
        $check->bind_param("ii", $user_id, $level);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            $check->close();

            $bonus = $level_bonuses[$level];

            // Log bonus history
            $insert = $conn->prepare("INSERT INTO bonus_wallet (user_id, level, bonus_amount, awarded_at) VALUES (?, ?, ?, ?)");
            $insert->bind_param("iids", $user_id, $level, $bonus, $now);
            $insert->execute();
            $insert->close();

            // Update bonus wallet
            $get_balance = $conn->prepare("SELECT balance FROM user_bonus_wallet WHERE user_id = ?");
            $get_balance->bind_param("i", $user_id);
            $get_balance->execute();
            $get_balance->bind_result($current_balance);
            $get_balance->fetch();
            $get_balance->close();

            $new_balance = $current_balance + $bonus;

            $update_wallet = $conn->prepare("UPDATE user_bonus_wallet SET balance = ?, updated_at = ? WHERE user_id = ?");
            $update_wallet->bind_param("dsi", $new_balance, $now, $user_id);
            $update_wallet->execute();
            $update_wallet->close();

        } else {
            $check->close();
        }
    }
}
