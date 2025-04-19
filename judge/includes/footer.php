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
                    <a href="../help.php" class="text-sm text-yellow-600 hover:underline">made by Ankit ❤️ </a>
                    <a href="../privacy.php" class="text-sm text-yellow-600 hover:underline">Privacy Policy</a>
                    <a href="../terms.php" class="text-sm text-yellow-600 hover:underline">Terms of Service</a>
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
            
            // Create geometry - Scales of Justice
            const baseGeometry = new THREE.CylinderGeometry(0.5, 0.5, 8, 32);
            const baseMaterial = new THREE.MeshBasicMaterial({ color: 0xf59e0b, wireframe: true });
            const base = new THREE.Mesh(baseGeometry, baseMaterial);
            base.rotation.z = Math.PI / 2;
            scene.add(base);
            
            // Create the scales
            const dishGeometry = new THREE.CylinderGeometry(2, 2, 0.2, 32);
            const dishMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff, wireframe: true });
            
            const leftDish = new THREE.Mesh(dishGeometry, dishMaterial);
            leftDish.position.set(-4, 0, 0);
            scene.add(leftDish);
            
            const rightDish = new THREE.Mesh(dishGeometry, dishMaterial);
            rightDish.position.set(4, 0, 0);
            scene.add(rightDish);
            
            // Create chains (simplified as lines)
            const chainMaterial = new THREE.LineBasicMaterial({ color: 0xf59e0b });
            
            const leftChainGeometry = new THREE.BufferGeometry().setFromPoints([
                new THREE.Vector3(0, 0, 0),
                new THREE.Vector3(-4, 0, 0)
            ]);
            const leftChain = new THREE.Line(leftChainGeometry, chainMaterial);
            scene.add(leftChain);
            
            const rightChainGeometry = new THREE.BufferGeometry().setFromPoints([
                new THREE.Vector3(0, 0, 0),
                new THREE.Vector3(4, 0, 0)
            ]);
            const rightChain = new THREE.Line(rightChainGeometry, chainMaterial);
            scene.add(rightChain);
            
            // Add top sphere
            const topSphereGeometry = new THREE.SphereGeometry(0.8, 32, 32);
            const topSphereMaterial = new THREE.MeshBasicMaterial({ color: 0xf59e0b, wireframe: true });
            const topSphere = new THREE.Mesh(topSphereGeometry, topSphereMaterial);
            topSphere.position.set(0, 4, 0);
            scene.add(topSphere);
            
            camera.position.z = 15;
            
            // Animation
            let time = 0;
            function animate() {
                requestAnimationFrame(animate);
                
                time += 0.01;
                
                // Make the scales balance back and forth
                leftDish.position.y = Math.sin(time) * 0.5;
                rightDish.position.y = -Math.sin(time) * 0.5;
                
                // Update chain positions
                leftChain.geometry.dispose();
                leftChain.geometry = new THREE.BufferGeometry().setFromPoints([
                    new THREE.Vector3(0, 0, 0),
                    new THREE.Vector3(-4, leftDish.position.y, 0)
                ]);
                
                rightChain.geometry.dispose();
                rightChain.geometry = new THREE.BufferGeometry().setFromPoints([
                    new THREE.Vector3(0, 0, 0),
                    new THREE.Vector3(4, rightDish.position.y, 0)
                ]);
                
                // Rotate the entire scene slightly
                scene.rotation.y = Math.sin(time * 0.5) * 0.2;
                
                renderer.render(scene, camera);
            }
            
            animate();
        });
    </script>
</body>
</html>
