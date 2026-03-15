<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $book_id = intval($_POST['book_id']);
    $user_id = $_SESSION['user_id'];
    
    // Catch rating securely
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = htmlspecialchars(trim($_POST['comment']));

    if ($rating < 1 || $rating > 5) {
        header("Location: user_dashboard.php?msg=PleaseSelectStars");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO reviews (book_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $book_id, $user_id, $rating, $comment);
    
    if ($stmt->execute()) {
        header("Location: user_dashboard.php?msg=ReviewSubmitted");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>