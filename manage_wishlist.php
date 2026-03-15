<?php
session_start();
include 'db.php';

if (isset($_GET['book_id']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $book_id = intval($_GET['book_id']);

    $check = $conn->query("SELECT * FROM wishlist WHERE user_id = $user_id AND book_id = $book_id");
    
    if ($check->num_rows > 0) {
        $conn->query("DELETE FROM wishlist WHERE user_id = $user_id AND book_id = $book_id");
    } else {
        $conn->query("INSERT INTO wishlist (user_id, book_id) VALUES ($user_id, $book_id)");
    }
    // Wapas usi page pe le jao jahan se click kiya tha
    header("Location: " . $_SERVER['HTTP_REFERER']);
}
?>