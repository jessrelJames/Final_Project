<?php
require('db.php');
require('auth_session.php');
checkLogin();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get all unique conversations
// This query fetches the most recent message for each contact to order them by recent activity
$contacts_query = "
    SELECT u.id, u.username, 
           MAX(m.created_at) as last_msg_time,
           SUM(CASE WHEN m.receiver_id = '$user_id' AND m.is_read = 0 THEN 1 ELSE 0 END) as unread_count
    FROM users u
    JOIN messages m ON (u.id = m.sender_id AND m.receiver_id = '$user_id') 
                    OR (u.id = m.receiver_id AND m.sender_id = '$user_id')
    WHERE u.id != '$user_id'
    GROUP BY u.id, u.username
    ORDER BY last_msg_time DESC
";
$contacts = mysqli_query($con, $contacts_query);

// Current selected chat
$active_chat_id = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : 0;

if ($active_chat_id > 0) {
    // Mark as read
    mysqli_query($con, "UPDATE messages SET is_read = 1 WHERE sender_id = '$active_chat_id' AND receiver_id = '$user_id'");

    // Fetch conversation
    $messages_query = "
        SELECT * FROM messages 
        WHERE (sender_id = '$user_id' AND receiver_id = '$active_chat_id') 
           OR (sender_id = '$active_chat_id' AND receiver_id = '$user_id') 
        ORDER BY created_at ASC
    ";
    $messages = mysqli_query($con, $messages_query);
    
    // Get Contact Name
    $contact_info = mysqli_fetch_assoc(mysqli_query($con, "SELECT username FROM users WHERE id='$active_chat_id'"));
}

// Send Reply
if (isset($_POST['send_reply']) && $active_chat_id > 0) {
    $msg = mysqli_real_escape_string($con, $_POST['message']);
    if (!empty($msg)) {
        mysqli_query($con, "INSERT INTO messages (sender_id, receiver_id, message) VALUES ('$user_id', '$active_chat_id', '$msg')");
        header("Location: messages.php?chat_id=$active_chat_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages - Export & Trade</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom Chat Styles overrides */
        .chat-container {
            display: flex;
            height: calc(100vh - 140px);
            gap: 20px;
        }
        .contact-list {
            width: 320px;
            flex-shrink: 0;
            overflow-y: auto;
            border-right: 1px solid #f0f0f0;
            padding-right: 15px;
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .chat-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #f0f0f0;
        }
        .contact-item {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid transparent;
        }
        .contact-item:hover {
            background: #f8fafc;
        }
        .contact-item.active {
            background: #f0fdf4; /* Green-50 */
            border: 1px solid #dcfce7; /* Green-100 */
        }
        .unread-badge {
            background: var(--danger);
            color: white;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .chat-history {
            flex-grow: 1;
            padding: 25px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
            background: #f8fafc;
        }
        .message-bubble {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 0.95rem;
            line-height: 1.5;
            position: relative;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .message-bubble.sent {
            align-self: flex-end;
            background: var(--primary);
            color: white;
            border-bottom-right-radius: 2px;
        }
        .message-bubble.received {
            align-self: flex-start;
            background: white;
            color: var(--text-primary);
            border-bottom-left-radius: 2px;
            border: 1px solid #e2e8f0;
        }
        .chat-input {
            padding: 20px;
            border-top: 1px solid #f0f0f0;
            background: #fff;
            display: flex;
            gap: 10px;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-leaf"></i> SmartFarm
            </div>
            <ul class="sidebar-menu">
                <?php if($role == 'admin'): ?>
                    <li><a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
                    <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                    <li><a href="admin_reports.php"><i class="fas fa-file-alt"></i> Reports</a></li>
                <?php elseif($role == 'farmer'): ?>
                    <li><a href="farmer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
                    <li><a href="add_product.php"><i class="fas fa-plus-circle"></i> Add Product</a></li>
                    <li><a href="manage_products.php"><i class="fas fa-boxes"></i> My Products</a></li>
                    <li><a href="farmer_orders.php"><i class="fas fa-clipboard-list"></i> Orders</a></li>
                    <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="farmer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
                <?php else: ?>
                    <li><a href="buyer_dashboard.php"><i class="fas fa-store"></i> Marketplace</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> My Cart</a></li>
                    <li><a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a></li>
                    <li><a href="messages.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="buyer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>Messages</h2>
                <div class="user-profile">
                    <div class="user-info">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <small><?php echo ucfirst($role); ?></small>
                    </div>
                    <div class="profile-img"><i class="fas fa-user"></i></div>
                    <a href="logout.php" style="margin-left: 10px; color: var(--danger);"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <div class="page-content" style="overflow: hidden;"> <!-- Overflow hidden to keep chat contained -->
                <div class="chat-container">
                    <div class="contact-list custom-scroll">
                        <h3 style="padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; margin-bottom: 15px; color: var(--text-primary); font-size: 1.1rem;">Conversations</h3>
                        
                        <?php if(mysqli_num_rows($contacts) > 0): ?>
                            <?php while($c = mysqli_fetch_assoc($contacts)): ?>
                                <a href="messages.php?chat_id=<?php echo $c['id']; ?>" style="text-decoration: none;">
                                    <div class="contact-item <?php echo ($active_chat_id == $c['id']) ? 'active' : ''; ?>">
                                        <div>
                                            <div style="font-weight: 600; font-size: 0.95rem; color: var(--text-primary); margin-bottom: 3px;"><?php echo $c['username']; ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-secondary);">Last: <?php echo date("M d", strtotime($c['last_msg_time'])); ?></div>
                                        </div>
                                        <?php if($c['unread_count'] > 0): ?>
                                            <span class="unread-badge"><?php echo $c['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: var(--text-secondary); text-align: center; margin-top: 30px; font-size: 0.9rem;">No active chats.</p>
                        <?php endif; ?>
                    </div>

                    <div class="chat-area">
                        <?php if ($active_chat_id > 0): ?>
                            <div style="padding: 15px 25px; border-bottom: 1px solid #f0f0f0; background: #fff; display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 35px; height: 35px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #64748b;">
                                        <?php echo strtoupper(substr($contact_info['username'], 0, 1)); ?>
                                    </div>
                                    <h3 style="margin: 0; font-size: 1.1rem; color: var(--text-primary);"><?php echo $contact_info['username']; ?></h3>
                                </div>
                            </div>
                            
                            <div class="chat-history custom-scroll" id="chatHistory">
                                <?php while($msg = mysqli_fetch_assoc($messages)): ?>
                                    <div class="message-bubble <?php echo ($msg['sender_id'] == $user_id) ? 'sent' : 'received'; ?>">
                                        <?php echo nl2br($msg['message']); ?>
                                        <div style="font-size: 0.65rem; opacity: 0.7; margin-top: 4px; text-align: right;">
                                            <?php echo date("H:i", strtotime($msg['created_at'])); ?>
                                            <?php if($msg['sender_id'] == $user_id): ?>
                                                <i class="fas fa-check<?php echo ($msg['is_read']) ? '-double' : ''; ?>" style="margin-left: 3px;"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                            <form action="" method="post" class="chat-input">
                                <input type="text" name="message" placeholder="Type a message..." autocomplete="off" required style="flex-grow: 1; padding: 12px 15px; border: 1px solid #e2e8f0; border-radius: 25px; outline: none; transition: 0.2s;">
                                <button type="submit" name="send_reply" class="btn btn-primary" style="border-radius: 25px; padding: 10px 25px;"><i class="fas fa-paper-plane"></i></button>
                            </form>

                            <script>
                                var chatHistory = document.getElementById("chatHistory");
                                chatHistory.scrollTop = chatHistory.scrollHeight;
                            </script>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; background: #f9fafb;">
                                <div style="font-size: 4rem; opacity: 0.2; margin-bottom: 20px;"><i class="far fa-comments"></i></div>
                                <h3 style="color: var(--text-primary); margin-bottom: 10px;">Select a conversation</h3>
                                <p>Choose a contact from the left to start chatting.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
