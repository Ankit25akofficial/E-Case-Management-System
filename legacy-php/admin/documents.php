
<body class="bg-gray-50 font-sans">
    <!-- Header -->
    <header class="bg-blue-800 text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <img src="emblem.png" alt="Emblem" class="h-10 mr-2">
                    <i class="fas fa-balance-scale text-2xl"></i>
                    <h1 class="text-2xl font-bold">LegalCasePro</h1>
                </div>
                <nav class="hidden md:block">
                    <ul class="flex space-x-6">
                        <li><a href="#features" class="hover:text-blue-200 transition">Features</a></li>
                        <li><a href="#clients" class="hover:text-blue-200 transition">Clients</a></li>
                        <li><a href="#lawyers" class="hover:text-blue-200 transition">Lawyers</a></li>
                        <li><a href="#tech" class="hover:text-blue-200 transition">Technical</a></li>
                    </ul>
                </nav>
                <button class="md:hidden text-xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="mt-8 text-center">
                <h2 class="text-4xl font-bold mb-4">Case Management Documentation</h2>
                <p class="text-xl text-blue-200 max-w-3xl mx-auto">Comprehensive guide for clients and lawyers using our legal case management platform</p>
            </div>
        </div>
    </header>

    <!-- Overview Section -->
    <section class="container mx-auto px-4 py-12">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-3xl font-bold text-blue-800 mb-6">Overview</h2>
            <p class="text-gray-700 mb-6">
                This document outlines the features and functionality of our case management website designed for clients and lawyers to collaborate effectively on legal matters.
            </p>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-blue-50 p-6 rounded-lg border border-blue-100">
                    <h3 class="text-xl font-semibold text-blue-700 mb-4 flex items-center">
                        <i class="fas fa-user mr-3"></i> For Clients
                    </h3>
                    <p class="text-gray-700">
                        Easy access to case information, documents, and communication with your legal team in a secure environment.
                    </p>
                </div>
                <div class="bg-green-50 p-6 rounded-lg border border-green-100">
                    <h3 class="text-xl font-semibold text-green-700 mb-4 flex items-center">
                        <i class="fas fa-gavel mr-3"></i> For Lawyers
                    </h3>
                    <p class="text-gray-700">
                        Comprehensive tools to manage cases, clients, documents, and billing all in one integrated platform.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- User Roles Section -->
    <section id="features" class="container mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold text-blue-800 mb-8 text-center">User Roles & Features</h2>
        
        <!-- Client Features -->
        <div id="clients" class="bg-white rounded-lg shadow-md p-8 mb-12">
            <div class="flex items-center mb-6">
                <div class="bg-blue-100 p-3 rounded-full mr-4">
                    <i class="fas fa-user text-blue-700 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-blue-800">Client Features</h3>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Dashboard Card -->
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-tachometer-alt text-blue-700"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Dashboard</h4>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>View case status and updates</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>See upcoming deadlines</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>Quick document access</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Case Management Card -->
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-folder-open text-blue-700"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Case Management</h4>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>View all assigned cases</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>Check case progress timeline</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>Case update notifications</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Document Management Card -->
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-file-alt text-blue-700"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Document Management</h4>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>Upload personal documents</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>View/download case documents</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>Electronic signatures</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Communication Card -->
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-comments text-blue-700"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Communication</h4>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>Secure messaging</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>Video conference scheduling</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>Case-specific discussions</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Billing Card -->
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-credit-card text-blue-700"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Billing</h4>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>View invoices and history</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>Secure online payments</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-blue-500 mt-1 mr-2 text-sm"></i>
                            <span>Download receipts</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Lawyer Features -->
        <div id="lawyers" class="bg-white rounded-lg shadow-md p-8">
            <div class="flex items-center mb-6">
                <div class="bg-green-100 p-3 rounded-full mr-4">
                    <i class="fas fa-gavel text-green-700 text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-green-800">Lawyer Features</h3>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Dashboard Card -->
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-tachometer-alt text-green-700"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Dashboard</h4>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Overview of active cases</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Upcoming court dates</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Task management</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Case Management Card -->
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-folder-open text-green-700"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Case Management</h4>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Create/manage case files</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Assign tasks to team</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Track billable hours</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Document Management Card -->
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-file-alt text-green-700"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Document Management</h4>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Upload/organize documents</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Version control</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Template library</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Client Management Card -->
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-users text-green-700"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Client Management</h4>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Client profiles</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Conflict checking</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Intake forms</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Billing Card -->
                <div class="border rounded-lg p-6 hover:shadow-md transition">
                    <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-credit-card text-green-700"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Billing</h4>
                    <ul class="text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Time tracking</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Generate invoices</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2 text-sm"></i>
                            <span>Payment processing</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>





<?php include_once 'includes/footer.php'; ?>
