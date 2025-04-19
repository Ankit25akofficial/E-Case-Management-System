<?php
session_start();
include_once '../includes/config.php';
include_once '../includes/functions.php';

// Check if user is logged in and is client
if(!is_logged_in() || !is_user_type('client')) {
    redirect('../login.php');
}

// Get client's cases
$client_cases = get_client_cases($_SESSION['user_id']);

$page_title = 'My Cases';
include_once 'includes/header.php';
?>
<div class="ml-64 p-8">
    <h1 class="text-2xl font-bold mb-4">My Cases</h1>
    <?php if(empty($client_cases)): ?>
        <div class="bg-blue-50 p-4 rounded-md">
            <p class="text-blue-700">You don't have any cases yet. Click below to file a new case.</p>
            <a href="cases/add.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">File New Case</a>
            <a href="#" id="showHearingsBtn" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Show Hearings</a>
            <div id="hearingsOutput" class="mt-4 hidden text-green-700 font-semibold"></div>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Case Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filing Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($client_cases as $case): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-blue-900"><?php echo htmlspecialchars($case['case_number']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($case['title']); ?></td>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="cases/view.php?id=<?php echo $case['id']; ?>" class="text-blue-600 hover:underline mr-2">View</a>
                                <a href="cases/edit.php?id=<?php echo $case['id']; ?>" class="text-yellow-600 hover:underline mr-2">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php include_once 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('showHearingsBtn');
    const output = document.getElementById('hearingsOutput');
    if(btn && output) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            output.textContent = 'Hearings\n1 upcoming';
            output.classList.remove('hidden');
        });
    }
});
</script>
