<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$message = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch current book details
$res = $conn->query("SELECT * FROM books WHERE id = $id");
$book = $res->fetch_assoc();

if (!$book) {
    die("Book not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars(trim($_POST['title']));
    $author = htmlspecialchars(trim($_POST['author']));
    $category = htmlspecialchars(trim($_POST['category']));
    $price = floatval($_POST['price']);

    // Default paths are the existing ones
    $ebookPath = $book['file_path'];
    $imgPath = $book['img'];

    // Handle New PDF Upload
    if (isset($_FILES['ebook']) && $_FILES['ebook']['error'] == 0) {
        $ebookName = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['ebook']['name']));
        $targetEbook = "ebooks/" . $ebookName;
        if (move_uploaded_file($_FILES['ebook']['tmp_name'], $targetEbook)) {
            $ebookPath = $targetEbook;
        }
    }

    // Handle New Image Upload
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $imgName = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['cover']['name']));
        $targetImg = "img/" . $imgName;
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $targetImg)) {
            $imgPath = $targetImg;
        }
    }

    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, price=?, file_path=?, img=? WHERE id=?");
    $stmt->bind_param("sssdssi", $title, $author, $category, $price, $ebookPath, $imgPath, $id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?msg=Book Updated");
        exit;
    } else {
        $message = "<div class='error-msg'>Update failed: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book - Admin Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #1b212c; color: #f1f5f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 2rem; }
        .edit-container { background: linear-gradient(145deg, #252d3a, #1b212c); padding: 3rem; border-radius: 24px; width: 100%; max-width: 700px; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
        h1 { font-size: 2rem; margin-bottom: 2rem; color: #facc15; text-align: center; }
        .input-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 10px; font-weight: 600; color: #cbd5e1; font-size: 1.1rem; }
        .input-field { width: 100%; padding: 14px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #fff; font-size: 1.1rem; outline: none; }
        .input-field:focus { border-color: #facc15; }
        .btn-update { width: 100%; padding: 16px; background: #facc15; color: #0f172a; border: none; border-radius: 12px; font-weight: 800; font-size: 1.2rem; cursor: pointer; transition: 0.3s; }
        .btn-update:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(250, 204, 21, 0.3); }
        .back-link { display: block; text-align: center; margin-top: 1.5rem; color: #94a3b8; text-decoration: none; font-weight: 600; font-size: 1.1rem; }
        .error-msg { background: rgba(239, 68, 68, 0.1); color: #f87171; padding: 15px; border-radius: 10px; margin-bottom: 1.5rem; text-align: center; }
    </style>
</head>
<body>
    <div class="edit-container">
        <h1>Edit Book Details</h1>
        <?php echo $message; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Book Title</label>
                <input type="text" name="title" class="input-field" value="<?php echo htmlspecialchars($book['title']); ?>" required>
            </div>
            <div class="input-group">
                <label>Author</label>
                <input type="text" name="author" class="input-field" value="<?php echo htmlspecialchars($book['author']); ?>" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="input-group">
                    <label>Category</label>
                    <select name="category" class="input-field" required>
                        <option value="Technology" <?php if($book['category'] == 'Technology') echo 'selected'; ?>>Technology</option>
                        <option value="Science Fiction" <?php if($book['category'] == 'Science Fiction') echo 'selected'; ?>>Science Fiction</option>
                        <option value="Business" <?php if($book['category'] == 'Business') echo 'selected'; ?>>Business</option>
                        <option value="Self-Help" <?php if($book['category'] == 'Self-Help') echo 'selected'; ?>>Self-Help</option>
                        <option value="Fantasy" <?php if($book['category'] == 'Fantasy') echo 'selected'; ?>>Fantasy</option>
                        <option value="General" <?php if($book['category'] == 'General') echo 'selected'; ?>>General</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Price (₹)</label>
                    <input type="number" name="price" step="0.01" min="0" class="input-field" value="<?php echo $book['price']; ?>" required>
                </div>
            </div>
            <div class="input-group">
                <label>Update E-book (Leave empty to keep current)</label>
                <input type="file" name="ebook" accept=".pdf" class="input-field" style="font-size: 0.9rem;">
            </div>
            <div class="input-group">
                <label>Update Cover (Leave empty to keep current)</label>
                <input type="file" name="cover" accept="image/*" class="input-field" style="font-size: 0.9rem;">
            </div>
            <button type="submit" class="btn-update">Save Changes</button>
        </form>
        <a href="admin_dashboard.php" class="back-link">Cancel and Return</a>
    </div>
</body>
</html>