<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// --- LOGIC SECTION ---

// Handle delete book
if (isset($_GET['delete_book'])) {
    $id = intval($_GET['delete_book']);
    $conn->query("DELETE FROM books WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit;
}

// Handle delete user
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];
    $conn->query("UPDATE orders SET payment_status='$new_status' WHERE id=$order_id");
    header("Location: admin_dashboard.php?msg=Status Updated");
    exit;
}

// --- ANALYTICS QUERIES ---

// 1. Total Books
$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];

// 2. Total Users
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

// 3. Total Revenue (Sirf successful payments ka)
$rev_res = $conn->query("SELECT SUM(amount) as total FROM orders WHERE payment_status IN ('Success', 'Shipped', 'Delivered')");
$total_revenue = $rev_res->fetch_assoc()['total'] ?? 0;

// 4. Top 3 Selling Books
$top_books = $conn->query("SELECT b.title, COUNT(o.id) as sales FROM orders o 
                           JOIN books b ON o.book_id = b.id 
                           GROUP BY o.book_id ORDER BY sales DESC LIMIT 3");

// --- DATA FETCHING ---
$books = $conn->query("SELECT * FROM books ORDER BY id DESC");
$orders = $conn->query("SELECT o.*, u.username, b.title FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        JOIN books b ON o.book_id = b.id ORDER BY o.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Book Enthusiast</title>
    <link rel="stylesheet" href="admin_dashboard.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { margin: 0; color: #7f8c8d; font-size: 14px; text-transform: uppercase; }
        .stat-card p { margin: 10px 0 0; font-size: 28px; font-weight: bold; color: #2c3e50; }
        .top-selling { background: #fff; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .badge { background: #34495e; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 12px; }
        .btn-add { background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .logout { color: #e74c3c; font-weight: bold; text-decoration: none; margin-left: 15px; }
    </style>
</head>
<body>
    <div class="container" style="width: 1000px; margin: 40px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #f1f1f1; padding-bottom: 20px;">
            <div>
                <h1 style="margin: 0; color: #2c3e50;">Admin Panel 🎩</h1>
                <p style="margin: 5px 0 0; color: #7f8c8d;">Welcome back, <strong><?php echo $_SESSION['admin_user']; ?></strong></p>
            </div>
            <div>
                <a href="add_book.php" class="btn-add">➕ Add New Book</a>
                <a href="admin_logout.php" class="logout">Logout</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Books</h3>
                <p><?php echo $total_books; ?></p>
            </div>
            <div class="stat-card">
                <h3>Registered Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="stat-card" style="border-bottom: 4px solid #27ae60;">
                <h3>Total Revenue</h3>
                <p>₹<?php echo number_format($total_revenue, 2); ?></p>
            </div>
        </div>

        <div class="top-selling">
            <h3 style="margin-top: 0; font-size: 16px; color: #2c3e50;">🔥 Top Selling Books</h3>
            <div style="display: flex; gap: 15px; margin-top: 10px;">
                <?php while($tb = $top_books->fetch_assoc()) { ?>
                    <div style="background: #f9f9f9; padding: 10px 15px; border-radius: 8px; border: 1px solid #eee;">
                        <strong><?php echo htmlspecialchars($tb['title']); ?></strong> 
                        <span class="badge"><?php echo $tb['sales']; ?> Sold</span>
                    </div>
                <?php } ?>
            </div>
        </div>

        <h3 style="border-left: 5px solid #e67e22; padding-left: 10px;">Recent Orders</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 40px;">
            <tr style="background: #f8f9fa;">
                <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: left;">Customer</th>
                <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: left;">Book</th>
                <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: left;">Amount</th>
                <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: left;">Status Control</th>
            </tr>
            <?php while ($ord = $orders->fetch_assoc()) { ?>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">
                        <strong><?php echo htmlspecialchars($ord['username']); ?></strong><br>
                        <small style="color: #666;"><?php echo $ord['phone']; ?></small>
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($ord['title']); ?></td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee; font-weight: bold;">₹<?php echo $ord['amount']; ?></td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                            <select name="new_status" style="padding:5px; border-radius:4px; border: 1px solid #ccc;">
                                <option value="Pending" <?php if($ord['payment_status']=='Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Success" <?php if($ord['payment_status']=='Success') echo 'selected'; ?>>Success</option>
                                <option value="Shipped" <?php if($ord['payment_status']=='Shipped') echo 'selected'; ?>>Shipped</option>
                                <option value="Delivered" <?php if($ord['payment_status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                            </select>
                            <button type="submit" name="update_status" style="padding:5px 10px; background:#34495e; color: white; border: none; border-radius:4px; cursor: pointer;">Update</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <h3>Manage Inventory</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f8f9fa;">
                <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: left;">Book Info</th>
                <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: left;">Price</th>
                <th style="padding: 12px; border-bottom: 2px solid #dee2e6; text-align: left;">Action</th>
            </tr>
            <?php while ($row = $books->fetch_assoc()) { ?>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">
                        <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                        <small>By <?php echo htmlspecialchars($row['author']); ?></small>
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">₹<?php echo $row['price']; ?></td>
                    <td style="padding: 12px; border-bottom: 1px solid #eee;">
                        <a href="<?php echo $row['file_path']; ?>" target="_blank" style="color:#3498db; text-decoration: none;">View</a> | 
                        <a href="admin_dashboard.php?delete_book=<?php echo $row['id']; ?>" style="color:#e74c3c; text-decoration: none;" onclick="return confirm('Delete this book?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>