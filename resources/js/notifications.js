import { getToken } from 'firebase/messaging';
import { messaging } from './firebase';
import api from './api/client';

export async function requestNotificationPermission() {
    const permission = await Notification.requestPermission();

    if (permission !== 'granted') {
        return null;
    }

    const token = await getToken(messaging, {
        vapidKey: import.meta.env.VITE_FIREBASE_VAPID_KEY,
    });

    await api.post('/device-token', { token });

    return token;
}