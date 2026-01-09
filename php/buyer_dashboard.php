<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['buyer']);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

$query = "
    SELECT p.*, f.username as farmer_name, f.id as farmer_user_id, fp.location 
    FROM products p 
    JOIN users f ON p.farmer_id = f.id 
    LEFT JOIN farm_profiles fp ON f.id = fp.user_id
    WHERE p.quantity > 0 
    AND p.name IN ('Apple', 'Banana', 'Orange', 'Mango', 'Pineapple', 'Papaya', 'Grapes', 'Watermelon', 'Strawberry', 'Lemon')
";

if ($search) {
    $safe_search = mysqli_real_escape_string($con, $search);
    $query .= " AND (p.name LIKE '%$safe_search%' OR p.description LIKE '%$safe_search%')";
}

if ($sort == 'price_asc') {
    $query .= " ORDER BY p.price ASC";
} elseif ($sort == 'price_desc') {
    $query .= " ORDER BY p.price DESC";
} else {
    $query .= " ORDER BY p.created_at DESC";
}

$products = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buyer Dashboard - Export & Trade</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-leaf"></i> Export & Trade
            </div>
            <ul class="sidebar-menu">
                <li><a href="buyer_dashboard.php" class="active"><i class="fas fa-store"></i> Marketplace</a></li>
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
                <h2>Marketplace</h2>
                <div class="user-profile">
                    <div class="user-info">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <small>Buyer</small>
                    </div>
                    <div class="profile-img"><i class="fas fa-user"></i></div>
                    <a href="logout.php" style="margin-left: 10px; color: var(--danger);"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <div class="page-content">
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <div>
                        <h1>Good <?php echo (date('H') < 12) ? 'Morning' : ((date('H') < 18) ? 'Afternoon' : 'Evening'); ?>, <?php echo $_SESSION['username']; ?>!</h1>
                        <p>Welcome to the marketplace. Browsing fresh produce has never been easier.</p>
                    </div>
                    <div>
                        <a href="cart.php" class="btn btn-light" style="color: var(--primary); font-weight: bold;">My Cart <i class="fas fa-shopping-cart"></i></a>
                    </div>
                </div>

                <!-- Search Box -->
                 <div class="panel" style="margin-bottom: 20px;">
                    <form action="" method="get" style="display: flex; gap: 10px;">
                        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                        <select name="sort" style="width: 200px;">
                            <option value="">Sort By</option>
                            <option value="price_asc" <?php if($sort=='price_asc') echo 'selected'; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php if($sort=='price_desc') echo 'selected'; ?>>Price: High to Low</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>

                <!-- Products Grid -->
                <div class="grid">
                    <?php if (mysqli_num_rows($products) > 0): // Changed $result to $products ?>
                        <?php while($row = mysqli_fetch_assoc($products)): // Changed $result to $products ?>
                            <div class="panel" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                                <?php if($row['image']): ?>
                                    <img src="<?php echo $row['image']; ?>" style="width: 100%; height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 200px; background: #eee; display: flex; align-items: center; justify-content: center; color: #777;">
                                        <i class="fas fa-image fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column;">
                                    <h3 style="border: none; padding: 0; margin-bottom: 5px;">
                                        <a href="product_details.php?id=<?php echo $row['id']; ?>" style="color: var(--text-primary); text-decoration: none;"><?php echo $row['name']; ?></a>
                                    </h3>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 10px;">
                                        by <?php echo $row['farmer_name']; ?> 
                                    </p>
                                    <p style="color: #555; font-size: 0.9rem; margin-bottom: 15px; flex-grow: 1;"><?php echo substr($row['description'], 0, 80) . '...'; ?></p>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                                        <span style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">$<?php echo $row['price']; ?></span>
                                        <div>
                                            <button onclick="addToCart(<?php echo $row['id']; ?>, '<?php echo $row['name']; ?>', <?php echo $row['quantity']; ?>)" class="btn btn-primary" style="padding: 8px 12px; font-size: 0.8rem;"><i class="fas fa-cart-plus"></i></button>
                                            <a href="send_message.php?farmer_id=<?php echo $row['farmer_id']; ?>&product_name=<?php echo urlencode($row['name']); ?>" class="btn btn-secondary" style="padding: 8px 12px; font-size: 0.8rem;"><i class="fas fa-comment"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="grid-column: 1/-1; text-align: center; color: var(--text-secondary);">No products found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function addToCart(id, name, maxQty) {
            let qty = prompt("Enter quantity for " + name + " (Max: " + maxQty + "):");
            if (qty && qty > 0 && qty <= maxQty) {
                // Create a temporary form to submit
                let form = document.createElement('form');
                form.action = 'cart_action.php';
                form.method = 'POST';
                
                let actionInput = document.createElement('input');
                actionInput.name = 'add_to_cart';
                actionInput.value = '1';
                form.appendChild(actionInput);
                
                let idInput = document.createElement('input');
                idInput.name = 'product_id';
                idInput.value = id;
                form.appendChild(idInput);
                
                let qtyInput = document.createElement('input');
                qtyInput.name = 'quantity';
                qtyInput.value = qty;
                form.appendChild(qtyInput);
                
                document.body.appendChild(form);
                form.submit();
            } else if (qty) {
                alert("Invalid quantity!");
            }
        }
    </script>
</body>
</html>

