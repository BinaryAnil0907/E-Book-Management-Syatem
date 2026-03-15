<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['book_id'])) {
    header("Location: user_dashboard.php");
    exit;
}

$book_id = intval($_GET['book_id']);
$res = $conn->query("SELECT * FROM books WHERE id = $book_id");
$book = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - Order Book</title>
    <link rel="stylesheet" href="registerlogin.css">
    <style>
        .order-summary { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd; }
        textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h1>Book Enthusiast</h1>
    <div class="container">
        <h2>Complete Your Order</h2>
        <div class="order-summary">
            <p><strong>Book:</strong> <?php echo htmlspecialchars($book['title']); ?></p>
            <p><strong>Price:</strong> ₹<?php echo htmlspecialchars($book['price']); ?></p>
        </div>

        <form action="payment_process.php" method="POST">
            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
            <input type="hidden" name="amount" value="<?php echo $book['price']; ?>">

            <label>Delivery Address:</label>
            <textarea name="address" placeholder="Enter your full home address" required></textarea>

            <label>Pincode:</label>
            <input type="text" name="pincode" pattern="[0-9]{6}" title="6 digit pincode" required>

            <label>Phone Number:</label>
            <input type="text" name="phone" pattern="[0-9]{10}" title="10 digit mobile number" required>

            <button type="submit">Place Order & Pay</button>
        </form>
        <br>        
        <a href="content.php">Cancel Order</a>
    </div>
</body>
</html> 