<?php
include 'db.php'; // Database connection

// Handle activation/deactivation
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $action = $_GET['action'] == 'deactivate' ? 'deactivate' : 'active';
    $conn->query("UPDATE users SET status = '$action' WHERE id = '$user_id'");
    header("Location: manage-users.php");
    exit;
}

// Fetch users excluding admin
$users = [];
$result = $conn->query("SELECT * FROM users WHERE username != 'admin' ORDER BY id ASC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch wallets
$wallets = [];
$walletResult = $conn->query("SELECT user_id, balance FROM wallet");
if ($walletResult->num_rows > 0) {
    while ($wallet = $walletResult->fetch_assoc()) {
        $wallets[$wallet['user_id']] = $wallet['balance'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">

<div class="card">
    <h5 class="card-header">Manage Users</h5>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Referral Code</th>
                    <th>Wallet Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><i class="fab fa-user fa-lg text-primary me-3"></i> <strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['referral_code']); ?></td>
                        <td>
                            <?php
                            $userId = $user['id'];
                            echo isset($wallets[$userId]) ? number_format($wallets[$userId], 2) : 'No Wallet';
                            ?>
                        </td>
                        <td>
                            <?php if ($user['status'] == 'active'): ?>
                                <span class="badge bg-label-success">Active</span>
                            <?php elseif ($user['status'] == 'inactive'): ?>
                                <span class="badge bg-label-warning">Inactive</span>
                            <?php elseif ($user['status'] == 'deactivate'): ?>
                                <span class="badge bg-label-danger">Deactivate</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <?php if ($user['status'] == 'active'): ?>
                                        <a class="dropdown-item" href="?action=deactivate&id=<?php echo $user['id']; ?>">
                                            <i class="bx bx-block me-1"></i> Deactivate
                                        </a>
                                    <?php else: ?>
                                        <a class="dropdown-item" href="?action=activate&id=<?php echo $user['id']; ?>">
                                            <i class="bx bx-check-circle me-1"></i> Activate
                                        </a>
                                    <?php endif; ?>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="viewReferrals(<?php echo $user['id']; ?>)">
                                        <i class="bx bx-user-plus me-1"></i> View Referrals
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal to show referrals -->
<div class="modal fade" id="referralsModal" tabindex="-1" aria-labelledby="referralsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Referrals</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="referralsList">
                <!-- Referrals will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// View referrals
function viewReferrals(userId) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get-referrals.php?user_id=" + userId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('referralsList').innerHTML = xhr.responseText;
            var myModal = new bootstrap.Modal(document.getElementById('referralsModal'));
            myModal.show();
        }
    };
    xhr.send();
}
</script>

</div>
</body>
</html>
