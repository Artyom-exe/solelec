<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Link } from "@inertiajs/vue3";
import logo from "./logo.vue";

const mobileMenuOpen = ref(false);

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
        class="flex fixed top-0 left-0 right-0 bg-[#2D2D2D] md:px-16 px-5 justify-center flex-col items-center md:h-[72px] h-[64px] z-50"
    >
        <div class="flex justify-between items-center md:gap-8 self-stretch">
            <!-- Logo -->
            <logo />

            <!-- Navigation principale (Desktop) -->
            <div class="hidden md:flex items-center space-x-8">
                <div class="flex space-x-6">
                    <!-- Items de navigation standard -->
                    <template v-for="item in navItems" :key="item.name">
                        <Link
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

            <!-- Hamburger (Mobile) avec animation cross - masqué sur desktop avec v-if -->
            <div class="md:hidden">
                <button
                    @click="toggleMobileMenu"
                    class="hamburger-icon cross-animation z-[100] my-auto"
                    :class="{ open: mobileMenuOpen }"
                    aria-label="Menu"
                >
                    <span></span>
                </button>
            </div>
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
                class="md:hidden fixed top-[64px] left-0 right-0 bottom-0 bg-white shadow-lg py-4 px-6 overflow-y-auto z-50"
            >
                <div class="flex flex-col space-y-6 mt-4">
                    <!-- Animation des éléments du menu -->
                    <Link
                        v-for="(item, index) in navItems"
                        :key="item.name"
                        :href="route(item.route)"
                        class="font-inter text-gray-700 py-2 hover:text-[#FF8C42] text-lg border-b border-gray-100 pb-3 transition-all duration-300 transform"
                        :style="{
                            'animation-delay': index * 100 + 'ms',
                            animation: 'fadeInDown 0.5s ease forwards',
                        }"
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

/* Animation du hamburger */
.hamburger-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 30px;
    position: relative;
    width: 30px;
    margin: auto 0;
}

.hamburger-icon span {
    border-radius: 3px;
    background-color: white;
    display: block;
    height: 3px;
    margin: 0 auto;
    position: relative;
    width: 30px;
}

.hamburger-icon span:before {
    border-radius: 3px;
    background-color: white;
    content: "";
    display: block;
    height: 3px;
    margin-top: -9px;
    position: absolute;
    width: 30px;
}

.hamburger-icon span:after {
    border-radius: 3px;
    background-color: white;
    content: "";
    display: block;
    height: 3px;
    margin-top: 9px;
    position: absolute;
    width: 30px;
}

/* Animation */
.cross-animation span {
    transition-delay: 0.2s;
    transition-duration: 0s;
}

.cross-animation span:before {
    transition-delay: 0.2s, 0s;
    transition-duration: 0.2s;
    transition-property: margin, transform;
}

.cross-animation span:after {
    transition-delay: 0.2s, 0s;
    transition-duration: 0.2s;
    transition-property: margin, transform;
}

.cross-animation:hover span:before {
    margin-top: -12px;
}

.cross-animation:hover span:after {
    margin-top: 12px;
}

.cross-animation.open span {
    transition-delay: 0.2s;
    background-color: rgba(255, 255, 255, 0);
}

.cross-animation.open span:before {
    transform: rotate(45deg);
    transition-delay: 0s, 0.2s;
    margin-top: 0;
}

.cross-animation.open span:after {
    transform: rotate(-45deg);
    transition-delay: 0s, 0.2s;
    margin-top: 0;
}
</style>
