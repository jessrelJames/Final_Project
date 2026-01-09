<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['admin']);

// CSV Export Logic
if (isset($_GET['type'])) {
    $type = $_GET['type'];
    $filename = "report_" . $type . "_" . date("Ymd") . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if ($type == 'users') {
        fputcsv($output, array('ID', 'Username', 'Email', 'Role', 'Status', 'Created At'));
        $query = mysqli_query($con, "SELECT id, username, email, role, is_approved, created_at FROM users");
        while($row = mysqli_fetch_assoc($query)) {
            $row['is_approved'] = $row['is_approved'] ? 'Approved' : 'Pending';
            fputcsv($output, $row);
        }
    } elseif ($type == 'orders') {
        fputcsv($output, array('Order ID', 'Buyer Name', 'Product', 'Quantity', 'Total Price', 'Status', 'Date'));
        $query = mysqli_query($con, "
            SELECT o.id, u.username, p.name, o.quantity, o.total_price, o.status, o.created_at 
            FROM orders o 
            JOIN users u ON o.buyer_id = u.id 
            JOIN products p ON o.product_id = p.id
        ");
        while($row = mysqli_fetch_assoc($query)) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

// Visual Report Logic
$total_revenue = mysqli_fetch_assoc(mysqli_query($con, "SELECT SUM(total_price) as total FROM orders WHERE status != 'Cancelled'"))['total'] ?? 0;
$total_orders = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM orders"))['c'];
$total_users = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) as c FROM users"))['c'];

// Graph Data: Last 7 Days Revenue
$dates = [];
$totals = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
    $totals[] = 0; // Initialize with 0
}

$chart_query = mysqli_query($con, "
    SELECT DATE(created_at) as date, SUM(total_price) as total 
    FROM orders 
    WHERE status != 'Cancelled' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
");

while($row = mysqli_fetch_assoc($chart_query)) {
    $key = array_search($row['date'], $dates);
    if ($key !== false) {
        $totals[$key] = $row['total'];
    }
}

// Format for JS (Revenue)
$js_dates = "'" . implode("','", $dates) . "'";
$js_totals = implode(",", $totals);

// Graph Data: Order Statistics (Status Distribution)
$status_counts = ['Pending' => 0, 'Delivered' => 0, 'Cancelled' => 0];
// Ensure standard statuses exist, but fetch actuals
$status_query = mysqli_query($con, "SELECT status, COUNT(*) as count FROM orders GROUP BY status");
while($row = mysqli_fetch_assoc($status_query)) {
    $status_counts[$row['status']] = $row['count'];
}

$js_statuses = "'" . implode("','", array_keys($status_counts)) . "'";
$js_status_counts = implode(",", array_values($status_counts));


// Recent Orders Table
$recent_orders = mysqli_query($con, "
    SELECT o.id, u.username, p.name, o.total_price, o.status, o.created_at 
    FROM orders o 
    JOIN users u ON o.buyer_id = u.id 
    JOIN products p ON o.product_id = p.id
    ORDER BY o.created_at DESC LIMIT 10
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports - Export & Trade</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .sidebar, .btn, .user-profile, .print-hidden {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }
            body, .app-container {
                background: white !important;
                height: auto !important;
            }
            .panel, .stats-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                break-inside: avoid;
            }
            header {
                padding: 10px 0 !important;
                border-bottom: 2px solid #28a745;
                margin-bottom: 20px;
            }
            /* Ensure charts print correctly */
            canvas {
                max-width: 100% !important;
            }
        }
    </style>
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
                <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="admin_reports.php" class="active"><i class="fas fa-file-alt"></i> Reports</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>System Reports</h2>
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
                
                <!-- Export Actions -->
                <div class="panel">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3>Export Data</h3>
                            <p style="color: var(--text-secondary);">Download comprehensive data in CSV format.</p>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button onclick="window.print()" class="btn" style="background: #6c757d; color: white;"><i class="fas fa-print"></i> Print Report</button>
                            <a href="admin_reports.php?type=users" class="btn btn-primary"><i class="fas fa-download"></i> User Report</a>
                            <a href="admin_reports.php?type=orders" class="btn btn-primary"><i class="fas fa-download"></i> Order Report</a>
                        </div>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="grid">
                    <div class="stats-card">
                        <div class="stats-icon green"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="stats-info">
                            <h3>Total Revenue</h3>
                            <p>$<?php echo number_format($total_revenue, 2); ?></p>
                        </div>
                    </div>
                    <div class="stats-card yellow"><div class="stats-icon yellow"><i class="fas fa-shopping-cart"></i></div>
                        <div class="stats-info">
                            <h3>Total Orders</h3>
                            <p><?php echo $total_orders; ?></p>
                        </div>
                    </div>
                    <div class="stats-card blue"><div class="stats-icon blue"><i class="fas fa-users"></i></div>
                        <div class="stats-info">
                            <h3>Total Users</h3>
                            <p><?php echo $total_users; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Charts Grid -->
                <div class="grid" style="grid-template-columns: 2fr 1fr;">
                    
                    <!-- Sales Graph -->
                    <div class="panel">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h3>Revenue Overview (Last 7 Days)</h3>
                        </div>
                        <div style="height: 300px;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>

                    <!-- Order Stats Graph -->
                    <div class="panel">
                        <div style="margin-bottom: 20px;">
                            <h3>Order Statistics</h3>
                        </div>
                        <div style="height: 300px; position: relative;">
                            <canvas id="orderChart"></canvas>
                        </div>
                    </div>

                </div>

                <!-- Detailed Transactions -->
                <div class="panel">
                    <h3>Recent Transactions</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Buyer</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($recent_orders)): ?>
                                        <tr>
                                            <td>#<?php echo $row['id']; ?></td>
                                            <td><?php echo $row['username']; ?></td>
                                            <td><?php echo $row['name']; ?></td>
                                            <td>$<?php echo number_format($row['total_price'], 2); ?></td>
                                            <td>
                                                <?php if($row['status'] == 'Pending'): ?>
                                                    <span style="color: var(--warning); font-weight: bold;">Pending</span>
                                                <?php elseif($row['status'] == 'Delivered'): ?>
                                                    <span style="color: var(--primary); font-weight: bold;">Delivered</span>
                                                <?php elseif($row['status'] == 'Cancelled'): ?>
                                                    <span style="color: var(--danger); font-weight: bold;">Cancelled</span>
                                                <?php else: ?>
                                                    <span><?php echo $row['status']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; padding: 20px;">No recent transactions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Chart Configuration -->
    <script>
        // Revenue Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(40, 167, 69, 0.2)');
        gradient.addColorStop(1, 'rgba(40, 167, 69, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php echo $js_dates; ?>],
                datasets: [{
                    label: 'Revenue ($)',
                    data: [<?php echo $js_totals; ?>],
                    borderColor: '#28a745',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#28a745',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)', borderDash: [5, 5] },
                        ticks: { callback: function(value) { return '$' + value; } }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // Order Statistics Chart
        const ctxOrder = document.getElementById('orderChart').getContext('2d');
        new Chart(ctxOrder, {
            type: 'doughnut',
            data: {
                labels: [<?php echo $js_statuses; ?>],
                datasets: [{
                    data: [<?php echo $js_status_counts; ?>],
                    backgroundColor: [
                        '#ffc107', // Warning/Pending
                        '#28a745', // Success/Delivered
                        '#dc3545', // Danger/Cancelled
                        '#17a2b8'  // Info/Other
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
                        labels: { usePointStyle: true, padding: 20 }
                    }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>
