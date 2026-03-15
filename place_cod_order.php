<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];

// 1. Fetch all items from the cart
$cart_items = $conn->query("SELECT c.*, b.price FROM cart c JOIN books b ON c.book_id = b.id WHERE c.user_id = $user_id");

if ($cart_items->num_rows > 0) {
    while($item = $cart_items->fetch_assoc()) {
        $book_id = $item['book_id'];
        $amount = $item['price'] * $item['quantity'];
        
        // 2. Insert into orders table with status 'Pending' and method 'COD'
        // Note: I am setting payment_status as 'Pending' because COD is paid at delivery
        $stmt = $conn->prepare("INSERT INTO orders (user_id, book_id, amount, payment_status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("iid", $user_id, $book_id, $amount);
        $stmt->execute();
    }

    // 3. Clear the cart after successful order
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    header("Location: my_orders.php?msg=CODOrderPlaced");
} else {
    header("Location: cart.php?msg=EmptyCart");
}
?>