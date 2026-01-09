<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['buyer']);

$buyer_id = $_SESSION['user_id'];

$orders = mysqli_query($con, "
    SELECT o.*, 
           f.username as farmer_name, 
           p.name as product_name,
           p.image
    FROM orders o
    JOIN users f ON o.farmer_id = f.id
    JOIN products p ON o.product_id = p.id
    WHERE o.buyer_id='$buyer_id'
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Orders - Export & Trade</title>
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
                <li><a href="buyer_dashboard.php"><i class="fas fa-store"></i> Marketplace</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> My Cart</a></li>
                <li><a href="my_orders.php" class="active"><i class="fas fa-box"></i> My Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="buyer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>My Orders</h2>
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
                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success') echo "<div class='panel' style='color: var(--primary); margin-bottom: 20px;'>Order request sent successfully!</div>"; ?>
                
                <div class="panel">
                    <h3>Order History</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Farmer</th>
                                    <th>Qty</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($orders)): ?>
                                <tr>
                                    <td style="display: flex; align-items: center; gap: 10px;">
                                        <?php if($row['image']): ?>
                                            <img src="<?php echo $row['image']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <div style="width: 40px; height: 40px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-image" style="color:#aaa;"></i></div>
                                        <?php endif; ?>
                                        <div style="font-weight: 500;"><?php echo $row['product_name']; ?></div>
                                    </td>
                                    <td><?php echo $row['farmer_name']; ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
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
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
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
