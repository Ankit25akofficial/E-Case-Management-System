<?php
session_start();
include '../includes/config.php';
include '../includes/functions.php';

// Check if user is logged in and is judge
if(!is_logged_in() || !is_user_type('judge')) {
    redirect('../login.php');
}

// Get judge's cases
$judge_cases = get_judge_cases($_SESSION['user_id']);

// Get upcoming hearings
$upcoming_hearings = get_upcoming_hearings($_SESSION['user_id'], 'judge');

// Get recent activities
$activities = get_recent_activities($_SESSION['user_id'], 'judge', 5);

// Page title
$page_title = "Judge Dashboard";
include 'includes/header.php';
?>

<!-- Main Content -->
<div class="flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Judge Dashboard</h1>
        <p class="text-gray-600">Welcome, Hon. <?php echo $_SESSION['full_name']; ?>!</p>
    </div>

    <!-- Case Summary and Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Case Summary -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Assigned Cases</h2>
                <a href="cases/index.php" class="text-blue-600 hover:underline text-sm">View All Cases</a>
            </div>
            
            <?php if(empty($judge_cases)): ?>
                <div class="bg-blue-50 p-4 rounded-md">
                    <p class="text-blue-700">You don't have any assigned cases yet.</p>
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
                                    Client
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
                            foreach($judge_cases as $case): 
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $case['client_name']; ?>
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
                <a href="hearings/schedule.php" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                    <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <span>Schedule Hearing</span>
                </a>
                <a href="judgments/add.php" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span>Deliver Judgment</span>
                </a>
                <a href="documents/upload.php" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <span>Upload Document</span>
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
        <!-- Upcoming Hearings -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Upcoming Hearings</h2>
                <a href="hearings/index.php" class="text-blue-600 hover:underline text-sm">View All</a>
            </div>
            
            <?php if(empty($upcoming_hearings)): ?>
                <p class="text-gray-500">No upcoming hearings scheduled.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php 
                    $count = 0;
                    foreach($upcoming_hearings as $hearing): 
                        if($count < 3): // Show only 3 upcoming hearings
                            $count++;
                    ?>
                        <div class="flex items-start border-b border-gray-100 pb-4">
                            <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium"><?php echo format_date($hearing['hearing_date'], 'd M Y, h:i A'); ?></p>
                                <p class="text-xs text-gray-500">Case: <a href="cases/view.php?id=<?php echo $hearing['case_id']; ?>" class="text-blue-600 hover:underline"><?php echo $hearing['case_number']; ?></a></p>
                                <p class="text-xs text-gray-500">Client: <?php echo $hearing['client_name']; ?></p>
                                <p class="text-xs text-gray-500">Location: <?php echo $hearing['location']; ?></p>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
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
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Case Statistics -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Case Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <?php
            // Calculate statistics
            $total_cases = count($judge_cases);
            $pending_cases = 0;
            $in_progress_cases = 0;
            $closed_cases = 0;
            
            foreach($judge_cases as $case) {
                if($case['status'] == 'pending' || $case['status'] == 'assigned') {
                    $pending_cases++;
                } elseif($case['status'] == 'in_progress') {
                    $in_progress_cases++;
                } elseif($case['status'] == 'closed') {
                    $closed_cases++;
                }
            }
            ?>
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Cases</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_cases; ?></h3>
                    </div>
                </div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Pending Cases</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $pending_cases; ?></h3>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">In Progress</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $in_progress_cases; ?></h3>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Closed Cases</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $closed_cases; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6">
            <canvas id="caseStatusChart" width="400" height="100"></canvas>
        </div>
    </div>
</div>

<!-- JavaScript for Chart -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Case Status Chart
        const ctx = document.getElementById('caseStatusChart').getContext('2d');
        const caseStatusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Closed'],
                datasets: [{
                    data: [<?php echo $pending_cases; ?>, <?php echo $in_progress_cases; ?>, <?php echo $closed_cases; ?>],
                    backgroundColor: [
                        'rgba(251, 191, 36, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)'
                    ],
                    borderColor: [
                        'rgba(251, 191, 36, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(16, 185, 129, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Case Status Distribution'
                    }
                }
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
