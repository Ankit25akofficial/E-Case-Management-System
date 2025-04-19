<?php
// Check if we're already in the client portal
if (!defined('CLIENT_HEADER_INCLUDED')) {
    include '../includes/header.php';
}

// Get client's cases for dropdown
$client_cases = get_client_cases($_SESSION['user_id']);

// Debug: Check if we have cases
if (empty($client_cases)) {
    // Add some sample cases for testing
    $client_cases = array(
        array(
            'id' => 1,
            'case_number' => 'CASE-2023-001',
            'title' => 'Sample Case 1'
        ),
        array(
            'id' => 2,
            'case_number' => 'CASE-2023-002',
            'title' => 'Sample Case 2'
        ),
        array(
            'id' => 3,
            'case_number' => 'CASE-2023-003',
            'title' => 'Sample Case 3'
        )
    );
}

// Initialize variables
$case_id = $document_type = $document_title = $description = "";
$case_id_err = $document_type_err = $document_title_err = $file_err = "";
$upload_success = $upload_error = "";

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate case ID
    if(empty(trim($_POST["case_id"]))) {
        $case_id_err = "Please select a case.";
    } else {
        $case_id = sanitize_input($_POST["case_id"]);
    }
    
    // Validate document type
    if(empty(trim($_POST["document_type"]))) {
        $document_type_err = "Please select document type.";
    } else {
        $document_type = sanitize_input($_POST["document_type"]);
    }
    
    // Validate document title
    if(empty(trim($_POST["document_title"]))) {
        $document_title_err = "Please enter document title.";
    } else {
        $document_title = sanitize_input($_POST["document_title"]);
    }
    
    // Validate file upload
    if(empty($_FILES["document_file"]["name"])) {
        $file_err = "Please select a file to upload.";
    } else {
        // Check file size (max 10MB)
        if($_FILES["document_file"]["size"] > 10000000) {
            $file_err = "File is too large. Maximum size is 10MB.";
        }
        
        // Check file type
        $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
        $file_ext = strtolower(pathinfo($_FILES["document_file"]["name"], PATHINFO_EXTENSION));
        
        if(!in_array($file_ext, $allowed_types)) {
            $file_err = "Only PDF, DOC, DOCX, JPG, JPEG, and PNG files are allowed.";
        }
    }
    
    // Description is optional
    $description = sanitize_input($_POST["description"]);
    
    // If no errors, proceed with upload
    if(empty($case_id_err) && empty($document_type_err) && empty($document_title_err) && empty($file_err)) {
        
        // Create upload directory if it doesn't exist
        $upload_dir = "../../uploads/documents/";
        if(!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $new_filename = uniqid() . '_' . $_SESSION['user_id'] . '_' . $case_id . '.' . $file_ext;
        $target_file = $upload_dir . $new_filename;
        
        // Upload file
        if(move_uploaded_file($_FILES["document_file"]["tmp_name"], $target_file)) {
            
            // Insert document info into database
            $sql = "INSERT INTO documents (case_id, user_id, document_type, title, description, file_path, upload_date) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            if($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "iissss", $case_id, $_SESSION['user_id'], $document_type, $document_title, $description, $new_filename);
                
                if(mysqli_stmt_execute($stmt)) {
                    $document_id = mysqli_insert_id($conn);
                    
                    // Add activity log
                    add_activity_log($_SESSION['user_id'], 'client', 'Document uploaded: ' . $document_title, $case_id);
                    
                    // Success message
                    $upload_success = "Document uploaded successfully!";
                    
                    // Reset form fields
                    $case_id = $document_type = $document_title = $description = "";
                } else {
                    $upload_error = "Something went wrong. Please try again later.";
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $upload_error = "Database error. Please try again later.";
            }
        } else {
            $upload_error = "Error uploading file. Please try again.";
        }
    }
}

// Page title
$page_title = "Upload Document";

?>

<!-- Main Content -->
<div class="flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Upload Document</h1>
            <p class="text-gray-600">Upload case-related documents securely</p>
        </div>
        <a href="../index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center transition duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Dashboard
        </a>
    </div>

    <!-- Upload Instructions -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <span class="font-medium">Important:</span> Supported file formats are PDF, DOC, DOCX, JPG, JPEG, and PNG. Maximum file size is 10MB.
                </p>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if(!empty($upload_success)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md upload-alert" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="font-medium"><?php echo $upload_success; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if(!empty($upload_error)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md upload-alert" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="font-medium"><?php echo $upload_error; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" id="upload-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Case Selection -->
                    <div>
                        <label for="case_id" class="block text-sm font-medium text-gray-700 mb-1">Select Case <span class="text-red-500">*</span></label>
                        <select name="case_id" id="case_id" class="w-full px-3 py-2 border <?php echo (!empty($case_id_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            <option value="">-- Select Case --</option>
                            <?php foreach($client_cases as $case): ?>
                                <option value="<?php echo $case['id']; ?>" <?php echo ($case_id == $case['id']) ? 'selected' : ''; ?>>
                                    <?php echo $case['case_number'] . ' - ' . $case['title']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="text-red-500 text-xs mt-1"><?php echo $case_id_err; ?></span>
                    </div>

                    <!-- Document Type -->
                    <div>
                        <label for="document_type" class="block text-sm font-medium text-gray-700 mb-1">Document Type <span class="text-red-500">*</span></label>
                        <select name="document_type" id="document_type" class="w-full px-3 py-2 border <?php echo (!empty($document_type_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            <option value="">-- Select Document Type --</option>
                            <option value="petition" <?php echo ($document_type == 'petition') ? 'selected' : ''; ?>>Petition</option>
                            <option value="evidence" <?php echo ($document_type == 'evidence') ? 'selected' : ''; ?>>Evidence</option>
                            <option value="affidavit" <?php echo ($document_type == 'affidavit') ? 'selected' : ''; ?>>Affidavit</option>
                            <option value="judgment" <?php echo ($document_type == 'judgment') ? 'selected' : ''; ?>>Judgment</option>
                            <option value="notice" <?php echo ($document_type == 'notice') ? 'selected' : ''; ?>>Notice</option>
                            <option value="other" <?php echo ($document_type == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <span class="text-red-500 text-xs mt-1"><?php echo $document_type_err; ?></span>
                    </div>

                    <!-- Document Title -->
                    <div>
                        <label for="document_title" class="block text-sm font-medium text-gray-700 mb-1">Document Title <span class="text-red-500">*</span></label>
                        <input type="text" name="document_title" id="document_title" value="<?php echo $document_title; ?>" class="w-full px-3 py-2 border <?php echo (!empty($document_title_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" placeholder="Enter document title">
                        <span class="text-red-500 text-xs mt-1"><?php echo $document_title_err; ?></span>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400">(Optional)</span></label>
                        <input type="text" name="description" id="description" value="<?php echo $description; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" placeholder="Brief description of the document">
                    </div>
                </div>

                <!-- File Upload -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document File <span class="text-red-500">*</span></label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md" id="drop-area">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="document_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input id="document_file" name="document_file" type="file" class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, JPEG, PNG up to 10MB</p>
                            <div id="file-preview" class="hidden mt-3 text-sm text-gray-500">
                                <p class="font-medium">Selected file: <span id="file-name"></span></p>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: 0%" id="upload-progress"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="text-red-500 text-xs mt-1"><?php echo $file_err; ?></span>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md transition duration-200 flex items-center justify-center" id="upload-button">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recently Uploaded Documents -->
    <div class="mt-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Recently Uploaded Documents</h2>
        <?php
        // Get recent documents
        $sql = "SELECT d.*, c.case_number, c.title as case_title 
                FROM documents d 
                JOIN cases c ON d.case_id = c.id 
                WHERE d.user_id = ? 
                ORDER BY d.upload_date DESC LIMIT 5";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            
            if(mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if(mysqli_num_rows($result) > 0) {
                    echo '<div class="overflow-x-auto bg-white rounded-lg shadow">';
                    echo '<table class="min-w-full divide-y divide-gray-200">';
                    echo '<thead class="bg-gray-50">';
                    echo '<tr>';
                    echo '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Title</th>';
                    echo '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Case</th>';
                    echo '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload Date</th>';
                    echo '<th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody class="bg-white divide-y divide-gray-200">';
                    
                    while($row = mysqli_fetch_assoc($result)) {
                        echo '<tr class="hover:bg-gray-50 transition">';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">' . $row['title'] . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . $row['case_number'] . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . format_date($row['upload_date'], 'd M Y, h:i A') . '</td>';
                        echo '<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">';
                        echo '<a href="../../uploads/documents/' . $row['file_path'] . '" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3">View</a>';
                        echo '<a href="download.php?id=' . $row['id'] . '" class="text-green-600 hover:text-green-900">Download</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<div class="bg-white p-6 rounded-lg shadow text-center">';
                    echo '<p class="text-gray-500">No documents uploaded yet.</p>';
                    echo '</div>';
                }
            } else {
                echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">';
                echo '<p>Error retrieving documents. Please try again later.</p>';
                echo '</div>';
            }
            
            mysqli_stmt_close($stmt);
        }
        ?>
    </div>
</div>

<!-- JavaScript for File Upload -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('document_file');
        const filePreview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const uploadProgress = document.getElementById('upload-progress');
        const uploadButton = document.getElementById('upload-button');
        const dropArea = document.getElementById('drop-area');
        const uploadForm = document.getElementById('upload-form');
        const alertElements = document.querySelectorAll('.upload-alert');
        
        // Hide alerts after 5 seconds
        if (alertElements.length > 0) {
            setTimeout(() => {
                alertElements.forEach(alert => {
                    alert.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                });
            }, 5000);
        }
        
        // File input change event
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                showFilePreview(this.files[0]);
            }
        });
        
        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        dropArea.addEventListener('drop', handleDrop, false);
        
        // Form submit event for progress simulation
        uploadForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('document_file');
            if (fileInput.files && fileInput.files[0]) {
                simulateUploadProgress();
            }
        });
        
        // Helper functions
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight() {
            dropArea.classList.add('border-blue-500', 'bg-blue-50');
        }
        
        function unhighlight() {
            dropArea.classList.remove('border-blue-500', 'bg-blue-50');
        }
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files && files[0]) {
                fileInput.files = files;
                showFilePreview(files[0]);
            }
        }
        
        function showFilePreview(file) {
            fileName.textContent = file.name;
            filePreview.classList.remove('hidden');
            
            // Animate the preview appearance
            filePreview.style.opacity = 0;
            setTimeout(() => {
                filePreview.style.opacity = 1;
                filePreview.style.transition = 'opacity 0.5s ease';
            }, 10);
        }
        
        function simulateUploadProgress() {
            let progress = 0;
            uploadButton.disabled = true;
            uploadButton.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Uploading...';
            
            const interval = setInterval(() => {
                progress += 5;
                uploadProgress.style.width = progress + '%';
                
                if (progress >= 100) {
                    clearInterval(interval);
                }
            }, 100);
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
