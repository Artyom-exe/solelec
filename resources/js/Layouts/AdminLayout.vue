<script setup>
import { ref, provide, inject, onMounted, onUnmounted, watchEffect } from "vue";
import { Head } from "@inertiajs/vue3";
import NavBarAdmin from "@/Components/NavBarAdmin.vue";
import NotificationPanel from "@/Components/NotificationPanel.vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

// Injection du système de notification global
const notification = ref({
    show: false,
    message: "",
    type: "success",
});

// PWA Installation - Méthode classique du navigateur
const deferredPrompt = ref(null);

// Système de notifications PWA
const notificationCount = ref(0);
const showNotificationPanel = ref(false);

// Surveiller les messages flash pour les afficher automatiquement
watchEffect(() => {
    if (page.props.flash.success) {
        notification.value = {
            show: true,
            message: page.props.flash.success,
            type: "success",
        };

        // Cacher la notification après 5 secondes
        setTimeout(() => {
            notification.value.show = false;
        }, 5000);
    }

    if (page.props.flash.error) {
        notification.value = {
            show: true,
            message: page.props.flash.error,
            type: "error",
        };

        // Cacher la notification après 5 secondes
        setTimeout(() => {
            notification.value.show = false;
        }, 5000);
    }
});

const showNotificationHandler = (event) => {
    notification.value = {
        show: true,
        message: event.detail.message,
        type: event.detail.type,
    };

    // Cacher la notification après 5 secondes
    setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

// Fonction pour afficher les notifications depuis les composants enfants
const showNotification = (message, type = "success") => {
    notification.value = {
        show: true,
        message: message,
        type: type,
    };

    // Cacher la notification après 5 secondes
    setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

// Fournir la fonction aux composants enfants
provide("showNotification", showNotification);

const hideNotificationHandler = () => {
    notification.value.show = false;
};

// Détection mobile basique
const isMobile = (() => {
    if (typeof navigator === "undefined") return false;
    return /Mobi|Android|iP(hone|od|ad)|Windows Phone/i.test(
        navigator.userAgent
    );
})();

// Fonctions pour les notifications PWA
const updateNotificationCount = async () => {
    try {
        const response = await fetch("/admin/notifications/count");
        const data = await response.json();
        const newCount = data.total_count || 0;

        // Si le nombre a augmenté, déclencher une notification locale
        if (newCount > notificationCount.value && notificationCount.value > 0) {
            showLocalNotification();
        }

        notificationCount.value = newCount;

        // Mettre à jour le badge de l'application avec le nombre de notifications non lues
        if (
            "serviceWorker" in navigator &&
            navigator.serviceWorker.controller
        ) {
            navigator.serviceWorker.controller.postMessage({
                type: "UPDATE_BADGE",
                count: newCount,
            });

            // Envoyer aussi le message d'initialisation pour synchroniser le service worker
            navigator.serviceWorker.controller.postMessage({
                type: "INIT_COUNT",
                count: newCount,
            });
        }

        // Fallback pour mobile : changer le titre de la page
        if (newCount > 0) {
            document.title = `(${newCount}) Solelec Admin`;
        } else {
            document.title = "Solelec Admin";
        }
    } catch (error) {
        console.error(
            "Erreur lors de la récupération du nombre de notifications:",
            error
        );
    }
};

// Fonction pour afficher une notification locale
const showLocalNotification = () => {
    // Vérifier les permissions
    if (Notification.permission === "granted") {
        // Si on a un service worker, utiliser showNotification pour PWA
        if (
            "serviceWorker" in navigator &&
            navigator.serviceWorker.controller
        ) {
            // Envoyer un message au service worker pour déclencher une notification
            navigator.serviceWorker.controller.postMessage({
                type: "SHOW_NOTIFICATION",
                title: "Solelec Admin",
                body: "Nouvelle notification disponible",
                icon: "/images/icons/icon-192x192.png",
                badge: "/images/icons/icon-192x192.png",
                tag: "solelec-new-notification",
                data: { url: "/admin" },
            });
        } else {
            // Fallback : notification normale
            new Notification("Solelec Admin", {
                body: "Nouvelle notification disponible",
                icon: "/images/icons/icon-192x192.png",
                badge: "/images/icons/icon-192x192.png",
                tag: "solelec-new-notification",
                vibrate: [200, 100, 200],
                requireInteraction: false,
                data: { url: "/admin" },
            });
        }
    } else if (Notification.permission === "default") {
        // Demander la permission
        Notification.requestPermission().then((permission) => {
            if (permission === "granted") {
                showLocalNotification();
            }
        });
    }
};

// Initialiser les notifications push
const initializePushNotifications = async () => {
    try {
        // Ne pas afficher la notification de test plus d'une fois par session
        if (sessionStorage.getItem("admin_push_notif_shown")) return;

        if (!("Notification" in window)) {
            // Fallback pour mobile : informer l'utilisateur via le panneau interne
            if (isMobile) {
                try {
                    if (
                        !sessionStorage.getItem(
                            "admin_push_notif_supported_shown"
                        )
                    ) {
                        showNotification(
                            "Les notifications natives ne sont pas prises en charge par ce navigateur. Installez l'application (PWA) ou utilisez le panneau de notifications intégré.",
                            "info"
                        );
                        sessionStorage.setItem(
                            "admin_push_notif_supported_shown",
                            "1"
                        );
                    }
                } catch (e) {}
            }
            return;
        }

        if (Notification.permission === "granted") {
            // Test immédiat pour voir si ça marche (une seule fois par session)
            new Notification("Solelec Admin", {
                body: "Notifications déjà actives !",
                icon: "/images/icons/icon-192x192.png",
            });
            // Marquer comme affichée pour cette session
            try {
                sessionStorage.setItem("admin_push_notif_shown", "1");
            } catch (e) {
                // ignore storage errors
            }
            return;
        }

        if (Notification.permission === "denied") {
            // Si l'utilisateur a déjà refusé, informer via le panneau (mobile/desktop) une seule fois
            try {
                if (!sessionStorage.getItem("admin_push_notif_denied_shown")) {
                    showNotification(
                        "Les notifications natives sont désactivées pour ce site. Activez-les dans les paramètres du navigateur si vous souhaitez les recevoir.",
                        "info"
                    );
                    sessionStorage.setItem(
                        "admin_push_notif_denied_shown",
                        "1"
                    );
                }
            } catch (e) {}
            return;
        }

        // Permission par défaut - demander maintenant
        const permission = await Notification.requestPermission();

        if (permission === "granted") {
            showNotification(
                "Notifications activées ! Vous recevrez les alertes importantes.",
                "success"
            );

            // Test immédiat
            setTimeout(() => {
                new Notification("Solelec Admin", {
                    body: "Notifications configurées avec succès !",
                    icon: "/images/icons/icon-192x192.png",
                    tag: "setup-success",
                });
                // Marquer comme affichée pour cette session
                try {
                    sessionStorage.setItem("admin_push_notif_shown", "1");
                } catch (e) {
                    // ignore storage errors
                }
            }, 1000);
        }
    } catch (error) {
        // Erreur silencieuse
    }
};

const toggleNotificationPanel = () => {
    showNotificationPanel.value = !showNotificationPanel.value;
};

const closeNotificationPanel = () => {
    showNotificationPanel.value = false;
};

// Fonction appelée quand une notification est lue
const handleNotificationRead = () => {
    // Mise à jour rapide du badge (optimiste)
    if (notificationCount.value > 0) notificationCount.value -= 1;
    // Synchroniser avec le serveur en arrière-plan
    updateNotificationCount();
};

onMounted(() => {
    window.addEventListener("show-notification", showNotificationHandler);
    window.addEventListener("hide-notification", hideNotificationHandler);

    // Initialiser les notifications immédiatement
    setTimeout(() => {
        initializePushNotifications();
    }, 1000);

    // Gestion globale des erreurs 419 (CSRF token expiré)
    window.addEventListener("unhandledrejection", (event) => {
        if (event.reason && event.reason.status === 419) {
            showNotification(
                "Session expirée. La page va être rechargée automatiquement.",
                "error"
            );
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    });

    // Intercepter les erreurs Inertia
    document.addEventListener("inertia:error", (event) => {
        if (event.detail.status === 419) {
            showNotification(
                "Session expirée. Rechargement de la page...",
                "error"
            );
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    });
    window.addEventListener("hide-notification", hideNotificationHandler);

    // Enregistrer le Service Worker pour PWA
    if (
        "serviceWorker" in navigator &&
        (window.location.protocol === "https:" ||
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1" ||
            window.location.hostname.includes("laragon"))
    ) {
        window.addEventListener("load", async () => {
            navigator.serviceWorker
                .register("/sw.js")
                .then(async (registration) => {
                    // Service Worker enregistré avec succès

                    // Initialiser les notifications push
                    await initializePushNotifications();

                    // Écouter les mises à jour
                    registration.addEventListener("updatefound", () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener("statechange", () => {
                            if (
                                newWorker.state === "installed" &&
                                navigator.serviceWorker.controller
                            ) {
                                showNotification(
                                    "Une nouvelle version de l'application est disponible. Rechargez la page pour l'utiliser.",
                                    "success"
                                );
                            }
                        });
                    });
                })
                .catch((error) => {
                    // Échec de l'enregistrement du Service Worker
                });
        });

        // Écouter les messages du Service Worker
        navigator.serviceWorker.addEventListener("message", (event) => {
            if (event.data && event.data.type === "UPDATE_AVAILABLE") {
                showNotification(event.data.message, "success");
            }
        });
    }

    // Gestion basique de l'installation PWA (méthode classique)
    window.addEventListener("beforeinstallprompt", (e) => {
        // Stocker l'événement pour référence (optionnel)
        deferredPrompt.value = e;

        // Laisser le navigateur gérer l'affichage de l'installation
        // Ne pas appeler e.preventDefault() pour laisser le comportement natif
    });

    // Détection d'installation réussie
    window.addEventListener("appinstalled", (evt) => {
        showNotification("Application installée avec succès !", "success");
        deferredPrompt.value = null;
    });

    // Mettre à jour le nombre de notifications au démarrage
    updateNotificationCount();

    // Actualiser les notifications toutes les 2 minutes
    const notificationInterval = setInterval(updateNotificationCount, 5000);

    // Stocker l'interval pour le nettoyer plus tard
    window.notificationInterval = notificationInterval;
});

onUnmounted(() => {
    window.removeEventListener("show-notification", showNotificationHandler);
    window.removeEventListener("hide-notification", hideNotificationHandler);

    // Nettoyer l'interval des notifications
    if (window.notificationInterval) {
        clearInterval(window.notificationInterval);
    }
});
</script>

<template>
    <div class="flex flex-col min-h-screen">
        <!-- Métadonnées PWA -->
        <Head>
            <!-- Manifest PWA -->
            <link rel="manifest" href="/manifest.json" />

            <!-- Métadonnées PWA -->
            <meta name="theme-color" content="#FF8C42" />
            <meta name="background-color" content="#2D2D2D" />

            <!-- Favicon standard -->
            <link rel="icon" type="image/x-icon" href="/favicon.ico?v=2" />
            <link rel="shortcut icon" href="/favicon.ico" />
            <link
                rel="icon"
                type="image/png"
                sizes="32x32"
                href="/favicon-32x32.png?v=2"
            />
            <link
                rel="icon"
                type="image/png"
                sizes="16x16"
                href="/favicon-16x16.png?v=2"
            />
            <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2" />
            <meta name="msapplication-TileImage" content="/favicon.ico?v=2" />

            <!-- Icônes pour différentes plateformes -->
            <link
                rel="icon"
                type="image/png"
                href="/images/icons/icon-192x192.png"
            />
            <link
                rel="apple-touch-icon"
                href="/images/icons/icon-192x192.png"
            />

            <!-- Métadonnées Apple -->
            <meta name="apple-mobile-web-app-capable" content="yes" />
            <meta
                name="apple-mobile-web-app-status-bar-style"
                content="black-translucent"
            />
            <meta name="apple-mobile-web-app-title" content="Solelec Admin" />

            <!-- Métadonnées Microsoft -->
            <meta name="msapplication-TileColor" content="#FF8C42" />
            <meta
                name="msapplication-TileImage"
                content="/images/icons/icon-192x192.png"
            />

            <!-- Optimisations mobile -->
            <meta name="mobile-web-app-capable" content="yes" />
            <meta
                name="viewport"
                content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"
            />
        </Head>

        <NavBarAdmin
            :notificationCount="notificationCount"
            @toggleNotifications="toggleNotificationPanel"
        />

        <!-- Panneau de notifications -->
        <NotificationPanel
            :showNotifications="showNotificationPanel"
            @close="closeNotificationPanel"
            @notificationRead="handleNotificationRead"
        />

        <!-- Système de notification -->
        <div
            v-if="notification.show"
            :class="{
                'fixed top-20 right-4 md:right-4 left-4 md:left-auto z-50 p-4 rounded-md shadow-lg max-w-sm md:max-w-sm w-auto md:w-auto animate-fade-in': true,
                'bg-green-100 border-l-4 border-green-500 text-green-700':
                    notification.type === 'success',
                'bg-red-100 border-l-4 border-red-500 text-red-700':
                    notification.type === 'error',
            }"
        >
            <div class="flex items-center">
                <!-- Icône de succès -->
                <div
                    v-if="notification.type === 'success'"
                    class="flex-shrink-0"
                >
                    <svg
                        class="h-5 w-5 text-green-500"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <!-- Icône d'erreur -->
                <div v-else class="flex-shrink-0">
                    <svg
                        class="h-5 w-5 text-red-500"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">
                        {{ notification.message }}
                    </p>
                </div>
                <!-- Bouton pour fermer -->
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button
                            @click="hideNotificationHandler"
                            class="inline-flex rounded-md p-1.5 text-gray-500 hover:bg-gray-100 focus:outline-none"
                        >
                            <span class="sr-only">Fermer</span>
                            <svg
                                class="h-5 w-5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <main class="flex-grow">
            <slot />
        </main>
    </div>
</template>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>
