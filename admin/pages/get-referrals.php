<?php
include 'db.php';

if (isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE sponsor_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo '<ul class="list-group">';
        while ($row = $result->fetch_assoc()) {
            echo '<li class="list-group-item">';
            echo '<strong>' . htmlspecialchars($row['username']) . '</strong> (' . htmlspecialchars($row['email']) . ')';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No referrals found for this user.</p>';
    }
}
?>
