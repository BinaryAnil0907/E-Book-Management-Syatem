<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $book_id = $_POST['book_id'];
    $address = $conn->real_escape_string($_POST['address']);
    $pincode = $_POST['pincode'];
    $phone = $_POST['phone'];
    $amount = $_POST['amount'];

    // Order Table mein entry
    $sql = "INSERT INTO orders (user_id, book_id, address, pincode, phone, amount, payment_status) 
            VALUES ('$user_id', '$book_id', '$address', '$pincode', '$phone', '$amount', 'Success')";

    if ($conn->query($sql)) {
        echo "<script>alert('Congratulations! Your payment of ₹$amount was successful. Order placed for delivery.'); window.location.href='content.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: user_dashboard.php");
}
?>      