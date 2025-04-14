<script setup>
import { ref, provide, inject, onMounted, onUnmounted } from "vue";
import NavBarAdmin from "@/Components/NavBarAdmin.vue";

// Injection du système de notification global
const notification = ref({
    show: false,
    message: "",
    type: "success",
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

const hideNotificationHandler = () => {
    notification.value.show = false;
};

onMounted(() => {
    window.addEventListener("show-notification", showNotificationHandler);
    window.addEventListener("hide-notification", hideNotificationHandler);
});
</script>

<template>
    <div class="flex flex-col min-h-screen">
        <NavBarAdmin />

        <!-- Système de notification -->
        <div
            v-if="notification.show"
            :class="{
                'fixed top-20 right-4 z-50 p-4 rounded-md shadow-lg max-w-sm animate-fade-in': true,
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
