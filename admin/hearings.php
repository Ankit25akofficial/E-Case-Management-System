<?php
// Hearing Schedule section (Admin)
session_start();
include_once '../includes/config.php';
include_once '../includes/functions.php';

// Check admin authentication
if(!is_logged_in() || !is_user_type('admin')) {
    redirect('../login.php');
}

// Fetch hearings with case and judge/client info
$sql = "SELECT h.id, h.hearing_date, h.location, h.status, h.notes, c.case_number, c.title AS case_title, 
               client.full_name AS client_name, judge.full_name AS judge_name
        FROM hearings h
        JOIN cases c ON h.case_id = c.id
        JOIN users client ON c.client_id = client.id
        LEFT JOIN users judge ON c.judge_id = judge.id
        ORDER BY h.hearing_date DESC";
$result = mysqli_query($conn, $sql);
$hearings = [];
while($row = mysqli_fetch_assoc($result)) {
    $hearings[] = $row;
}

$page_title = 'Hearing Schedule';
include 'includes/header.php';
?>
<div class="ml-64 p-8">
    <h1 class="text-2xl font-bold mb-4">Hearing Schedule</h1>
    <a href="hearings/add.php" class="mb-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ New Hearing</a>
    <div class="bg-white rounded-lg shadow md p-6 justify-center flex flex-center">
        <table class="min-w-full divide-y divide-gray-200 ">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Case #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Case Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judge</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($hearings as $hearing): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-blue-900"><?php echo htmlspecialchars($hearing['case_number']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($hearing['case_title']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($hearing['client_name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($hearing['judge_name'] ?? '-'); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d M Y, H:i', strtotime($hearing['hearing_date'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($hearing['location']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold
                            <?php
                                switch($hearing['status']) {
                                    case 'scheduled': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'completed': echo 'bg-green-100 text-green-800'; break;
                                    case 'postponed': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                            ?>"><?php echo ucfirst($hearing['status']); ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="hearings/view.php?id=<?php echo $hearing['id']; ?>" class="text-blue-600 hover:underline mr-2">View</a>
                        <a href="hearings/edit.php?id=<?php echo $hearing['id']; ?>" class="text-yellow-600 hover:underline mr-2">Edit</a>
                        <a href="hearings/delete.php?id=<?php echo $hearing['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this hearing?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if(empty($hearings)): ?>
            <p class="text-gray-500 mt-6">No hearings found.</p>
        <?php endif; ?>
    </div>
</div>
<?php include_once 'includes/footer.php'; ?>
