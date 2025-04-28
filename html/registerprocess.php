<?php
session_start();
include 'db_conn.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $referral_code = trim($_POST['referral_code']);

    if (empty($referral_code)) {
        $_SESSION['register_message'] = "Referral code is required.";
        header("Location: register.php");
        exit();
    }

    // Check if the referral code exists and get the sponsor's ID
    $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
    $stmt->bind_param("s", $referral_code);
    $stmt->execute();
    $stmt->bind_result($sponsor_id);
    $stmt->fetch();
    $stmt->close();

    if (!$sponsor_id) {
        $_SESSION['register_message'] = "Invalid referral code.";
        header("Location: register.php");
        exit();
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, sponsor_id, status) VALUES (?, ?, ?, ?, 'inactive')");
    $stmt->bind_param("sssi", $username, $email, $password, $sponsor_id);
    
    if ($stmt->execute()) {
        $_SESSION['register_message'] = "Registration successful! Redirecting to login...";
        $_SESSION['redirect_url'] = "login.php";
    } else {
        $_SESSION['register_message'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: register.php");
    exit();
}
?>
