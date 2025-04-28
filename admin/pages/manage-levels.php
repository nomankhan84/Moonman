<?php
include 'db.php'; // Database connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['percentage'] as $level_id => $percentage) {
        $level_id = (int)$level_id;
        $percentage = (float)$percentage;
        
        $conn->query("UPDATE levels SET percentage = '$percentage' WHERE id = '$level_id'");
    }
}

// Fetch all levels
$levels = [];
$result = $conn->query("SELECT * FROM levels ORDER BY id ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $levels[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Levels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Manage Levels</h2>
    <form method="post">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Level Name</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($levels as $level): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($level['level_name']); ?></td>
                        <td>
                            <input type="text" 
                                   class="form-control" 
                                   name="percentage[<?php echo $level['id']; ?>]" 
                                   value="<?php echo htmlspecialchars($level['percentage']); ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Update Levels</button>
    </form>
</div>
</body>
</html>
