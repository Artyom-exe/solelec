<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    clients: Object,
    quotes: Array,
    quoteService: Object,
    errors: Object,
    flash: Object,
});
</script>

<template>
    <AdminLayout>
        <section class="flex py-28 px-16 items-start gap-20 bg-[#2D2D2D]">
            <div class="flex flex-col items-start gap-4 text-white w-3/4">
                <h2 class="text-center font-inter text-base font-semibold">
                    Devis
                </h2>
                <div class="relative flex flex-col gap-6">
                    <h3
                        class="font-poppins text-5xl text-left font-medium leading-[57.6px] tracking-[-0.48px] mb-6"
                    >
                        Mes demande de devis
                    </h3>
                    <div
                        class="absolute bottom-0 right border-2 border-[#FF8C42] w-[70%] max-w-[353px] min-w-[200px]"
                    ></div>
                </div>
            </div>
            <article class="flex flex-col items-start gap-12 w-full">
                <div class="flex items-center w-full">
                    <button
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-none text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Voir tout
                    </button>
                    <button
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Types
                    </button>
                    <button
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Clients
                    </button>
                </div>

                <!-- Liste des devis -->
                <div class="flex flex-col gap-8 items-start w-full">
                    <div
                        v-for="quote in quotes"
                        :key="quote.id"
                        class="bg-[#242424] rounded-lg border border-white/20 text-white p-8 gap-6 items-start w-full"
                    >
                        <div
                            class="flex flex-col items-start gap-8 self-stretch"
                        >
                            <div class="flex gap-4 self-stretch">
                                <div
                                    class="flex items-center gap-4 flex-1 flex-wrap"
                                >
                                    <h4
                                        class="font-poppins text-2xl font-medium tracking-[-0.24px]"
                                    >
                                        {{ quote.client?.name }}
                                        {{ quote.client?.lastname }}
                                    </h4>
                                    <span
                                        v-for="service in quote.services"
                                        :key="service.id"
                                        class="flex py-1 px-[10px] items-start rounded-[4px] border border-white/5 bg-white/5 text-[#FF8C42] font-inter font-semibold text-sm"
                                    >
                                        {{ service.title }}
                                    </span>
                                </div>
                                <div class="flex gap-2 h-max items-center">
                                    <Link
                                        :href="`/admin/devis/${quote.id}/convert`"
                                        class="text-[#FF8C42] font-inter text-base font-medium group flex items-center"
                                    >
                                        Convertir
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 256 512"
                                            class="w-4 h-4 fill-current text-[#FF8C42] ml-1"
                                        >
                                            <path
                                                d="M246.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-160 160c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L178.7 256 41.4 118.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l160 160z"
                                            />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                            <p class="font-inter text-base font-normal">
                                {{ quote.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Message si aucun devis -->
                <div
                    v-if="quotes.length === 0"
                    class="text-white text-center w-full py-10"
                >
                    <p class="font-inter text-lg">
                        Aucun devis disponible pour le moment.
                    </p>
                </div>
            </article>
        </section>
    </AdminLayout>
</template>
