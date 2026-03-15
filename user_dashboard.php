<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle search logic
$search_query = "";
if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $search_query = $conn->real_escape_string($_GET['query']);
    $result = $conn->query("SELECT * FROM books WHERE title LIKE '%$search_query%' OR author LIKE '%$search_query%'");
} else {
    $result = $conn->query("SELECT * FROM books ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eBook Library - Book Enthusiast</title>
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

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: #e2e8f0;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-links a:hover, .nav-links a.active {
            color: #facc15;
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444 !important;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            transition: 0.3s;
        }

        .btn-logout:hover {
            background: #ef4444;
            color: #fff !important;
        }

        /* Content Area */
        .content-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .dashboard-header h1 {
            font-size: 2.8rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 10px;
        }

        .dashboard-header p {
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .dashboard-header strong {
            color: #facc15;
        }

        /* Search Bar */
        .search-bar {
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-bar input[type="text"] {
            width: 100%;
            padding: 16px 25px;
            padding-right: 120px;
            background: rgba(37, 45, 58, 0.6);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 50px;
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            transition: 0.3s;
        }

        .search-bar input:focus {
            border-color: #facc15;
            box-shadow: 0 0 15px rgba(250, 204, 21, 0.2);
            background: rgba(37, 45, 58, 0.9);
        }

        .search-bar button {
            position: absolute;
            right: 5px;
            top: 5px;
            bottom: 5px;
            padding: 0 25px;
            background: #facc15;
            color: #0f172a;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
        }

        .search-bar button:hover {
            background: #eab308;
            box-shadow: 0 0 15px rgba(250, 204, 21, 0.4);
        }

        /* Book Grid */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
        }

        .book-card {
            background: #252d3a;
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 1.5rem;
            transition: 0.3s;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .book-card:hover {
            transform: translateY(-8px);
            border-color: rgba(250, 204, 21, 0.4);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .book-icon img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.4);
        }

        .book-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
        }

        .book-card p.author {
            color: #94a3b8;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .price-badge {
            position: absolute;
            top: 2.5rem;
            right: 2.5rem;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.9rem;
            background: #facc15;
            color: #0f172a;
            box-shadow: 0 4px 10px rgba(250, 204, 21, 0.3);
        }

        .price-badge.free {
            background: #22c55e;
            color: #fff;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3);
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: auto;
            margin-bottom: 1.5rem;
        }

        .btn-custom {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }

        .btn-read {
            background: rgba(255,255,255,0.05);
            color: #facc15;
            border: 1px solid rgba(250, 204, 21, 0.3);
        }

        .btn-read:hover {
            background: rgba(250, 204, 21, 0.1);
        }

        .btn-order {
            background: #facc15;
            color: #0f172a;
            box-shadow: 0 4px 15px rgba(250, 204, 21, 0.2);
        }

        .btn-order:hover {
            background: #eab308;
            transform: translateY(-2px);
        }

        /* Dark Comment Section */
        .comment-box {
            background: rgba(15, 23, 42, 0.5);
            padding: 15px;
            border-radius: 10px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .comment-box h4 {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .comment {
            font-size: 0.85rem;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #cbd5e1;
        }

        .comment:last-of-type {
            border-bottom: none;
        }

        .comment b {
            color: #facc15;
        }

        .comment-form {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .comment-form input[type="text"] {
            flex: 1;
            padding: 8px 12px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 6px;
            color: #fff;
            font-size: 0.85rem;
            outline: none;
        }

        .comment-form input[type="text"]:focus {
            border-color: #facc15;
        }

        .comment-form button {
            padding: 8px 15px;
            background: #facc15;
            color: #0f172a;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .comment-form button:hover {
            background: #eab308;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-container { flex-direction: column; gap: 1rem; }
            .nav-links { flex-wrap: wrap; justify-content: center; }
            .search-bar input[type="text"] { padding-right: 25px; }
            .search-bar button { position: static; width: 100%; margin-top: 10px; border-radius: 50px; padding: 12px; }
            .button-group { flex-direction: column; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Book Enthusiast</a>
            <div class="nav-links">
                <a href="user_dashboard.php" class="active"><i class="fas fa-book-open"></i> Library</a>
                <a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="content-container">
        
        <header class="dashboard-header">
            <h1>Digital & Physical Library</h1>
            <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! Ready for your next adventure?</p>
        </header>

        <div class="search-bar">
            <form method="GET" action="user_dashboard.php">
                <input type="text" name="query" placeholder="Search for books or authors..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>

        <div class="book-grid">
            <?php if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) { 
                    // Price Check Logic for UI
                    $price = floatval($row['price']);
                    $is_free = ($price <= 0);
            ?>
                <div class="book-card">
                    <?php if($is_free): ?>
                        <div class="price-badge free">FREE</div>
                    <?php else: ?>
                        <div class="price-badge">₹<?php echo htmlspecialchars($row['price']); ?></div>
                    <?php endif; ?>

                    <div class="book-icon">
                        <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="Cover">
                    </div>

                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="author">By <?php echo htmlspecialchars($row['author']); ?></p>
                    
                    <div class="button-group">
                        <a class="btn-custom btn-read" href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">
                            <i class="fas fa-book-reader"></i> Read PDF
                        </a>
                        <a class="btn-custom btn-order" href="checkout.php?book_id=<?php echo $row['id']; ?>">
                            <i class="fas fa-truck"></i> Order Hard Copy
                        </a>
                    </div>

                    <div class="comment-box">
                        <h4><i class="fas fa-comments"></i> Recent Comments</h4>
                        
                        <?php
                        $book_id = $row['id'];
                        $csql = "SELECT c.comment, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.book_id = $book_id ORDER BY c.id DESC LIMIT 2";
                        $cresult = $conn->query($csql);
                        
                        if ($cresult && $cresult->num_rows > 0) {
                            while ($c = $cresult->fetch_assoc()) {
                                echo "<div class='comment'><b>" . htmlspecialchars($c['username']) . "</b>: " . htmlspecialchars($c['comment']) . "</div>";
                            }
                        } else { 
                            echo "<div class='comment' style='color:#64748b; font-style:italic;'>No comments yet. Be the first!</div>"; 
                        }
                        ?>
                        
                        <form method="POST" action="add_comment.php" class="comment-form">
                            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                            <input type="text" name="comment" placeholder="Write a review..." required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            <?php } } else { 
                echo "<div style='grid-column: 1/-1; text-align:center; padding: 4rem; background: rgba(37, 45, 58, 0.5); border-radius: 16px;'>
                        <i class='fas fa-search' style='font-size: 3rem; color: #64748b; margin-bottom: 1rem;'></i>
                        <h2 style='color: #fff;'>No books found</h2>
                        <p style='color: #94a3b8;'>Try searching with a different keyword.</p>
                      </div>"; 
            } ?>
        </div>
    </div>
</body>
</html>