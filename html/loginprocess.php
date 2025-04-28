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

            $_SESSION['login_message'] = "Login successful! Redirecting...";
            $_SESSION['redirect_url'] = ($user_id == 3) ? 'adminindex.php' : 'index.php';

            header("Location: login.php"); // Go back to login page (index.php)
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
