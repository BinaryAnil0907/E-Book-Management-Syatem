<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get Cart Total and Items
$cart_items = $conn->query("SELECT c.*, b.title, b.price FROM cart c JOIN books b ON c.book_id = b.id WHERE c.user_id = $user_id");
$total_amount = 0;
$book_names = [];

while($item = $cart_items->fetch_assoc()){
    $total_amount += ($item['price'] * $item['quantity']);
    $book_names[] = $item['title'];
}

if($total_amount <= 0) {
    header("Location: cart.php?msg=EmptyCart");
    exit;
}

// Razorpay Settings
$api_key = "rzp_test_SPRbM48uGd6FEp"; // <--- RAZORPAY KEY 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Processing Payment...</title>
    <style>
        body { background: #1b212c; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; }
        .loader { text-align: center; }
    </style>
</head>
<body>

    <div class="loader">
        <h2>Initialising Secure Payment...</h2>
        <p>Please do not refresh or close this window.</p>
        <button id="pay-btn" style="background:#facc15; border:none; padding:15px 30px; border-radius:10px; font-weight:800; cursor:pointer; margin-top:20px;">CLICK TO PAY ₹<?php echo $total_amount; ?></button>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    var options = {
        "key": "<?php echo $api_key; ?>", 
        "amount": "<?php echo $total_amount * 100; ?>", // Amount in ind
        "currency": "INR",
        "name": "Book Enthusiast",
        "description": "Payment for: <?php echo implode(', ', $book_names); ?>",
        "image": "https://your-logo-url.com/logo.png",
        "handler": function (response){
        
            window.location.href = "verify_payment.php?razorpay_payment_id=" + response.razorpay_payment_id + "&total=" + <?php echo $total_amount; ?>;
        },
        "prefill": {
            "name": "<?php echo $username; ?>",
            "email": "user@example.com"
        },
        "theme": {
            "color": "#facc15"
        }
    };
    var rzp1 = new Razorpay(options);
    
    // Auto open Razorpay on load
    window.onload = function(){
        document.getElementById('pay-btn').onclick = function(e){
            rzp1.open();
            e.preventDefault();
        }
    };
    </script>
</body>
</html>