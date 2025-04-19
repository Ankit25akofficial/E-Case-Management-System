    </div>
    <!-- End of Main Content Wrapper -->

    <!-- Footer -->
    <footer class="bg-white py-4 mt-auto border-t border-gray-200">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p class="text-sm text-gray-600">&copy; <?php echo date('Y'); ?> E-Case Management System. All rights reserved.</p>
                    <p class="text-xs text-gray-500 mt-1">Designed and Developed by National Informatics Centre (NIC), Government of India</p>
                </div>
                <div class="flex space-x-4">
                    <a href="../help.php" class="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-300">made by Ankit❤️</a>
                    <a href="../privacy.php" class="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-300">Privacy Policy</a>
                    <a href="../terms.php" class="text-sm text-blue-600 hover:text-blue-800 transition-colors duration-300">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Initialize AOS animations
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Mobile Sidebar Toggle with animation
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('hidden')) {
                sidebar.classList.remove('hidden');
                gsap.from(sidebar, {
                    x: -300,
                    duration: 0.5,
                    ease: "power2.out"
                });
            } else {
                gsap.to(sidebar, {
                    x: -300,
                    duration: 0.5,
                    ease: "power2.in",
                    onComplete: function() {
                        sidebar.classList.add('hidden');
                        gsap.set(sidebar, {x: 0});
                    }
                });
            }
        });

        // User Profile Dropdown Toggle
        const userMenuButton = document.getElementById('user-menu-button');
        const userDropdown = document.getElementById('user-dropdown');
        
        if (userMenuButton && userDropdown) {
            userMenuButton.addEventListener('click', function() {
                userDropdown.classList.toggle('hidden');
                
                if (!userDropdown.classList.contains('hidden')) {
                    // Add animation when showing dropdown
                    userDropdown.classList.add('animate__fadeIn');
                    userDropdown.classList.remove('animate__fadeOut');
                } else {
                    // Add animation when hiding dropdown
                    userDropdown.classList.add('animate__fadeOut');
                    userDropdown.classList.remove('animate__fadeIn');
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
        }

        // Notifications Dropdown Toggle
        document.getElementById('notifications-toggle').addEventListener('click', function() {
            const dropdown = document.getElementById('notifications-dropdown');
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                gsap.from(dropdown, {
                    y: -20,
                    opacity: 0,
                    duration: 0.3,
                    ease: "back.out(1.7)"
                });
            } else {
                gsap.to(dropdown, {
                    y: -20,
                    opacity: 0,
                    duration: 0.3,
                    ease: "power2.in",
                    onComplete: function() {
                        dropdown.classList.add('hidden');
                        gsap.set(dropdown, {y: 0, opacity: 1});
                    }
                });
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            // User menu dropdown
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');
            if (userMenuButton && userMenuDropdown && !userMenuButton.contains(event.target) && !userMenuDropdown.contains(event.target)) {
                if (!userMenuDropdown.classList.contains('hidden')) {
                    gsap.to(userMenuDropdown, {
                        y: -20,
                        opacity: 0,
                        duration: 0.3,
                        ease: "power2.in",
                        onComplete: function() {
                            userMenuDropdown.classList.add('hidden');
                            gsap.set(userMenuDropdown, {y: 0, opacity: 1});
                        }
                    });
                }
            }

            // Notifications dropdown
            const notificationsToggle = document.getElementById('notifications-toggle');
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            if (notificationsToggle && notificationsDropdown && !notificationsToggle.contains(event.target) && !notificationsDropdown.contains(event.target)) {
                if (!notificationsDropdown.classList.contains('hidden')) {
                    gsap.to(notificationsDropdown, {
                        y: -20,
                        opacity: 0,
                        duration: 0.3,
                        ease: "power2.in",
                        onComplete: function() {
                            notificationsDropdown.classList.add('hidden');
                            gsap.set(notificationsDropdown, {y: 0, opacity: 1});
                        }
                    });
                }
            }
        });

        // File Upload Enhancements
        if (document.getElementById('drop-area')) {
            const dropArea = document.getElementById('drop-area');
            const fileInput = document.getElementById('document_file');
            const filePreview = document.getElementById('file-preview');
            const fileName = document.getElementById('file-name');
            const uploadProgress = document.getElementById('upload-progress');
            
            // Highlight drop area when file is dragged over
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    gsap.to(dropArea, {
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: '#3b82f6',
                        duration: 0.3
                    });
                }, false);
            });
            
            // Remove highlight when file is dragged out or dropped
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    gsap.to(dropArea, {
                        backgroundColor: 'transparent',
                        borderColor: '#d1d5db',
                        duration: 0.3
                    });
                }, false);
            });
            
            // Handle file drop
            dropArea.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files && files.length) {
                    fileInput.files = files;
                    updateFilePreview(files[0]);
                }
            }, false);
            
            // Handle file selection
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length) {
                    updateFilePreview(this.files[0]);
                }
            });
            
            // Update file preview with animation
            function updateFilePreview(file) {
                if (fileName) {
                    fileName.textContent = file.name;
                    
                    // Show file preview with animation
                    if (filePreview.classList.contains('hidden')) {
                        filePreview.classList.remove('hidden');
                        gsap.from(filePreview, {
                            y: 20,
                            opacity: 0,
                            duration: 0.5,
                            ease: "power2.out"
                        });
                    }
                    
                    // Determine file type icon
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    let fileIcon = '';
                    
                    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                        fileIcon = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>';
                    } else if (['pdf'].includes(fileExtension)) {
                        fileIcon = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>';
                    } else if (['doc', 'docx'].includes(fileExtension)) {
                        fileIcon = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
                    } else {
                        fileIcon = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
                    }
                    
                    // Add file icon
                    const fileNameElement = document.getElementById('file-name');
                    if (fileNameElement) {
                        fileNameElement.innerHTML = fileIcon + file.name;
                    }
                    
                    // Animate progress bar for visual feedback
                    if (uploadProgress) {
                        gsap.to(uploadProgress, {
                            width: '100%',
                            duration: 1.5,
                            ease: "power2.inOut"
                        });
                    }
                }
            }
        }

        // 3D Animation for Sidebar
        document.addEventListener('DOMContentLoaded', function() {
            // Create scene
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, 200 / 200, 0.1, 1000);
            
            const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
            renderer.setSize(200, 200);
            
            const sidebarAnimation = document.getElementById('sidebar-animation');
            if (sidebarAnimation) {
                sidebarAnimation.appendChild(renderer.domElement);
                
                // Create geometry - Indian flag-inspired animation
                const geometry = new THREE.SphereGeometry(3, 32, 32);
                const material1 = new THREE.MeshBasicMaterial({ color: 0xFF9933, wireframe: true }); // Saffron
                const material2 = new THREE.MeshBasicMaterial({ color: 0xFFFFFF, wireframe: true }); // White
                const material3 = new THREE.MeshBasicMaterial({ color: 0x138808, wireframe: true }); // Green
                
                const sphere1 = new THREE.Mesh(geometry, material1);
                sphere1.position.y = 2;
                sphere1.scale.set(0.7, 0.7, 0.7);
                scene.add(sphere1);
                
                const sphere2 = new THREE.Mesh(geometry, material2);
                scene.add(sphere2);
                
                const sphere3 = new THREE.Mesh(geometry, material3);
                sphere3.position.y = -2;
                sphere3.scale.set(0.7, 0.7, 0.7);
                scene.add(sphere3);
                
                // Add Ashoka Chakra (simplified as a circle)
                const chakraGeometry = new THREE.TorusGeometry(1, 0.2, 16, 50);
                const chakraMaterial = new THREE.MeshBasicMaterial({ color: 0x000080, wireframe: true });
                const chakra = new THREE.Mesh(chakraGeometry, chakraMaterial);
                scene.add(chakra);
                
                camera.position.z = 10;
                
                // Animation with GSAP integration
                gsap.to(sphere1.rotation, {
                    y: Math.PI * 2,
                    duration: 8,
                    repeat: -1,
                    ease: "none"
                });
                
                gsap.to(sphere2.rotation, {
                    y: -Math.PI * 2,
                    duration: 10,
                    repeat: -1,
                    ease: "none"
                });
                
                gsap.to(sphere3.rotation, {
                    y: Math.PI * 2,
                    duration: 12,
                    repeat: -1,
                    ease: "none"
                });
                
                gsap.to(chakra.rotation, {
                    z: Math.PI * 2,
                    duration: 6,
                    repeat: -1,
                    ease: "none"
                });
                
                // Animation
                function animate() {
                    requestAnimationFrame(animate);
                    renderer.render(scene, camera);
                }
                
                animate();
            }
        });
    </script>
</body>
</html>
