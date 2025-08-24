<template>
    <div
        v-if="showNotifications"
        ref="panelRef"
        class="fixed top-20 right-0 md:right-4 z-50 w-full md:w-96 max-h-[32rem] overflow-y-auto bg-white rounded-lg shadow-xl border border-gray-200"
    >
        <!-- En-tête -->
        <div
            class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center"
        >
            <h3 class="text-lg font-semibold text-gray-900">
                Notifications
                <span
                    v-if="unreadCount > 0"
                    class="ml-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full"
                >
                    {{ unreadCount }}
                </span>
            </h3>
            <button
                @click="closeNotifications"
                class="text-gray-400 hover:text-gray-600 focus:outline-none"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    ></path>
                </svg>
            </button>
        </div>

        <!-- Liste des notifications -->
        <div v-if="notifications.length > 0" class="divide-y divide-gray-200">
            <div
                v-for="notification in notifications"
                :key="notification.id"
                @click="() => openNotification(notification)"
                class="p-4 hover:bg-gray-50 cursor-pointer transition-colors duration-200"
                :class="[
                    notification.is_read ? 'opacity-60' : 'opacity-100',
                    {
                        'border-l-4 border-red-500 bg-red-50':
                            notification.priority === 'high',
                        'border-l-4 border-yellow-500 bg-yellow-50':
                            notification.priority === 'medium',
                        'border-l-4 border-blue-500 bg-blue-50':
                            notification.priority === 'low',
                    },
                ]"
            >
                <div class="flex items-start">
                    <!-- Icône -->
                    <div class="flex-shrink-0 mr-3 mt-1">
                        <div
                            v-if="notification.type === 'intervention'"
                            class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"
                        >
                            <svg
                                class="w-4 h-4 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                ></path>
                            </svg>
                        </div>
                        <div
                            v-else-if="
                                notification.type === 'quote' ||
                                notification.type === 'new_quote'
                            "
                            class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center"
                        >
                            <svg
                                class="w-4 h-4 text-green-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                ></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Contenu -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900">
                                {{ notification.title }}
                            </p>
                            <div
                                class="flex items-center text-xs text-gray-500 gap-3"
                            >
                                <span
                                    v-if="notification.priority === 'high'"
                                    class="text-red-600 font-medium"
                                    >Urgent</span
                                >
                                <span
                                    v-else-if="
                                        notification.priority === 'medium'
                                    "
                                    class="text-yellow-600 font-medium"
                                    >Important</span
                                >
                                <span
                                    v-if="
                                        notification.priority === 'high' ||
                                        notification.priority === 'medium'
                                    "
                                    class="mx-1"
                                    >•</span
                                >
                                <span>{{ formatDate(notification.date) }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ notification.message }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message si pas de notifications -->
        <div v-else class="p-6 text-center text-gray-500">
            <svg
                class="w-12 h-12 mx-auto mb-4 text-gray-300"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-5 5-5-5h5v-5a7.5 7.5 0 1 1 15 0v5z"
                ></path>
            </svg>
            <p class="text-sm">Aucune notification urgente</p>
        </div>

        <!-- Actions -->
        <div
            v-if="notifications.length > 0"
            class="bg-gray-50 px-4 py-3 border-t border-gray-200"
        >
            <button
                @click="() => markAllAsRead(true)"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium"
            >
                Tout marquer comme lu
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from "vue";
import { inject } from "vue";
// Permet de notifier la navbar de réinitialiser la cloche mobile
const resetMobileBell = inject("resetMobileBell", null);
// Référence pour le panneau
const panelRef = ref(null);

// Gestion du clic extérieur pour fermer le panneau
const handleClickOutside = (event) => {
    if (panelRef.value && !panelRef.value.contains(event.target)) {
        closeNotifications();
    }
};
import { router } from "@inertiajs/vue3";

const unreadCount = computed(
    () => notifications.value.filter((n) => !n.is_read).length
);

const props = defineProps({
    showNotifications: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "notificationRead"]);

const notifications = ref([]);
const loading = ref(false);

const urgentCount = computed(() => {
    return notifications.value.filter((notif) => notif.priority === "high")
        .length;
});

// Fonction pour récupérer les notifications
const fetchNotifications = async () => {
    if (loading.value) return;

    loading.value = true;
    try {
        const response = await fetch("/admin/notifications");
        const data = await response.json();
        notifications.value = data.notifications || [];
    } catch (error) {
        console.error("Erreur lors du chargement des notifications:", error);
    } finally {
        loading.value = false;
    }
};

// Fonction pour fermer le panneau de notifications
const closeNotifications = () => {
    emit("close");
    if (resetMobileBell) resetMobileBell();
};

// Fonction pour ouvrir une notification
const openNotification = async (notification) => {
    // Marquer comme lue avant de naviguer (mise à jour optimiste)
    await markAsRead(notification, { optimistic: true });

    if (notification.url) {
        router.visit(notification.url);
    }
    closeNotifications();
};

// Fonction pour marquer une notification comme lue
const markAsRead = async (
    notification,
    { optimistic } = { optimistic: false }
) => {
    try {
        await fetch("/admin/notifications/mark-read", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({
                type: notification.type,
                notification_id:
                    notification.notification_id || notification.data?.id,
            }),
        });
        // Mise à jour optimiste: retirer l'élément sans re-fetch
        if (optimistic) {
            const targetId =
                notification.notification_id || notification.data?.id;
            const targetType = notification.type;
            notifications.value = notifications.value.filter((n) => {
                const id = n.notification_id || n.data?.id;
                return !(n.type === targetType && id === targetId);
            });
        }

        // Émettre un événement pour mettre à jour le compteur dans la navbar
        emit("notificationRead");
        // Recharger la liste pour affichage temps réel
        await fetchNotifications();
    } catch (error) {
        console.error("Erreur lors du marquage de la notification:", error);
    }
};

// Fonction pour marquer toutes les notifications comme lues
const markAllAsRead = async (closePanel = false) => {
    try {
        await fetch("/admin/notifications/mark-all-read", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
                "X-Requested-With": "XMLHttpRequest",
            },
        });
        emit("notificationRead");
        // Recharger la liste pour affichage temps réel
        await fetchNotifications();
        if (closePanel) {
            closeNotifications();
        }
    } catch (error) {
        console.error(
            "Erreur lors du marquage de toutes les notifications:",
            error
        );
    }
};

// Fonction pour formater la date
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));

    if (diffInHours < 1) {
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));
        return diffInMinutes < 1 ? "À l'instant" : `Il y a ${diffInMinutes}min`;
    } else if (diffInHours < 24) {
        return `Il y a ${diffInHours}h`;
    } else {
        const diffInDays = Math.floor(diffInHours / 24);
        return `Il y a ${diffInDays}j`;
    }
};

// Charger les notifications et marquer comme lues à l'ouverture du panneau
watch(
    () => props.showNotifications,
    async (newValue) => {
        if (newValue) {
            await fetchNotifications();
            // Marquer toutes comme lues dès l'ouverture
            await markAllAsRead();
            // Recharger la liste pour mettre à jour l'état "is_read"
            await fetchNotifications();
        }
    }
);

// Actualiser les notifications périodiquement
let notificationInterval;

onMounted(() => {
    // Actualiser toutes les 5 secondes
    notificationInterval = setInterval(() => {
        if (props.showNotifications) {
            fetchNotifications();
        }
    }, 5000);

    watch(
        () => props.showNotifications,
        (show) => {
            if (show) {
                nextTick(() => {
                    document.addEventListener("mousedown", handleClickOutside);
                    document.addEventListener("touchstart", handleClickOutside);
                });
            } else {
                document.removeEventListener("mousedown", handleClickOutside);
                document.removeEventListener("touchstart", handleClickOutside);
            }
        },
        { immediate: true }
    );
});

onUnmounted(() => {
    if (notificationInterval) {
        clearInterval(notificationInterval);
    }
    document.removeEventListener("mousedown", handleClickOutside);
    document.removeEventListener("touchstart", handleClickOutside);
});
</script>
