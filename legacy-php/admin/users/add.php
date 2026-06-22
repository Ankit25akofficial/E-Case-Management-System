<?php
session_start();
include '../../includes/config.php';
include '../../includes/functions.php';

// Check if user is logged in and is admin
if(!is_logged_in() || !is_user_type('admin')) {
    redirect('../../login.php');
}

// Define variables and initialize with empty values
$username = $password = $confirm_password = $email = $full_name = $user_type = "";
$username_err = $password_err = $confirm_password_err = $email_err = $full_name_err = $user_type_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate username
    if(empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        // Check if email is valid
        if(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        } else {
            // Check if email already exists
            $sql = "SELECT id FROM users WHERE email = ?";
            
            if($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                $param_email = trim($_POST["email"]);
                
                if(mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);
                    
                    if(mysqli_stmt_num_rows($stmt) == 1) {
                        $email_err = "This email is already registered.";
                    } else {
                        $email = trim($_POST["email"]);
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
    
    // Validate full name
    if(empty(trim($_POST["full_name"]))) {
        $full_name_err = "Please enter full name.";     
    } else {
        $full_name = trim($_POST["full_name"]);
    }
    
    // Validate user type
    if(empty(trim($_POST["user_type"]))) {
        $user_type_err = "Please select user type.";     
    } else {
        $user_type = trim($_POST["user_type"]);
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($email_err) && empty($full_name_err) && empty($user_type_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password, email, user_type, full_name) VALUES (?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssss", $param_username, $param_password, $param_email, $param_user_type, $param_full_name);
            
            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_email = $email;
            $param_user_type = $user_type;
            $param_full_name = $full_name;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                // Redirect to users page
                redirect("index.php?success=1");
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

// Page title
$page_title = "Add New User";
include '../includes/header.php';
?>

<!-- Main Content -->
<div class="flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Add New User</h1>
            <p class="text-gray-600">Create a new user account in the system</p>
        </div>
        <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Users
        </a>
    </div>

    <!-- Add User Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Full Name -->
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="full_name" id="full_name" class="w-full border <?php echo (!empty($full_name_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo $full_name; ?>">
                    <span class="text-red-500 text-xs"><?php echo $full_name_err; ?></span>
                </div>
                
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" id="username" class="w-full border <?php echo (!empty($username_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo $username; ?>">
                    <span class="text-red-500 text-xs"><?php echo $username_err; ?></span>
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" class="w-full border <?php echo (!empty($email_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo $email; ?>">
                    <span class="text-red-500 text-xs"><?php echo $email_err; ?></span>
                </div>
                
                <!-- User Type -->
                <div>
                    <label for="user_type" class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                    <select name="user_type" id="user_type" class="w-full border <?php echo (!empty($user_type_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Select User Type</option>
                        <option value="admin" <?php echo ($user_type == "admin") ? "selected" : ""; ?>>Administrator</option>
                        <option value="judge" <?php echo ($user_type == "judge") ? "selected" : ""; ?>>Judge</option>
                        <option value="client" <?php echo ($user_type == "client") ? "selected" : ""; ?>>Client</option>
                    </select>
                    <span class="text-red-500 text-xs"><?php echo $user_type_err; ?></span>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" class="w-full border <?php echo (!empty($password_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <span class="text-red-500 text-xs"><?php echo $password_err; ?></span>
                </div>
                
                <!-- Confirm Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="w-full border <?php echo (!empty($confirm_password_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <span class="text-red-500 text-xs"><?php echo $confirm_password_err; ?></span>
                </div>
            </div>
            
            <!-- Additional Fields for Different User Types -->
            <div id="client-fields" class="hidden border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Client Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input type="text" name="address" id="address" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>
            </div>
            
            <div id="judge-fields" class="hidden border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Judge Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="court" class="block text-sm font-medium text-gray-700 mb-1">Court</label>
                        <input type="text" name="court" id="court" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <div>
                        <label for="specialization" class="block text-sm font-medium text-gray-700 mb-1">Specialization</label>
                        <input type="text" name="specialization" id="specialization" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4">
                <button type="reset" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Reset
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for Form Handling -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userTypeSelect = document.getElementById('user_type');
        const clientFields = document.getElementById('client-fields');
        const judgeFields = document.getElementById('judge-fields');
        
        // Show/hide additional fields based on user type
        userTypeSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            
            // Hide all additional fields first
            clientFields.classList.add('hidden');
            judgeFields.classList.add('hidden');
            
            // Show relevant fields based on selection
            if (selectedValue === 'client') {
                clientFields.classList.remove('hidden');
            } else if (selectedValue === 'judge') {
                judgeFields.classList.remove('hidden');
            }
        });
        
        // Trigger change event if a value is already selected (e.g., on form validation error)
        if (userTypeSelect.value) {
            userTypeSelect.dispatchEvent(new Event('change'));
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
