<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['farmer']);

if (isset($_POST['name'])) {
    $farmer_id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($con, $_REQUEST['name']);
    $description = mysqli_real_escape_string($con, $_REQUEST['description']);
    $quantity = mysqli_real_escape_string($con, $_REQUEST['quantity']);
    $price = mysqli_real_escape_string($con, $_REQUEST['price']);
    $harvest_date = mysqli_real_escape_string($con, $_REQUEST['harvest_date']);

    // Image Upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $image = $_FILES["image"]["name"];
    $target_file = $target_dir . basename($image);
    
    // Add unique ID to filename to prevent overwrite
    if ($image) {
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $new_name = uniqid() . "." . $imageFileType;
        $target_file = $target_dir . $new_name;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        $db_image_path = $target_file; // Store relative path
    } else {
        $db_image_path = "";
    }

    $query = "INSERT INTO products (farmer_id, name, description, image, quantity, price, harvest_date)
              VALUES ('$farmer_id', '$name', '$description', '$db_image_path', '$quantity', '$price', '$harvest_date')";
    
    if (mysqli_query($con, $query)) {
        $message = "Product added successfully!";
    } else {
        $message = "Error adding product.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Product - FarmConnect</title>
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
                <li><a href="farmer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
                <li><a href="add_product.php" class="active"><i class="fas fa-plus-circle"></i> Add Product</a></li>
                <li><a href="manage_products.php"><i class="fas fa-boxes"></i> My Products</a></li>
                <li><a href="farmer_orders.php"><i class="fas fa-clipboard-list"></i> Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="farmer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
            </ul>
        </div>

        <div class="main-content">
            <header>
                <h2>Add Product</h2>
                <div class="user-profile">
                    <div class="user-info">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <small>Farmer</small>
                    </div>
                    <div class="profile-img"><i class="fas fa-user-tie"></i></div>
                    <a href="logout.php" style="margin-left: 10px; color: var(--danger);"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <div class="page-content">
                <div class="panel" style="max-width: 800px; margin: 0 auto;">
                    <h3>List New Produce</h3>
                    <?php if (isset($message)) echo "<p style='color: var(--primary); margin-bottom: 15px; padding: 10px; background: rgba(40,167,69,0.1); border: 1px solid var(--primary); border-radius: 4px;'>$message</p>"; ?>
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Product Class</label>
                                <select name="name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                                    <option value="">Select Produce Type</option>
                                    <option value="Apple">Apple</option>
                                    <option value="Banana">Banana</option>
                                    <option value="Orange">Orange</option>
                                    <option value="Mango">Mango</option>
                                    <option value="Pineapple">Pineapple</option>
                                    <option value="Papaya">Papaya</option>
                                    <option value="Grapes">Grapes</option>
                                    <option value="Watermelon">Watermelon</option>
                                    <option value="Strawberry">Strawberry</option>
                                    <option value="Lemon">Lemon</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Price per Unit ($)</label>
                                <input type="number" step="0.01" name="price" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label>Quantity Available</label>
                                <input type="number" name="quantity" placeholder="e.g. 50" required>
                            </div>
                            <div class="form-group">
                                <label>Harvest Date</label>
                                <input type="date" name="harvest_date" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="4" placeholder="Describe your product..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Product Image</label>
                            <input type="file" name="image" accept="image/*" required style="padding: 5px;">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="margin-top: 15px;"><i class="fas fa-save"></i> List Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
