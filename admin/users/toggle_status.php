<?php
include '../../includes/config.php';
include '../../includes/functions.php';
if(!is_logged_in() || !is_user_type('admin')) exit('Unauthorized');
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "UPDATE users SET status = IF(status='active','inactive','active') WHERE id = ?";
if($stmt = mysqli_prepare($stmt, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
echo 'OK';
