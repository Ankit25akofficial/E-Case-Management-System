<?php
if (!defined('HEADER_INCLUDED')) {
    define('HEADER_INCLUDED', true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Case Management System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-blue-900 text-white px-6 py-3">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <img src="emblem.png" alt="Emblem" class="h-10 mr-3 rounded-full shadow-lg border-white transition-transform transform hover:scale-105">
                <div>
                    <h1 class="text-xl font-bold">E-Case Management System</h1>
                    <p class="text-xs">A Government of India Initiative</p>
                </div>
            </div>
            <div class="flex space-x-6">
                <a href="index.php" class="hover:text-yellow-300 transition">Home</a>
                <a href="about.php" class="hover:text-yellow-300 transition">About</a>
                <a href="login.php" class="hover:text-yellow-300 transition">Login</a>
                <a href="register.php" class="hover:text-yellow-300 transition">Register</a>
            </div>
        </div>
    </nav>
<?php } ?>
