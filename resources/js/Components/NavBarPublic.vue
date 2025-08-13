<script setup>
import { ref, onMounted, onBeforeUnmount, inject, computed, watch } from "vue";
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
const currentSection = ref("");
const pendingSection = ref(""); // Section vers laquelle on navigue intentionnellement

// Calculer si un item est actif
const isItemActive = (item) => {
    if (item.anchor) {
        // Pour les liens avec ancre
        const sectionName = item.anchor.substring(1);

        // Si on navigue intentionnellement vers cette section
        if (
            route().current(item.route) &&
            pendingSection.value === sectionName
        ) {
            return true;
        }

        // Pour tous les liens avec ancre, vérifier qu'on est sur la bonne route ET dans la bonne section
        if (
            route().current(item.route) &&
            currentSection.value === sectionName
        ) {
            return true;
        }

        return false;
    } else {
        // Pour les liens normaux, vérifier seulement la route
        return route().current(item.route);
    }
};

// Pour fermer le dropdown si on clique ailleurs
const closeDropdowns = (event) => {
    if (!event.target.closest(".about-dropdown")) {
        aboutDropdownOpen.value = false;
    }
};

// Fonction appelée quand on clique sur un lien avec ancre
const handleAnchorClick = (sectionName, route, path) => {
    // Définir la section comme "en attente" pour l'activer immédiatement
    pendingSection.value = sectionName;

    // Naviguer vers la section
    navigateToSection(sectionName, route, path);

    // Réinitialiser après un délai plus long pour laisser le temps aux animations
    setTimeout(() => {
        if (pendingSection.value === sectionName) {
            pendingSection.value = "";
        }
    }, 5000); // Augmenté à 5 secondes
};

// Fonction pour détecter manuellement quelle section est visible
const detectCurrentSection = () => {
    // Pour la page d'accueil
    if (route().current("accueil")) {
        const contactSection = document.getElementById("contact");
        const zonesSection = document.getElementById("zones");

        let bestSection = null;
        let bestScore = 0;

        // Fonction pour calculer le score de visibilité (plus proche du centre = meilleur score)
        const calculateVisibilityScore = (element) => {
            const rect = element.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const center = windowHeight / 2;

            // Si l'élément n'est pas visible, score 0
            if (rect.bottom < 0 || rect.top > windowHeight) return 0;

            // Calculer quelle partie de l'élément est visible
            const visibleTop = Math.max(0, rect.top);
            const visibleBottom = Math.min(windowHeight, rect.bottom);
            const visibleHeight = visibleBottom - visibleTop;
            const elementHeight = rect.height;
            const visibilityRatio = visibleHeight / elementHeight;

            // Calculer la distance du centre de l'élément au centre de l'écran
            const elementCenter = (visibleTop + visibleBottom) / 2;
            const distanceFromCenter = Math.abs(center - elementCenter);
            const centerScore = Math.max(0, 1 - distanceFromCenter / center);

            // Score final : visibilité * proximité du centre
            return visibilityRatio * centerScore;
        };

        if (zonesSection) {
            const score = calculateVisibilityScore(zonesSection);
            if (score > bestScore) {
                bestScore = score;
                bestSection = "zones";
            }
        }

        if (contactSection) {
            const score = calculateVisibilityScore(contactSection);
            if (score > bestScore) {
                bestScore = score;
                bestSection = "contact";
            }
        }

        if (bestSection && bestScore > 0.1) {
            currentSection.value = bestSection;
            // Réinitialiser pendingSection seulement si on a un excellent score de visibilité
            if (pendingSection.value === bestSection && bestScore > 0.5) {
                pendingSection.value = "";
            }
            return;
        }

        // Si on a un pendingSection mais qu'aucune section n'est bien visible, on garde le pending
        if (pendingSection.value && bestScore < 0.2) {
            currentSection.value = pendingSection.value;
        }
    }

    // Pour la page services-portfolio
    if (route().current("services-portfolio")) {
        const servicesSection = document.getElementById("services");
        const portfolioSection = document.getElementById("portfolio");

        let bestSection = null;
        let bestScore = 0;

        const calculateVisibilityScore = (element) => {
            const rect = element.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            const center = windowHeight / 2;

            if (rect.bottom < 0 || rect.top > windowHeight) return 0;

            const visibleTop = Math.max(0, rect.top);
            const visibleBottom = Math.min(windowHeight, rect.bottom);
            const visibleHeight = visibleBottom - visibleTop;
            const elementHeight = rect.height;
            const visibilityRatio = visibleHeight / elementHeight;

            const elementCenter = (visibleTop + visibleBottom) / 2;
            const distanceFromCenter = Math.abs(center - elementCenter);
            const centerScore = Math.max(0, 1 - distanceFromCenter / center);

            return visibilityRatio * centerScore;
        };

        if (servicesSection) {
            const score = calculateVisibilityScore(servicesSection);
            if (score > bestScore) {
                bestScore = score;
                bestSection = "services";
            }
        }

        if (portfolioSection) {
            const score = calculateVisibilityScore(portfolioSection);
            if (score > bestScore) {
                bestScore = score;
                bestSection = "portfolio";
            }
        }

        if (bestSection && bestScore > 0.1) {
            currentSection.value = bestSection;
            // Réinitialiser pendingSection seulement si on a un excellent score de visibilité
            if (pendingSection.value === bestSection && bestScore > 0.5) {
                pendingSection.value = "";
            }
            return;
        }

        // Si on a un pendingSection mais qu'aucune section n'est bien visible, on garde le pending
        if (pendingSection.value && bestScore < 0.2) {
            currentSection.value = pendingSection.value;
        }
    }
};

// Observer les sections visibles pour mettre à jour l'état actif
const observeSections = () => {
    const sections = document.querySelectorAll("section[id], div[id]");
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting && entry.intersectionRatio > 0.3) {
                    // Seulement mettre à jour si il n'y a pas de pendingSection ou si c'est la même section
                    if (
                        !pendingSection.value ||
                        pendingSection.value === entry.target.id
                    ) {
                        currentSection.value = entry.target.id;
                        // Réinitialiser pendingSection si on a atteint la section cible
                        if (pendingSection.value === entry.target.id) {
                            pendingSection.value = "";
                        }
                    }
                }
            });
        },
        {
            threshold: [0.1, 0.3, 0.5, 0.7],
            rootMargin: "-80px 0px -80px 0px",
        }
    );

    sections.forEach((section) => {
        observer.observe(section);
    });
    return observer;
};

onMounted(() => {
    document.addEventListener("click", closeDropdowns);

    // Démarrer l'observation des sections après un délai pour s'assurer que le DOM est prêt
    setTimeout(() => {
        observeSections();
        // Détecter immédiatement la section courante après le chargement
        detectCurrentSection();
    }, 500);

    // Ajouter une détection supplémentaire après un délai plus long pour les navigations
    setTimeout(() => {
        detectCurrentSection();
    }, 1000);

    // Ajouter un listener pour détecter les changements de scroll manuellement comme backup
    const handleScroll = () => {
        detectCurrentSection();
    };

    window.addEventListener("scroll", handleScroll);

    // Cleanup dans onBeforeUnmount
    onBeforeUnmount(() => {
        window.removeEventListener("scroll", handleScroll);
    });
});

// Watcher pour détecter les changements de route et forcer une nouvelle détection
watch(
    () => route().current(),
    () => {
        setTimeout(() => {
            detectCurrentSection();
        }, 300);
        // Ajouter un délai supplémentaire pour les navigations avec scroll
        setTimeout(() => {
            detectCurrentSection();
        }, 800);
    },
    { immediate: true }
);

// Watcher pour détecter les changements d'URL (y compris les ancres)
watch(
    () => window.location.href,
    () => {
        setTimeout(() => {
            detectCurrentSection();
        }, 500);
        setTimeout(() => {
            detectCurrentSection();
        }, 1200);
    }
);

// Écouter les événements de navigation avec scroll terminé
onMounted(() => {
    // Écouter la fin du scroll pour forcer une détection
    let scrollTimeout;
    const handleScrollEnd = () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            detectCurrentSection();
        }, 150);
    };

    window.addEventListener("scroll", handleScrollEnd);

    onBeforeUnmount(() => {
        window.removeEventListener("scroll", handleScrollEnd);
        clearTimeout(scrollTimeout);
    });
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
    {
        name: "Services",
        anchor: "#services",
        route: "services-portfolio",
        path: "/services-portfolio",
    },
    {
        name: "Réalisations",
        anchor: "#portfolio",
        route: "services-portfolio",
        path: "/services-portfolio",
    },
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
                        <!-- Lien standard sans ancre -->
                        <Link
                            v-if="!item.anchor"
                            :href="route(item.route)"
                            class="font-inter text-base transition-colors duration-200"
                            :class="{
                                'text-[#FF8C42] font-medium':
                                    isItemActive(item),
                                'text-white hover:text-[#FF8C42]':
                                    !isItemActive(item),
                            }"
                        >
                            {{ item.name }}
                        </Link>

                        <!-- Lien avec ancre -->
                        <a
                            v-else
                            href="#"
                            @click.prevent="
                                handleAnchorClick(
                                    item.anchor.substring(1),
                                    item.route,
                                    item.path
                                )
                            "
                            class="font-inter text-base transition-colors duration-200"
                            :class="{
                                'text-[#FF8C42] font-medium':
                                    isItemActive(item),
                                'text-white hover:text-[#FF8C42]':
                                    !isItemActive(item),
                            }"
                        >
                            {{ item.name }}
                        </a>
                    </template>

                    <!-- Menu À propos avec dropdown -->
                    <div class="relative about-dropdown">
                        <button
                            @click="toggleAboutDropdown"
                            class="font-inter transition-colors duration-200 flex items-center"
                            :class="{
                                'text-[#FF8C42] font-medium':
                                    aboutSubItems.some((subItem) =>
                                        isItemActive(subItem)
                                    ),
                                'text-white hover:text-[#FF8C42]':
                                    !aboutSubItems.some((subItem) =>
                                        isItemActive(subItem)
                                    ),
                            }"
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
                                    handleAnchorClick(
                                        item.anchor.substring(1),
                                        item.route,
                                        item.path
                                    );
                                    aboutDropdownOpen = false;
                                "
                                class="block px-4 py-2 transition-colors duration-200"
                                :class="{
                                    'text-[#FF8C42] bg-gray-50 font-medium':
                                        isItemActive(item),
                                    'text-gray-700 hover:bg-gray-100 hover:text-[#FF8C42]':
                                        !isItemActive(item),
                                }"
                            >
                                {{ item.name }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Groupe boutons droite (mobile) -->
            <div class="flex items-center justify-center gap-4 md:gap-8 h-full">
                <PrimaryButton @click="openDevisModal" navStyle>
                    Devis
                </PrimaryButton>
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
                    <template
                        v-for="(item, index) in navItems"
                        :key="item.name"
                    >
                        <!-- Lien standard sans ancre -->
                        <Link
                            v-if="!item.anchor"
                            :href="route(item.route)"
                            class="font-inter py-2 text-lg border-b border-gray-100 pb-3 transition-all duration-300 transform"
                            :style="{
                                'animation-delay': index * 100 + 'ms',
                                animation: 'fadeInDown 0.5s ease forwards',
                            }"
                            :class="{
                                'text-[#FF8C42] font-medium':
                                    isItemActive(item),
                                'text-gray-700 hover:text-[#FF8C42]':
                                    !isItemActive(item),
                            }"
                            @click="toggleMobileMenu"
                        >
                            {{ item.name }}
                        </Link>

                        <!-- Lien avec ancre -->
                        <a
                            v-else
                            href="#"
                            @click.prevent="
                                handleAnchorClick(
                                    item.anchor.substring(1),
                                    item.route,
                                    item.path
                                );
                                toggleMobileMenu();
                            "
                            class="font-inter py-2 text-lg border-b border-gray-100 pb-3 transition-all duration-300 transform"
                            :style="{
                                'animation-delay': index * 100 + 'ms',
                                animation: 'fadeInDown 0.5s ease forwards',
                            }"
                            :class="{
                                'text-[#FF8C42] font-medium':
                                    isItemActive(item),
                                'text-gray-700 hover:text-[#FF8C42]':
                                    !isItemActive(item),
                            }"
                        >
                            {{ item.name }}
                        </a>
                    </template>

                    <!-- À propos et sous-éléments -->
                    <div
                        class="py-2 border-b border-gray-100 pb-3 transition-all duration-300 transform"
                        style="
                            animation: fadeInDown 0.5s ease forwards;
                            animation-delay: 300ms;
                        "
                    >
                        <div
                            class="font-inter text-gray-700 font-medium text-lg"
                        >
                            À propos
                        </div>
                        <div class="pl-4 mt-2 space-y-2">
                            <a
                                v-for="item in aboutSubItems"
                                :key="item.name"
                                href="#"
                                @click.prevent="
                                    handleAnchorClick(
                                        item.anchor.substring(1),
                                        item.route,
                                        item.path
                                    );
                                    toggleMobileMenu();
                                "
                                class="block py-2 transition-all duration-200 transform hover:translate-x-1"
                                :class="{
                                    'text-[#FF8C42] font-medium':
                                        isItemActive(item),
                                    'text-gray-600 hover:text-[#FF8C42]':
                                        !isItemActive(item),
                                }"
                            >
                                {{ item.name }}
                            </a>
                        </div>
                    </div>

                    <PrimaryButton
                        class="w-full justify-center mt-4 py-3 text-lg transition-all duration-300 transform"
                        style="
                            animation: fadeInDown 0.5s ease forwards;
                            animation-delay: 500ms;
                        "
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
