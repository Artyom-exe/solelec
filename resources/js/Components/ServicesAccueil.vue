<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import LogoLoader from "@/Components/LogoLoader.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

// Propriété pour détecter si on est sur mobile (< 1280px = taille xl en Tailwind)
const isMobile = ref(false);

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
const activeIndex = ref(0); // Pour la page accueil, activer le premier service par défaut
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
    // Sur desktop uniquement, activer le survol
    if (!isMobile.value) {
        activeIndex.value = index;
    }
};

const handleMouseLeave = () => {
    // Sur desktop uniquement, revenir au premier service
    if (!isMobile.value) {
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

const toggleActive = (index, event) => {
    const previousIndex = activeIndex.value;

    if (isMobile.value) {
        // Sur mobile: activer seulement sans possibilité de désactiver
        activeIndex.value = index;
    } else {
        // Sur desktop: fonctionnement normal toggle on/off
        activeIndex.value = activeIndex.value === index ? 0 : index;
    }

    // Si on active une carte (pas désactivation), centrer la carte
    if (activeIndex.value !== 0 && previousIndex !== activeIndex.value) {
        centerCardInView(event.currentTarget);
    }
};

// Fonction pour naviguer vers la page services-portfolio avec le service concerné
const navigateToServiceDetail = (serviceId) => {
    router.visit(`/services-portfolio#service-${serviceId}`, {
        preserveState: true,
    });
};

// Fonction pour vérifier et mettre à jour l'état mobile
const checkIfMobile = () => {
    isMobile.value = window.innerWidth < 1280; // 1280px correspond à la classe 'xl:' de Tailwind
};

// Configuration des écouteurs d'événements de redimensionnement
onMounted(() => {
    checkIfMobile();
    window.addEventListener("resize", checkIfMobile);
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", checkIfMobile);
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
            @touchstart="isMobile ? toggleActive(index, $event) : false"
            @keydown.enter.prevent="
                activeIndex === index
                    ? navigateToServiceDetail(service.id)
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
@media (max-width: 1279px) {
    article {
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }
}
</style>
