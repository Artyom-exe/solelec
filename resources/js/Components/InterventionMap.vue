<script setup>
import { onMounted, ref, nextTick, onUnmounted } from "vue";

const mapElement = ref(null);
const map = ref(null);
const isMobile = ref(false);
const scrollableElement = ref(null);
const showGradient = ref(true);
const activeCityIndex = ref(0); // Index de la ville active par défaut
// Variables pour le parallaxe mobile
const citiesScrollContainer = ref(null);
const isScrolling = ref(false);

const cities = [
    { name: "Ottignies", coords: { lat: 50.6683, lng: 4.6144 } },
    { name: "Louvain-la-Neuve", coords: { lat: 50.6689, lng: 4.6111 } },
    { name: "Wavre", coords: { lat: 50.7172, lng: 4.6014 } },
    { name: "Nivelles", coords: { lat: 50.5956, lng: 4.3286 } },
    { name: "Gembloux", coords: { lat: 50.5639, lng: 4.6984 } },
    { name: "Namur", coords: { lat: 50.4674, lng: 4.8718 } },
    { name: "Charleroi", coords: { lat: 50.4108, lng: 4.4446 } },
    { name: "Bruxelles", coords: { lat: 50.8503, lng: 4.3517 } },
];

const centerOn = (coords, cityIndex) => {
    activeCityIndex.value = cityIndex; // Mettre à jour la ville active
    if (map.value) {
        map.value.setCenter(coords);
        map.value.setZoom(12);
    }

    // Sur mobile, faire défiler vers la ville dans le carrousel
    if (isMobile.value) {
        scrollToCity(cityIndex);
    }
};

// Fonction pour gérer le parallaxe mobile des villes
const handleCitiesParallaxScroll = () => {
    if (!citiesScrollContainer.value || !isMobile.value) return;

    const container = citiesScrollContainer.value;
    const scrollLeft = container.scrollLeft;
    const containerWidth = container.scrollWidth - container.clientWidth;

    if (containerWidth === 0) return;

    // Calculer l'index basé sur la position de scroll
    const progress = scrollLeft / containerWidth;
    const newIndex = Math.round(progress * (cities.length - 1));

    if (
        newIndex !== activeCityIndex.value &&
        newIndex >= 0 &&
        newIndex < cities.length
    ) {
        activeCityIndex.value = newIndex;

        // Mettre à jour la carte avec la nouvelle ville
        if (map.value) {
            map.value.setCenter(cities[newIndex].coords);
            map.value.setZoom(12);
        }
    }
};

// Fonction pour faire défiler vers une ville spécifique
const scrollToCity = (index) => {
    if (!citiesScrollContainer.value) return;

    const container = citiesScrollContainer.value;
    const containerWidth = container.scrollWidth - container.clientWidth;
    const targetScroll = (containerWidth / (cities.length - 1)) * index;

    container.scrollTo({
        left: targetScroll,
        behavior: "smooth",
    });
};

const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

const handleScroll = () => {
    if (!scrollableElement.value || !isMobile.value) return;

    const { scrollTop, scrollHeight, clientHeight } = scrollableElement.value;
    const threshold = 10; // Marge de 10px pour détecter le bas

    // Cacher le dégradé si on est proche du bas
    showGradient.value = scrollTop + clientHeight < scrollHeight - threshold;
};

onMounted(() => {
    checkMobile();
    window.addEventListener("resize", checkMobile);

    window.initMap = () => {
        map.value = new google.maps.Map(mapElement.value, {
            center: cities[0].coords,
            zoom: 12,
            disableDefaultUI: true,
        });

        cities.forEach((city) => {
            new google.maps.Marker({
                position: city.coords,
                map: map.value,
                title: city.name,
            });
        });
    };

    if (window.google && window.google.maps) {
        window.initMap();
    }

    nextTick(() => {
        if (scrollableElement.value) {
            scrollableElement.value.addEventListener("scroll", handleScroll);
            handleScroll(); // Vérifier l'état initial
        }

        // Ajouter throttle pour optimiser les performances du parallaxe des villes
        let ticking = false;
        const throttledCitiesScroll = () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    handleCitiesParallaxScroll();
                    ticking = false;
                });
                ticking = true;
            }
        };

        // Attacher l'événement de scroll pour le carrousel des villes
        if (citiesScrollContainer.value) {
            citiesScrollContainer.value.addEventListener(
                "scroll",
                throttledCitiesScroll,
                { passive: true }
            );
        }
    });
});

onUnmounted(() => {
    window.removeEventListener("resize", checkMobile);

    if (scrollableElement.value) {
        scrollableElement.value.removeEventListener("scroll", handleScroll);
    }

    if (citiesScrollContainer.value) {
        citiesScrollContainer.value.removeEventListener(
            "scroll",
            handleCitiesParallaxScroll
        );
    }
});
</script>

<template>
    <article
        class="flex flex-col gap-8 bg-[#F5F5F5]"
        data-aos="fade-up"
        data-aos-duration="800"
    >
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Deux colonnes -->
            <div class="relative md:w-1/2">
                <!-- Version desktop - grille -->
                <div
                    ref="scrollableElement"
                    class="hidden md:grid md:grid-cols-2 gap-6"
                    data-aos="fade-right"
                    data-aos-delay="100"
                    data-aos-duration="800"
                >
                    <button
                        v-for="(city, index) in cities"
                        :key="'desktop-' + city.name"
                        @click="centerOn(city.coords, index)"
                        class="group flex flex-col items-start gap-2 px-6 py-5 rounded-lg border transition-all duration-300 ease-in-out focus:outline-none"
                        :class="
                            activeCityIndex === index
                                ? 'border-[#FF8C42] bg-[#f9f9f9] shadow-md'
                                : 'border-transparent hover:bg-[#f9f9f9] hover:border-[#FF8C42] hover:shadow-md'
                        "
                    >
                        <h3
                            class="text-left text-xl font-semibold transition-colors duration-300 font-poppins"
                            :class="
                                activeCityIndex === index
                                    ? 'text-[#FF8C42]'
                                    : 'group-hover:text-[#FF8C42]'
                            "
                        >
                            {{ city.name }}
                        </h3>
                        <div class="flex gap-3 items-center">
                            <p
                                class="text-left text-sm font-medium transition-colors duration-300 font-inter"
                                :class="
                                    activeCityIndex === index
                                        ? 'text-[#0D0703]'
                                        : 'text-gray-500 group-hover:text-[#0D0703]'
                                "
                            >
                                Voir sur la carte
                            </p>
                            <svg
                                class="w-4 h-4 transition-all duration-300"
                                :class="
                                    activeCityIndex === index
                                        ? 'text-[#FF8C42] translate-x-1'
                                        : 'text-gray-500 group-hover:text-[#FF8C42] group-hover:translate-x-1'
                                "
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 448 512"
                                fill="currentColor"
                            >
                                <path
                                    d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"
                                />
                            </svg>
                        </div>
                    </button>
                </div>

                <!-- Version mobile - carrousel horizontal avec parallaxe -->
                <div
                    class="md:hidden w-full"
                    data-aos="fade-right"
                    data-aos-delay="100"
                    data-aos-duration="800"
                >
                    <!-- Indicateurs de navigation -->
                    <div class="flex justify-center mb-4 gap-2">
                        <button
                            v-for="(city, index) in cities"
                            :key="'indicator-' + index"
                            class="w-2 h-2 rounded-full transition-all duration-300"
                            :class="
                                activeCityIndex === index
                                    ? 'bg-[#FF8C42] scale-125'
                                    : 'bg-gray-400'
                            "
                            @click="centerOn(city.coords, index)"
                        ></button>
                    </div>

                    <!-- Conteneur de scroll horizontal -->
                    <div
                        ref="citiesScrollContainer"
                        class="flex overflow-x-auto hide-scrollbar gap-4 p-4 scroll-smooth snap-x snap-mandatory"
                    >
                        <div
                            v-for="(city, index) in cities"
                            :key="'mobile-city-' + index"
                            class="min-w-[80vw] flex flex-col p-6 bg-white rounded-lg border-2 snap-center transition-all duration-500 shadow-sm"
                            :class="{
                                'border-[#FF8C42] shadow-lg scale-[1.02] bg-[#f9f9f9]':
                                    activeCityIndex === index,
                                'border-gray-200 opacity-75':
                                    activeCityIndex !== index,
                            }"
                            :style="{
                                transform: `translateX(${
                                    (index - activeCityIndex) * 8
                                }px) scale(${
                                    activeCityIndex === index ? 1.02 : 0.98
                                })`,
                                filter:
                                    activeCityIndex === index
                                        ? 'brightness(1)'
                                        : 'brightness(0.85)',
                            }"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <h3
                                    class="text-xl font-semibold font-poppins transition-colors duration-300"
                                    :class="
                                        activeCityIndex === index
                                            ? 'text-[#FF8C42]'
                                            : 'text-gray-700'
                                    "
                                >
                                    {{ city.name }}
                                </h3>
                                <svg
                                    class="w-5 h-5 transition-all duration-300"
                                    :class="
                                        activeCityIndex === index
                                            ? 'text-[#FF8C42] translate-x-1'
                                            : 'text-gray-400'
                                    "
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 384 512"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z"
                                    />
                                </svg>
                            </div>
                            <p
                                class="text-sm font-medium font-inter transition-colors duration-300"
                                :class="
                                    activeCityIndex === index
                                        ? 'text-[#0D0703]'
                                        : 'text-gray-500'
                                "
                            >
                                Zone d'intervention électrique
                            </p>
                            <div class="mt-3 flex items-center gap-2">
                                <span
                                    class="text-xs px-2 py-1 rounded-full transition-all duration-300"
                                    :class="
                                        activeCityIndex === index
                                            ? 'bg-[#FF8C42] text-white'
                                            : 'bg-gray-200 text-gray-600'
                                    "
                                >
                                    Disponible
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Carte -->
            <div
                class="md:w-1/2 h-[440px] rounded-lg overflow-hidden shadow"
                data-aos="zoom-in"
                data-aos-delay="300"
                data-aos-duration="1000"
            >
                <div ref="mapElement" class="w-full h-full"></div>
            </div>
        </div>

        <!-- Texte SEO caché (optionnel) -->
        <p class="sr-only">
            Solelec intervient en Wallonie et à Bruxelles, notamment à
            Ottignies, Louvain-la-Neuve, Wavre, Nivelles, Gembloux, Namur,
            Charleroi, Liège, Mons, Rixensart, Court-Saint-Étienne,
            Mont-Saint-Guibert, Eghezée, Sombreffe, Perwez, Bastogne, Spa,
            Marche-en-Famenne, Tournai et bien d'autres localités.
        </p>
    </article>
</template>

<style scoped>
/* Styles pour le carrousel mobile avec parallaxe */
.hide-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
    scroll-behavior: smooth;
}

.hide-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

/* Snap scrolling pour mobile */
.snap-x {
    scroll-snap-type: x mandatory;
}

.snap-center {
    scroll-snap-align: center;
}

/* Animation smooth pour les transitions parallaxe des villes */
.mobile-city-card {
    transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94),
        filter 0.4s ease, opacity 0.4s ease, scale 0.3s ease,
        border-color 0.3s ease, background-color 0.3s ease;
}

/* Indicateurs de navigation personnalisés */
.nav-indicator {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.nav-indicator:hover {
    transform: scale(1.2);
    opacity: 0.8;
}

/* Effet de profondeur pour les cartes des villes */
.city-depth-effect {
    transform-style: preserve-3d;
    perspective: 1000px;
}

/* Animation pour les icônes */
.city-icon {
    transition: transform 0.3s ease, color 0.3s ease;
}

/* Effet de focus amélioré */
.city-card:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 140, 66, 0.3);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .min-w-\[80vw\] {
        min-width: 85vw;
    }
}
</style>
