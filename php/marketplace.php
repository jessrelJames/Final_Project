<?php
require('db.php');
session_start();
// Optional: Allow public to view? Use checkLogin if you want restricted.
// Let's assume public can view, but must login to buy.

$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$where = "WHERE p.quantity > 0";
if ($search) {
    $where .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%' OR fp.location LIKE '%$search%')";
}

$query = "
    SELECT p.*, f.username as farmer_name, fp.location 
    FROM products p 
    JOIN users f ON p.farmer_id = f.id 
    LEFT JOIN farm_profiles fp ON f.id = fp.user_id
    $where
    ORDER BY p.created_at DESC
";
$products = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Marketplace - FarmConnect</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        .search-box {
            flex-grow: 1;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--card-bg);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav>
            <div class="logo">FarmConnect Marketplace</div>
            <ul>
                <?php if(isset($_SESSION['username'])): ?>
                    <li><a href="login.php">Dashboard</a></li>
                    <li><a href="logout.php" class="btn btn-secondary">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <section class="hero" style="padding: 40px 20px;">
            <h1>Fresh Produce Marketplace</h1>
            <p>Explore the best quality crops directly from our farmers.</p>
        </section>

        <form action="" method="get" class="filters">
            <input type="text" name="search" class="search-box" placeholder="Search for carrots, apples, or location..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn">Search</button>
        </form>

        <div class="grid">
            <?php if(mysqli_num_rows($products) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($products)): ?>
                    <div class="card">
                        <?php if($row['image']): ?>
                            <img src="<?php echo $row['image']; ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 15px;">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: #334155; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; color: var(--text-secondary);">No Image</div>
                        <?php endif; ?>
                        
                        <h3><a href="product_details.php?id=<?php echo $row['id']; ?>" style="color: white;"><?php echo $row['name']; ?></a></h3>
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 5px;">
                            by <?php echo $row['farmer_name']; ?> 
                            <?php if($row['location']) echo "In $row[location]"; ?>
                        </p>
                        <p style="margin-bottom: 15px;"><?php echo substr($row['description'], 0, 80) . '...'; ?></p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                            <span style="font-size: 1.2rem; font-weight: 700; color: var(--primary);">$<?php echo $row['price']; ?> / unit</span>
                            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'buyer'): ?>
                                <button onclick="addToCart(<?php echo $row['id']; ?>, '<?php echo $row['name']; ?>', <?php echo $row['quantity']; ?>)" class="btn">Add to Cart</button>
                                <button onclick="messageFarmer(<?php echo $row['farmer_id']; ?>, '<?php echo $row['name']; ?>', '<?php echo $row['farmer_name']; ?>')" class="btn btn-secondary" style="margin-left: 5px;">Message</button>
                            <?php elseif(!isset($_SESSION['user_id'])): ?>
                                <a href="login.php" class="btn btn-secondary">Login to Buy</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; color: var(--text-secondary);">No products found matching your search.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- JS for Actions -->
    <script>
        function addToCart(id, name, maxQty) {
            let qty = prompt("Enter quantity for " + name + " (Max: " + maxQty + "):");
            if (qty && qty > 0 && qty <= maxQty) {
                // Create a temporary form to submit
                let form = document.createElement('form');
                form.action = 'cart_action.php';
                form.method = 'POST';
                
                let actionInput = document.createElement('input');
                actionInput.name = 'add_to_cart';
                actionInput.value = '1';
                form.appendChild(actionInput);
                
                let idInput = document.createElement('input');
                idInput.name = 'product_id';
                idInput.value = id;
                form.appendChild(idInput);
                
                let qtyInput = document.createElement('input');
                qtyInput.name = 'quantity';
                qtyInput.value = qty;
                form.appendChild(qtyInput);
                
                document.body.appendChild(form);
                form.submit();
                alert(name + " added to cart!");
            } else if (qty) {
                alert("Invalid quantity!");
            }
        }

        function messageFarmer(farmerId, productName, farmerName) {
            let msg = prompt("Send message to " + farmerName + " about " + productName + ":");
            if (msg) {
                let form = document.createElement('form');
                form.action = 'send_message.php';
                form.method = 'POST';
                
                let idInput = document.createElement('input');
                idInput.name = 'farmer_id';
                idInput.value = farmerId;
                form.appendChild(idInput);
                
                let pInput = document.createElement('input');
                pInput.name = 'product_name';
                pInput.value = productName;
                form.appendChild(pInput);

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
