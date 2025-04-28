<?php
include 'db.php'; // your DB connection

// Approve withdrawal request
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $withdrawal_id = (int) $_GET['approve'];

    // Get the withdrawal request details
    $getRequest = $conn->prepare("SELECT user_id, amount FROM withdrawal_requests WHERE id = ? AND status = 'pending'");
    $getRequest->bind_param("i", $withdrawal_id);
    $getRequest->execute();
    $getRequest->store_result();

    if ($getRequest->num_rows > 0) {
        $getRequest->bind_result($user_id, $amount);
        $getRequest->fetch();
        $getRequest->close(); // IMPORTANT to close before next query

        // Get current wallet balance
        $getWallet = $conn->prepare("SELECT balance FROM wallet WHERE user_id = ?");
        $getWallet->bind_param("i", $user_id);
        $getWallet->execute();
        $getWallet->bind_result($balance);
        $getWallet->fetch();
        $getWallet->close(); // again close

        if ($balance >= $amount) {
            // Deduct the amount
            $updateWallet = $conn->prepare("UPDATE wallet SET balance = balance - ? WHERE user_id = ?");
            $updateWallet->bind_param("di", $amount, $user_id);
            $updateWallet->execute();
            $updateWallet->close();

            // Mark withdrawal request as completed
            $updateRequest = $conn->prepare("UPDATE withdrawal_requests SET status = 'completed' WHERE id = ?");
            $updateRequest->bind_param("i", $withdrawal_id);
            $updateRequest->execute();
            $updateRequest->close();

            $_SESSION['success'] = "Withdrawal request approved successfully.";
        } else {
            $_SESSION['error'] = "User does not have sufficient balance.";
        }
    } else {
        $_SESSION['error'] = "Withdrawal request not found or already processed.";
    }

    header("Location: manage-withdrawals.php");
    exit;
}

// Fetch all pending withdrawals
$withdrawals = [];
$result = $conn->query("SELECT wr.id, wr.user_id, wr.amount, wr.status, u.username 
                        FROM withdrawal_requests wr
                        JOIN users u ON wr.user_id = u.id
                        WHERE wr.status = 'pending'
                        ORDER BY wr.id DESC");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $withdrawals[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Withdrawals</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Manage Withdrawal Requests</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>User</th>
                <th>Requested Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($withdrawals)): ?>
                <?php foreach ($withdrawals as $withdrawal): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($withdrawal['username']); ?></td>
                        <td><?php echo number_format($withdrawal['amount'], 2); ?></td>
                        <td>
                            <a href="?approve=<?php echo $withdrawal['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Approve this withdrawal request?')">Approve</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">No pending withdrawal requests.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
