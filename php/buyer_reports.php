<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['buyer']);

$buyer_id = $_SESSION['user_id'];

// Stats
// Stats
$total_spent_res = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(total_price) as s FROM orders WHERE buyer_id='$buyer_id' AND status!='Cancelled'"));
$total_spent = $total_spent_res['s'] ? $total_spent_res['s'] : 0;

$total_orders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders WHERE buyer_id='$buyer_id'"))['c'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders WHERE buyer_id='$buyer_id' AND status='Pending'"))['c'];

// Chart Data (Last 7 Days Spending)
$days = [];
$spending_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $days[] = date('D', strtotime($date));
    
    $query_chart = "SELECT SUM(total_price) as total FROM orders 
                    WHERE buyer_id='$buyer_id' 
                    AND status!='Cancelled' 
                    AND DATE(created_at) = '$date'";
    $res_chart = mysqli_query($con, $query_chart);
    $row_chart = mysqli_fetch_assoc($res_chart);
    $spending_data[] = $row_chart['total'] ? $row_chart['total'] : 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports - Export & Trade</title>
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
                <li><a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="buyer_reports.php" class="active"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>My Reports</h2>
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
                
                <!-- Stats Grid -->
                <div class="grid" style="margin-bottom: 30px;">
                    <div class="stats-card">
                        <div class="stats-icon green"><i class="fas fa-wallet"></i></div>
                        <div class="stats-info">
                            <h3>Total Spent</h3>
                            <p>$<?php echo number_format($total_spent, 2); ?></p>
                        </div>
                    </div>
                    <div class="stats-card blue">
                        <div class="stats-icon blue"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stats-info">
                            <h3>Total Orders</h3>
                            <p><?php echo $total_orders; ?></p>
                        </div>
                    </div>
                    <div class="stats-card yellow">
                        <div class="stats-icon yellow"><i class="fas fa-clock"></i></div>
                        <div class="stats-info">
                            <h3>Pending Orders</h3>
                            <p><?php echo $pending_orders; ?></p>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                    <!-- Spending Chart -->
                    <div class="panel">
                        <h3>Spending Overview (Last 7 Days)</h3>
                        <div style="height: 300px;">
                            <canvas id="spendingChart"></canvas>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="panel">
                        <h3>Recent Activity</h3>
                        <?php
                            $recent_buyer = mysqli_query($con, "SELECT * FROM orders WHERE buyer_id='$buyer_id' ORDER BY created_at DESC LIMIT 5");
                            if(mysqli_num_rows($recent_buyer) > 0):
                        ?>
                        <ul class="recent-list">
                            <?php while($r = mysqli_fetch_assoc($recent_buyer)): ?>
                            <li class="recent-item">
                                <div class="recent-icon <?php echo strtolower($r['status']); ?>">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="recent-details">
                                    <span class="price">$<?php echo $r['total_price']; ?></span>
                                    <span class="status"><?php echo $r['status']; ?></span>
                                </div>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                        <?php else: ?>
                            <p style="color: #888; font-size: 0.9rem;">No recent orders.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('spendingChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($days); ?>,
                datasets: [{
                    label: 'Spending ($)',
                    data: <?php echo json_encode($spending_data); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: '#3b82f6',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { color: '#94a3b8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8' }
                    }
                },
                plugins: {
                    legend: { labels: { color: '#64748b' } }
                }
            }
        });
    </script>
</body>
</html>
