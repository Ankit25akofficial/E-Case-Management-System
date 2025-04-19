<?php
session_start();
include_once '../includes/config.php';
include_once '../includes/functions.php';

// Check if user is logged in and is client
if(!is_logged_in() || !is_user_type('client')) {
    redirect('../login.php');
}

// Fetch all documents uploaded by this client (across all their cases)
$client_id = $_SESSION['user_id'];
$documents = get_client_documents($client_id); // You should have this function in functions.php

$page_title = 'Documents';
include_once 'includes/header.php';
?>
<div class="ml-64 p-8">
    <h1 class="text-2xl font-bold mb-4">My Documents</h1>
    <?php if(empty($documents)): ?>
        <div class="bg-blue-50 p-4 rounded-md">
            <p class="text-blue-700">You have not uploaded any documents yet.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Case</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded On</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($documents as $doc): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($doc['file_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($doc['case_number']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d M Y', strtotime($doc['uploaded_at'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="text-blue-600 hover:underline">Download</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include_once 'includes/footer.php'; ?>
