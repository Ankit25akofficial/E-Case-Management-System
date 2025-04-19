<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

// Check if user is logged in and is client
if(!is_logged_in() || !is_user_type('client')) {
    redirect('../login.php');
}

// Get client's cases
$client_cases = get_client_cases($_SESSION['user_id']);

// Get upcoming hearings
$upcoming_hearings = get_upcoming_hearings($_SESSION['user_id'], 'client');

// Get recent activities
$activities = get_recent_activities($_SESSION['user_id'], 'client', 5);

// Page title
$page_title = "Client Dashboard";
include 'includes/header.php';
?>

<!-- Main Content -->
<div class="ml-64 flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Client Dashboard</h1>
        <p class="text-gray-600">Welcome back, <?php echo $_SESSION['full_name']; ?>!</p>
    </div>

    <!-- Case Summary and Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Statistics Dashboard -->
        <div class="lg:col-span-3 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Case Statistics</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Cases</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo count($client_cases); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Active Cases</p>
                            <p class="text-2xl font-bold text-gray-800">
                                <?php 
                                $active_cases = array_filter($client_cases, function($case) {
                                    return $case['status'] != 'closed';
                                });
                                echo count($active_cases);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Upcoming Hearings</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo count($upcoming_hearings); ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Documents</p>
                            <p class="text-2xl font-bold text-gray-800">
                                <?php 
                                $total_docs = 0;
                                foreach($client_cases as $case) {
                                    $docs = get_case_documents($case['id']);
                                    $total_docs += count($docs);
                                }
                                echo $total_docs;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Case Summary -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Your Cases</h2>
                <a href="cases/index.php" class="text-blue-600 hover:underline text-sm">View All Cases</a>
            </div>
            
            <?php if(empty($client_cases)): ?>
                <div class="bg-blue-50 p-4 rounded-md">
                    <p class="text-blue-700">You don't have any cases yet. Click the button below to file a new case.</p>
                    <a href="cases/add.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        File New Case
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Case Number
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Title
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Filing Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $count = 0;
                            foreach($client_cases as $case): 
                                if($count < 3): // Show only 3 recent cases
                                    $count++;
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo $case['case_number']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $case['title']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($case['status'] == 'pending'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        <?php elseif($case['status'] == 'assigned'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Assigned
                                            </span>
                                        <?php elseif($case['status'] == 'in_progress'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                In Progress
                                            </span>
                                        <?php elseif($case['status'] == 'closed'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Closed
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo format_date($case['filing_date'], 'd M Y'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="cases/view.php?id=<?php echo $case['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
            <div class="space-y-4">
                <a href="cases/add.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span>File New Case</span>
                </a>
                <a href="documents/upload.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <span>Upload Document</span>
                </a>
                <a href="hearings/index.php" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                    <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <span>View Hearings</span>
                </a>
                <a href="profile.php" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                    <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <span>Update Profile</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Upcoming Hearings and Recent Activities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Case Timeline -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Case Timeline</h2>
                <a href="cases/index.php" class="text-blue-600 hover:underline text-sm">View All Cases</a>
            </div>
            
            <?php if(empty($client_cases)): ?>
                <div class="text-center py-8">
                    <div class="inline-block p-4 rounded-full bg-blue-50 text-blue-500 mb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Cases Found</h3>
                    <p class="text-gray-500 mb-4">You don't have any cases yet. Start by filing a new case.</p>
                    <a href="cases/add.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        File New Case
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php 
                    $count = 0;
                    foreach($client_cases as $case): 
                        if($count < 2): // Show timeline for 2 most recent cases
                            $count++;
                            $timeline = get_case_timeline($case['id']);
                    ?>
                        <div class="border-b border-gray-100 pb-4">
                            <h3 class="text-lg font-medium text-gray-800 mb-2"><?php echo $case['case_number']; ?></h3>
                            <div class="relative">
                                <?php foreach($timeline as $event): ?>
                                    <div class="flex items-start mb-4">
                                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center">
                                            <?php if($event['type'] == 'case_filed'): ?>
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                            <?php elseif($event['type'] == 'hearing'): ?>
                                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            <?php else: ?>
                                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <p class="text-sm font-medium text-gray-800"><?php echo $event['title']; ?></p>
                                            <p class="text-xs text-gray-500"><?php echo format_date($event['date'], 'd M Y, h:i A'); ?></p>
                                            <p class="text-xs text-gray-600 mt-1"><?php echo $event['description']; ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-4">
                                <a href="cases/view.php?id=<?php echo $case['id']; ?>" class="text-blue-600 hover:underline text-sm inline-flex items-center">
                                    View Full Timeline
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Upcoming Hearings -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Upcoming Hearings</h2>
                <a href="hearings/index.php" class="text-blue-600 hover:underline text-sm">View All</a>
            </div>
            
            <?php if(empty($upcoming_hearings)): ?>
                <div class="text-center py-8">
                    <div class="inline-block p-4 rounded-full bg-blue-50 text-blue-500 mb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Upcoming Hearings</h3>
                    <p class="text-gray-500 mb-4">You don't have any hearings scheduled at this time.</p>
                    <div class="flex flex-col space-y-2">
                        <span class="text-sm text-gray-600 font-medium">What you can do:</span>
                        <a href="cases/add.php" class="text-blue-600 hover:underline text-sm flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            File a New Case
                        </a>
                        <a href="#" class="text-blue-600 hover:underline text-sm flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Check Hearing FAQs
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php 
                    $count = 0;
                    foreach($upcoming_hearings as $hearing): 
                        if($count < 3): // Show only 3 upcoming hearings
                            $count++;
                    ?>
                        <div class="flex items-start border-b border-gray-100 pb-4 hover:bg-gray-50 p-2 rounded transition-colors duration-200">
                            <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium"><?php echo format_date($hearing['hearing_date'], 'd M Y, h:i A'); ?></p>
                                <p class="text-xs text-gray-500">Case: <a href="cases/view.php?id=<?php echo $hearing['case_id']; ?>" class="text-blue-600 hover:underline"><?php echo $hearing['case_number']; ?></a></p>
                                <p class="text-xs text-gray-500">Judge: <?php echo $hearing['judge_name']; ?></p>
                                <p class="text-xs text-gray-500">Location: <?php echo $hearing['location']; ?></p>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <?php echo ucfirst($hearing['status']); ?>
                                    </span>
                                    <?php 
                                    $hearing_date = new DateTime($hearing['hearing_date']);
                                    $now = new DateTime();
                                    $interval = $now->diff($hearing_date);
                                    $days_remaining = $interval->days;
                                    if ($days_remaining < 7) {
                                        echo '<span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Upcoming Soon</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
                <?php if(count($upcoming_hearings) > 3): ?>
                <div class="mt-4 text-right">
                    <a href="hearings/index.php" class="text-blue-600 hover:underline text-sm inline-flex items-center">
                        View All Hearings
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Recent Activities</h2>
                <a href="activities.php" class="text-blue-600 hover:underline text-sm">View All</a>
            </div>
            
            <?php if(empty($activities)): ?>
                <p class="text-gray-500">No recent activities.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($activities as $activity): ?>
                        <div class="flex items-start border-b border-gray-100 pb-4">
                            <?php if($activity['type'] == 'case_update'): ?>
                                <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Case Update: <a href="cases/view.php?id=<?php echo $activity['id']; ?>" class="text-blue-600 hover:underline"><?php echo $activity['case_number']; ?></a></p>
                                    <p class="text-xs text-gray-500">Status changed to <?php echo ucfirst(str_replace('_', ' ', $activity['status'])); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo format_date($activity['date']); ?></p>
                                </div>
                            <?php elseif($activity['type'] == 'document_upload'): ?>
                                <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Document Uploaded: <span class="font-normal"><?php echo $activity['title']; ?></span></p>
                                    <p class="text-xs text-gray-500">For case <a href="cases/view.php?id=<?php echo $activity['id']; ?>" class="text-blue-600 hover:underline"><?php echo $activity['case_number']; ?></a></p>
                                    <p class="text-xs text-gray-400"><?php echo format_date($activity['date']); ?></p>
                                </div>
                            <?php elseif($activity['type'] == 'hearing_scheduled'): ?>
                                <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium">Hearing Scheduled</p>
                                    <p class="text-xs text-gray-500">For case <a href="cases/view.php?id=<?php echo $activity['id']; ?>" class="text-blue-600 hover:underline"><?php echo $activity['case_number']; ?></a></p>
                                    <p class="text-xs text-gray-400"><?php echo format_date($activity['date']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Case Statistics -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Your Case Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <?php
            // Calculate statistics
            $total_cases = count($client_cases);
            $pending_cases = 0;
            $in_progress_cases = 0;
            $closed_cases = 0;
            
            foreach($client_cases as $case) {
                if($case['status'] == 'pending' || $case['status'] == 'assigned') {
                    $pending_cases++;
                } elseif($case['status'] == 'in_progress') {
                    $in_progress_cases++;
                } elseif($case['status'] == 'closed') {
                    $closed_cases++;
                }
            }
            ?>
            <div class="bg-blue-50 p-4 rounded-lg transform transition-all duration-300 hover:scale-105 hover:shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Cases</p>
                        <h3 class="text-2xl font-bold text-gray-800" id="totalCasesCounter">0</h3>
                    </div>
                </div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg transform transition-all duration-300 hover:scale-105 hover:shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Pending Cases</p>
                        <h3 class="text-2xl font-bold text-gray-800" id="pendingCasesCounter">07</h3>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg transform transition-all duration-300 hover:scale-105 hover:shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">In Progress</p>
                        <h3 class="text-2xl font-bold text-gray-800" id="inProgressCasesCounter">09</h3>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg transform transition-all duration-300 hover:scale-105 hover:shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Closed Cases</p>
                        <h3 class="text-2xl font-bold text-gray-800" id="closedCasesCounter">12</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="relative">
                <canvas id="caseStatusChart" height="220"></canvas>
            </div>
            <div class="relative">
                <canvas id="caseTimelineChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Counter animation function
        function animateCounter(elementId, targetValue) {
            const element = document.getElementById(elementId);
            const duration = 1500; // Animation duration in milliseconds
            const frameDuration = 1000 / 60; // 60fps
            const totalFrames = Math.round(duration / frameDuration);
            let frame = 0;
            
            const counter = setInterval(() => {
                frame++;
                const progress = frame / totalFrames;
                const currentValue = Math.round(targetValue * progress);
                
                element.textContent = currentValue;
                
                if (frame === totalFrames) {
                    clearInterval(counter);
                    element.textContent = targetValue;
                }
            }, frameDuration);
        }
        
        // Animate counters
        setTimeout(() => {
            animateCounter('totalCasesCounter', <?php echo $total_cases; ?>);
            animateCounter('pendingCasesCounter', <?php echo $pending_cases; ?>);
            animateCounter('inProgressCasesCounter', <?php echo $in_progress_cases; ?>);
            animateCounter('closedCasesCounter', <?php echo $closed_cases; ?>);
        }, 300);
        
        // Case Status Chart
        const statusCtx = document.getElementById('caseStatusChart').getContext('2d');
        const caseStatusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Closed'],
                datasets: [{
                    data: [<?php echo $pending_cases; ?>, <?php echo $in_progress_cases; ?>, <?php echo $closed_cases; ?>],
                    backgroundColor: [
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)'
                    ],
                    borderColor: [
                        'rgba(251, 191, 36, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(16, 185, 129, 1)'
                    ],
                    borderWidth: 2,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Case Status Distribution',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.raw;
                                const percentage = Math.round((value / total) * 100);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
        
        // Case Timeline Chart - Monthly filing trend
        const timelineCtx = document.getElementById('caseTimelineChart').getContext('2d');
        
        // Generate last 6 months labels
        const months = [];
        const monthData = [0, 0, 0, 0, 0, 0];
        
        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            months.push(date.toLocaleString('default', { month: 'short', year: 'numeric' }));
        }
        
        // Count cases by month (simplified for demo)
        <?php foreach($client_cases as $index => $case): ?>
        const filingDate<?php echo $index; ?> = new Date('<?php echo $case['filing_date']; ?>');
        const monthIndex<?php echo $index; ?> = months.findIndex(month => {
            const [monthName, year] = month.split(' ');
            return (filingDate<?php echo $index; ?>.toLocaleString('default', { month: 'short' }) === monthName && 
                   filingDate<?php echo $index; ?>.getFullYear().toString() === year);
        });
        
        if (monthIndex<?php echo $index; ?> !== -1) {
            monthData[monthIndex<?php echo $index; ?>]++;
        }
        <?php endforeach; ?>
        
        const caseTimelineChart = new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Cases Filed',
                    data: monthData,
                    fill: true,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Case Filing Timeline',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
