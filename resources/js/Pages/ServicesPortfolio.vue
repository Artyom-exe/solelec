<script setup>
import { ref, onMounted, watch, inject, nextTick } from "vue";
import axios from "axios";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Splide, SplideSlide } from "@splidejs/vue-splide";
import "@splidejs/vue-splide/css";
// Importez AOS
import AOS from "aos";
import "aos/dist/aos.css";

const props = defineProps({
    serviceId: [Number, String],
});

const openDevisModal = ref(null);
const services = ref([]);
const activeIndex = ref(0);
const prevActiveIndex = ref(0);
// Ajout de la définition de portfolio
const portfolio = ref([]);
// Variables pour Splide
const splideRef = ref(null);

// Récupérer les services depuis l'API Laravel
const fetchServices = async () => {
    try {
        const response = await axios.get("/services");
        services.value = response.data;

        // Après avoir chargé les services, vérifier si un service spécifique doit être sélectionné
        checkForServiceSelection();
    } catch (error) {
        console.error("Erreur lors de la récupération des services:", error);
    }
};

// Fonction locale de défilement
const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
        const offset = 72; // Hauteur du header + marge additionnelle
        const elementPosition =
            element.getBoundingClientRect().top + window.scrollY;

        // Pour la section services, centrer la vue au milieu de la section
        if (sectionId === "services") {
            const sectionHeight = element.offsetHeight;
            const viewportHeight = window.innerHeight;

            // Calculer la position pour centrer la section dans la viewport
            const centerPosition =
                elementPosition + sectionHeight / 2 - viewportHeight / 2;

            // S'assurer qu'on ne dépasse pas le début de la section
            const minPosition = elementPosition - offset;

            // Position finale : au moins au début de la section, mais centrée si possible
            const finalPosition = Math.max(minPosition, centerPosition);

            window.scrollTo({
                top: finalPosition,
                behavior: "smooth",
            });
        } else {
            // Pour les autres sections, comportement normal
            const offsetPosition = elementPosition - offset;
            window.scrollTo({
                top: offsetPosition,
                behavior: "smooth",
            });
        }
    }
};

// Fonction helper pour centrer un service dans le conteneur avec défilement
const centerServiceInContainer = (serviceElement) => {
    if (serviceElement) {
        const container = serviceElement.closest(".overflow-y-auto");
        if (container) {
            // Centrer l'élément dans le conteneur avec défilement
            const containerHeight = container.clientHeight;
            const elementHeight = serviceElement.clientHeight;
            const elementOffsetTop = serviceElement.offsetTop;
            const containerScrollHeight = container.scrollHeight;

            // Calculer la position pour centrer l'élément
            let scrollPosition =
                elementOffsetTop - containerHeight / 2 + elementHeight / 2;

            // S'assurer que le scroll ne dépasse pas la hauteur maximale du conteneur
            const maxScrollPosition = containerScrollHeight - containerHeight;
            scrollPosition = Math.min(scrollPosition, maxScrollPosition);

            // S'assurer que le scroll ne soit pas négatif
            scrollPosition = Math.max(scrollPosition, 0);

            container.scrollTo({
                top: scrollPosition,
                behavior: "smooth",
            });
        }
    }
};

// Fonction pour vérifier et sélectionner un service à partir du hash de l'URL ou des props
const checkForServiceSelection = () => {
    // 1. Vérifier d'abord si un serviceId a été passé dans les props
    if (props.serviceId) {
        const serviceId = parseInt(props.serviceId);

        // Trouver l'index du service correspondant à cet ID
        const serviceIndex = services.value.findIndex(
            (service) => service.id === serviceId
        );

        if (serviceIndex !== -1) {
            // Mettre à jour l'index actif pour sélectionner le service
            activeIndex.value = serviceIndex;

            // Sur mobile, naviguer vers la slide correspondante avec Splide
            if (window.innerWidth < 768 && splideRef.value) {
                setTimeout(() => {
                    splideRef.value.go(serviceIndex);
                }, 600);
            }

            // Faire défiler jusqu'au service spécifique dans le conteneur (desktop)
            setTimeout(() => {
                // D'abord, faire défiler vers la section services
                scrollToSection("services");

                // Ensuite, centrer le service dans son conteneur
                setTimeout(() => {
                    const serviceElement = document.getElementById(
                        "service-" + serviceId
                    );
                    centerServiceInContainer(serviceElement);
                }, 300);
            }, 500); // Augmentation du délai pour s'assurer que le DOM est prêt
        }
    }
    // 2. Sinon vérifier le hash de l'URL
    else {
        const hash = window.location.hash;
        if (hash && hash.startsWith("#service-")) {
            const serviceId = parseInt(hash.replace("#service-", ""));
            // Trouver l'index du service correspondant à cet ID
            const serviceIndex = services.value.findIndex(
                (service) => service.id === serviceId
            );
            if (serviceIndex !== -1) {
                // Mettre à jour l'index actif pour sélectionner le service
                activeIndex.value = serviceIndex;

                // Sur mobile, naviguer vers la slide correspondante avec Splide
                if (window.innerWidth < 768 && splideRef.value) {
                    setTimeout(() => {
                        splideRef.value.go(serviceIndex);
                    }, 600);
                }

                // Faire défiler vers le service (desktop)
                setTimeout(() => {
                    // D'abord, faire défiler vers la section services
                    scrollToSection("services");

                    // Ensuite, centrer le service dans son conteneur
                    setTimeout(() => {
                        const serviceElement = document.getElementById(
                            hash.substring(1)
                        );
                        centerServiceInContainer(serviceElement);
                    }, 300);
                }, 300);
            }
        }
    }
};

const fetchPortfolio = async () => {
    try {
        const response = await axios.get("/portfolios");
        portfolio.value = response.data;
    } catch (error) {
        console.error("Erreur lors de la récupération du portfolio:", error);
    }
};

// Sélectionner un service
const selectService = (index) => {
    prevActiveIndex.value = activeIndex.value;
    activeIndex.value = index;

    // Sur mobile, naviguer vers la slide correspondante
    if (window.innerWidth < 768 && splideRef.value) {
        splideRef.value.go(index);
    } else {
        // Sur desktop, faire défiler jusqu'au service sélectionné
        const serviceId = services.value[index]?.id;
        if (serviceId) {
            setTimeout(() => {
                const serviceElement = document.getElementById(
                    "service-" + serviceId
                );
                centerServiceInContainer(serviceElement);
            }, 100);
        }
    }
};

// Fonction pour gérer le changement de slide Splide
const onSlideChange = (splide, newIndex) => {
    const realIndex = newIndex.index;
    if (activeIndex.value !== realIndex && services.value[realIndex]) {
        activeIndex.value = realIndex;
    }
};

// Vérifier si une description est active (et donc déployée)
const isActive = (index) => {
    return activeIndex.value === index;
};

// Observer les changements de activeIndex pour animer l'image
watch(activeIndex, (newVal) => {
    // Déclenchement de l'animation AOS pour l'image active
    setTimeout(() => {
        AOS.refresh();
    }, 100);
});

// Appel au montage
onMounted(() => {
    fetchServices();
    fetchPortfolio();

    // Initialisation d'AOS avec des options pour toute la page
    AOS.init({
        duration: 1000,
        once: true,
        mirror: false,
        easing: "ease-out-cubic",
        offset: 50,
        anchorPlacement: "top-center",
        startEvent: "DOMContentLoaded",
    });

    document.documentElement.style.overflowX = "hidden";
    document.body.style.overflowX = "hidden";

    // Vérifier si l'URL contient un fragment (ancre)
    const hash = window.location.hash;
    if (hash) {
        // Extraire l'ID de la section sans le #
        const sectionId = hash.substring(1);

        // Laisser le temps à la page de se charger complètement
        setTimeout(() => {
            scrollToSection(sectionId);

            // Si le hash commence par "service-", on extrait l'ID du service
            if (sectionId.startsWith("service-")) {
                const serviceId = parseInt(sectionId.replace("service-", ""));
                // Trouver l'index du service correspondant à cet ID
                const serviceIndex = services.value.findIndex(
                    (service) => service.id === serviceId
                );
                if (serviceIndex !== -1) {
                    // Mettre à jour l'index actif pour sélectionner le service
                    activeIndex.value = serviceIndex;

                    // Sur mobile, naviguer vers la slide correspondante avec Splide
                    if (window.innerWidth < 768 && splideRef.value) {
                        setTimeout(() => {
                            splideRef.value.go(serviceIndex);
                        }, 600);
                    }
                }
            }
        }, 300);
    }
});
</script>

<template>
    <PublicLayout @devisModalOpened="openDevisModal = $event">
        <section
            id="services"
            class="flex md:flex-row flex-col md:py-28 py-16 md:px-16 px-5 items-start md:gap-20 gap-6 bg-[#2D2D2D] text-white md:mt-[72px] mt-[64px]"
        >
            <article
                class="flex md:w-1/2 w-full flex-col items-start gap-8 flex-1"
                data-aos="fade-right"
                data-aos-delay="200"
            >
                <!-- Partie gauche avec animations -->
                <div
                    class="flex flex-col items-start md:gap-4 gap-3 self-stretch"
                    data-aos="fade-up"
                    data-aos-delay="300"
                >
                    <h3
                        class="font-inter font-semibold text-base"
                        data-aos="fade-right"
                        data-aos-delay="400"
                    >
                        Services
                    </h3>
                    <div
                        class="flex flex-col items-start md:gap-6 gap-5 self-stretch"
                    >
                        <h2
                            class="font-poppins md:text-5xl text-4xl font-medium"
                            data-aos="fade-up"
                            data-aos-delay="500"
                        >
                            Nos Services Électriques de Qualité
                        </h2>
                        <p
                            class="font-inter md:text-lg text-base"
                            data-aos="fade-up"
                            data-aos-delay="600"
                        >
                            Nous offrons une gamme complète de services
                            électriques adaptés à vos besoins. Découvrez notre
                            expertise en photovoltaïque, mise en conformité,
                            gros œuvre et bornes de recharge.
                        </p>
                    </div>
                </div>
                <!-- Version desktop - liste verticale -->
                <div
                    class="hidden md:flex flex-col items-start self-stretch max-h-[294px] overflow-y-auto hide-scrollbar"
                    data-aos="fade-up"
                    data-aos-delay="700"
                >
                    <div
                        v-for="(service, index) in services"
                        :key="index"
                        :id="'service-' + service.id"
                        class="w-full flex pr-4 py-4 pl-6 flex-col justify-center items-start gap-2 self-stretch cursor-pointer transition-all duration-300 ease-in-out"
                        :class="{
                            'border-l border-[#FF8C42] bg-[#242424] translate-x-2 scale-[1.02] z-10 shadow-lg':
                                isActive(index),
                            'border-l border-transparent hover:border-[#693a1d] hover:translate-x-2 hover:bg-[#242424] opacity-70 hover:opacity-90':
                                !isActive(index),
                        }"
                        @click="selectService(index)"
                    >
                        <h4
                            class="font-poppins md:text-2xl text-xl font-medium"
                        >
                            {{ service.title }}
                        </h4>
                        <div class="w-full">
                            <p
                                class="description font-inter text-base font-normal"
                                :class="
                                    isActive(index) ? 'expanded' : 'collapsed'
                                "
                            >
                                {{ service.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Version mobile - carrousel Splide -->
                <div
                    class="md:hidden w-full"
                    data-aos="fade-up"
                    data-aos-delay="700"
                >
                    <!-- Indicateurs de navigation -->
                    <div class="flex justify-center mb-4 gap-2">
                        <button
                            v-for="(service, index) in services"
                            :key="'indicator-' + index"
                            class="w-2 h-2 rounded-full transition-all duration-300"
                            :class="
                                isActive(index)
                                    ? 'bg-[#FF8C42] scale-125'
                                    : 'bg-gray-600 hover:bg-gray-500'
                            "
                            @click="selectService(index)"
                        ></button>
                    </div>

                    <!-- Carrousel Splide -->
                    <Splide
                        ref="splideRef"
                        :options="{
                            type: 'slide',
                            gap: '16px',
                            perPage: 1,
                            arrows: false,
                            pagination: false,
                            drag: true,
                            snap: true,
                            speed: 400,
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
                            v-for="(service, index) in services"
                            :key="'slide-' + index"
                            class="w-full"
                        >
                            <div
                                :id="'mobile-service-' + service.id"
                                class="flex flex-col p-6 bg-[#242424] rounded-lg border-l-4 transition-all duration-500 min-h-[160px] w-full"
                                :class="{
                                    'border-[#FF8C42] shadow-lg':
                                        isActive(index),
                                    'border-gray-600 opacity-70':
                                        !isActive(index),
                                }"
                            >
                                <h4
                                    class="font-poppins text-xl font-medium mb-3"
                                >
                                    {{ service.title }}
                                </h4>
                                <p class="font-inter text-sm leading-relaxed">
                                    {{ service.description }}
                                </p>
                            </div>
                        </SplideSlide>
                    </Splide>
                </div>
                <PrimaryButton
                    class="hidden md:flex"
                    @click="openDevisModal"
                    data-aos="fade-up"
                    data-aos-delay="500"
                >
                    Devis
                </PrimaryButton>
            </article>
            <div
                class="md:w-1/2 w-full flex justify-center items-center"
                data-aos="fade-left"
                data-aos-delay="400"
            >
                <div
                    v-if="services.length > 0"
                    class="relative w-full md:h-[640px] h-[346px]"
                >
                    <!-- Images avec animations -->
                    <div
                        v-for="(service, index) in services"
                        :key="index"
                        class="absolute top-0 left-0 w-full h-full"
                        :class="{ hidden: index !== activeIndex }"
                    >
                        <img
                            :src="service.image || '/images/placeholder.jpg'"
                            :alt="service.title"
                            class="w-full h-full object-cover rounded-lg shadow-lg"
                            data-aos="fade-left"
                            data-aos-duration="800"
                        />
                    </div>
                </div>
                <div
                    v-else
                    class="flex justify-center items-center h-96 w-full bg-gray-800 rounded-lg"
                    data-aos="fade-in"
                >
                    <p class="text-gray-400">Chargement des services...</p>
                </div>
            </div>
            <PrimaryButton
                class="md:hidden flex"
                @click="openDevisModal"
                data-aos="fade-up"
                data-aos-delay="500"
            >
                Devis
            </PrimaryButton>
        </section>
        <section
            id="portfolio"
            class="flex md:py-28 py-16 md:px-16 px-5 flex-col items-center md:gap-20 gap-12"
        >
            <div class="flex flex-col gap-4 text-[#0D0703]" data-aos="fade-up">
                <div class="relative flex flex-col md:gap-6 gap-5">
                    <h2
                        class="font-poppins md:text-5xl text-4xl text-center font-medium leading-[57.6px] tracking-[-0.48px]"
                    >
                        Nos réalisations
                    </h2>
                    <div
                        class="absolute bottom-10 right-1/2 border-2 border-[#FF8C42] w-[40%] md:min-w-[353px] min-w-[187px]"
                        data-aos="slide-right"
                        data-aos-duration="1000"
                    ></div>
                    <h4 class="font-inter md:text-lg text-base text-center">
                        Découvrez nos réalisations en électricité.
                    </h4>
                </div>
            </div>
            <div class="w-full" data-aos="fade-up" data-aos-delay="300">
                <div class="portfolio-gallery">
                    <div
                        v-for="item in portfolio"
                        :key="item.id"
                        class="portfolio-item overflow-hidden rounded-lg shadow-lg transition-all duration-300 hover:shadow-xl"
                    >
                        <div class="relative group">
                            <img
                                :src="item.image"
                                :alt="item.title"
                                class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-6"
                            >
                                <div
                                    class="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300"
                                >
                                    <h3
                                        class="font-poppins text-xl font-medium text-white mb-2"
                                    >
                                        {{ item.title }}
                                    </h3>
                                    <!-- Description affichée seulement au hover -->
                                    <p
                                        class="text-sm text-white/90 mb-3 line-clamp-3"
                                    >
                                        {{ item.description }}
                                    </p>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <span
                                            v-for="tag in item.tags"
                                            :key="tag.id"
                                            class="px-2 py-1 bg-[#FF8C42]/80 text-white text-xs rounded-full"
                                        >
                                            {{ tag.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
.hide-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
    scroll-behavior: smooth;
}

.hide-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

/* Styles pour les descriptions */
.description {
    transition: max-height 0.4s ease, opacity 0.3s ease;
    overflow: hidden;
    width: 100%;
}

.collapsed {
    max-height: 1.5em;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    opacity: 0.8;
}

.expanded {
    max-height: 300px;
    white-space: normal;
    opacity: 1;
}

/* Styles pour la galerie portfolio */
.portfolio-gallery {
    column-count: 3;
    column-gap: 24px;
    width: 100%;
}

.portfolio-item {
    break-inside: avoid;
    margin-bottom: 24px;
    position: relative;
    background-color: white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: inline-block;
    width: 100%;
}

.portfolio-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

/* Styles responsifs pour les colonnes */
@media (max-width: 1024px) {
    .portfolio-gallery {
        column-count: 2;
    }
}

@media (max-width: 640px) {
    .portfolio-gallery {
        column-count: 1;
    }
}

/* Animation personnalisée pour le chargement initial */
[data-aos] {
    pointer-events: none;
}

[data-aos].aos-animate {
    pointer-events: auto;
}

/* Styles pour le carrousel mobile avec parallaxe */
.snap-x {
    scroll-snap-type: x mandatory;
}

.snap-center {
    scroll-snap-align: center;
}

/* Animation smooth pour les transitions parallaxe */
.mobile-service-card {
    transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94),
        filter 0.4s ease, opacity 0.4s ease, scale 0.3s ease;
}

/* Amélioration du scroll horizontal */
.hide-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
    scroll-behavior: smooth;
}

.hide-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}

/* Indicateurs personnalisés */
.nav-indicator {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.nav-indicator:hover {
    transform: scale(1.2);
    opacity: 0.8;
}

/* Effet de profondeur pour les cartes inactives */
.depth-effect {
    transform-style: preserve-3d;
    perspective: 1000px;
}

/* Empêcher le débordement global pendant les animations */
:deep(body) {
    overflow-x: hidden !important;
}

/* Empêcher les débordements pendant les animations AOS */
:deep([data-aos]:not(.aos-animate)) {
    overflow: hidden;
}
</style>
