<?php
session_start();
include 'db.php';

// Check if the administrator is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize input data to prevent XSS attacks
    $title = htmlspecialchars(trim($_POST['title']));
    $author = htmlspecialchars(trim($_POST['author']));
    $category = htmlspecialchars(trim($_POST['category']));
    $price = floatval($_POST['price']);

    // 2. Validate price value
    if ($price < 0) {
        $message = "<div class='error-msg'><i class='fas fa-exclamation-circle'></i> Error: Price cannot be a negative value.</div>";
    } else {
        
        // 3. Check if both files are uploaded without errors
        if (isset($_FILES['ebook']) && $_FILES['ebook']['error'] == 0 &&
            isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {

            // Define allowed formats and maximum sizes
            $allowed_img_types = ['image/jpeg', 'image/png', 'image/webp'];
            $allowed_pdf_types = ['application/pdf'];
            $max_img_size = 5 * 1024 * 1024; // 5 MB
            $max_pdf_size = 50 * 1024 * 1024; // 50 MB

            $ebook_mime = mime_content_type($_FILES['ebook']['tmp_name']);
            $cover_mime = mime_content_type($_FILES['cover']['tmp_name']);
            $ebook_size = $_FILES['ebook']['size'];
            $cover_size = $_FILES['cover']['size'];

            // 4. Validate file formats
            if (!in_array($ebook_mime, $allowed_pdf_types)) {
                $message = "<div class='error-msg'><i class='fas fa-file-pdf'></i> Error: The e-book must be a valid PDF format.</div>";
            } elseif (!in_array($cover_mime, $allowed_img_types)) {
                $message = "<div class='error-msg'><i class='fas fa-image'></i> Error: The cover must be a JPG, PNG, or WEBP image.</div>";
            } 
            // 5. Validate file sizes
            elseif ($ebook_size > $max_pdf_size) {
                $message = "<div class='error-msg'><i class='fas fa-weight-hanging'></i> Error: The PDF file exceeds the 50MB limit.</div>";
            } elseif ($cover_size > $max_img_size) {
                $message = "<div class='error-msg'><i class='fas fa-weight-hanging'></i> Error: The cover image exceeds the 5MB limit.</div>";
            } else {
                
                // Define upload directories
                $ebookDir = "ebooks/";
                $imgDir = "img/";

                // Create directories if they do not exist on the server
                if (!is_dir($ebookDir)) mkdir($ebookDir, 0777, true);
                if (!is_dir($imgDir)) mkdir($imgDir, 0777, true);

                // Sanitize filenames to remove spaces and special characters
                $cleanEbookName = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['ebook']['name']));
                $cleanImgName = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['cover']['name']));

                // Generate unique file paths
                $ebookName = time() . "_" . $cleanEbookName;
                $ebookPath = $ebookDir . $ebookName;

                $imgName = time() . "_" . $cleanImgName;
                $imgPath = $imgDir . $imgName;

                // 6. Move the files to the server and insert into the database
                if (move_uploaded_file($_FILES['ebook']['tmp_name'], $ebookPath) &&
                    move_uploaded_file($_FILES['cover']['tmp_name'], $imgPath)) {

                    // Prepare the SQL statement to prevent SQL injection
                    $stmt = $conn->prepare("INSERT INTO books (title, author, category, price, file_path, img) VALUES (?, ?, ?, ?, ?, ?)");
                    
                    // Bind parameters: s = string, d = double (4 strings, 1 double, 1 string)
                    $stmt->bind_param("ssssss", $title, $author, $category, $price, $ebookPath, $imgPath);

                    if ($stmt->execute()) {
                        $message = "<div class='success-msg'><i class='fas fa-check-circle'></i> Success: Book added to the library (₹" . number_format($price, 2) . ")</div>";
                    } else {
                        $message = "<div class='error-msg'><i class='fas fa-database'></i> Database error: " . $conn->error . "</div>";
                    }
                    $stmt->close();
                } else {
                    $message = "<div class='error-msg'><i class='fas fa-server'></i> Error: Failed to save files to the server directory.</div>";
                }
            }
        } else {
            $message = "<div class='error-msg'><i class='fas fa-info-circle'></i> Error: Please upload both the PDF document and the cover image.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Upload Book</title>
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

        .admin-wrapper {
            background: linear-gradient(145deg, #252d3a, #1b212c);
            padding: 2.5rem 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            width: 100%;
            max-width: 700px;
            border: 1px solid rgba(255,255,255,0.05);
            position: relative;
        }

        .admin-wrapper::before {
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

        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 5px;
        }

        .header-title p {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group.full-width {
            grid-column: 1 / -1;
            margin-bottom: 0.5rem;
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
            padding: 12px 15px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: 0.3s;
        }

        .input-field:focus {
            border-color: #facc15;
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 10px rgba(250, 204, 21, 0.1);
        }

        .file-upload {
            padding: 10px;
            background: rgba(15, 23, 42, 0.4);
            border: 1px dashed rgba(255,255,255,0.2);
            border-radius: 8px;
            cursor: pointer;
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .file-upload:hover {
            border-color: #facc15;
            color: #facc15;
        }

        .btn-submit {
            grid-column: 1 / -1;
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
            margin-top: 1rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(250, 204, 21, 0.4);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .back-link:hover {
            color: #facc15;
        }

        .success-msg {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid rgba(34, 197, 94, 0.3);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Responsive configuration */
        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>

    <div class="admin-wrapper">
        <div class="header-title">
            <h1><i class="fas fa-book-medical" style="color: #facc15;"></i> Upload Book</h1>
            <p>Add new literature to the platform repository</p>
        </div>

        <?php if (!empty($message)) echo $message; ?>

        <form method="POST" enctype="multipart/form-data" class="form-grid">
            
            <div class="input-group full-width">
                <label>Book Title</label>
                <input type="text" name="title" maxlength="255" class="input-field" placeholder="Enter the exact book title" required>
            </div>

            <div class="input-group full-width">
                <label>Author Name</label>
                <input type="text" name="author" maxlength="255" class="input-field" placeholder="Enter the primary author's name" required>
            </div>

            <div class="input-group">
                <label>Category</label>
                <select name="category" class="input-field" required>
                    <option value="" disabled selected>Select Category</option>
                    <option value="Technology">Technology</option>
                    <option value="Science Fiction">Science Fiction</option>
                    <option value="Business">Business</option>
                    <option value="Self-Help">Self-Help</option>
                    <option value="Fantasy">Fantasy</option>
                    <option value="Mystery">Mystery</option>
                    <option value="General">General</option>
                </select>
            </div>

            <div class="input-group">
                <label>Price (₹)</label>
                <input type="number" name="price" step="0.01" min="0" class="input-field" placeholder="0.00" required>
            </div>

            <div class="input-group">
                <label>Upload E-book (PDF format)</label>
                <input type="file" name="ebook" accept="application/pdf" class="input-field file-upload" required>
            </div>

            <div class="input-group">
                <label>Upload Cover (JPG, PNG, WEBP)</label>
                <input type="file" name="cover" accept="image/jpeg, image/png, image/webp" class="input-field file-upload" required>
            </div>

            <button type="submit" class="btn-submit"><i class="fas fa-cloud-upload-alt"></i> Publish Book to Library</button>
        </form>

        <a href="admin_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Return to Admin Dashboard</a>
    </div>

</body>
</html>