<?php
session_start();
include 'db.php';

if (isset($_GET['razorpay_payment_id']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $payment_id = $_GET['razorpay_payment_id'];

    // Get cart items again for database entry
    $cart_items = $conn->query("SELECT c.*, b.price FROM cart c JOIN books b ON c.book_id = b.id WHERE c.user_id = $user_id");

    while($item = $cart_items->fetch_assoc()) {
        $book_id = $item['book_id'];
        $amount = $item['price'] * $item['quantity'];
        
        // Save to orders table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, book_id, amount, razorpay_order_id, payment_status) VALUES (?, ?, ?, ?, 'Success')");
        $stmt->bind_param("iiis", $user_id, $book_id, $amount, $payment_id);
        $stmt->execute();
    }

    // Clear the cart
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    header("Location: my_orders.php?msg=OrderPlacedSuccessfully");
} else {
    header("Location: cart.php?msg=PaymentFailed");
}
?>