<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$title = $_POST['title'];
$description = $_POST['description'];

$sql = "INSERT INTO tasks (user_id, title, description) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $user_id, $title, $description);
$stmt->execute();

header('Location: dashboard.php');
exit;
