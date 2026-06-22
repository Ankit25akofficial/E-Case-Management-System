/**
 * Client-side optimizations for E-Case Management System
 * Enhances performance and user experience
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize optimizations
    initializeOptimizations();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load dynamic content
    loadDynamicContent();
});

/**
 * Initialize all optimizations
 */
function initializeOptimizations() {
    // Initialize AOS animations with optimized settings
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            disable: 'mobile' // Disable on mobile for better performance
        });
    }
    
    // Initialize GSAP animations
    if (typeof gsap !== 'undefined') {
        // Register ScrollTrigger plugin if available
        if (typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);
        }
        
        // Optimize GSAP for better performance
        gsap.config({
            autoSleep: 60,
            force3D: "auto",
            nullTargetWarn: false
        });
    }
    
    // Optimize images
    optimizeImages();
    
    // Preconnect to external domains
    preconnectToDomains();
    
    // Initialize offline support
    initOfflineSupport();
}

/**
 * Setup event listeners for better performance
 */
function setupEventListeners() {
    // Use event delegation for better performance
    document.addEventListener('click', function(e) {
        // Handle sidebar links
        if (e.target.closest('.sidebar-link')) {
            const link = e.target.closest('.sidebar-link');
            highlightActiveLink(link);
        }
        
        // Handle tab navigation
        if (e.target.closest('[data-tab]')) {
            const tab = e.target.closest('[data-tab]');
            const tabId = tab.getAttribute('data-tab');
            switchTab(tabId);
            e.preventDefault();
        }
    });
    
    // Optimize form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                // Prevent double submissions
                if (submitButton.getAttribute('data-submitting') === 'true') {
                    e.preventDefault();
                    return;
                }
                
                submitButton.setAttribute('data-submitting', 'true');
                submitButton.innerHTML = '<span class="spinner"></span> Processing...';
                
                // Reset button after 30 seconds (failsafe)
                setTimeout(() => {
                    submitButton.removeAttribute('data-submitting');
                    submitButton.innerHTML = submitButton.getAttribute('data-original-text') || 'Submit';
                }, 30000);
                
                // Store original text
                if (!submitButton.getAttribute('data-original-text')) {
                    submitButton.setAttribute('data-original-text', submitButton.textContent);
                }
            }
        });
    });
    
    // Optimize scroll events with throttling
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (!scrollTimeout) {
            scrollTimeout = setTimeout(function() {
                scrollTimeout = null;
                handleScroll();
            }, 100); // Throttle to once every 100ms
        }
    });
}

/**
 * Handle scroll events
 */
function handleScroll() {
    // Add scroll-based optimizations here
    const scrollPosition = window.scrollY;
    
    // Show/hide back-to-top button
    const backToTopButton = document.getElementById('back-to-top');
    if (backToTopButton) {
        if (scrollPosition > 300) {
            backToTopButton.classList.remove('hidden');
        } else {
            backToTopButton.classList.add('hidden');
        }
    }
    
    // Implement sticky header
    const header = document.querySelector('nav');
    if (header) {
        if (scrollPosition > 100) {
            header.classList.add('sticky', 'top-0', 'z-50', 'shadow-md');
        } else {
            header.classList.remove('sticky', 'top-0', 'z-50', 'shadow-md');
        }
    }
}

/**
 * Optimize images on the page
 */
function optimizeImages() {
    // Lazy load images that aren't already using loading="lazy"
    const images = document.querySelectorAll('img:not([loading])');
    images.forEach(img => {
        img.setAttribute('loading', 'lazy');
    });
    
    // Add decoding="async" to images
    document.querySelectorAll('img').forEach(img => {
        img.setAttribute('decoding', 'async');
    });
    
    // Convert images to WebP format if supported
    if (supportsWebP()) {
        document.querySelectorAll('img[src$=".jpg"], img[src$=".jpeg"], img[src$=".png"]').forEach(img => {
            const src = img.getAttribute('src');
            if (!src.includes('.webp')) {
                // Create WebP version path (this assumes WebP versions exist)
                const webpSrc = src.replace(/\.(jpg|jpeg|png)$/i, '.webp');
                img.setAttribute('src', webpSrc);
            }
        });
    }
}

/**
 * Check if browser supports WebP format
 */
function supportsWebP() {
    const elem = document.createElement('canvas');
    if (elem.getContext && elem.getContext('2d')) {
        return elem.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    }
    return false;
}

/**
 * Preconnect to external domains for faster loading
 */
function preconnectToDomains() {
    const domains = [
        'https://cdn.jsdelivr.net',
        'https://cdnjs.cloudflare.com'
    ];
    
    domains.forEach(domain => {
        const link = document.createElement('link');
        link.rel = 'preconnect';
        link.href = domain;
        link.crossOrigin = 'anonymous';
        document.head.appendChild(link);
    });
}

/**
 * Initialize offline support
 */
function initOfflineSupport() {
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();
    
    // Cache important resources if browser supports it
    if ('caches' in window) {
        const resourcesToCache = [
            '/assets/css/style.css',
            '/assets/js/main.js',
            '/assets/images/emblem.png',
            '/assets/images/avatar.png'
        ];
        
        caches.open('ecms-static-v1').then(cache => {
            cache.addAll(resourcesToCache);
        });
    }
}

/**
 * Update online status and show appropriate notification
 */
function updateOnlineStatus() {
    const isOnline = navigator.onLine;
    const statusBar = document.getElementById('connection-status');
    
    if (!statusBar) {
        // Create status bar if it doesn't exist
        const newStatusBar = document.createElement('div');
        newStatusBar.id = 'connection-status';
        newStatusBar.style.position = 'fixed';
        newStatusBar.style.bottom = '0';
        newStatusBar.style.left = '0';
        newStatusBar.style.right = '0';
        newStatusBar.style.padding = '10px';
        newStatusBar.style.textAlign = 'center';
        newStatusBar.style.zIndex = '9999';
        newStatusBar.style.transition = 'transform 0.3s ease-in-out';
        document.body.appendChild(newStatusBar);
    }
    
    const bar = document.getElementById('connection-status');
    
    if (!isOnline) {
        bar.textContent = 'You are offline. Some features may be limited.';
        bar.style.backgroundColor = '#fff3cd';
        bar.style.color = '#856404';
        bar.style.borderTop = '1px solid #ffeeba';
        bar.style.transform = 'translateY(0)';
        
        // Enable offline mode
        enableOfflineMode();
    } else {
        bar.textContent = 'You are back online!';
        bar.style.backgroundColor = '#d4edda';
        bar.style.color = '#155724';
        bar.style.borderTop = '1px solid #c3e6cb';
        
        // Show for 3 seconds then hide
        setTimeout(() => {
            bar.style.transform = 'translateY(100%)';
        }, 3000);
        
        // Sync any pending data
        syncPendingData();
    }
}

/**
 * Enable offline mode
 */
function enableOfflineMode() {
    // Disable forms and show offline message
    document.querySelectorAll('form').forEach(form => {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            
            // Add offline message if not already present
            if (!form.querySelector('.offline-message')) {
                const message = document.createElement('div');
                message.className = 'offline-message bg-yellow-100 text-yellow-800 p-2 rounded mt-2 text-sm';
                message.textContent = 'This feature is not available while offline.';
                form.appendChild(message);
            }
        }
    });
}

/**
 * Sync pending data when back online
 */
function syncPendingData() {
    // Get pending data from localStorage
    const pendingData = localStorage.getItem('pendingData');
    
    if (pendingData) {
        try {
            const data = JSON.parse(pendingData);
            
            // Process each pending item
            data.forEach(item => {
                // Send data to server
                fetch(item.url, {
                    method: item.method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(item.data)
                })
                .then(response => response.json())
                .then(result => {
                    console.log('Synced:', result);
                })
                .catch(error => {
                    console.error('Sync error:', error);
                });
            });
            
            // Clear pending data
            localStorage.removeItem('pendingData');
            
            // Re-enable forms
            document.querySelectorAll('form').forEach(form => {
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = false;
                }
                
                // Remove offline messages
                form.querySelectorAll('.offline-message').forEach(msg => {
                    msg.remove();
                });
            });
        } catch (error) {
            console.error('Error parsing pending data:', error);
        }
    }
}

/**
 * Load dynamic content with optimized loading
 */
function loadDynamicContent() {
    // Load notifications
    loadNotifications();
    
    // Load case statistics
    loadCaseStatistics();
}

/**
 * Load notifications with optimized loading
 */
function loadNotifications() {
    const container = document.getElementById('notification-container');
    if (!container) return;
    
    // Check if we have cached notifications
    const cachedNotifications = sessionStorage.getItem('notifications');
    if (cachedNotifications) {
        container.innerHTML = cachedNotifications;
        return;
    }
    
    // Fetch notifications
    fetch('api/notifications.php')
        .then(response => response.json())
        .then(data => {
            let html = '';
            
            if (data.length === 0) {
                html = '<p class="px-4 py-2 text-gray-500 text-center">No notifications</p>';
            } else {
                data.forEach(notification => {
                    html += `
                        <a href="${notification.link}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-100 transition-all duration-200 hover:pl-6">
                            <p class="font-medium">${notification.title}</p>
                            <p class="text-xs text-gray-500">${notification.message}</p>
                            <p class="text-xs text-gray-400">${notification.time_ago}</p>
                        </a>
                    `;
                });
            }
            
            // Update container
            container.innerHTML = html;
            
            // Cache for 5 minutes
            sessionStorage.setItem('notifications', html);
            setTimeout(() => {
                sessionStorage.removeItem('notifications');
            }, 300000); // 5 minutes
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            container.innerHTML = '<p class="px-4 py-2 text-red-500 text-center">Error loading notifications</p>';
        });
}

/**
 * Load case statistics with optimized loading
 */
function loadCaseStatistics() {
    const container = document.getElementById('case-statistics');
    if (!container) return;
    
    // Check if we have cached statistics
    const cachedStats = sessionStorage.getItem('case-statistics');
    if (cachedStats) {
        const data = JSON.parse(cachedStats);
        renderCaseStatistics(data);
        return;
    }
    
    // Fetch statistics
    fetch('api/case-statistics.php')
        .then(response => response.json())
        .then(data => {
            renderCaseStatistics(data);
            
            // Cache for 15 minutes
            sessionStorage.setItem('case-statistics', JSON.stringify(data));
            setTimeout(() => {
                sessionStorage.removeItem('case-statistics');
            }, 900000); // 15 minutes
        })
        .catch(error => {
            console.error('Error loading case statistics:', error);
            container.innerHTML = '<p class="text-red-500 text-center">Error loading statistics</p>';
        });
}

/**
 * Render case statistics
 */
function renderCaseStatistics(data) {
    const container = document.getElementById('case-statistics');
    if (!container) return;
    
    // Find or create canvas
    let canvas = container.querySelector('canvas');
    if (!canvas) {
        canvas = document.createElement('canvas');
        container.appendChild(canvas);
    }
    
    // Create chart if Chart.js is available
    if (typeof Chart !== 'undefined') {
        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: [
                        '#4299e1', // blue
                        '#48bb78', // green
                        '#ecc94b', // yellow
                        '#ed8936', // orange
                        '#9f7aea'  // purple
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    }
}

/**
 * Highlight active sidebar link
 */
function highlightActiveLink(link) {
    // Remove active class from all links
    document.querySelectorAll('.sidebar-link').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to clicked link
    link.classList.add('active');
}

/**
 * Switch between tabs
 */
function switchTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('[data-tab-content]').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Show selected tab content
    const selectedContent = document.querySelector(`[data-tab-content="${tabId}"]`);
    if (selectedContent) {
        selectedContent.classList.remove('hidden');
    }
    
    // Update active tab
    document.querySelectorAll('[data-tab]').forEach(tab => {
        tab.classList.remove('bg-blue-500', 'text-white');
        tab.classList.add('bg-gray-200', 'text-gray-700');
    });
    
    const selectedTab = document.querySelector(`[data-tab="${tabId}"]`);
    if (selectedTab) {
        selectedTab.classList.remove('bg-gray-200', 'text-gray-700');
        selectedTab.classList.add('bg-blue-500', 'text-white');
    }
}
