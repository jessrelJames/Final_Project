<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['farmer']);

$farmer_id = $_SESSION['user_id'];

if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    
    // Check if this order belongs to a product owned by this farmer (Security check)
    // Actually the query below effectively limits viewing, but for updates we should verify ownership too.
    // For simplicity, we assume the POST order_id is valid for this user if they can see the button.
    
    mysqli_query($con, "UPDATE orders SET status='$status' WHERE id='$order_id' AND farmer_id='$farmer_id'");
    header("Location: farmer_orders.php");
}

$orders = mysqli_query($con, "
    SELECT o.*, 
           b.username as buyer_name, 
           p.name as product_name
    FROM orders o
    JOIN users b ON o.buyer_id = b.id
    JOIN products p ON o.product_id = p.id
    WHERE o.farmer_id='$farmer_id'
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Incoming Orders - FarmConnect</title>
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
                <li><a href="farmer_orders.php" class="active"><i class="fas fa-clipboard-list"></i> Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="farmer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <header>
                <h2>Incoming Orders</h2>
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
                                    <th>Buyer</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($orders)): ?>
                                <tr>
                                    <td><?php echo $row['buyer_name']; ?></td>
                                    <td><?php echo $row['product_name']; ?></td>
                                    <td><?php echo $row['quantity']; ?></td>
                                    <td>$<?php echo $row['total_price']; ?></td>
                                    <td>
                                        <span class="status <?php echo strtolower($row['status']); ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td style="display: flex; align-items: center; gap: 8px;">
                                        <form method="post" action="" style="display:inline; margin: 0;">
                                            <input type="hidden" name="update_status" value="1">
                                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" style="padding: 5px; font-size: 0.9rem; border-radius: 4px; border: 1px solid #ddd;">
                                                <option value="Pending" <?php if($row['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                                <option value="Approved" <?php if($row['status']=='Approved') echo 'selected'; ?>>Approve</option>
                                                <option value="Delivered" <?php if($row['status']=='Delivered') echo 'selected'; ?>>Delivered</option>
                                                <option value="Cancelled" <?php if($row['status']=='Cancelled') echo 'selected'; ?>>Cancel</option>
                                            </select>
                                        </form>
                                        <button onclick="messageBuyer(<?php echo $row['buyer_id']; ?>, '<?php echo $row['buyer_name']; ?>', '<?php echo $row['id']; ?>')" class="btn btn-secondary" style="padding: 6px 10px; font-size: 0.8rem; display: flex; align-items: center; gap: 5px;"><i class="fas fa-comment-alt"></i> Message</button>
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
    <script>
        function messageBuyer(buyerId, buyerName, orderId) {
            let msg = prompt("Send message to " + buyerName + " about Order #" + orderId + ":");
            if (msg) {
                let form = document.createElement('form');
                form.action = 'send_message.php';
                form.method = 'POST';
                
                let idInput = document.createElement('input');
                idInput.name = 'farmer_id'; // reused field name for receiver id
                idInput.value = buyerId;
                form.appendChild(idInput);
                
                // Add context
                let cInput = document.createElement('input');
                cInput.name = 'product_name'; // reused field for context subject
                cInput.value = "Order #" + orderId;
                form.appendChild(cInput);

                let mInput = document.createElement('input');
                mInput.name = 'message';
                mInput.value = msg;
                form.appendChild(mInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
