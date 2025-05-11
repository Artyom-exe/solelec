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

const emit = defineEmits(["service-selected"]);

const services = ref([]);
const loading = ref(true);
const localSelectedServices = ref([...props.selectedServices]);

// Calculer les services à afficher (tous les services dans le modal)
const displayedServices = computed(() => {
    return services.value;
});

// Vérifier si un service est sélectionné
const isSelected = (serviceId) => {
    return localSelectedServices.value.includes(serviceId);
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

fetchServices();
</script>

<template>
    <!-- Indicateur de chargement avec le logo -->
    <div v-if="loading" class="w-full flex justify-center items-center py-8">
        <LogoLoader />
    </div>

    <section
        v-else
        class="flex w-full flex-wrap justify-between gap-y-4"
        aria-label="Liste des services pour sélection"
    >
        <article
            v-for="(service, index) in displayedServices"
            :key="service.id"
            class="group relative flex rounded-lg border border-[rgba(13,7,3,0.15)] bg-[#FAF8F3] transition-all duration-500 overflow-hidden shadow-md md:w-[calc(33.33%-0.75rem)] w-[calc(50%-0.5rem)] aspect-square cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-500 hover:shadow-lg flex-col"
            :class="[
                isSelected(service.id)
                    ? 'text-white border-none'
                    : 'text-[#0D0703]',
            ]"
            :style="[
                isSelected(service.id)
                    ? 'background-color: #2D2D2D;'
                    : '',
                { height: height },
            ]"
            @click="toggleService(service)"
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

            <!-- Image du service -->
            <div
                class="absolute top-0 left-0 right-0 bottom-0 z-0 transition-all duration-500 overflow-hidden"
                :class="{
                    'border-l-4 border-[#FF8C42]': isSelected(service.id)
                }"
            >
                <!-- Image de fond (visible uniquement si sélectionné) -->
                <img
                    v-if="service.image_url"
                    :src="service.image_url"
                    :alt="service.name"
                    class="w-full h-full object-cover transition-all duration-500 ease-in-out overflow-hidden"
                    :class="{
                        'opacity-20': isSelected(service.id),
                        'opacity-0': !isSelected(service.id),
                    }"
                />
            </div>

            <!-- Contenu (titre et icône) -->
            <div
                class="relative z-10 flex flex-col justify-center gap-4 transition-all duration-500 p-6 h-full w-full"
                :class="{
                    'bg-[#2D2D2D]/95': isSelected(service.id),
                }"
            >
                <!-- Icône -->
                <img
                    v-if="service.icon_url"
                    :src="service.icon_url"
                    :alt="`Icône ${service.name}`"
                    class="w-10 h-10 object-contain transition-all duration-500"
                    :class="{
                        'grayscale-0': isSelected(service.id),
                        'grayscale': !isSelected(service.id),
                    }"
                />

                <!-- Titre -->
                <div class="flex flex-col gap-2 transition-all duration-500">
                    <h3
                        class="text-lg font-semibold capitalize transition-all duration-500"
                    >
                        {{ service.name }}
                    </h3>
                    <p
                        class="text-sm leading-relaxed transition-all duration-500"
                        :class="{
                            'text-white/80': isSelected(service.id),
                            'text-[#0D0703]/80': !isSelected(service.id),
                        }"
                    >
                        {{ service.description.substring(0, 60) }}{{ service.description.length > 60 ? '...' : '' }}
                    </p>
                </div>
            </div>
        </article>
    </section>
</template>
