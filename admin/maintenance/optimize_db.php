<?php
/**
 * Database Optimization Script
 * 
 * This script performs database maintenance tasks:
 * 1. Removes expired cache entries
 * 2. Optimizes database tables
 * 3. Logs performance metrics
 */

// Include configuration and functions
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is admin
if(!is_logged_in() || !is_user_type('admin')) {
    redirect('../../login.php');
}

// Initialize variables
$messages = [];
$errors = [];
$start_time = microtime(true);

// Function to log maintenance activity
function log_maintenance($action, $details) {
    global $conn;
    $sql = "INSERT INTO maintenance_log (action, details, executed_by, execution_time) 
            VALUES (?, ?, ?, NOW())";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        $user_id = $_SESSION['user_id'];
        mysqli_stmt_bind_param($stmt, "ssi", $action, $details, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Create maintenance_log table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS maintenance_log (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    executed_by INT NOT NULL,
    execution_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (executed_by) REFERENCES users(id)
)";
mysqli_query($conn, $sql);

// 1. Remove expired cache entries
$sql = "DELETE FROM cache WHERE ttl < ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    $current_time = time();
    mysqli_stmt_bind_param($stmt, "i", $current_time);
    
    if(mysqli_stmt_execute($stmt)) {
        $deleted_rows = mysqli_stmt_affected_rows($stmt);
        $messages[] = "Removed $deleted_rows expired cache entries.";
        log_maintenance("Cache Cleanup", "Removed $deleted_rows expired cache entries");
    } else {
        $errors[] = "Error removing expired cache entries: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
}

// 2. Optimize database tables
$tables = [];
$result = mysqli_query($conn, "SHOW TABLES");
while($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

foreach($tables as $table) {
    // Check table status
    $status_result = mysqli_query($conn, "CHECK TABLE `$table`");
    $status = mysqli_fetch_assoc($status_result);
    
    if($status['Msg_text'] == 'OK') {
        // Optimize table
        if(mysqli_query($conn, "OPTIMIZE TABLE `$table`")) {
            $messages[] = "Optimized table: $table";
        } else {
            $errors[] = "Error optimizing table $table: " . mysqli_error($conn);
        }
    } else {
        $errors[] = "Table $table has issues: " . $status['Msg_text'];
        
        // Try to repair table
        if(mysqli_query($conn, "REPAIR TABLE `$table`")) {
            $messages[] = "Repaired table: $table";
            log_maintenance("Table Repair", "Repaired table: $table");
        } else {
            $errors[] = "Error repairing table $table: " . mysqli_error($conn);
        }
    }
}

// 3. Analyze tables for better query optimization
foreach($tables as $table) {
    if(mysqli_query($conn, "ANALYZE TABLE `$table`")) {
        $messages[] = "Analyzed table: $table";
    } else {
        $errors[] = "Error analyzing table $table: " . mysqli_error($conn);
    }
}

// Calculate execution time
$execution_time = microtime(true) - $start_time;
$messages[] = "Maintenance completed in " . round($execution_time, 4) . " seconds.";
log_maintenance("Maintenance Complete", "Execution time: " . round($execution_time, 4) . " seconds");

// Page title
$page_title = "Database Optimization";
include '../includes/header.php';
?>

<!-- Main Content -->
<div class="flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Database Optimization</h1>
            <p class="text-gray-600">Maintenance tasks to optimize database performance</p>
        </div>
        <a href="../index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center transition duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Dashboard
        </a>
    </div>

    <!-- Results -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Maintenance Results</h2>
            
            <?php if(!empty($messages)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Success</h3>
                            <div class="mt-2 text-sm text-green-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <?php foreach($messages as $message): ?>
                                        <li><?php echo $message; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Errors</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <?php foreach($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Database Statistics -->
            <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">Database Statistics</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rows</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($tables as $table): ?>
                            <?php
                            // Get table statistics
                            $stats_result = mysqli_query($conn, "SHOW TABLE STATUS LIKE '$table'");
                            $stats = mysqli_fetch_assoc($stats_result);
                            $size = ($stats['Data_length'] + $stats['Index_length']) / 1024;
                            $size_unit = 'KB';
                            
                            if($size > 1024) {
                                $size = $size / 1024;
                                $size_unit = 'MB';
                            }
                            
                            $size = round($size, 2);
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $table; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo number_format($stats['Rows']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $size . ' ' . $size_unit; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if($stats['Engine'] == 'InnoDB'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Optimized
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <?php echo $stats['Engine']; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Maintenance History -->
            <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">Maintenance History</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Executed By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        $log_sql = "SELECT m.*, u.full_name 
                                    FROM maintenance_log m 
                                    JOIN users u ON m.executed_by = u.id 
                                    ORDER BY m.execution_time DESC 
                                    LIMIT 10";
                        $log_result = mysqli_query($conn, $log_sql);
                        
                        if(mysqli_num_rows($log_result) > 0):
                            while($log = mysqli_fetch_assoc($log_result)):
                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y, h:i A', strtotime($log['execution_time'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $log['action']; ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo $log['details']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $log['full_name']; ?></td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No maintenance history found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Schedule Maintenance -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Schedule Maintenance</h3>
                <p class="text-gray-600 mb-4">Set up automatic database optimization to run at regular intervals.</p>
                
                <form action="schedule_maintenance.php" method="post" class="max-w-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="frequency" class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <select name="frequency" id="frequency" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="daily">Daily</option>
                                <option value="weekly" selected>Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div>
                            <label for="time" class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                            <input type="time" name="time" id="time" value="02:00" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Server time (recommended during off-peak hours)</p>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                            Schedule Maintenance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
