<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO todo_lists (user_id, title) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $user_id, $title);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit;
    }
}
?>

<?php include 'header.php'; ?>

<h3 class="mb-4">Create a New To-Do List</h3>

<form method="POST" action="">
    <div class="form-group">
        <label for="title">To-Do List Title:</label>
        <input type="text" name="title" id="title" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Create</button>
</form>

<?php include 'footer.php'; ?>
