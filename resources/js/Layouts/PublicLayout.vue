<script setup>
import { ref, provide, onMounted } from "vue";
import { Head, router } from "@inertiajs/vue3";
import NavBarPublic from "@/Components/NavBarPublic.vue";
import logo from "@/Components/logo.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import "vue-final-modal/style.css";
import { ModalsContainer, useModal } from "vue-final-modal";
import ModalDevis from "@/Components/ModalDevis.vue";

const { open, close } = useModal({
    component: ModalDevis,
    attrs: {
        title: "Demande de devis",
        onSubmit(formData) {
            console.log("Form data soumis :", formData);
            close();
        },
        onSuccess(message) {
            showNotification(message, "success");
            close();
        },
        onError(message) {
            showNotification(message, "error");
        },
        lockScroll: true,
        reserveScrollBarGap: false,
    },
});

defineProps({
    title: {
        type: String,
        default: "Solelec",
    },
});

const emits = defineEmits(["devisModalOpened"]);
// Émettre la fonction d'ouverture du modal après le montage du composant
onMounted(() => {
    emits("devisModalOpened", open);
});

const navItems = [
    { name: "Accueil", route: "accueil" },
    { name: "Services", route: "services" },
    { name: "Réalisations", route: "realisations" },
];

const aboutSubItems = [
    { name: "Contact", anchor: "#contact", route: "accueil" },
    { name: "Zones d'intervention", anchor: "#zones", route: "accueil" },
];

// Système de notification global
const notification = ref({
    show: false,
    message: "",
    type: "success", // 'success' ou 'error'
});

const showNotification = (message, type = "success") => {
    notification.value = {
        show: true,
        message: message,
        type: type,
    };

    // Cacher la notification après 5 secondes
    setTimeout(() => {
        hideNotification();
    }, 5000);
};

const hideNotification = () => {
    notification.value.show = false;
};

const navigateToSection = (sectionId, route) => {
    // Récupérer la route actuelle
    const currentRoute = window.location.pathname.substring(1) || "accueil";

    if (currentRoute === route) {
        // Si on est déjà sur la bonne page, on défile simplement vers la section
        scrollToSection(sectionId);
    } else {
        // Sinon, on navigue vers la page puis on défile
        router.visit("/" + route, {
            onSuccess: () => {
                // Une fois la navigation terminée, on défile vers la section
                // On ajoute un petit délai pour que le DOM soit bien chargé
                setTimeout(() => {
                    scrollToSection(sectionId);
                }, 100);
            },
        });
    }
};

// Fonction de défilement vers une section
const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
        const offset = 72; // Hauteur du header + marge additionnelle
        const elementPosition =
            element.getBoundingClientRect().top + window.scrollY;
        const offsetPosition = elementPosition - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: "smooth",
        });
    }
};

// Exposer ces fonctions pour qu'elles soient accessibles par les composants enfants
provide("showNotification", showNotification);
provide("hideNotification", hideNotification);
provide("navigateToSection", navigateToSection);
</script>

<template>
    <div class="flex flex-col min-h-screen bg-white">
        <Head :title="title" />

        <NavBarPublic
            :openDevisModal="open"
            @scrollToSection="$emit('scrollToSection', $event)"
        />

        <ModalsContainer />

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
                            @click="hideNotification"
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

        <footer
            class="flex py-20 px-16 flex-col items-center gap-20 bg-[#2D2D2D]"
        >
            <div class="flex justify-between w-full items-center self-stretch">
                <div class="flex flex-col items-start gap-8">
                    <logo />
                    <div
                        class="flex flex-col text-[#ffff] font-inter items-start gap-6 self-stretch"
                    >
                        <div
                            class="flex flex-col items-start gap-1 self-stretch"
                        >
                            <p class="self-stretch text-base font-semibold">
                                Adresse:
                            </p>
                            <p class="self-stretch text-sm">
                                Rue de Neufmoustier 4,
                            </p>
                            <p class="self-stretch text-sm">
                                1348 Ottignies-Louvain-la-Neuve, Belgium
                            </p>
                        </div>
                        <div
                            class="flex flex-col items-start gap-1 self-stretch text-[#ffff] font-inter"
                        >
                            <p class="self-stretch text-base font-semibold">
                                Contact:
                            </p>
                            <p class="self-stretch text-sm">
                                <a
                                    href="tel:0492510931"
                                    class="underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors"
                                    >0492 51 09 31</a
                                >
                            </p>
                            <p class="self-stretch text-sm">
                                <a
                                    href="mailto:solelec.lmbt@gmail.com"
                                    class="underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors"
                                    >solelec.lmbt@gmail.com</a
                                >
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-start w-auto">
                    <ul class="flex flex-col gap-2">
                        <li v-for="item in navItems" :key="item.name">
                            <a
                                :href="'/' + item.route"
                                class="text-white text-sm underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors"
                            >
                                {{ item.name }}
                            </a>
                        </li>
                    </ul>
                    <ul class="flex flex-col gap-2 mt-2">
                        <li v-for="item in aboutSubItems" :key="item.name">
                            <a
                                href="#"
                                @click.prevent="
                                    navigateToSection(
                                        item.anchor.substring(1),
                                        item.route
                                    )
                                "
                                class="text-white text-sm underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors"
                            >
                                {{ item.name }}
                            </a>
                        </li>
                    </ul>
                    <PrimaryButton @click="open" class="mt-4" navStyle>
                        Devis</PrimaryButton
                    >
                </div>
            </div>
            <div
                class="flex flex-col flex-start gap-8 self-stretch text-[#ffff] font-inter text-sm"
            >
                <div class="border-t border-white/20"></div>
                <div class="flex justify-between items-start self-stretch">
                    <p class="">© 2025 Solelec. Tous droits réservés.</p>
                    <div class="flex flex-start gap-6">
                        <a
                            class="underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors"
                            href=""
                            >Politique de confidentialité</a
                        >
                        <a
                            class="underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors"
                            href=""
                            >Conditions de service</a
                        >
                        <a
                            class="underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors"
                            href=""
                            >Paramètres des cookies</a
                        >
                    </div>
                </div>
            </div>
        </footer>
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
