<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['admin']);

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    mysqli_query($con, "UPDATE users SET is_approved=1 WHERE id=$id");
    header("Location: admin_users.php");
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Optional: Delete related Data? For now just user. Foreign keys are ON DELETE CASCADE usually but we set that in schema.
    mysqli_query($con, "DELETE FROM users WHERE id=$id");
    header("Location: admin_users.php");
}

$users = mysqli_query($con, "SELECT * FROM users WHERE role!='admin' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Export & Trade</title>
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
                <li><a href="admin_users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="admin_reports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>Manage Users</h2>
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
                    <h3>User List</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($users)): ?>
                                <tr>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><span style="font-weight: 500; font-size: 0.9rem; padding: 2px 8px; border-radius: 4px; background: #eee;"><?php echo ucfirst($row['role']); ?></span></td>
                                    <td>
                                        <?php if($row['is_approved']): ?>
                                            <span style="color: var(--primary); font-weight: 600;">Active</span>
                                        <?php else: ?>
                                            <span style="color: var(--warning); font-weight: 600;">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!$row['is_approved']): ?>
                                            <a href="admin_users.php?approve=<?php echo $row['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;">Approve</a>
                                        <?php endif; ?>
                                        <a href="admin_users.php?delete=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.8rem; background: var(--danger);" onclick="return confirm('Delete user?')">Delete</a>
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
