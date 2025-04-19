<?php
// Registration script for ECMS
require_once 'includes/config.php';
require_once 'includes/functions.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $user_type = 'client'; // Only allow client registration for security

    // Validation
    if (!$username || !$email || !$password || !$confirm_password || !$full_name) {
        $message = '<span class="text-red-600">All fields are required.</span>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<span class="text-red-600">Invalid email address.</span>';
    } elseif ($password !== $confirm_password) {
        $message = '<span class="text-red-600">Passwords do not match.</span>';
    } else {
        // Check if username or email exists
        $sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = '<span class="text-red-600">Username or email already exists.</span>';
        } else {
            // Register user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, email, user_type, full_name) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sssss', $username, $hashed_password, $email, $user_type, $full_name);
            if (mysqli_stmt_execute($stmt)) {
                $message = '<span class="text-green-600">Registration successful! <a href=\'login.php\' class=\'text-blue-600 underline\'>Login here</a>.</span>';
            } else {
                $message = '<span class="text-red-600">Registration failed. Please try again.</span>';
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Case Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-md p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Create Your Account</h1>
        <?php if ($message) echo '<div class="mb-4 text-center">' . $message . '</div>'; ?>
        <form method="post" action="">
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Full Name</label>
                <input type="text" name="full_name" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Username</label>
                <input type="text" name="username" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring" required>
            </div>
            <button type="submit" class="w-full bg-blue-700 text-white py-2 rounded hover:bg-blue-800 transition">Register</button>
        </form>
        <div class="mt-6 text-center text-gray-600">
            Already have an account? <a href="login.php" class="text-blue-600 underline">Login here</a>
        </div>
    </div>
</body>
</html>
