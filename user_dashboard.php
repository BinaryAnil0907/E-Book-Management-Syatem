<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle search and category logic
$search_query = "";
$category_filter = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : 'All';

$sql = "SELECT * FROM books WHERE 1=1";
if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $search_query = $conn->real_escape_string($_GET['query']);
    $sql .= " AND (title LIKE '%$search_query%' OR author LIKE '%$search_query%')";
}
if ($category_filter !== 'All') {
    $sql .= " AND category = '$category_filter'";
}
$sql .= " ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eBook Library - Premium Experience</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #1b212c; color: #f1f5f9; line-height: 1.6; display: flex; flex-direction: column; min-height: 100vh; }

        /* Premium Navbar */
        .navbar { position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; background: rgba(27, 33, 44, 0.95); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(241, 196, 15, 0.2); padding: 1rem 0; }
        .nav-container { max-width: 100%; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 2rem; }
        .logo { font-size: 1.6rem; font-weight: 700; color: #facc15; text-decoration: none; transition: 0.3s; }
        .logo:hover { text-shadow: 0 0 10px rgba(250, 204, 21, 0.5); }
        .nav-links { display: flex; gap: 2rem; align-items: center; }
        .nav-links a { color: #e2e8f0; text-decoration: none; font-weight: 500; transition: 0.3s; }
        .nav-links a:hover { color: #facc15; transform: translateY(-2px); }

        /* User Profile Dropdown */
        .user-menu { position: relative; display: inline-block; }
        .user-trigger { background: rgba(255,255,255,0.05); padding: 8px 15px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.1); cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
        .user-trigger:hover { border-color: #facc15; background: rgba(255,255,255,0.1); }
        .user-dropdown { position: absolute; right: 0; top: 120%; background: #151e2f; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; width: 200px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); opacity: 0; visibility: hidden; transition: 0.3s; transform: translateY(10px); }
        .user-menu:hover .user-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
        .user-dropdown a { display: block; padding: 12px 20px; color: #f1f5f9; text-decoration: none; font-size: 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .user-dropdown a:hover { background: rgba(250, 204, 21, 0.1); color: #facc15; }

        /* SIDEBAR GENRES */
        .sidebar { width: 260px; background: #151e2f; height: 100vh; position: fixed; left: 0; top: 70px; padding: 2.5rem 1.5rem; border-right: 1px solid rgba(255,255,255,0.05); z-index: 900; }
        .sidebar h4 { color: #94a3b8; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; margin-bottom: 1.5rem; }
        .sidebar-links { display: flex; flex-direction: column; gap: 10px; }
        .cat-link-sidebar { padding: 12px 18px; border-radius: 12px; color: #94a3b8; text-decoration: none; font-weight: 600; transition: 0.3s; background: rgba(255,255,255,0.02); border-left: 3px solid transparent; }
        .cat-link-sidebar:hover, .cat-link-sidebar.active { background: #facc15; color: #0f172a; border-left: 3px solid #fff; padding-left: 22px; }

        /* MAIN CONTENT AREA */
        .content-container { margin-left: 260px; margin-top: 70px; width: calc(100% - 260px); padding: 3rem 2rem; display: flex; flex-direction: column; flex: 1; }
        .dashboard-header { text-align: center; margin-bottom: 3rem; width: 100%; animation: fadeInDown 0.8s ease; }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        /* Search Bar & Spacing */
        .search-bar { text-align: center; margin-bottom: 5rem; position: relative; width: 100%; max-width: 600px; margin-left: auto; margin-right: auto; }
        .search-bar input { width: 100%; padding: 16px 25px; padding-right: 120px; background: rgba(37, 45, 58, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 50px; color: #fff; font-size: 1rem; outline: none; transition: 0.3s; }
        .search-bar input:focus { border-color: #facc15; background: rgba(37, 45, 58, 0.9); box-shadow: 0 0 15px rgba(250, 204, 21, 0.2); }
        .search-bar button { position: absolute; right: 5px; top: 5px; bottom: 5px; padding: 0 25px; background: #facc15; border: none; border-radius: 50px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .search-bar button:hover { background: #eab308; box-shadow: 0 0 10px rgba(250, 204, 21, 0.4); }

        /* FIXED BOOK GRID SIZE LOGIC - NO RIGHT SPACE */
        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem; width: 100%; }

        /* Card Effects */
        .book-card { background: #252d3a; border: 1px solid rgba(255,255,255,0.05); border-radius: 16px; padding: 1.5rem; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; display: flex; flex-direction: column; animation: fadeInUp 0.5s ease backwards; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .book-card:hover { transform: translateY(-12px); border-color: rgba(250, 204, 21, 0.4); box-shadow: 0 20px 40px rgba(0,0,0,0.4); }

        .book-icon img { width: 100%; height: 320px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem; transition: 0.5s; }
        .book-card:hover .book-icon img { transform: scale(1.02); }

        .book-card h3 { font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 5px; }
        .book-card p.author { color: #94a3b8; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .cat-badge { font-size: 0.75rem; color: #facc15; background: rgba(250, 204, 21, 0.1); padding: 4px 10px; border-radius: 6px; font-weight: 800; text-transform: uppercase; margin-bottom: 1rem; display: inline-block; }

        /* STAR RATING HOVER EFFECT RESTORED */
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px; margin-bottom: 10px; }
        .star-rating input { display: none; }
        .star-rating label { color: #334155; font-size: 1.4rem; cursor: pointer; transition: 0.2s; }
        .star-rating label:hover, .star-rating label:hover ~ label { color: #facc15 !important; }
        .star-rating input:checked ~ label { color: #facc15; }

        .price-badge { position: absolute; top: 2.5rem; right: 2.5rem; padding: 0.5rem 1rem; border-radius: 30px; font-weight: 700; background: #facc15; color: #0f172a; z-index: 10; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .price-badge.free { background: #22c55e; color: #fff; }

        .button-group { display: flex; gap: 10px; margin-top: auto; margin-bottom: 1.5rem; }
        .btn-custom { flex: 1; padding: 10px; border-radius: 8px; text-align: center; text-decoration: none; font-weight: 600; font-size: 0.85rem; transition: 0.3s; display: flex; justify-content: center; align-items: center; }
        .btn-read { background: rgba(255,255,255,0.05); color: #facc15; border: 1px solid rgba(250, 204, 21, 0.3); }
        .btn-order { background: #facc15; color: #0f172a; }
        .btn-custom:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(250, 204, 21, 0.2); }

        /* Comment Section with Recent Stars */
        .comment-box { background: rgba(15, 23, 42, 0.5); padding: 15px; border-radius: 10px; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.05); }
        .comment { font-size: 0.8rem; padding: 5px 0; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .recent-stars { color: #facc15; font-size: 0.7rem; margin-bottom: 2px; }

        /* Footer */
        footer { background: #151e2f; padding: 2rem; border-top: 1px solid rgba(255,255,255,0.05); text-align: center; margin-left: 260px; width: calc(100% - 260px); }
        footer p { color: #94a3b8; font-size: 0.85rem; letter-spacing: 1px; }

        /* Modal */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); }
        .modal-content { background: #161b2a; margin: 5% auto; padding: 2.5rem; border-radius: 20px; width: 90%; max-width: 600px; border: 1px solid rgba(255,255,255,0.1); position: relative; animation: zoomIn 0.3s ease; }
        @keyframes zoomIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .close { position: absolute; right: 20px; top: 15px; font-size: 2rem; cursor: pointer; color: #94a3b8; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .content-container, footer { margin-left: 0; width: 100%; align-items: center; }
            .book-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="user_dashboard.php" class="logo">BOOK ENTHUSIAST</a>
            <div class="nav-links">
                <a href="user_dashboard.php"><i class="fas fa-book"></i> Library</a>
                <a href="my_orders.php"><i class="fas fa-box"></i> Orders</a>
                <div class="user-menu">
                    <div class="user-trigger">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                    </div>
                    <div class="user-dropdown">
                        <a href="user_profile.php"><i class="fas fa-user-edit"></i> Profile Settings</a>
                        <a href="logout.php" style="color: #f87171;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="sidebar">
        <h4>Browse Genres</h4>
        <div class="sidebar-links">
            <?php 
            $cats = ['All', 'Technology', 'Business', 'Science Fiction', 'General'];
            foreach($cats as $c) {
                $active = ($category_filter == $c) ? 'active' : '';
                echo "<a href='user_dashboard.php?category=$c' class='cat-link-sidebar $active'>$c</a>";
            }
            ?>
        </div>
    </div>

    <div class="content-container">
        <header class="dashboard-header">
            <h1>Digital & Physical <span>Library</span></h1>
            <p>Welcome back, <strong style="color:#facc15"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
        </header>

        <div class="search-bar">
            <form method="GET" action="user_dashboard.php">
                <input type="text" name="query" placeholder="Search title or author..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>

        <div class="book-grid">
            <?php if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) { 
                    $price = floatval($row['price']);
                    $is_free = ($price <= 0);
                    $book_id = $row['id'];
            ?>
                <div class="book-card">
                    <div class="price-badge <?php echo $is_free ? 'free' : ''; ?>">
                        <?php echo $is_free ? 'FREE' : '₹'.htmlspecialchars($row['price']); ?>
                    </div>
                    <div class="book-icon"><img src="<?php echo htmlspecialchars($row['img']); ?>" alt="Cover"></div>
                    <span class="cat-badge"><?php echo htmlspecialchars($row['category'] ?? 'General'); ?></span>
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="author">By <?php echo htmlspecialchars($row['author']); ?></p>
                    
                    <form method="POST" action="submit_rating.php">
                        <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                        <div class="star-rating">
                            <input type="radio" name="rating" id="s5-<?php echo $book_id; ?>" value="5"><label for="s5-<?php echo $book_id; ?>"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="s4-<?php echo $book_id; ?>" value="4"><label for="s4-<?php echo $book_id; ?>"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="s3-<?php echo $book_id; ?>" value="3"><label for="s3-<?php echo $book_id; ?>"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="s2-<?php echo $book_id; ?>" value="2"><label for="s2-<?php echo $book_id; ?>"><i class="fas fa-star"></i></label>
                            <input type="radio" name="rating" id="s1-<?php echo $book_id; ?>" value="1"><label for="s1-<?php echo $book_id; ?>"><i class="fas fa-star"></i></label>
                        </div>
                        <div class="comment-box">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <h4 style="font-size:0.7rem; color:#94a3b8">RECENT REVIEW</h4>
                                <a href="javascript:void(0)" onclick="openModal(<?php echo $book_id; ?>)" style="color:#facc15; font-size:0.65rem; text-decoration:none; font-weight:800;">VIEW ALL →</a>
                            </div>
                            <?php
                            // Fetch latest review WITH stars
                            $csql = "SELECT c.comment, c.rating, u.username FROM reviews c JOIN users u ON c.user_id = u.id WHERE c.book_id = $book_id ORDER BY c.id DESC LIMIT 1";
                            $cresult = $conn->query($csql);
                            if ($cresult && $cresult->num_rows > 0) {
                                while ($c = $cresult->fetch_assoc()) {
                                    echo "<div class='recent-stars'>";
                                    for($i=1; $i<=$c['rating']; $i++) echo "<i class='fas fa-star'></i>";
                                    echo "</div>";
                                    echo "<div class='comment'><b>" . htmlspecialchars($c['username']) . "</b>: " . htmlspecialchars($c['comment']) . "</div>";
                                }
                            } else { echo "<div class='comment' style='color:#64748b; font-style:italic;'>No reviews yet.</div>"; }
                            ?>
                            <div style="display:flex; gap:5px; margin-top:10px;">
                                <input type="text" name="comment" placeholder="Write review..." required style="flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:6px; color:#fff; font-size:0.75rem; padding:8px;">
                                <button type="submit" style="background:#facc15; border:none; border-radius:6px; padding:0 10px; cursor:pointer;"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </form>
                    <div class="button-group" style="margin-top: 1.2rem;">
                        <a class="btn-custom btn-read" href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">READ PDF</a>
                        <a class="btn-custom btn-order" href="checkout.php?book_id=<?php echo $book_id; ?>">ORDER</a>
                    </div>
                </div>
            <?php } } ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Book Enthusiast. All Rights Reserved. Built for readers.</p>
    </footer>

    <div id="commentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="color:#facc15; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; margin-bottom: 15px;">Community Feedback</h2>
            <div id="modalBody" style="max-height: 400px; overflow-y: auto;"></div>
        </div>
    </div>

    <script>
        function openModal(bookId) {
            const modal = document.getElementById('commentModal');
            const body = document.getElementById('modalBody');
            modal.style.display = "block";
            body.innerHTML = "<p style='color:#94a3b8; text-align:center;'>Loading reviews...</p>";
            fetch('fetch_reviews.php?book_id=' + bookId)
                .then(response => response.text())
                .then(data => { body.innerHTML = data; });
        }
        function closeModal() { document.getElementById('commentModal').style.display = "none"; }
        window.onclick = function(event) { if (event.target == document.getElementById('commentModal')) closeModal(); }
    </script>
</body>
</html>