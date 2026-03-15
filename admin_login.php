<?php
session_start();
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_user'] = $username;
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $message = "Invalid password!";
        }
    } else {
        $message = "Admin not found!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin_login.css">

</head>

<body>
    <div class="container">
        <h1>Book Enthusiast Management</h1>
        <h2>Admin Login</h2>
        <form method="post">
            Username: <input type="text" name="username" required><br><br>
            Password: <input type="password" name="password" required><br><br>
            <button type="submit">Login</button>
        </form>
        <p style="color:red;"><?php echo $message; ?></p>
    </div>
</body>

</html>