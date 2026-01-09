<?php
require('db.php');
session_start();

$message = "";

if (isset($_POST['username'])) {
    $username = stripslashes($_REQUEST['username']);
    $username = mysqli_real_escape_string($con, $username);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($con, $password);
    
    $query = "SELECT * FROM `users` WHERE username='$username'";
    $result = mysqli_query($con, $query);
    
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            if ($row['is_approved'] == 1) {
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $row['role'];
                $_SESSION['user_id'] = $row['id'];
                
                // Redirect based on role
                if ($row['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($row['role'] === 'farmer') {
                    header("Location: farmer_dashboard.php");
                } else {
                    header("Location: buyer_dashboard.php");
                }
                exit();
            } else {
                $message = "Your account is waiting for admin approval.";
            }
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Export & Trade</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="auth-card" style="max-width: 450px;">
        <div class="auth-header">
            <h1>Export & Trade Facilitation Platform</h1>
            <p>Welcome Back</p>
        </div>
        
        <?php if ($message): ?>
            <div style="background-color: #f8d7da; color: #842029; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="post">
            <div class="form-group">
                <label>Username</label>
                <div style="position: relative;">
                    <i class="fas fa-user" style="position: absolute; top: 13px; left: 15px; color: #999;"></i>
                    <input type="text" name="username" required style="padding-left: 40px;">
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div style="position: relative;">
                    <i class="fas fa-lock" style="position: absolute; top: 13px; left: 15px; color: #999;"></i>
                    <input type="password" name="password" required style="padding-left: 40px;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1.1rem; border-radius: 8px; margin-top: 10px;">Login</button>
            
            <div class="auth-footer">
                New here? <a href="register.php">Create Account</a> <br>
                <a href="index.php" style="color: var(--text-secondary); font-size: 0.85rem; display: inline-block; margin-top: 10px;">Back to Home</a>
            </div>
        </form>
    </div>
</body>
</html>
