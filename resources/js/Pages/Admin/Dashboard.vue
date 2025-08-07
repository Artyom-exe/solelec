<script setup>
import AdminLayout from "../../Layouts/AdminLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    pendingQuotesCount: Number,
    ongoingInterventionsCount: Number,
    recentActivities: Array,
});
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard" />
        <header
            class="flex md:py-28 py-16 md:px-16 px-5 flex-col items-start gap-20 bg-[#2D2D2D] md:mt-[3.5rem] mt-16"
        >
            <div
                class="flex md:flex-row flex-col items-start md:gap-20 gap-5 self-stretch"
            >
                <h1
                    class="text-white font-poppins text-[2rem] md:text-[2.5rem] font-medium md:leading-[3rem] leading-[2.4rem] md:w-1/2"
                >
                    Suivez l'évolution de vos devis et de vos interventions
                </h1>
                <div class="flex flex-col items-start gap-8 flex-1 md:w-1/2">
                    <p class="text-white font-inter md:text-lg text-base">
                        Découvrez les statistiques sur les devis en attente et
                        les interventions en cours pour optimiser votre gestion.
                        Restez informé et améliorez vos performances.
                    </p>
                    <div class="flex flex-col items-start gap-4 self-stretch">
                        <div
                            class="flex md:flex-row flex-col py-2 items-start gap-6 self-stretch"
                        >
                            <div class="flex flex-col items-start gap-2 flex-1">
                                <span
                                    class="text-white font-poppins md:text-[2.5rem] text-4xl font-medium md:leading-[3.3rem] leading-[2.7rem]"
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
                                    class="text-[#FF8C42] font-poppins md:text-[2.5rem] text-4xl font-medium md:leading-[3.3rem] leading-[2.7rem]"
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

        <!-- Section des activités récentes -->
        <section
            class="bg-white md:py-28 py-16 md:px-16 px-5 items-center flex flex-col md:gap-20 gap-12"
        >
            <div class="relative flex flex-col gap-6">
                <h2
                    class="font-poppins md:text-5xl text-4xl text-center font-medium md:leading-[57.6px] md:tracking-[-0.48px] leading-[43.2px] tracking-[-0.36px] mb-6"
                >
                    Activités Récentes
                </h2>
                <div
                    class="absolute md:bottom-0 bottom-2 right-1/2 border-2 border-[#FF8C42] w-[70%] max-w-[353px] md:min-w-[200px] min-w-[188px]"
                ></div>
            </div>
            <div class="bg-white w-full">
                <div
                    v-if="recentActivities && recentActivities.length"
                    class="overflow-x-auto"
                >
                    <!-- Vue desktop : tableau classique -->
                    <table class="w-full border-collapse hidden md:table">
                        <thead>
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-5 text-left font-poppins text-sm font-medium text-[#0D0703]"
                                >
                                    Type
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-5 text-left font-poppins text-sm font-medium text-[#0D0703]"
                                >
                                    Description
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-5 text-left font-poppins text-sm font-medium text-[#0D0703]"
                                >
                                    Utilisateur
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-5 text-left font-poppins text-sm font-medium text-[#0D0703]"
                                >
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(activity, index) in recentActivities"
                                :key="activity.id"
                                :class="{
                                    'bg-white': index % 2 === 1,
                                    'bg-[#F9F9F9]': index % 2 === 0,
                                }"
                            >
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'bg-[#E7F6E7] text-[#28A745]':
                                                activity.type === 'create',
                                            'bg-[#E6F3FF] text-[#0D6EFD]':
                                                activity.type === 'update',
                                            'bg-[#FEECEC] text-[#DC3545]':
                                                activity.type === 'delete',
                                            'bg-[#FFF4EC] text-[#FF8C42]':
                                                activity.type === 'quote',
                                            'bg-[#F3EBFF] text-[#6F42C1]':
                                                activity.type ===
                                                'intervention',
                                        }"
                                        class="px-4 py-1.5 rounded-full text-xs font-medium font-inter inline-block min-w-[90px] text-center"
                                    >
                                        {{ activity.typeLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="font-inter text-[#0D0703]">
                                        {{ activity.description }}
                                    </p>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <p class="font-inter text-[#0D0703]">
                                        {{ activity.user }}
                                    </p>
                                </td>
                                <td
                                    class="px-6 py-5 whitespace-nowrap font-inter text-[#6C757D]"
                                    :title="activity.date"
                                >
                                    {{ activity.timeAgo }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Vue mobile : cartes empilées -->
                    <div class="md:hidden space-y-4">
                        <div
                            v-for="(activity, index) in recentActivities"
                            :key="activity.id"
                            class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm"
                        >
                            <div class="flex justify-between items-start mb-3">
                                <span
                                    :class="{
                                        'bg-[#E7F6E7] text-[#28A745]':
                                            activity.type === 'create',
                                        'bg-[#E6F3FF] text-[#0D6EFD]':
                                            activity.type === 'update',
                                        'bg-[#FEECEC] text-[#DC3545]':
                                            activity.type === 'delete',
                                        'bg-[#FFF4EC] text-[#FF8C42]':
                                            activity.type === 'quote',
                                        'bg-[#F3EBFF] text-[#6F42C1]':
                                            activity.type === 'intervention',
                                    }"
                                    class="px-3 py-1 rounded-full text-xs font-medium font-inter"
                                >
                                    {{ activity.typeLabel }}
                                </span>
                                <span
                                    class="text-xs font-inter text-[#6C757D]"
                                    :title="activity.date"
                                >
                                    {{ activity.timeAgo }}
                                </span>
                            </div>
                            <p class="font-inter text-[#0D0703] text-sm mb-2">
                                {{ activity.description }}
                            </p>
                            <p class="font-inter text-[#6C757D] text-xs">
                                Par {{ activity.user }}
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    v-else
                    class="py-12 px-6 text-center font-inter text-[#6C757D]"
                >
                    Aucune activité récente à afficher
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
