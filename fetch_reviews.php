<?php
include 'db.php';

if (isset($_GET['book_id'])) {
    $book_id = intval($_GET['book_id']);

    // Fetch reviews with user details
    $query = "SELECT r.*, u.username FROM reviews r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.book_id = $book_id 
              ORDER BY r.id DESC";
    $res = $conn->query($query);

    if ($res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $rating = intval($row['rating']);
            ?>
            <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; gap: 15px; align-items: flex-start;">
                
                <div style="background: rgba(250, 204, 21, 0.1); color: #facc15; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                    <i class="fas fa-user-circle"></i>
                </div>

                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <strong style="color: #facc15; font-size: 1rem;"><?php echo htmlspecialchars($row['username']); ?></strong>
                        
                        <div style="color: #facc15; font-size: 0.8rem;">
                            <?php 
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rating) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star" style="color: #334155;"></i>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <p style="color: #e2e8f0; margin-top: 5px; font-size: 0.9rem; line-height: 1.4;">
                        <?php echo htmlspecialchars($row['comment']); ?>
                    </p>
                    
                    <small style="color: #64748b; font-size: 0.7rem;"><?php echo date('d M Y', strtotime($row['created_at'])); ?></small>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<p style='color: #94a3b8; text-align: center; padding: 20px;'>No reviews yet. Be the first one to share feedback!</p>";
    }
}
?>