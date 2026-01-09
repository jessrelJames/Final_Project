<?php
session_start();

function checkLogin() {
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
}

function checkRole($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        // Simple unauthorized redirect or message
        echo "Access Denied. You do not have permission to view this page.";
        exit();
    }
}
?>
