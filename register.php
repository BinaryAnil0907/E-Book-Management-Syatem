<?php
session_start();
include 'db.php';

// Session se message nikal kar variable mein daalo aur phir session clear kar do
$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Ek baar dikhane ke baad hata do
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if already logged in
    if (isset($_SESSION['user_id'])) {
        $_SESSION['message'] = "You are already logged in. Logout before registering again.";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $_SESSION['message'] = "Email already registered!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Registration successful! <a href='login.php' style='color:#fff; text-decoration:underline;'>Login here</a>";
            } else {
                $_SESSION['message'] = "Error: " . $conn->error;
            }
        }
    }
    
    // PRG Pattern: POST request aane ke baad page ko wapas reload (GET request) kar do
    // Isse 'Confirm Form Resubmission' wala error hamesha ke liye chala jayega
    header("Location: register.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Book Enthusiast</title>
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
            background: #1b212c; /* Exact Matte Dark Background */
            color: #f1f5f9;
            height: 100vh;
            overflow: hidden;
        }

        .auth-container {
            display: flex;
            height: 100vh;
        }

        /* Left Side Image Panel */
        .auth-image {
            flex: 1;
            background: linear-gradient(to right, rgba(27, 33, 44, 0.4), rgba(27, 33, 44, 1)), 
                        url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80') center/cover;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            position: relative;
        }

        .auth-image h2 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 1rem;
            line-height: 1.1;
            z-index: 2;
        }

        .auth-image h2 span {
            color: #facc15; /* Gold accent */
        }

        .auth-image p {
            font-size: 1.2rem;
            color: #cbd5e1;
            max-width: 400px;
            z-index: 2;
        }

        /* Right Side Form Panel */
        .auth-form-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #1b212c;
            padding: 2rem;
            overflow-y: auto;
        }

        .auth-card {
            background: rgba(37, 45, 58, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 3rem;
            border-radius: 20px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h3 {
            font-size: 2rem;
            font-weight: 700;
            color: #facc15;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        /* Form Inputs */
        .input-group {
            margin-bottom: 1.2rem;
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
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

        /* Submit Button */
        .btn-submit {
            width: 100%;
            background: #facc15;
            color: #0f172a;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(250, 204, 21, 0.2);
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background: #eab308;
            box-shadow: 0 0 25px rgba(250, 204, 21, 0.4);
            transform: translateY(-2px);
        }

        /* Links & Messages */
        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #94a3b8;
        }

        .auth-links a {
            color: #facc15;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .msg-box {
            background: rgba(250, 204, 21, 0.1);
            border: 1px solid rgba(250, 204, 21, 0.3);
            color: #facc15;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(0,0,0,0.5);
            padding: 8px 18px;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            transition: 0.3s;
        }

        .back-home:hover {
            background: #facc15;
            color: #0f172a;
        }

        /* Responsive */
        @media (max-width: 968px) {
            .auth-container { flex-direction: column; height: auto; }
            .auth-image { display: none; }
            body { overflow: auto; }
            .auth-form-section { height: 100vh; padding: 1rem; }
            .auth-card { padding: 2rem; }
        }
    </style>
</head>
<body>

    <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i> Back to Home</a>

    <div class="auth-container">
        <div class="auth-image">
            <h2>Join Our <br><span>Reading</span> Community.</h2>
            <p>Create an account to track your orders, access your digital library, and save your favorite books.</p>
        </div>

        <div class="auth-form-section">
            <div class="auth-card">
                <div class="auth-header">
                    <h3>Create Account</h3>
                    <p>Start your reading journey today</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="msg-box">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="input-field" placeholder="Full Name" required>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="input-field" placeholder="Email Address" required>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="input-field" placeholder="Password" required>
                    </div>

                    <button type="submit" class="btn-submit">Sign Up</button>
                </form>

                <div class="auth-links">
                    Already have an account? <a href="login.php">Log In Here</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>