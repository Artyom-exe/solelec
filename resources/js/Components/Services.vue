<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    limit: {
        type: Number,
        default: 3
    }
});

const services = ref([]);
const activeIndex = ref(null);

const fetchServices = async () => {
    try {
        const response = await axios.get('/api/services');
        services.value = response.data;
    } catch (error) {
        console.error(error);
    }
};

onMounted(() => {
    fetchServices();
});
</script>

<template>
    <section class="flex gap-6">
        <div v-for="(service, index) in services.slice(0, props.limit)" :key="service.id" class="flex items-center gap-6 text-[#0D0703] rounded-lg border border-[rgba(13,7,3,0.15)] bg-[#FAF8F3] h-[340px]
            transition-all duration-500 overflow-hidden shadow-md"
            :class="(activeIndex !== null ? activeIndex === index : index === 0) ? 'w-2/3' : 'w-1/3'"
            @mouseenter="activeIndex = index" @mouseleave="activeIndex = null">

            <!-- Image qui s'étire vers la gauche -->
            <div class="h-full w-0 transition-all duration-500 ease-in-out"
                :class="(activeIndex !== null ? activeIndex === index : index === 0) ? 'w-1/2 opacity-100' : 'w-0 opacity-0'">
                <img :src="service.image" :alt="service.title + ' image'"
                    class="w-full h-full object-cover rounded-l-lg">
            </div>

            <!-- Section droite contenant le texte, toujours visible -->
            <div class="flex flex-col gap-4 transition-all duration-500 flex-1 z-10 pr-6 "
                :class="activeIndex === index ? 'w-1/3' : 'w-full'">
                <!-- Icône -->
                <img :src="service.icon" :alt="service.title + ' icon'" class="w-12 h-12 transition-all duration-500">

                <!-- Texte principal -->
                <div class="flex flex-col gap-2">
                    <h5 class="font-poppins text-2xl font-medium transition-all duration-500">
                        {{ service.title }}
                    </h5>
                    <p class="font-inter text-base font-normal transition-all duration-500">
                        {{ service.short_description }}
                    </p>
                </div>

                <!-- Lien "En savoir plus" + flèche -->
                <div class="flex gap-2 items-center cursor-pointer">
                    <a href="#" class="font-inter text-base font-medium transition-all duration-500">
                        En savoir plus
                    </a>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512"
                        class="w-4 h-4 transition-all duration-500 fill-current">
                        <path
                            d="M246.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-160 160c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L178.7 256 41.4 118.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l160 160z" />
                    </svg>
                </div>
            </div>
        </div>
    </section>
</template>
