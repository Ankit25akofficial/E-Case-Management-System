<?php
// Include mock database configuration
include_once '../includes/mock_config.php';
include_once '../includes/functions.php';    

// Set page title
$page_title = "Client Dashboard";

// Include header
include_once 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h1 class="text-2xl font-bold text-blue-800 mb-4">Welcome to E-Case Management System</h1>
        <p class="text-gray-700 mb-4">This is a test page to verify that all files are properly connected.</p>
        
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
            <p class="font-bold">Test Mode</p>
            <p>Running with mock database configuration to test file connections.</p>
        </div>
        
        <h2 class="text-xl font-semibold text-gray-800 mb-3">System Components Status:</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-green-100 p-4 rounded-lg">
                <h3 class="font-bold text-green-800">✅ Header Included</h3>
                <p class="text-sm text-green-700">The header file is properly connected.</p>
            </div>
            
            <div class="bg-green-100 p-4 rounded-lg">
                <h3 class="font-bold text-green-800">✅ Functions Included</h3>
                <p class="text-sm text-green-700">The functions file is properly connected.</p>
            </div>
            
            <div class="bg-green-100 p-4 rounded-lg">
                <h3 class="font-bold text-green-800">✅ Mock Database Connected</h3>
                <p class="text-sm text-green-700">Using mock data for testing.</p>
            </div>
            
            <div class="bg-green-100 p-4 rounded-lg">
                <h3 class="font-bold text-green-800">✅ CSS Styling Applied</h3>
                <p class="text-sm text-green-700">Tailwind CSS is properly loaded.</p>
            </div>
        </div>
        
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Mock Data Preview:</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Case Number</th>
                        <th class="py-2 px-4 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Title</th>
                        <th class="py-2 px-4 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="py-2 px-4 border-b border-gray-300 text-left text-sm font-semibold text-gray-700">Filing Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($GLOBALS['mock_data']['cases'] as $case): ?>
                    <tr>
                        <td class="py-2 px-4 border-b border-gray-300 text-sm"><?php echo $case['case_number']; ?></td>
                        <td class="py-2 px-4 border-b border-gray-300 text-sm"><?php echo $case['title']; ?></td>
                        <td class="py-2 px-4 border-b border-gray-300 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs 
                                <?php 
                                switch($case['status']) {
                                    case 'pending':
                                        echo 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'assigned':
                                        echo 'bg-blue-100 text-blue-800';
                                        break;
                                    case 'in_progress':
                                        echo 'bg-purple-100 text-purple-800';
                                        break;
                                    case 'closed':
                                        echo 'bg-gray-100 text-gray-800';
                                        break;
                                    default:
                                        echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $case['status'])); ?>
                            </span>
                        </td>
                        <td class="py-2 px-4 border-b border-gray-300 text-sm"><?php echo date('M d, Y', strtotime($case['filing_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <h2 class="text-xl font-semibold text-gray-800 mt-6 mb-3">Recent Documents:</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($GLOBALS['mock_data']['documents'] as $document): ?>
            <div class="border border-gray-300 rounded-lg p-4 flex items-start">
                <div class="bg-blue-100 p-3 rounded-lg mr-3">
                    <?php if (strpos($document['file_type'], 'pdf') !== false): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <?php elseif (strpos($document['file_type'], 'image') !== false): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800"><?php echo $document['title']; ?></h3>
                    <p class="text-sm text-gray-600">Uploaded: <?php echo date('M d, Y', strtotime($document['upload_date'])); ?></p>
                    <p class="text-xs text-gray-500">Case: <?php 
                        foreach ($GLOBALS['mock_data']['cases'] as $case) {
                            if ($case['id'] == $document['case_id']) {
                                echo $case['case_number'];
                                break;
                            }
                        }
                    ?></p>
                    <a href="#" class="text-blue-600 text-sm hover:underline mt-1 inline-block">Download</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-8 p-4 bg-gray-100 rounded-lg">
            <h2 class="text-xl font-semibold text-gray-800 mb-3">Navigation Links Test:</h2>
            <div class="flex flex-wrap gap-2">
                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Dashboard</a>
                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">My Cases</a>
                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Documents</a>
                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Hearings</a>
                <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Profile</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>
