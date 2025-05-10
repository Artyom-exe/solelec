<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import LogoLoader from "@/Components/LogoLoader.vue";

const props = defineProps({
    limit: {
        type: Number,
        default: 3,
    },
    serviceIds: {
        type: Array,
        default: () => [],
    },
    // Mode sélection pour le modal
    selectable: {
        type: Boolean,
        default: false,
    },
    // Hauteur personnalisable
    height: {
        type: String,
        default: "340px",
    },
    // Services sélectionnés (pour le modal)
    selectedServices: {
        type: Array,
        default: () => [],
    },
    // Variante d'affichage ('accueil' ou 'modal')
    variant: {
        type: String,
        default: "accueil",
        validator: (value) => ['accueil', 'modal'].includes(value)
    },
});

const emit = defineEmits(["service-selected"]);

const services = ref([]);
const activeIndex = ref(props.selectable ? null : 0);
const loading = ref(true); // Si non selectable (landing page), activer le premier
const localSelectedServices = ref([...props.selectedServices]);

// Calculer les services à afficher
const displayedServices = computed(() => {
    if (props.serviceIds && props.serviceIds.length > 0) {
        // Filtre les services par les IDs spécifiés dans le même ordre
        return props.serviceIds
            .map((id) => services.value.find((service) => service.id === id))
            .filter(Boolean);
    } else {
        // En mode modal, on affiche tous les services
        return services.value;
    }
});

// Vérifier si un service est sélectionné
const isSelected = (serviceId) => {
    return localSelectedServices.value.includes(serviceId);
};

// Toggle sélection d'un service (avec feedback clavier et souris)
const toggleService = (service) => {
    if (!props.selectable) return;
    const index = localSelectedServices.value.indexOf(service.id);
    if (index === -1) {
        localSelectedServices.value.push(service.id);
    } else {
        localSelectedServices.value.splice(index, 1);
    }
    emit("service-selected", localSelectedServices.value);
};

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
    // Si nous sommes sur la landing page (non selectable), revenir au premier service
    activeIndex.value = props.selectable ? null : 0;
};

// Fonction pour naviguer vers la page services-portfolio avec le service concerné
const navigateToServiceDetail = (serviceId) => {
    // Si nous sommes en mode sélection, ne pas naviguer
    if (props.selectable) return;

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
        class="flex w-full"
        :class="{
            'flex-wrap justify-between gap-y-4': props.selectable && props.variant === 'modal',
            'flex-wrap gap-6': props.selectable && props.variant !== 'modal',
            'flex-col md:flex-row gap-6': !props.selectable
        }"
        aria-label="Liste des services"
    >
        <article
            v-for="(service, index) in displayedServices"
            :key="service.id"
            class="group relative flex rounded-lg border border-[rgba(13,7,3,0.15)] bg-[#FAF8F3] transition-all duration-500 overflow-hidden shadow-md mb-4"
            :class="[
                props.selectable && props.variant === 'modal'
                    ? 'md:w-[calc(33.33%-0.75rem)] w-[calc(50%-0.5rem)] aspect-square cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-500 hover:shadow-lg flex-col'
                    : props.selectable
                    ? 'md:w-[31%] w-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-500 hover:shadow-lg flex-col'
                    : activeIndex === index || isSelected(service.id)
                    ? 'md:w-2/3 w-full gap-6'
                    : 'md:w-1/3 w-full gap-6',
                activeIndex === index || isSelected(service.id)
                    ? 'text-white border-none'
                    : 'text-[#0D0703]',
            ]"
            :style="[
                activeIndex === index || isSelected(service.id)
                    ? 'background-color: #2D2D2D;'
                    : '',
                { height: height },
            ]"
            @mouseenter="handleMouseEnter(index)"
            @mouseleave="handleMouseLeave"
            @click="
                props.selectable
                    ? toggleService(service)
                    : navigateToServiceDetail(service.id)
            "
            v-bind="
                props.selectable
                    ? {
                          tabindex: '0',
                          role: 'button',
                          'aria-pressed': isSelected(service.id),
                      }
                    : {}
            "
            @keydown.enter.prevent="
                props.selectable
                    ? toggleService(service)
                    : navigateToServiceDetail(service.id)
            "
        >
            <!-- Indicateur de sélection (mode modal uniquement) -->
            <div
                v-if="props.selectable && isSelected(service.id)"
                class="absolute top-2 right-2 bg-orange-500 text-white p-1 rounded-full z-10"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>

            <!-- Image qui s'étire vers le haut (mode modal) ou vers la gauche (landing page) -->
            <div
                v-if="props.selectable"
                class="w-full transition-all duration-500 ease-in-out"
                :class="
                    activeIndex === index || isSelected(service.id)
                        ? 'h-1/2 opacity-100'
                        : 'h-0 opacity-0'
                "
            >
                <img
                    :src="service.image"
                    :alt="service.title + ' image'"
                    loading="lazy"
                    class="w-full h-full object-cover rounded-t-lg"
                />
            </div>
            <div
                v-else
                class="h-full w-0 transition-all duration-500 ease-in-out"
                :class="
                    activeIndex === index || isSelected(service.id)
                        ? 'w-1/2 opacity-100'
                        : 'w-0 opacity-0'
                "
            >
                <img
                    :src="service.image"
                    :alt="service.title + ' image'"
                    loading="lazy"
                    class="w-full h-full object-cover rounded-l-lg"
                />
            </div>

            <!-- Section droite/bas contenant le texte, toujours visible -->
            <div
                class="flex flex-col gap-4 flex-1 z-10"
                :style="{
                    height: props.selectable ? 'auto' : 'calc(100% - 24px)',
                }"
                :class="[
                    props.selectable
                        ? 'w-full p-4'
                        : activeIndex === index || isSelected(service.id)
                        ? 'w-1/3 pr-6 pt-6'
                        : 'w-full pr-6 pt-6',
                ]"
            >
                <!-- Icône (masquée en mode modal quand l'image est étendue) -->
                <img
                    v-if="
                        !props.selectable ||
                        !(activeIndex === index || isSelected(service.id))
                    "
                    :src="service.icon"
                    :alt="service.title + ' icon'"
                    :class="[
                        props.selectable ? 'w-8 h-8' : 'w-12 h-12',
                        activeIndex === index || isSelected(service.id)
                            ? 'filter brightness-0 invert'
                            : '',
                    ]"
                />

                <!-- Texte principal -->
                <div
                    class="flex flex-col gap-2"
                    :class="props.selectable ? 'h-16' : 'h-auto'"
                >
                    <h2
                        class="font-poppins font-medium"
                        :class="props.selectable ? 'sm:text-lg text-sm' : 'text-2xl'"
                    >
                        {{ service.title }}
                    </h2>
                    <div v-if="!props.selectable">
                        <p class="font-inter font-normal text-base">
                            {{ service.short_description }}
                        </p>
                    </div>
                </div>

                <!-- Lien "En savoir plus" + flèche (uniquement hors mode selectable) -->
                <div
                    v-if="!props.selectable"
                    class="flex gap-2 items-center cursor-pointer mt-auto"
                    :class="
                        activeIndex === index || isSelected(service.id)
                            ? 'text-[#FF8C42]'
                            : ''
                    "
                    @click.prevent="navigateToServiceDetail(service.id)"
                >
                    <a href="#" class="font-inter text-base font-medium">
                        En savoir plus
                    </a>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 256 512"
                        class="w-4 h-4 fill-current group-hover:text-[#FF8C42]"
                    >
                        <path
                            d="M246.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-160 160c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L178.7 256 41.4 118.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l160 160z"
                        />
                    </svg>
                </div>

                <!-- Message de sélection en mode modal -->
                <div
                    v-if="props.selectable"
                    class="text-[10px] xs:text-[9px] sm:text-xs mt-2"
                    :class="
                        isSelected(service.id)
                            ? 'text-orange-400'
                            : 'text-gray-500'
                    "
                >
                    {{
                        isSelected(service.id)
                            ? "Sélectionné"
                            : "Cliquez pour sélectionner"
                    }}
                </div>
            </div>
        </article>
    </section>
</template>
