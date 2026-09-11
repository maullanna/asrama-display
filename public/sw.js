// Service worker untuk Web Push notifikasi abnormality.
self.addEventListener('push', function (event) {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: 'Asrama AKTI', body: event.data ? event.data.text() : '' };
    }

    const title = data.title || 'Asrama AKTI';
    const options = {
        body: data.body || '',
        icon: '/icon-192.png',
        badge: '/badge-96.png',
        data: { url: data.url || '/' },
        vibrate: [200, 100, 200],
        tag: 'abnormal',
        renotify: true,
        requireInteraction: true,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
            for (const client of list) {
                if ('focus' in client) return client.focus();
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});
