const CONTENT_TO_CACHE = [
    '/list',

    '/img/listodo-logo-full.d7eb7c96.svg',
    '/img/search.7f3f9190.svg',
    '/img/plus.15bec49d.svg',
    '/img/bell.e1bcf1f3.svg',
    '/img/calendar-2.797fa156.svg',
    '/img/calendar-2-white.1aebf275.svg',
    '/img/checkbox-list.af3ed3b2.svg',
    '/img/profile.1403a3d4.svg',
    '/img/checkbox-list-white.01752f19.svg',
    '/img/plus-white.f8484fa3.svg',
    '/img/profile.1403a3d4.svg',
    '/img/profile-white.bc58212a.svg',
    '/img/clock.bbe3dfe6.svg',
    '/img/trash.c02477fa.svg',
    '/img/cloud-crossed-no2.93a75f45.svg',
    '/listodo-logo.svg',

    '/js/chunk-vendors.js',
    '/js/chunk-common.js',
    '/js/index.js',
];
const CACHE_ASSETS_NAME = 'pwa-assets'
const CACHE_FETCH_NAME = 'pwa-fetch'
// период обновления кэша - одни сутки
const MAX_AGE = 86400000

self.addEventListener('install', (e) => {
    console.log('[Service Worker] Install');
    e.waitUntil((async () => {
        caches.open(CACHE_ASSETS_NAME)
            .then(cache => {
                return cache.addAll(CONTENT_TO_CACHE);
            })

        console.log('[Service Worker] Caching all: app shell and content');
    })());
})


self.addEventListener('fetch', function(event) {

    event.respondWith(
        // ищем запрошенный ресурс среди закэшированных
        caches.match(event.request).then(function(cachedResponse) {
            var lastModified, fetchRequest;

            // если ресурс есть в кэше
            if (cachedResponse) {
                // получаем дату последнего обновления
                lastModified = new Date(cachedResponse.headers.get('last-modified'));
                // и если мы считаем ресурс устаревшим
                if (lastModified && (Date.now() - lastModified.getTime()) > MAX_AGE) {

                    fetchRequest = event.request.clone();
                    // создаём новый запрос
                    return fetch(fetchRequest).then(function(response) {
                        // при неудаче всегда можно выдать ресурс из кэша
                        if (!response || response.status !== 200) {
                            return cachedResponse;
                        }
                        // обновляем кэш
                        caches.open(CACHE_ASSETS_NAME).then(function(cache) {
                            cache.put(event.request, response.clone());
                        });
                        // возвращаем свежий ресурс
                        return response;
                    }).catch(function() {
                        return cachedResponse;
                    });
                }
                return cachedResponse;
            }

            // запрашиваем из сети как обычно
            return fetch(event.request);
        })
    );
});
