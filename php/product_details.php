<?php
require('db.php');
session_start();

if (!isset($_GET['id'])) {
    header("Location: marketplace.php");
    exit;
}

$product_id = intval($_GET['id']);

// Add Review
if (isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $comment = mysqli_real_escape_string($con, $_POST['comment']);
    
    mysqli_query($con, "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES ('$product_id', '$user_id', '$rating', '$comment')");
}

// Fetch Product Details
$product = mysqli_fetch_assoc(mysqli_query($con, "
    SELECT p.*, f.username as farmer_name, fp.location 
    FROM products p 
    JOIN users f ON p.farmer_id = f.id 
    LEFT JOIN farm_profiles fp ON f.id = fp.user_id
    WHERE p.id='$product_id'
"));

// Fetch Reviews
$reviews = mysqli_query($con, "
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id='$product_id'
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $product['name']; ?> - Export & Trade</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-leaf"></i> SmartFarm
            </div>
            <ul class="sidebar-menu">
                <li><a href="buyer_dashboard.php"><i class="fas fa-store"></i> Marketplace</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> My Cart</a></li>
                <li><a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="buyer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <a href="buyer_dashboard.php" style="color: var(--text-secondary);"><i class="fas fa-arrow-left"></i> Back</a>
                    <h2>Product Details</h2>
                </div>
                
                <div class="user-profile">
                    <div class="user-info">
                        <!-- We might not know username if session not started properly or generic page, but let's assume session -->
                        <span><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?></span>
                        <small>Buyer</small>
                    </div>
                    <div class="profile-img"><i class="fas fa-user"></i></div>
                </div>
            </header>

            <div class="page-content">
                <div class="panel" style="display: flex; gap: 40px; margin-bottom: 30px;">
                    <div style="flex: 1;">
                        <?php if($product['image']): ?>
                            <img src="<?php echo $product['image']; ?>" style="width: 100%; border-radius: 8px; max-height: 400px; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 300px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #aaa;"><i class="fas fa-image fa-4x"></i></div>
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1;">
                        <h1 style="font-size: 2rem; margin-bottom: 10px; color: var(--text-primary);"><?php echo $product['name']; ?></h1>
                        <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 1.1rem;">
                            by <strong style="color: var(--primary);"><?php echo $product['farmer_name']; ?></strong> 
                            <?php if($product['location']) echo "<br><i class='fas fa-map-marker-alt' style='color: #666;'></i> $product[location]"; ?>
                        </p>
                        
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                            <p style="font-size: 1.1rem; line-height: 1.6; color: #444;"><?php echo $product['description']; ?></p>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
                            <span style="font-size: 2.2rem; font-weight: 700; color: var(--primary);">$<?php echo $product['price']; ?> <span style="font-size: 1rem; color: #888; font-weight: 400;">/ unit</span></span>
                            <span style="background: #e8f5e9; color: var(--primary); padding: 5px 15px; border-radius: 20px; font-weight: 600;"><?php echo $product['quantity']; ?> in stock</span>
                        </div>
                        
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'buyer'): ?>
                            <div style="display: flex; gap: 15px;">
                                <form action="cart_action.php" method="post" style="display: flex; gap: 10px; align-items: center;">
                                    <input type="hidden" name="add_to_cart" value="1">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>" style="width: 80px; padding: 12px; border: 2px solid #ddd; border-radius: 5px; font-weight: bold;">
                                    <button type="submit" class="btn btn-primary" style="padding: 12px 25px; font-size: 1rem;"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                                </form>
                                <a href="send_message.php?farmer_id=<?php echo $product['farmer_id']; ?>&product_name=<?php echo urlencode($product['name']); ?>" class="btn btn-secondary" style="padding: 12px 20px; display: flex; align-items: center;"><i class="fas fa-comment"></i></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="panel">
                    <h3><i class="fas fa-star" style="color: #fbbf24;"></i> Reviews & Ratings</h3>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <form action="" method="post" style="border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
                        <label style="font-weight: 600;">Leave a Review</label>
                        <div style="display: flex; gap: 15px; margin-bottom: 10px;">
                            <select name="rating" style="width: 150px; padding: 8px;">
                                <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                                <option value="4">⭐⭐⭐⭐ (4)</option>
                                <option value="3">⭐⭐⭐ (3)</option>
                                <option value="2">⭐⭐ (2)</option>
                                <option value="1">⭐ (1)</option>
                            </select>
                        </div>
                        <textarea name="comment" placeholder="Share your experience with this product..." required style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                        <button type="submit" name="submit_review" class="btn btn-primary" style="margin-top: 10px;">Submit Review</button>
                    </form>
                    <?php endif; ?>

                    <div>
                        <?php if(mysqli_num_rows($reviews) > 0): ?>
                            <?php while($r = mysqli_fetch_assoc($reviews)): ?>
                                <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f0f0f0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 32px; height: 32px; background: #e0e0e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; color: #555;">
                                                <?php echo strtoupper(substr($r['username'], 0, 1)); ?>
                                            </div>
                                            <strong><?php echo $r['username']; ?></strong>
                                        </div>
                                        <span style="color: #fbbf24; font-size: 0.9rem;"><?php echo str_repeat("<i class='fas fa-star'></i>", $r['rating']); ?></span>
                                    </div>
                                    <p style="color: #444; line-height: 1.5; margin-left: 42px;"><?php echo nl2br($r['comment']); ?></p>
                                    <small style="color: #999; margin-left: 42px; display: block; margin-top: 5px;"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></small>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 20px; color: #999;">
                                <i class="fas fa-comment-slash fa-2x" style="margin-bottom: 10px; opacity: 0.5;"></i>
                                <p>No reviews yet. Be the first to review!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
