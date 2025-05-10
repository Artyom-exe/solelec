<script setup>
import { ref, onMounted, inject } from "vue";
import axios from "axios";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, router } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import Services from "@/Components/Services.vue";
import AOS from "aos";
import "aos/dist/aos.css";
import ReviewSlider from "@/Components/ReviewSlider.vue";
import InterventionMap from "@/Components/InterventionMap.vue";

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
    } else if (route && route !== "accueil") {
        // Si on doit naviguer vers une autre page
        const url = route === "accueil" ? "/" : "/" + route;
        router.visit(url);
    }
});

// Fonction pour le défilement local sur la même page
const scrollToSection = (sectionId) => {
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
};

const props = defineProps({
    limit: {
        type: Number,
        default: 2,
    },
});

const openDevisModal = ref(() => {
    console.log("La fonction openDevisModal n'a pas encore été chargée");
});
const portfolio = ref([]);
const tags = ref([]);
const randomPortfolio = ref([]);

const faq = ref([]);
const openedItems = ref([]);

const contactForm = ref({
    name: "",
    email: "",
    message: "",
    acceptConditions: false,
});

const formStatus = ref({
    success: false,
    errors: {
        name: false,
        email: false,
        message: false,
        acceptConditions: false,
        general: false,
    },
    message: "",
});

const validateForm = () => {
    // Réinitialiser les erreurs
    formStatus.value.errors = {
        name: false,
        email: false,
        message: false,
        acceptConditions: false,
        general: false,
    };

    let isValid = true;

    // Validation des champs
    if (!contactForm.value.name.trim()) {
        formStatus.value.errors.name = true;
        isValid = false;
    }

    if (!contactForm.value.email.trim()) {
        formStatus.value.errors.email = true;
        isValid = false;
    } else {
        // Validation basique du format email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(contactForm.value.email)) {
            formStatus.value.errors.email = true;
            isValid = false;
        }
    }

    if (!contactForm.value.message.trim()) {
        formStatus.value.errors.message = true;
        isValid = false;
    }

    if (!contactForm.value.acceptConditions) {
        formStatus.value.errors.acceptConditions = true;
        isValid = false;
    }

    return isValid;
};

const sendEmail = async () => {
    // Réinitialiser le statut du formulaire
    formStatus.value.success = false;
    formStatus.value.message = "";

    // Valider le formulaire avant envoi
    if (!validateForm()) {
        return;
    }

    try {
        await axios.post("send-email", contactForm.value);

        // Mise à jour du statut en cas de succès
        formStatus.value.success = true;
        formStatus.value.message =
            "Votre message a été envoyé avec succès! Nous vous répondrons dans les meilleurs délais.";

        // Réinitialiser le formulaire après l'envoi
        contactForm.value = {
            name: "",
            email: "",
            message: "",
            acceptConditions: false,
        };
    } catch (error) {
        console.error("Erreur lors de l'envoi du message:", error);
        formStatus.value.errors.general = true;

        // Afficher plus de détails sur l'erreur
        if (error.response) {
            console.error("Données d'erreur:", error.response.data);
            console.error("Status:", error.response.status);

            // Traitement des erreurs de validation du serveur
            if (error.response.status === 422 && error.response.data.errors) {
                const serverErrors = error.response.data.errors;

                if (serverErrors.name) formStatus.value.errors.name = true;
                if (serverErrors.email) formStatus.value.errors.email = true;
                if (serverErrors.message)
                    formStatus.value.errors.message = true;
                if (serverErrors.acceptConditions)
                    formStatus.value.errors.acceptConditions = true;

                formStatus.value.message =
                    "Veuillez corriger les erreurs dans le formulaire.";
            } else {
                formStatus.value.message =
                    error.response.data.message ||
                    "Une erreur est survenue lors de l'envoi du message. Veuillez réessayer plus tard.";
            }
        } else {
            formStatus.value.message =
                "Une erreur de connexion est survenue. Veuillez vérifier votre connexion internet et réessayer.";
        }
    }
};

const toggle = (id) => {
    const index = openedItems.value.indexOf(id);
    if (index === -1) {
        openedItems.value.push(id);
    } else {
        openedItems.value.splice(index, 1);
    }
};

const fetchFaq = async () => {
    try {
        const response = await axios.get("faq");
        faq.value = response.data;

        if (faq.value.length > 0) {
            openedItems.value.push(faq.value[0].id);
        }
    } catch (error) {
        console.error(error);
    }
};

const fetchPortfolio = async () => {
    try {
        const response = await axios.get("portfolios");
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
        const response = await axios.get("tags");
        tags.value = response.data;
    } catch (error) {
        console.error(error);
    }
};
fetch;

// Définition des images pour les 2 colonnes
const leftImages = ref([
    {
        src: "/images/header/ouvrier-dos.jpg",
        alt: "Électricien Solelec de dos travaillant sur une installation",
        aosDelay: 0,
    },
    {
        src: "/images/header/electricien.webp",
        alt: "Technicien qualifié Solelec en intervention",
        aosDelay: 200,
    },
    {
        src: "/images/header/ouvrier-panneau.jpg",
        alt: "Installation de panneau solaire par l'équipe Solelec",
        aosDelay: 400,
    },
]);

const rightImages = ref([
    {
        src: "/images/header/ouvrier-tableau-electrique.jpg",
        alt: "Expert Solelec travaillant sur un tableau électrique",
        aosDelay: 100,
    },
    {
        src: "/images/header/plan-travail.jpg",
        alt: "Plan d'installation électrique étudié par Solelec",
        aosDelay: 300,
    },
    {
        src: "/images/header/tableau-electrique-zoom.jpg",
        alt: "Gros plan sur tableau électrique installé par Solelec",
        aosDelay: 500,
    },
]);

onMounted(() => {
    // Vérifier si on est sur desktop (largeur d'écran >= 1024px)
    const isDesktop = window.innerWidth >= 1024;
    
    // Configuration AOS - activer uniquement sur desktop
    AOS.init({
        duration: 1000,
        once: true,
        mirror: false,
        easing: "ease-out-cubic",
        offset: 50,
        anchorPlacement: "top-center",
        startEvent: "DOMContentLoaded",
        // Désactiver AOS sur mobile en définissant disable sur true si ce n'est pas desktop
        disable: !isDesktop
    });

    // Empêcher le débordement horizontal
    document.documentElement.style.overflowX = "hidden";

    fetchPortfolio();
    fetchTags();
    fetchFaq();

    // Ajouter un écouteur d'événement pour redémarrer AOS lors du redimensionnement
    window.addEventListener('resize', () => {
        const isDesktopNow = window.innerWidth >= 1024;
        // Réinitialiser AOS avec la nouvelle valeur de disable
        AOS.init({
            duration: 1000,
            once: true,
            mirror: false,
            easing: "ease-out-cubic",
            offset: 50,
            anchorPlacement: "top-center",
            disable: !isDesktopNow
        });
    });

    setTimeout(() => {
        AOS.refresh();
    }, 200);
});
</script>

<template>
    <PublicLayout title="Solelec" @devisModalOpened="openDevisModal = $event">
        <Head>
            <title>
                Solelec - Expert en électricité & énergies renouvelables
            </title>
            <meta
                name="description"
                content="Solutions électriques innovantes et éco-responsables pour vos projets. Expertise en électricité et énergies renouvelables par Solelec."
            />
            <meta
                name="keywords"
                content="électricité, énergies renouvelables, installation électrique, panneau solaire"
            />
        </Head>

        <!-- Header -->

        <header
            id="header"
            class="flex flex-col items-center gap-2 bg-[#2D2D2D] h-[calc(100vh-72px)] md:px-16 px-5 overflow-hidden md:mt-[72px] mt-[64px] relative"
        >
            <div
                class="flex flex-col lg:flex-row items-center flex-1 self-stretch h-full w-full"
            >
                <div
                    class="flex flex-col md:pr-20 justify-center items-start gap-8 flex-1"
                    data-aos="fade-right"
                    data-aos-duration="1200"
                >
                    <div class="flex flex-col items-start gap-6 text-white">
                        <h1
                            class="text-white font-poppins text-[56px] font-medium leading-[120%] tracking-[-0.56px]"
                            data-aos="fade-up"
                            data-aos-delay="150"
                            data-aos-anchor="#header"
                        >
                            S<span class="text-[#FF8C42]">o</span>lelec - Votre
                            expert en électricité & énergies renouvelables
                        </h1>

                        <p
                            class="font-inter text-lg font-normal leading-[150%]"
                            data-aos="fade-up"
                            data-aos-delay="300"
                            data-aos-anchor="#header"
                        >
                            Découvrez des solutions électriques innovantes et
                            éco-responsables. Faites confiance à notre expertise
                            pour vos projets d'énergie renouvelable.
                        </p>
                    </div>

                    <div
                        class="flex gap-4"
                        data-aos="fade-up"
                        data-aos-delay="450"
                        data-aos-anchor="#header"
                    >
                        <PrimaryButton @click="openDevisModal"
                            >Demander un devis</PrimaryButton
                        >

                        <SecondaryButton
                            @click="navigateToSection('contact', 'accueil')"
                        >
                            Nous contacter
                        </SecondaryButton>
                    </div>
                </div>
                <div
                    class="flex-1 h-full relative"
                    data-aos="fade-left"
                    data-aos-duration="1200"
                >
                    <div
                        class="absolute left-0 w-[calc(50%-8px)] h-[130%] flex flex-col gap-4"
                        style="top: -20%; bottom: 0"
                    >
                        <div
                            v-for="(img, index) in leftImages"
                            :key="'left-' + index"
                            class="h-[33.33%] overflow-hidden rounded-lg"
                            data-aos="fade-up"
                            :data-aos-delay="img.aosDelay"
                            data-aos-anchor="#header"
                        >
                            <img
                                :src="img.src"
                                :alt="img.alt"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            />
                        </div>
                    </div>

                    <div
                        class="absolute right-0 w-[calc(50%-8px)] h-[130%] flex flex-col gap-4"
                        style="top: -5%; bottom: 0"
                    >
                        <div
                            v-for="(img, index) in rightImages"
                            :key="'right-' + index"
                            class="h-[33.33%] overflow-hidden rounded-lg"
                            data-aos="fade-up"
                            :data-aos-delay="img.aosDelay"
                            data-aos-anchor="#header"
                        >
                            <img
                                :src="img.src"
                                :alt="img.alt"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- Services -->
        <section
            id="services"
            class="flex py-28 px-16 flex-col gap-20 bg-[#FBFAF6]"
        >
            <div class="flex flex-col gap-4 text-[#0D0703]" data-aos="fade-up">
                <h2 class="text-center font-inter text-base font-semibold">
                    services
                </h2>
                <div class="relative flex flex-col gap-6">
                    <h3
                        class="font-poppins text-5xl text-center font-medium leading-[57.6px] tracking-[-0.48px]"
                    >
                        Nos Services Principaux
                    </h3>
                    <div
                        class="absolute bottom-10 right-1/2 border-2 border-[#FF8C42] w-[40%] max-w-[353px] min-w-[200px]"
                        data-aos="slide-right"
                        data-aos-duration="1000"
                    ></div>
                    <h4 class="font-inter text-lg text-center">
                        Des solutions adaptées à vos besoins énergétiques.
                    </h4>
                </div>
            </div>
            <Services
                data-aos="zoom-in"
                data-aos-duration="800"
                :serviceIds="[4, 2, 3]"
            />
        </section>

        <!-- À propos -->
        <section
            id="a-propos"
            class="flex py-28 px-16 gap-20 bg-[#2D2D2D] text-white"
        >
            <div
                class="flex flex-col flex-1 gap-4"
                data-aos="fade-right"
                data-aos-duration="1000"
            >
                <h2 class="font-poppins text-[40px] leading-[48px] font-medium">
                    Découvrez l'expertise de S<span class="text-[#FF8C42]"
                        >o</span
                    >lelec en électricité et énergies renouvelables.
                </h2>
            </div>
            <div
                class="flex flex-1 flex-col gap-6"
                data-aos="fade-left"
                data-aos-duration="1000"
                data-aos-delay="200"
            >
                <p class="font-inter text-lg font-normal">
                    S<span class="text-[#FF8C42]">o</span>lelec, c'est 3 ans
                    d'expertise dans les solutions électriques et
                    photovoltaïques. Nous guidons nos clients à chaque étape de
                    leur projet, garantissant un service de qualité et des
                    installations conformes aux normes. Faites confiance à notre
                    savoir-faire pour des solutions adaptées à vos besoins.
                </p>
            </div>
        </section>

        <!-- Portfolio -->
        <section
            id="portfolioAccueil"
            class="flex py-[120px] px-16 flex-col items-center gap-20 bg-[#F5F5F5] text-[#0D0703]"
        >
            <div
                class="flex flex-col gap-4 text-[#0D0703]"
                data-aos="fade-up"
                dataaos-duration="800"
            >
                <h2 class="text-center font-inter text-base font-semibold">
                    portfolio
                </h2>
                <div class="relative flex flex-col gap-6">
                    <h3
                        class="font-poppins text-5xl text-center font-medium leading-[57.6px] tracking-[-0.48px]"
                    >
                        Nos projets récents
                    </h3>
                    <div
                        class="absolute bottom-10 left-1/2 border-2 border-[#FF8C42] w-[80%] max-w-[353px] min-w-[200px]"
                        data-aos="slide-left"
                        data-aos-duration="1000"
                    ></div>
                    <h4 class="font-inter text-lg text-center">
                        Découvrez notre expertise à travers nos réalisations.
                    </h4>
                </div>
            </div>
            <div class="flex flex-col gap-16 items-center">
                <div class="flex items-start gap-12 flex-wrap justify-center">
                    <article
                        v-for="(item, index) in randomPortfolio"
                        :key="index"
                        class="flex flex-col items-start gap-6 flex-1"
                        data-aos="fade-up"
                        :data-aos-delay="index * 150"
                    >
                        <img
                            :src="item.image"
                            :alt="item.title"
                            class="w-full max-h-[356px] min-h-[200px] aspect-[4/3] object-cover rounded-lg"
                        />
                        <div class="flex flex-col gap-4 text-[#0D0703]">
                            <h5 class="font-poppins text-2xl font-medium">
                                {{ item.title }}
                            </h5>
                            <p class="font-inter text-base">
                                {{ item.description }}
                            </p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span
                                    v-for="tag in item.tags"
                                    :key="tag.id"
                                    class="px-[10px] py-1 rounded font-inter text-sm font-semibold border border-[#0D0703] border-opacity-15 bg-[#0D0703] bg-opacity-5"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </article>
                </div>
                <SecondaryButton
                    variant="dark"
                    @click="
                        navigateToSection('portfolio', 'services-portfolio')
                    "
                    >Voir plus</SecondaryButton
                >
            </div>
        </section>

        <!-- Avis clients -->

        <section
            id="avis-client"
            class="flex py-28 px-16 flex-col items-center gap-20 bg-[#2D2D2D] text-white"
        >
            <div
                class="flex flex-col gap-4"
                data-aos="fade-up"
                dataaos-duration="800"
            >
                <div class="relative flex flex-col gap-6">
                    <h3
                        class="font-poppins text-5xl text-center font-medium leading-[57.6px] tracking-[-0.48px]"
                    >
                        Témoignages clients
                    </h3>
                    <div
                        class="absolute bottom-10 right-1/2 border-2 border-[#FF8C42] w-[80%] max-w-[353px] min-w-[200px]"
                        data-aos="slide-right"
                        data-aos-duration="1000"
                    ></div>
                    <h4 class="font-inter text-lg text-center">
                        Nos clients partagent leur expérience avec Solelec.
                    </h4>
                </div>
            </div>

            <div class="w-screen -mx-16">
                <ReviewSlider data-aos="fade-up" data-aos-duration="1000" />
            </div>

            <SecondaryButton
                :to="'https://search.google.com/local/writereview?placeid=ChIJIyOb7r_HvWkRJGDmFy5S2j4'"
                >Donner votre avis sur Google
            </SecondaryButton>
        </section>

        <!-- FAQ -->
        <section
            id="faq"
            class="flex flex-col py-28 px-16 gap-20 bg-[#F5F5F5] text-white"
        >
            <div class="flex flex-col gap-4 text-[#0D0703]" data-aos="fade-up">
                <div class="relative flex flex-col gap-6">
                    <h2
                        class="font-poppins text-5xl text-left font-medium leading-[57.6px] tracking-[-0.48px]"
                    >
                        FAQs
                    </h2>
                    <div
                        class="absolute bottom-[76px] ml-36 border-2 border-[#FF8C42] w-[100%] max-w-[353px] min-w-[200px]"
                        data-aos="slide-left"
                        data-aos-duration="1000"
                    ></div>
                    <h4 class="font-inter text-lg text-left">
                        Voici les réponses aux questions les plus fréquentes
                        concernant nos services.
                    </h4>
                </div>
            </div>
            <div
                class="flex flex-col border-b border-[#0D070326/15] text-[#0D0703]"
            >
                <div
                    v-for="(item, index) in faq"
                    :key="item.id"
                    class="flex flex-col font-inter border-t border-[#0D070326/15]"
                    data-aos="fade-up"
                    :data-aos-delay="index * 100"
                    data-aos-duration="800"
                    data-aos-anchor="#faq"
                >
                    <div
                        class="flex items py-5 gap-6 justify-between cursor-pointer"
                        @click="toggle(item.id)"
                    >
                        <h5 class="text-lg font-bold">{{ item.question }}</h5>
                        <svg
                            v-if="openedItems.includes(item.id)"
                            xmlns="http://www.w3.org/2000/svg"
                            width="32"
                            height="33"
                            viewBox="0 0 32 33"
                            fill="none"
                            class="transform transition-transform duration-300"
                        >
                            <path
                                d="M23.8233 21.0656L23.8232 21.0656L23.4697 21.4192C23.4697 21.4192 23.4697 21.4192 23.4697 21.4192C23.3721 21.5168 23.2138 21.5168 23.1162 21.4192C23.1162 21.4192 23.1162 21.4192 23.1161 21.4192L16.3536 14.6566L16 14.303L15.6465 14.6566L8.88388 21.4192C8.78625 21.5168 8.62803 21.5168 8.53039 21.4192L8.17679 21.0656C8.07915 20.968 8.07915 20.8097 8.17679 20.7121L15.8233 13.0656C15.9209 12.968 16.0791 12.968 16.1768 13.0656L23.8233 20.7121C23.8233 20.7121 23.8233 20.7121 23.8233 20.7121C23.9209 20.8097 23.9209 20.968 23.8233 21.0656Z"
                                fill="#0D0703"
                                stroke="#0D0703"
                            />
                        </svg>
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            width="32"
                            height="33"
                            viewBox="0 0 32 33"
                            fill="none"
                            class="transform transition-transform duration-300"
                        >
                            <path
                                d="M8.17679 13.0656L8.17686 13.0656L8.53046 12.712C8.5305 12.712 8.53054 12.712 8.53058 12.712C8.62819 12.6144 8.78638 12.6144 8.884 12.712C8.88404 12.712 8.88408 12.712 8.88412 12.712L15.6465 19.4744L16 19.828L16.3535 19.4744L23.1162 12.712C23.2138 12.6144 23.372 12.6144 23.4697 12.712L23.8233 13.0656C23.921 13.1632 23.921 13.3214 23.8233 13.4191L16.1768 21.0656C16.0792 21.1632 15.9209 21.1632 15.8233 21.0656L8.17679 13.4191C8.17676 13.4191 8.17672 13.419 8.17679 13.4191C8.07919 13.3214 8.07919 13.1632 8.17679 13.0656Z"
                                fill="#0D0703"
                                stroke="#0D0703"
                            />
                        </svg>
                    </div>

                    <div
                        class="overflow-hidden transition-all duration-300 ease-in-out"
                        :class="
                            openedItems.includes(item.id)
                                ? 'max-h-96 opacity-100'
                                : 'max-h-0 opacity-0'
                        "
                    >
                        <p
                            class="font-inter text-base pb-6 gap-4 max-w-[768px] min-w-[600px]"
                        >
                            {{ item.answer }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-6" data-aos="fade-up">
                <div class="flex flex-col gap-4 text-[#0D0703]">
                    <h3
                        class="font-poppins text-[32px] font-medium leading-[41.6px]"
                    >
                        Des questions supplémentaires ?
                    </h3>
                    <p class="font-inter text-lg">
                        N'hésitez pas à nous contacter.
                    </p>
                </div>
                <SecondaryButton
                    class="self-start"
                    variant="dark"
                    @click="scrollToSection('contact')"
                    >Contact</SecondaryButton
                >
            </div>
        </section>

        <!-- Contact -->
        <section
            id="contact"
            class="flex py-28 px-16 flex-col gap-20 text-white bg-[#2D2D2D]"
        >
            <div class="flex gap-20">
                <div
                    class="flex flex-col gap-8 flex-1"
                    data-aos="fade-right"
                    data-aos-duration="1000"
                >
                    <h2 class="font-inter text-base font-semibold">Contact</h2>
                    <div class="relative flex flex-col gap-6">
                        <h3 class="font-poppins text-5xl font-medium">
                            Nous contacter
                        </h3>
                        <div
                            class="absolute bottom-[40px] ml-28 border-2 border-[#FF8C42] w-[100%] max-w-[353px] min-w-[200px]"
                            data-aos="slide-left"
                            data-aos-duration="1000"
                        ></div>
                        <p class="font-inter text-lg">
                            Nous sommes là pour répondre à vos questions.
                        </p>
                    </div>
                    <div
                        class="flex flex-col py-2 gap-4 font-inter max-w-[255px] min-w-[155px]"
                    >
                        <div
                            class="flex gap-4"
                            data-aos="fade-up"
                            data-aos-delay="100"
                        >
                            <img
                                src="/assets/icons/contact/envelope-solid.svg"
                                alt="Icône d'une enveloppe"
                                class="w-6 h-6 filter invert brightness-100"
                            />
                            <p>solelec.lmbt@gmail.com</p>
                        </div>
                        <div
                            class="flex gap-4"
                            data-aos="fade-up"
                            data-aos-delay="200"
                        >
                            <img
                                src="/assets/icons/contact/phone-solid.svg"
                                alt="Icône d'une enveloppe"
                                class="w-6 h-6 filter invert brightness-100"
                            />
                            <p>0492 51 09 31</p>
                        </div>
                        <div
                            class="flex gap-4"
                            data-aos="fade-up"
                            data-aos-delay="300"
                        >
                            <img
                                src="/assets/icons/contact/location-dot-solid.svg"
                                alt="Icône d'une enveloppe"
                                class="w-6 h-6 filter invert brightness-100"
                            />
                            <p>
                                Rue de Neufmoustier 4, 1348
                                Ottignies-Louvain-la-Neuve, Belgium
                            </p>
                        </div>
                    </div>
                </div>
                <form
                    @submit.prevent="sendEmail"
                    class="flex flex-col gap-6 flex-1 font-inter"
                    data-aos="fade-left"
                    data-aos-duration="1000"
                    data-aos-delay="200"
                >
                    <!-- Message de statut général -->
                    <div
                        v-if="formStatus.message && !formStatus.success"
                        class="p-4 bg-red-800/30 border border-red-700 rounded-md mb-2 text-sm"
                    >
                        {{ formStatus.message }}
                    </div>

                    <div
                        v-if="formStatus.success"
                        class="p-4 bg-green-800/30 border border-green-700 rounded-md mb-2 text-sm"
                    >
                        {{ formStatus.message }}
                    </div>

                    <div
                        class="flex flex-col gap-2"
                        data-aos="fade-up"
                        data-aos-delay="300"
                        data-aos-anchor="#contact"
                    >
                        <label for="name"
                            >Nom<span class="text-red-500">*</span></label
                        >
                        <input
                            type="text"
                            id="name"
                            v-model="contactForm.name"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500':
                                    formStatus.errors.name,
                            }"
                            class="w-full bg-white/10 border border-white/20 rounded-[6px] focus:ring-[#FF8C42] focus:border-[#FF8C42] p-2"
                        />
                        <span
                            v-if="formStatus.errors.name"
                            class="text-red-400 text-sm mt-1"
                            >Veuillez entrer votre nom</span
                        >
                    </div>

                    <div
                        class="flex flex-col gap-2"
                        data-aos="fade-up"
                        data-aos-delay="400"
                        data-aos-anchor="#contact"
                    >
                        <label for="email"
                            >Email<span class="text-red-500">*</span></label
                        >
                        <input
                            type="email"
                            id="email"
                            v-model="contactForm.email"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500':
                                    formStatus.errors.email,
                            }"
                            class="w-full bg-white/10 border border-white/20 rounded-[6px] focus:ring-[#FF8C42] focus:border-[#FF8C42] p-2"
                        />
                        <span
                            v-if="formStatus.errors.email"
                            class="text-red-400 text-sm mt-1"
                            >Veuillez entrer une adresse email valide</span
                        >
                    </div>

                    <div
                        class="flex flex-col gap-2"
                        data-aos="fade-up"
                        data-aos-delay="500"
                        data-aos-anchor="#contact"
                    >
                        <label for="message"
                            >Message<span class="text-red-500">*</span></label
                        >
                        <textarea
                            id="message"
                            v-model="contactForm.message"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500':
                                    formStatus.errors.message,
                            }"
                            class="w-full h-[180px] bg-white/10 border border-white/20 rounded-[6px] focus:ring-[#FF8C42] focus:border-[#FF8C42] p-2"
                        ></textarea>
                        <span
                            v-if="formStatus.errors.message"
                            class="text-red-400 text-sm mt-1"
                            >Veuillez entrer votre message</span
                        >
                    </div>

                    <div
                        class="flex pb-4"
                        data-aos="fade-up"
                        data-aos-delay="600"
                        data-aos-anchor="#contact"
                    >
                        <label
                            class="flex gap-2 items-start"
                            :class="{
                                'text-red-400':
                                    formStatus.errors.acceptConditions,
                            }"
                        >
                            <input
                                type="checkbox"
                                v-model="contactForm.acceptConditions"
                                :class="{
                                    'outline-red-500':
                                        formStatus.errors.acceptConditions,
                                }"
                                class="w-4 h-4 border border-white/20 bg-white/10 rounded-[2px] focus:ring-[#FF8C42] focus:border-[#FF8C42]"
                            />
                            <span class="text-sm"
                                >J'accepte les conditions générales
                                d'utilisation<span class="text-red-500"
                                    >*</span
                                ></span
                            >
                        </label>
                    </div>

                    <div
                        data-aos="fade-up"
                        data-aos-delay="700"
                        data-aos-anchor="#contact"
                    >
                        <PrimaryButton type="submit">Envoyer</PrimaryButton>
                    </div>
                </form>
            </div>
        </section>

        <!-- zones d'intervention -->
        <section
            id="zones"
            class="flex py-28 px-16 flex-col gap-20 bg-[#F5F5F5] text-[#0D0703]"
        >
            <div
                class="flex flex-col gap-8 flex-1"
                data-aos="fade-right"
                data-aos-duration="1000"
            >
                <h2 class="font-inter text-base font-semibold">Zones</h2>
                <div class="relative flex flex-col gap-6">
                    <h3 class="font-poppins text-5xl font-medium">
                        Interventions
                    </h3>
                    <div
                        class="absolute bottom-[72px] ml-[340px] border-2 border-[#FF8C42] w-[100%] max-w-[353px] min-w-[200px]"
                        data-aos="slide-left"
                        data-aos-duration="1000"
                    ></div>
                    <p class="font-inter text-lg">
                        Nous intervenons dans
                        <strong>toute la Wallonie</strong> et à
                        <strong>Bruxelles</strong>. Voici quelques villes
                        régulièrement desservies :
                    </p>
                </div>
            </div>

            <InterventionMap />
        </section>
    </PublicLayout>
</template>

<style scoped>
/* Ajoutez ces styles pour gérer l'effet de débordement voulu tout en préservant l'apparence */
#header {
    overflow: hidden;
}

/* Assurez-vous que les colonnes peuvent déborder en toute sécurité */
#header > div {
    overflow: visible;
}
</style>
