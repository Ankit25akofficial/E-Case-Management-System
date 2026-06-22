<?php
// Mock database configuration for testing file connections
// This file allows the application to run without an actual database connection

// Define a mock database connection
$conn = true; // Simulate a successful connection

// Create mock data for testing
$GLOBALS['mock_data'] = [
    'users' => [
        [
            'id' => 1,
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'email' => 'admin@ecms.gov.in',
            'user_type' => 'admin',
            'full_name' => 'System Administrator',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'username' => 'client1',
            'password' => password_hash('client123', PASSWORD_DEFAULT),
            'email' => 'client1@example.com',
            'user_type' => 'client',
            'full_name' => 'John Doe',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 3,
            'username' => 'judge1',
            'password' => password_hash('judge123', PASSWORD_DEFAULT),
            'email' => 'judge1@ecms.gov.in',
            'user_type' => 'judge',
            'full_name' => 'Hon. Justice Smith',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ],
    'cases' => [
        [
            'id' => 1,
            'case_number' => 'ECMS-2025-001',
            'title' => 'Smith vs. Johnson',
            'description' => 'Property dispute case regarding boundary wall',
            'client_id' => 2,
            'judge_id' => 3,
            'status' => 'in_progress',
            'filing_date' => date('Y-m-d H:i:s', strtotime('-30 days')),
            'closing_date' => null
        ],
        [
            'id' => 2,
            'case_number' => 'ECMS-2025-002',
            'title' => 'State vs. Williams',
            'description' => 'Criminal case regarding theft',
            'client_id' => 2,
            'judge_id' => 3,
            'status' => 'pending',
            'filing_date' => date('Y-m-d H:i:s', strtotime('-15 days')),
            'closing_date' => null
        ]
    ],
    'hearings' => [
        [
            'id' => 1,
            'case_id' => 1,
            'hearing_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'location' => 'Court Room 3B',
            'notes' => 'Initial hearing',
            'status' => 'scheduled',
            'created_at' => date('Y-m-d H:i:s', strtotime('-20 days'))
        ]
    ],
    'documents' => [
        [
            'id' => 1,
            'case_id' => 1,
            'user_id' => 2,
            'title' => 'Property Deed',
            'file_path' => 'uploads/documents/property_deed.pdf',
            'file_type' => 'application/pdf',
            'upload_date' => date('Y-m-d H:i:s', strtotime('-25 days'))
        ],
        [
            'id' => 2,
            'case_id' => 1,
            'user_id' => 2,
            'title' => 'Evidence Photos',
            'file_path' => 'uploads/documents/evidence.jpg',
            'file_type' => 'image/jpeg',
            'upload_date' => date('Y-m-d H:i:s', strtotime('-20 days'))
        ]
    ],
    'cache' => []
];

// Mock database functions
function mock_query($query) {
    // Very simple mock query handler
    // This doesn't actually parse SQL, just returns mock data based on table name
    
    $result = new stdClass();
    $result->num_rows = 0;
    $result->mock_data = [];
    
    // Extract table name from query (very simple approach)
    if (preg_match('/FROM\s+(\w+)/i', $query, $matches)) {
        $table = $matches[1];
        if (isset($GLOBALS['mock_data'][$table])) {
            $result->num_rows = count($GLOBALS['mock_data'][$table]);
            $result->mock_data = $GLOBALS['mock_data'][$table];
        }
    } else if (preg_match('/INSERT INTO\s+(\w+)/i', $query, $matches)) {
        // For insert queries, just return true
        return true;
    } else if (preg_match('/UPDATE\s+(\w+)/i', $query, $matches)) {
        // For update queries, just return true
        return true;
    } else if (preg_match('/DELETE FROM\s+(\w+)/i', $query, $matches)) {
        // For delete queries, just return true
        return true;
    } else if (strpos($query, 'CREATE DATABASE') !== false) {
        // For create database queries, just return true
        return true;
    } else if (strpos($query, 'CREATE TABLE') !== false) {
        // For create table queries, just return true
        return true;
    }
    
    return $result;
}

// Override mysqli functions to use our mock data
function mysqli_query($conn, $query) {
    return mock_query($query);
}

function mysqli_fetch_assoc($result) {
    static $index = 0;
    
    if (!isset($result->mock_data[$index])) {
        $index = 0; // Reset for next query
        return null;
    }
    
    return $result->mock_data[$index++];
}

function mysqli_num_rows($result) {
    return $result->num_rows;
}

function mysqli_error($conn) {
    return "Mock error message";
}

function mysqli_connect_error() {
    return "Mock connection error";
}

function mysqli_real_escape_string($conn, $string) {
    return addslashes($string);
}

// Global connection variable
$GLOBALS['conn'] = $conn;
?>
