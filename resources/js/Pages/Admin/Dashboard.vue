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
            class="flex py-28 px-16 flex-col items-start gap-20 bg-[#2D2D2D] mt-[3.5rem]"
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

        <!-- Section des activités récentes -->
        <section class="bg-white py-16 px-16">
            <h2
                class="text-[#2D2D2D] font-poppins text-[1.75rem] font-medium mb-8"
            >
                Activités récentes
            </h2>

            <div
                class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100"
            >
                <div
                    v-if="recentActivities && recentActivities.length"
                    class="overflow-x-auto"
                >
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Type
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Description
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Utilisateur
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="activity in recentActivities"
                                :key="activity.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800':
                                                activity.type === 'create',
                                            'bg-blue-100 text-blue-800':
                                                activity.type === 'update',
                                            'bg-red-100 text-red-800':
                                                activity.type === 'delete',
                                            'bg-orange-100 text-orange-800':
                                                activity.type === 'quote',
                                            'bg-purple-100 text-purple-800':
                                                activity.type ===
                                                'intervention',
                                        }"
                                        class="px-3 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ activity.typeLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">
                                        {{ activity.description }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm text-gray-900">
                                        {{ activity.user }}
                                    </p>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    :title="activity.date"
                                >
                                    {{ activity.timeAgo }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="py-8 px-6 text-center text-gray-500">
                    Aucune activité récente à afficher
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
