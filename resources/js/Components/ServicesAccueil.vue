<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import LogoLoader from "@/Components/LogoLoader.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

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
const isMobile = ref(false);

// Fonction pour détecter si on est en version mobile (jusqu'à xl)
const checkIfMobile = () => {
    isMobile.value = window.innerWidth < 1280; // Seuil xl (1280px)
};

// Ajouter un écouteur d'événement pour redimensionnement
onMounted(() => {
    checkIfMobile();
    window.addEventListener('resize', checkIfMobile);
});

// Supprimer l'écouteur quand le composant est détruit
onUnmounted(() => {
    window.removeEventListener('resize', checkIfMobile);
});

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
    timestamp: null
};

const fetchServices = async () => {
    // Si nous avons des données en cache récentes (moins de 5 minutes), les utiliser
    const now = Date.now();
    const cacheExpiration = 5 * 60 * 1000; // 5 minutes en millisecondes

    if (servicesCache.data && servicesCache.timestamp && (now - servicesCache.timestamp < cacheExpiration)) {
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

// Gestion des entrées/sorties de souris
const handleMouseEnter = (index) => {
    activeIndex.value = index;
};

const handleMouseLeave = () => {
    // Sur la landing page, revenir au premier service
    // Mais uniquement si on est sur desktop, pour mobile garder le dernier élément actif après le tap
    if (!isMobile.value) {
        activeIndex.value = 0;
    }
};

const centerCardInView = (element, index) => {
    if (element && isMobile.value) {
        // Augmenter le délai pour s'assurer que la transition d'expansion de la carte est complète
        // Les transitions CSS prennent 500ms (0.5s) d'après les styles
        setTimeout(() => {
            // Position de l'élément par rapport au haut de la page
            const elementTop = element.getBoundingClientRect().top + window.scrollY;
            // Début de l'élément + décalage pour placer le centre de la carte au milieu de l'écran
            const middleScreen = window.innerHeight / 2;

            // Défiler vers la position absolue de l'élément
            window.scrollTo({
                top: elementTop - middleScreen + 150, // +150px pour ajuster
                behavior: 'smooth'
            });

        }, 300); // Augmenter le délai pour s'assurer que les animations sont complètes
    }
};

const toggleMobileActive = (index, event) => {
    // Sur mobile, on veut un comportement de type toggle à chaque tap
    if (isMobile.value) {
        const previousIndex = activeIndex.value;
        activeIndex.value = activeIndex.value === index ? 0 : index;

        // Si on active une carte (pas désactivation), centrer la carte
        if (activeIndex.value !== 0 && previousIndex !== activeIndex.value) {
            // L'élément parent est l'élément article (la carte)
            centerCardInView(event.currentTarget, index);
        }
    }
};

// Fonction pour naviguer vers la page services-portfolio avec le service concerné
const navigateToServiceDetail = (serviceId) => {
    // Utiliser le router Inertia pour naviguer vers la page services-portfolio avec l'ID du service
    router.visit("/services-portfolio#services", {
        data: { serviceId: serviceId },
        preserveState: true,
    });
};

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
        :class="{ 'flex-col': isMobile }"
        aria-label="Liste des services"
    >
        <article
            v-for="(service, index) in displayedServices"
            :key="service.id"
            class="group relative flex rounded-lg border border-[rgba(13,7,3,0.15)] bg-[#FAF8F3] transition duration-500 overflow-hidden shadow-md"
            :class="[
                // Version desktop: largeur
                activeIndex === index
                    ? 'xl:w-2/3 text-white border-none active'
                    : 'xl:w-1/3 text-[#0D0703]',
                // En version mobile, la classe w-full s'applique toujours
                'w-full',
                // Mise en page flexible (colonne sur mobile, ligne sur desktop)
                'flex-col xl:flex-row'
            ]"
            :style="{
                // Appliquer la hauteur dynamique uniquement en version mobile
                height: isMobile ? (activeIndex === index ? '518px' : '220px') : height,
                backgroundColor: activeIndex === index ? '#2D2D2D' : '',
                transition: isMobile ? 'height 0.5s ease, background-color 0.5s ease' : 'all 0.5s ease'
            }"
            @mouseenter="handleMouseEnter(index)"
            @mouseleave="handleMouseLeave"
            @click="isMobile ? toggleMobileActive(index, $event) : handleMouseEnter(index)"
            @keydown.enter.prevent="activeIndex === index ? navigateToServiceDetail(service.id) : handleMouseEnter(index)"
        >
            <!-- Section Image (gauche sur desktop, dessus sur mobile) -->
            <div
                class="overflow-hidden border-[#FF8C42] flex-shrink-0"
                :class="{
                    'xl:w-1/2 xl:h-full h-1/2 w-full xl:border-r-8 border-b-8 xl:border-b-0': activeIndex === index,
                    'xl:w-0 w-0 opacity-0 h-0': activeIndex !== index
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
                    'w-full': activeIndex !== index
                }"
            >
                <!-- Icône (masquée quand étirée en version mobile) -->
                <img
                    v-if="service.icon && (!isMobile || activeIndex !== index)"
                    :src="service.icon"
                    :alt="`Icône ${service.title}`"
                    class="w-10 h-10 object-contain transition-all duration-300"
                    :class="{
                        'grayscale-0 brightness-0 invert': activeIndex === index,
                        'grayscale': activeIndex !== index,
                    }"
                />

                <!-- Titre et description -->
                <div class="flex flex-col gap-4">
                    <h3 class="font-poppins font-medium capitalize" :class="{ 'text-xl md:text-2xl': !isMobile, 'text-[20px]': isMobile }">
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
/* Style spécifique pour la transition en version mobile */
@media (max-width: 1279px) {
  article {
    transition: height 0.5s ease-in-out, background-color 0.5s ease-in-out;
    margin-bottom: 12px;
  }

  /* Ajouter une ombre plus visible sur les éléments actifs pour renforcer l'effet */
  article.active {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  }
}
</style>
