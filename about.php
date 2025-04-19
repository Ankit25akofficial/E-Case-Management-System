<?php
// About Us page for E-Case Management System
include_once 'includes/header.php';
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center mb-12">
            <div class="md:w-1/2 mb-8 md:mb-0">
                <img src="emblem.png" alt="Emblem" class="h-24 mx-auto md:mx-0 mb-4 rounded-full shadow-lg border-white transition-transform transform hover:scale-105">
                <h1 class="text-4xl font-bold text-blue-900 mb-4">About E-Case Management System</h1>
                <p class="text-lg text-gray-700 mb-6">
                    The E-Case Management System (ECMS) is a digital initiative by the Government of India designed to streamline the judicial process, making case management more transparent, efficient, and accessible for all stakeholders—including clients, judges, and administrators.
                </p>
                <ul class="list-disc ml-8 text-gray-700 mb-6">
                    <li>Secure, role-based access for Admins, Judges, and Clients</li>
                    <li>Comprehensive case lifecycle management</li>
                    <li>Document uploads and digital evidence management</li>
                    <li>Hearing scheduling and notifications</li>
                    <li>Real-time reports and analytics</li>
                </ul>
                <p class="text-gray-700">
                    Our mission is to empower the justice system with technology, ensuring timely and fair resolution of cases while reducing paperwork and manual intervention.
                </p>
            </div>
            <div class="md:w-1/2 md:pl-12 flex flex-col justify-center items-center">
                <!-- Modern justice/case SVG illustration -->
                <svg width="340" height="260" viewBox="0 0 340 260" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full max-w-md rounded-lg shadow-lg mb-4">
                  <rect x="20" y="60" width="300" height="180" rx="20" fill="#e0e7ff"/>
                  <rect x="40" y="80" width="260" height="140" rx="12" fill="#fff"/>
                  <rect x="60" y="100" width="80" height="20" rx="6" fill="#c7d2fe"/>
                  <rect x="60" y="130" width="180" height="10" rx="5" fill="#a5b4fc"/>
                  <rect x="60" y="150" width="140" height="10" rx="5" fill="#a5b4fc"/>
                  <rect x="60" y="170" width="100" height="10" rx="5" fill="#a5b4fc"/>
                  <ellipse cx="260" cy="210" rx="30" ry="10" fill="#c7d2fe"/>
                  <g>
                    <circle cx="260" cy="170" r="28" fill="#6366f1"/>
                    <rect x="252" y="158" width="16" height="24" rx="5" fill="#fff"/>
                    <rect x="258" y="170" width="4" height="12" rx="2" fill="#6366f1"/>
                    <rect x="254" y="182" width="12" height="3" rx="1.5" fill="#6366f1"/>
                  </g>
                  <g>
                    <rect x="120" y="210" width="40" height="8" rx="4" fill="#6366f1"/>
                    <rect x="170" y="210" width="20" height="8" rx="4" fill="#818cf8"/>
                  </g>
                  <g>
                    <ellipse cx="100" cy="220" rx="14" ry="4" fill="#c7d2fe"/>
                    <rect x="90" y="200" width="20" height="20" rx="6" fill="#6366f1"/>
                    <rect x="96" y="210" width="8" height="8" rx="2" fill="#fff"/>
                  </g>
                  <g>
                    <ellipse cx="180" cy="70" rx="14" ry="4" fill="#c7d2fe"/>
                    <rect x="170" y="50" width="20" height="20" rx="6" fill="#6366f1"/>
                    <rect x="176" y="60" width="8" height="8" rx="2" fill="#fff"/>
                  </g>
                </svg>
                <img src="emblem.png" alt="Emblem" class="h-20 mt-2 mb-1 rounded-full shadow-lg border-white transition-transform transform hover:scale-105">
                <span class="text-blue-900 font-semibold text-lg">Emblem</span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-blue-800 mb-4">Key Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-lg text-blue-700 mb-2">Role-Based Dashboards</h3>
                    <p class="text-gray-600">Custom dashboards for Admins, Judges, and Clients to manage cases, hearings, and documents efficiently.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-blue-700 mb-2">Digital Documentation</h3>
                    <p class="text-gray-600">Upload, view, and manage case-related documents securely with audit trails.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-blue-700 mb-2">Automated Notifications</h3>
                    <p class="text-gray-600">Receive instant notifications for new hearings, case updates, and document uploads.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-lg text-blue-700 mb-2">Analytics & Reports</h3>
                    <p class="text-gray-600">Visualize case trends, hearing schedules, and closure rates with integrated analytics.</p>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <h2 class="text-2xl font-bold text-blue-800 mb-2">Contact Us</h2>
            <p class="text-gray-700 mb-1">National Informatics Centre (NIC), Government of India</p>
            <p class="text-gray-700">Email: <a href="mailto:support@ecms.gov.in" class="text-blue-600 underline">support@ecms.gov.in</a></p>
            <p class="text-gray-700">Phone: +91-1234-567890</p>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
