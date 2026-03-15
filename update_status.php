<?php
include 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];

    $sql = "UPDATE orders SET payment_status = '$new_status' WHERE id = '$order_id'";
    if ($conn->query($sql)) {
        header("Location: admin_dashboard.php?msg=Status Updated");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>