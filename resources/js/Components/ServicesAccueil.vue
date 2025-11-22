<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import LogoLoader from "@/Components/LogoLoader.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

// Propriété pour détecter si on est sur mobile/tactile
const isMobile = ref(false);
const isTouchDevice = ref(false);

// Pointer interaction state to distinguish tap vs scroll
const pointerStartX = ref(0);
const pointerStartY = ref(0);
const pointerMoved = ref(false);
const lastPointerToggleAt = ref(0);
const POINTER_MOVE_THRESHOLD = 10; // px

// Ref to component root to detect outside clicks
const rootRef = ref(null);

const onDocumentPointerDown = (e) => {
    // Only apply outside-click closing on mobile/touch devices
    if (!isMobile.value && !isTouchDevice.value) return;
    const root = rootRef.value;
    if (!root) return;
    const target = e.target;
    // If click/touch is outside our root, close active card
    if (target && !root.contains(target)) {
        // Only change state if something is active
        if (activeIndex.value !== -1 && activeIndex.value !== 0) {
            activeIndex.value = -1;
        }
    }
};

const props = defineProps({
    limit: {
        type: Number,
        default: 3,
    },
    serviceIds: {
        type: Array,
        default: () => [],
    },
    // Hauteur personnalisable
    height: {
        type: String,
        default: "340px",
    },
});

const services = ref([]);
const activeIndex = ref(0); // Pour la page accueil, par défaut on active le premier service (peut devenir -1 sur mobile)
const loading = ref(true);

// Calculer les services à afficher
const displayedServices = computed(() => {
    if (props.serviceIds && props.serviceIds.length > 0) {
        // Filtre les services par les IDs spécifiés dans le même ordre
        return props.serviceIds
            .map((id) => services.value.find((service) => service.id === id))
            .filter(Boolean);
    } else {
        return services.value;
    }
});

// Cache global pour les services (partagé entre toutes les instances du composant)
const servicesCache = {
    data: null,
    timestamp: null,
};

const fetchServices = async () => {
    // Si nous avons des données en cache récentes (moins de 5 minutes), les utiliser
    const now = Date.now();
    const cacheExpiration = 5 * 60 * 1000; // 5 minutes en millisecondes

    if (
        servicesCache.data &&
        servicesCache.timestamp &&
        now - servicesCache.timestamp < cacheExpiration
    ) {
        services.value = servicesCache.data;
        loading.value = false;
        return;
    }

    loading.value = true;
    try {
        const response = await axios.get("services");
        services.value = response.data;

        // Mettre à jour le cache
        servicesCache.data = response.data;
        servicesCache.timestamp = now;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

// Gestion des entrées/sorties de souris (desktop uniquement)
const handleMouseEnter = (index) => {
    // Sur desktop uniquement (pas tactile et écran large), activer le survol
    if (!isMobile.value && !isTouchDevice.value) {
        activeIndex.value = index;
    }
};

const handleMouseLeave = () => {
    // Sur desktop uniquement (pas tactile et écran large), revenir au premier service
    if (!isMobile.value && !isTouchDevice.value) {
        activeIndex.value = 0;
    }
};

const centerCardInView = (element) => {
    if (element) {
        // Augmenter le délai pour s'assurer que la transition d'expansion de la carte est complète
        setTimeout(() => {
            const rect = element.getBoundingClientRect();
            const elementTop = rect.top + window.scrollY;
            const middleScreen = window.innerHeight / 2;
            window.scrollTo({
                top: elementTop - middleScreen + 150, // +150px pour ajuster
                behavior: "smooth",
            });
        }, 300);
    }
};

const toggleActiveImmediate = (index, event) => {
    // helper preserving previous behavior when directly toggled
    const previousIndex = activeIndex.value;
    let newIndex;
    if (isMobile.value || isTouchDevice.value) {
        // Sur mobile/tactile, ne pas refermer la carte quand on clique dessus
        // (comportement uniforme avec desktop : cliquer garde la carte active)
        newIndex = index;
    } else {
        // desktop non tactile: do NOT close the card when clicking the same index
        newIndex = index;
    }

    activeIndex.value = newIndex;

    // Only center when we activated a card (not when we deactivated)
    if (newIndex !== -1 && newIndex !== 0 && previousIndex !== newIndex) {
        centerCardInView(event.currentTarget);
    } else if (
        newIndex >= 0 &&
        previousIndex !== newIndex &&
        !isMobile.value &&
        !isTouchDevice.value
    ) {
        // desktop case where activeIndex becomes a positive index (not 0)
        centerCardInView(event.currentTarget);
    }
};

// Pointer handlers
const handlePointerDown = (index, e) => {
    pointerMoved.value = false;
    if (e && typeof e.clientX !== "undefined") {
        pointerStartX.value = e.clientX;
        pointerStartY.value = e.clientY;
    }
    // keep visual state minimal (no immediate activation)
};

const handlePointerMove = (e) => {
    if (!e || typeof e.clientX === "undefined") return;
    const dx = Math.abs(e.clientX - pointerStartX.value);
    const dy = Math.abs(e.clientY - pointerStartY.value);
    if (dx > POINTER_MOVE_THRESHOLD || dy > POINTER_MOVE_THRESHOLD) {
        pointerMoved.value = true;
    }
};

const handlePointerUp = (index, event) => {
    if (!pointerMoved.value) {
        // Considered a tap -> activate on release
        toggleActiveImmediate(index, event);
        lastPointerToggleAt.value = Date.now();
    }
    pointerMoved.value = false;
};

const handlePointerCancel = () => {
    pointerMoved.value = false;
};

// click fallback that ignores clicks immediately after pointer-triggered toggles
const handleClick = (index, event) => {
    const now = Date.now();
    if (now - lastPointerToggleAt.value < 500) return;
    toggleActiveImmediate(index, event);
};

// Fonction pour naviguer vers la page services-portfolio avec le service concerné
const navigateToServiceDetail = (serviceId) => {
    router.visit(`/services-portfolio#service-${serviceId}`, {
        preserveState: true,
    });
};

// Fonction pour vérifier et mettre à jour l'état mobile et tactile
const checkDeviceCapabilities = () => {
    // Vérifier la taille d'écran (mobile = < 1280px)
    isMobile.value = window.innerWidth < 1280;

    // Vérifier si c'est un appareil tactile
    isTouchDevice.value =
        "ontouchstart" in window ||
        navigator.maxTouchPoints > 0 ||
        navigator.msMaxTouchPoints > 0;

    // Si on est sur mobile/tactile, fermer toutes les cartes par défaut (-1 = aucune)
    if (isMobile.value || isTouchDevice.value) {
        activeIndex.value = -1;
    } else {
        // Sur desktop non tactile : si aucune carte n'est active, activer la première
        if (activeIndex.value === -1) {
            activeIndex.value = 0;
        }
    }
};

// Configuration des écouteurs d'événements de redimensionnement
onMounted(() => {
    checkDeviceCapabilities();
    window.addEventListener("resize", checkDeviceCapabilities);
    // register outside click listener
    document.addEventListener("pointerdown", onDocumentPointerDown);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", checkDeviceCapabilities);
    document.removeEventListener("pointerdown", onDocumentPointerDown);
});

fetchServices();
</script>

<template>
    <!-- Indicateur de chargement avec le logo -->
    <div v-if="loading" class="w-full flex justify-center items-center py-8">
        <LogoLoader />
    </div>

    <section
        v-else
        ref="rootRef"
        class="flex w-full flex-col xl:flex-row gap-6"
        aria-label="Liste des services"
    >
        <article
            v-for="(service, index) in displayedServices"
            :key="service.id"
            class="group relative flex rounded-lg border border-[rgba(13,7,3,0.15)] bg-[#FAF8F3] overflow-hidden shadow-md w-full flex-col xl:flex-row mb-3 transition-all duration-500"
            :class="[
                // Largeur et couleur conditionnelles
                activeIndex === index
                    ? 'xl:w-2/3 text-white border-none active'
                    : 'xl:w-1/3 text-[#0D0703]',
                // Classes de hauteur pour mobile uniquement
                { 'h-[518px]': activeIndex === index },
                { 'h-[220px]': activeIndex !== index },
            ]"
            :style="{
                backgroundColor: activeIndex === index ? '#2D2D2D' : '',
            }"
            @mouseenter="handleMouseEnter(index)"
            @mouseleave="handleMouseLeave"
            @click="handleClick(index, $event)"
            @pointerdown.passive="handlePointerDown(index, $event)"
            @pointermove.passive="handlePointerMove($event)"
            @pointerup.passive="handlePointerUp(index, $event)"
            @pointercancel="handlePointerCancel()"
            @keydown.enter.prevent="
                activeIndex === index
                    ? navigateToServiceDetail(service.id)
                    : isMobile || isTouchDevice
                    ? toggleActiveImmediate(index, $event)
                    : handleMouseEnter(index)
            "
            tabindex="0"
        >
            <!-- Section Image (gauche sur desktop, dessus sur mobile) -->
            <div
                class="overflow-hidden border-[#FF8C42] flex-shrink-0"
                :class="{
                    'xl:w-1/2 xl:h-full h-1/2 w-full xl:border-r-8 border-b-8 xl:border-b-0':
                        activeIndex === index,
                    'xl:w-0 w-0 opacity-0 h-0': activeIndex !== index,
                }"
            >
                <img
                    v-if="service.image"
                    :src="service.image"
                    :alt="service.title"
                    class="w-full h-full object-cover"
                />
            </div>

            <!-- Contenu (titre, description et icône) -->
            <div
                class="flex flex-col justify-center gap-4 p-6 h-full"
                :class="{
                    'xl:w-2/3 w-full': activeIndex === index,
                    'w-full': activeIndex !== index,
                }"
            >
                <!-- Icône - masquée sur mobile si actif -->
                <img
                    v-if="service.icon"
                    :src="service.icon"
                    :alt="`Icône ${service.title}`"
                    class="w-10 h-10 object-contain transition-all duration-300"
                    :class="{
                        'grayscale-0 brightness-0 invert':
                            activeIndex === index,
                        grayscale: activeIndex !== index,
                        'hidden xl:block': activeIndex === index,
                    }"
                />

                <!-- Titre et description -->
                <div class="flex flex-col gap-4">
                    <h3
                        class="font-poppins font-medium capitalize text-[20px] md:text-xl lg:text-2xl"
                    >
                        {{ service.title }}
                    </h3>
                    <p
                        class="text-sm leading-relaxed font-inter"
                        :class="{
                            'text-white/80': activeIndex === index,
                            'text-[#0D0703]/80': activeIndex !== index,
                        }"
                    >
                        {{ service.short_description }}
                    </p>
                </div>

                <!-- Bouton "En savoir plus" (visible uniquement sur l'élément actif) -->
                <primary-button
                    v-if="activeIndex === index"
                    @click.stop="navigateToServiceDetail(service.id)"
                    class="xl:w-max"
                >
                    En savoir plus
                </primary-button>
            </div>
        </article>
    </section>
</template>

<style scoped>
article {
    transition: height 0.5s ease-in-out, background-color 0.5s ease-in-out,
        width 0.5s ease-in-out;
}

/* Ajouter une ombre plus visible sur les éléments actifs pour renforcer l'effet */
article.active {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

/* Utiliser les media queries CSS pour appliquer la hauteur personnalisée en version desktop */
@media (min-width: 1280px) {
    article {
        height: 340px !important;
    }
}

/* Améliorer le focus pour l'accessibilité */
article:focus {
    outline: 2px solid #ff8c42;
    outline-offset: 2px;
}

/* Supprimer l'effet de tap highlight sur mobile qui peut interférer avec nos styles */
@media (max-width: 1279px), (pointer: coarse) {
    article {
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }
}
</style>
