<?php
session_start();
include 'db_conn.php'; // your mysqli $conn

// 1) Handle approval
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $withdrawal_id = (int) $_GET['approve'];

    // Fetch the pending withdrawal
    $stmt = $conn->prepare("
      SELECT user_id, amount 
        FROM withdrawal_requests 
       WHERE id = ? 
         AND status = 'pending'
    ");
    $stmt->bind_param("i", $withdrawal_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows) {
        $stmt->bind_result($user_id, $amount);
        $stmt->fetch();
        $stmt->close();

        // Fetch main wallet balance
        $stmt = $conn->prepare("
          SELECT balance 
            FROM wallet 
           WHERE user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($main_balance);
        $stmt->fetch();
        $stmt->close();

        if ($main_balance >= $amount) {
            // Deduct
            $upd = $conn->prepare("
              UPDATE wallet 
                 SET balance = balance - ? 
               WHERE user_id = ?
            ");
            $upd->bind_param("di", $amount, $user_id);
            $upd->execute();
            $upd->close();

            // Mark completed
            $upd = $conn->prepare("
              UPDATE withdrawal_requests 
                 SET status = 'completed' 
               WHERE id = ?
            ");
            $upd->bind_param("i", $withdrawal_id);
            $upd->execute();
            $upd->close();

            $_SESSION['success'] = "Withdrawal approved.";
        } else {
            $_SESSION['error'] = "Insufficient main wallet balance.";
        }
    } else {
        $_SESSION['error'] = "Request not found or already processed.";
    }

    header("Location: manage-withdrawals.php");
    exit;
}

// 2) Fetch pending, with per-row subqueries for balances
$pending = $conn->query("
  SELECT
    wr.id,
    wr.user_id,
    wr.amount,
    u.username,
    -- per-row subqueries guarantee correct single-value fetch
    (SELECT balance FROM wallet WHERE user_id = wr.user_id)       AS main_balance,
    (SELECT balance FROM user_bonus_wallet WHERE user_id = wr.user_id) AS bonus_balance
  FROM withdrawal_requests wr
  JOIN users u ON wr.user_id = u.id
  WHERE wr.status = 'pending'
  ORDER BY wr.id DESC
");

// 3) Fetch all history likewise
$history = $conn->query("
  SELECT
    wr.id,
    wr.user_id,
    wr.amount,
    wr.status,
    wr.created_at,
    u.username,
    (SELECT balance FROM wallet WHERE user_id = wr.user_id)       AS main_balance,
    (SELECT balance FROM user_bonus_wallet WHERE user_id = wr.user_id) AS bonus_balance
  FROM withdrawal_requests wr
  JOIN users u ON wr.user_id = u.id
  ORDER BY wr.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Withdrawals</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
</head>
<body>
<div class="container mt-5">
  <h2 class="mb-4">Manage Withdrawal Requests</h2>

  <!-- Alerts -->
  <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
      <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <!-- Pending Withdrawals -->
  <div class="card mb-5">
    <h5 class="card-header">Pending Withdrawals</h5>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>User</th>
            <th>Wallet Info</th>
            <th>Amount</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <?php if ($pending && $pending->num_rows): ?>
            <?php while ($r = $pending->fetch_assoc()): ?>
            <tr>
              <td>
                <i class="fab fa-user fa-sm text-primary me-2"></i>
                <?= htmlspecialchars($r['username']) ?>
              </td>
              <td>
                <span class="d-block">
                  <strong>Wallet:</strong>
                  <?= number_format($r['main_balance']??0,2) ?>
                </span>
                <span class="d-block">
                  <strong>Bonus:</strong>
                  <?= number_format($r['bonus_balance']??0,2) ?>
                </span>
              </td>
              <td><?= number_format($r['amount'],2) ?></td>
              <td>
                <a
                  href="?approve=<?= $r['id'] ?>"
                  class="btn btn-sm btn-success"
                  onclick="return confirm('Approve this withdrawal?')"
                >
                  <i class="bx bx-check-circle me-1"></i> Approve
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-muted">
                No pending requests.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Withdrawal History -->
  <div class="card">
    <h5 class="card-header">Withdrawal History</h5>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>User</th>
            <th>Wallet Info</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Requested At</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          <?php if ($history && $history->num_rows): ?>
            <?php while ($h = $history->fetch_assoc()):
              $cls = match(strtolower($h['status'])) {
                'completed' => 'bg-label-success',
                'pending'   => 'bg-label-warning',
                default     => 'bg-label-secondary'
              };
            ?>
            <tr>
              <td>
                <i class="fab fa-user fa-sm text-primary me-2"></i>
                <?= htmlspecialchars($h['username']) ?>
              </td>
              <td>
                <span class="d-block">
                  <strong>Wallet:</strong>
                  <?= number_format($h['main_balance']??0,2) ?>
                </span>
                <span class="d-block">
                  <strong>Bonus:</strong>
                  <?= number_format($h['bonus_balance']??0,2) ?>
                </span>
              </td>
              <td><?= number_format($h['amount'],2) ?></td>
              <td>
                <span class="badge <?= $cls ?> me-1">
                  <?= ucfirst($h['status']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($h['created_at']) ?></td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center text-muted">
                No withdrawal history.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
