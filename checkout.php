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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - Book Enthusiast</title>
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

        /* Checkout Layout Container */
        .checkout-container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .checkout-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .checkout-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
        }

        .checkout-header p {
            color: #94a3b8;
            font-size: 1.1rem;
            margin-top: 5px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 3rem;
            align-items: start;
        }

        /* Order Summary Card (Left Side) */
        .order-summary {
            background: linear-gradient(145deg, #252d3a, #1b212c);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }

        .order-summary::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #f1c40f, #f39c12);
        }

        .summary-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #facc15;
            margin-bottom: 1.5rem;
            border-bottom: 1px dashed rgba(255,255,255,0.1);
            padding-bottom: 10px;
        }

        .book-preview {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .book-preview img {
            width: 80px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .book-details {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .book-details h3 {
            font-size: 1.2rem;
            color: #fff;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .book-details p {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .price-breakdown {
            border-top: 1px dashed rgba(255,255,255,0.1);
            padding-top: 1.5rem;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #cbd5e1;
            font-size: 0.95rem;
        }

        .price-row.total {
            font-size: 1.3rem;
            font-weight: 800;
            color: #facc15;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* Shipping Form Card (Right Side) */
        .shipping-form {
            background: rgba(37, 45, 58, 0.4);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .form-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1.5rem;
        }

        /* Input Styling */
        .input-group {
            margin-bottom: 1.2rem;
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 16px;
            top: 16px; 
            color: #94a3b8;
            transition: 0.3s;
        }

        .input-field {
            width: 100%;
            padding: 14px 15px 14px 45px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            color: #fff;
            font-family: inherit;
            font-size: 0.95rem;
            transition: 0.3s;
            outline: none;
        }

        textarea.input-field {
            resize: vertical;
            min-height: 100px;
        }

        .input-field::placeholder {
            color: #64748b;
        }

        .input-field:focus {
            border-color: #facc15;
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 10px rgba(250, 204, 21, 0.1);
        }

        .input-field:focus + i, .input-field:valid + i {
            color: #facc15;
        }

        .input-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Payment Options */
        .payment-options {
            background: rgba(0,0,0,0.2);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1.5rem;
        }

        .payment-options label {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #e2e8f0;
            font-size: 1rem;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .payment-options label:last-child {
            margin-bottom: 0;
        }

        .payment-options input[type="radio"] {
            accent-color: #facc15;
            width: 18px;
            height: 18px;
        }

        /* Buttons */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #f1c40f, #f39c12);
            color: #0f172a;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(250, 204, 21, 0.3);
            margin-top: 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(250, 204, 21, 0.4);
        }

        .cancel-btn {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .cancel-btn:hover {
            color: #ef4444;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            .input-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Book Enthusiast</a>
        </div>
    </nav>

    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Secure Checkout <i class="fas fa-lock" style="font-size: 1.8rem; color: #facc15;"></i></h1>
            <p>Please review your order and enter shipping details</p>
        </div>

        <div class="checkout-grid">
            
            <div class="order-summary">
                <div class="summary-title">Order Summary</div>
                
                <div class="book-preview">
                    <img src="<?php echo htmlspecialchars($book['img']); ?>" alt="Book Cover">
                    <div class="book-details">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p>By <?php echo htmlspecialchars($book['author']); ?></p>
                    </div>
                </div>

                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Book Price</span>
                        <span>₹<?php echo htmlspecialchars($book['price']); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Shipping & Handling</span>
                        <span style="color: #22c55e;">FREE</span>
                    </div>
                    <div class="price-row total">
                        <span>Total Amount</span>
                        <span>₹<?php echo htmlspecialchars($book['price']); ?></span>
                    </div>
                </div>
            </div>

            <div class="shipping-form">
                <div class="form-title">Shipping Details</div>
                
                <form id="checkout-form" action="payment_process.php" method="POST">
                    <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                    <input type="hidden" name="amount" value="<?php echo $book['price']; ?>">
                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">

                    <div class="input-group">
                        <i class="fas fa-map-marker-alt"></i>
                        <textarea name="address" id="address" class="input-field" placeholder="Full Home Address" required></textarea>
                    </div>

                    <div class="input-row">
                        <div class="input-group">
                            <i class="fas fa-map-pin"></i>
                            <input type="text" name="pincode" id="pincode" class="input-field" pattern="[0-9]{6}" title="6 digit pincode" placeholder="Pincode (6 Digits)" required>
                        </div>

                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="text" name="phone" id="phone" class="input-field" pattern="[0-9]{10}" title="10 digit mobile number" placeholder="Mobile Number" required>
                        </div>
                    </div>

                    <div class="payment-options">
                        <label>
                            <input type="radio" name="payment_method" value="razorpay" checked>
                            <i class="fas fa-credit-card" style="color:#facc15;"></i> Pay Online (Razorpay)
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="cod">
                            <i class="fas fa-money-bill-wave" style="color:#4ade80;"></i> Cash on Delivery (COD)
                        </label>
                    </div>

                    <button type="button" id="pay-btn" class="btn-submit">
                        Place Order <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                <a href="user_dashboard.php" class="cancel-btn"><i class="fas fa-times"></i> Cancel Order & Go Back</a>
            </div>

        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    document.getElementById('pay-btn').onclick = function(e) {
        
        var address = document.getElementById('address').value;
        var pincode = document.getElementById('pincode').value;
        var phone = document.getElementById('phone').value;

        if(address === "" || pincode === "" || phone === "") {
            alert("Please fill all shipping details first!");
            return;
        }

        // Check selected payment method
        var paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

        if (paymentMethod === 'cod') {
            // if cod
            document.getElementById('checkout-form').submit();
        } else {
            // if Online (Razorpay) popup oprn
            var options = {
                "key": "rzp_test_SPRbM48uGd6FEp", // Replace with your Test Key if you want to use Online mode
                "amount": "<?php echo $book['price'] * 100; ?>", 
                "currency": "INR",
                "name": "Book Enthusiast",
                "description": "Purchase: <?php echo htmlspecialchars($book['title']); ?>",
                "image": "https://cdn-icons-png.flaticon.com/512/2232/2232688.png",
                "handler": function (response){
                    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                    document.getElementById('checkout-form').submit();
                },
                "prefill": {
                    "name": "<?php echo $_SESSION['username']; ?>",
                    "contact": phone
                },
                "theme": {
                    "color": "#facc15" 
                }
            };
            var rzp1 = new Razorpay(options);
            rzp1.open();
            e.preventDefault();
        }
    }
    </script>

</body>
</html>