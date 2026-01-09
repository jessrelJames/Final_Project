<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['farmer']);

$farmer_id = $_SESSION['user_id'];

// Stats
$my_products = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM products WHERE farmer_id='$farmer_id'"))['c'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders WHERE farmer_id='$farmer_id' AND status='Pending'"))['c'];
$completed_orders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders WHERE farmer_id='$farmer_id' AND status='Delivered'"))['c'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Farmer Dashboard - Export & Trade</title>
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
                <li><a href="farmer_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
                <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Add Product</a></li>
                <li><a href="manage_products.php"><i class="fas fa-boxes"></i> My Products</a></li>
                <li><a href="farmer_orders.php"><i class="fas fa-clipboard-list"></i> Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="farmer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>Farm Overview</h2>
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
                
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <div>
                        <h1>
                            <?php 
                                $hour = date('H');
                                if ($hour < 12) echo "Good Morning,";
                                elseif ($hour < 18) echo "Good Afternoon,";
                                else echo "Good Evening,";
                            ?> 
                            <?php echo $_SESSION['username']; ?>!
                        </h1>
                        <p>Here's what's happening on your farm today.</p>
                    </div>
                    <div>
                        <a href="add_product.php" class="btn btn-light"><i class="fas fa-plus"></i> New Product</a>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid">
                    <div class="stats-card">
                        <div class="stats-icon green"><i class="fas fa-seedling"></i></div>
                        <div class="stats-info">
                            <h3>Active Products</h3>
                            <p><?php echo $my_products; ?></p>
                        </div>
                    </div>
                    <div class="stats-card yellow">
                        <div class="stats-icon yellow"><i class="fas fa-clock"></i></div>
                        <div class="stats-info">
                            <h3>Pending Orders</h3>
                            <p><?php echo $pending_orders; ?></p>
                        </div>
                    </div>
                    <div class="stats-card blue">
                        <div class="stats-icon blue"><i class="fas fa-check-circle"></i></div>
                        <div class="stats-info">
                            <h3>Completed Orders</h3>
                            <p><?php echo $completed_orders; ?></p>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
                    <!-- Recent Orders & Quick Actions -->
                    <div style="display: flex; flex-direction: row; gap: 30px;">
                        
                        <!-- Quick Actions -->
                         <div class="panel" style="flex: 1;">
                            <h3>Quick Actions</h3>
                            <div class="quick-actions">
                                <a href="farmer_orders.php" class="quick-action-btn">
                                    <i class="fas fa-clipboard-check"></i>
                                    <span>Manage Orders</span>
                                </a>
                                <a href="messages.php" class="quick-action-btn">
                                    <i class="fas fa-envelope"></i>
                                    <span>Check Messages</span>
                                </a>
                                <a href="profile.php" class="quick-action-btn">
                                    <i class="fas fa-user-edit"></i>
                                    <span>Update Profile</span>
                                </a>
                            </div>
                        </div>

                        <!-- Recent Orders Preview (Mockup or Query) -->
                        <div class="panel" style="flex: 1;">
                            <h3>Recent Activity</h3>
                            <?php
                                $recent = mysqli_query($con, "SELECT * FROM orders WHERE farmer_id='$farmer_id' ORDER BY created_at DESC LIMIT 3");
                                if(mysqli_num_rows($recent) > 0):
                            ?>
                            <ul class="recent-list">
                                <?php while($r = mysqli_fetch_assoc($recent)): ?>
                                <li class="recent-item">
                                    <div class="recent-icon <?php echo strtolower($r['status']); ?>">
                                        <i class="fas fa-shopping-bag"></i>
                                    </div>
                                    <div class="recent-details">
                                        <span class="price">$<?php echo $r['total_price']; ?></span>
                                        <span class="status"><?php echo $r['status']; ?></span>
                                    </div>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                            <?php else: ?>
                                <p style="color: #888; font-size: 0.9rem;">No recent activity.</p>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

