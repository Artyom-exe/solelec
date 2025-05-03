<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    quotes: Array,
    quoteService: Object,
    errors: Object,
    flash: Object,
});
</script>

<template>
    <AdminLayout>
        <section class="flex py-28 px-16 items-start gap-20 bg-[#2D2D2D]">
            <div
                class="flex max-w-[768px] flex-col items-start gap-4 text-white"
            >
                <h2 class="text-center font-inter text-base font-semibold">
                    Devis
                </h2>
                <div class="relative flex flex-col gap-6">
                    <h3
                        class="font-poppins text-5xl text-center font-medium leading-[57.6px] tracking-[-0.48px] mb-6"
                    >
                        Mes demande de devis
                    </h3>
                    <div
                        class="absolute bottom-0 right-1/3 border-2 border-[#FF8C42] w-[80%] max-w-[353px] min-w-[200px]"
                    ></div>
                </div>
            </div>
            <article class="flex flex-col items-start gap-12 flex-1">
                <div class="flex items-center">
                    <button
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
                    <div
                        v-for="quote in quotes"
                        :key="quote.id"
                        class="bg-[#1A1A1A] p-6 rounded-lg border border-white/10 text-white hover:border-white/30 transition-all duration-300"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <h4 class="font-poppins text-xl font-medium">
                                {{ quote.title || "Devis #" + quote.id }}
                            </h4>
                            <span
                                class="px-3 py-1 bg-[#FF8C42]/10 text-[#FF8C42] rounded-full text-sm"
                            >
                                {{ quote.status || "En attente" }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <p class="text-white/70 mb-1">Client:</p>
                            <p class="font-inter">
                                {{ quote.client?.name || "Non spécifié" }}
                            </p>
                        </div>

                        <div class="mb-4">
                            <p class="text-white/70 mb-1">Date de demande:</p>
                            <p class="font-inter">
                                {{
                                    quote.created_at
                                        ? new Date(
                                              quote.created_at
                                          ).toLocaleDateString("fr-FR")
                                        : "Non spécifiée"
                                }}
                            </p>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button
                                class="flex py-2 px-4 justify-center items-center gap-2 text-[#FF8C42] font-inter text-base transition-all duration-300 ease-in-out hover:bg-[#FF8C42]/10"
                            >
                                Voir détails
                            </button>
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
