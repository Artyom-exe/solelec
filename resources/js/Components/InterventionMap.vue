<script setup>
import { onMounted, ref, nextTick, onUnmounted } from "vue";
import { Splide, SplideSlide } from "@splidejs/vue-splide";
import "@splidejs/vue-splide/css";

const mapElement = ref(null);
const map = ref(null);
const isMobile = ref(false);
const scrollableElement = ref(null);
const showGradient = ref(true);
const activeCityIndex = ref(0); // Index de la ville active par défaut
// Variables pour Splide
const splideRef = ref(null);

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

    // Sur mobile, naviguer vers la slide correspondante
    if (isMobile.value && splideRef.value) {
        splideRef.value.go(cityIndex);
    }
};

// Fonction pour gérer le changement de slide Splide
const onSlideChange = (splide, newIndex) => {
    // Debug pour comprendre le problème
    console.log(
        "Splide moved to:",
        newIndex.index,
        "cities length:",
        cities.length
    );

    const realIndex = newIndex.index; // Plus de modulo car pas de loop
    console.log(
        "Real index:",
        realIndex,
        "Current active:",
        activeCityIndex.value
    );

    if (activeCityIndex.value !== realIndex && cities[realIndex]) {
        activeCityIndex.value = realIndex;
        console.log(
            "Updated activeCityIndex to:",
            realIndex,
            "City:",
            cities[realIndex].name
        );

        // Mettre à jour la carte avec la nouvelle ville
        if (map.value) {
            map.value.setCenter(cities[realIndex].coords);
            map.value.setZoom(12);
        }
    }
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
    });
});

onUnmounted(() => {
    window.removeEventListener("resize", checkMobile);

    if (scrollableElement.value) {
        scrollableElement.value.removeEventListener("scroll", handleScroll);
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

                <!-- Version mobile - carrousel Splide -->
                <div
                    class="md:hidden w-full"
                    data-aos="fade-right"
                    data-aos-delay="100"
                    data-aos-duration="800"
                >
                    <!-- Indicateurs de navigation -->
                    <div class="flex justify-center mb-6 gap-2">
                        <button
                            v-for="(city, index) in cities"
                            :key="'indicator-' + index"
                            class="w-2 h-2 rounded-full transition-all duration-300"
                            :class="
                                activeCityIndex === index
                                    ? 'bg-[#FF8C42] scale-125'
                                    : 'bg-gray-400 hover:bg-gray-500'
                            "
                            @click="centerOn(city.coords, index)"
                        ></button>
                    </div>

                    <!-- Carrousel Splide -->
                    <Splide
                        ref="splideRef"
                        :options="{
                            type: 'slide',
                            perPage: 1,
                            gap: '16px',
                            padding: { left: '16px', right: '16px' },
                            arrows: false,
                            pagination: false,
                            drag: true,
                            snap: true,
                            speed: 300,
                            focus: 'center',
                            trimSpace: false,
                            autoWidth: false,
                            rewind: true,
                        }"
                        @splide:moved="onSlideChange"
                        @splide:active="onSlideChange"
                        class="w-full"
                    >
                        <SplideSlide
                            v-for="(city, index) in cities"
                            :key="'slide-' + index"
                            class="w-full"
                        >
                            <div
                                class="flex flex-col p-6 my-2 bg-white rounded-xl border-2 transition-all duration-500 min-h-[140px] w-full"
                                :class="{
                                    'border-[#FF8C42] bg-gradient-to-br from-white to-[#fef8f5] ':
                                        activeCityIndex === index,
                                    'border-gray-200 opacity-90':
                                        activeCityIndex !== index,
                                }"
                            >
                                <div
                                    class="flex items-center justify-between mb-4"
                                >
                                    <h3
                                        class="text-xl font-bold font-poppins transition-colors duration-300"
                                        :class="
                                            activeCityIndex === index
                                                ? 'text-[#FF8C42]'
                                                : 'text-gray-700'
                                        "
                                    >
                                        {{ city.name }}
                                    </h3>
                                    <svg
                                        class="w-6 h-6 transition-all duration-300"
                                        :class="
                                            activeCityIndex === index
                                                ? 'text-[#FF8C42] translate-x-1 scale-110'
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
                                    class="text-base font-medium font-inter transition-colors duration-300 mb-6"
                                    :class="
                                        activeCityIndex === index
                                            ? 'text-[#0D0703]'
                                            : 'text-gray-500'
                                    "
                                >
                                    Zone d'intervention électrique
                                </p>
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm px-4 py-2 rounded-full font-medium transition-all duration-300"
                                        :class="
                                            activeCityIndex === index
                                                ? 'bg-[#FF8C42] text-white shadow-sm'
                                                : 'bg-gray-100 text-gray-600'
                                        "
                                    >
                                        Disponible
                                    </span>
                                    <div
                                        class="flex items-center gap-1 text-xs text-gray-400"
                                    >
                                        <span>{{ index + 1 }}</span>
                                        <span>/</span>
                                        <span>{{ cities.length }}</span>
                                    </div>
                                </div>
                            </div>
                        </SplideSlide>
                    </Splide>
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
/* Styles personnalisés pour les indicateurs */
.nav-indicator {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.nav-indicator:hover {
    transform: scale(1.2);
    opacity: 0.8;
}

/* Effet de focus amélioré pour les cartes */
.city-card:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 140, 66, 0.3);
}

/* Animation pour les icônes avec rebond */
.city-icon {
    transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55),
        color 0.3s ease;
}

/* Styles pour les slides Splide */
.splide__slide {
    opacity: 0.7;
    transform: scale(0.95);
    transition: all 0.4s ease;
}

.splide__slide.is-active {
    opacity: 1;
    transform: scale(1);
}

/* Customisation des styles Splide */
.splide__track {
    padding: 0.5rem 0;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .splide__slide {
        opacity: 0.8;
        transform: scale(0.96);
    }

    .splide__slide.is-active {
        transform: scale(1.02);
    }
}
</style>
