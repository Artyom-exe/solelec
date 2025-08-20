const CACHE_NAME = "solelec-admin-v1";
const urlsToCache = [
    "/admin",
    "/admin/interventions",
    "/admin/devis",
    "/admin/clients",
    "/manifest.json",
    // CSS et JS essentiels seront ajoutés automatiquement
];

// Installation du Service Worker
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log("Cache ouvert");
            return cache.addAll(urlsToCache);
        })
    );
});

// Activation du Service Worker
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log(
                            "Suppression de l'ancien cache:",
                            cacheName
                        );
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Intercepter les requêtes réseau
self.addEventListener("fetch", (event) => {
    // Ne cache que les requêtes GET pour l'admin
    if (
        event.request.method === "GET" &&
        event.request.url.includes("/admin")
    ) {
        event.respondWith(
            caches
                .match(event.request)
                .then((response) => {
                    // Retourner la version en cache si disponible
                    if (response) {
                        return response;
                    }

                    // Sinon, faire la requête réseau
                    return fetch(event.request).then((response) => {
                        // Vérifier si la réponse est valide
                        if (
                            !response ||
                            response.status !== 200 ||
                            response.type !== "basic"
                        ) {
                            return response;
                        }

                        // Cloner la réponse
                        const responseToCache = response.clone();

                        // Ajouter au cache
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseToCache);
                        });

                        return response;
                    });
                })
                .catch(() => {
                    // En cas d'erreur réseau, retourner une page hors ligne si disponible
                    if (event.request.url.includes("/admin")) {
                        return caches.match("/admin");
                    }
                })
        );
    }
});

// Gérer les messages de l'application principale
self.addEventListener("message", (event) => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        self.skipWaiting();
    }
});

// Notification de mise à jour disponible
self.addEventListener("updatefound", () => {
    const newWorker = registration.installing;
    newWorker.addEventListener("statechange", () => {
        if (
            newWorker.state === "installed" &&
            navigator.serviceWorker.controller
        ) {
            // Nouvelle version disponible
            self.clients.matchAll().then((clients) => {
                clients.forEach((client) => {
                    client.postMessage({
                        type: "UPDATE_AVAILABLE",
                        message:
                            "Une nouvelle version de l'application est disponible.",
                    });
                });
            });
        }
    });
});
