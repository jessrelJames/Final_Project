<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['buyer']);

$user_id = $_SESSION['user_id'];

$cart = mysqli_query($con, "
    SELECT c.*, p.name, p.price, p.image, p.farmer_id 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id='$user_id'
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Cart - Export & Trade</title>
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
                <li><a href="cart.php" class="active"><i class="fas fa-shopping-cart"></i> My Cart</a></li>
                <li><a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="buyer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>Shopping Cart</h2>
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
                <?php if (mysqli_num_rows($cart) > 0): ?>
                    <div class="panel">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $grand_total = 0;
                                    while($row = mysqli_fetch_assoc($cart)): 
                                        $subtotal = $row['price'] * $row['quantity'];
                                        $grand_total += $subtotal;
                                    ?>
                                    <tr>
                                        <td style="display: flex; align-items: center; gap: 15px;">
                                            <?php if($row['image']): ?>
                                                <img src="<?php echo $row['image']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; background: #eee; border-radius: 6px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-image" style="color:#aaa;"></i></div>
                                            <?php endif; ?>
                                            <div>
                                                <div style="font-weight: 500; font-size: 1rem;"><?php echo $row['name']; ?></div>
                                                <a href="product_details.php?id=<?php echo $row['product_id']; ?>" style="font-size: 0.8rem; color: var(--primary); text-decoration: none;">View Details</a>
                                            </div>
                                        </td>
                                        <td style="font-weight: 600;">$<?php echo $row['price']; ?></td>
                                        <td>
                                            <span style="background: #f1f5f9; padding: 5px 15px; border-radius: 15px; font-weight: 600;"><?php echo $row['quantity']; ?></span>
                                        </td>
                                        <td style="font-weight: 700; color: var(--primary);">$<?php echo number_format($subtotal, 2); ?></td>
                                        <td>
                                            <a href="cart_action.php?remove=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; background: #fee2e2; color: #ef4444; border: none;"><i class="fas fa-trash"></i> Remove</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div style="margin-top: 30px; display: flex; justify-content: flex-end; align-items: center; gap: 20px; border-top: 1px solid #f0f0f0; padding-top: 20px;">
                            <div style="text-align: right;">
                                <span style="display: block; color: var(--text-secondary); font-size: 0.9rem;">Grand Total</span>
                                <span style="font-size: 2rem; font-weight: 700; color: var(--text-primary);">$<?php echo number_format($grand_total, 2); ?></span>
                            </div>
                            <form action="cart_action.php" method="post">
                                <button type="submit" name="checkout" class="btn btn-primary" style="padding: 15px 30px; font-size: 1.1rem;"><i class="fas fa-check-circle"></i> Checkout</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="panel" style="text-align: center; padding: 50px 20px;">
                        <div style="width: 80px; height: 80px; background: #f0f9ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            <i class="fas fa-shopping-cart fa-3x" style="color: var(--primary);"></i>
                        </div>
                        <h3 style="margin-bottom: 10px; color: var(--text-primary);">Your cart is empty</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 25px;">Looks like you haven't added anything to your cart yet.</p>
                        <a href="buyer_dashboard.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
