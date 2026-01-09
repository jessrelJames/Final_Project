<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['farmer']);

$farmer_id = $_SESSION['user_id'];

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($con, "DELETE FROM products WHERE id='$id' AND farmer_id='$farmer_id'");
    header("Location: manage_products.php");
}

$products = mysqli_query($con, "SELECT * FROM products WHERE farmer_id='$farmer_id' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Products - FarmConnect</title>
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
                <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Add Product</a></li>
                <li><a href="manage_products.php" class="active"><i class="fas fa-boxes"></i> My Products</a></li>
                <li><a href="farmer_orders.php"><i class="fas fa-clipboard-list"></i> Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="farmer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
            </ul>
        </div>

        <div class="main-content">
            <header>
                <h2>My Products</h2>
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
                <div class="card">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Price/Unit</th>
                                    <th>Available Qty</th>
                                    <th>Harvest Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($products)): ?>
                                <tr>
                                    <td>
                                        <?php if($row['image']): ?>
                                            <img src="<?php echo $row['image']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">No Img</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td>$<?php echo $row['price']; ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td><?php echo $row['harvest_date']; ?></td>
                                    <td>
                                        <a href="manage_products.php?delete=<?php echo $row['id']; ?>" class="status cancelled" onclick="return confirm('Delete this product?')" style="text-decoration:none; padding: 5px 10px; display: inline-block;"><i class="fas fa-trash-alt"></i> Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
