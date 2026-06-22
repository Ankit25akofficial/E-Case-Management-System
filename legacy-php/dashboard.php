<?php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

// Check if user is logged in
if(!is_logged_in()) {
    redirect('login.php');
}

// Redirect to appropriate dashboard based on user type
if(is_user_type('admin')) {
    redirect('admin/index.php');
} elseif(is_user_type('client')) {
    redirect('client/index.php');
} elseif(is_user_type('judge')) {
    redirect('judge/index.php');
} else {
    // Logout if user type is invalid
    session_destroy();
    redirect('login.php');
}
?>
