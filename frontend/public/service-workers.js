if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/cache-sw.js');

    // FireBase (push уведомления)
    navigator.serviceWorker.register('/firebase-messaging-sw.js');


    // window.addEventListener('load', function () {
    //
    // });
}
