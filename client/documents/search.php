<?php
session_start();
include '../../includes/config.php';
include '../../includes/functions.php';

// Check if user is logged in and is client
if(!is_logged_in() || !is_user_type('client')) {
    redirect('../../login.php');
}

// Get search results if search term is provided
$search_results = array();
$search_term = '';
if(isset($_GET['q']) && !empty($_GET['q'])) {
    $search_term = sanitize_input($_GET['q']);
    $search_results = search_documents($_SESSION['user_id'], 'client', $search_term);
}

// Page title
$page_title = "Document Search";
include '../includes/header.php';
?>

<!-- Main Content -->
<div class="ml-64 flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Document Search</h1>
        <p class="text-gray-600">Search through your case documents</p>
    </div>

    <!-- Search Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form action="" method="GET" class="flex items-center">
            <div class="relative flex-grow">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search_term); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="Search documents by title, description, or case number...">
                <button type="submit" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <!-- Search Results -->
    <?php if(!empty($search_term)): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h2>
            
            <?php if(empty($search_results)): ?>
                <div class="text-center py-8">
                    <div class="inline-block p-4 rounded-full bg-gray-50 text-gray-500 mb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Documents Found</h3>
                    <p class="text-gray-500">Try adjusting your search terms or browse through your cases.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($search_results as $doc): ?>
                        <div class="border-b border-gray-100 pb-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 p-2 rounded-full bg-blue-100 text-blue-600 mr-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-grow">
                                    <h3 class="text-lg font-medium text-gray-800"><?php echo htmlspecialchars($doc['title']); ?></h3>
                                    <p class="text-sm text-gray-600">Case: <?php echo htmlspecialchars($doc['case_number']); ?> - <?php echo htmlspecialchars($doc['case_title']); ?></p>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($doc['description']); ?></p>
                                    <div class="mt-2 flex items-center text-sm text-gray-500">
                                        <span>Uploaded on <?php echo format_date($doc['upload_date'], 'd M Y'); ?></span>
                                        <span class="mx-2">•</span>
                                        <span><?php echo format_file_size($doc['file_size']); ?></span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="view.php?id=<?php echo $doc['id']; ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?> 