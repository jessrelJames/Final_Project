<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['admin']);

$orders = mysqli_query($con, "
    SELECT o.*, 
           b.username as buyer_name, 
           f.username as farmer_name,
           p.name as product_name
    FROM orders o
    JOIN users b ON o.buyer_id = b.id
    JOIN users f ON o.farmer_id = f.id
    JOIN products p ON o.product_id = p.id
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>All Orders - Export & Trade</title>
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
                <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="admin_reports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>All Orders</h2>
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
                    <h3>Order History</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Buyer</th>
                                    <th>Farmer</th>
                                    <th>Product</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($orders)): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td><?php echo $row['buyer_name']; ?></td>
                                    <td><?php echo $row['farmer_name']; ?></td>
                                    <td><?php echo $row['product_name']; ?> (x<?php echo $row['quantity']; ?>)</td>
                                    <td>$<?php echo $row['total_price']; ?></td>
                                    <td>
                                        <?php if($row['status'] == 'Pending'): ?>
                                            <span style="color: var(--warning); font-weight: 600;"><?php echo $row['status']; ?></span>
                                        <?php elseif($row['status'] == 'Delivered'): ?>
                                            <span style="color: var(--primary); font-weight: 600;"><?php echo $row['status']; ?></span>
                                        <?php elseif($row['status'] == 'Cancelled'): ?>
                                            <span style="color: var(--danger); font-weight: 600;"><?php echo $row['status']; ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-secondary); font-weight: 600;"><?php echo $row['status']; ?></span>
                                        <?php endif; ?>
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
