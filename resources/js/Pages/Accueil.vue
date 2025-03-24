<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Services from '@/Components/Services.vue';
import AOS from 'aos'
import 'aos/dist/aos.css'

const props = defineProps({
    limit: {
        type: Number,
        default: 2
    }
});

const portfolio = ref([]);
const tags = ref([]);
const randomPortfolio = ref([]);


const fetchPortfolio = async () => {
    try {
        const response = await axios.get('portfolios');
        portfolio.value = response.data;
        getRandomPortfolio();
    } catch (error) {
        console.error(error);
    }
};

const getRandomPortfolio = () => {
    if (portfolio.value.length <= props.limit) {
        // Si le portfolio a moins ou égal au nombre d'éléments demandés, on prend tout
        randomPortfolio.value = [...portfolio.value];
    } else {
        // Création d'une copie du tableau pour ne pas modifier l'original
        const shuffled = [...portfolio.value];
        // Mélange du tableau (algorithme de Fisher-Yates)
        for (let i = shuffled.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
        }
        // Sélection des premiers éléments après mélange
        randomPortfolio.value = shuffled.slice(0, props.limit);
    }
};

const fetchTags = async () => {
    try {
        const response = await axios.get('tags');
        tags.value = response.data;
    } catch (error) {
        console.error(error);
    }
};
fetch

// Définition des images pour les 2 colonnes
const leftImages = ref([
    { src: '/images/header/ouvrier-dos.jpg', alt: 'Électricien Solelec de dos travaillant sur une installation', aosDelay: 0 },
    { src: '/images/header/electricien.webp', alt: 'Technicien qualifié Solelec en intervention', aosDelay: 200 },
    { src: '/images/header/ouvrier-panneau.jpg', alt: "Installation de panneau solaire par l'équipe Solelec", aosDelay: 400 },
])

const rightImages = ref([
    { src: '/images/header/ouvrier-tableau-electrique.jpg', alt: 'Expert Solelec travaillant sur un tableau électrique', aosDelay: 100 },
    { src: '/images/header/plan-travail.jpg', alt: "Plan d'installation électrique étudié par Solelec", aosDelay: 300 },
    { src: '/images/header/tableau-electrique-zoom.jpg', alt: "Gros plan sur tableau électrique installé par Solelec", aosDelay: 500 },
])

onMounted(() => {
    // Configuration optimisée pour AOS
    AOS.init({
        duration: 1000,
        once: true, // Changement à true pour éviter les réapparitions non désirées
        mirror: false, // Changement à false pour plus de stabilité
        easing: 'ease-out-cubic',
        offset: 50,
        anchorPlacement: 'top-center', // Point d'ancrage commun
        startEvent: 'DOMContentLoaded'
    })

    document.documentElement.style.overflowX = 'hidden';

    fetchPortfolio();
    fetchTags();

    // Force une pause puis un rafraîchissement pour s'assurer que toutes les images sont chargées
    setTimeout(() => {
        AOS.refresh();
    }, 200);
})
</script>

<template>
    <PublicLayout title="Solelec">

        <Head>
            <title>Solelec - Expert en électricité & énergies renouvelables</title>
            <meta name="description"
                content="Solutions électriques innovantes et éco-responsables pour vos projets. Expertise en électricité et énergies renouvelables par Solelec." />
            <meta name="keywords"
                content="électricité, énergies renouvelables, installation électrique, panneau solaire" />
        </Head>

        <!-- Header -->

        <header
            class="flex flex-col items-center gap-2 bg-[#2D2D2D] h-[calc(100vh-72px)] px-16 overflow-hidden mt-[72px]">
            <div class="flex flex-col lg:flex-row items-center flex-1 self-stretch h-full w-full">
                <div class="flex flex-col pr-20 justify-center items-start gap-8 flex-1">
                    <div class="flex flex-col items-start gap-6 text-white">
                        <h1 class="text-white font-poppins text-[56px] font-medium leading-[120%] tracking-[-0.56px]">
                            S<span class="text-[#FF8C42]">o</span>lelec - Votre expert en électricité & énergies
                            renouvelables</h1>

                        <p class="font-inter text-lg font-normal leading-[150%]">Découvrez des solutions électriques
                            innovantes et éco-responsables. Faites confiance à notre expertise pour vos projets
                            d'énergie renouvelable.</p>
                    </div>

                    <div class="flex gap-4">
                        <PrimaryButton>Demander un devis</PrimaryButton>
                        <SecondaryButton>Nous contacter</SecondaryButton>
                    </div>
                </div>
                <div class="flex gap-4 flex-1 h-full overflow-hidden">
                    <!-- Colonne gauche -->
                    <div class="flex flex-col w-1/2 gap-4 -translate-y-60">
                        <div v-for="(img, index) in leftImages" :key="'left-' + index" class="h-[340px]"
                            data-aos="fade-up" :data-aos-delay="img.aosDelay" data-aos-anchor="#header-section">
                            <img :src="img.src" :alt="img.alt" class="w-full h-full object-cover rounded-lg"
                                loading="lazy" />
                        </div>
                    </div>
                    <!-- Colonne droite -->
                    <div class="flex flex-col w-1/2 gap-4 -translate-y-40">
                        <div v-for="(img, index) in rightImages" :key="'right-' + index" class="h-[340px]"
                            data-aos="fade-up" :data-aos-delay="img.aosDelay" data-aos-anchor="#header-section">
                            <img :src="img.src" :alt="img.alt" class="w-full h-full object-cover rounded-lg"
                                loading="lazy" />
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Services -->
        <section class="flex py-28 px-16 flex-col gap-20 bg-[#FBFAF6]">
            <div class="flex flex-col gap-4 text-[#0D0703]" data-aos="fade-up">
                <h2 class="text-center font-inter text-base font-semibold">services</h2>
                <div class="relative flex flex-col gap-6">
                    <h3 class="font-poppins text-5xl text-center font-medium leading-[57.6px] tracking-[-0.48px]">
                        Nos
                        Services
                        Principaux</h3>
                    <div
                        class="absolute bottom-10 right-1/2 border-2 border-[#FF8C42] w-[40%] max-w-[353px] min-w-[200px]">
                    </div>
                    <h4 class="font-inter text-lg text-center">Des solutions adaptées à vos besoins
                        énergétiques.</h4>
                </div>
            </div>
            <Services data-aos="zoom-in" data-aos-duration="800" />
        </section>

        <!-- À propos -->
        <section class="flex py-28 px-16 gap-20 bg-[#2D2D2D] text-white">
            <div class="flex flex-col flex-1 gap-4" data-aos="fade-right" data-aos-duration="1000">
                <h2 class="font-poppins text-[40px] leading-[48px] font-medium">Découvrez
                    l'expertise de S<span class="text-[#FF8C42]">o</span>lelec en
                    électricité et énergies renouvelables.</h2>
            </div>
            <div class="flex flex-1 flex-col gap-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                <p class="font-inter text-lg font-normal">S<span class="text-[#FF8C42]">o</span>lelec, c'est 3 ans
                    d'expertise dans les
                    solutions
                    électriques et
                    photovoltaïques. Nous guidons nos
                    clients à chaque étape de leur projet, garantissant un service de qualité et des
                    installations
                    conformes
                    aux normes. Faites confiance à notre savoir-faire pour des solutions adaptées à
                    vos besoins.</p>
            </div>
        </section>

        <!-- Portfolio -->
        <section class="flex py-[120px] px-16 flex-col items-center gap-20 bg-[#FEFEFD] text-[#0D0703]">
            <div class="flex flex-col gap-4 text-[#0D0703]" data-aos="fade-up" data-aos-duration="800">
                <h2 class="text-center font-inter text-base font-semibold">portfolio</h2>
                <div class="relative flex flex-col gap-6">
                    <h3 class="font-poppins text-5xl text-center font-medium leading-[57.6px] tracking-[-0.48px]">
                        Nos
                        projets récents</h3>
                    <div
                        class="absolute bottom-10 left-1/2 border-2 border-[#FF8C42] w-[80%] max-w-[353px] min-w-[200px]">
                    </div>
                    <h4 class="font-inter text-lg text-center">Découvrez notre expertise à travers nos réalisations.
                    </h4>
                </div>
            </div>
            <div class="flex flex-col gap-16 items-center">
                <div class="flex items-start gap-12 flex-wrap justify-center">
                    <article v-for="(item, index) in randomPortfolio" :key="index"
                        class="flex flex-col items-start gap-6 flex-1" data-aos="fade-up" :data-aos-delay="index * 150">
                        <img :src="item.image" :alt="item.title"
                            class="w-full max-h-[356px] min-h-[200px] aspect-[4/3] object-cover rounded-lg" />
                        <div class="flex flex-col gap-4 text-[#0D0703]">
                            <h5 class="font-poppins text-2xl font-medium">{{ item.title }}</h5>
                            <p class="font-inter text-base">{{ item.description }}</p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span v-for="tag in item.tags" :key="tag.id"
                                    class="px-[10px] py-1 bg-[#F5F5F5] rounded-l font-inter text-sm font-semibold border border-[#0D070326/15] bg-[#0D070326/5]">
                                    {{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
                <SecondaryButton variant="dark" @click="$inertia.visit('#')">Voir plus</SecondaryButton>
            </div>
        </section>
    </PublicLayout>
</template>


