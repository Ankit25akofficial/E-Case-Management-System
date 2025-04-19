<?php
// List all cases for admin
session_start();
include_once '../../includes/config.php';
include_once '../../includes/functions.php';

// Check admin authentication
if(!is_logged_in() || !is_user_type('admin')) {
    redirect('../../login.php');
}

// Fetch cases
$sql = "SELECT cases.id, case_number, title, status, filing_date, closing_date, users.full_name AS client_name
        FROM cases
        JOIN users ON cases.client_id = users.id
        ORDER BY cases.filing_date DESC";
$result = mysqli_query($conn, $sql);
$cases = [];
while($row = mysqli_fetch_assoc($result)) {
    $cases[] = $row;
}

$page_title = 'Case Management';
include '../includes/header.php';
?>
<div class="ml-64 p-8">
    <h1 class="text-2xl font-bold mb-4">Case Management</h1>
    <a href="add.php" class="mb-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ New Case</a>
    <div class="bg-white rounded-lg shadow p-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Case #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Filing Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Closing Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach($cases as $case): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-mono text-blue-900"><?php echo htmlspecialchars($case['case_number']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($case['title']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($case['client_name']); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-block px-2 py-1 rounded text-xs font-semibold
                            <?php
                                switch($case['status']) {
                                    case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'in_progress': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'closed': echo 'bg-green-100 text-green-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800';
                                }
                            ?>"><?php echo ucfirst(str_replace('_',' ',$case['status'])); ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d M Y', strtotime($case['filing_date'])); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $case['closing_date'] ? date('d M Y', strtotime($case['closing_date'])) : '-'; ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="view.php?id=<?php echo $case['id']; ?>" class="text-blue-600 hover:underline mr-2">View</a>
                        <a href="edit.php?id=<?php echo $case['id']; ?>" class="text-yellow-600 hover:underline mr-2">Edit</a>
                        <a href="delete.php?id=<?php echo $case['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this case?');">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if(empty($cases)): ?>
            <p class="text-gray-500 mt-6">No cases found.</p>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
