<?php
// Database configuration
define('DB_SERVER', '127.0.0.1'); // Using IP instead of 'localhost'
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); // Update this with your actual MySQL password if needed
define('DB_NAME', 'ecms_db');
define('DB_PORT', 3306); // Explicitly define the port

// First connect to MySQL without specifying a database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, '', DB_PORT);

// Check connection
if($conn === false){
    die("ERROR: Could not connect to MySQL server. " . mysqli_connect_error());
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if(mysqli_query($conn, $sql)){
    // Close the connection and reconnect to the specific database
    mysqli_close($conn);
    $conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
    
    if($conn === false){
        die("ERROR: Could not connect to database. " . mysqli_connect_error());
    }
} else {
    die("ERROR: Could not create database. " . mysqli_error($conn));
}

// Create users table if not exists
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    user_type ENUM('admin', 'client', 'judge') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

mysqli_query($conn, $sql);

// Check if login_attempts column exists
$check_login_attempts = "SHOW COLUMNS FROM users LIKE 'login_attempts'";
$result = mysqli_query($conn, $check_login_attempts);

if(mysqli_num_rows($result) == 0) {
    // Add login_attempts column if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN login_attempts INT DEFAULT 0";
    mysqli_query($conn, $sql);
}

// Check if last_attempt column exists
$check_last_attempt = "SHOW COLUMNS FROM users LIKE 'last_attempt'";
$result = mysqli_query($conn, $check_last_attempt);

if(mysqli_num_rows($result) == 0) {
    // Add last_attempt column if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN last_attempt DATETIME DEFAULT NULL";
    mysqli_query($conn, $sql);
}

// Create cases table if not exists
$sql = "CREATE TABLE IF NOT EXISTS cases (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    case_number VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    client_id INT NOT NULL,
    judge_id INT,
    status ENUM('pending', 'assigned', 'in_progress', 'closed') NOT NULL DEFAULT 'pending',
    filing_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closing_date TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES users(id),
    FOREIGN KEY (judge_id) REFERENCES users(id)
)";
mysqli_query($conn, $sql);

// Create hearings table if not exists
$sql = "CREATE TABLE IF NOT EXISTS hearings (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    case_id INT NOT NULL,
    hearing_date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    notes TEXT,
    status ENUM('scheduled', 'completed', 'postponed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id)
)";
mysqli_query($conn, $sql);

// Create documents table if not exists
$sql = "CREATE TABLE IF NOT EXISTS documents (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    case_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
mysqli_query($conn, $sql);

// Create cache table if not exists
$sql = "CREATE TABLE IF NOT EXISTS cache (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `key` VARCHAR(255) NOT NULL UNIQUE,
    value LONGTEXT NOT NULL,
    ttl INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (ttl),
    INDEX (`key`)
)";
mysqli_query($conn, $sql);

// Insert default admin user if not exists
$check_admin = "SELECT * FROM users WHERE username = 'admin'";
$result = mysqli_query($conn, $check_admin);

if(mysqli_num_rows($result) == 0){
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, email, user_type, full_name) 
            VALUES ('admin', '$hashed_password', 'admin@ecms.gov.in', 'admin', 'System Administrator')";
    mysqli_query($conn, $sql);
}

// Insert default client user if not exists
$check_client = "SELECT * FROM users WHERE username = 'client'";
$result = mysqli_query($conn, $check_client);

if(mysqli_num_rows($result) == 0){
    $hashed_password = password_hash('client123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, email, user_type, full_name) 
            VALUES ('client', '$hashed_password', 'client@example.com', 'client', 'Demo Client')";
    mysqli_query($conn, $sql);
}

// Global connection variable
$GLOBALS['conn'] = $conn;
?>
