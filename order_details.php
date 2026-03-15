<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch detailed order info
$query = "SELECT o.*, u.username, u.email, b.title, b.author 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          JOIN books b ON o.book_id = b.id 
          WHERE o.id = $order_id";

$res = $conn->query($query);
$order = $res->fetch_assoc();

if (!$order) {
    die("Order not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Invoice - Admin View</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #1b212c; color: #f1f5f9; padding: 4rem; }
        .invoice-box { max-width: 900px; margin: 0 auto; background: rgba(37, 45, 58, 0.4); border-radius: 24px; padding: 3rem; border: 1px solid rgba(255,255,255,0.05); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #facc15; padding-bottom: 1.5rem; margin-bottom: 2rem; }
        .header h1 { font-size: 2.2rem; }
        .status-badge { background: #facc15; color: #0f172a; padding: 8px 16px; border-radius: 8px; font-weight: 800; font-size: 1rem; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem; }
        h3 { color: #facc15; margin-bottom: 1rem; font-size: 1.4rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 8px; }
        p { font-size: 1.2rem; margin-bottom: 10px; color: #e2e8f0; line-height: 1.6; }
        strong { color: #fff; }
        .total-section { background: rgba(0,0,0,0.2); padding: 2rem; border-radius: 16px; text-align: right; }
        .total-price { font-size: 2rem; font-weight: 800; color: #facc15; }
        .btn-back { display: inline-block; margin-top: 2rem; padding: 12px 24px; background: #334155; color: white; text-decoration: none; border-radius: 10px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>Order #<?php echo $order['id']; ?></h1>
            <div class="status-badge"><?php echo strtoupper($order['payment_status']); ?></div>
        </div>

        <div class="grid">
            <div>
                <h3><i class="fas fa-user"></i> Customer Info</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
            </div>
            <div>
                <h3><i class="fas fa-shipping-fast"></i> Shipping Address</h3>
                <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                <p><strong>Pincode:</strong> <?php echo $order['pincode']; ?></p>
            </div>
        </div>

        <div class="grid">
            <div>
                <h3><i class="fas fa-book"></i> Product Details</h3>
                <p><strong>Book:</strong> <?php echo htmlspecialchars($order['title']); ?></p>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($order['author']); ?></p>
            </div>
            <div>
                <h3><i class="fas fa-credit-card"></i> Payment Details</h3>
                <p><strong>Transaction ID:</strong> <span style="font-family: monospace;"><?php echo $order['razorpay_order_id']; ?></span></p>
                <p><strong>Method:</strong> <?php echo (strpos($order['razorpay_order_id'], 'COD') !== false) ? 'Cash on Delivery' : 'Online Payment'; ?></p>
            </div>
        </div>

        <div class="total-section">
            <p>Order Grand Total</p>
            <div class="total-price">₹<?php echo number_format($order['amount'], 2); ?></div>
        </div>

        <a href="admin_dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</body>
</html>