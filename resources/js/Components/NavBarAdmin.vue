<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Link } from "@inertiajs/vue3";
import logo from "./logo.vue";

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

const navItems = [
    { name: "Tableau de bord", route: "dashboard" },
    { name: "Clients", route: "clients" },
    { name: "Devis", route: "devis" },
    { name: "Interventions", route: "interventions" },
];
</script>

<template>
    <nav
        class="fixed top-0 left-0 right-0 bg-[#2D2D2D] px-16 justify-center flex-col items-center py-4 z-50"
    >
        <div class="flex justify-center items-center gap-8 self-stretch">
            <!-- Logo (maintenant en position absolute) -->
            <div class="absolute left-16">
                <logo />
            </div>

            <!-- Navigation principale (Desktop) -->
            <div class="hidden md:flex items-center justify-center">
                <div class="flex gap-8">
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
                    </template>
                </div>
            </div>
            <!-- Hamburger (Mobile) -->
            <button
                @click="toggleMobileMenu"
                class="md:hidden flex items-center text-white absolute right-16"
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
                    :href="route(item.route)"
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
            </div>
        </div>
    </nav>
</template>
