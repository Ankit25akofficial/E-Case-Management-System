<?php
// Add New Hearing (Admin)
session_start();
include_once '../../includes/config.php';
include_once '../../includes/functions.php';

// Check admin authentication
if(!is_logged_in() || !is_user_type('admin')) {
    redirect('../../login.php');
}

$errors = [];
$success = false;
$case_id = $hearing_date = $location = $notes = '';

// Fetch cases for dropdown (only those not closed)
$cases = [];
$sql = "SELECT id, case_number, title FROM cases WHERE status != 'closed' ORDER BY filing_date DESC";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)) {
    $cases[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $case_id = intval($_POST['case_id']);
    $hearing_date = trim($_POST['hearing_date']);
    $location = trim($_POST['location']);
    $notes = trim($_POST['notes']);
    $status = 'scheduled';
    
    if (!$case_id) $errors[] = 'Case is required.';
    if ($hearing_date === '') $errors[] = 'Hearing date and time are required.';
    if ($location === '') $errors[] = 'Location is required.';

    if (empty($errors)) {
        $sql = "INSERT INTO hearings (case_id, hearing_date, location, notes, status) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, 'issss', $case_id, $hearing_date, $location, $notes, $status);
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
            } else {
                $errors[] = 'Failed to add hearing.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = 'Database error.';
        }
    }
}

$page_title = 'Add New Hearing';
include '../includes/header.php';
?>
<div class="ml-64 p-8">
    <h1 class="text-2xl font-bold mb-4">Add New Hearing</h1>
    <?php if ($success): ?>
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">Hearing added successfully!
            <a href="list.php" class="ml-2 text-blue-600 underline">Back to Hearing List</a>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            <?php foreach($errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" class="bg-white rounded-lg shadow p-6 max-w-lg">
        <div class="mb-4">
            <label class="block font-semibold mb-1">Case <span class="text-red-500">*</span></label>
            <select name="case_id" class="w-full border border-gray-300 rounded px-3 py-2" required>
                <option value="">-- Select Case --</option>
                <?php foreach($cases as $case): ?>
                    <option value="<?php echo $case['id']; ?>" <?php if($case_id==$case['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($case['case_number'] . ' - ' . $case['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Hearing Date & Time <span class="text-red-500">*</span></label>
            <input type="datetime-local" name="hearing_date" value="<?php echo htmlspecialchars($hearing_date); ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Location <span class="text-red-500">*</span></label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Notes</label>
            <textarea name="notes" class="w-full border border-gray-300 rounded px-3 py-2"><?php echo htmlspecialchars($notes); ?></textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Hearing</button>
        <a href="list.php" class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
