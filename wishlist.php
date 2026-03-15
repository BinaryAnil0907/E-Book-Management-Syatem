<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];
$wishlist = $conn->query("SELECT w.id as wish_id, b.* FROM wishlist w JOIN books b ON w.book_id = b.id WHERE w.user_id = $user_id ORDER BY w.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | Book Enthusiast</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #1b212c; color: #f1f5f9; line-height: 1.6; min-height: 100vh; overflow-x: hidden; }
        
        /* Nav Header Animation */
        .nav-header { background: rgba(27, 33, 44, 0.95); padding: 1.5rem 2rem; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; backdrop-filter: blur(10px); animation: slideDown 0.6s ease; }
        @keyframes slideDown { from { transform: translateY(-100%); } to { transform: translateY(0); } }

        .back-link { color: #facc15; text-decoration: none; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: 0.3s; }
        .back-link:hover { transform: translateX(-5px); filter: brightness(1.2); }

        .content-container { max-width: 1400px; margin: 0 auto; padding: 4rem 2rem; }
        
        /* Title Animation */
        .page-title { margin-bottom: 3rem; text-align: center; animation: fadeIn 1s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .page-title h1 { font-size: 2.8rem; font-weight: 800; }
        .page-title span { color: #facc15; text-shadow: 0 0 15px rgba(250, 204, 21, 0.3); }

        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2.5rem; justify-content: center; }

        /* Book Card Entrance Animation */
        .book-card { background: #252d3a; border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 1.5rem; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative; animation: cardEntrance 0.8s ease backwards; }
        @keyframes cardEntrance { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

        .book-card:hover { transform: translateY(-12px) scale(1.02); border-color: #facc15; box-shadow: 0 25px 50px rgba(0,0,0,0.5); }
        
        .book-icon img { width: 100%; height: 350px; object-fit: cover; border-radius: 12px; margin-bottom: 1.2rem; transition: 0.5s; }
        .book-card:hover .book-icon img { filter: contrast(1.1); }
        
        .remove-wish { position: absolute; top: 1.5rem; right: 1.5rem; background: rgba(239, 68, 68, 0.8); color: #fff; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s; z-index: 10; opacity: 0; }
        .book-card:hover .remove-wish { opacity: 1; }
        .remove-wish:hover { transform: rotate(90deg); background: #ef4444; box-shadow: 0 0 15px rgba(239, 68, 68, 0.5); }

        .btn-add-cart { width: 100%; padding: 14px; background: #facc15; color: #0f172a; border-radius: 12px; text-align: center; text-decoration: none; font-weight: 800; display: block; margin-top: 1rem; transition: 0.3s; position: relative; overflow: hidden; }
        .btn-add-cart::after { content: ''; position: absolute; top: 50%; left: 50%; width: 0; height: 0; background: rgba(255,255,255,0.2); border-radius: 50%; transition: 0.4s; transform: translate(-50%, -50%); }
        .btn-add-cart:hover::after { width: 300px; height: 300px; }
        .btn-add-cart:hover { background: #fff; transform: translateY(-2px); }

        .empty-state { text-align: center; padding: 8rem 2rem; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 0.5; } 50% { opacity: 1; } 100% { opacity: 0.5; } }
    </style>
</head>
<body>
    <div class="nav-header">
        <a href="user_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> BACK TO LIBRARY</a>
        <div style="font-weight: 800; color: #facc15; letter-spacing: 1px; font-size: 1.2rem;">BOOK ENTHUSIAST</div>
    </div>

    <div class="content-container">
        <div class="page-title">
            <h1>My <span>Wishlist</span> ❤️</h1>
        </div>

        <div class="book-grid">
            <?php if ($wishlist->num_rows > 0): 
                $delay = 0;
                while ($row = $wishlist->fetch_assoc()): 
                $delay += 0.1;
            ?>
                <div class="book-card" style="animation-delay: <?php echo $delay; ?>s">
                    <a href="manage_wishlist.php?book_id=<?php echo $row['id']; ?>" class="remove-wish" title="Remove"><i class="fas fa-times"></i></a>
                    <div class="book-icon"><img src="<?php echo $row['img']; ?>"></div>
                    <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 15px;">By <?php echo $row['author']; ?></p>
                    <a href="manage_cart.php?action=add&book_id=<?php echo $row['id']; ?>" class="btn-add-cart"><i class="fas fa-cart-plus"></i> MOVE TO CART</a>
                </div>
            <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <i class="fas fa-heart-circle-exclamation" style="font-size: 5rem; color: #334155; margin-bottom: 1rem;"></i>
                    <h2 style="color: #64748b;">Wishlist is empty</h2>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>