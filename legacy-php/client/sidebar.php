<?php
// Ensure session is started before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>E-Case Management System | Client Portal</title>
    
    <!-- Performance Optimized Assets -->
    <link rel="preload" href="../assets/css/minified.css" as="style">
    <link rel="preload" href="../assets/js/minified.js" as="script">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    
    <!-- Modern CSS with CSS Variables -->
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --danger: #ef4444;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .nav-item::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        
        .nav-item:hover::after {
            width: 100%;
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
    
    <!-- Tailwind with Just-in-Time -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#4f46e5',
                            light: '#6366f1',
                            dark: '#4338ca'
                        },
                        secondary: {
                            DEFAULT: '#10b981',
                            light: '#34d399',
                            dark: '#059669'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="antialiased">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 overflow-hidden -z-10">
        <div class="absolute top-1/4 left-1/4 w-64 h-64 rounded-full bg-primary/10 blur-3xl"></div>
        <div class="absolute bottom-1/3 right-1/4 w-80 h-80 rounded-full bg-secondary/10 blur-3xl"></div>
    </div>

    <!-- Main Layout -->
    <div class="flex h-screen">
        <!-- Sidebar - Glass Morphism Design -->
        <aside class="hidden lg:flex flex-col w-80 p-6 space-y-8 bg-white/80 backdrop-blur-lg border-r border-gray-200/50">
            <!-- Logo with Floating Animation -->
            <div class="flex items-center space-x-3">
                <div class="floating">
                    <svg class="w-10 h-10 text-primary" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19.4 15C19.2669 15.3016 19.227 15.6362 19.2861 15.9605C19.3452 16.2848 19.5003 16.5814 19.7276 16.8087C19.9549 17.036 20.2515 17.1911 20.5758 17.2502C20.9001 17.3093 21.2347 17.2694 21.5363 17.1363L21.5364 17.1363C21.838 17.0031 22.0909 16.7839 22.2576 16.5106C22.4243 16.2373 22.4959 15.9249 22.4616 15.6149C22.4273 15.3048 22.2893 15.0147 22.07 14.7954L22.07 14.7954C21.8507 14.5761 21.5606 14.4381 21.2505 14.4038C20.9405 14.3695 20.6281 14.4411 20.3548 14.6078C20.0815 14.7745 19.8623 15.0274 19.7292 15.329L19.4 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.60002 15C4.73314 15.3016 4.77304 15.6362 4.71393 15.9605C4.65482 16.2848 4.49974 16.5814 4.27243 16.8087C4.04512 17.036 3.74855 17.1911 3.42423 17.2502C3.09991 17.3093 2.7653 17.2694 2.46369 17.1363L2.46364 17.1363C2.16203 17.0031 1.90914 16.7839 1.74244 16.5106C1.57574 16.2373 1.50413 15.9249 1.53843 15.6149C1.57273 15.3048 1.71074 15.0147 1.93002 14.7954L1.93005 14.7954C2.14935 14.5761 2.43946 14.4381 2.74951 14.4038C3.05956 14.3695 3.37196 14.4411 3.64525 14.6078C3.91854 14.7745 4.13772 15.0274 4.27087 15.329L4.60002 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 19.4C15.3016 19.2669 15.6362 19.227 15.9605 19.2861C16.2848 19.3452 16.5814 19.5003 16.8087 19.7276C17.036 19.9549 17.1911 20.2515 17.2502 20.5758C17.3093 20.9001 17.2694 21.2347 17.1363 21.5363L17.1363 21.5364C17.0031 21.838 16.7839 22.0909 16.5106 22.2576C16.2373 22.4243 15.9249 22.4959 15.6149 22.4616C15.3048 22.4273 15.0147 22.2893 14.7954 22.07L14.7954 22.07C14.5761 21.8507 14.4381 21.5606 14.4038 21.2505C14.3695 20.9405 14.4411 20.6281 14.6078 20.3548C14.7745 20.0815 15.0274 19.8623 15.329 19.7292L15 19.4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 4.60002C15.3016 4.73314 15.6362 4.77304 15.9605 4.71393C16.2848 4.65482 16.5814 4.49974 16.8087 4.27243C17.036 4.04512 17.1911 3.74855 17.2502 3.42423C17.3093 3.09991 17.2694 2.7653 17.1363 2.46369L17.1363 2.46364C17.0031 2.16203 16.7839 1.90914 16.5106 1.74244C16.2373 1.57574 15.9249 1.50413 15.6149 1.53843C15.3048 1.57273 15.0147 1.71074 14.7954 1.93002L14.7954 1.93005C14.5761 2.14935 14.4381 2.43946 14.4038 2.74951C14.3695 3.05956 14.4411 3.37196 14.6078 3.64525C14.7745 3.91854 15.0274 4.13772 15.329 4.27087L15 4.60002Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19.4 8.99999C19.2669 8.69838 19.227 8.36379 19.2861 8.03947C19.3452 7.71515 19.5003 7.41859 19.7276 7.19128C19.9549 6.96397 20.2515 6.80889 20.5758 6.74978C20.9001 6.69067 21.2347 6.73057 21.5363 6.86369L21.5364 6.86374C21.838 6.99686 22.0909 7.21605 22.2576 7.48934C22.4243 7.76263 22.4959 8.07503 22.4616 8.38508C22.4273 8.69513 22.2893 8.98524 22.07 9.20454L22.07 9.20455C21.8507 9.42385 21.5606 9.56186 21.2505 9.59616C20.9405 9.63046 20.6281 9.55887 20.3548 9.39217C20.0815 9.22547 19.8623 8.97258 19.7292 8.67097L19.4 8.99999Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.60002 8.99999C4.73314 8.69838 4.77304 8.36379 4.71393 8.03947C4.65482 7.71515 4.49974 7.41859 4.27243 7.19128C4.04512 6.96397 3.74855 6.80889 3.42423 6.74978C3.09991 6.69067 2.7653 6.73057 2.46369 6.86369L2.46364 6.86374C2.16203 6.99686 1.90914 7.21605 1.74244 7.48934C1.57574 7.76263 1.50413 8.07503 1.53843 8.38508C1.57273 8.69513 1.71074 8.98524 1.93002 9.20454L1.93005 9.20455C2.14935 9.42385 2.43946 9.56186 2.74951 9.59616C3.05956 9.63046 3.37196 9.55887 3.64525 9.39217C3.91854 9.22547 4.13772 8.97258 4.27087 8.67097L4.60002 8.99999Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                    E-Case<span class="font-light">Portal</span>
                </h1>
            </div>
            
            <!-- User Profile Card -->
            <div class="glass-card p-4 flex items-center space-x-3">
                <div class="relative">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name'] ?? 'User'); ?>&background=4f46e5&color=fff&rounded=true" 
                         alt="Profile" class="w-12 h-12 rounded-full">
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                </div>
                <div>
                    <p class="font-medium"><?php echo $_SESSION['full_name'] ?? 'Guest'; ?></p>
                    <p class="text-sm text-gray-500">Client</p>
                </div>
            </div>
            
            <!-- Navigation -->
            <!-- <nav class="flex-1 space-y-2">
                <a href="index.php" class="flex items-center p-3 rounded-lg transition-all hover:bg-primary/10 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:text-primary'; ?>">
                    <i class="fas fa-tachometer-alt w-6 text-center mr-3"></i>
                    Dashboard
                </a>
                
                <a href="cases/index.php" class="flex items-center p-3 rounded-lg transition-all hover:bg-primary/10 <?php echo strpos($_SERVER['PHP_SELF'], 'cases/') !== false ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:text-primary'; ?>">
                    <i class="fas fa-folder-open w-6 text-center mr-3"></i>
                    My Cases
                    <span class="ml-auto bg-primary/10 text-primary text-xs px-2 py-1 rounded-full"><?php echo rand(1, 5); ?> active</span>
                </a>
                
                <a href="hearings/index.php" class="flex items-center p-3 rounded-lg transition-all hover:bg-primary/10 <?php echo strpos($_SERVER['PHP_SELF'], 'hearings/') !== false ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:text-primary'; ?>">
                    <i class="fas fa-calendar-day w-6 text-center mr-3"></i>
                    Hearings
                    <span class="ml-auto bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full"><?php echo rand(1, 3); ?> upcoming</span>
                </a>
                
                <a href="documents/index.php" class="flex items-center p-3 rounded-lg transition-all hover:bg-primary/10 <?php echo strpos($_SERVER['PHP_SELF'], 'documents/') !== false ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:text-primary'; ?>">
                    <i class="fas fa-file-upload w-6 text-center mr-3"></i>
                    Documents
                </a>
                
                <a href="notifications.php" class="flex items-center p-3 rounded-lg transition-all hover:bg-primary/10 <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:text-primary'; ?>">
                    <i class="fas fa-bell w-6 text-center mr-3"></i>
                    Notifications
                    <span class="ml-auto bg-primary text-white text-xs px-2 py-1 rounded-full"><?php echo rand(1, 9); ?> new</span>
                </a>
                
                <a href="profile.php" class="flex items-center p-3 rounded-lg transition-all hover:bg-primary/10 <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'bg-primary/10 text-primary' : 'text-gray-600 hover:text-primary'; ?>">
                    <i class="fas fa-user-cog w-6 text-center mr-3"></i>
                    ONLINE ASSISTANT
                </a>
            </nav> -->
            
            <!-- Bottom Actions -->
            <div class="mt-auto pt-4 border-t border-gray-200/50">
                <a href="../logout.php" class="flex items-center p-3 rounded-lg transition-all hover:bg-red-100 text-red-600">
                    <i class="fas fa-sign-out-alt w-6 text-center mr-3"></i>
                    Logout
                </a>
            </div>
        

        <!-- Mobile Sidebar Overlay -->
        <div id="mobileOverlay" class="fixed inset-0 bg-black/50 z-40 hidden"></div>
        </aside>
        <!-- Mobile Sidebar -->
        <aside id="mobileSidebar" class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-50 transform -translate-x-full transition-transform duration-300 lg:hidden">
            <!-- Mobile sidebar content would mirror desktop but with close button -->
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation - Glass Morphism -->
            <header class="glass-card bg-white/80 backdrop-blur-md border-b border-gray-200/50">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuButton" class="lg:hidden text-gray-600 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Search Bar -->
                    <div class="relative mx-4 flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary focus:border-primary" placeholder="Search cases...">
                    </div>
                    
                    <!-- Right Side Icons -->
                    <div class="flex items-center space-x-4">
                        <!-- Notification Bell -->
                        <div class="relative">
                            <button class="text-gray-600 hover:text-primary focus:outline-none">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                        </div>
                        
                        <!-- User Dropdown -->
                        <div class="relative">
                            <button id="userMenuButton" class="flex items-center space-x-2 focus:outline-none">
                                <span class="sr-only">Open user menu</span>
                                <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center">
                                    <?php echo substr($_SESSION['full_name'] ?? 'U', 0, 1); ?>
                                </div>
                                <span class="hidden md:inline-block font-medium"><?php echo $_SESSION['full_name'] ?? 'User'; ?></span>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50 border border-gray-200">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-circle mr-2"></i> Profile
                                </a>
                                <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-2"></i> Settings
                                </a>
                                <div class="border-t border-gray-200"></div>
                                <a href="../logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Your page content goes here -->
                <div class="max-w-7xl mx-auto">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6"><?php echo $page_title ?? 'Dashboard'; ?></h2>
                        
                        <!-- Content placeholder -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Example cards -->
                            <div class="glass-card p-6 rounded-xl border border-gray-200/50">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-medium text-gray-700">Active Cases</h3>
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <i class="fas fa-folder-open text-primary"></i>
                                    </div>
                                </div>
                                <p class="text-3xl font-bold"><?php echo rand(1, 5); ?></p>
                                <p class="text-sm text-gray-500 mt-2"><?php echo rand(1, 3); ?> need attention</p>
                            </div>
                            
                            <div class="glass-card p-6 rounded-xl border border-gray-200/50">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-medium text-gray-700">Upcoming Hearings</h3>
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-gavel text-green-600"></i>
                                    </div>
                                </div>
                                <p class="text-3xl font-bold"><?php echo rand(0, 3); ?></p>
                                <p class="text-sm text-gray-500 mt-2">Next in <?php echo rand(1, 14); ?> days</p>
                            </div>
                            
                            <div class="glass-card p-6 rounded-xl border border-gray-200/50">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="font-medium text-gray-700">New Documents</h3>
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-file-pdf text-blue-600"></i>
                                    </div>
                                </div>
                                <p class="text-3xl font-bold"><?php echo rand(0, 5); ?></p>
                                <p class="text-sm text-gray-500 mt-2"><?php echo rand(0, 2); ?> require signature</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');
        
        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', () => {
                mobileSidebar.classList.toggle('-translate-x-full');
                mobileOverlay.classList.toggle('hidden');
            });
            
            mobileOverlay.addEventListener('click', () => {
                mobileSidebar.classList.add('-translate-x-full');
                mobileOverlay.classList.add('hidden');
            });
        }
        
        // User dropdown toggle
        const userMenuButton = document.getElementById('userMenuButton');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userMenuButton) {
            userMenuButton.addEventListener('click', () => {
                userDropdown.classList.toggle('hidden');
            });
            
            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
        }
        
        // Active link highlighting
        document.querySelectorAll('nav a').forEach(link => {
            if (link.href === window.location.href) {
                link.classList.add('bg-primary/10', 'text-primary');
            }
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            // Toast/message on clicking Documents
            const docLink = document.querySelector('a[href$="documents.php"]');
            if(docLink) {
                docLink.addEventListener('click', function(e) {
                    // Show a toast message
                    const toast = document.createElement('div');
                    toast.textContent = 'Loading your documents...';
                    toast.className = 'fixed top-8 right-8 bg-blue-600 text-white px-6 py-3 rounded shadow-lg z-50 animate-bounce';
                    document.body.appendChild(toast);
                    setTimeout(() => {
                        toast.remove();
                    }, 1200);
                });
            }
        });
    </script>
</body>
</html>