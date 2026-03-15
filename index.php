<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'book_enthusiast';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $pdo = null;
}

// Fetch trending books (latest 4)
$trendingBooks = [];
if ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM books ORDER BY created_at DESC LIMIT 4");
    $stmt->execute();
    $trendingBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- DUMMY PREMIUM BOOKS ADDED HERE (Not from Database) ---
// Ye array database ko override kar dega taaki hamesha books dikhein
$trendingBooks = [
    [
        'id' => 1,
        'title' => 'The Psychology of Money',
        'author' => 'Morgan Housel',
        'price' => 299.00,
        'image_url' => 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'
    ],
    [
        'id' => 2,
        'title' => 'Atomic Habits',
        'author' => 'James Clear',
        'price' => 249.00, // Ise FREE se hata kar paid kar diya hai
        'image_url' => 'https://images.unsplash.com/photo-1589998059171-988d887df646?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'
    ],
    [
        'id' => 3,
        'title' => 'Deep Work',
        'author' => 'Cal Newport',
        'price' => 199.00,
        'image_url' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'
    ],
    [
        'id' => 4,
        'title' => 'Rich Dad Poor Dad',
        'author' => 'Robert T. Kiyosaki',
        'price' => 149.00,
        'image_url' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80'
    ]
];
// ----------------------------------------------------------
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Enthusiast - Your Digital & Physical Book Haven</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            color: #f1f5f9;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(15, 23, 42, 0.95);
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
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #f1c40f, #f39c12);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: #f1c40f;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: #f1c40f;
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f1c40f, #f39c12);
            color: #0f172a !important; /* FIXED: Now always black */
            box-shadow: 0 8px 32px rgba(241, 196, 15, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(241, 196, 15, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: #f1c40f;
            border: 2px solid rgba(241, 196, 15, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-outline:hover {
            background: rgba(241, 196, 15, 0.1);
            border-color: #f1c40f;
            transform: translateY(-2px);
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 4px;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background: #f1c40f;
            transition: 0.3s;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            gap: 4rem;
        }

        .hero-content {
            flex: 1;
            z-index: 2;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.25rem;
            color: #94a3b8;
            margin-bottom: 2.5rem;
            max-width: 500px;
            font-weight: 400;
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        /* Hero Image */
        .hero-image {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .hero-illustration {
            width: 100%;
            max-width: 500px;
            height: auto;
            filter: drop-shadow(0 25px 50px rgba(0,0,0,0.5));
            transform: rotate(-5deg);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(-5deg); }
            50% { transform: translateY(-20px) rotate(-5deg); }
        }

        /* Trending Section */
        .trending {
            padding: 6rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            background: linear-gradient(135deg, #f1c40f, #f39c12);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            color: #94a3b8;
            font-size: 1.25rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .book-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(241, 196, 15, 0.2);
            border-radius: 24px;
            padding: 1.5rem;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .book-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f1c40f, #f39c12);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .book-card:hover {
            transform: translateY(-12px) scale(1.02);
            background: rgba(30, 41, 59, 0.95);
            box-shadow: 0 32px 64px rgba(0,0,0,0.4);
            border-color: rgba(241, 196, 15, 0.4);
        }

        .book-card:hover::before {
            transform: scaleX(1);
        }

        .book-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 1rem;
        }

        .book-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #f1f5f9;
        }

        .book-author {
            color: #94a3b8;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .price-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .price-free {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .price-paid {
            background: linear-gradient(135deg, #f1c40f, #f39c12);
            color: #0f172a;
        }

        /* --- About Section Styles --- */
        .about-section {
            padding: 6rem 2rem;
            background: rgba(15, 23, 42, 0.4);
            border-top: 1px solid rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .about-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 4rem;
        }

        .about-text {
            flex: 1;
        }

        .about-text h2 {
            font-size: 2.5rem;
            color: #f1f5f9;
            margin-bottom: 1.5rem;
        }

        .about-text h2 span {
            color: #f1c40f;
        }

        .about-text p {
            color: #94a3b8;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .about-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #e2e8f0;
        }

        .feature-item i {
            color: #f1c40f;
            font-size: 1.5rem;
            background: rgba(241, 196, 15, 0.1);
            padding: 12px;
            border-radius: 50%;
        }

        .about-image {
            flex: 1;
            text-align: right;
        }

        .about-image img {
            width: 100%;
            max-width: 500px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        /* --- Footer Styles --- */
        .site-footer {
            background: #0f172a;
            padding: 4rem 2rem 2rem;
            border-top: 1px solid rgba(241, 196, 15, 0.1);
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 4rem;
            margin-bottom: 3rem;
        }

        .footer-about .logo {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .footer-about p {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            max-width: 300px;
        }

        .social-icons {
            display: flex;
            gap: 1rem;
        }

        .social-icons a {
            color: #cbd5e1;
            background: rgba(255,255,255,0.05);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: 0.3s;
        }

        .social-icons a:hover {
            background: #f1c40f;
            color: #0f172a;
            transform: translateY(-3px);
        }

        .footer-links h4 {
            color: #f1f5f9;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links ul li {
            margin-bottom: 0.8rem;
        }

        .footer-links ul li a {
            color: #94a3b8;
            text-decoration: none;
            transition: 0.3s;
            font-size: 0.95rem;
        }

        .footer-links ul li a:hover {
            color: #f1c40f;
            padding-left: 5px;
        }

        .footer-bottom {
            max-width: 1400px;
            margin: 0 auto;
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.05);
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Responsive Update */
        @media (max-width: 968px) {
            .about-container {
                flex-direction: column;
                text-align: center;
            }
            .about-features {
                text-align: left;
            }
            .footer-container {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 2rem;
            }
            .footer-about p {
                margin: 0 auto 1.5rem;
            }
            .social-icons {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }
            .nav-links {
                display: none;
            }
            .hero {
                flex-direction: column;
                text-align: center;
                gap: 2rem;
                padding: 0 1rem;
            }
            .hero-buttons {
                justify-content: center;
            }
            .books-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
            }
            .nav-container {
                padding: 0 1rem;
            }
            .about-features {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                margin-bottom: 1rem;
            }
            .hero p {
                font-size: 1.1rem;
            }
            .trending {
                padding: 4rem 1rem;
            }
        }

        /* Glassmorphism utilities */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">Book Enthusiast</div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#books">Books</a></li>
                <li><a href="#about">About Us</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a></li>
                <?php else: ?>
                    <div class="cta-buttons">
                        <a href="login.php" class="btn btn-outline">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Join Now
                        </a>
                    </div>
                <?php endif; ?>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Read, Explore & Own Your Stories</h1>
            <p>Discover thousands of digital and physical books across every genre. From timeless classics to modern bestsellers, find your next favorite read with our curated collection.</p>
            <div class="hero-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-rocket"></i> Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Join Now
                    </a>
                    <a href="register.php" class="btn btn-outline">
                        <i class="fas fa-book-open"></i> Browse Books
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Digital Reading Illustration" class="hero-illustration">
        </div>
    </section>

    <section class="trending" id="books">
        <div class="section-header">
            <h2 class="section-title">Trending Now</h2>
            <p class="section-subtitle">Discover the books everyone is reading right now</p>
        </div>
        <div class="books-grid">
            <?php if (empty($trendingBooks)): ?>
                <div class="book-card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <i class="fas fa-book" style="font-size: 4rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                    <h3>No books available yet</h3>
                    <p style="color: #94a3b8;">Check back soon for trending titles!</p>
                </div>
            <?php else: ?>
                <?php foreach ($trendingBooks as $book): ?>
                    <div class="book-card">
                        <img src="<?php echo htmlspecialchars($book['image_url'] ?? 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80'); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-image">
                        
                        <div class="price-badge price-paid">
                            ₹<?php echo number_format($book['price'], 2); ?>
                        </div>

                        <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="book-author">by <?php echo htmlspecialchars($book['author'] ?? 'Unknown Author'); ?></p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="user_dashboard.php" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                <i class="fas fa-shopping-cart"></i> Get Book
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                <i class="fas fa-shopping-cart"></i> Get Book
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="about-section" id="about">
        <div class="about-container">
            <div class="about-text">
                <h2>Why Choose <span>Book Enthusiast?</span></h2>
                <p>We believe that reading should be accessible to everyone, everywhere. Whether you prefer the tactile feel of a physical hardcover or the convenience of a digital e-book on your screen, we've built a platform that bridges the gap.</p>
                <p>Our library is constantly updated with bestsellers, academic journals, and indie publications to ensure your reading journey never hits a roadblock.</p>
                
                <div class="about-features">
                    <div class="feature-item">
                        <i class="fas fa-mobile-alt"></i>
                        <span>Read anywhere, anytime</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-truck"></i>
                        <span>Fast physical delivery</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-tags"></i>
                        <span>Affordable pricing</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure payments</span>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="About Our Library">
            </div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-about">
                <div class="logo footer-logo">Book Enthusiast</div>
                <p>Your ultimate destination for digital and physical books. Explore a world of knowledge, fiction, and imagination all in one place.</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#books">Trending Books</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="register.php">Create Account</a></li>
                </ul>
            </div>
            
            <div class="footer-links">
                <h4>Support</h4>
                <ul>
                    <li><a href="#">FAQ & Help</a></li>
                    <li><a href="#">Shipping Policy</a></li>
                    <li><a href="#">Return Policy</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> Book Enthusiast. All rights reserved. Designed for readers.</p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(15, 23, 42, 0.98)';
            } else {
                navbar.style.background = 'rgba(15, 23, 42, 0.95)';
            }
        });
    </script>
</body>
</html>