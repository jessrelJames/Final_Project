<?php
require('db.php');
require('auth_session.php');
checkLogin();
checkRole(['buyer']);

if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $buyer_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Fetch product details
    $product_query = mysqli_query($con, "SELECT * FROM products WHERE id='$product_id'");
    $product = mysqli_fetch_assoc($product_query);

    if ($product && $quantity > 0 && $quantity <= $product['quantity']) {
        $farmer_id = $product['farmer_id'];
        $total_price = $quantity * $product['price'];
        
        $query = "INSERT INTO orders (buyer_id, farmer_id, product_id, quantity, total_price, status)
                  VALUES ('$buyer_id', '$farmer_id', '$product_id', '$quantity', '$total_price', 'Pending')";
        
        if (mysqli_query($con, $query)) {
            // Optional: Reduce stock immediately? 
            // For now, let's keep stock as is until "Approve" (logic not implemented in admin/farmer, so stock stays same)
            // Or simple decrement now.
            // Let's decrement now for simplicity so user can't buy invalid stock.
            mysqli_query($con, "UPDATE products SET quantity = quantity - $quantity WHERE id='$product_id'");
            
            header("Location: my_orders.php?msg=success");
        } else {
            echo "Error processing order.";
        }
    } else {
        echo "Invalid quantity or product unavailable.";
    }
} else {
    header("Location: buyer_dashboard.php");
}
?>
