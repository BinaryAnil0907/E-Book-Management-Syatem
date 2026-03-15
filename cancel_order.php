<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Only allow cancellation if order is 'Pending'
    $query = "UPDATE orders SET payment_status = 'Cancelled' 
              WHERE id = $order_id AND user_id = $user_id AND payment_status = 'Pending'";
    
    if ($conn->query($query)) {
        header("Location: my_orders.php?msg=Cancelled");
    } else {
        header("Location: my_orders.php?msg=Error");
    }
}
?>