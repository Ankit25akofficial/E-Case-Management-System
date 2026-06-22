<?php
session_start();
include '../../includes/config.php';
include '../../includes/functions.php';

// Check if user is logged in and is judge
if(!is_logged_in() || !is_user_type('judge')) {
    redirect('../../login.php');
}

// Define variables and initialize with empty values
$case_id = $hearing_date = $location = $notes = "";
$case_id_err = $hearing_date_err = $location_err = "";
$success_msg = $error_msg = "";

// Get judge's cases for dropdown
$judge_cases = get_judge_cases($_SESSION['user_id']);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate case
    if(empty(trim($_POST["case_id"]))) {
        $case_id_err = "Please select a case.";
    } else {
        $case_id = sanitize_input($_POST["case_id"]);
        
        // Check if judge has access to this case
        if(!user_has_case_access($case_id, $_SESSION['user_id'], 'judge')) {
            $case_id_err = "You don't have access to this case.";
        }
    }
    
    // Validate hearing date
    if(empty(trim($_POST["hearing_date"]))) {
        $hearing_date_err = "Please enter hearing date and time.";
    } else {
        $hearing_date = sanitize_input($_POST["hearing_date"]);
        
        // Check if date is in the future
        if(strtotime($hearing_date) < time()) {
            $hearing_date_err = "Hearing date must be in the future.";
        }
    }
    
    // Validate location
    if(empty(trim($_POST["location"]))) {
        $location_err = "Please enter hearing location.";
    } else {
        $location = sanitize_input($_POST["location"]);
    }
    
    // Get notes
    $notes = sanitize_input($_POST["notes"]);
    
    // Check input errors before inserting in database
    if(empty($case_id_err) && empty($hearing_date_err) && empty($location_err)) {
        
        // Prepare an insert statement
        $sql = "INSERT INTO hearings (case_id, hearing_date, location, notes, status) VALUES (?, ?, ?, ?, 'scheduled')";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "isss", $case_id, $hearing_date, $location, $notes);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                // Update case status to in_progress if it's not already
                $sql = "UPDATE cases SET status = 'in_progress' WHERE id = ? AND status != 'closed'";
                if($update_stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($update_stmt, "i", $case_id);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                }
                
                $success_msg = "Hearing scheduled successfully!";
                
                // Clear form data after successful submission
                $case_id = $hearing_date = $location = $notes = "";
            } else {
                $error_msg = "Something went wrong. Please try again later.";
            }
            
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

// Page title
$page_title = "Schedule Hearing";
include '../includes/header.php';
?>

<!-- Main Content -->
<div class="flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Schedule Hearing</h1>
            <p class="text-gray-600">Schedule a new hearing for a case</p>
        </div>
        <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Hearings
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

    <!-- Hearing Scheduling Instructions -->
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>Instructions:</strong> Please select a case and schedule a hearing date and time. Make sure to provide the location details and any special instructions for the hearing. All parties involved will be notified automatically.
                </p>
            </div>
        </div>
    </div>

    <!-- Hearing Scheduling Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
            <!-- Case Selection -->
            <div>
                <label for="case_id" class="block text-sm font-medium text-gray-700 mb-1">Select Case <span class="text-red-500">*</span></label>
                <select name="case_id" id="case_id" class="w-full border <?php echo (!empty($case_id_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-600">
                    <option value="">Select a case</option>
                    <?php foreach($judge_cases as $case): ?>
                        <option value="<?php echo $case['id']; ?>" <?php echo ($case_id == $case['id']) ? 'selected' : ''; ?>>
                            <?php echo $case['case_number'] . ' - ' . $case['title'] . ' (' . $case['client_name'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="text-red-500 text-xs"><?php echo $case_id_err; ?></span>
            </div>
            
            <!-- Hearing Date and Time -->
            <div>
                <label for="hearing_date" class="block text-sm font-medium text-gray-700 mb-1">Hearing Date and Time <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="hearing_date" id="hearing_date" class="w-full border <?php echo (!empty($hearing_date_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-600" value="<?php echo $hearing_date; ?>">
                <span class="text-red-500 text-xs"><?php echo $hearing_date_err; ?></span>
            </div>
            
            <!-- Hearing Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Hearing Location <span class="text-red-500">*</span></label>
                <input type="text" name="location" id="location" class="w-full border <?php echo (!empty($location_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-600" value="<?php echo $location; ?>" placeholder="Enter the court room or virtual meeting details">
                <span class="text-red-500 text-xs"><?php echo $location_err; ?></span>
            </div>
            
            <!-- Hearing Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes or Special Instructions</label>
                <textarea name="notes" id="notes" rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-600" placeholder="Enter any special instructions or notes for the hearing"><?php echo $notes; ?></textarea>
            </div>
            
            <!-- Additional Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Expected Duration</label>
                    <select name="duration" id="duration" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-600">
                        <option value="30">30 minutes</option>
                        <option value="60" selected>1 hour</option>
                        <option value="90">1 hour 30 minutes</option>
                        <option value="120">2 hours</option>
                        <option value="180">3 hours</option>
                        <option value="240">4 hours</option>
                        <option value="300">5 hours</option>
                        <option value="360">6 hours</option>
                        <option value="480">8 hours (Full day)</option>
                    </select>
                </div>
                <div>
                    <label for="hearing_type" class="block text-sm font-medium text-gray-700 mb-1">Hearing Type</label>
                    <select name="hearing_type" id="hearing_type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-600">
                        <option value="initial">Initial Hearing</option>
                        <option value="status">Status Conference</option>
                        <option value="motion">Motion Hearing</option>
                        <option value="trial">Trial</option>
                        <option value="sentencing">Sentencing</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            
            <!-- Notification Options -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Notification Options</label>
                <div class="flex items-center">
                    <input type="checkbox" id="notify_client" name="notify_client" class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded" checked>
                    <label for="notify_client" class="ml-2 block text-sm text-gray-700">Notify client</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="notify_lawyers" name="notify_lawyers" class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded" checked>
                    <label for="notify_lawyers" class="ml-2 block text-sm text-gray-700">Notify lawyers</label>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="send_reminders" name="send_reminders" class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded" checked>
                    <label for="send_reminders" class="ml-2 block text-sm text-gray-700">Send reminders (24 hours before hearing)</label>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <button type="reset" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Reset
                </button>
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    Schedule Hearing
                </button>
            </div>
        </form>
    </div>
    
    <!-- Calendar Preview -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Your Hearing Calendar</h2>
        <div id="calendar" class="min-h-[400px]"></div>
    </div>
</div>

<!-- JavaScript for Calendar -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get upcoming hearings data
        const hearings = [
            <?php 
            $upcoming_hearings = get_upcoming_hearings($_SESSION['user_id'], 'judge');
            foreach($upcoming_hearings as $hearing): 
            ?>
            {
                title: '<?php echo addslashes($hearing['case_number']); ?>',
                start: '<?php echo date('Y-m-d\TH:i:s', strtotime($hearing['hearing_date'])); ?>',
                url: 'view.php?id=<?php echo $hearing['id']; ?>',
                backgroundColor: '#f59e0b',
                borderColor: '#d97706'
            },
            <?php endforeach; ?>
        ];
        
        // Initialize calendar
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: hearings,
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: 'short'
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '18:00:00',
            allDaySlot: false,
            height: 'auto',
            eventClick: function(info) {
                if (info.event.url) {
                    window.location.href = info.event.url;
                    return false;
                }
            }
        });
        calendar.render();
        
        // Set minimum date for hearing date input
        const today = new Date();
        today.setDate(today.getDate() + 1); // Set minimum to tomorrow
        const formattedDate = today.toISOString().slice(0, 16);
        document.getElementById('hearing_date').min = formattedDate;
    });
</script>

<!-- Include FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

<?php include '../includes/footer.php'; ?>
