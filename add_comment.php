<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$book_id = $_POST['book_id'];
$comment = trim($_POST['comment']);

if (!empty($comment)) {
    $comment = $conn->real_escape_string($comment);
    $sql = "INSERT INTO comments (user_id, book_id, comment) VALUES ('$user_id', '$book_id', '$comment')";
    $conn->query($sql);
}

header("Location: user_dashboard.php");
exit;
?>
    