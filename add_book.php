<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $price = $_POST['price']; // Naya Price field

    // File upload logic
    if (isset($_FILES['ebook']) && $_FILES['ebook']['error'] == 0 &&
        isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {

        $ebookDir = "ebooks/";
        $imgDir = "img/";

        // Ebook file
        $ebookName = time() . "_" . basename($_FILES['ebook']['name']);
        $ebookPath = $ebookDir . $ebookName;

        // Image file
        $imgName = time() . "_" . basename($_FILES['cover']['name']);
        $imgPath = $imgDir . $imgName;

        // Move files to folders
        if (move_uploaded_file($_FILES['ebook']['tmp_name'], $ebookPath) &&
            move_uploaded_file($_FILES['cover']['tmp_name'], $imgPath)) {

            // SQL Query: Isme 'price' column add kiya gaya hai
            $stmt = $conn->prepare("INSERT INTO books (title, author, file_path, img, price) VALUES (?, ?, ?, ?, ?)");
            // 'ssssd' ka matlab: 4 strings aur 1 decimal (price)
            $stmt->bind_param("ssssd", $title, $author, $ebookPath, $imgPath, $price);

            if ($stmt->execute()) {
                $message = "Book uploaded successfully with price ₹$price!";
            } else {
                $message = "Database error: " . $conn->error;
            }
        } else {
            $message = "Error uploading files to folders!";
        }
    } else {
        $message = "Please fill all fields and select files!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Book - Admin</title>
    <link rel="stylesheet" href="add_book.css">
</head>
<body>
    <div class="container">
        <h1>Book Enthusiast Management</h1>
        <h2>Add New Book</h2>
        <form method="post" enctype="multipart/form-data">
            <label>Book Title:</label>
            <input type="text" name="title" required>

            <label>Author Name:</label>
            <input type="text" name="author" required>

            <label>Price (₹):</label>
            <input type="number" name="price" step="0.01" placeholder="e.g. 499.00" required>

            <label>Upload E-book (PDF):</label>
            <input type="file" name="ebook" accept=".pdf" required>

            <label>Upload Cover Image:</label>
            <input type="file" name="cover" accept="image/*" required>

            <button type="submit">Add Book to Library</button>
        </form>
        <p style="text-align:center; font-weight:bold; color:green;"><?php echo $message; ?></p>
        <a href="admin_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</body>
</html>