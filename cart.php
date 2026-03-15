<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];
$cart_items = $conn->query("SELECT c.*, b.title, b.price, b.img FROM cart c JOIN books b ON c.book_id = b.id WHERE c.user_id = $user_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart | Book Enthusiast</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { transition: all 0.3s ease; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
        
        /* Cart Wrapper Entrance */
        .cart-wrapper { width: 100%; max-width: 900px; background: #1b212c; border-radius: 28px; padding: 3.5rem; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 40px 100px -20px rgba(0,0,0,0.7); animation: zoomIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
        @keyframes zoomIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        
        .nav-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; }
        .back-btn { color: #94a3b8; text-decoration: none; font-weight: 700; font-size: 0.85rem; }
        .back-btn:hover { color: #facc15; transform: translateX(-5px); }

        .cart-item { display: flex; align-items: center; gap: 25px; background: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 20px; margin-bottom: 1.2rem; border: 1px solid rgba(255,255,255,0.03); }
        .cart-item:hover { background: rgba(255,255,255,0.06); transform: scale(1.01); border-color: rgba(250, 204, 21, 0.2); }
        .cart-item img { width: 70px; height: 100px; object-fit: cover; border-radius: 10px; }
        
        .qty-control { display: flex; align-items: center; gap: 12px; }
        .q-btn { background: #252d3a; color: #fff; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 10px; text-decoration: none; border: 1px solid rgba(255,255,255,0.1); font-weight: bold; }
        .q-btn:hover { background: #facc15; color: #000; transform: translateY(-2px); }

        .price-col .amt { font-size: 1.3rem; font-weight: 800; color: #facc15; display: block; text-shadow: 0 0 10px rgba(250, 204, 21, 0.2); }
        .remove-item:hover { color: #ff4757; transform: scale(1.1); }

        /* Pay Button Shimmer Effect */
        .pay-btn { background: #facc15; color: #0f172a; padding: 1.2rem 3rem; border-radius: 15px; text-decoration: none; font-weight: 800; font-size: 1.1rem; position: relative; overflow: hidden; box-shadow: 0 10px 20px rgba(250, 204, 21, 0.2); }
        .pay-btn::before { content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent); transition: 0.5s; }
        .pay-btn:hover::before { left: 150%; }
        .pay-btn:hover { transform: translateY(-5px); background: #fff; box-shadow: 0 15px 30px rgba(255,255,255,0.2); }

        .total-info h2 { animation: slideUp 0.6s ease; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>
    <div class="cart-wrapper">
        <div class="nav-top">
            <a href="user_dashboard.php" class="back-btn"><i class="fas fa-chevron-left"></i> BACK TO LIBRARY</a>
            <div style="font-weight: 800; opacity: 0.5; letter-spacing: 2px;">SECURE CHECKOUT</div>
        </div>

        <h1 style="margin-bottom: 2rem; font-weight: 900;">Shopping Cart</h1>

        <?php 
        $grand_total = 0;
        if ($cart_items->num_rows > 0): 
            while($item = $cart_items->fetch_assoc()): 
                $subtotal = $item['price'] * $item['quantity'];
                $grand_total += $subtotal;
        ?>
            <div class="cart-item">
                <img src="<?php echo $item['img']; ?>">
                <div class="item-details" style="flex: 1;">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <div class="qty-control">
                        <a href="manage_cart.php?action=update&book_id=<?php echo $item['book_id']; ?>&qty=<?php echo $item['quantity']-1; ?>" class="q-btn">-</a>
                        <span style="font-weight: 800; font-size: 1.1rem;"><?php echo $item['quantity']; ?></span>
                        <a href="manage_cart.php?action=update&book_id=<?php echo $item['book_id']; ?>&qty=<?php echo $item['quantity']+1; ?>" class="q-btn">+</a>
                    </div>
                </div>
                <div class="price-col" style="text-align: right;">
                    <span class="amt">₹<?php echo $subtotal; ?></span>
                    <a href="manage_cart.php?action=remove&book_id=<?php echo $item['book_id']; ?>" class="remove-item" style="color: #f87171; text-decoration: none; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">Remove</a>
                </div>
            </div>
        <?php endwhile; ?>

        <div class="checkout-box" style="margin-top: 3.5rem; padding-top: 2.5rem; border-top: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: flex-end;">
            <div class="total-info">
                <span style="color: #64748b; font-weight: 600;">TOTAL PAYABLE</span>
                <h2 style="font-size: 2.5rem; font-weight: 900;">₹<?php echo $grand_total; ?></h2>
            </div>
            <a href="payment_method.php" class="pay-btn">PROCEED TO PAY <i class="fas fa-arrow-right" style="margin-left: 10px;"></i></a>
        </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 0;">
                <i class="fas fa-shopping-basket" style="font-size: 4rem; opacity: 0.1; margin-bottom: 1.5rem;"></i>
                <h2 style="color: #475569;">Cart is empty</h2>
                <a href="user_dashboard.php" style="color: #facc15; text-decoration: none; font-weight: 800; margin-top: 1rem; display: inline-block;">GO SHOPPING</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>