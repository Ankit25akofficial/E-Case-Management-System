<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
if(!is_logged_in() || !is_user_type('admin')) {
    redirect('../login.php');
}

// Function to generate random dates within a range
function random_date($start_date, $end_date) {
    $min = strtotime($start_date);
    $max = strtotime($end_date);
    $rand_time = mt_rand($min, $max);
    return date('Y-m-d H:i:s', $rand_time);
}

// Get user IDs
$client_id = null;
$judge_id = null;

// Get client ID
$sql = "SELECT id FROM users WHERE user_type = 'client' LIMIT 1";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $client_id = $row['id'];
} else {
    // Create a judge if none exists
    $hashed_password = password_hash('judge123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, email, user_type, full_name) 
            VALUES ('judge', '$hashed_password', 'judge@example.com', 'judge', 'Demo Judge')";
    if(mysqli_query($conn, $sql)) {
        $judge_id = mysqli_insert_id($conn);
    }
    
    // Create a client if none exists
    $hashed_password = password_hash('client123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, email, user_type, full_name) 
            VALUES ('client', '$hashed_password', 'client@example.com', 'client', 'Demo Client')";
    if(mysqli_query($conn, $sql)) {
        $client_id = mysqli_insert_id($conn);
    }
}

// Get judge ID
$sql = "SELECT id FROM users WHERE user_type = 'judge' LIMIT 1";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $judge_id = $row['id'];
}

// Sample case data
$sample_cases = [
    [
        'title' => 'Property Dispute in Mumbai Suburb',
        'description' => 'Dispute over property boundaries between neighboring plots in Andheri East.',
        'status' => 'pending',
        'filing_date' => random_date('2025-01-01', '2025-03-15')
    ],
    [
        'title' => 'Motor Vehicle Accident Compensation',
        'description' => 'Claim for compensation following a serious road accident on NH-8.',
        'status' => 'in_progress',
        'filing_date' => random_date('2025-01-01', '2025-02-15')
    ],
    [
        'title' => 'Corporate Contract Breach',
        'description' => 'Alleged breach of contract between two IT companies based in Bangalore.',
        'status' => 'in_progress',
        'filing_date' => random_date('2024-11-01', '2025-01-15')
    ],
    [
        'title' => 'Intellectual Property Rights Violation',
        'description' => 'Unauthorized use of patented technology in consumer electronics.',
        'status' => 'closed',
        'filing_date' => random_date('2024-09-01', '2024-10-15'),
        'closing_date' => random_date('2025-02-01', '2025-04-01')
    ],
    [
        'title' => 'Family Inheritance Dispute',
        'description' => 'Dispute over ancestral property distribution among siblings.',
        'status' => 'closed',
        'filing_date' => random_date('2024-08-01', '2024-09-15'),
        'closing_date' => random_date('2025-01-01', '2025-03-01')
    ],
    [
        'title' => 'Employment Termination Case',
        'description' => 'Alleged wrongful termination from a multinational company.',
        'status' => 'pending',
        'filing_date' => random_date('2025-03-01', '2025-04-10')
    ],
    [
        'title' => 'Consumer Product Liability',
        'description' => 'Damages claim for injuries caused by defective household appliance.',
        'status' => 'assigned',
        'filing_date' => random_date('2025-02-01', '2025-03-20')
    ],
    [
        'title' => 'Rental Agreement Violation',
        'description' => 'Dispute between landlord and tenant over lease terms and conditions.',
        'status' => 'in_progress',
        'filing_date' => random_date('2024-12-01', '2025-01-20')
    ]
];

// Insert sample cases
$cases_added = 0;
$hearings_added = 0;
$documents_added = 0;

foreach($sample_cases as $case) {
    $case_number = generate_case_number();
    $closing_date = isset($case['closing_date']) ? "'" . $case['closing_date'] . "'" : "NULL";
    
    $sql = "INSERT INTO cases (case_number, title, description, client_id, judge_id, status, filing_date, closing_date) 
            VALUES ('$case_number', '{$case['title']}', '{$case['description']}', $client_id, " . 
            ($case['status'] == 'pending' ? "NULL" : $judge_id) . ", '{$case['status']}', '{$case['filing_date']}', $closing_date)";
    
    if(mysqli_query($conn, $sql)) {
        $case_id = mysqli_insert_id($conn);
        $cases_added++;
        
        // Add hearings for non-pending cases
        if($case['status'] != 'pending') {
            $hearing_statuses = ['scheduled', 'completed', 'postponed'];
            $num_hearings = mt_rand(1, 3);
            
            for($i = 0; $i < $num_hearings; $i++) {
                $hearing_date = random_date($case['filing_date'], date('Y-m-d H:i:s', strtotime('+30 days')));
                $hearing_status = $hearing_statuses[array_rand($hearing_statuses)];
                $location = 'Court Room ' . mt_rand(1, 10) . ', District Court, Mumbai';
                $notes = 'Standard hearing for case proceedings and evidence presentation.';
                
                $sql = "INSERT INTO hearings (case_id, hearing_date, location, notes, status) 
                        VALUES ($case_id, '$hearing_date', '$location', '$notes', '$hearing_status')";
                
                if(mysqli_query($conn, $sql)) {
                    $hearings_added++;
                }
            }
            
            // Add documents
            $document_titles = [
                'Initial Complaint',
                'Evidence Submission',
                'Witness Statement',
                'Expert Opinion',
                'Court Order',
                'Legal Brief'
            ];
            
            $num_docs = mt_rand(1, 4);
            for($i = 0; $i < $num_docs; $i++) {
                $title = $document_titles[array_rand($document_titles)] . ' ' . ($i + 1);
                $file_path = 'uploads/cases/' . $case_id . '/document_' . mt_rand(1000, 9999) . '.pdf';
                $file_type = 'pdf';
                $upload_date = random_date($case['filing_date'], date('Y-m-d H:i:s'));
                
                $sql = "INSERT INTO documents (case_id, user_id, title, file_path, file_type, upload_date) 
                        VALUES ($case_id, $client_id, '$title', '$file_path', '$file_type', '$upload_date')";
                
                if(mysqli_query($conn, $sql)) {
                    $documents_added++;
                }
            }
        }
    }
}

// Clear cache to ensure fresh data is loaded
$sql = "TRUNCATE TABLE cache";
mysqli_query($conn, $sql);

// Output results
$message = "Sample data generation complete:<br>";
$message .= "- $cases_added cases added<br>";
$message .= "- $hearings_added hearings scheduled<br>";
$message .= "- $documents_added documents uploaded<br>";
$message .= "<br><a href='index.php' class='bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700'>Return to Dashboard</a>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Data Generation - E-Case Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-md p-8 max-w-md w-full">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Sample Data Generation</h1>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $message; ?>
        </div>
    </div>
</body>
</html>
