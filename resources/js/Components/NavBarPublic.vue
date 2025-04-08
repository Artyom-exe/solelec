<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import logo from "./logo.vue";

const props = defineProps({
    openDevisModal: {
        type: Function,
        required: true,
    },
});

const mobileMenuOpen = ref(false);
const aboutDropdownOpen = ref(false);

// Pour fermer le dropdown si on clique ailleurs
const closeDropdowns = (event) => {
    if (!event.target.closest(".about-dropdown")) {
        aboutDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", closeDropdowns);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", closeDropdowns);
});

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const toggleAboutDropdown = (event) => {
    event.stopPropagation();
    aboutDropdownOpen.value = !aboutDropdownOpen.value;
};

const navItems = [
    { name: "Accueil", route: "accueil" },
    { name: "Services", route: "services" },
    { name: "Réalisations", route: "realisations" },
];

const aboutSubItems = [
    { name: "Contact", anchor: "#contact", route: "accueil" },
    { name: "Zones d'intervention", anchor: "#zones", route: "accueil" },
];

// Fonction pour naviguer vers une section spécifique
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

const emit = defineEmits(["scrollToSection"]);
</script>

<template>
    <nav
        class="fixed top-0 left-0 right-0 bg-[#2D2D2D] px-16 justify-center flex-col items-center py-4 z-50"
    >
        <div class="flex justify-between items-center gap-8 self-stretch">
            <!-- Logo -->
            <logo />

            <!-- Navigation principale (Desktop) -->
            <div class="hidden md:flex items-center space-x-8">
                <div class="flex space-x-6">
                    <!-- Items de navigation standard -->
                    <Link
                        v-for="item in navItems"
                        :key="item.name"
                        :href="route('accueil')"
                        class="font-inter text-white text-base hover:text-[#FF8C42] transition-colors duration-200"
                        :class="{
                            'text-[#FF8C42] font-medium': route().current(
                                item.route
                            ),
                        }"
                    >
                        {{ item.name }}
                    </Link>

                    <!-- Menu À propos avec dropdown -->
                    <div class="relative about-dropdown">
                        <button
                            @click="toggleAboutDropdown"
                            class="font-inter text-white hover:text-[#FF8C42] transition-colors duration-200 flex items-center"
                        >
                            À propos
                            <svg
                                class="w-4 h-4 ml-1"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div
                            v-show="aboutDropdownOpen"
                            class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10"
                        >
                            <a
                                v-for="item in aboutSubItems"
                                :key="item.name"
                                href="#"
                                @click.prevent="
                                    navigateToSection(
                                        item.anchor.substring(1),
                                        item.route
                                    );
                                    aboutDropdownOpen = false;
                                "
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100 hover:text-[#FF8C42]"
                            >
                                {{ item.name }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <PrimaryButton @click="openDevisModal" navStyle>
                Devis
            </PrimaryButton>
            <!-- Hamburger (Mobile) -->
            <button
                @click="toggleMobileMenu"
                class="md:hidden flex items-center text-white"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        v-if="!mobileMenuOpen"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                    <path
                        v-else
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <!-- Menu Mobile avec sous-menus -->
        <div
            v-if="mobileMenuOpen"
            class="md:hidden absolute top-full left-0 right-0 bg-white shadow-lg py-4 px-6"
        >
            <div class="flex flex-col space-y-4">
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="route('accueil')"
                    class="font-inter text-gray-700 py-2 hover:text-[#FF8C42]"
                    :class="{
                        'text-[#FF8C42] font-medium': route().current(
                            item.route
                        ),
                    }"
                    @click="toggleMobileMenu"
                >
                    {{ item.name }}
                </Link>

                <!-- À propos et sous-éléments -->
                <div class="py-2">
                    <div class="font-inter text-gray-700 font-medium">
                        À propos
                    </div>
                    <div class="pl-4 mt-2 space-y-2">
                        <a
                            v-for="item in aboutSubItems"
                            :key="item.name"
                            href="#"
                            @click.prevent="
                                navigateToSection(
                                    item.anchor.substring(1),
                                    item.route
                                );
                                toggleMobileMenu();
                            "
                            class="block py-1 text-gray-600 hover:text-[#FF8C42]"
                        >
                            {{ item.name }}
                        </a>
                    </div>
                </div>

                <PrimaryButton
                    class="w-full justify-center"
                    @click="
                        openDevisModal();
                        toggleMobileMenu();
                    "
                >
                    Demander un devis
                </PrimaryButton>
            </div>
        </div>
    </nav>
</template>
