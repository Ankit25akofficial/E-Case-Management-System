/**
 * E-Case Management System Performance Optimization
 * 
 * This file contains JavaScript functions to optimize front-end performance
 * including lazy loading, resource prefetching, and other optimizations.
 */

// Lazy loading images
document.addEventListener('DOMContentLoaded', function() {
    // Lazy load images
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const image = entry.target;
                    image.src = image.dataset.src;
                    image.removeAttribute('data-src');
                    imageObserver.unobserve(image);
                }
            });
        });
        
        lazyImages.forEach(function(image) {
            imageObserver.observe(image);
        });
    } else {
        // Fallback for browsers that don't support IntersectionObserver
        let lazyLoadThrottleTimeout;
        
        function lazyLoad() {
            if (lazyLoadThrottleTimeout) {
                clearTimeout(lazyLoadThrottleTimeout);
            }
            
            lazyLoadThrottleTimeout = setTimeout(function() {
                const scrollTop = window.pageYOffset;
                
                lazyImages.forEach(function(img) {
                    if (img.offsetTop < (window.innerHeight + scrollTop)) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                });
                
                if (lazyImages.length == 0) {
                    document.removeEventListener('scroll', lazyLoad);
                    window.removeEventListener('resize', lazyLoad);
                    window.removeEventListener('orientationChange', lazyLoad);
                }
            }, 20);
        }
        
        document.addEventListener('scroll', lazyLoad);
        window.addEventListener('resize', lazyLoad);
        window.addEventListener('orientationChange', lazyLoad);
    }
    
    // Prefetch pages on hover
    const links = document.querySelectorAll('a');
    
    links.forEach(link => {
        link.addEventListener('mouseenter', function() {
            const href = this.getAttribute('href');
            
            // Only prefetch internal links
            if (href && href.startsWith('/') && !link.prefetched) {
                const prefetchLink = document.createElement('link');
                prefetchLink.rel = 'prefetch';
                prefetchLink.href = href;
                document.head.appendChild(prefetchLink);
                link.prefetched = true;
            }
        });
    });
    
    // Optimize form submissions with debounce
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', debounce(function(e) {
            // Add loading indicators or other UI feedback here
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner"></span> Processing...';
            }
        }, 300));
    });
    
    // Initialize performance monitoring
    initPerformanceMonitoring();
});

// Debounce function to limit how often a function can be called
function debounce(func, wait, immediate) {
    let timeout;
    
    return function() {
        const context = this, args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        
        if (callNow) func.apply(context, args);
    };
}

// Throttle function to limit how often a function can be called
function throttle(func, limit) {
    let inThrottle;
    
    return function() {
        const args = arguments;
        const context = this;
        
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Performance monitoring
function initPerformanceMonitoring() {
    if ('performance' in window && 'PerformanceObserver' in window) {
        // Create performance observer
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                // Log performance metrics
                console.log(`${entry.name}: ${entry.startTime.toFixed(0)}ms`);
            }
        });
        
        // Observe paint timing
        observer.observe({ entryTypes: ['paint'] });
        
        // Log navigation timing metrics
        window.addEventListener('load', () => {
            setTimeout(() => {
                const timing = performance.timing;
                const interactive = timing.domInteractive - timing.navigationStart;
                const dcl = timing.domContentLoadedEventEnd - timing.navigationStart;
                const complete = timing.domComplete - timing.navigationStart;
                
                console.log('Interactive: ' + interactive + 'ms');
                console.log('DOMContentLoaded: ' + dcl + 'ms');
                console.log('Complete: ' + complete + 'ms');
                
                // Send metrics to server if needed
                // sendMetricsToServer({ interactive, dcl, complete });
            }, 0);
        });
    }
}

// Dynamic resource loading
function loadResourceDynamically(url, type = 'script') {
    return new Promise((resolve, reject) => {
        let element;
        
        if (type === 'script') {
            element = document.createElement('script');
            element.src = url;
            element.async = true;
        } else if (type === 'style') {
            element = document.createElement('link');
            element.href = url;
            element.rel = 'stylesheet';
        }
        
        element.onload = () => resolve();
        element.onerror = () => reject(new Error(`Failed to load ${url}`));
        
        document.head.appendChild(element);
    });
}

// Cache API for caching resources
function cacheResources(resources) {
    if ('caches' in window) {
        caches.open('ecms-static-v1').then(cache => {
            cache.addAll(resources).then(() => {
                console.log('Resources cached successfully');
            });
        });
    }
}

// Initialize caching for important resources
const resourcesToPrecache = [
    '/assets/css/style.css',
    '/assets/js/main.js',
    '/assets/images/emblem.png'
];

cacheResources(resourcesToPrecache);

// Add event listener for online/offline status
window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);

function updateOnlineStatus() {
    const status = navigator.onLine ? 'online' : 'offline';
    
    if (status === 'offline') {
        // Show offline notification
        showNotification('You are currently offline. Some features may be limited.');
    } else {
        // Show online notification
        showNotification('You are back online!', 'success');
        
        // Sync any pending data
        syncPendingData();
    }
}

// Show notification
function showNotification(message, type = 'warning') {
    // Create notification element if it doesn't exist
    let notification = document.getElementById('system-notification');
    
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'system-notification';
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.padding = '10px 20px';
        notification.style.borderRadius = '4px';
        notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
        notification.style.transition = 'all 0.3s ease';
        document.body.appendChild(notification);
    }
    
    // Set notification style based on type
    if (type === 'success') {
        notification.style.backgroundColor = '#d4edda';
        notification.style.color = '#155724';
        notification.style.border = '1px solid #c3e6cb';
    } else if (type === 'warning') {
        notification.style.backgroundColor = '#fff3cd';
        notification.style.color = '#856404';
        notification.style.border = '1px solid #ffeeba';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#f8d7da';
        notification.style.color = '#721c24';
        notification.style.border = '1px solid #f5c6cb';
    }
    
    // Set notification message
    notification.textContent = message;
    
    // Show notification
    notification.style.opacity = '1';
    
    // Hide notification after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

// Sync pending data when back online
function syncPendingData() {
    // Check for pending data in localStorage
    const pendingData = localStorage.getItem('pendingData');
    
    if (pendingData) {
        try {
            const data = JSON.parse(pendingData);
            
            // Send pending data to server
            fetch('/api/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Clear pending data
                    localStorage.removeItem('pendingData');
                    console.log('Pending data synced successfully');
                }
            })
            .catch(error => {
                console.error('Error syncing pending data:', error);
            });
        } catch (error) {
            console.error('Error parsing pending data:', error);
        }
    }
}

// Save data locally when offline
function saveDataLocally(data) {
    if (!navigator.onLine) {
        // Store data locally
        let pendingData = localStorage.getItem('pendingData');
        
        if (pendingData) {
            try {
                pendingData = JSON.parse(pendingData);
                pendingData.push(data);
            } catch (error) {
                pendingData = [data];
            }
        } else {
            pendingData = [data];
        }
        
        localStorage.setItem('pendingData', JSON.stringify(pendingData));
        showNotification('Data saved locally. It will be synced when you are back online.', 'warning');
        
        return true;
    }
    
    return false;
}

// Initialize performance optimizations
document.addEventListener('DOMContentLoaded', function() {
    // Add loading attribute to iframes
    const iframes = document.querySelectorAll('iframe');
    iframes.forEach(iframe => {
        iframe.setAttribute('loading', 'lazy');
    });
    
    // Add fetchpriority attribute to critical images
    const criticalImages = document.querySelectorAll('.hero-image, .logo');
    criticalImages.forEach(img => {
        img.setAttribute('fetchpriority', 'high');
    });
});
