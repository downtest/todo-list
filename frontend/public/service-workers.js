if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/cache-sw.js');

    // Firebase будет работать только на https, иначе выдаст ошибку
    if (location.protocol === 'https:') {
        // FireBase (push уведомления)
        navigator.serviceWorker.register('/firebase-messaging-sw.js');
    }


    // window.addEventListener('load', function () {
    //
    // });
}
