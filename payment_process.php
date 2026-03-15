<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    
    $user_id = $_SESSION['user_id'];
    $book_id = $conn->real_escape_string($_POST['book_id']);
    $address = $conn->real_escape_string(trim($_POST['address']));
    $pincode = $conn->real_escape_string(trim($_POST['pincode']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $amount = floatval($_POST['amount']);
    
    // Check payment method
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'razorpay';

    if ($payment_method === 'cod') {
        // Agar COD hai
        $razorpay_order_id = 'COD-' . time(); // Unique id for COD
        $payment_status = 'Pending'; // COD payment is pending until delivery
    } else {
        // Agar Razorpay Online hai
        $razorpay_order_id = isset($_POST['razorpay_payment_id']) ? $conn->real_escape_string($_POST['razorpay_payment_id']) : '';
        $payment_status = !empty($razorpay_order_id) ? 'Success' : 'Failed';
    }

    // Order Table mein entry
    $sql = "INSERT INTO orders (user_id, book_id, address, pincode, phone, amount, razorpay_order_id, payment_status) 
            VALUES ('$user_id', '$book_id', '$address', '$pincode', '$phone', '$amount', '$razorpay_order_id', '$payment_status')";

    if ($conn->query($sql)) {
        header("Location: my_orders.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
        echo "<br><a href='user_dashboard.php'>Go Back</a>";
    }
} else {
    header("Location: user_dashboard.php");
    exit();
}
?>