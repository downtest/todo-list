// https://firebase.google.com/docs/web/setup#available-libraries

importScripts("https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js");

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
    apiKey: "AIzaSyBtD9XFoqJTInXl4krXe1PkDg0EqzE_eYY",
    authDomain: "listodo-6a00c.firebaseapp.com",
    projectId: "listodo-6a00c",
    storageBucket: "listodo-6a00c.appspot.com",
    messagingSenderId: "727465091360",
    appId: "1:727465091360:web:d23a7dc5512372108d7745",
    measurementId: "G-WFRBJQYJM7"
};

// Initialize Firebase
const app = firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

messaging.onBackgroundMessage(messaging, (payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    // Customize notification here
    const notificationTitle = 'Background Message Title';
    const notificationOptions = {
        body: 'Background Message body.',
        // icon: '/firebase-logo.png'
    };

    self.registration.showNotification(notificationTitle,
        notificationOptions);
});
