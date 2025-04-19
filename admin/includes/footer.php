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
                    <a href="../help.php" class="text-sm text-blue-600 hover:underline">Help & Support</a>
                    <a href="../privacy.php" class="text-sm text-blue-600 hover:underline">Privacy Policy</a>
                    <a href="../terms.php" class="text-sm text-blue-600 hover:underline">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile Sidebar Toggle
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
        });

        // User Menu Dropdown
        document.getElementById('user-menu-button').addEventListener('click', function() {
            document.getElementById('user-menu-dropdown').classList.toggle('hidden');
        });

        // Notifications Dropdown
        document.getElementById('notifications-toggle').addEventListener('click', function() {
            document.getElementById('notifications-dropdown').classList.toggle('hidden');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            // User menu dropdown
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');
            if (userMenuButton && userMenuDropdown && !userMenuButton.contains(event.target) && !userMenuDropdown.contains(event.target)) {
                userMenuDropdown.classList.add('hidden');
            }

            // Notifications dropdown
            const notificationsToggle = document.getElementById('notifications-toggle');
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            if (notificationsToggle && notificationsDropdown && !notificationsToggle.contains(event.target) && !notificationsDropdown.contains(event.target)) {
                notificationsDropdown.classList.add('hidden');
            }
        });

        // 3D Animation for Sidebar
        document.addEventListener('DOMContentLoaded', function() {
            // Create scene
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, 200 / 200, 0.1, 1000);
            
            const renderer = new THREE.WebGLRenderer({ alpha: true });
            renderer.setSize(200, 200);
            document.getElementById('sidebar-animation').appendChild(renderer.domElement);
            
            // Create geometry - Indian flag-inspired animation
            const geometry = new THREE.TorusGeometry(3, 1, 16, 50);
            const material1 = new THREE.MeshBasicMaterial({ color: 0xFF9933, wireframe: true }); // Saffron
            const material2 = new THREE.MeshBasicMaterial({ color: 0xFFFFFF, wireframe: true }); // White
            const material3 = new THREE.MeshBasicMaterial({ color: 0x138808, wireframe: true }); // Green
            
            const torus1 = new THREE.Mesh(geometry, material1);
            torus1.rotation.x = Math.PI / 2;
            torus1.position.y = 2;
            scene.add(torus1);
            
            const torus2 = new THREE.Mesh(geometry, material2);
            torus2.rotation.x = Math.PI / 2;
            scene.add(torus2);
            
            const torus3 = new THREE.Mesh(geometry, material3);
            torus3.rotation.x = Math.PI / 2;
            torus3.position.y = -2;
            scene.add(torus3);
            
            // Add Ashoka Chakra (simplified as a circle)
            const chakraGeometry = new THREE.RingGeometry(1.5, 1.8, 24);
            const chakraMaterial = new THREE.MeshBasicMaterial({ color: 0x000080, wireframe: true });
            const chakra = new THREE.Mesh(chakraGeometry, chakraMaterial);
            scene.add(chakra);
            
            camera.position.z = 10;
            
            // Animation
            function animate() {
                requestAnimationFrame(animate);
                
                torus1.rotation.z += 0.01;
                torus2.rotation.z -= 0.01;
                torus3.rotation.z += 0.01;
                chakra.rotation.z += 0.02;
                
                renderer.render(scene, camera);
            }
            
            animate();
        });
    </script>
</body>
</html>
