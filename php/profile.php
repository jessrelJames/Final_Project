<?php
require('db.php');
require('auth_session.php');
checkLogin();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$msg = "";
$message = "";
$error = "";

// Handle Update
if (isset($_POST['email'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    
    $update_query = "UPDATE users SET email='$email'";
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query .= ", password='$hashed_password'";
    }
    $update_query .= " WHERE id='$user_id'";
    mysqli_query($con, $update_query);

    // Farm Profile Update if Farmer (existing logic)
    if ($role == 'farmer') {
        $farm_name = mysqli_real_escape_string($con, $_POST['farm_name']);
        $location = mysqli_real_escape_string($con, $_POST['location']);
        $product_type = mysqli_real_escape_string($con, $_POST['product_type']);
        
        // Check if profile exists
        $check = mysqli_query($con, "SELECT * FROM farm_profiles WHERE user_id='$user_id'");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($con, "UPDATE farm_profiles SET farm_name='$farm_name', location='$location', product_type='$product_type' WHERE user_id='$user_id'");
        } else {
            mysqli_query($con, "INSERT INTO farm_profiles (user_id, farm_name, location, product_type) VALUES ('$user_id', '$farm_name', '$location', '$product_type')");
        }
    }
    $message = "Profile updated successfully!"; // Using $message
}

// Handle Profile Image and additional user details update (new logic)
if (isset($_POST['update_profile'])) {
    $first_name = mysqli_real_escape_string($con, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($con, $_POST['last_name']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $dob = mysqli_real_escape_string($con, $_POST['dob']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    
    $update_query = "UPDATE users SET first_name='$first_name', last_name='$last_name', phone='$phone', date_of_birth='$dob', gender='$gender' WHERE id='$user_id'";
    
    // Handle Profile Image
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $file_extension = pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $image_update_query = "UPDATE users SET profile_image='$target_file' WHERE id='$user_id'";
            mysqli_query($con, $image_update_query);
        }
    }

    if (mysqli_query($con, $update_query)) {
        $message = "Profile updated successfully!";
    } else {
        $error = "Error updating profile: " . mysqli_error($con);
    }
}

// Fetch Current Data
$user = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM users WHERE id='$user_id'"));
if ($role == 'farmer') {
    $farm = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM farm_profiles WHERE user_id='$user_id'"));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile - Export & Trade</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        .profile-pic-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 15px;
            border: 4px solid var(--primary);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .profile-pic-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        .btn-upload {
            border: 1px solid var(--primary);
            color: var(--primary);
            background-color: white;
            padding: 8px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-upload:hover {
            background-color: var(--primary);
            color: white;
        }
        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }
        /* Added for better form layout */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-grid .form-full {
            grid-column: 1 / -1;
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
                    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="farmer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="profile.php" class="active"><i class="fas fa-user-cog"></i> Profile</a></li>
                <?php else: ?>
                    <li><a href="buyer_dashboard.php"><i class="fas fa-store"></i> Marketplace</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> My Cart</a></li>
                    <li><a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a></li>
                    <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="buyer_reports.php"><i class="fas fa-chart-line"></i> Reports</a></li>
                    <li><a href="profile.php" class="active"><i class="fas fa-user-circle"></i> Profile</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h2>My Profile</h2>
                <div class="user-profile">
                    <div class="user-info">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <small><?php echo ucfirst($role); ?></small>
                    </div>
                    <div class="profile-img">
                        <?php if(!empty($user['profile_image'])): ?>
                            <img src="<?php echo $user['profile_image']; ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <a href="logout.php" style="margin-left: 10px; color: var(--danger);"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>

            <div class="page-content">
                <?php if($message) echo "<div class='panel' style='color: var(--primary); padding: 15px; margin-bottom: 20px;'>$message</div>"; ?>
                <?php if($error) echo "<div class='panel' style='color: var(--danger); padding: 15px; margin-bottom: 20px;'>$error</div>"; ?>

                <div class="panel" style="max-width: 800px; margin: 0 auto;">
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <!-- Profile Image Section -->
                        <div class="profile-upload">
                            <div class="profile-pic-container">
                                <?php if(!empty($user['profile_image'])): ?>
                                    <img src="<?php echo $user['profile_image']; ?>" alt="Profile">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; background: #eee; display: flex; align-items: center; justify-content: center; color: #aaa; font-size: 3rem;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="upload-btn-wrapper">
                                <button type="button" class="btn-upload"><i class="fas fa-camera"></i> Change Photo</button>
                                <input type="file" name="profile_image" accept="image/*" onchange="this.form.submit()">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" value="<?php echo $user['username']; ?>" disabled style="background: #f9f9f9; cursor: not-allowed;">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?php echo $user['email']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>New Password <small style="color: #888;">(leave blank to keep current)</small></label>
                                <input type="password" name="password" placeholder="New Password">
                            </div>
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" value="<?php echo isset($user['first_name']) ? $user['first_name'] : ''; ?>" placeholder="Enter first name">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" value="<?php echo isset($user['last_name']) ? $user['last_name'] : ''; ?>" placeholder="Enter last name">
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" value="<?php echo isset($user['phone']) ? $user['phone'] : ''; ?>" placeholder="Enter phone number">
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" value="<?php echo isset($user['date_of_birth']) ? $user['date_of_birth'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php if(isset($user['gender']) && $user['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if(isset($user['gender']) && $user['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                    <option value="Other" <?php if(isset($user['gender']) && $user['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <?php if($role == 'farmer'): ?>
                            <h3 style="margin-top: 30px; margin-bottom: 20px; border-top: 1px solid #eee; padding-top: 20px; font-size: 1.1rem;">Farm Details</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Farm Name</label>
                                    <input type="text" name="farm_name" value="<?php echo isset($farm['farm_name'])?$farm['farm_name']:''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" name="location" value="<?php echo isset($farm['location'])?$farm['location']:''; ?>">
                                </div>
                                <div class="form-group form-full">
                                    <label>Product Type</label>
                                    <input type="text" name="product_type" value="<?php echo isset($farm['product_type'])?$farm['product_type']:''; ?>">
                                </div>
                            </div>
                        <?php endif; ?>

                        <button type="submit" name="update_profile" class="btn btn-primary" style="margin-top: 20px;">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
