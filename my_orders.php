<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders with book details
$query = "SELECT o.*, b.title, b.img FROM orders o 
          JOIN books b ON o.book_id = b.id 
          WHERE o.user_id = '$user_id' 
          ORDER BY o.id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Book Enthusiast</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #1b212c; /* Matte Dark Background */
            color: #f1f5f9;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Premium Navbar */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(27, 33, 44, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(241, 196, 15, 0.2);
            padding: 1rem 0;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: #facc15;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: #e2e8f0;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links a:hover, .nav-links a.active {
            color: #facc15;
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444 !important;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            transition: 0.3s;
        }

        .btn-logout:hover {
            background: #ef4444;
            color: #fff !important;
        }

        /* Orders Container */
        .content-container {
            max-width: 900px;
            margin: 3rem auto 5rem;
            padding: 0 2rem;
        }

        .page-header {
            margin-bottom: 2.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #94a3b8;
            font-size: 1.05rem;
        }

        /* Order Cards */
        .order-card {
            background: #252d3a;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            border: 1px solid rgba(255,255,255,0.05);
            transition: 0.3s ease;
            position: relative;
        }

        .order-card:hover {
            transform: translateY(-5px);
            border-color: rgba(250, 204, 21, 0.4);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }

        .order-card img {
            width: 90px;
            height: 130px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.4);
        }

        .order-details {
            flex-grow: 1;
        }

        .order-details h3 {
            margin: 0 0 5px 0;
            color: #fff;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .order-info-line {
            color: #94a3b8;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }

        .order-info-line strong {
            color: #cbd5e1;
            font-weight: 600;
        }

        .price-highlight {
            color: #facc15;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .txn-text {
            font-size: 0.85rem;
            color: #64748b;
            font-family: monospace;
            background: rgba(0,0,0,0.2);
            padding: 3px 8px;
            border-radius: 4px;
            margin-top: 5px;
            display: inline-block;
        }

        /* Dark Mode Dynamic Status Badges */
        .status-container {
            text-align: right;
            min-width: 120px;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Glowing Neon Badges */
        .status-Pending {
            background: rgba(245, 158, 11, 0.15);
            color: #fcd34d;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .status-Success {
            background: rgba(56, 189, 248, 0.15);
            color: #7dd3fc;
            border: 1px solid rgba(56, 189, 248, 0.3);
        }

        .status-Shipped {
            background: rgba(168, 85, 247, 0.15);
            color: #c084fc;
            border: 1px dashed rgba(168, 85, 247, 0.4);
        }

        .status-Delivered {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(37, 45, 58, 0.4);
            border-radius: 16px;
            border: 1px dashed rgba(255,255,255,0.1);
        }

        .empty-state i {
            font-size: 3rem;
            color: #64748b;
            margin-bottom: 1rem;
        }

        .btn-browse {
            display: inline-block;
            margin-top: 1.5rem;
            background: #facc15;
            color: #0f172a;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 5px 15px rgba(250, 204, 21, 0.2);
            transition: 0.3s;
        }

        .btn-browse:hover {
            background: #eab308;
            transform: translateY(-2px);
        }

        /* --- Footer Styles --- */
        .site-footer {
            background: rgba(15, 23, 42, 0.95);
            border-top: 1px solid rgba(241, 196, 15, 0.1);
            padding: 2.5rem 0;
            text-align: center;
            color: #94a3b8;
            margin-top: auto;
        }

        .footer-logo {
            color: #facc15;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-container { flex-direction: column; gap: 1rem; }
            .nav-links { flex-wrap: wrap; justify-content: center; }
            
            .order-card {
                flex-direction: column;
                text-align: center;
                padding: 2rem 1rem;
            }
            
            .order-card img {
                margin-right: 0;
                margin-bottom: 1.5rem;
                width: 120px;
                height: 170px;
            }

            .status-container {
                margin-top: 1.5rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Book Enthusiast</a>
            <div class="nav-links">
                <a href="user_dashboard.php"><i class="fas fa-book-open"></i> Library</a>
                <a href="my_orders.php" class="active"><i class="fas fa-box"></i> My Orders</a>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="content-container">
        <div class="page-header">
            <h1><i class="fas fa-shopping-bag" style="color: #facc15;"></i> My Order History</h1>
            <p>Track your physical book deliveries and past purchases.</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="order-card">
                    <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="Cover">
                    <div class="order-details">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="order-info-line">
                            <i class="far fa-calendar-alt"></i> <strong>Ordered:</strong> <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                        </p>
                        <p class="order-info-line">
                            <i class="fas fa-wallet"></i> <strong>Paid:</strong> <span class="price-highlight">₹<?php echo $row['amount']; ?></span>
                        </p>
                        <?php if(!empty($row['razorpay_order_id'])): ?>
                            <div class="txn-text">TXN: <?php echo htmlspecialchars($row['razorpay_order_id']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="status-container">
                        <span class="status-badge status-<?php echo $row['payment_status']; ?>">
                            <?php 
                                // Auto-icon based on status
                                if($row['payment_status'] == 'Pending') echo '<i class="fas fa-clock"></i>';
                                else if($row['payment_status'] == 'Success') echo '<i class="fas fa-check-circle"></i>';
                                else if($row['payment_status'] == 'Shipped') echo '<i class="fas fa-truck-fast"></i>';
                                else if($row['payment_status'] == 'Delivered') echo '<i class="fas fa-box-open"></i>';
                                else echo '<i class="fas fa-circle"></i>';
                            ?>
                            <?php echo $row['payment_status']; ?>
                        </span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h2 style="color: #fff; margin-bottom: 10px;">No orders yet</h2>
                <p>Looks like you haven't ordered any physical hard copies yet. Buy your first hard copy today!</p>
                <a href="user_dashboard.php" class="btn-browse"><i class="fas fa-compass"></i> Browse Library</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="site-footer">
        <div class="nav-container" style="justify-content: center;">
            <p>&copy; <?php echo date("Y"); ?> <span class="footer-logo">Book Enthusiast</span>. Crafted for Passionate Readers.</p>
        </div>
    </footer>

</body>
</html> 