<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['admin']);

$products = mysqli_query($con, "
    SELECT p.*, u.username as farmer_name 
    FROM products p 
    JOIN users u ON p.farmer_id = u.id 
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Products - Export & Trade</title>
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
                <li><a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="admin_products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="admin_reports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>All Products</h2>
                <div class="user-profile">
                    <div class="user-info">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <small>Administrator</small>
                    </div>
                    <div class="profile-img"><i class="fas fa-user"></i></div>
                    <a href="logout.php" style="margin-left: 10px; color: var(--danger);"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <div class="page-content">
                <div class="panel">
                    <h3>Product Inventory</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Farmer</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Harvest Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($products)): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 500;"><?php echo $row['name']; ?></div>
                                        <div style="font-size: 0.8rem; color: #888;">ID: #<?php echo $row['id']; ?></div>
                                    </td>
                                    <td><?php echo $row['farmer_name']; ?></td>
                                    <td>$<?php echo $row['price']; ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['harvest_date'])); ?></td>
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
