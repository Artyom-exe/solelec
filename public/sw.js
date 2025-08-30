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

        // Afficher la notification puis mettre à jour le badge
        event.waitUntil(
            (async () => {
                await self.registration.showNotification(
                    data.title || "Solelec Admin",
                    options
                );

                // Mettre à jour le compteur de badge : si le payload fournit un count/badge, on l'utilise,
                // sinon on incrémente le compteur localement.
                try {
                    const payloadCount =
                        typeof data.count === "number"
                            ? data.count
                            : typeof data.badge === "number"
                            ? data.badge
                            : null;

                    let newCount;
                    if (payloadCount !== null) {
                        newCount = payloadCount;
                    } else {
                        // Incrément par défaut
                        const current = await readBadgeCount();
                        newCount = (current || 0) + 1;
                    }

                    await writeBadgeCount(newCount);
                    await setAppBadgeSafe(newCount);
                } catch (e) {
                    // Ne pas bloquer l'affichage de la notification si l'update du badge échoue
                    console.warn("Badge update failed in push handler:", e);
                }
            })()
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
                        // Clear badge when user opens the app from the notification
                        (async () => {
                            try {
                                await self.registration.clearAppBadge();
                            } catch (e) {
                                // ignore
                            }
                            try {
                                await writeBadgeCount(0);
                            } catch (e) {}
                        })();
                        return client.focus();
                    }
                }
                // Sinon ouvrir une nouvelle fenêtre
                if (clients.openWindow) {
                    const url = event.notification.data.url || "/admin";
                    // Clear badge when opening app
                    (async () => {
                        try {
                            await self.registration.clearAppBadge();
                        } catch (e) {}
                        try {
                            await writeBadgeCount(0);
                        } catch (e) {}
                    })();
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

        // Mettre à jour le badge sur l'icône de l'application (PWA)
        event.waitUntil(
            (async () => {
                try {
                    if (count > 0) {
                        await writeBadgeCount(count);
                        await setAppBadgeSafe(count);
                    } else {
                        await writeBadgeCount(0);
                        await clearAppBadgeSafe();
                    }
                } catch (e) {
                    console.warn("UPDATE_BADGE failed:", e);
                }
            })()
        );
    }
});

// --- Helpers for badge persistence (IndexedDB) and safe badge calls ---
function openDb() {
    return new Promise((resolve, reject) => {
        try {
            const req = indexedDB.open("solelec-sw", 1);
            req.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains("meta")) {
                    db.createObjectStore("meta");
                }
            };
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        } catch (e) {
            reject(e);
        }
    });
}

function readBadgeCount() {
    return new Promise(async (resolve, reject) => {
        try {
            const db = await openDb();
            const tx = db.transaction("meta", "readonly");
            const store = tx.objectStore("meta");
            const r = store.get("badge");
            r.onsuccess = () => resolve(r.result || 0);
            r.onerror = () => resolve(0);
        } catch (e) {
            resolve(0);
        }
    });
}

function writeBadgeCount(count) {
    return new Promise(async (resolve, reject) => {
        try {
            const db = await openDb();
            const tx = db.transaction("meta", "readwrite");
            const store = tx.objectStore("meta");
            const r = store.put(count, "badge");
            r.onsuccess = () => resolve();
            r.onerror = () => reject(r.error);
        } catch (e) {
            reject(e);
        }
    });
}

async function setAppBadgeSafe(count) {
    try {
        if (self.registration && "setAppBadge" in self.registration) {
            await self.registration.setAppBadge(count);
        } else if (
            typeof navigator !== "undefined" &&
            "setAppBadge" in navigator
        ) {
            await navigator.setAppBadge(count);
        }
    } catch (e) {
        // Some browsers may throw if badge unsupported
        // ignore silently
    }
}

async function clearAppBadgeSafe() {
    try {
        if (self.registration && "clearAppBadge" in self.registration) {
            await self.registration.clearAppBadge();
        } else if (
            typeof navigator !== "undefined" &&
            "clearAppBadge" in navigator
        ) {
            await navigator.clearAppBadge();
        }
    } catch (e) {
        // ignore
    }
}

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
