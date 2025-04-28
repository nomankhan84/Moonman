<?php
include 'db.php'; // <-- only this line for connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];

    // Check if amount setting already exists
    $check = $conn->query("SELECT * FROM settings WHERE setting_key = 'amount'");
    if ($check->num_rows > 0) {
        // Update existing
        $conn->query("UPDATE settings SET setting_value = '$amount' WHERE setting_key = 'amount'");
    } else {
        // Insert new
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('amount', '$amount')");
    }
}

// Fetch current amount
$currentAmount = '';
$result = $conn->query("SELECT * FROM settings WHERE setting_key = 'amount'");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $currentAmount = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Amount</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Manage Amount</h2>
    <form method="post">
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="text" class="form-control" id="amount" name="amount" value="<?php echo htmlspecialchars($currentAmount); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
</body>
</html>
