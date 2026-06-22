<?php
// Ensure session is started before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if we're already in the client portal
if (!defined('CLIENT_HEADER_INCLUDED')) {
    define('CLIENT_HEADER_INCLUDED', true);
    include_once __DIR__ . '/../sidebar.php';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>E-Case Management System | Client Portal</title>
        
        <!-- Preload critical resources -->
        <link rel="preload" href="../assets/css/style.css" as="style">
        <link rel="preload" href="../assets/js/main.js" as="script">
        <link rel="preload" href="emblem.png" as="image">
        
        <!-- Meta tags for better SEO and performance -->
        <meta name="description" content="E-Case Management System for the Indian Judicial System - Client Portal">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="theme-color" content="#2563eb">
        
        <!-- CSS Libraries -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
        <link rel="stylesheet" href="../assets/css/style.css">
        
        <!-- Defer non-critical JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/gsap@3.9.1/dist/gsap.min.js" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js" defer></script>
        <script src="../assets/js/performance.js" defer></script>
        
        <!-- CSS Libraries with media attribute for non-blocking loading -->
        <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" media="print" onload="this.media='all'"/>
        
        <!-- Inline critical CSS -->
        <style>
            /* Critical CSS for above-the-fold content */
            .sidebar-animation {
                height: 200px;
                margin-top: 20px;
                margin-bottom: 20px;
            }
            .sidebar-link.active {
                background-color: rgba(37, 99, 235, 0.1);
                border-left: 4px solid #2563eb;
            }
            .sidebar-link:hover {
                background-color: rgba(37, 99, 235, 0.05);
            }
            .animated-icon {
                transition: transform 0.3s ease;
            }
            .sidebar-link:hover .animated-icon {
                transform: scale(1.2);
            }
            .nav-item-hover {
                position: relative;
                overflow: hidden;
            }
            .nav-item-hover:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 0%;
                height: 2px;
                background-color: #f3f4f6;
                transition: width 0.3s ease;
            }
            .nav-item-hover:hover:after {
                width: 100%;
            }
            .pulse-animation {
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0% {
                    transform: scale(1);
                    opacity: 1;
                }
                50% {
                    transform: scale(1.05);
                    opacity: 0.8;
                }
                100% {
                    transform: scale(1);
                    opacity: 1;
                }
            }
            .float-animation {
                animation: float 3s ease-in-out infinite;
            }
            @keyframes float {
                0% {
                    transform: translateY(0px);
                }
                50% {
                    transform: translateY(-10px);
                }
                100% {
                    transform: translateY(0px);
                }
            }
            .rotate-animation {
                animation: rotate 10s linear infinite;
            }
            @keyframes rotate {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }
            
            /* Skeleton loading placeholders */
            .skeleton {
                background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                background-size: 200% 100%;
                animation: skeleton-loading 1.5s infinite;
            }
            @keyframes skeleton-loading {
                0% {
                    background-position: 200% 0;
                }
                100% {
                    background-position: -200% 0;
                }
            }
        </style>
    </head>
    <body class="bg-gray-100">
    </body>
    </html>
<?php } ?>
