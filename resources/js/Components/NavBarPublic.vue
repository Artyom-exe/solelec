<script setup>
import { ref, onMounted, onBeforeUnmount, inject } from "vue";
import { Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import logo from "./logo.vue";

const navigateToSection = inject("navigateToSection");

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
    { name: "Accueil", route: "accueil", path: "/" },
    { name: "Services", anchor: "#services", route: "services-portfolio" },
    { name: "Réalisations", anchor: "#portfolio", route: "services-portfolio" },
];

const aboutSubItems = [
    { name: "Contact", anchor: "#contact", route: "accueil", path: "/" },
    {
        name: "Zones d'intervention",
        anchor: "#zones",
        route: "accueil",
        path: "/",
    },
];

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
                    <template v-for="item in navItems" :key="item.name">
                        <!-- Lien standard sans ancre -->
                        <Link
                            v-if="!item.anchor"
                            :href="route(item.route)"
                            class="font-inter text-white text-base hover:text-[#FF8C42] transition-colors duration-200"
                            :class="{
                                'text-[#FF8C42] font-medium': route().current(
                                    item.route
                                ),
                            }"
                        >
                            {{ item.name }}
                        </Link>

                        <!-- Lien avec ancre -->
                        <a
                            v-else
                            href="#"
                            @click.prevent="
                                navigateToSection(
                                    item.anchor.substring(1),
                                    item.route,
                                    item.path
                                )
                            "
                            class="font-inter text-white text-base hover:text-[#FF8C42] transition-colors duration-200"
                            :class="{
                                'text-[#FF8C42] font-medium': route().current(
                                    item.route
                                ),
                            }"
                        >
                            {{ item.name }}
                        </a>
                    </template>

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
            <!-- Hamburger (Mobile) amélioré -->
            <button
                @click="toggleMobileMenu"
                class="md:hidden flex flex-col justify-center items-center w-10 h-10 relative focus:outline-none z-50"
                aria-label="Menu"
            >
                <div
                    class="w-6 h-0.5 bg-white rounded-full transition-all duration-300 ease-in-out"
                    :class="{
                        'rotate-45 translate-y-1.5': mobileMenuOpen,
                        'mb-1.5': !mobileMenuOpen
                    }"
                ></div>
                <div
                    class="w-6 h-0.5 bg-white rounded-full transition-all duration-300 ease-in-out"
                    :class="{
                        'opacity-0': mobileMenuOpen,
                        'mb-1.5': !mobileMenuOpen
                    }"
                ></div>
                <div
                    class="w-6 h-0.5 bg-white rounded-full transition-all duration-300 ease-in-out"
                    :class="{
                        '-rotate-45 -translate-y-1.5': mobileMenuOpen
                    }"
                ></div>
            </button>
        </div>

        <!-- Menu Mobile avec sous-menus -->
        <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-full"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-full"
        >
            <div
                v-if="mobileMenuOpen"
                class="md:hidden fixed top-[56px] left-0 right-0 bottom-0 bg-white shadow-lg py-4 px-6 overflow-y-auto z-40"
            >
                <div class="flex flex-col space-y-6 mt-4">
                <!-- Animation des éléments du menu -->
                <Link
                    v-for="(item, index) in navItems"
                    :key="item.name"
                    :href="route(item.route)"
                    class="font-inter text-gray-700 py-2 hover:text-[#FF8C42] text-lg border-b border-gray-100 pb-3 transition-all duration-300 transform"
                    :style="{ 'animation-delay': index * 100 + 'ms', 'animation': 'fadeInDown 0.5s ease forwards' }"
                    :class="{
                        'text-[#FF8C42] font-medium': route().current(item.route)
                    }"
                    @click="toggleMobileMenu"
                >
                    {{ item.name }}
                </Link>

                <!-- À propos et sous-éléments -->
                <div 
                    class="py-2 border-b border-gray-100 pb-3 transition-all duration-300 transform"
                    style="animation: fadeInDown 0.5s ease forwards; animation-delay: 300ms;"
                >
                    <div class="font-inter text-gray-700 font-medium text-lg">
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
                                    item.route,
                                    item.path
                                );
                                toggleMobileMenu();
                            "
                            class="block py-2 text-gray-600 hover:text-[#FF8C42] transition-all duration-200 transform hover:translate-x-1"
                        >
                            {{ item.name }}
                        </a>
                    </div>
                </div>

                <PrimaryButton
                    class="w-full justify-center mt-4 py-3 text-lg transition-all duration-300 transform"
                    style="animation: fadeInDown 0.5s ease forwards; animation-delay: 500ms;"
                    @click="
                        openDevisModal();
                        toggleMobileMenu();
                    "
                >
                    Demander un devis
                </PrimaryButton>
                </div>
            </div>
        </transition>
    </nav>
</template>

<style scoped>
/* Animations pour le menu mobile */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
