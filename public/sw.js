const CACHE_NAME = "solelec-admin-v1.3";
const isDevelopment =
    location.hostname === "localhost" || location.hostname.includes(".test");

// URLs à mettre en cache (adaptation automatique dev/prod)
const urlsToCache = [
    "/manifest.json",
    "/images/icons/icon-192x192.png",
    "/images/icons/icon-512x512.png",
].concat(
    isDevelopment ? [] : ["/build/assets/app.css", "/build/assets/app.js"]
);

// Installation du Service Worker
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(urlsToCache).catch((err) => {
                // Erreur silencieuse lors de la mise en cache
            });
        })
    );
    self.skipWaiting(); // Force l'activation immédiate
});

// Activation du Service Worker
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim(); // Prend contrôle immédiatement
});

// Gestion des notifications push
self.addEventListener("push", (event) => {
    if (event.data) {
        const data = event.data.json();

        const options = {
            body: data.message || "Nouvelle notification Solelec",
            icon: "/images/icons/icon-192x192.png",
            badge: "/images/icons/icon-192x192.png",
            tag: data.tag || "solelec-notification",
            data: data.data || {},
            actions: [
                {
                    action: "view",
                    title: "Voir",
                    icon: "/images/icons/icon-192x192.png",
                },
                {
                    action: "dismiss",
                    title: "Ignorer",
                },
            ],
            requireInteraction: data.priority === "high",
            renotify: true,
            vibrate: data.priority === "high" ? [200, 100, 200] : [100],
        };

        event.waitUntil(
            self.registration.showNotification(
                data.title || "Solelec Admin",
                options
            )
        );
    }
});

// Gestion des clics sur les notifications
self.addEventListener("notificationclick", (event) => {
    event.notification.close();

    if (event.action === "view" || !event.action) {
        // Ouvrir ou focus sur l'application
        event.waitUntil(
            clients.matchAll({ type: "window" }).then((clientList) => {
                // Chercher si l'app est déjà ouverte
                for (const client of clientList) {
                    if (client.url.includes("/admin") && "focus" in client) {
                        return client.focus();
                    }
                }
                // Sinon ouvrir une nouvelle fenêtre
                if (clients.openWindow) {
                    const url = event.notification.data.url || "/admin";
                    return clients.openWindow(url);
                }
            })
        );
    }
});

// Gestion des messages pour mettre à jour le badge
self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "UPDATE_BADGE") {
        const count = event.data.count || 0;

        // Mettre à jour le badge sur l'icône de l'application
        if ("setAppBadge" in navigator) {
            if (count > 0) {
                navigator.setAppBadge(count);
            } else {
                navigator.clearAppBadge();
            }
        }
    }
});

// Intercepter les requêtes réseau
self.addEventListener("fetch", (event) => {
    const request = event.request;

    // Ne pas mettre en cache les requêtes POST/PUT/DELETE (formulaires avec CSRF)
    if (request.method !== "GET") {
        return;
    }

    // Ne pas mettre en cache les pages admin (à cause du CSRF token)
    if (
        request.url.includes("/admin") &&
        !request.url.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico)$/)
    ) {
        return;
    }

    // Cache seulement les assets statiques
    if (request.url.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|json)$/)) {
        event.respondWith(
            caches.match(request).then((response) => {
                return (
                    response ||
                    fetch(request).then((fetchResponse) => {
                        // Cloner la réponse pour la mettre en cache
                        const responseClone = fetchResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                        return fetchResponse;
                    })
                );
            })
        );
    }
});
