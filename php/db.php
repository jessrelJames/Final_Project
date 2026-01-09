<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "farm_system";

$con = mysqli_connect($host, $user, $pass, $db_name);

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
?>
