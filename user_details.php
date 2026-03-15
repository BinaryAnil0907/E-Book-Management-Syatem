<?php
session_start();
include 'db.php';

// Authentication Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Fetch User Profile Data
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_res = $user_query->get_result();
$user_data = $user_res->fetch_assoc();

if (!$user_data) {
    die("<div style='color:white; background:#0f172a; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'><h1>User profile not found in database.</h1></div>");
}

// 2. Fetch User's Order History
$orders_query = $conn->prepare("SELECT o.*, b.title as book_title FROM orders o 
                                JOIN books b ON o.book_id = b.id 
                                WHERE o.user_id = ? ORDER BY o.id DESC");
$orders_query->bind_param("i", $user_id);
$orders_query->execute();
$orders_res = $orders_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile - <?php echo htmlspecialchars($user_data['username']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #facc15;
            --bg: #0b0f19;
            --card: #161b2a;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg); color: #f1f5f9; padding: 4rem; }

        .profile-container { max-width: 1000px; margin: 0 auto; }

        /* Header / Top Nav */
        .top-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; }
        .btn-back { 
            text-decoration: none; color: #fff; background: rgba(255,255,255,0.05); 
            padding: 12px 20px; border-radius: 12px; font-weight: 600; 
            border: 1px solid rgba(255,255,255,0.1); transition: 0.3s;
        }
        .btn-back:hover { background: var(--primary); color: #000; }

        /* User Identity Card */
        .user-card { 
            background: var(--card); border-radius: 30px; padding: 3rem; 
            border: 1px solid rgba(255,255,255,0.05); display: flex; 
            align-items: center; gap: 3rem; margin-bottom: 3rem;
        }
        .avatar-large { 
            width: 150px; height: 150px; background: rgba(250, 204, 21, 0.1); 
            border-radius: 40px; display: flex; align-items: center; 
            justify-content: center; font-size: 4rem; color: var(--primary);
            border: 2px solid rgba(250, 204, 21, 0.2);
        }
        .user-info h1 { font-size: 2.8rem; font-weight: 800; margin-bottom: 10px; }
        .user-info p { font-size: 1.2rem; color: var(--text-muted); margin-bottom: 5px; }
        .badge-active { background: rgba(34, 197, 94, 0.1); color: #4ade80; padding: 6px 15px; border-radius: 10px; font-weight: 800; font-size: 0.9rem; text-transform: uppercase; }

        /* Details Grid */
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem; }
        .detail-item { background: rgba(30, 41, 59, 0.4); padding: 2rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); }
        .detail-item h4 { color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 1px; }
        .detail-item p { font-size: 1.4rem; font-weight: 700; }

        /* Activity / Orders Table */
        .activity-box { background: var(--card); border-radius: 30px; padding: 3rem; border: 1px solid rgba(255,255,255,0.05); }
        .activity-box h3 { font-size: 1.8rem; margin-bottom: 2rem; border-left: 8px solid var(--primary); padding-left: 20px; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--text-muted); font-size: 1rem; border-bottom: 2px solid rgba(255,255,255,0.05); }
        td { padding: 20px 15px; font-size: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.02); }
        .status-tag { font-size: 0.85rem; font-weight: 800; color: var(--primary); text-transform: uppercase; }

    </style>
</head>
<body>

    <div class="profile-container">
        
        <div class="top-nav">
            <a href="admin_dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <p style="color: var(--text-muted);">Admin Control / User Records</p>
        </div>

        <div class="user-card">
            <div class="avatar-large">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="user-info">
                <h1><?php echo htmlspecialchars($user_data['username']); ?></h1>
                <p><i class="fas fa-envelope" style="margin-right: 10px;"></i> <?php echo htmlspecialchars($user_data['email']); ?></p>
                <p><i class="fas fa-id-card-alt" style="margin-right: 10px;"></i> System User #<?php echo $user_id; ?></p>
                <span class="badge-active">Verified Member</span>
            </div>
        </div>

        <div class="details-grid">
            <div class="detail-item">
                <h4>Registration Email</h4>
                <p><?php echo htmlspecialchars($user_data['email']); ?></p>
            </div>
            <div class="detail-item">
                <h4>Account Status</h4>
                <p style="color: #4ade80;">Active & Operational</p>
            </div>
        </div>

        <div class="activity-box">
            <h3>Recent Purchase History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Product Title</th>
                        <th>Amount Paid</th>
                        <th>Transaction Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders_res->num_rows > 0) { 
                        while ($order = $orders_res->fetch_assoc()) { ?>
                            <tr>
                                <td>#ORD-<?php echo $order['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($order['book_title']); ?></strong></td>
                                <td style="color: var(--primary); font-weight: 800;">₹<?php echo number_format($order['amount'], 2); ?></td>
                                <td><span class="status-tag"><?php echo $order['payment_status']; ?></span></td>
                            </tr>
                    <?php } } else { ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 3rem;">No purchases found for this user.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>