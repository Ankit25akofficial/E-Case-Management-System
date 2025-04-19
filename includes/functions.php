<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Function to sanitize input data
 * @param string $data
 * @return string
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if($conn) {
        $data = mysqli_real_escape_string($conn, $data);
    }
    return $data;
}

/**
 * Function to check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Function to check user type
 * @param string $type
 * @return bool
 */
function is_user_type($type) {
    return is_logged_in() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === $type;
}

/**
 * Function to redirect user
 * @param string $location
 * @return void
 */
function redirect($location) {
    header("Location: $location");
    exit;
}

/**
 * Function to generate a unique case number
 * @return string
 */
function generate_case_number() {
    $year = date('Y');
    $random = mt_rand(1000, 9999);
    return "ECMS-$year-$random";
}

/**
 * Function to get user details by ID
 * @param int $user_id
 * @return array|bool
 */
function get_user_by_id($user_id) {
    global $conn;
    $sql = "SELECT id, username, email, user_type, full_name, created_at FROM users WHERE id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1) {
                return mysqli_fetch_assoc($result);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Function to get all cases for a client
 * @param int $client_id
 * @return array
 */
function get_client_cases($client_id) {
    global $conn;
    $cases = array();
    
    // Check if cached result exists
    $cache_key = "client_cases_" . $client_id;
    $cached_result = get_cache($cache_key);
    
    if ($cached_result !== false) {
        return $cached_result;
    }
    
    $sql = "SELECT c.*, u.full_name as judge_name 
            FROM cases c 
            LEFT JOIN users u ON c.judge_id = u.id 
            WHERE c.client_id = ? 
            ORDER BY c.filing_date DESC";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $client_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            while($row = mysqli_fetch_assoc($result)) {
                $cases[] = $row;
            }
            
            // Cache the result
            set_cache($cache_key, $cases, 3600); // Cache for 1 hour
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $cases;
}

/**
 * Function to get all cases for a judge
 * @param int $judge_id
 * @return array
 */
function get_judge_cases($judge_id) {
    global $conn;
    $cases = array();
    
    // Check if cached result exists
    $cache_key = "judge_cases_" . $judge_id;
    $cached_result = get_cache($cache_key);
    
    if ($cached_result !== false) {
        return $cached_result;
    }
    
    $sql = "SELECT c.*, u.full_name as client_name 
            FROM cases c 
            JOIN users u ON c.client_id = u.id 
            WHERE c.judge_id = ? 
            ORDER BY c.filing_date DESC";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $judge_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            while($row = mysqli_fetch_assoc($result)) {
                $cases[] = $row;
            }
            
            // Cache the result
            set_cache($cache_key, $cases, 3600); // Cache for 1 hour
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $cases;
}

/**
 * Function to get all cases (for admin)
 * @return array
 */
function get_all_cases() {
    global $conn;
    $cases = array();
    
    // Check if cached result exists
    $cache_key = "all_cases";
    $cached_result = get_cache($cache_key);
    
    if ($cached_result !== false) {
        return $cached_result;
    }
    
    $sql = "SELECT c.*, 
            client.full_name as client_name, 
            judge.full_name as judge_name 
            FROM cases c 
            JOIN users client ON c.client_id = client.id 
            LEFT JOIN users judge ON c.judge_id = judge.id 
            ORDER BY c.filing_date DESC";
    
    if($result = mysqli_query($conn, $sql)) {
        while($row = mysqli_fetch_assoc($result)) {
            $cases[] = $row;
        }
        
        // Cache the result
        set_cache($cache_key, $cases, 1800); // Cache for 30 minutes
    }
    
    return $cases;
}

/**
 * Function to get case details by ID
 * @param int $case_id
 * @return array|bool
 */
function get_case_by_id($case_id) {
    global $conn;
    
    $sql = "SELECT c.*, 
            client.full_name as client_name, 
            client.email as client_email,
            judge.full_name as judge_name,
            judge.email as judge_email
            FROM cases c 
            JOIN users client ON c.client_id = client.id 
            LEFT JOIN users judge ON c.judge_id = judge.id 
            WHERE c.id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $case_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1) {
                return mysqli_fetch_assoc($result);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Function to get hearings for a case
 * @param int $case_id
 * @return array
 */
function get_case_hearings($case_id) {
    global $conn;
    $hearings = array();
    
    $sql = "SELECT * FROM hearings WHERE case_id = ? ORDER BY hearing_date ASC";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $case_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            while($row = mysqli_fetch_assoc($result)) {
                $hearings[] = $row;
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $hearings;
}

/**
 * Function to get documents for a case
 * @param int $case_id
 * @return array
 */
function get_case_documents($case_id) {
    global $conn;
    $documents = array();
    
    $sql = "SELECT d.*, u.full_name as uploaded_by 
            FROM documents d 
            JOIN users u ON d.user_id = u.id 
            WHERE d.case_id = ? 
            ORDER BY d.upload_date DESC";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $case_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            while($row = mysqli_fetch_assoc($result)) {
                $documents[] = $row;
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $documents;
}

/**
 * Function to get all judges
 * @return array
 */
function get_all_judges() {
    global $conn;
    $judges = array();
    
    $sql = "SELECT id, username, email, full_name FROM users WHERE user_type = 'judge'";
    
    if($result = mysqli_query($conn, $sql)) {
        while($row = mysqli_fetch_assoc($result)) {
            $judges[] = $row;
        }
    }
    
    return $judges;
}

/**
 * Function to get all clients
 * @return array
 */
function get_all_clients() {
    global $conn;
    $clients = array();
    
    $sql = "SELECT id, username, email, full_name FROM users WHERE user_type = 'client'";
    
    if($result = mysqli_query($conn, $sql)) {
        while($row = mysqli_fetch_assoc($result)) {
            $clients[] = $row;
        }
    }
    
    return $clients;
}

/**
 * Function to get system statistics
 * @return array
 */
function get_system_stats() {
    global $conn;
    $stats = array();
    
    // Total cases
    $sql = "SELECT COUNT(*) as total FROM cases";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $stats['total_cases'] = $row['total'];
    
    // Pending cases
    $sql = "SELECT COUNT(*) as pending FROM cases WHERE status = 'pending'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $stats['pending_cases'] = $row['pending'];
    
    // In progress cases
    $sql = "SELECT COUNT(*) as in_progress FROM cases WHERE status = 'in_progress'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $stats['in_progress_cases'] = $row['in_progress'];
    
    // Closed cases
    $sql = "SELECT COUNT(*) as closed FROM cases WHERE status = 'closed'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $stats['closed_cases'] = $row['closed'];
    
    // Total users
    $sql = "SELECT COUNT(*) as total FROM users";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $stats['total_users'] = $row['total'];
    
    // Total clients
    $sql = "SELECT COUNT(*) as total FROM users WHERE user_type = 'client'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $stats['total_clients'] = $row['total'];
    
    // Total judges
    $sql = "SELECT COUNT(*) as total FROM users WHERE user_type = 'judge'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $stats['total_judges'] = $row['total'];
    
    // Upcoming hearings
    $sql = "SELECT COUNT(*) as total FROM hearings WHERE hearing_date > NOW() AND status = 'scheduled'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $stats['upcoming_hearings'] = $row['total'];
    
    return $stats;
}

/**
 * Function to format date
 * @param string $date
 * @param string $format
 * @return string
 */
function format_date($date, $format = 'd M Y, h:i A') {
    return date($format, strtotime($date));
}

/**
 * Function to upload document
 * @param array $file
 * @param int $case_id
 * @param int $user_id
 * @param string $title
 * @return bool|string
 */
function upload_document($file, $case_id, $user_id, $title) {
    // Check if file was uploaded without errors
    if(isset($file) && $file['error'] == 0) {
        $allowed = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
        $filename = $file['name'];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Check if file extension is allowed
        if(in_array(strtolower($file_ext), $allowed)) {
            // Create upload directory if it doesn't exist
            $upload_dir = '../uploads/cases/' . $case_id . '/';
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = 'doc_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            // Move uploaded file
            if(move_uploaded_file($file['tmp_name'], $upload_path)) {
                global $conn;
                
                $sql = "INSERT INTO documents (case_id, user_id, title, file_path, file_type) VALUES (?, ?, ?, ?, ?)";
                
                if($stmt = mysqli_prepare($conn, $sql)) {
                    $file_path = 'uploads/cases/' . $case_id . '/' . $new_filename;
                    
                    mysqli_stmt_bind_param($stmt, "iisss", $case_id, $user_id, $title, $file_path, $file_ext);
                    
                    if(mysqli_stmt_execute($stmt)) {
                        return true;
                    } else {
                        return "Error: " . mysqli_stmt_error($stmt);
                    }
                    
                    mysqli_stmt_close($stmt);
                } else {
                    return "Error: " . mysqli_error($conn);
                }
            } else {
                return "Error uploading file.";
            }
        } else {
            return "Error: Invalid file format. Allowed formats: " . implode(', ', $allowed);
        }
    } else {
        return "Error: " . $file['error'];
    }
}

/**
 * Function to check if user has access to a case
 * @param int $case_id
 * @param int $user_id
 * @param string $user_type
 * @return bool
 */
function user_has_case_access($case_id, $user_id, $user_type) {
    global $conn;
    
    if($user_type === 'admin') {
        return true; // Admin has access to all cases
    }
    
    $sql = "SELECT * FROM cases WHERE id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $case_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1) {
                $case = mysqli_fetch_assoc($result);
                
                if($user_type === 'client' && $case['client_id'] == $user_id) {
                    return true; // Client has access to their own cases
                }
                
                if($user_type === 'judge' && $case['judge_id'] == $user_id) {
                    return true; // Judge has access to cases assigned to them
                }
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Function to get upcoming hearings for a user
 * @param int $user_id
 * @param string $user_type
 * @return array
 */
function get_upcoming_hearings($user_id, $user_type) {
    global $conn;
    $hearings = array();
    
    if($user_type === 'admin') {
        $sql = "SELECT h.*, c.case_number, c.title as case_title, 
                client.full_name as client_name, judge.full_name as judge_name
                FROM hearings h
                JOIN cases c ON h.case_id = c.id
                JOIN users client ON c.client_id = client.id
                LEFT JOIN users judge ON c.judge_id = judge.id
                WHERE h.hearing_date > NOW() AND h.status = 'scheduled'
                ORDER BY h.hearing_date ASC";
        
        if($result = mysqli_query($conn, $sql)) {
            while($row = mysqli_fetch_assoc($result)) {
                $hearings[] = $row;
            }
        }
    } else if($user_type === 'client') {
        $sql = "SELECT h.*, c.case_number, c.title as case_title, judge.full_name as judge_name
                FROM hearings h
                JOIN cases c ON h.case_id = c.id
                LEFT JOIN users judge ON c.judge_id = judge.id
                WHERE c.client_id = ? AND h.hearing_date > NOW() AND h.status = 'scheduled'
                ORDER BY h.hearing_date ASC";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                while($row = mysqli_fetch_assoc($result)) {
                    $hearings[] = $row;
                }
            }
            
            mysqli_stmt_close($stmt);
        }
    } else if($user_type === 'judge') {
        $sql = "SELECT h.*, c.case_number, c.title as case_title, client.full_name as client_name
                FROM hearings h
                JOIN cases c ON h.case_id = c.id
                JOIN users client ON c.client_id = client.id
                WHERE c.judge_id = ? AND h.hearing_date > NOW() AND h.status = 'scheduled'
                ORDER BY h.hearing_date ASC";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                while($row = mysqli_fetch_assoc($result)) {
                    $hearings[] = $row;
                }
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    return $hearings;
}

/**
 * Function to get recent activities for a user
 * @param int $user_id
 * @param string $user_type
 * @param int $limit
 * @return array
 */
function get_recent_activities($user_id, $user_type, $limit = 10) {
    global $conn;
    $activities = array();
    
    if($user_type === 'admin') {
        // For admin, show all recent case updates and document uploads
        $sql = "SELECT 'case_update' as type, c.id, c.case_number, c.title, c.status, c.filing_date as date,
                client.full_name as client_name, judge.full_name as judge_name
                FROM cases c
                JOIN users client ON c.client_id = client.id
                LEFT JOIN users judge ON c.judge_id = judge.id
                UNION ALL
                SELECT 'document_upload' as type, c.id, c.case_number, d.title, d.file_type, d.upload_date as date,
                u.full_name as client_name, '' as judge_name
                FROM documents d
                JOIN cases c ON d.case_id = c.id
                JOIN users u ON d.user_id = u.id
                UNION ALL
                SELECT 'hearing_scheduled' as type, c.id, c.case_number, c.title, h.status, h.created_at as date,
                client.full_name as client_name, judge.full_name as judge_name
                FROM hearings h
                JOIN cases c ON h.case_id = c.id
                JOIN users client ON c.client_id = client.id
                LEFT JOIN users judge ON c.judge_id = judge.id
                ORDER BY date DESC
                LIMIT ?";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $limit);
            
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                while($row = mysqli_fetch_assoc($result)) {
                    $activities[] = $row;
                }
            }
            
            mysqli_stmt_close($stmt);
        }
    } else if($user_type === 'client') {
        // For client, show their case updates, document uploads, and hearing schedules
        $sql = "SELECT 'case_update' as type, c.id, c.case_number, c.title, c.status, c.filing_date as date,
                '' as client_name, judge.full_name as judge_name
                FROM cases c
                LEFT JOIN users judge ON c.judge_id = judge.id
                WHERE c.client_id = ?
                UNION ALL
                SELECT 'document_upload' as type, c.id, c.case_number, d.title, d.file_type, d.upload_date as date,
                u.full_name as client_name, '' as judge_name
                FROM documents d
                JOIN cases c ON d.case_id = c.id
                JOIN users u ON d.user_id = u.id
                WHERE c.client_id = ?
                UNION ALL
                SELECT 'hearing_scheduled' as type, c.id, c.case_number, c.title, h.status, h.created_at as date,
                '' as client_name, judge.full_name as judge_name
                FROM hearings h
                JOIN cases c ON h.case_id = c.id
                LEFT JOIN users judge ON c.judge_id = judge.id
                WHERE c.client_id = ?
                ORDER BY date DESC
                LIMIT ?";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiii", $user_id, $user_id, $user_id, $limit);
            
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                while($row = mysqli_fetch_assoc($result)) {
                    $activities[] = $row;
                }
            }
            
            mysqli_stmt_close($stmt);
        }
    } else if($user_type === 'judge') {
        // For judge, show their assigned case updates, document uploads, and hearing schedules
        $sql = "SELECT 'case_update' as type, c.id, c.case_number, c.title, c.status, c.filing_date as date,
                client.full_name as client_name, '' as judge_name
                FROM cases c
                JOIN users client ON c.client_id = client.id
                WHERE c.judge_id = ?
                UNION ALL
                SELECT 'document_upload' as type, c.id, c.case_number, d.title, d.file_type, d.upload_date as date,
                u.full_name as client_name, '' as judge_name
                FROM documents d
                JOIN cases c ON d.case_id = c.id
                JOIN users u ON d.user_id = u.id
                WHERE c.judge_id = ?
                UNION ALL
                SELECT 'hearing_scheduled' as type, c.id, c.case_number, c.title, h.status, h.created_at as date,
                client.full_name as client_name, '' as judge_name
                FROM hearings h
                JOIN cases c ON h.case_id = c.id
                JOIN users client ON c.client_id = client.id
                WHERE c.judge_id = ?
                ORDER BY date DESC
                LIMIT ?";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiii", $user_id, $user_id, $user_id, $limit);
            
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                while($row = mysqli_fetch_assoc($result)) {
                    $activities[] = $row;
                }
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    return $activities;
}

/**
 * Function to get cache
 * @param string $key
 * @return mixed
 */
function get_cache($key) {
    global $conn;
    
    $sql = "SELECT value FROM cache WHERE `key` = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $key);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                return unserialize($row['value']);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Function to set cache
 * @param string $key
 * @param mixed $value
 * @param int $ttl
 * @return bool
 */
function set_cache($key, $value, $ttl) {
    global $conn;
    
    $sql = "INSERT INTO cache (`key`, value, ttl) VALUES (?, ?, ?)";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        $value = serialize($value);
        $ttl = time() + $ttl;
        
        mysqli_stmt_bind_param($stmt, "ssi", $key, $value, $ttl);
        
        if(mysqli_stmt_execute($stmt)) {
            return true;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Function to get case timeline data
 * @param int $case_id
 * @return array
 */
function get_case_timeline($case_id) {
    global $conn;
    $timeline = array();
    
    // Get case details
    $case = get_case_by_id($case_id);
    if($case) {
        $timeline[] = array(
            'date' => $case['filing_date'],
            'type' => 'case_filed',
            'title' => 'Case Filed',
            'description' => 'Case was filed with number ' . $case['case_number']
        );
    }
    
    // Get hearings
    $hearings = get_case_hearings($case_id);
    foreach($hearings as $hearing) {
        $timeline[] = array(
            'date' => $hearing['hearing_date'],
            'type' => 'hearing',
            'title' => 'Hearing Scheduled',
            'description' => 'Hearing scheduled with ' . $hearing['judge_name'] . ' at ' . $hearing['location']
        );
    }
    
    // Get documents
    $documents = get_case_documents($case_id);
    foreach($documents as $doc) {
        $timeline[] = array(
            'date' => $doc['upload_date'],
            'type' => 'document',
            'title' => 'Document Uploaded',
            'description' => $doc['title'] . ' was uploaded'
        );
    }
    
    // Sort timeline by date
    usort($timeline, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    
    return $timeline;
}

/**
 * Function to search documents
 * @param int $user_id
 * @param string $user_type
 * @param string $search_term
 * @return array
 */
function search_documents($user_id, $user_type, $search_term) {
    global $conn;
    $documents = array();
    
    $search_term = '%' . $search_term . '%';
    
    if($user_type == 'client') {
        $sql = "SELECT d.*, c.case_number, c.title as case_title 
                FROM documents d 
                JOIN cases c ON d.case_id = c.id 
                WHERE c.client_id = ? 
                AND (d.title LIKE ? OR d.description LIKE ? OR c.case_number LIKE ? OR c.title LIKE ?)";
    } else if($user_type == 'judge') {
        $sql = "SELECT d.*, c.case_number, c.title as case_title 
                FROM documents d 
                JOIN cases c ON d.case_id = c.id 
                WHERE c.judge_id = ? 
                AND (d.title LIKE ? OR d.description LIKE ? OR c.case_number LIKE ? OR c.title LIKE ?)";
    } else {
        $sql = "SELECT d.*, c.case_number, c.title as case_title 
                FROM documents d 
                JOIN cases c ON d.case_id = c.id 
                WHERE d.title LIKE ? OR d.description LIKE ? OR c.case_number LIKE ? OR c.title LIKE ?";
    }
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        if($user_type == 'admin') {
            mysqli_stmt_bind_param($stmt, "ssss", $search_term, $search_term, $search_term, $search_term);
        } else {
            mysqli_stmt_bind_param($stmt, "issss", $user_id, $search_term, $search_term, $search_term, $search_term);
        }
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            while($row = mysqli_fetch_assoc($result)) {
                $documents[] = $row;
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $documents;
}

/**
 * Function to format file size
 * @param int $bytes
 * @return string
 */
function format_file_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}

?>
