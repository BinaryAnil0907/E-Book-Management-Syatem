<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        // Verify the hashed password
        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_user'] = $username;
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $message = "<div class='error-msg'><i class='fas fa-lock'></i> Authentication failed. Invalid password.</div>";
        }
    } else {
        $message = "<div class='error-msg'><i class='fas fa-user-slash'></i> Administrator account not found.</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Secure Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: #1b212c;
            color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .login-wrapper {
            background: linear-gradient(145deg, #252d3a, #1b212c);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255,255,255,0.05);
            position: relative;
        }

        .login-wrapper::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #f1c40f, #f39c12);
            border-radius: 20px 20px 0 0;
        }

        .header-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header-title .icon-container {
            width: 70px;
            height: 70px;
            background: rgba(250, 204, 21, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            border: 1px solid rgba(250, 204, 21, 0.2);
        }

        .header-title .icon-container i {
            font-size: 2rem;
            color: #facc15;
        }

        .header-title h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .header-title p {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 16px;
            top: 40px;
            color: #94a3b8;
            transition: 0.3s;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #e2e8f0;
            font-size: 0.9rem;
        }

        .input-field {
            width: 100%;
            padding: 14px 15px 14px 45px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
        }

        .input-field:focus {
            border-color: #facc15;
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 10px rgba(250, 204, 21, 0.1);
        }

        .input-field:focus + i, .input-field:valid + i {
            color: #facc15;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #f1c40f, #f39c12);
            color: #0f172a;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(250, 204, 21, 0.2);
            margin-top: 0.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(250, 204, 21, 0.4);
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .back-link:hover {
            color: #f1f5f9;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="header-title">
            <div class="icon-container">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>System Administration</h1>
            <p>Enter your credentials to access the backend</p>
        </div>

        <?php if (!empty($message)) echo $message; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Admin Username</label>
                <input type="text" name="username" class="input-field" placeholder="Enter username" required>
                <i class="fas fa-user"></i>
            </div>

            <div class="input-group">
                <label>Secure Password</label>
                <input type="password" name="password" class="input-field" placeholder="Enter password" required>
                <i class="fas fa-lock"></i>
            </div>

            <button type="submit" class="btn-submit">Authenticate <i class="fas fa-sign-in-alt"></i></button>
        </form>
        
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Return to Public Website</a>
    </div>

</body>
</html>