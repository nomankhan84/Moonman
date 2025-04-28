<?php
session_start();
include 'html/db_conn.php';

// Fetch all pending payment requests
$result = $conn->query("
    SELECT p.id, p.user_id, p.number_of_bookings, p.amount, p.txn_id, u.username, u.email, p.created_at
    FROM payments p
    JOIN users u ON p.user_id = u.id
    WHERE p.status = 'pending'
");
?>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;">Payment approved successfully!</p>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Payment Requests</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Pending Payment Requests</h2>
    <table border="1">
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Number Of Bookings</th>
            <th>amount</th>
            <th>TXN_ID</th>
            <th>Request Date</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['number_of_bookings']); ?></td>
            <td><?php echo htmlspecialchars($row['amount']); ?></td>
            <td><?php echo htmlspecialchars($row['txn_id']); ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td>
                <form action="approve_payment.php" method="post">
                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                    <input type="hidden" name="amount" value="<?php echo $row['amount']; ?>">
                    <button type="submit">Approve</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
