<?php
require('db.php');
require('auth_session.php');
checkLogin();

$user_id = $_SESSION['user_id'];

// Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Check if item exists in cart
    $check = mysqli_query($con, "SELECT * FROM cart WHERE user_id='$user_id' AND product_id='$product_id'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($con, "UPDATE cart SET quantity = quantity + $quantity WHERE user_id='$user_id' AND product_id='$product_id'");
    } else {
        mysqli_query($con, "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$user_id', '$product_id', '$quantity')");
    }
    
    // Redirect back
    if(isset($_SERVER['HTTP_REFERER'])) {
        $separator = (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY) == NULL) ? '?' : '&';
        header("Location: " . $_SERVER['HTTP_REFERER'] . $separator . "msg=added");
    } else {
        header("Location: marketplace.php?msg=added");
    }
}

// Remove Item
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    mysqli_query($con, "DELETE FROM cart WHERE id='$cart_id' AND user_id='$user_id'");
    header("Location: cart.php");
}

// Checkout
if (isset($_POST['checkout'])) {
    // Get all items
    $cart_items = mysqli_query($con, "SELECT * FROM cart WHERE user_id='$user_id'");
    
    while($item = mysqli_fetch_assoc($cart_items)) {
        $product_id = $item['product_id'];
        $qty = $item['quantity'];
        
        // Get Product Info for Price and Farmer
        $prod = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM products WHERE id='$product_id'"));
        if ($prod && $prod['quantity'] >= $qty) {
            $farmer_id = $prod['farmer_id'];
            $total_price = $qty * $prod['price'];
            
            // Create Order
            mysqli_query($con, "INSERT INTO orders (buyer_id, farmer_id, product_id, quantity, total_price, status) 
                                VALUES ('$user_id', '$farmer_id', '$product_id', '$qty', '$total_price', 'Pending')");
            
            // Update Stock
            mysqli_query($con, "UPDATE products SET quantity = quantity - $qty WHERE id='$product_id'");
        }
    }
    
    // Clear Cart
    mysqli_query($con, "DELETE FROM cart WHERE user_id='$user_id'");
    header("Location: my_orders.php?msg=ordered");
}
?>
