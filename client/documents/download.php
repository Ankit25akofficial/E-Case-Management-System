<?php
session_start();
include '../../includes/config.php';
include '../../includes/functions.php';

// Check if user is logged in and is client
if(!is_logged_in() || !is_user_type('client')) {
    redirect('../../login.php');
}

// Check if document ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('upload.php');
}

$document_id = sanitize_input($_GET['id']);

// Get document information
$sql = "SELECT d.*, c.client_id 
        FROM documents d 
        JOIN cases c ON d.case_id = c.id 
        WHERE d.id = ?";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $document_id);
    
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) == 1) {
            $document = mysqli_fetch_assoc($result);
            
            // Check if document belongs to the logged-in client
            if($document['client_id'] != $_SESSION['user_id']) {
                // Log unauthorized access attempt
                log_activity($_SESSION['user_id'], 'client', 'Unauthorized document download attempt: ' . $document_id);
                redirect('../index.php');
            }
            
            $file_path = "../../uploads/documents/" . $document['file_path'];
            
            // Check if file exists
            if(file_exists($file_path)) {
                // Log download activity
                log_activity($_SESSION['user_id'], 'client', 'Downloaded document: ' . $document['title']);
                
                // Set headers for download
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($document['file_path']) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_path));
                
                // Clear output buffer
                ob_clean();
                flush();
                
                // Read file and output to browser
                readfile($file_path);
                exit;
            } else {
                // File not found
                $_SESSION['error'] = "Document file not found.";
                redirect('upload.php');
            }
        } else {
            // Document not found
            $_SESSION['error'] = "Document not found.";
            redirect('upload.php');
        }
    } else {
        // Database error
        $_SESSION['error'] = "Database error. Please try again later.";
        redirect('upload.php');
    }
    
    mysqli_stmt_close($stmt);
} else {
    // Database error
    $_SESSION['error'] = "Database error. Please try again later.";
    redirect('upload.php');
}
?>
