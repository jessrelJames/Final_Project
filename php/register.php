<?php
require('db.php');
session_start();

$message = "";

if (isset($_POST['username'])) {
    $first_name = mysqli_real_escape_string($con, $_REQUEST['first_name']);
    $last_name  = mysqli_real_escape_string($con, $_REQUEST['last_name']);
    $username   = mysqli_real_escape_string($con, $_REQUEST['username']);
    $email      = mysqli_real_escape_string($con, $_REQUEST['email']);
    $phone      = mysqli_real_escape_string($con, $_REQUEST['phone']);
    $dob        = mysqli_real_escape_string($con, $_REQUEST['date_of_birth']);
    $gender     = mysqli_real_escape_string($con, $_REQUEST['gender']);
    $password   = mysqli_real_escape_string($con, $_REQUEST['password']);
    $role       = mysqli_real_escape_string($con, $_REQUEST['role']);
    
    // Auto-approve Admin for testing purposes, or check if table is empty
    $is_approved = ($role === 'admin') ? 1 : 0;
    
    // Check if user exists
    $check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $result = mysqli_query($con, $check_query);
    
    if (mysqli_num_rows($result) > 0) {
        $message = "Username or Email already exists.";
    } else {
        $created_at = date("Y-m-d H:i:s");
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT into `users` (first_name, last_name, username, email, phone, date_of_birth, gender, password, role, is_approved, created_at)
                  VALUES ('$first_name', '$last_name', '$username', '$email', '$phone', '$dob', '$gender', '$hashed_password', '$role', '$is_approved', '$created_at')";
        
        if (mysqli_query($con, $query)) {
            $user_id = mysqli_insert_id($con);
            
            // If Farmer, add profile
            if ($role === 'farmer') {
                $farm_name = mysqli_real_escape_string($con, $_REQUEST['farm_name']);
                $location = mysqli_real_escape_string($con, $_REQUEST['location']);
                $product_type = mysqli_real_escape_string($con, $_REQUEST['product_type']);
                
                $farm_query = "INSERT into `farm_profiles` (user_id, farm_name, location, product_type)
                               VALUES ('$user_id', '$farm_name', '$location', '$product_type')";
                mysqli_query($con, $farm_query);
            }

            $message = "Registration successful! <a href='login.php'>Login here</a>. " . 
                       (($role !== 'admin') ? "Please wait for admin approval." : "");
        } else {
            $message = "Registration failed: " . mysqli_error($con);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Export & Trade</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        function toggleFields() {
            var role = document.getElementById("role").value;
            var farmFields = document.getElementById("farm-fields");
            if (role === "farmer") {
                farmFields.style.display = "block";
            } else {
                farmFields.style.display = "none";
            }
        }
    </script>
</head>
<body class="auth-body">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Export & Trade Facilitation Platform</h1>
            <p>Registration</p>
        </div>
        
        <?php if ($message): ?>
            <div style="background-color: #d1e7dd; color: #0f5132; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="post">
            <div class="form-grid">
                <!-- Row 1: Name -->
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required>
                </div>

                <!-- Row 2: Username & Email (Username is required for login logic, usually distinct from Email) -->
                <!-- The design showed Email and Phone. I will include Username as well since our backend uses it. -->
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <!-- Row 3: Phone & Role -->
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="role" onchange="toggleFields()" required>
                        <option value="">Select Role...</option>
                        <option value="buyer">Buyer</option>
                        <option value="farmer">Farmer</option>
                        <option value="admin">System Admin</option>
                    </select>
                </div>

                <!-- Row 4: DOB & Gender -->
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <div class="gender-options">
                        <label class="gender-option">
                            <input type="radio" name="gender" value="Male" required> Male
                        </label>
                        <label class="gender-option">
                            <input type="radio" name="gender" value="Female"> Female
                        </label>
                    </div>
                </div>

                <!-- Row 5: Password -->
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <!-- Farmer Fields (Full Width) -->
                <div id="farm-fields" class="form-full" style="display: none; border-top: 1px solid #eee; padding-top: 20px; margin-top: 10px;">
                    <h4 style="margin-bottom: 15px; color: var(--primary);">Farm Details</h4>
                    <div class="form-grid">
                        <div class="form-group form-full">
                            <label>Farm Name</label>
                            <input type="text" name="farm_name">
                        </div>
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location">
                        </div>
                        <div class="form-group">
                            <label>Main Product Type</label>
                            <input type="text" name="product_type" placeholder="e.g. Vegetables">
                        </div>
                    </div>
                </div>

                <div class="form-full" style="margin-top: 10px;">
                    <button type="submit" class="btn-block">Create Account</button>
                </div>
            </div>
            
            <div class="auth-footer">
                Already a member? <a href="login.php">Login</a> | <a href="index.php">Home</a>
            </div>
        </form>
    </div>
</body>
</html>
