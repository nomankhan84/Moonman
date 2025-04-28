<?php
require 'html/db_conn.php';

/**
 * Generate a unique referral code of the form "MM123456"
 *
 * @param mysqli $conn  Active DB connection
 * @return string       A unique referral code
 */
function createUniqueReferralCode(mysqli $conn): string {
    do {
        // Generate 6 random digits, zero-padded on the left
        $digits = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $code   = 'MM' . $digits;

        // Check for collision
        $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

    } while ($exists);

    return $code;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['amount'])) {
    $user_id = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']); // make sure amount is decimal-safe

    // Validate if user exists
    $check_user = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $check_user->bind_param("i", $user_id);
    $check_user->execute();
    $check_user->store_result();

    if ($check_user->num_rows === 0) {
        echo "Invalid User ID!";
        exit;
    }
    $check_user->close();

    // Update user status to active
    $update_user = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    $update_user->bind_param("i", $user_id);
    $update_user->execute();
    $update_user->close();

    // Generate unique referral code and save
    $referral_code = createUniqueReferralCode($conn);
    $update_referral = $conn->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
    $update_referral->bind_param("si", $referral_code, $user_id);
    $update_referral->execute();
    $update_referral->close();

    // Open wallet if not exists
    $wallet_check = $conn->prepare("SELECT id FROM wallet WHERE user_id = ?");
    $wallet_check->bind_param("i", $user_id);
    $wallet_check->execute();
    $wallet_check->store_result();

    if ($wallet_check->num_rows == 0) {
        $wallet_insert = $conn->prepare("INSERT INTO wallet (user_id, balance) VALUES (?, 0)");
        $wallet_insert->bind_param("i", $user_id);
        $wallet_insert->execute();
        $wallet_insert->close();
    }
    $wallet_check->close();

    // Distribute earnings
    distributeEarnings($user_id, $amount);

    // Mark payment request as completed
    $update_payment = $conn->prepare(
        "UPDATE payments 
           SET status = 'completed' 
         WHERE user_id = ?"
    );
    $update_payment->bind_param("i", $user_id);
    $update_payment->execute();
    $update_payment->close();

    // Redirect back with success message
    header("Location: admin_payments.php?success=1");
    exit;
}

function distributeEarnings($user_id, $amount) {
    global $conn;

    $current_user = $user_id;
    for ($level = 1; $level <= 7; $level++) {
        $query = $conn->prepare("SELECT sponsor_id FROM users WHERE id = ?");
        $query->bind_param("i", $current_user);
        $query->execute();
        $query->store_result();
        $query->bind_result($sponsor_id);
        $query->fetch();
        $query->close();

        if (!$sponsor_id) break;

        $percentage_query = $conn->prepare("SELECT percentage FROM levels WHERE level_name = ?");
        $percentage_query->bind_param("i", $level);
        $percentage_query->execute();
        $percentage_query->store_result();
        $percentage_query->bind_result($percentage);
        $percentage_query->fetch();
        $percentage_query->close();

        $income = ($amount * $percentage) / 100;

        $update_wallet = $conn->prepare("UPDATE wallet SET balance = balance + ? WHERE user_id = ?");
        $update_wallet->bind_param("di", $income, $sponsor_id);
        $update_wallet->execute();
        $update_wallet->close();

        $insert_income = $conn->prepare("INSERT INTO income_history (user_id, amount, level, received_from) VALUES (?, ?, ?, ?)");
        $insert_income->bind_param("idii", $sponsor_id, $income, $level, $user_id);
        $insert_income->execute();
        $insert_income->close();

        $current_user = $sponsor_id;
    }
}
?>
