<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { computed, inject } from "vue";

// Injection des fonctions de notification
const showNotification = inject("showNotification");

const props = defineProps({
    intervention: Object,
    errors: Object,
    flash: Object,
    clients: Array,
    quotes: Array,
    services: Array,
});

// Fonction pour formater la date au format JJ/MM/AA
function formatDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = String(date.getFullYear()).slice(-2);
    return `${day}/${month}/${year}`;
}

// Couleurs des statuts
const statusColors = {
    planifiée: "#DAD9D9",
    "en cours": "#4A90E2",
    terminée: "#82AD84",
};

// Fonction pour déterminer le prochain statut
function nextStatus(current) {
    if (current === "planifiée") return "en cours";
    if (current === "en cours") return "terminée";
    return "planifiée";
}

// Fonction pour mettre à jour le statut
function updateStatus(intervention) {
    const newStatus = nextStatus(intervention.status);
    router.put(
        `/admin/interventions/${intervention.id}/status`,
        { status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                showNotification(`Le statut a été mis à jour en "${newStatus}"`, "success");
            },
            onError: (errors) => {
                showNotification("Une erreur est survenue lors de la mise à jour du statut.", "error");
            }
        }
    );
}
</script>

<template>
    <AdminLayout>
        <!-- En-tête noir avec les informations principales -->
        <header class="bg-[#2D2D2D] text-white py-28 px-16">
            <div class="container flex items-start gap-20 self-stretch">
                    <!-- Côté gauche: Titre et statut -->
                    <div class="flex flex-col items-start gap-6 flex-1">
                        <h1 class="text-[3.5rem] font-medium font-poppins leading-[120%] tracking-[-0.56px]">Intervention #{{ intervention.id }}</h1>
                        <div class="flex items-start content-start gap-2 self-stretch flex-wrap">
                            <!-- Affichage des services avec popup -->
                            <div class="relative service-group">
                                <div
                                    class="flex py-1 px-[10px] items-start rounded-[4px] border border-white/5 bg-white/5 text-[#FF8C42] font-inter font-semibold text-sm cursor-pointer"
                                >
                                    {{ intervention.devis?.services && intervention.devis.services.length > 0 ? intervention.devis.services[0].title : 'Aucun service' }}
                                    <span v-if="intervention.devis?.services && intervention.devis.services.length > 1" class="ml-1">
                                        +{{ intervention.devis.services.length - 1 }}
                                    </span>
                                </div>
                                <!-- Popup qui apparaît au survol -->
                                <div
                                    v-if="intervention.devis?.services && intervention.devis.services.length > 1"
                                    class="absolute z-10 top-full right-0 mt-1 bg-[#1A1A1A] border border-white/10 rounded-md p-2 shadow-lg opacity-0 invisible service-popup transition-all duration-200 min-w-[150px]"
                                >
                                    <div
                                        v-for="service in intervention.devis.services"
                                        :key="service.id"
                                        class="py-1 px-2 text-[#FF8C42] font-inter font-semibold text-sm whitespace-nowrap"
                                    >
                                        {{ service.title }}
                                    </div>
                                </div>
                            </div>

                            <!-- Bouton pour changer le statut -->
                            <button
                                class="flex py-1 px-[10px] items-start rounded border border-white/5 font-inter font-semibold text-sm transition-colors duration-300"
                                :style="{
                                    color: statusColors[intervention.status]
                                }"
                                @mouseover="$event.target.style.backgroundColor = statusColors[intervention.status]; $event.target.style.color = intervention.status === 'planifiée' ? '#2D2D2D' : 'white';"
                                @mouseout="$event.target.style.backgroundColor = 'transparent'; $event.target.style.color = statusColors[intervention.status];"
                                @click="updateStatus(intervention)"
                            >
                                {{ intervention.status }}
                            </button>
                        </div>
                    </div>

                    <!-- Côté droit: Statut et date -->
                    <div class="flex items-center space-x-6">
                        <div class="text-center">
                            <div class="text-gray-400 text-sm">Statut</div>
                            <div class="font-semibold" :style="{ color: statusColors[intervention.status] }">
                                {{ intervention.status }}
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-400 text-sm">Date</div>
                            <div class="font-semibold">{{ formatDate(intervention.date) }}</div>
                        </div>
                    </div>

                <!-- Informations de contact -->
                <div class="mt-8 grid grid-cols-3 gap-8">
                    <!-- Téléphone -->
                    <div class="flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white" class="mt-1">
                            <path d="M6.54 5C6.6 5.89 6.75 6.76 6.99 7.59L5.79 8.79C5.38 7.59 5.12 6.32 5.03 5H6.54ZM16.4 17.02C17.25 17.26 18.12 17.41 19 17.47V18.96C17.68 18.87 16.41 18.61 15.2 18.21L16.4 17.02ZM7.5 3H4C3.45 3 3 3.45 3 4C3 13.39 10.61 21 20 21C20.55 21 21 20.55 21 20V16.51C21 15.96 20.55 15.51 20 15.51C18.76 15.51 17.55 15.31 16.43 14.94C16.331 14.903 16.2256 14.886 16.12 14.89C15.86 14.89 15.61 14.99 15.41 15.18L13.21 17.38C10.3755 15.9303 8.06966 13.6245 6.62 10.79L8.82 8.59C9.1 8.31 9.18 7.92 9.07 7.57C8.69132 6.41789 8.4989 5.21274 8.5 4C8.5 3.45 8.05 3 7.5 3Z" />
                        </svg>
                        <div>
                            <div class="text-sm text-gray-400">Téléphone</div>
                            <a :href="`tel:${intervention.client?.phone || ''}`" class="font-semibold hover:text-[#FF8C42] transition-colors">
                                {{ intervention.client?.phone || 'Non renseigné' }}
                            </a>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white" class="mt-1">
                            <path d="M20 4H4C2.9 4 2.01 4.9 2.01 6L2 18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 8L12 13L4 8V6L12 11L20 6V8Z" />
                        </svg>
                        <div>
                            <div class="text-sm text-gray-400">Email</div>
                            <a :href="`mailto:${intervention.client?.email || ''}`" class="font-semibold hover:text-[#FF8C42] transition-colors">
                                {{ intervention.client?.email || 'Non renseigné' }}
                            </a>
                        </div>
                    </div>

                    <!-- Adresse -->
                    <div class="flex items-start space-x-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="white" class="mt-1">
                            <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2ZM12 11.5C10.62 11.5 9.5 10.38 9.5 9C9.5 7.62 10.62 6.5 12 6.5C13.38 6.5 14.5 7.62 14.5 9C14.5 10.38 13.38 11.5 12 11.5Z" />
                        </svg>
                        <div>
                            <div class="text-sm text-gray-400">Adresse</div>
                            <div class="font-semibold">
                                {{ intervention.client?.address || '24 Rue des Bourgeois, 17000 Niort, Bretagne' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenu principal -->
        <main class="container mx-auto py-8 px-4">
            <!-- Détails de l'intervention -->
            <section class="mb-12">
                <h2 class="text-xl font-bold mb-4">Détails de l'intervention</h2>
                <div class="grid grid-cols-3 gap-8">
                    <!-- Colonne de gauche vide pour l'alignement -->
                    <div></div>

                    <!-- Colonne du milieu: informations détaillées -->
                    <div class="bg-gray-100 p-6 rounded-md">
                        <h3 class="font-semibold mb-2">Service</h3>
                        <p class="mb-4">{{ devisDetails?.description || 'Installation et mise en service' }}</p>

                        <div class="space-y-2 mt-4">
                            <div class="flex">
                                <span class="text-sm font-semibold">• Type de véhicule:</span>
                                <span class="text-sm ml-2">{{ devisDetails?.vehicleType || 'Tesla Model 3' }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-sm font-semibold">• Prestation souhaitée:</span>
                                <span class="text-sm ml-2">{{ devisDetails?.prestationDuration || '15 min' }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-sm font-semibold">• Date d'intervention:</span>
                                <span class="text-sm ml-2">{{ formatDate(intervention.date) }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-sm font-semibold">• Distance au tableau électrique:</span>
                                <span class="text-sm ml-2">{{ devisDetails?.electricalPanel || 'Environ 8 mètres' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Photos -->
            <section class="mb-12">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Photos</h2>
                    <Link :href="route('interventions.edit', intervention.id)" class="bg-[#FF8C42] text-white px-4 py-1 rounded-full text-sm hover:bg-orange-600 transition-colors">
                        Ajouter
                    </Link>
                </div>

                <!-- Galerie de photos -->
                <div v-if="intervention.images && intervention.images.length > 0" class="grid grid-cols-4 gap-4">
                    <div v-for="image in intervention.images" :key="image.id" class="relative group">
                        <img :src="`/storage/${image.path}`" :alt="`Intervention ${intervention.id}`" class="w-full h-40 object-cover rounded-md">
                        <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <a :href="`/storage/${image.path}`" target="_blank" class="text-white hover:text-[#FF8C42]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M15 3l2.3 2.3-2.89 2.87 1.42 1.42L18.7 6.7 21 9V3h-6zM3 9l2.3-2.3 2.87 2.89 1.42-1.42L6.7 5.3 9 3H3v6zm6 12l-2.3-2.3 2.89-2.87-1.42-1.42L5.3 17.3 3 15v6h6zm12-6l-2.3 2.3-2.87-2.89-1.42 1.42 2.89 2.87L15 21h6v-6z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Message si pas de photos -->
                <div v-else class="bg-gray-100 p-8 rounded-md text-center">
                    <p class="text-gray-500">Aucune photo disponible pour cette intervention</p>
                    <p class="text-sm text-[#FF8C42] mt-2">Ajoutez des photos pour documenter l'intervention</p>
                </div>
            </section>

            <!-- Notes -->
            <section>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Notes</h2>
                </div>

                <!-- Affichage des notes existantes -->
                <div v-if="intervention.notes" class="bg-gray-100 p-6 rounded-md mb-4">
                    <p class="whitespace-pre-line">{{ intervention.notes }}</p>
                </div>

                <!-- Message si pas de notes -->
                <div v-else class="bg-gray-100 p-8 rounded-md text-center">
                    <p class="text-gray-500">Aucune note disponible pour cette intervention</p>
                    <p class="text-sm text-[#FF8C42] mt-2">Ajoutez des notes pour documenter l'intervention</p>
                </div>

                <!-- Formulaire pour ajouter des notes -->
                <div class="mt-6">
                    <textarea
                        class="w-full p-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#FF8C42]"
                        rows="4"
                        placeholder="Ajouter une note..."
                    ></textarea>
                    <div class="flex justify-end mt-2">
                        <button class="bg-[#FF8C42] text-white px-4 py-2 rounded-md hover:bg-orange-600 transition-colors">
                            Enregistrer
                        </button>
                    </div>
                </div>
            </section>
        </main>
    </AdminLayout>
</template>

<style scoped>
/* Styles pour le popup de services qui apparaît uniquement au survol de l'élément de service */
.service-group:hover .service-popup {
    opacity: 1 !important;
    visibility: visible !important;
}
</style>
