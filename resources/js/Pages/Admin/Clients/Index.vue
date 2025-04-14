<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";

// Récupération des clients depuis les props
const props = defineProps({
    clients: Array,
});
</script>
<template>
    <AdminLayout>
        <section class="flex py-28 px-16 flex-col gap-20 mt-14 bg-[#2D2D2D]">
            <div class="flex justify-between self-stretch">
                <div
                    class="flex max-w-[768px] flex-col items-start gap-4 text-white"
                >
                    <h2 class="text-center font-inter text-base font-semibold">
                        Clients
                    </h2>
                    <div class="relative flex flex-col gap-6">
                        <h3
                            class="font-poppins text-5xl text-center font-medium leading-[57.6px] tracking-[-0.48px] mb-6"
                        >
                            Liste de mes clients
                        </h3>
                        <div
                            class="absolute bottom-0 left-1/3 border-2 border-[#FF8C42] w-[80%] max-w-[353px] min-w-[200px]"
                        ></div>
                    </div>
                </div>
                <div class="flex items-end">
                    <PrimaryButton class="h-max">Ajouter</PrimaryButton>
                </div>
            </div>
            <div class="flex flex-wrap gap-12">
                <div
                    v-for="client in clients"
                    :key="client.id"
                    class="client-card flex flex-col md:flex-row items-center gap-8 w-full md:w-[calc(50%-1.5rem)]"
                >
                    <div
                        class="w-full md:w-1/2 rounded-lg bg-[#65558F]"
                        style="
                            aspect-ratio: 148/125;
                            max-width: 300px;
                            min-width: 150px;
                        "
                    ></div>
                    <div
                        class="flex flex-col items-start gap-2 w-full md:w-1/2"
                    >
                        <p class="font-medium font-poppins text-white text-2xl">
                            {{ client.name }} {{ client.lastname }}
                        </p>
                        <p
                            class="font-inter text-white text-base font-normal"
                            v-if="client.email"
                        >
                            {{ client.email }}
                        </p>
                        <p
                            class="font-inter text-[#FF8C42] text-base font-normal"
                            v-if="client.phone"
                        >
                            {{ client.phone }}
                        </p>
                        <p
                            class="font-inter text-white text-base font-normal"
                            v-if="client.adress"
                        >
                            {{ client.adress }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
