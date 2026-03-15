<?php
session_start();
include 'db.php';

// Authentication Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// --- LOGIC SECTION ---

// Handle book deletion
if (isset($_GET['delete_book'])) {
    $id = intval($_GET['delete_book']);
    $conn->query("DELETE FROM books WHERE id=$id");
    header("Location: admin_dashboard.php?msg=BookDeleted");
    exit;
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: admin_dashboard.php?msg=UserRemoved");
    exit;
}

// Handle order status updates
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $conn->real_escape_string($_POST['new_status']);
    $conn->query("UPDATE orders SET payment_status='$new_status' WHERE id=$order_id");
    header("Location: admin_dashboard.php?msg=StatusUpdated");
    exit;
}

// --- ANALYTICS QUERIES ---
$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$rev_res = $conn->query("SELECT SUM(amount) as total FROM orders WHERE payment_status IN ('Success', 'Shipped', 'Delivered')");
$total_revenue = $rev_res->fetch_assoc()['total'] ?? 0;

// --- DATA FETCHING ---
$books = $conn->query("SELECT * FROM books ORDER BY id DESC");
$orders = $conn->query("SELECT o.*, u.username, b.title FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        JOIN books b ON o.book_id = b.id ORDER BY o.id DESC");
$users_list = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Admin Dashboard | Book Enthusiast</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #facc15;
            --bg: #0f172a;
            --text: #f1f5f9;
            --card-glass: rgba(30, 41, 59, 0.7);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        
        body { background: var(--bg); color: var(--text); display: flex; height: 100vh; overflow: hidden; }

        /* --- FIXED SIDEBAR --- */
        .sidebar {
            width: 280px; background: #151e2f; height: 100vh; position: fixed;
            left: 0; top: 0; padding: 2.5rem 1.5rem; border-right: 1px solid rgba(255,255,255,0.05);
            display: flex; flex-direction: column; z-index: 1000;
        }
        .brand { color: var(--primary); font-size: 1.8rem; font-weight: 800; margin-bottom: 1rem; text-align: center; text-decoration: none; }
        .brand span { color: #fff; }

        .admin-profile { text-align: center; padding: 1.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 2rem; }
        .admin-profile i { font-size: 3rem; color: var(--primary); margin-bottom: 10px; }
        .admin-profile p { font-size: 0.9rem; color: #94a3b8; font-weight: 600; }

        .nav-links { list-style: none; flex-grow: 1; }
        .nav-links li { margin-bottom: 8px; }
        .nav-links a { 
            display: flex; align-items: center; gap: 15px; padding: 12px 18px;
            color: #94a3b8; text-decoration: none; font-weight: 600; font-size: 1.1rem;
            border-radius: 12px; transition: 0.3s;
        }
        .nav-links a:hover, .nav-links a.active { background: rgba(250, 204, 21, 0.1); color: var(--primary); }

        .nav-item-logout {
            display: flex; align-items: center; gap: 15px; padding: 15px 20px;
            color: #f87171; text-decoration: none; font-weight: 700; font-size: 1.1rem;
            border-radius: 12px; transition: 0.3s; background: rgba(239, 68, 68, 0.05);
            margin-top: auto;
        }
        .nav-item-logout:hover { background: #ef4444; color: #fff; box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3); }

        /* --- CONTENT STRUCTURE --- */
        .main-content { margin-left: 280px; width: calc(100% - 280px); height: 100vh; display: flex; flex-direction: column; }
        
        /* Fixed Header */
        .header-fixed-part { padding: 3rem 3rem 1rem 3rem; background: var(--bg); z-index: 900; }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; }
        .header-top h1 { font-size: 2.5rem; font-weight: 800; }
        .header-top h1 span { color: var(--primary); }

        /* Scrollable Area */
        .scrollable-content { padding: 0 3rem 3rem 3rem; overflow-y: auto; flex: 1; }

        /* Stats & Panels */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 4rem; }
        .stat-card { background: var(--card-glass); padding: 2.5rem; border-radius: 28px; border: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(10px); position: relative; overflow: hidden; }
        .stat-card .number { font-size: 3.5rem; font-weight: 800; }
        .stat-card i { position: absolute; right: -15px; bottom: -15px; font-size: 6rem; color: rgba(255,255,255,0.03); transform: rotate(-15deg); }

        .panel { background: var(--card-glass); border-radius: 30px; padding: 3rem; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 4rem; }
        .panel h3 { font-size: 1.8rem; border-left: 8px solid var(--primary); padding-left: 20px; margin-bottom: 2.5rem; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 20px; font-size: 1rem; color: #64748b; text-transform: uppercase; border-bottom: 2px solid rgba(255,255,255,0.05); }
        td { padding: 25px 20px; font-size: 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.02); }

        /* Elements */
        .btn-action { font-size: 1.5rem; color: #94a3b8; transition: 0.3s; text-decoration: none; }
        .btn-action:hover { color: var(--primary); }
        .btn-delete:hover { color: #f87171; }

        select { background: #0f172a; color: #fff; border: 1px solid #334155; padding: 10px; border-radius: 10px; font-size: 1.1rem; outline: none; }
        .btn-set { background: var(--primary); border: none; padding: 10px 15px; border-radius: 10px; font-weight: 800; cursor: pointer; margin-left: 20px; margin-right: 15px; }

        .user-meta { display: flex; align-items: center; gap: 15px; }
        .user-icon { width: 50px; height: 50px; background: #334155; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: var(--primary); }
        .status-pill { padding: 6px 14px; border-radius: 10px; font-size: 0.9rem; font-weight: 800; text-transform: uppercase; background: rgba(250, 204, 21, 0.1); color: var(--primary); }

        .scrollable-content::-webkit-scrollbar { width: 8px; }
        .scrollable-content::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="admin_dashboard.php" class="brand">ADMIN<span>UI</span></a>
    <div class="admin-profile">
        <i class="fas fa-user-circle"></i>
        <p>Logged as: <?php echo $_SESSION['admin_user']; ?></p>
    </div>
    <ul class="nav-links">
        <li><a href="#stats" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
        <li><a href="#orders"><i class="fas fa-shopping-cart"></i> All Orders</a></li>
        <li><a href="#inventory"><i class="fas fa-book"></i> Library</a></li>
        <li><a href="#users"><i class="fas fa-users"></i> User Base</a></li>
    </ul>
    <a href="admin_logout.php" class="nav-item-logout"><i class="fas fa-power-off"></i> Logout Session</a>
</div>

<div class="main-content">
    <div class="header-fixed-part">
        <header class="header-top">
            <h1>Executive <span>Dashboard</span></h1>
            <a href="add_book.php" class="btn-set" style="padding: 15px 30px; text-decoration: none; color: #000; font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-plus"></i> ADD NEW BOOK
            </a>
        </header>
    </div>

    <div class="scrollable-content">
        <div class="stats-grid" id="stats">
            <div class="stat-card">
                <h4 style="color:#94a3b8; text-transform:uppercase;">Total Revenue</h4>
                <div class="number" style="color: var(--primary);">₹<?php echo number_format($total_revenue, 2); ?></div>
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-card">
                <h4 style="color:#94a3b8; text-transform:uppercase;">Total Users</h4>
                <div class="number"><?php echo $total_users; ?></div>
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-card">
                <h4 style="color:#94a3b8; text-transform:uppercase;">Total Books</h4>
                <div class="number"><?php echo $total_books; ?></div>
                <i class="fas fa-book-open"></i>
            </div>
        </div>

        <div class="panel" id="orders">
            <h3>Customer Transactions</h3>
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Status Control</th>
                        <th style="text-align:center;">View</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ord = $orders->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <div class="user-meta">
                                    <div class="user-icon"><i class="fas fa-user"></i></div>
                                    <div><strong><?php echo htmlspecialchars($ord['username']); ?></strong><br><small style="color: #64748b;"><?php echo $ord['phone']; ?></small></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($ord['title']); ?></td>
                            <td style="font-weight: 800; color: var(--primary);">₹<?php echo $ord['amount']; ?></td>
                            <td>
                                <form method="POST" style="display:flex; align-items:center;">
                                    <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                    <select name="new_status">
                                        <option value="Pending" <?php if($ord['payment_status']=='Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="Success" <?php if($ord['payment_status']=='Success') echo 'selected'; ?>>Success</option>
                                        <option value="Shipped" <?php if($ord['payment_status']=='Shipped') echo 'selected'; ?>>Shipped</option>
                                        <option value="Delivered" <?php if($ord['payment_status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-set">SET</button>
                                </form>
                            </td>
                            <td style="text-align:center;">
                                <a href="order_details.php?id=<?php echo $ord['id']; ?>" class="btn-action"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="panel" id="users">
            <h3>Registered User Base</h3>
            <table>
                <thead>
                    <tr>
                        <th>Identity</th>
                        <th>Contact Email</th>
                        <th>Access Level</th>
                        <th style="text-align:center;">View</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_list->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <div class="user-meta">
                                    <div class="user-icon" style="background: rgba(250, 204, 21, 0.1);"><i class="fas fa-id-badge"></i></div>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="status-pill">Active User</span></td>
                            <td style="text-align:center;">
                                <a href="user_details.php?id=<?php echo $user['id']; ?>" class="btn-action"><i class="fas fa-eye"></i></a>
                            </td>
                            <td style="text-align:center;">
                                <a href="admin_dashboard.php?delete_user=<?php echo $user['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete user?')"><i class="fas fa-user-minus"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="panel" id="inventory">
            <h3>Library Stock</h3>
            <table>
                <thead>
                    <tr>
                        <th>Book Info</th>
                        <th>Genre</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $books->fetch_assoc()) { ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['title']); ?></strong><br><small style="color:#64748b;">By <?php echo htmlspecialchars($row['author']); ?></small></td>
                            <td><span class="status-pill" style="background: rgba(56, 189, 248, 0.1); color: #38bdf8;"><?php echo htmlspecialchars($row['category'] ?? 'General'); ?></span></td>
                            <td style="font-weight: 800; color: var(--primary);">₹<?php echo $row['price']; ?></td>
                            <td>
                                <a href="edit_book.php?id=<?php echo $row['id']; ?>" class="btn-action" style="margin-right:15px;"><i class="fas fa-edit"></i></a>
                                <a href="admin_dashboard.php?delete_book=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete Book?')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>