<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Link, router } from "@inertiajs/vue3";
import logo from "./logo.vue";

const mobileMenuOpen = ref(false);
const profileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const toggleProfileMenu = () => {
    profileMenuOpen.value = !profileMenuOpen.value;
};

const logout = () => {
    router.post(route("logout"));
};

// Fermer le menu profil en cliquant à l'extérieur
const closeProfileMenu = (event) => {
    if (!event.target.closest(".profile-dropdown")) {
        profileMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", closeProfileMenu);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", closeProfileMenu);
});

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

            <!-- Navigation principale (Desktop) - Centrée -->
            <div class="hidden md:flex items-center justify-center flex-1">
                <div class="flex space-x-8">
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

            <!-- Menu profil (Desktop) -->
            <div class="hidden md:flex items-center relative profile-dropdown">
                <button
                    @click="toggleProfileMenu"
                    class="flex items-center space-x-2 text-white hover:text-[#FF8C42] transition-colors duration-200"
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
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                    </svg>
                    <span class="font-inter text-sm">{{
                        $page.props.auth?.user?.name || "Profil"
                    }}</span>
                    <svg
                        class="w-4 h-4 transition-transform duration-200"
                        :class="{ 'rotate-180': profileMenuOpen }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </button>

                <!-- Dropdown du profil -->
                <transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-[-10px]"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-[-10px]"
                >
                    <div
                        v-if="profileMenuOpen"
                        class="absolute right-0 top-full mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50"
                    >
                        <!-- Info utilisateur -->
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $page.props.auth?.user?.name }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $page.props.auth?.user?.email }}
                            </p>
                        </div>

                        <!-- Liens du profil -->
                        <div class="py-1">
                            <Link
                                :href="route('profile.admin')"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-[#FF8C42] transition-colors duration-200"
                                @click="profileMenuOpen = false"
                            >
                                <svg
                                    class="w-4 h-4 mr-3"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                                Gérer mon profil
                            </Link>

                            <div class="border-t border-gray-100 my-1"></div>

                            <button
                                @click="logout"
                                class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 hover:text-red-800 transition-colors duration-200"
                            >
                                <svg
                                    class="w-4 h-4 mr-3"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                    />
                                </svg>
                                Se déconnecter
                            </button>
                        </div>
                    </div>
                </transition>
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
                    <!-- Informations utilisateur -->
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-10 h-10 bg-[#FF8C42] rounded-full flex items-center justify-center"
                            >
                                <svg
                                    class="w-5 h-5 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ $page.props.auth?.user?.name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $page.props.auth?.user?.email }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation principale -->
                    <div class="space-y-4">
                        <h3
                            class="text-sm font-semibold text-gray-400 uppercase tracking-wider"
                        >
                            Navigation
                        </h3>
                        <Link
                            v-for="(item, index) in navItems"
                            :key="item.name"
                            :href="route(item.route)"
                            class="flex items-center font-inter text-gray-700 py-2 hover:text-[#FF8C42] text-lg border-b border-gray-100 pb-3 transition-all duration-300 transform"
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

                    <!-- Section Profil -->
                    <div class="space-y-4 border-t border-gray-200 pt-4">
                        <h3
                            class="text-sm font-semibold text-gray-400 uppercase tracking-wider"
                        >
                            Profil
                        </h3>

                        <Link
                            :href="route('profile.show')"
                            class="flex items-center space-x-3 text-gray-700 py-2 hover:text-[#FF8C42] transition-colors duration-200"
                            @click="toggleMobileMenu"
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
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                            <span>Modifier le profil</span>
                        </Link>

                        <Link
                            :href="route('profile.show')"
                            class="flex items-center space-x-3 text-gray-700 py-2 hover:text-[#FF8C42] transition-colors duration-200"
                            @click="toggleMobileMenu"
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
                                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                />
                            </svg>
                            <span>Changer l'email</span>
                        </Link>

                        <Link
                            :href="route('profile.show')"
                            class="flex items-center space-x-3 text-gray-700 py-2 hover:text-[#FF8C42] transition-colors duration-200"
                            @click="toggleMobileMenu"
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
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                />
                            </svg>
                            <span>Changer le mot de passe</span>
                        </Link>

                        <button
                            @click="logout"
                            class="flex items-center space-x-3 text-red-600 py-2 hover:text-red-800 transition-colors duration-200 w-full text-left"
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
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                />
                            </svg>
                            <span>Se déconnecter</span>
                        </button>
                    </div>
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
