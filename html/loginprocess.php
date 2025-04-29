<?php
session_start();
include 'db_conn.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $username, $hashed_password, $status);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['status'] = $status;

            // Check if login already recorded for today
            $today = date('Y-m-d');
            $check_stmt = $conn->prepare("SELECT id FROM user_login_records WHERE user_id = ? AND login_date = ?");
            $check_stmt->bind_param("is", $user_id, $today);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows == 0) {
                // No login recorded today, insert new record
                $insert_stmt = $conn->prepare("INSERT INTO user_login_records (user_id, login_date) VALUES (?, ?)");
                $insert_stmt->bind_param("is", $user_id, $today);
                $insert_stmt->execute();
                $insert_stmt->close();
            }
            $check_stmt->close();

            $_SESSION['login_message'] = "Login successful! Redirecting...";
            $_SESSION['redirect_url'] = ($user_id == 3) ? 'adminindex.php' : 'index.php';

            header("Location: login.php");
            exit();
        } else {
            $_SESSION['login_message'] = "Incorrect password.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['login_message'] = "User not found.";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
