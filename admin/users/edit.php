<?php
include '../../includes/config.php';
include '../../includes/functions.php';
if(!is_logged_in() || !is_user_type('admin')) redirect('../../login.php');
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $full_name = sanitize_input($_POST['full_name']);
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $user_type = sanitize_input($_POST['user_type']);
    $status = sanitize_input($_POST['status']);
    $sql = "UPDATE users SET full_name=?, username=?, email=?, user_type=?, status=? WHERE id=?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssssi", $full_name, $username, $email, $user_type, $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: index.php');
    exit;
}
header('Location: index.php');
