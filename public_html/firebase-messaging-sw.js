importScripts('https://www.gstatic.com/firebasejs/5.7.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/5.7.2/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
firebase.initializeApp({
    messagingSenderId: '48205136182'
    //messagingSenderId: '223613695720'
});

messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
    console.log('Received background message ', payload);
    alert(JSON.stringify(payload));
    // here you can override some options describing what's in the message;
    // however, the actual content will come from the Webtask
    var notificationTitle = 'Background Message Title';
    var notificationOptions = {
        body: 'Background Message body.',
        icon: 'images/icon.png'
    };
    return self.registration.showNotification(notificationTitle, notificationOptions);
});