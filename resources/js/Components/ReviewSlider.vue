<script setup>
import { Splide, SplideSlide } from "@splidejs/vue-splide";
import { AutoScroll } from "@splidejs/splide-extension-auto-scroll";
import { ref, onMounted } from "vue";
import axios from "axios";

const reviews = ref([]);
// Variable réactive pour détecter si on est sur mobile ou desktop
const isMobile = ref(false);

// Vérifier la taille de l'écran au chargement et lors du redimensionnement
const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
};

onMounted(async () => {
    const res = await axios.get("customer-reviews");
    reviews.value = res.data.filter((r) => r.comment?.length > 0);

    // Vérifier la taille initiale de l'écran
    checkScreenSize();

    // Ajouter un écouteur pour le redimensionnement de fenêtre
    window.addEventListener("resize", checkScreenSize);
});
</script>

<template>
    <!-- Slider Desktop - défilement automatique sans glissement -->
    <Splide
        v-if="!isMobile"
        :options="{
            type: 'loop',
            perPage: 3,
            gap: '32px',
            arrows: false,
            pagination: false,
            drag: false,
            autoScroll: { speed: 1, pauseOnHover: true, pauseOnFocus: true },
            breakpoints: {
                1280: { perPage: 2, gap: '1.5rem' },
            },
        }"
        :extensions="{ AutoScroll }"
        class="w-full"
    >
        <SplideSlide
            v-for="review in reviews"
            :key="'desktop-' + review.id"
            class="w-auto"
        >
            <div
                class="h-[233px] flex p-8 flex-col justify-between gap-6 rounded-lg border border-white/20 bg-[#242424] text-white shadow-md"
            >
                <!-- Étoiles -->
                <div class="text-orange-400 text-xl flex">
                    <span v-for="n in review.note" :key="n">
                        <img
                            src="/assets/icons/vector.svg"
                            alt="star"
                            class="w-5 h-5 mr-1"
                        />
                    </span>
                </div>
                <!-- Commentaire -->
                <p class="font-inter text-base line-clamp-2">
                    "{{ review.comment }}"
                </p>
                <!-- Auteur -->
                <p class="mt-2 text-sm text-gray-400">— {{ review.author }}</p>
            </div>
        </SplideSlide>
    </Splide>

    <!-- Slider Mobile - glissement manuel sans défilement automatique -->
    <Splide
        v-else
        :options="{
            type: 'loop',
            perPage: 1,
            gap: 0,
            padding: { left: '5%', right: '5%' },
            arrows: false,
            pagination: false,
            drag: true,
            snap: true,
            speed: 400,
            focus: 'center',
            trimSpace: false,
        }"
        class="w-full"
    >
        <SplideSlide
            v-for="review in reviews"
            :key="'mobile-' + review.id"
            class="w-auto touch-pan-y"
        >
            <div
                class="h-[233px] mx-2 flex p-6 flex-col justify-between gap-4 rounded-lg border border-white/20 bg-[#242424] text-white shadow-md"
            >
                <!-- Étoiles -->
                <div class="text-orange-400 text-xl flex">
                    <span v-for="n in review.note" :key="n">
                        <img
                            src="/assets/icons/vector.svg"
                            alt="star"
                            class="w-5 h-5 mr-1"
                        />
                    </span>
                </div>
                <!-- Commentaire -->
                <p class="font-inter text-base line-clamp-2">
                    "{{ review.comment }}"
                </p>
                <!-- Auteur -->
                <p class="mt-2 text-sm text-gray-400">— {{ review.author }}</p>
            </div>
        </SplideSlide>
    </Splide>
</template>
