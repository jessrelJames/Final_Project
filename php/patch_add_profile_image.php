<?php
require('db.php');

$query = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL";
if (mysqli_query($con, $query)) {
    echo "Column 'profile_image' added successfully.";
} else {
    echo "Error adding column: " . mysqli_error($con);
}
?>
