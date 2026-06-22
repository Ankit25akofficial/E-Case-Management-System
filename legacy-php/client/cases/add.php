<?php
session_start();
include '../../includes/config.php';
include '../../includes/functions.php';

// Check if user is logged in and is client
if(!is_logged_in() || !is_user_type('client')) {
    redirect('../../login.php');
}

// Define variables and initialize with empty values
$title = $description = "";
$title_err = $description_err = "";
$success_msg = $error_msg = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate title
    if(empty(trim($_POST["title"]))) {
        $title_err = "Please enter case title.";
    } else {
        $title = sanitize_input($_POST["title"]);
    }
    
    // Validate description
    if(empty(trim($_POST["description"]))) {
        $description_err = "Please enter case description.";
    } else {
        $description = sanitize_input($_POST["description"]);
    }
    
    // Check input errors before inserting in database
    if(empty($title_err) && empty($description_err)) {
        
        // Generate a unique case number
        $case_number = generate_case_number();
        
        // Prepare an insert statement
        $sql = "INSERT INTO cases (case_number, title, description, client_id, status) VALUES (?, ?, ?, ?, 'pending')";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssi", $case_number, $title, $description, $_SESSION['user_id']);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                $case_id = mysqli_insert_id($conn);
                
                // Handle file upload if present
                if(isset($_FILES['documents']) && $_FILES['documents']['error'][0] != 4) { // 4 means no file was uploaded
                    $file_count = count($_FILES['documents']['name']);
                    
                    for($i = 0; $i < $file_count; $i++) {
                        $file = array(
                            'name' => $_FILES['documents']['name'][$i],
                            'type' => $_FILES['documents']['type'][$i],
                            'tmp_name' => $_FILES['documents']['tmp_name'][$i],
                            'error' => $_FILES['documents']['error'][$i],
                            'size' => $_FILES['documents']['size'][$i]
                        );
                        
                        if($file['error'] == 0) {
                            $document_title = "Initial Filing Document " . ($i + 1);
                            $upload_result = upload_document($file, $case_id, $_SESSION['user_id'], $document_title);
                            
                            if($upload_result !== true) {
                                $error_msg = $upload_result; // Display upload error
                            }
                        }
                    }
                }
                
                $success_msg = "Case filed successfully! Your case number is: " . $case_number;
                
                // Clear form data after successful submission
                $title = $description = "";
            } else {
                $error_msg = "Something went wrong. Please try again later.";
            }
            
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

// Page title
$page_title = "File New Case";
include '../includes/header.php';
?>

<!-- Main Content -->
<div class="flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">File New Case</h1>
            <p class="text-gray-600">Submit a new case to the judicial system</p>
        </div>
        <a href="../index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to My Cases
        </a>
    </div>

    <?php if(!empty($success_msg)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($error_msg)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <!-- Case Filing Instructions -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Instructions:</strong> Please fill out the form below with accurate details about your case. You can attach relevant documents to support your case filing. Once submitted, your case will be reviewed by the appropriate authorities.
                </p>
            </div>
        </div>
    </div>

    <!-- Case Filing Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="space-y-6">
            <!-- Case Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Case Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" class="w-full border <?php echo (!empty($title_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" value="<?php echo $title; ?>" placeholder="Enter a descriptive title for your case">
                <span class="text-red-500 text-xs"><?php echo $title_err; ?></span>
            </div>
            
            <!-- Case Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Case Description <span class="text-red-500">*</span></label>
                <textarea name="description" id="description" rows="6" class="w-full border <?php echo (!empty($description_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="Provide a detailed description of your case including relevant facts, dates, and circumstances"><?php echo $description; ?></textarea>
                <span class="text-red-500 text-xs"><?php echo $description_err; ?></span>
            </div>
            
            <!-- Case Category -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Case Category</label>
                <select name="category" id="category" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <option value="">Select Category</option>
                    <option value="civil">Civil Case</option>
                    <option value="criminal">Criminal Case</option>
                    <option value="family">Family Law Case</option>
                    <option value="property">Property Dispute</option>
                    <option value="commercial">Commercial/Business Case</option>
                    <option value="consumer">Consumer Complaint</option>
                    <option value="labor">Labor/Employment Case</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <!-- Document Upload -->
            <div>
                <label for="documents" class="block text-sm font-medium text-gray-700 mb-1">Upload Supporting Documents</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="documents" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload files</span>
                                <input id="documents" name="documents[]" type="file" class="sr-only" multiple>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">
                            PDF, DOC, DOCX, JPG, PNG up to 10MB each
                        </p>
                    </div>
                </div>
                <div id="file-list" class="mt-2"></div>
            </div>
            
            <!-- Priority and Additional Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Case Priority</label>
                    <select name="priority" id="priority" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                <div>
                    <label for="preferred_court" class="block text-sm font-medium text-gray-700 mb-1">Preferred Court (if any)</label>
                    <input type="text" name="preferred_court" id="preferred_court" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="Enter preferred court if applicable">
                </div>
            </div>
            
            <!-- Terms and Conditions -->
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="terms" name="terms" type="checkbox" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" required>
                </div>
                <div class="ml-3 text-sm">
                    <label for="terms" class="font-medium text-gray-700">I confirm that all information provided is accurate and complete</label>
                    <p class="text-gray-500">I understand that providing false information may result in legal consequences.</p>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <button type="reset" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Reset
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Submit Case
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for File Upload Preview -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('documents');
        const fileList = document.getElementById('file-list');
        
        fileInput.addEventListener('change', function() {
            fileList.innerHTML = '';
            
            if (this.files.length > 0) {
                const list = document.createElement('ul');
                list.className = 'mt-2 divide-y divide-gray-200';
                
                for (let i = 0; i < this.files.length; i++) {
                    const file = this.files[i];
                    const listItem = document.createElement('li');
                    listItem.className = 'py-2 flex items-center';
                    
                    // File icon based on type
                    let iconSvg = '';
                    if (file.type.includes('pdf')) {
                        iconSvg = '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>';
                    } else if (file.type.includes('image')) {
                        iconSvg = '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>';
                    } else if (file.type.includes('word') || file.name.endsWith('.doc') || file.name.endsWith('.docx')) {
                        iconSvg = '<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
                    } else {
                        iconSvg = '<svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>';
                    }
                    
                    // Format file size
                    let fileSize = '';
                    if (file.size < 1024) {
                        fileSize = file.size + ' bytes';
                    } else if (file.size < 1024 * 1024) {
                        fileSize = (file.size / 1024).toFixed(1) + ' KB';
                    } else {
                        fileSize = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
                    }
                    
                    listItem.innerHTML = `
                        <div class="mr-3">${iconSvg}</div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">${file.name}</p>
                            <p class="text-xs text-gray-500">${fileSize}</p>
                        </div>
                        <button type="button" class="remove-file text-red-600 hover:text-red-900" data-index="${i}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    `;
                    
                    list.appendChild(listItem);
                }
                
                fileList.appendChild(list);
                
                // Add event listeners to remove buttons
                document.querySelectorAll('.remove-file').forEach(button => {
                    button.addEventListener('click', function() {
                        // We can't directly modify the files array, so we need to create a new one
                        const dt = new DataTransfer();
                        const files = fileInput.files;
                        const index = parseInt(this.dataset.index);
                        
                        for (let i = 0; i < files.length; i++) {
                            if (i !== index) {
                                dt.items.add(files[i]);
                            }
                        }
                        
                        fileInput.files = dt.files;
                        
                        // Trigger change event to update the list
                        const event = new Event('change');
                        fileInput.dispatchEvent(event);
                    });
                });
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
