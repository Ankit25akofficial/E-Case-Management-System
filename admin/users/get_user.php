<?php
// Return user info as JSON for editing
include '../../includes/config.php';
include '../../includes/functions.php';
if(!is_logged_in() || !is_user_type('admin')) exit('Unauthorized');
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user = [];
$sql = "SELECT id, username, email, user_type, full_name, status FROM users WHERE id = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if($row = mysqli_fetch_assoc($result)) {
        $user = $row;
    }
    mysqli_stmt_close($stmt);
}
header('Content-Type: application/json');
echo json_encode($user);
