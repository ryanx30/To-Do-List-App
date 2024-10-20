<?php
session_start();
include 'config.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<?php include 'header.php'; ?>

<div class="container">
    <h3>Your Profile</h3>
    <table class="table table-bordered">
        <tr>
            <th>Username</th>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
        </tr>
    </table>

    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
</div>

<?php include 'footer.php'; ?>
