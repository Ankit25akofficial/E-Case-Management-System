<?php
// Add New Case (Admin)
session_start();
include_once '../../includes/config.php';
include_once '../../includes/functions.php';

// Check admin authentication
if(!is_logged_in() || !is_user_type('admin')) {
    redirect('../../login.php');
}

$errors = [];
$success = false;
$title = $description = $client_id = '';

// Fetch clients for dropdown 
$clients = [];
$sql = "SELECT id, full_name FROM users WHERE user_type='client' ORDER BY full_name";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)) {
    $clients[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $client_id = intval($_POST['client_id']);
    $filing_date = date('Y-m-d');
    
    if ($title === '') $errors[] = 'Title is required.';
    if ($description === '') $errors[] = 'Description is required.';
    if (!$client_id) $errors[] = 'Client is required.';

    if (empty($errors)) {
        $case_number = generate_case_number();
        $sql = "INSERT INTO cases (case_number, title, description, client_id, status, filing_date) VALUES (?, ?, ?, ?, 'pending', ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, 'sssds', $case_number, $title, $description, $client_id, $filing_date);
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
            } else {
                $errors[] = 'Failed to add case.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = 'Database error.';
        }
    }
}

$page_title = 'Add New Case';
include '../includes/header.php';
?>
<div class="ml-64 p-8">
    <h1 class="text-2xl font-bold mb-4">Add New Case</h1>
    <?php if ($success): ?>
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">Case added successfully!
            <a href="list.php" class="ml-2 text-blue-600 underline">Back to Case List</a>
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
            <label class="block font-semibold mb-1">Case Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Description <span class="text-red-500">*</span></label>
            <textarea name="description" class="w-full border border-gray-300 rounded px-3 py-2" required><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Client <span class="text-red-500">*</span></label>
            <select name="client_id" class="w-full border border-gray-300 rounded px-3 py-2" required>
                <option value="">-- Select Client --</option>
                <?php foreach($clients as $client): ?>
                    <option value="<?php echo $client['id']; ?>" <?php if($client_id==$client['id']) echo 'selected'; ?>><?php echo htmlspecialchars($client['full_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Case</button>
        <a href="list.php" class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
