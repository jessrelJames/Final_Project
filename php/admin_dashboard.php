<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['admin']);

// Stats
$user_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM users WHERE role!='admin'"))['c'];
$product_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM products"))['c'];
$order_count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders"))['c'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Export & Trade</title>
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
                <li><a href="admin_dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a></li>
                <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="admin_reports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>Dashboard Overview</h2>
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
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <div>
                        <h1>Good <?php echo (date('H') < 12) ? 'Morning' : ((date('H') < 18) ? 'Afternoon' : 'Evening'); ?>, <?php echo $_SESSION['username']; ?>!</h1>
                        <p>Here is what's happening in the system today. Overview reports and statistics.</p>
                    </div>
                    <div>
                        <a href="admin_users.php" class="btn btn-light" style="color: var(--primary); font-weight: bold;">Manage Users <i class="fas fa-users"></i></a>
                    </div>
                </div>
                <div class="grid">
                    <div class="stats-card">
                        <div class="stats-icon blue"><i class="fas fa-users"></i></div>
                        <div class="stats-info">
                            <h3>Total Users</h3>
                            <p><?php echo $user_count; ?></p>
                        </div>
                    </div>
                    <div class="stats-card green"><div class="stats-icon green"><i class="fas fa-box-open"></i></div>
                        <div class="stats-info">
                            <h3>Products</h3>
                            <p><?php echo $product_count; ?></p>
                        </div>
                    </div>
                    <div class="stats-card yellow"><div class="stats-icon yellow"><i class="fas fa-shopping-basket"></i></div>
                        <div class="stats-info">
                            <h3>Orders</h3>
                            <p><?php echo $order_count; ?></p>
                        </div>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns: 2fr 1fr;">
                    <!-- Left: Recent Activity Table -->
                    <div class="panel">
                        <h3>Recent Orders</h3>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Status</th>
                                        <th>Price</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $recent = mysqli_query($con, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
                                    while($r = mysqli_fetch_assoc($recent)): 
                                    ?>
                                    <tr>
                                        <td>#<?php echo $r['id']; ?></td>
                                        <td>
                                            <?php if($r['status'] == 'Pending'): ?>
                                                <span style="color: var(--warning); font-weight: 600;"><?php echo $r['status']; ?></span>
                                            <?php elseif($r['status'] == 'Delivered'): ?>
                                                <span style="color: var(--primary); font-weight: 600;"><?php echo $r['status']; ?></span>
                                            <?php else: ?>
                                                <span style="color: var(--text-secondary); font-weight: 600;"><?php echo $r['status']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>$<?php echo $r['total_price']; ?></td>
                                        <td><?php echo date('M d', strtotime($r['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div style="margin-top: 15px; text-align: center;">
                                <a href="admin_orders.php" class="btn btn-secondary" style="font-size: 0.85rem;">View All Orders</a>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Doughnut Chart -->
                    <div class="panel">
                        <h3>Order Statistics</h3>
                        <div style="height: 250px;">
                             <canvas id="orderChart"></canvas>
                        </div>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    <?php
                        $pending = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders WHERE status='Pending'"))['c'];
                        $approved = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders WHERE status='Approved'"))['c'];
                        $delivered = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders WHERE status='Delivered'"))['c'];
                        $cancelled = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders WHERE status='Cancelled'"))['c'];
                    ?>
                    const ctx = document.getElementById('orderChart');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Pending', 'Approved', 'Delivered', 'Cancelled'],
                            datasets: [{
                                data: [<?php echo "$pending, $approved, $delivered, $cancelled"; ?>],
                                backgroundColor: [
                                    '#f59e0b', // Yellow/Orange
                                    '#10b981', // Green
                                    '#3b82f6', // Blue
                                    '#ef4444'  // Red
                                ],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { 
                                        boxWidth: 12,
                                        padding: 15,
                                        font: { size: 11 }
                                    }
                                }
                            },
                            layout: {
                                padding: 10
                            }
                        }
                    });
                </script>
            </div>
        </div>
    </div>
</body>
</html>
