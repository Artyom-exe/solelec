<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const openDevisModal = ref(null);
const services = ref([]);
const activeIndex = ref(0);

// Récupérer les services depuis l'API Laravel
const fetchServices = async () => {
    try {
        const response = await axios.get("/services");
        services.value = response.data;
    } catch (error) {
        console.error("Erreur lors de la récupération des services:", error);
    }
};

// Sélectionner un service
const selectService = (index) => {
    activeIndex.value = index;
};

// Vérifier si une description est active (et donc déployée)
const isActive = (index) => {
    return activeIndex.value === index;
};

// Appel au montage
onMounted(() => {
    fetchServices();
});
</script>

<template>
    <PublicLayout @devisModalOpened="openDevisModal = $event">
        <section
            class="flex py-28 px-16 items-start gap-20 bg-[#2D2D2D] text-white"
        >
            <article class="flex flex-col items-start gap-8 flex-1">
                <div class="flex flex-col items-start gap-4 self-stretch">
                    <h3 class="font-inter font-semibold text-base">Services</h3>
                    <div class="flex flex-col items-start gap-6 self-stretch">
                        <h2 class="font-poppins text-5xl font-medium">
                            Nos Services Électriques de Qualité
                        </h2>
                        <p class="font-inter text-lg">
                            Nous offrons une gamme complète de services
                            électriques adaptés à vos besoins. Découvrez notre
                            expertise en photovoltaïque, mise en conformité,
                            gros œuvre et bornes de recharge.
                        </p>
                    </div>
                </div>
                <div
                    class="flex flex-col items-start self-stretch h-[294px] overflow-y-auto hide-scrollbar"
                >
                    <div
                        v-for="(service, index) in services"
                        :key="index"
                        class="flex pr-4 py-4 pl-6 flex-col justify-center items-start gap-2 self-stretch hover:border-l hover:border-[#FF8C42] hover:bg-[#242424] cursor-pointer"
                        :class="{
                            'border-l border-[#FF8C42] bg-[#242424]':
                                isActive(index),
                        }"
                        @click="selectService(index)"
                    >
                        <h4 class="font-poppins text-2xl font-medium">
                            {{ service.title }}
                        </h4>
                        <div class="description-container">
                            <p
                                class="font-inter transition-height duration-300 ease-in-out"
                                :class="{ 'line-clamp-1': !isActive(index) }"
                            >
                                {{ service.description }}
                            </p>
                        </div>
                    </div>
                </div>
                <PrimaryButton @click="openDevisModal"> Devis </PrimaryButton>
            </article>
            <div class="w-1/2 flex justify-center items-center">
                <div
                    v-if="services.length > 0"
                    class="relative w-full h-[640px]"
                >
                    <transition name="fade" mode="out-in">
                        <img
                            :key="activeIndex"
                            :src="
                                services[activeIndex]?.image ||
                                '/images/placeholder.jpg'
                            "
                            :alt="services[activeIndex]?.title"
                            class="w-full h-full object-cover rounded-lg shadow-lg"
                        />
                    </transition>
                </div>
                <div
                    v-else
                    class="flex justify-center items-center h-96 w-full bg-gray-800 rounded-lg"
                >
                    <p class="text-gray-400">Chargement des services...</p>
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

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-height: 1.5em;
}

.transition-height {
    transition-property: max-height, opacity;
    transition-duration: 300ms;
    transition-timing-function: ease-in-out;
    overflow: hidden;
    max-height: 500px; /* Une valeur suffisamment grande pour contenir le texte */
}

.description-container {
    width: 100%;
    overflow: hidden;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
