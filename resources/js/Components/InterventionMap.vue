<script setup>
import { onMounted, ref, nextTick, onUnmounted } from "vue";

const mapElement = ref(null);
const map = ref(null);
const isMobile = ref(false);
const scrollableElement = ref(null);
const showGradient = ref(true);
const activeCityIndex = ref(0); // Index de la ville active par défaut

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
                <div
                    ref="scrollableElement"
                    class="md:grid md:grid-cols-2 flex flex-col md:gap-6 gap-4 md:overflow-visible overflow-y-auto md:max-h-none max-h-[300px]"
                    data-aos="fade-right"
                    data-aos-delay="100"
                    data-aos-duration="800"
                >
                    <button
                        v-for="(city, index) in cities"
                        :key="city.name"
                        @click="centerOn(city.coords, index)"
                        class="group flex flex-col items-start gap-2 px-6 py-5 rounded-lg border transition-all duration-300 ease-in-out focus:outline-none flex-shrink-0"
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

                <!-- Dégradé en bas (uniquement mobile et si pas en bas) -->
                <div
                    v-show="showGradient && isMobile"
                    class="absolute bottom-0 left-0 right-0 h-8 bg-gradient-to-t from-[#F5F5F5] to-transparent pointer-events-none"
                ></div>
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
