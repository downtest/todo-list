// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getMessaging, getToken } from "firebase/messaging";
// https://firebase.google.com/docs/web/setup#available-libraries

process.env.GOOGLE_APPLICATION_CREDENTIALS = '/config/firebase/listodo-6a00c-firebase-adminsdk-6qpds-575a893522.json'

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
    apiKey: "AIzaSyBtD9XFoqJTInXl4krXe1PkDg0EqzE_eYY",
    authDomain: "listodo-6a00c.firebaseapp.com",
    projectId: "listodo-6a00c",
    storageBucket: "listodo-6a00c.appspot.com",
    messagingSenderId: "727465091360",
    appId: "1:727465091360:web:d23a7dc5512372108d7745",
    measurementId: "G-WFRBJQYJM7",
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

export default {
    messaging,
    getToken,
    vapidKey: process.env.VUE_APP_FIREBASE_VAPID_KEY,
}
