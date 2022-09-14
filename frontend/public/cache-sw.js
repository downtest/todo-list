const contentToCache = [
    '/img/checkbox-list-white.01752f19.svg',
    '/js/chunk-vendors.js',
    '/js/chunk-common.js',
    '/js/index.js',
];
const CACHE_ASSETS_NAME = 'pwa-assets'
const CACHE_FETCH_NAME = 'pwa-fetch'

self.addEventListener('install', (e) => {
    console.log('[Service Worker] Install');
    e.waitUntil((async () => {
        caches.open(CACHE_ASSETS_NAME)
            .then(cache => {
                return cache.addAll(contentToCache);
            })

        console.log('[Service Worker] Caching all: app shell and content');
    })());
})


// https://webformyself.com/5-strategij-keshirovaniya-service-worker-dlya-prilozheniya-pwa/
// Network First
self.addEventListener('fetch', function (event) {
    console.log(`[Service Worker] Fetching resource: ${event.request.url}`);

    event.respondWith(
        fetch(event.request).catch(function() {
            return caches.match(event.request)
        })
    )
})
