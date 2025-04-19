/**
 * E-Case Management System Service Worker
 * Provides offline functionality and performance optimizations
 */

// Cache version - update this when resources change
const CACHE_VERSION = 'v1';
const CACHE_NAME = `ecms-cache-${CACHE_VERSION}`;

// Resources to cache on install
const STATIC_RESOURCES = [
  '/',
  '/index.php',
  '/login.php',
  '/assets/css/style.css',
  '/assets/js/main.js',
  '/assets/js/performance.js',
  '/assets/js/client-optimizations.js',
  '/assets/images/emblem.png',
  '/assets/images/avatar.png',
  'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css',
  'https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js',
  'https://cdn.jsdelivr.net/npm/gsap@3.9.1/dist/gsap.min.js',
  'https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js',
  'https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css',
  'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'
];

// Install event - cache static resources
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Caching static resources');
        return cache.addAll(STATIC_RESOURCES);
      })
      .then(() => {
        // Skip waiting to activate immediately
        return self.skipWaiting();
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.filter(cacheName => {
            return cacheName.startsWith('ecms-cache-') && cacheName !== CACHE_NAME;
          }).map(cacheName => {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          })
        );
      })
      .then(() => {
        // Claim clients so the service worker is in control immediately
        return self.clients.claim();
      })
  );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', event => {
  // Skip non-GET requests and browser extension requests
  if (event.request.method !== 'GET' || event.request.url.startsWith('chrome-extension://')) {
    return;
  }

  // Skip API requests (they should be handled differently)
  if (event.request.url.includes('/api/')) {
    return networkFirst(event);
  }

  // For HTML pages - use network first strategy
  if (event.request.headers.get('Accept').includes('text/html')) {
    return networkFirst(event);
  }

  // For other resources - use cache first strategy
  event.respondWith(
    cacheFirst(event.request)
  );
});

/**
 * Cache-first strategy
 * Try to serve from cache, fall back to network
 */
function cacheFirst(request) {
  return caches.match(request)
    .then(cachedResponse => {
      if (cachedResponse) {
        // Return cached response and update cache in background
        updateCache(request);
        return cachedResponse;
      }
      
      // Not in cache, get from network
      return fetchAndCache(request);
    })
    .catch(error => {
      console.error('Cache first strategy failed:', error);
      // Return offline fallback for images
      if (request.url.match(/\.(jpg|jpeg|png|gif|svg)$/)) {
        return caches.match('/assets/images/offline.png');
      }
      
      // For other resources, just propagate the error
      throw error;
    });
}

/**
 * Network-first strategy
 * Try to serve from network, fall back to cache
 */
function networkFirst(event) {
  event.respondWith(
    fetch(event.request)
      .then(networkResponse => {
        // Clone the response to store in cache
        const clonedResponse = networkResponse.clone();
        
        // Update the cache with the new response
        caches.open(CACHE_NAME)
          .then(cache => {
            cache.put(event.request, clonedResponse);
          });
          
        return networkResponse;
      })
      .catch(() => {
        // Network failed, try to serve from cache
        return caches.match(event.request)
          .then(cachedResponse => {
            if (cachedResponse) {
              return cachedResponse;
            }
            
            // If it's a navigation request, return the offline page
            if (event.request.mode === 'navigate') {
              return caches.match('/offline.html');
            }
            
            // Otherwise, propagate the error
            throw new Error('No cached response available');
          });
      })
  );
}

/**
 * Fetch and cache a request
 */
function fetchAndCache(request) {
  return fetch(request)
    .then(response => {
      // Check if we received a valid response
      if (!response || response.status !== 200 || response.type !== 'basic') {
        return response;
      }
      
      // Clone the response to store in cache
      const clonedResponse = response.clone();
      
      caches.open(CACHE_NAME)
        .then(cache => {
          cache.put(request, clonedResponse);
        });
        
      return response;
    });
}

/**
 * Update cache in background
 */
function updateCache(request) {
  // Only update cache for certain resources
  if (!request.url.match(/\.(css|js|png|jpg|jpeg|gif|svg)$/)) {
    return;
  }
  
  // Fetch and update cache in background
  fetch(request)
    .then(response => {
      if (!response || response.status !== 200 || response.type !== 'basic') {
        return;
      }
      
      caches.open(CACHE_NAME)
        .then(cache => {
          cache.put(request, response);
        });
    })
    .catch(error => {
      console.error('Background cache update failed:', error);
    });
}

// Handle push notifications
self.addEventListener('push', event => {
  if (!event.data) {
    return;
  }
  
  const data = event.data.json();
  
  const options = {
    body: data.body,
    icon: '/assets/images/notification-icon.png',
    badge: '/assets/images/badge-icon.png',
    data: {
      url: data.url
    },
    actions: [
      {
        action: 'view',
        title: 'View'
      },
      {
        action: 'close',
        title: 'Close'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Handle notification click
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  if (event.action === 'close') {
    return;
  }
  
  // Open the URL from the notification data
  const urlToOpen = event.notification.data.url || '/';
  
  event.waitUntil(
    clients.matchAll({
      type: 'window',
      includeUncontrolled: true
    })
    .then(windowClients => {
      // Check if there is already a window/tab open with the URL
      const matchingClient = windowClients.find(client => {
        return new URL(client.url).pathname === new URL(urlToOpen, self.location.href).pathname;
      });
      
      if (matchingClient) {
        // If so, focus it
        return matchingClient.focus();
      }
      
      // Otherwise, open a new window/tab
      return clients.openWindow(urlToOpen);
    })
  );
});

// Handle background sync
self.addEventListener('sync', event => {
  if (event.tag === 'sync-pending-data') {
    event.waitUntil(syncPendingData());
  }
});

/**
 * Sync pending data from IndexedDB
 */
function syncPendingData() {
  return new Promise((resolve, reject) => {
    // This would typically use IndexedDB to store and retrieve pending data
    // For simplicity, we're using a placeholder implementation
    console.log('Background sync triggered');
    
    // Simulated success
    resolve();
  });
}

// Create offline fallback page
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.put('/offline.html', new Response(`
          <!DOCTYPE html>
          <html lang="en">
          <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Offline - E-Case Management System</title>
            <style>
              body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                background-color: #f7fafc;
                color: #4a5568;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
                text-align: center;
              }
              .container {
                max-width: 500px;
                padding: 40px;
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
              }
              h1 {
                color: #2b6cb0;
                margin-bottom: 16px;
              }
              p {
                margin-bottom: 24px;
                line-height: 1.5;
              }
              .icon {
                font-size: 64px;
                margin-bottom: 24px;
              }
              .button {
                display: inline-block;
                background-color: #2b6cb0;
                color: white;
                padding: 12px 24px;
                border-radius: 4px;
                text-decoration: none;
                font-weight: bold;
                transition: background-color 0.3s;
              }
              .button:hover {
                background-color: #2c5282;
              }
            </style>
          </head>
          <body>
            <div class="container">
              <div class="icon">📶</div>
              <h1>You're Offline</h1>
              <p>It seems you're not connected to the internet. Please check your connection and try again.</p>
              <p>Some features of the E-Case Management System are available offline.</p>
              <a href="/" class="button">Try Again</a>
            </div>
            <script>
              // Check when the user comes back online
              window.addEventListener('online', () => {
                window.location.reload();
              });
              
              // Add retry button functionality
              document.querySelector('.button').addEventListener('click', (e) => {
                e.preventDefault();
                window.location.reload();
              });
            </script>
          </body>
          </html>
        `, {
          headers: {
            'Content-Type': 'text/html'
          }
        }));
      })
  );
});
