<?php
include 'db.php'; // your mysqli connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['percentage'] as $lvl_id => $pct) {
        $id = (int)$lvl_id;
        $percentage = (float)$pct;
        $conn->query("
            UPDATE bonus_levels
            SET percentage = '{$percentage}'
            WHERE id = {$id}
        ");
    }
    // Optional: redirect to prevent resubmission
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}

// Fetch all bonus levels
$bonus_levels = [];
$res = $conn->query("SELECT * FROM bonus_levels ORDER BY id ASC");
while ($row = $res->fetch_assoc()) {
    $bonus_levels[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Bonus Levels</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
</head>
<body>
  <div class="container mt-5">
    <h2>Manage Bonus Levels</h2>
    <form method="post">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Level Name</th>
            <th>Percentage (%)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bonus_levels as $lvl): ?>
          <tr>
            <td><?= htmlspecialchars($lvl['level_name']) ?></td>
            <td>
              <input
                type="text"
                class="form-control"
                name="percentage[<?= $lvl['id'] ?>]"
                value="<?= htmlspecialchars($lvl['percentage']) ?>"
              />
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <button type="submit" class="btn btn-primary">Update Bonus Levels</button>
    </form>
  </div>
</body>
</html>
