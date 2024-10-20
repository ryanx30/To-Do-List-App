<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Inisialisasi variabel untuk menangani form
$title = '';
$description = '';
$success_message = '';
$error_message = '';

// Proses penambahan tugas baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Validasi input
    if (!empty($title) && !empty($description)) {
        $sql = "INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iss', $user_id, $title, $description);

        if ($stmt->execute()) {
            $success_message = "Task added successfully!";
        } else {
            $error_message = "Failed to add task.";
        }
    } else {
        $error_message = "Title and description are required.";
    }
}

// Proses update status tugas
if (isset($_GET['action']) && $_GET['action'] == 'complete' && isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];
    $sql = "UPDATE tasks SET status = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $task_id, $user_id);
    $stmt->execute();
    $success_message = "Task marked as completed.";
}

// Proses menghapus tugas
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['task_id'])) {
    $task_id = $_GET['task_id'];
    $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $task_id, $user_id);
    $stmt->execute();
    $success_message = "Task deleted successfully.";
}

// Query untuk mengambil task dari user yang sedang login
$sql = "SELECT * FROM tasks WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$tasks = $stmt->get_result();

$search_query = '';
$filter_status = 'all';

// Handle pencarian task
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Handle filter status
if (isset($_GET['status'])) {
    $filter_status = $_GET['status'];
}

// Query dasar untuk mengambil task
$sql = "SELECT * FROM tasks WHERE user_id = ?";

// Tambahkan filter pencarian
if (!empty($search_query)) {
    $sql .= " AND title LIKE ?";
    $search_query = "%" . $search_query . "%";
}

// Tambahkan filter status
if ($filter_status === 'completed') {
    $sql .= " AND status = 1";
} elseif ($filter_status === 'incomplete') {
    $sql .= " AND status = 0";
}

$stmt = $conn->prepare($sql);
if (!empty($search_query)) {
    $stmt->bind_param('is', $user_id, $search_query);
} else {
    $stmt->bind_param('i', $user_id);
}
$stmt->execute();
$tasks = $stmt->get_result();
?>

<?php include 'header.php'; ?>

<div class="container">
    <h3>Welcome to your Dashboard</h3>
    <p>Here is an overview of your tasks:</p>

    <!-- Formulir untuk menambahkan tugas -->
    <h4>Add New Task</h4>
    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php elseif ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" class="form-control" required></textarea>
        </div>

        <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
    </form>

    <!-- Form Search dan Filter -->
    <form method="GET" action="">
        <div class="form-group">
            <label for="search">Search Tasks:</label>
            <input type="text" id="search" name="search" class="form-control" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="status">Filter by Status:</label>
            <select id="status" name="status" class="form-control">
                <option value="all" <?php if ($filter_status === 'all') echo 'selected'; ?>>All</option>
                <option value="completed" <?php if ($filter_status === 'completed') echo 'selected'; ?>>Completed</option>
                <option value="incomplete" <?php if ($filter_status === 'incomplete') echo 'selected'; ?>>Incomplete</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <!-- Tabel Task -->
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($tasks->num_rows > 0): ?>
                <?php while ($task = $tasks->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['description']); ?></td>
                        <td><?php echo $task['status'] == 1 ? 'Completed' : 'Incomplete'; ?></td>
                        <td>
                            <?php if ($task['status'] == 0): ?>
                                <a href="?action=complete&task_id=<?php echo $task['id']; ?>" class="btn btn-success btn-sm">Mark as Completed</a>
                            <?php endif; ?>
                            <a href="?action=delete&task_id=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No tasks found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
