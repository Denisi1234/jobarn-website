// Jobarn Storefront Service Worker - High Performance Cache & Adaptive 2G/3G Preloader
const CACHE_NAME = 'jobarn-media-v6';

// 1. Critical lightweight assets (HTML, CSS, JS, logo, key products, tech workspace assets - ~200KB total)
// Downloaded immediately even on slow 2G/3G connections without clogging data
const CRITICAL_ASSETS = [
  '/',
  '/index.html',
  '/cart.html',
  '/collections/all.html',
  '/assets/ecommerce/cart.css',
  '/assets/ecommerce/bh_style.css',
  '/assets/css/site-footer.css',
  '/assets/ecommerce/ecommerce.js',
  '/assets/js/site-header.js',
  '/assets/js/site-footer.js',
  '/assets/img/jobarn-logo.jpeg',
  '/assets/img/upgrades/desk-setup.webp',
  '/assets/img/upgrades/production.jpg',
  '/assets/img/upgrades/custom-pc-poster.jpg',
  '/assets/img/upgrades/keyboards.jpg',
  '/assets/img/upgrades/monitors.jpg',
  '/assets/img/upgrades/ergonomic-chairs.jpg',
  '/assets/img/upgrades/gadgets.jpg',
  '/assets/img/upgrades/audio-equipments.jpg',
  '/assets/ecommerce/products/skadis-pegboard.jpg',
  '/assets/ecommerce/products/sihoo-m59as.jpg',
  '/assets/ecommerce/products/green-lion-cooler.jpg',
  '/assets/ecommerce/products/desk-140cm.jpg',
  '/assets/ecommerce/products/apple-studio-display.jpg',
  '/assets/ecommerce/products/sony-fx3.jpg'
];

// 2. Video streams - queued smoothly so 2G/3G bandwidth isn't choked
const HEAVY_MEDIA = [
  '/assets/video/video_new_tumehama-web.mp4',
  '/assets/img/upgrades/custom-pc-web.mp4',
  '/assets/video/video1-web.mp4',
  '/assets/video/video2-web.mp4',
  '/assets/video/video3-web.mp4',
  '/assets/video/video4-web.mp4',
  '/assets/video/video5-web.mp4'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(async (cache) => {
      // Step 1: Pre-cache critical small files first (instant visual rendering)
      await Promise.allSettled(
        CRITICAL_ASSETS.map((url) => cache.add(url).catch(() => {}))
      );

      // Step 2: Queue heavy video caching in sequence only if not on Data-Saver mode
      const isSaveData = navigator.connection && (navigator.connection.saveData || /2g/i.test(navigator.connection.effectiveType || ''));
      if (!isSaveData) {
        for (const videoUrl of HEAVY_MEDIA) {
          try {
            await cache.add(videoUrl);
          } catch (e) {
            // Ignore video download aborts on sluggish connections
          }
        }
      }
    }).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Bypass cache for admin panel, mutation calls, and live json api data
  if (
    url.pathname.startsWith('/admin') ||
    url.pathname.endsWith('.json') ||
    req.method !== 'GET'
  ) {
    return;
  }

  // Handle static assets (images, posters, css, js, fonts, videos)
  const isStatic =
    url.pathname.match(/\.(mp4|webm|jpg|jpeg|png|webp|svg|gif|css|js|woff2?|ttf)$/i) ||
    url.pathname.startsWith('/assets/');

  if (isStatic) {
    event.respondWith(
      caches.match(req, { ignoreSearch: true }).then((cached) => {
        // Return from local cache in 1-3ms if available - NEVER re-download
        if (cached) {
          return cached;
        }

        // Otherwise fetch once from server, store in cache for all next times
        return fetch(req).then((response) => {
          if (response && (response.status === 200 || response.type === 'opaque')) {
            const copy = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
          }
          return response;
        }).catch(() => {
          return cached;
        });
      })
    );
    return;
  }

  // For HTML pages: Stale-While-Revalidate (instant 1ms load from cache, background refresh for freshest content)
  if (req.mode === 'navigate' || url.pathname.endsWith('.html') || url.pathname === '/') {
    event.respondWith(
      caches.match(req).then((cached) => {
        const fetchPromise = fetch(req).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const clone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(req, clone));
          }
          return networkResponse;
        }).catch(() => cached);

        // If page is already visited/cached, open it instantly without network wait!
        return cached || fetchPromise;
      })
    );
    return;
  }

  // Fallback for any other requests
  event.respondWith(
    fetch(req).catch(() => caches.match(req))
  );
});
