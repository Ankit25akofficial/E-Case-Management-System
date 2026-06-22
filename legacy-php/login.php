<?php
session_start();
include 'includes/config.php';
include 'includes/functions.php';

// Check if user is already logged in
if(is_logged_in()) {
    redirect('dashboard.php');
}

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if username is empty
    if(empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = sanitize_input($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password, user_type, full_name, login_attempts, last_attempt FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1) {                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $user_type, $full_name, $login_attempts, $last_attempt);
                    if(mysqli_stmt_fetch($stmt)) {
                        // Check if account is locked
                        if($login_attempts >= 5 && $last_attempt && (time() - strtotime($last_attempt)) < 1800) {
                            $login_err = "Account locked. Please try again after 30 minutes.";
                        } else {
                            if(password_verify($password, $hashed_password)) {
                                // Reset login attempts on successful login
                                $reset_sql = "UPDATE users SET login_attempts = 0, last_attempt = NULL WHERE id = ?";
                                if($reset_stmt = mysqli_prepare($conn, $reset_sql)) {
                                    mysqli_stmt_bind_param($reset_stmt, "i", $id);
                                    mysqli_stmt_execute($reset_stmt);
                                    mysqli_stmt_close($reset_stmt);
                                }
                                
                                // Password is correct, so start a new session
                                session_start();
                                
                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["user_id"] = $id;
                                $_SESSION["username"] = $username;
                                $_SESSION["user_type"] = $user_type;
                                $_SESSION["full_name"] = $full_name;
                                $_SESSION["last_activity"] = time();
                                
                                // Redirect user to dashboard page
                                redirect("dashboard.php");
                            } else {
                                // Increment login attempts
                                $attempt_sql = "UPDATE users SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE id = ?";
                                if($attempt_stmt = mysqli_prepare($conn, $attempt_sql)) {
                                    mysqli_stmt_bind_param($attempt_stmt, "i", $id);
                                    mysqli_stmt_execute($attempt_stmt);
                                    mysqli_stmt_close($attempt_stmt);
                                }
                                
                                // Password is not valid, display a generic error message
                                $login_err = "Invalid username or password.";
                            }
                        }
                    }
                } else {
                    // Username doesn't exist, display a generic error message
                    $login_err = "Invalid username or password.";
                }
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

// Get statistics
$stats = array(
    'active_cases' => 0,
    'registered_users' => 0,
    'success_rate' => 0,
    'support_available' => '24/7'
);

// Get active cases count
$sql = "SELECT COUNT(*) as count FROM cases WHERE status != 'closed'";
$result = mysqli_query($conn, $sql);
if($row = mysqli_fetch_assoc($result)) {
    $stats['active_cases'] = $row['count'];
}

// Get registered users count
$sql = "SELECT COUNT(*) as count FROM users";
$result = mysqli_query($conn, $sql);
if($row = mysqli_fetch_assoc($result)) {
    $stats['registered_users'] = $row['count'];
}

// Get success rate (example calculation - you may want to adjust this based on your actual metrics)
$sql = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed FROM cases";
$result = mysqli_query($conn, $sql);
if($row = mysqli_fetch_assoc($result)) {
    if($row['total'] > 0) {
        $stats['success_rate'] = round(($row['closed'] / $row['total']) * 100);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Case Management System | Indian Government</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <style>
        .login-container {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .input-group {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #4a5568;
        }
        .input-field {
            padding-left: 3rem;
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #4a5568;
        }
        .login-btn {
            background: linear-gradient(135deg, #2c5282 0%, #1a365d 100%);
            transition: all 0.3s ease;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .portal-btn {
            transition: all 0.3s ease;
        }
        .portal-btn:hover {
            transform: translateY(-2px);
        }
        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            border-radius: 0.5rem;
            transition: transform 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="login-container">
        <div class="container mx-auto px-4">
            <!-- Statistics Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-gavel stats-icon text-blue-600"></i>
                    <h3 class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['active_cases']); ?>+</h3>
                    <p class="text-blue-500 font-medium">Active Cases</p>
                </div>
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-users stats-icon text-green-600"></i>
                    <h3 class="text-2xl font-bold text-green-600"><?php echo number_format($stats['registered_users']); ?>+</h3>
                    <p class="text-green-500 font-medium">Registered Users</p>
                </div>
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-chart-line stats-icon text-yellow-600"></i>
                    <h3 class="text-2xl font-bold text-yellow-600">50</h3>
                    <p class="text-yellow-500 font-medium">Success Rate</p>
                </div>
                <div class="stats-card p-4 text-center">
                    <i class="fas fa-headset stats-icon text-red-600"></i>
                    <h3 class="text-2xl font-bold text-red-600"><?php echo $stats['support_available']; ?></h3>
                    <p class="text-red-500 font-medium">Support Available</p>
                </div>
            </div>

            <div class="max-w-md mx-auto">
                <div class="login-card p-8">
                    <div class="text-center mb-8">
                        <img src="emblem.png" alt="Indian Emblem" class="h-16 mx-auto mb-4 rounded-full shadow-lg border-2 border-white transition-transform transform hover:scale-105">
                        <h2 class="text-3xl font-bold text-gray-800">E-Case Management System</h2>
                        <p class="text-gray-600 mt-2">Government of India</p>
                    </div>
                    
                    <?php if(!empty($login_err)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?php echo $login_err; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="username" id="username" 
                                class="w-full input-field px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 <?php echo (!empty($username_err)) ? 'border-red-500' : ''; ?>" 
                                placeholder="Username" 
                                value="<?php echo $username; ?>">
                            <span class="text-red-500 text-sm"><?php echo $username_err; ?></span>
                        </div>
                        
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" id="password" 
                                class="w-full input-field px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600 <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>" 
                                placeholder="Password">
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                            <span class="text-red-500 text-sm"><?php echo $password_err; ?></span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="remember" class="ml-2 block text-gray-700">Remember me</label>
                            </div>
                            <a href="forgot_password.php" class="text-blue-600 hover:text-blue-800 text-sm">Forgot Password?</a>
                        </div>
                        
                        <button type="submit" class="w-full login-btn text-white font-bold py-3 px-4 rounded-lg">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </button>
                        
                        <p class="text-center text-gray-600 text-sm">
                            Don't have an account? 
                            <a href="register.php" class="text-blue-600 hover:text-blue-800 font-medium">Register here</a>
                        </p>
                    </form>
                    
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-center text-gray-700 font-medium mb-4">Access Different Portals</h3>
                        <div class="grid grid-cols-3 gap-4">
                            <a href="client_login.php" class="portal-btn bg-blue-100 text-blue-900 rounded-lg p-3 text-center hover:bg-blue-200">
                                <i class="fas fa-user-tie mb-2"></i>
                                <span class="block text-sm font-medium">Client Portal</span>
                            </a>
                            <a href="judge_login.php" class="portal-btn bg-yellow-100 text-yellow-900 rounded-lg p-3 text-center hover:bg-yellow-200">
                                <i class="fas fa-gavel mb-2"></i>
                                <span class="block text-sm font-medium">Judge Portal</span>
                            </a>
                            <a href="admin_login.php" class="portal-btn bg-red-100 text-red-900 rounded-lg p-3 text-center hover:bg-red-200">
                                <i class="fas fa-shield-alt mb-2"></i>
                                <span class="block text-sm font-medium">Admin Portal</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form validation
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const username = document.querySelector('#username').value;
            const password = document.querySelector('#password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
            }
        });
    </script>
</body>
</html>
