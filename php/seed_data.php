<?php
require('db.php');

// Increase time limit for large insertion
set_time_limit(300); 

echo "Starting Data Seeding...<br>";
ob_flush();
flush();

// Clear existing data to ensure only the requested 10 classes exist
mysqli_query($con, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($con, "TRUNCATE TABLE reviews");
mysqli_query($con, "TRUNCATE TABLE cart");
mysqli_query($con, "TRUNCATE TABLE messages");
mysqli_query($con, "TRUNCATE TABLE orders");
mysqli_query($con, "TRUNCATE TABLE products");
// We keep users but clear their generated data. 
// Optional: TRUNCATE users if we want to regenerate them too, but let's keep it simple and just add more or assume previous run made users.
// Actually, to get clean slate, let's delete generated users (role != admin)
mysqli_query($con, "DELETE FROM users WHERE username != 'admin'");
mysqli_query($con, "TRUNCATE TABLE farm_profiles");
mysqli_query($con, "SET FOREIGN_KEY_CHECKS = 1");
echo "Cleared old data...<br>";

$faker_names = ['John', 'Jane', 'Mike', 'Sarah', 'David', 'Emily', 'Robert', 'Jessica', 'William', 'Ashley', 'James', 'Linda', 'George', 'Karen', 'Charles', 'Nancy', 'Joseph', 'Betty', 'Thomas', 'Lisa'];
$crops = ['Apple', 'Pineapple', 'Mango', 'Rice', 'Avocado', 'Pumpkin', 'Lettuce', 'Carrot', 'Orange', 'Cucumber'];
$locations = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'];
$adjectives = ['Fresh', 'Organic', 'Premium', 'Local', 'Sweet', 'Juicy', 'Red', 'Green', 'Yellow', 'Best'];

// 1. Create Users (50 Farmers, 200 Buyers)
$farmers = [];
$buyers = [];

// Create Farmers
echo "Creating Farmers...<br>";
for ($i = 0; $i < 50; $i++) {
    $username = 'farmer_' . $i . '_' . rand(100,999);
    $email = $username . '@example.com';
    $password = password_hash('password', PASSWORD_DEFAULT);
    
    if (mysqli_query($con, "INSERT INTO users (username, email, password, role, is_approved) VALUES ('$username', '$email', '$password', 'farmer', 1)")) {
        $user_id = mysqli_insert_id($con);
        $farmers[] = $user_id;
        
        // Create Farm Profile
        $farm_name = $faker_names[array_rand($faker_names)] . "'s Farm";
        $loc = $locations[array_rand($locations)];
        $prod = $crops[array_rand($crops)];
        mysqli_query($con, "INSERT INTO farm_profiles (user_id, farm_name, location, product_type) VALUES ('$user_id', '$farm_name', '$loc', '$prod')");
    }
}

// Create Buyers
echo "Creating Buyers...<br>";
for ($i = 0; $i < 200; $i++) {
    $username = 'buyer_' . $i . '_' . rand(100,999);
    $email = $username . '@example.com';
    $password = password_hash('password', PASSWORD_DEFAULT);
    
    if (mysqli_query($con, "INSERT INTO users (username, email, password, role, is_approved) VALUES ('$username', '$email', '$password', 'buyer', 1)")) {
        $buyers[] = mysqli_insert_id($con);
    }
}

// 2. Create Products (Each farmer gets 10-20 products) -> ~750 products
echo "Creating Products...<br>";
$product_ids = [];
foreach ($farmers as $fid) {
    $num_prods = rand(10, 20);
    for ($j = 0; $j < $num_prods; $j++) {
        $crop = $crops[array_rand($crops)];
        $adj = $adjectives[array_rand($adjectives)];
        $name = "$adj $crop";
        $desc = "High quality $name harvested fresh from our farm. Guaranteed satisfaction.";
        $qty = rand(50, 500);
        $price = rand(2, 50);
        
        if (mysqli_query($con, "INSERT INTO products (farmer_id, name, description, quantity, price, harvest_date) VALUES ('$fid', '$name', '$desc', '$qty', '$price', NOW())")) {
            $product_ids[] = mysqli_insert_id($con);
        }
    }
}

// 3. Create Orders (Each buyer places 20-30 orders) -> ~5000 orders
echo "Creating Orders...<br>";
$statuses = ['Pending', 'Approved', 'Delivered', 'Cancelled'];
foreach ($buyers as $bid) {
    $num_orders = rand(20, 30);
    for ($k = 0; $k < $num_orders; $k++) {
        $pid = $product_ids[array_rand($product_ids)];
        // Get product details (price, farmer)
        $p_res = mysqli_query($con, "SELECT price, farmer_id FROM products WHERE id='$pid'");
        $p_row = mysqli_fetch_assoc($p_res);
        
        if ($p_res && $p_row) {
            $qty = rand(1, 10);
            $total = $qty * $p_row['price'];
            $fid = $p_row['farmer_id'];
            $status = $statuses[array_rand($statuses)];
            $date = date("Y-m-d H:i:s", strtotime("-" . rand(0, 30) . " days")); // Random date in last 30 days
            
            mysqli_query($con, "INSERT INTO orders (buyer_id, farmer_id, product_id, quantity, total_price, status, created_at) 
                                VALUES ('$bid', '$fid', '$pid', '$qty', '$total', '$status', '$date')");
        }
    }
}

// 4. Create Messages (~2000)
echo "Creating Messages...<br>";
for ($m = 0; $m < 2000; $m++) {
    $sender = (rand(0,1)) ? $farmers[array_rand($farmers)] : $buyers[array_rand($buyers)];
    $receiver = ($sender == $farmers[0]) ? $buyers[array_rand($buyers)] : $farmers[array_rand($farmers)]; // Simplified logic
    $msg = "Is this product still available? I want to buy " . rand(10,100) . " units.";
    $read = rand(0,1);
    
    mysqli_query($con, "INSERT INTO messages (sender_id, receiver_id, message, is_read, created_at) VALUES ('$sender', '$receiver', '$msg', '$read', NOW())");
}

// 5. Create Reviews (~2000)
echo "Creating Reviews...<br>";
for ($r = 0; $r < 2000; $r++) {
    $bid = $buyers[array_rand($buyers)];
    $pid = $product_ids[array_rand($product_ids)];
    $rating = rand(3, 5); // Mostly good reviews
    $comments = ['Great quality!', 'Fresh and tasty.', 'Will buy again.', 'Highly recommended.', 'Fast delivery.'];
    $comment = $comments[array_rand($comments)];
    
    mysqli_query($con, "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES ('$pid', '$bid', '$rating', '$comment')");
}

echo "Data Seeding Completed! Added approx 10,000+ records across tables.";
?>
