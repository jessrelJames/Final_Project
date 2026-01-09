<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['farmer']);

$farmer_id = $_SESSION['user_id'];

// Stats for Reports Page (maybe more detailed?)
// For now, let's replicate the dashboard stats or add more depth.
// Let's add Total Revenue since start.
$total_revenue_res = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(total_price) as t FROM orders WHERE farmer_id='$farmer_id' AND status!='Cancelled'"));
$total_revenue = $total_revenue_res['t'] ? $total_revenue_res['t'] : 0;

$my_products = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM products WHERE farmer_id='$farmer_id'"))['c'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders WHERE farmer_id='$farmer_id' AND status='Pending'"))['c'];
$completed_orders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders WHERE farmer_id='$farmer_id' AND status='Delivered'"))['c'];

// Chart Data (Last 7 Days Revenue)
$days = [];
$revenue_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $days[] = date('D', strtotime($date)); // Mon, Tue, etc.
    
    $query = "SELECT SUM(total_price) as total FROM orders 
              WHERE farmer_id='$farmer_id' 
              AND status!='Cancelled' 
              AND DATE(created_at) = '$date'";
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);
    $revenue_data[] = $row['total'] ? $row['total'] : 0;
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
                <li><a href="farmer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
                <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Add Product</a></li>
                <li><a href="manage_products.php"><i class="fas fa-boxes"></i> My Products</a></li>
                <li><a href="farmer_orders.php"><i class="fas fa-clipboard-list"></i> Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="farmer_reports.php" class="active"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>Farm Reports</h2>
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
                
                <!-- Stats Grid -->
                <div class="grid">
                    <div class="stats-card">
                        <div class="stats-icon green"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="stats-info">
                            <h3>Total Revenue</h3>
                            <p>$<?php echo number_format($total_revenue, 2); ?></p>
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

                <div style="margin-top: 30px;">
                    <!-- Sales Chart -->
                    <div class="panel">
                        <h3>Sales Overview (Last 7 Days)</h3>
                        <div style="height: 400px;"> <!-- Made it bigger for the report page -->
                             <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('salesChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($days); ?>,
                datasets: [{
                    label: 'Revenue ($)',
                    data: <?php echo json_encode($revenue_data); ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true
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
