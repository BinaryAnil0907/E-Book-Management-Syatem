<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$status_msg = "";

// Fetch Current User Data
$stmt = $conn->prepare("SELECT username, email, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Profile Update Logic
if (isset($_POST['update_profile'])) {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    
    $update = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $update->bind_param("ssi", $username, $email, $user_id);
    
    if ($update->execute()) {
        $_SESSION['username'] = $username;
        $status_msg = "<p style='color:#4ade80;'>Profile updated successfully!</p>";
    }
}

// Password Change Logic
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Verify current password (assuming it's hashed)
    if (password_verify($old_pass, $user_data['password'])) {
        if ($new_pass === $confirm_pass) {
            $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $hashed_pass, $user_id);
            $upd->execute();
            $status_msg = "<p style='color:#4ade80;'>Password changed successfully!</p>";
        } else {
            $status_msg = "<p style='color:#f87171;'>New passwords do not match!</p>";
        }
    } else {
        $status_msg = "<p style='color:#f87171;'>Incorrect current password!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { background: #1b212c; color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; padding: 3rem; }
        .card { max-width: 500px; margin: 0 auto; background: #252d3a; padding: 2rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); }
        h2 { color: #facc15; margin-bottom: 1.5rem; }
        .group { margin-bottom: 1.2rem; }
        label { display: block; margin-bottom: 5px; color: #94a3b8; }
        input { width: 100%; padding: 12px; background: #1b212c; border: 1px solid #334155; border-radius: 10px; color: #fff; outline: none; }
        button { width: 100%; padding: 12px; background: #facc15; color: #000; border: none; border-radius: 10px; font-weight: 800; cursor: pointer; margin-top: 10px; }
        hr { border: 0; border-top: 1px solid #334155; margin: 2rem 0; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Account Settings</h2>
        <?php echo $status_msg; ?>
        
        <form method="POST">
            <div class="group"><label>Username</label><input type="text" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required></div>
            <div class="group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required></div>
            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <hr>

        <h2>Security</h2>
        <form method="POST">
            <div class="group"><label>Current Password</label><input type="password" name="old_password" required></div>
            <div class="group"><label>New Password</label><input type="password" name="new_password" required></div>
            <div class="group"><label>Confirm New Password</label><input type="password" name="confirm_password" required></div>
            <button type="submit" name="change_password">Change Password</button>
        </form>
        
        <a href="user_dashboard.php" style="display:block; text-align:center; color:#94a3b8; margin-top:1.5rem; text-decoration:none;">Back to Library</a>
    </div>
</body>
</html>