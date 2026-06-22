<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
if(!is_logged_in() || !is_user_type('admin')) {
    redirect('../login.php');
}

// Get system statistics
$stats = get_system_stats();

// Get recent activities
$activities = get_recent_activities($_SESSION['user_id'], 'admin', 10);

// Page title
$page_title = "Admin Dashboard";
include 'includes/header.php';
?>

<!-- Main Content -->
<div class="flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
        <p class="text-gray-600">Welcome back, <?php echo $_SESSION['full_name']; ?>!</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-600">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Cases</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_cases']; ?></h3>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-600">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Pending Cases</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['pending_cases']; ?></h3>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-600">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Closed Cases</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['closed_cases']; ?></h3>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-600">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Users</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo $stats['total_users']; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="users/add.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <span>Add New User</span>
            </a>
            <a href="cases/add.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <span>Create New Case</span>
            </a>
            <a href="hearings/schedule.php" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span>Schedule Hearing</span>
            </a>
            <a href="reports/index.php" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <span>Generate Reports</span>
            </a>
        </div>
    </div>

    <!-- Recent Activities and Upcoming Hearings -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Activities</h2>
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
                                    <p class="text-xs text-gray-500">Status changed to <?php echo ucfirst($activity['status']); ?></p>
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
                                    <p class="text-xs text-gray-500">For case <a href="cases/view.php?id=<?php echo $activity['id']; ?>" class="text-blue-600 hover:underline"><?php echo $activity['case_number']; ?></a> by <?php echo $activity['client_name']; ?></p>
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
                <div class="mt-4 text-right">
                    <a href="activities.php" class="text-blue-600 hover:underline text-sm">View All Activities</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Upcoming Hearings -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Upcoming Hearings</h2>
            <?php 
            $upcoming_hearings = get_upcoming_hearings($_SESSION['user_id'], 'admin');
            if(empty($upcoming_hearings)): 
            ?>
                <p class="text-gray-500">No upcoming hearings.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($upcoming_hearings as $hearing): ?>
                        <div class="flex items-start border-b border-gray-100 pb-4">
                            <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium"><?php echo format_date($hearing['hearing_date'], 'd M Y, h:i A'); ?></p>
                                <p class="text-xs text-gray-500">Case: <a href="cases/view.php?id=<?php echo $hearing['case_id']; ?>" class="text-blue-600 hover:underline"><?php echo $hearing['case_number']; ?></a></p>
                                <p class="text-xs text-gray-500">Client: <?php echo $hearing['client_name']; ?> | Judge: <?php echo $hearing['judge_name']; ?></p>
                                <p class="text-xs text-gray-500">Location: <?php echo $hearing['location']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 text-right">
                    <a href="hearings/index.php" class="text-blue-600 hover:underline text-sm">View All Hearings</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
