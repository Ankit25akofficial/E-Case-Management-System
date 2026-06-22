<?php
session_start();
include 'includes/config.php';
include 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Case Management System | Indian Government</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.132.2/build/three.min.js"></script>
    <style>
        .hero-section {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .typing-text {
            border-right: 2px solid #fff;
            animation: blink 0.75s step-end infinite;
        }
        @keyframes blink {
            from, to { border-color: transparent }
            50% { border-color: #fff; }
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .feature-icon {
            transition: all 0.3s ease;
        }
        .feature-icon:hover {
            transform: scale(1.1) rotate(5deg);
        }
        .parallax {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        .stats-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
        }
        .glow {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header Section -->
    <header class="bg-blue-900 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <img src="emblem.png" alt="Indian Emblem" class="h-16 mx-auto mb-4 rounded-full shadow-lg border-2 border-white transition-transform transform hover:scale-105">  
                <div>
                    <h1 class="text-2xl font-bold">E-Case Management System</h1>
                    <p class="text-sm">Government of India</p>
                </div>
            </div>
            <nav class="hidden md:flex space-x-6">
                <a href="index.php" class="hover:text-yellow-300 transition">Home</a>
                <a href="about.php" class="hover:text-yellow-300 transition">About</a>
                <a href="profile.php" class="hover:text-yellow-300 transition">Online Assistance</a>
                <!-- <a href="services.php" class="hover:text-yellow-300 transition">Services</a>    -->
                <a href="contact.php" class="hover:text-yellow-300 transition">Contact</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="hover:text-yellow-300 transition">Dashboard</a>
                    <a href="profile.php" class="hover:text-yellow-300 transition">Online Assistance</a>
                    <a href="logout.php" class="hover:text-yellow-300 transition">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-yellow-300 transition">Login</a>
                    <a href="register.php" class="hover:text-yellow-300 transition">Register</a>
                    <a href="profile.php" class="hover:text-yellow-300 transition">Online Assistance</a>
                  

                    </a>
                <?php endif; ?>
            </nav>
            <button id="mobile-menu-button" class="md:hidden text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-blue-800 pb-4 px-4">
            <a href="index.php" class="block py-2 hover:text-yellow-300 transition">Home</a>
            <a href="about.php" class="block py-2 hover:text-yellow-300 transition">About</a>
            <a href="services.php" class="block py-2 hover:text-yellow-300 transition">Services</a>
            <a href="contact.php" class="block py-2 hover:text-yellow-300 transition">Contact</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="block py-2 hover:text-yellow-300 transition">Dashboard</a>
                <a href="logout.php" class="block py-2 hover:text-yellow-300 transition">Logout</a>
            <?php else: ?>
                <a href="login.php" class="block py-2 hover:text-yellow-300 transition">Login</a>
                <a href="register.php" class="block py-2 hover:text-yellow-300 transition">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section text-white">
        <div class="container mx-auto px-4 py-20">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="md:w-1/2 mb-10 md:mb-0" data-aos="fade-right">
                    <h1 class="text-5xl font-bold mb-6">
                        <span class="typing-text">E-Case Management System</span>
                    </h1>
                    <p class="text-xl mb-8 opacity-90">A digital initiative by the Government of India to streamline the judicial process and enhance access to justice.</p>
                    <div class="flex space-x-4">
                        <a href="login.php" class="bg-white text-blue-900 px-8 py-3 rounded-lg font-semibold hover:bg-blue-100 transition duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="register.php" class="border-2 border-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-900 transition duration-300">
                            <i class="fas fa-user-plus mr-2"></i>Register
                        </a>
                    </div>
                </div>
                <div class="md:w-1/2" data-aos="fade-left">
                    <div class="floating">
                        <img src="emblem.png" alt="E-CMS Illustration" class="w-full max-w-lg mx-auto">
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,149.3C960,160,1056,160,1152,138.7C1248,117,1344,75,1392,53.3L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Key Features</h2>
                <p class="text-xl text-gray-600">Experience the power of digital case management</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card-hover p-6 rounded-lg bg-white shadow-lg" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon text-blue-600 text-4xl mb-4">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Digital Case Filing</h3>
                    <p class="text-gray-600">File cases online with ease and track their progress in real-time.</p>
                </div>
                <div class="card-hover p-6 rounded-lg bg-white shadow-lg" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon text-green-600 text-4xl mb-4">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Advanced Search</h3>
                    <p class="text-gray-600">Quickly find cases and documents with our powerful search engine.</p>
                </div>
                <div class="card-hover p-6 rounded-lg bg-white shadow-lg" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon text-purple-600 text-4xl mb-4">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Real-time Updates</h3>
                    <p class="text-gray-600">Get instant notifications about your case status and hearings.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 parallax" style="background-image: url('assets/images/stats-bg.jpg');">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="stats-card p-6 rounded-lg text-white text-center" data-aos="zoom-in">
                    <div class="text-4xl font-bold mb-2">10K+</div>
                    <div class="text-lg">Active Cases</div>
                </div>
                <div class="stats-card p-6 rounded-lg text-white text-center" data-aos="zoom-in" data-aos-delay="100">
                    <div class="text-4xl font-bold mb-2">5K+</div>
                    <div class="text-lg">Registered Users</div>
                </div>
                <div class="stats-card p-6 rounded-lg text-white text-center" data-aos="zoom-in" data-aos-delay="200">
                    <div class="text-4xl font-bold mb-2">99%</div>
                    <div class="text-lg">Success Rate</div>
                </div>
                <div class="stats-card p-6 rounded-lg text-white text-center" data-aos="zoom-in" data-aos-delay="300">
                    <div class="text-4xl font-bold mb-2">24/7</div>
                    <div class="text-lg">Support Available</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">What Our Users Say</h2>
                <p class="text-xl text-gray-600">Hear from our satisfied users</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card-hover p-6 rounded-lg bg-white shadow-lg" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center mb-4">
                        <img src="emblem.png" alt="User" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-semibold">Adv. Rajesh Kumar</h4>
                            <p class="text-gray-600 text-sm">Senior Advocate</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"The E-CMS has revolutionized how I manage my cases. The digital filing system saves me hours of paperwork."</p>
                </div>
                <div class="card-hover p-6 rounded-lg bg-white shadow-lg" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center mb-4">
                        <img src="emblem.png" alt="User" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-semibold">Dr. Priya Sharma</h4>
                            <p class="text-gray-600 text-sm">Legal Consultant</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"The real-time updates and notifications keep me informed about my cases, making my work much more efficient."</p>
                </div>
                <div class="card-hover p-6 rounded-lg bg-white shadow-lg" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center mb-4">
                        <img src="emblem.png" alt="User" class="w-12 h-12 rounded-full mr-4">
                        <div>
                            <h4 class="font-semibold">Mr. Amit Patel</h4>
                            <p class="text-gray-600 text-sm">Corporate Lawyer</p>
                        </div>
                    </div>
                    <p class="text-gray-600">"The advanced search feature helps me find relevant cases quickly, saving valuable time in research."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-blue-900 text-white">
        <div class="container mx-auto px-4 text-center" data-aos="fade-up">
            <h2 class="text-4xl font-bold mb-6">Ready to Get Started?</h2>
            <p class="text-xl mb-8 opacity-90">Join thousands of legal professionals using E-CMS</p>
            <a href="register.php" class="bg-white text-blue-900 px-8 py-3 rounded-lg font-semibold hover:bg-blue-100 transition duration-300">
                <i class="fas fa-user-plus mr-2"></i>Create Account
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blue-900 text-white py-10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">E-Case Management</h3>
                    <p class="mb-4">A digital initiative by the Government of India to streamline the judicial process.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-white hover:text-yellow-300">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-white hover:text-yellow-300">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-white hover:text-yellow-300">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="hover:text-yellow-300 transition">Home</a></li>
                        <li><a href="about.php" class="hover:text-yellow-300 transition">About Us</a></li>
                        <li><a href="services.php" class="hover:text-yellow-300 transition">Services</a></li>
                        <li><a href="contact.php" class="hover:text-yellow-300 transition">Contact Us</a></li>
                        <li><a href="faq.php" class="hover:text-yellow-300 transition">FAQs</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">User Portals</h3>
                    <ul class="space-y-2">
                        <li><a href="client_login.php" class="hover:text-yellow-300 transition">Client Portal</a></li>
                        <li><a href="judge_login.php" class="hover:text-yellow-300 transition">Judge Portal</a></li>
                        <li><a href="admin_login.php" class="hover:text-yellow-300 transition">Admin Portal</a></li>
                        <li><a href="help.php" class="hover:text-yellow-300 transition">Help & Support</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact Information</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mt-1 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Ministry of Law and Justice, Shastri Bhawan, New Delhi - 110001</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span>+91 11 2338 3823</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>ecms@gov.in</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-blue-800 mt-8 pt-8 text-center">
                <p>&copy; <?php echo date('Y'); ?> E-Case Management System. All rights reserved.</p>
                <p class="mt-2 text-sm">Designed and Developed by National Informatics Centre (NIC), Government of India</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Typing animation
        const text = "E-Case Management System";
        let i = 0;
        const speed = 100;

        function typeWriter() {
            if (i < text.length) {
                document.querySelector('.typing-text').textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, speed);
            }
        }

        // Start typing animation when page loads
        window.addEventListener('load', typeWriter);

        // Parallax effect
        window.addEventListener('scroll', function() {
            const parallax = document.querySelector('.parallax');
            let scrollPosition = window.pageYOffset;
            parallax.style.backgroundPositionY = scrollPosition * 0.5 + 'px';
        });
    </script>
</body>
</html>
