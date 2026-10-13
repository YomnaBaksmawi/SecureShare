importScripts(
    'https://www.gstatic.com/firebasejs/12.0.0/firebase-app-compat.js'
);
importScripts(
    'https://www.gstatic.com/firebasejs/12.0.0/firebase-messaging-compat.js'
);

firebase.initializeApp({
    apiKey: 'AIzaSyDEE6xja7EUUAoBXpdBrRYOo-FwA8AFFmg',
    authDomain: 'sharingdocument-dd091.firebaseapp.com',
    projectId: 'sharingdocument-dd091',
    storageBucket: 'sharingdocument-dd091.firebasestorage.app',
    messagingSenderId: '532162927085',
    appId: '532162927085',
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const notification = payload.notification;

    if (!notification) {
        return;
    }

    self.registration.showNotification(
        notification.title,
        {
            body: notification.body,
            icon: '/favicon.ico',
        }
    );
});