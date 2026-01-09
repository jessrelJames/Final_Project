<?php
require('db.php');
require('auth_session.php');
checkLogin();

$sender_id = $_SESSION['user_id'];
$message_status = "";

// Handle Message Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['receiver_id']) && isset($_POST['message'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $message = mysqli_real_escape_string($con, $_POST['message']);
    
    // Append product context if it's there and not already in the message
    if(isset($_POST['product_name']) && !empty($_POST['product_name'])) {
        $context = "[Inquiry about " . mysqli_real_escape_string($con, $_POST['product_name']) . "]\n";
        // Prevent doubling up if the user already edited the message to include it
        if (strpos($message, $context) === false) {
             $message = $context . $message;
        }
    }

    $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$sender_id', '$receiver_id', '$message')";
    
    if (mysqli_query($con, $query)) {
        $message_status = "success";
    } else {
        $message_status = "error";
    }
}

// Retrieve GET parameters for pre-filling
$receiver_id = isset($_GET['farmer_id']) ? intval($_GET['farmer_id']) : 0;
$product_name = isset($_GET['product_name']) ? $_GET['product_name'] : '';

// Resolve Receiver Name
$receiver_name = "Unknown User";
if ($receiver_id > 0) {
    $user_query = mysqli_query($con, "SELECT username FROM users WHERE id='$receiver_id'");
    if ($row = mysqli_fetch_assoc($user_query)) {
        $receiver_name = $row['username'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Send Message - Export & Trade</title>
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
                <li><a href="buyer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>Compose Message</h2>
                <div class="user-profile">
                    <div class="user-info">
                        <span><?php echo $_SESSION['username']; ?></span>
                    </div>
                </div>
            </header>

            <div class="page-content">
                <div class="panel" style="max-width: 600px; margin: 0 auto;">
                    
                    <?php if ($message_status == 'success'): ?>
                        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                            <i class="fas fa-check-circle"></i> Message sent successfully! 
                            <a href="messages.php" style="color: #155724; font-weight: bold; margin-left: 10px;">Go to Inbox</a>
                        </div>
                    <?php elseif ($message_status == 'error'): ?>
                         <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                            <i class="fas fa-exclamation-circle"></i> Error sending message. Please try again.
                        </div>
                    <?php endif; ?>

                    <?php if ($receiver_id > 0): ?>
                        <form action="" method="post">
                            <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product_name); ?>">
                            
                            <div class="form-group">
                                <label>To:</label>
                                <input type="text" value="<?php echo htmlspecialchars($receiver_name); ?>" disabled style="background: #f8f9fa;">
                            </div>
                            
                            <?php if ($product_name): ?>
                            <div class="form-group">
                                <label>Regarding:</label>
                                <input type="text" value="<?php echo htmlspecialchars($product_name); ?>" disabled style="background: #f8f9fa;">
                            </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label>Message</label>
                                <textarea name="message" rows="6" required placeholder="Write your message here..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                            </div>
                            
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Message</button>
                                <a href="buyer_dashboard.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div style="text-align: center; color: #666; padding: 40px;">
                            <i class="fas fa-user-slash fa-3x" style="margin-bottom: 20px; color: #ccc;"></i>
                            <p>No recipient selected.</p>
                            <a href="buyer_dashboard.php" class="btn btn-primary" style="margin-top: 20px;">Return to Marketplace</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
