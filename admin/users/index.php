<?php
session_start();
include '../../includes/config.php';
include '../../includes/functions.php';

// Check if user is logged in and is admin
if(!is_logged_in() || !is_user_type('admin')) {
    redirect('../../login.php');
}

// Delete user if requested
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = sanitize_input($_GET['id']);
    
    // Check if user exists and is not the current user
    if($user_id != $_SESSION['user_id']) {
        $sql = "DELETE FROM users WHERE id = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if(mysqli_stmt_execute($stmt)) {
                $success_msg = "User deleted successfully.";
            } else {
                $error_msg = "Error deleting user.";
            }
            
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_msg = "You cannot delete your own account.";
    }
}

// Get all users
$users = array();
$sql = "SELECT id, username, email, user_type, full_name, created_at, status FROM users ORDER BY user_type, full_name";

if($result = mysqli_query($conn, $sql)) {
    while($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Page title
$page_title = "User Management";
include '../includes/header.php';
?>

<!-- Main Content -->
<div class="flex-1 p-8 overflow-x-hidden overflow-y-auto">
    <!-- User Management Header & Add User Button -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">User Management</h1>
        <button id="addUserBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">Add User</button>
    </div>

    <!-- Search and Filter Controls -->
    <div class="flex flex-wrap gap-4 mb-4">
        <input type="text" id="searchInput" placeholder="Search by name, username, or email" class="border px-3 py-2 rounded w-64" />
        <select id="roleFilter" class="border px-3 py-2 rounded">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="client">Client</option>
            <option value="judge">Judge</option>
        </select>
        <select id="statusFilter" class="border px-3 py-2 rounded">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Username
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Role
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="users-table-body">
                <?php foreach($users as $user): ?>
                    <tr class="user-row" data-user-type="<?php echo $user['user_type']; ?>">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <?php if($user['user_type'] == 'admin'): ?>
                                        <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    <?php elseif($user['user_type'] == 'judge'): ?>
                                        <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                        </div>
                                    <?php else: ?>
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $user['full_name']; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $user['username']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo $user['email']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($user['user_type'] == 'admin'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Administrator
                                </span>
                            <?php elseif($user['user_type'] == 'judge'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Judge
                                </span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Client
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-block px-2 py-1 rounded text-xs font-semibold <?php echo ($user['status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'; ?>">
                                <?php echo ucfirst($user['status'] ?? 'active'); ?>
                            </span>
                            <button class="ml-2 text-xs text-blue-500 toggle-status-btn" data-id="<?php echo $user['id']; ?>">Toggle</button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="#" class="text-yellow-600 hover:text-yellow-900 mr-3 edit-user-btn" data-id="<?php echo $user['id']; ?>">Edit</a>
                            <a href="?action=delete&id=<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-900 delete-user-btn">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="fixed z-50 inset-0 overflow-y-auto hidden">
  <div class="flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-xl font-bold mb-4">Add New User</h2>
      <form id="addUserForm" method="POST" action="add.php">
        <div class="mb-3">
          <label class="block mb-1">Full Name</label>
          <input type="text" name="full_name" class="w-full border px-3 py-2 rounded" required />
        </div>
        <div class="mb-3">
          <label class="block mb-1">Username</label>
          <input type="text" name="username" class="w-full border px-3 py-2 rounded" required />
        </div>
        <div class="mb-3">
          <label class="block mb-1">Email</label>
          <input type="email" name="email" class="w-full border px-3 py-2 rounded" required />
        </div>
        <div class="mb-3">
          <label class="block mb-1">Password</label>
          <input type="password" name="password" class="w-full border px-3 py-2 rounded" required />
        </div>
        <div class="mb-3">
          <label class="block mb-1">Role</label>
          <select name="user_type" class="w-full border px-3 py-2 rounded" required>
            <option value="client">Client</option>
            <option value="judge">Judge</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="block mb-1">Status</label>
          <select name="status" class="w-full border px-3 py-2 rounded">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="flex justify-end">
          <button type="button" id="closeAddUserModal" class="mr-2 px-4 py-2 bg-gray-200 rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Edit User Modal (to be filled dynamically) -->
<div id="editUserModal" class="fixed z-50 inset-0 overflow-y-auto hidden">
  <div class="flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-xl font-bold mb-4">Edit User</h2>
      <form id="editUserForm" method="POST" action="edit.php">
        <input type="hidden" name="id" id="editUserId" />
        <div class="mb-3">
          <label class="block mb-1">Full Name</label>
          <input type="text" name="full_name" id="editFullName" class="w-full border px-3 py-2 rounded" required />
        </div>
        <div class="mb-3">
          <label class="block mb-1">Username</label>
          <input type="text" name="username" id="editUsername" class="w-full border px-3 py-2 rounded" required />
        </div>
        <div class="mb-3">
          <label class="block mb-1">Email</label>
          <input type="email" name="email" id="editEmail" class="w-full border px-3 py-2 rounded" required />
        </div>
        <div class="mb-3">
          <label class="block mb-1">Role</label>
          <select name="user_type" id="editUserType" class="w-full border px-3 py-2 rounded" required>
            <option value="client">Client</option>
            <option value="judge">Judge</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="block mb-1">Status</label>
          <select name="status" id="editStatus" class="w-full border px-3 py-2 rounded">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="flex justify-end">
          <button type="button" id="closeEditUserModal" class="mr-2 px-4 py-2 bg-gray-200 rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal and Table JS -->
<script>
// Modal open/close logic
const addUserBtn = document.getElementById('addUserBtn');
const addUserModal = document.getElementById('addUserModal');
const closeAddUserModal = document.getElementById('closeAddUserModal');
addUserBtn.onclick = () => addUserModal.classList.remove('hidden');
closeAddUserModal.onclick = () => addUserModal.classList.add('hidden');

const editUserModal = document.getElementById('editUserModal');
const closeEditUserModal = document.getElementById('closeEditUserModal');
closeEditUserModal.onclick = () => editUserModal.classList.add('hidden');

// Edit user button logic
const editUserBtns = document.querySelectorAll('.edit-user-btn');
editUserBtns.forEach(btn => {
  btn.onclick = function(e) {
    e.preventDefault();
    // Fetch user data via AJAX (to be implemented)
    const userId = this.dataset.id;
    fetch(`get_user.php?id=${userId}`)
      .then(res => res.json())
      .then(user => {
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editFullName').value = user.full_name;
        document.getElementById('editUsername').value = user.username;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editUserType').value = user.user_type;
        document.getElementById('editStatus').value = user.status;
        editUserModal.classList.remove('hidden');
      });
  }
});
// Toggle status logic
const toggleStatusBtns = document.querySelectorAll('.toggle-status-btn');
toggleStatusBtns.forEach(btn => {
  btn.onclick = function() {
    const userId = this.dataset.id;
    fetch(`toggle_status.php?id=${userId}`)
      .then(() => location.reload());
  }
});
// Search/filter logic
const searchInput = document.getElementById('searchInput');
const roleFilter = document.getElementById('roleFilter');
const statusFilter = document.getElementById('statusFilter');
searchInput.oninput = filterTable;
roleFilter.onchange = filterTable;
statusFilter.onchange = filterTable;
function filterTable() {
  const search = searchInput.value.toLowerCase();
  const role = roleFilter.value;
  const status = statusFilter.value;
  document.querySelectorAll('.user-row').forEach(row => {
    const name = row.children[0].textContent.toLowerCase();
    const username = row.children[1].textContent.toLowerCase();
    const email = row.children[2].textContent.toLowerCase();
    const userType = row.dataset.userType;
    const rowStatus = row.querySelector('span').textContent.toLowerCase();
    let show = true;
    if (search && !(name.includes(search) || username.includes(search) || email.includes(search))) show = false;
    if (role && userType !== role) show = false;
    if (status && rowStatus !== status) show = false;
    row.style.display = show ? '' : 'none';
  });
}
</script>

<?php include '../includes/footer.php'; ?>
