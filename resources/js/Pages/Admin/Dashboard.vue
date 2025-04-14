<script setup>
import AdminLayout from "../../Layouts/AdminLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    pendingQuotesCount: Number,
    ongoingInterventionsCount: Number,
});
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard" />
        <header
            class="flex py-28 px-16 flex-col items-start gap-20 bg-[#2D2D2D]"
        >
            <div class="flex items-start gap-20 self-stretch">
                <h1
                    class="text-white font-poppins text-[2.5rem] font-medium leading-[3rem] w-1/2"
                >
                    Suivez l'évolution de vos devis et de vos interventions
                </h1>
                <div class="flex flex-col items-start gap-8 flex-1 w-1/2">
                    <p class="text-white font-inter text-lg">
                        Découvrez les statistiques sur les devis en attente et
                        les interventions en cours pour optimiser votre gestion.
                        Restez informé et améliorez vos performances.
                    </p>
                    <div class="flex flex-col items-start gap-4 self-stretch">
                        <div class="flex py-2 items-start gap-6 self-stretch">
                            <div class="flex flex-col items-start gap-2 flex-1">
                                <span
                                    class="text-white font-poppins text-[2.5rem] font-medium leading-[3.3rem]"
                                    >{{ pendingQuotesCount }}</span
                                >
                                <p
                                    class="text-white font-inter text-base font-normal"
                                >
                                    Devis en attentes
                                </p>
                            </div>
                            <div class="flex flex-col items-start gap-2 flex-1">
                                <span
                                    class="text-[#FF8C42] font-poppins text-[2.5rem] font-medium leading-[3.3rem]"
                                    >{{ ongoingInterventionsCount }}</span
                                >
                                <p
                                    class="text-white font-inter text-base font-normal"
                                >
                                    Interventions en cours
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    </AdminLayout>
</template>
