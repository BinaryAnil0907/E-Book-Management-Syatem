<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Payment Method</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0f172a; color: #fff; height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .method-container { background: #1b212c; padding: 3rem; border-radius: 24px; border: 1px solid rgba(255,255,255,0.05); text-align: center; width: 100%; max-width: 500px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .option-card { background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 16px; margin-bottom: 1rem; border: 1px solid rgba(255,255,255,0.05); cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 20px; text-decoration: none; color: #fff; }
        .option-card:hover { background: rgba(250, 204, 21, 0.1); border-color: #facc15; transform: translateY(-5px); }
        .option-card i { font-size: 1.5rem; color: #facc15; }
        .option-card div { text-align: left; }
        .option-card h3 { margin: 0; font-size: 1.1rem; }
        .option-card p { margin: 0; font-size: 0.8rem; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="method-container">
        <h1 style="margin-bottom: 2rem;">Payment Method</h1>
        
        <a href="checkout_cart.php" class="option-card">
            <i class="fas fa-credit-card"></i>
            <div>
                <h3>Pay Online</h3>
                <p>Debit/Credit Card, UPI, Netbanking</p>
            </div>
        </a>

        <a href="place_cod_order.php" class="option-card" onclick="return confirm('Confirm Cash on Delivery order?')">
            <i class="fas fa-truck"></i>
            <div>
                <h3>Cash on Delivery</h3>
                <p>Pay when you receive your book</p>
            </div>
        </a>

        <a href="cart.php" style="color: #94a3b8; text-decoration: none; font-size: 0.8rem; display: block; margin-top: 1rem;">Back to Cart</a>
    </div>
</body>
</html>