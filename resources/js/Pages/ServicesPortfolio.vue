<script setup>
import { ref, onMounted, watch, inject } from "vue";
import axios from "axios";
import { router, usePage } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import MasonryWall from "@yeger/vue-masonry-wall";
// Importez AOS
import AOS from "aos";
import "aos/dist/aos.css";

const props = defineProps({
    serviceId: [Number, String],
});

// Injecter la fonction navigateToSection depuis PublicLayout avec fallback
const navigateToSection = inject("navigateToSection", (sectionId, route) => {
    // Version fallback de la fonction
    const element = document.getElementById(sectionId);
    if (element) {
        const offset = 72;
        const elementPosition =
            element.getBoundingClientRect().top + window.scrollY;
        const offsetPosition = elementPosition - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: "smooth",
        });
    }
});

// Fonction locale de défilement
const scrollToSection = (sectionId) => {
    const element = document.getElementById(sectionId);
    if (element) {
        const offset = 72; // Hauteur du header + marge additionnelle
        const elementPosition =
            element.getBoundingClientRect().top + window.scrollY;
        const offsetPosition = elementPosition - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: "smooth",
        });
    }
};

const openDevisModal = ref(null);
const services = ref([]);
const activeIndex = ref(0);
const prevActiveIndex = ref(0);
// Ajout de la définition de portfolio
const portfolio = ref([]);

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

// Fonction pour vérifier et sélectionner un service à partir du hash de l'URL ou des props
const checkForServiceSelection = () => {
    // 1. Vérifier d'abord si un serviceId a été passé dans les props
    if (props.serviceId) {
        const serviceId = parseInt(props.serviceId);
        console.log("Service ID à sélectionner depuis props:", serviceId);

        // Trouver l'index du service correspondant à cet ID
        const serviceIndex = services.value.findIndex(
            (service) => service.id === serviceId
        );
        console.log("Index du service trouvé:", serviceIndex);

        if (serviceIndex !== -1) {
            // Mettre à jour l'index actif pour sélectionner le service
            activeIndex.value = serviceIndex;
            console.log("activeIndex mis à jour:", activeIndex.value);

            // Faire défiler jusqu'au service spécifique dans le conteneur
            setTimeout(() => {
                const serviceElement = document.getElementById(
                    "service-" + serviceId
                );
                if (serviceElement) {
                    console.log("Élément service trouvé:", serviceElement);
                    // Faire défiler le conteneur parent (avec la classe overflow-y-auto)
                    const container =
                        serviceElement.closest(".overflow-y-auto");
                    if (container) {
                        console.log("Conteneur trouvé:", container);
                        // Calcul précis de la position de défilement
                        const containerTop =
                            container.getBoundingClientRect().top;
                        const elementTop =
                            serviceElement.getBoundingClientRect().top;
                        const scrollOffset = elementTop - containerTop - 20; // 20px de marge pour la lisibilité

                        console.log("Position de défilement:", scrollOffset);
                        container.scrollTop = scrollOffset;

                        // Alternative: utiliser scrollIntoView pour un défilement plus naturel
                        serviceElement.scrollIntoView({
                            behavior: "smooth",
                            block: "center",
                        });
                    }
                }
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
                // Faire défiler vers le service
                setTimeout(() => {
                    const serviceElement = document.getElementById(
                        hash.substring(1)
                    );
                    if (serviceElement) {
                        // Faire défiler le conteneur parent
                        const container =
                            serviceElement.closest(".overflow-y-auto");
                        if (container) {
                            container.scrollTop =
                                serviceElement.offsetTop - container.offsetTop;
                        }
                    }
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
    console.log("Props reçues:", props);
    console.log("ServiceId:", props.serviceId);

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
            class="flex py-28 px-16 items-start gap-20 bg-[#2D2D2D] text-white"
        >
            <article
                class="flex w-1/2 flex-col items-start gap-8 flex-1"
                data-aos="fade-right"
                data-aos-delay="200"
            >
                <!-- Partie gauche avec animations -->
                <div
                    class="flex flex-col items-start gap-4 self-stretch"
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
                    <div class="flex flex-col items-start gap-6 self-stretch">
                        <h2
                            class="font-poppins text-5xl font-medium"
                            data-aos="fade-up"
                            data-aos-delay="500"
                        >
                            Nos Services Électriques de Qualité
                        </h2>
                        <p
                            class="font-inter text-lg"
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
                <div
                    class="flex flex-col items-start self-stretch h-[294px] overflow-y-auto hide-scrollbar"
                    data-aos="fade-up"
                    data-aos-delay="700"
                >
                    <div
                        v-for="(service, index) in services"
                        :key="index"
                        :id="'service-' + service.id"
                        class="w-full flex pr-4 py-4 pl-6 flex-col justify-center items-start gap-2 self-stretch cursor-pointer hover:border-l hover:border-[#FF8C42] hover:bg-[#242424] transition duration-300 ease-in-out"
                        :class="{
                            'border-l border-[#FF8C42] bg-[#242424]':
                                isActive(index),
                        }"
                        @click="selectService(index)"
                    >
                        <h4 class="font-poppins text-2xl font-medium">
                            {{ service.title }}
                        </h4>
                        <div class="w-full">
                            <p
                                class="description font-inter"
                                :class="
                                    isActive(index) ? 'expanded' : 'collapsed'
                                "
                            >
                                {{ service.description }}
                            </p>
                        </div>
                    </div>
                </div>
                <PrimaryButton
                    @click="openDevisModal"
                    data-aos="fade-up"
                    data-aos-delay="500"
                >
                    Devis
                </PrimaryButton>
            </article>
            <div
                class="w-1/2 flex justify-center items-center"
                data-aos="fade-left"
                data-aos-delay="400"
            >
                <div
                    v-if="services.length > 0"
                    class="relative w-full h-[640px]"
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
        </section>
        <section
            id="portfolio"
            class="flex py-28 px-16 flex-col items-center gap-20"
        >
            <div class="flex flex-col gap-4 text-[#0D0703]" data-aos="fade-up">
                <div class="relative flex flex-col gap-6">
                    <h2
                        class="font-poppins text-5xl text-center font-medium leading-[57.6px] tracking-[-0.48px]"
                    >
                        Nos réalisations
                    </h2>
                    <div
                        class="absolute bottom-10 right-1/2 border-2 border-[#FF8C42] w-[40%] min-w-[353px]"
                        data-aos="slide-right"
                        data-aos-duration="1000"
                    ></div>
                    <h4 class="font-inter text-lg text-center">
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
</style>
