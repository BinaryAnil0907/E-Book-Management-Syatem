<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['action'])) {
    $book_id = intval($_GET['book_id']);

    if ($_GET['action'] == 'add') {
        // Check if book already in cart
        $check = $conn->query("SELECT * FROM cart WHERE user_id = $user_id AND book_id = $book_id");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE cart SET quantity = quantity + 1 WHERE user_id = $user_id AND book_id = $book_id");
        } else {
            $conn->query("INSERT INTO cart (user_id, book_id, quantity) VALUES ($user_id, $book_id, 1)");
        }
        header("Location: user_dashboard.php?msg=AddedToCart");

    } elseif ($_GET['action'] == 'remove') {
        $conn->query("DELETE FROM cart WHERE user_id = $user_id AND book_id = $book_id");
        header("Location: cart.php?msg=Removed");

    } elseif ($_GET['action'] == 'update') {
        $qty = intval($_GET['qty']);
        if ($qty > 0) {
            $conn->query("UPDATE cart SET quantity = $qty WHERE user_id = $user_id AND book_id = $book_id");
        } else {
            $conn->query("DELETE FROM cart WHERE user_id = $user_id AND book_id = $book_id");
        }
        header("Location: cart.php");
    }
}
?>