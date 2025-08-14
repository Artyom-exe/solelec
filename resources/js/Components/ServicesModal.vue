<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import LogoLoader from "@/Components/LogoLoader.vue";

const props = defineProps({
    // Services sélectionnés
    selectedServices: {
        type: Array,
        default: () => [],
    },
    // Hauteur personnalisable
    height: {
        type: String,
        default: "340px",
    },
});

const emit = defineEmits(["service-selected", "loading-complete"]);

const services = ref([]);
const loading = ref(true);
const localSelectedServices = ref([...props.selectedServices]);
const touchedServiceId = ref(null); // Pour suivre quel service est actuellement touché

// Calculer les services à afficher (tous les services dans le modal)
const displayedServices = computed(() => {
    return services.value;
});

// Vérifier si un service est sélectionné
const isSelected = (serviceId) => {
    return localSelectedServices.value.includes(serviceId);
};

// Vérifier si un service est touché
const isTouched = (serviceId) => {
    return touchedServiceId.value === serviceId;
};

// Gestionnaires d'événements tactiles
const handleTouchStart = (service) => {
    touchedServiceId.value = service.id;
};

const handleTouchEnd = (service) => {
    touchedServiceId.value = null;
    toggleService(service);
};

// Toggle sélection d'un service (avec feedback clavier et souris)
const toggleService = (service) => {
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
        emit("loading-complete");
    }
};

fetchServices();
</script>

<template>
    <!-- Indicateur de chargement avec le logo parfaitement centré -->
    <div
        v-if="loading"
        class="w-full h-full flex justify-center items-center"
        style="min-height: 200px"
    >
        <LogoLoader />
    </div>

    <section
        v-else
        class="flex w-full flex-wrap justify-between gap-y-4 p-1"
        aria-label="Liste des services pour sélection"
    >
        <article
            v-for="(service, index) in displayedServices"
            :key="service.id"
            class="group relative flex rounded-lg border border-[rgba(13,7,3,0.15)] bg-[#FAF8F3] transition-all duration-300 overflow-hidden shadow-md md:w-[calc(33.33%-0.75rem)] w-[calc(50%-0.5rem)] aspect-square cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-500 hover:shadow-lg active:shadow-inner flex-col"
            :class="[
                isSelected(service.id)
                    ? 'text-white border-none'
                    : 'text-[#0D0703]',
                isTouched(service.id)
                    ? 'scale-95 shadow-inner bg-gray-100'
                    : '',
            ]"
            :style="[
                isSelected(service.id) ? 'background-color: #2D2D2D;' : '',
                { height: height },
            ]"
            @click="toggleService(service)"
            @touchstart.prevent="handleTouchStart(service)"
            @touchend.prevent="handleTouchEnd(service)"
            @touchcancel="touchedServiceId = null"
            v-bind="{
                tabindex: '0',
                role: 'button',
                'aria-pressed': isSelected(service.id),
            }"
            @keydown.enter.prevent="toggleService(service)"
        >
            <!-- Indicateur de sélection -->
            <div
                v-if="isSelected(service.id)"
                class="absolute top-2 right-2 bg-orange-500 text-white p-1 rounded-full z-20"
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

            <!-- Image du service (apparaît au survol, ou visible si sélectionné) -->
            <div
                class="absolute top-0 left-0 w-full transition-all duration-500 overflow-hidden opacity-0 h-0 group-hover:opacity-100 group-hover:h-1/2"
                :class="{
                    'opacity-20 h-full group-hover:opacity-100': isSelected(
                        service.id
                    ),
                    'opacity-100 h-1/2': isTouched(service.id),
                }"
            >
                <img
                    v-if="service.image"
                    :src="service.image"
                    :alt="service.title"
                    class="w-full h-full object-cover transition-all duration-500 ease-in-out"
                />
            </div>

            <!-- Contenu (état normal aligné à gauche, déplacé vers le bas au survol) -->
            <div
                class="flex flex-col justify-center items-start gap-4 transition-all duration-500 p-6 w-full h-full group-hover:justify-end group-hover:h-1/2 group-hover:mt-auto group-hover:bg-[#2D2D2D]/95 group-hover:text-white"
                :class="{
                    'bg-[#2D2D2D]/95 text-white': isSelected(service.id),
                    'justify-end h-1/2 mt-auto bg-[#2D2D2D]/95 text-white':
                        isTouched(service.id),
                }"
            >
                <!-- Icône (masquée au survol, blanche si sélectionné) -->
                <img
                    v-if="service.icon"
                    :src="service.icon"
                    :alt="`Icône ${service.title}`"
                    class="w-10 h-10 object-contain transition-all duration-300 group-hover:opacity-0"
                    :class="{
                        'grayscale-0 brightness-0 invert': isSelected(
                            service.id
                        ),
                        grayscale: !isSelected(service.id),
                        'opacity-0': isTouched(service.id),
                    }"
                />

                <!-- Titre -->
                <div
                    class="flex flex-col transition-all duration-500 text-left w-full"
                >
                    <h3
                        class="sm:text-base font-poppins text-xs font-semibold capitalize transition-all duration-500 group-hover:text-[14px]"
                    >
                        {{ service.title }}
                    </h3>
                </div>
            </div>
        </article>
    </section>
</template>
