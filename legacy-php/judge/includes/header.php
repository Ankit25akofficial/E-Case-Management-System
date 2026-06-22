<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>E-Case Management System | Judge Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.9.1/dist/gsap.min.js"></script>
    <!-- Custom Judge CSS -->
    <style>
        .sidebar-animation {
            height: 200px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .sidebar-link.active {
            background-color: rgba(245, 158, 11, 0.1);
            border-left: 4px solid #f59e0b;
        }
        .sidebar-link:hover {
            background-color: rgba(245, 158, 11, 0.05);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-yellow-600 text-white px-6 py-3">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="mr-4 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <a href="index.php" class="flex items-center">
                    <img src="emblem.png" alt="Indian Emblem" class="h-10 mr-3 rounded-full shadow-lg border-white transition-transform transform hover:scale-105">
                    <div>
                        <h1 class="text-xl font-bold">E-Case Management</h1>
                        <p class="text-xs">Judge Portal</p>
                    </div>
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button id="notifications-toggle" class="focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                    </button>
                    <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-700">Notifications</h3>
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-100">
                                <p class="font-medium">New case assigned</p>
                                <p class="text-xs text-gray-500">Case #ECMS-2025-1234 has been assigned to you</p>
                                <p class="text-xs text-gray-400">2 hours ago</p>
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-100">
                                <p class="font-medium">Document uploaded</p>
                                <p class="text-xs text-gray-500">New document uploaded for Case #ECMS-2025-5678</p>
                                <p class="text-xs text-gray-400">Yesterday</p>
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <p class="font-medium">Hearing reminder</p>
                                <p class="text-xs text-gray-500">Hearing for Case #ECMS-2025-9012 tomorrow at 10:00 AM</p>
                                <p class="text-xs text-gray-400">3 days ago</p>
                            </a>
                        </div>
                        <div class="px-4 py-2 border-t border-gray-100 text-center">
                            <a href="notifications.php" class="text-xs text-yellow-600 hover:underline">View all notifications</a>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <button id="user-menu-button" class="flex items-center focus:outline-none">
                        <img src="emblem.png" alt="User Avatar" class="h-8 w-8 rounded-full mr-2 rounded-full shadow-lg border-white transition-transform transform hover:scale-105 ">
                        <!-- <span>Hon. <?php echo $_SESSION['full_name']; ?></span> -->
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="user-menu-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                        <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                        <div class="border-t border-gray-100"></div>
                        <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-white shadow-md">
            <div class="p-4">
                <div class="sidebar-animation" id="sidebar-animation"></div>
                <nav class="mt-6">
                    <ul class="space-y-1">
                        <li>
                            <a href="index.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-md <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="cases/index.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-md <?php echo strpos($_SERVER['PHP_SELF'], 'cases/') !== false ? 'active' : ''; ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Assigned Cases
                            </a>
                        </li>
                        <li>
                            <a href="hearings/index.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-md <?php echo strpos($_SERVER['PHP_SELF'], 'hearings/') !== false ? 'active' : ''; ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Hearing Schedule
                            </a>
                        </li>
                        <li>
                            <a href="judgments/index.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-md <?php echo strpos($_SERVER['PHP_SELF'], 'judgments/') !== false ? 'active' : ''; ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                Judgments
                            </a>
                        </li>
                        <li>
                            <a href="documents/index.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-md <?php echo strpos($_SERVER['PHP_SELF'], 'documents/') !== false ? 'active' : ''; ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Documents
                            </a>
                        </li>
                        <li>
                            <a href="notifications.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-md <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                Notifications
                            </a>
                        </li>
                        <li>
                            <a href="profile.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-md <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                My Profile
                            </a>
                        </li>
                        <li>
                            <a href="help.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 rounded-md <?php echo basename($_SERVER['PHP_SELF']) == 'help.php' ? 'active' : ''; ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Help & Support
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
