<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import secondaryButton from "@/Components/SecondaryButton.vue";
import { computed, ref, reactive, inject, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";

// Import des fonctions de validation
import { validateIntervention, getErrorMessage } from "@/utils/interventionValidation";

// Injection des fonctions de notification
const showNotification = inject("showNotification");
const page = usePage();

const props = defineProps({
    interventions: Array,
    errors: Object,
    flash: Object,
    clients: Array,
    quotes: Array,
    services: Array,
});

const statusColors = {
    planifiée: "#DAD9D9",
    "en cours": "#4A90E2",
    terminée: "#82AD84",
};

function nextStatus(current) {
    if (current === "planifiée") return "en cours";
    if (current === "en cours") return "terminée";
    return "planifiée";
}

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
                showNotification(getErrorMessage(errors), "error");
            }
        }
    );
}

// Fonction pour formater la date au format JJ/MM/AA
function formatDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = String(date.getFullYear()).slice(-2); // Prendre seulement les 2 derniers chiffres

    return `${day}/${month}/${year}`;
}

// Variable pour gérer l'affichage du formulaire d'ajout
const showAddForm = ref(false);

// Nouvelle intervention - simplifiée pour ne garder que l'essentiel
const newIntervention = reactive({
    status: "planifiée", // Statut par défaut: planifiée (non modifiable)
    clients_id: "",
    services: [], // Services sélectionnés pour le devis
    new_client_name: "",
    new_client_lastname: "",
    new_client_phone: "",
    new_client_email: "",
    isNewClient: false, // Pour basculer entre client existant et nouveau client
});

// Fonction pour basculer la sélection d'un service
function toggleService(serviceId) {
    const index = newIntervention.services.indexOf(serviceId);
    if (index === -1) {
        // Si le service n'est pas déjà sélectionné, l'ajouter
        newIntervention.services.push(serviceId);
    } else {
        // Sinon, le retirer
        newIntervention.services.splice(index, 1);
    }
}

// Fonction pour basculer entre client existant et nouveau client
function toggleClientMode() {
    newIntervention.isNewClient = !newIntervention.isNewClient;
    // Réinitialiser les champs correspondants
    if (newIntervention.isNewClient) {
        newIntervention.clients_id = "";
    } else {
        newIntervention.new_client_name = "";
        newIntervention.new_client_lastname = "";
        newIntervention.new_client_phone = "";
        newIntervention.new_client_email = "";
    }
}

// Fonction pour soumettre la nouvelle intervention
function submitNewIntervention() {
    // Valider les données du formulaire
    const validationErrors = validateIntervention(newIntervention);
    
    // Si des erreurs sont présentes, afficher une notification et arrêter
    if (Object.keys(validationErrors).length > 0) {
        showNotification(getErrorMessage(validationErrors), "error");
        return;
    }

    router.post('/admin/interventions', newIntervention, {
        preserveScroll: true,
        onSuccess: () => {
            showNotification("L'intervention a été créée avec succès", "success");
            showAddForm.value = false;
            // Réinitialiser le formulaire
            newIntervention.status = "planifiée";
            newIntervention.clients_id = "";
            newIntervention.services = [];
            newIntervention.new_client_name = "";
            newIntervention.new_client_lastname = "";
            newIntervention.new_client_phone = "";
            newIntervention.new_client_email = "";
            newIntervention.isNewClient = false;
        },
        onError: (errors) => {
            showNotification(getErrorMessage(errors), "error");
        }
    });
}

// Surveiller les changements dans les propriétés flash pour afficher les notifications
watch(
    () => page.props.flash,
    (newFlash) => {
        if (newFlash?.success) {
            showNotification(newFlash.success, "success");
        }
        if (newFlash?.error) {
            showNotification(newFlash.error, "error");
        }
    },
    { deep: true, immediate: true }
);

// Variables pour le filtrage
const currentFilter = ref("all");
const selectedType = ref(null);
const selectedClient = ref(null);
const selectedStatus = ref(null);

// Variables pour le tri
const sortBy = ref("date"); // "date" ou "status"
const sortDirection = ref("asc"); // "asc" ou "desc"

// Liste des statuts disponibles
const statusOptions = ["planifiée", "en cours", "terminée"];

// Définir le type de filtre actif
function setFilter(filter) {
    currentFilter.value = filter;
    // Réinitialiser les sélections lors du changement de filtre
    if (filter === "all") {
        selectedType.value = null;
        selectedClient.value = null;
        selectedStatus.value = null;
    }
}

// Extraire les types de services uniques des interventions
const serviceTypes = computed(() => {
    const types = new Set();
    props.interventions.forEach((intervention) => {
        if (intervention.devis && intervention.devis.services) {
            intervention.devis.services.forEach((service) => {
                types.add(service.title);
            });
        }
    });
    return Array.from(types);
});

// Extraire les clients uniques
const uniqueClients = computed(() => {
    const clients = new Set();
    props.interventions.forEach((intervention) => {
        if (intervention.client) {
            const fullName = `${intervention.client.name} ${
                intervention.client.lastname || ""
            }`.trim();
            clients.add(fullName);
        }
    });
    return Array.from(clients);
});

// Fonction pour changer le tri
function toggleSort(field) {
    if (sortBy.value === field) {
        // Si on clique sur le même champ, on inverse la direction
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        // Sinon on change le champ et on met la direction par défaut
        sortBy.value = field;
        sortDirection.value = field === "date" ? "desc" : "asc"; // Par défaut: dates récentes d'abord, statuts alphabétiques
    }
}

// Interventions filtrées et triées
const sortedInterventions = computed(() => {
    let filtered = [...props.interventions];

    // Appliquer les filtres
    if (currentFilter.value === "type" && selectedType.value) {
        filtered = filtered.filter((intervention) => {
            return intervention.devis?.services?.some(
                (service) => service.title === selectedType.value
            );
        });
    } else if (currentFilter.value === "client" && selectedClient.value) {
        filtered = filtered.filter((intervention) => {
            if (!intervention.client) return false;
            const fullName = `${intervention.client.name} ${
                intervention.client.lastname || ""
            }`.trim();
            return fullName === selectedClient.value;
        });
    } else if (currentFilter.value === "status" && selectedStatus.value) {
        filtered = filtered.filter((intervention) => {
            return intervention.status === selectedStatus.value;
        });
    }

    // Appliquer le tri
    filtered.sort((a, b) => {
        // Toujours mettre les interventions terminées à la fin, peu importe le tri
        if (a.status === "terminée" && b.status !== "terminée") return 1;
        if (a.status !== "terminée" && b.status === "terminée") return -1;

        // Tri par date ou statut
        if (sortBy.value === "date") {
            const dateA = new Date(a.date || a.created_at);
            const dateB = new Date(b.date || b.created_at);
            return sortDirection.value === "asc" ? dateA - dateB : dateB - dateA;
        } else if (sortBy.value === "status") {
            // Ordre des statuts: planifiée, en cours, terminée
            const statusOrder = { "planifiée": 1, "en cours": 2, "terminée": 3 };
            const orderA = statusOrder[a.status] || 0;
            const orderB = statusOrder[b.status] || 0;
            return sortDirection.value === "asc" ? orderA - orderB : orderB - orderA;
        }
        return 0;
    });

    return filtered;
});
</script>
<template>
    <AdminLayout>
        <section
            class="flex py-28 px-16 flex-col gap-20 mt-14 bg-[#2D2D2D] min-h-screen"
        >
            <div class="flex justify-between self-stretch">
                <div
                    class="flex max-w-[768px] flex-col items-start gap-4 text-white"
                >
                    <h2 class="text-center font-inter text-base font-semibold">
                        Interventions
                    </h2>
                    <div class="relative flex flex-col gap-6">
                        <h3
                            class="font-poppins text-5xl text-center font-medium leading-[57.6px] tracking-[-0.48px] mb-6"
                        >
                            Liste de mes interventions
                        </h3>
                        <div
                            class="absolute bottom-0 right-[-20%] border-2 border-[#FF8C42] w-[80%] max-w-[353px] min-w-[200px]"
                        ></div>
                    </div>
                </div>
                <div class="flex items-end">
                    <PrimaryButton @click="showAddForm = !showAddForm" class="h-max">
                        {{ showAddForm ? 'Annuler' : 'Ajouter' }}
                    </PrimaryButton>
                </div>
            </div>
            <div class="flex flex-col gap-6 w-full">
                <!-- Filtres -->
                <div class="flex items-center w-full">
                    <button
                        @click="setFilter('all')"
                        :class="{
                            'bg-[#0D0703] border border-white/20':
                                currentFilter === 'all',
                        }"
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Tous
                    </button>
                    <button
                        @click="setFilter('type')"
                        :class="{
                            'bg-[#0D0703] border border-white/20':
                                currentFilter === 'type',
                        }"
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Types
                    </button>
                    <button
                        @click="setFilter('client')"
                        :class="{
                            'bg-[#0D0703] border border-white/20':
                                currentFilter === 'client',
                        }"
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Clients
                    </button>
                    <button
                        @click="setFilter('status')"
                        :class="{
                            'bg-[#0D0703] border border-white/20':
                                currentFilter === 'status',
                        }"
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Statuts
                    </button>
                    <button
                        @click="toggleSort('date')"
                        :class="{
                            'bg-[#0D0703] border border-white/20': sortBy === 'date',
                        }"
                        class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                    >
                        Date
                        <svg v-if="sortBy === 'date'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" class="ml-1">
                            <path v-if="sortDirection === 'asc'" d="M18 15l-6-6-6 6"/>
                            <path v-else d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>

                </div>

                <!-- Options de filtre pour les types -->
                <div
                    v-if="currentFilter === 'type'"
                    class="flex flex-wrap gap-2 mb-2"
                >
                    <button
                        v-for="type in serviceTypes"
                        :key="type"
                        @click="selectedType = type"
                        :class="{
                            'bg-[#FF8C42] text-white': selectedType === type,
                        }"
                        class="px-3 py-1 text-sm rounded-md border border-white/20 text-white hover:bg-[#FF8C42] hover:text-white transition-colors duration-300"
                    >
                        {{ type }}
                    </button>
                </div>

                <!-- Options de filtre pour les clients -->
                <div
                    v-if="currentFilter === 'client'"
                    class="flex flex-wrap gap-2 mb-2"
                >
                    <button
                        v-for="client in uniqueClients"
                        :key="client"
                        @click="selectedClient = client"
                        :class="{
                            'bg-[#FF8C42] text-white':
                                selectedClient === client,
                        }"
                        class="px-3 py-1 text-sm rounded-md border border-white/20 text-white hover:bg-[#FF8C42] hover:text-white transition-colors duration-300"
                    >
                        {{ client }}
                    </button>
                </div>

                <!-- Options de filtre pour les statuts -->
                <div
                    v-if="currentFilter === 'status'"
                    class="flex flex-wrap gap-2 mb-2"
                >
                    <button
                        v-for="status in statusOptions"
                        :key="status"
                        @click="selectedStatus = status"
                        :style="{
                            backgroundColor: selectedStatus === status ? statusColors[status] : 'transparent',
                            color: selectedStatus === status ? (status === 'planifiée' ? '#2D2D2D' : '#fff') : '#fff'
                        }"
                        class="px-3 py-1 text-sm rounded-md border border-white/20 transition-colors duration-300"
                        :class="{
                            'bg-[#DAD9D9] text-[#2D2D2D]': selectedStatus === status && status === 'planifiée',
                            'bg-[#4A90E2] text-white': selectedStatus === status && status === 'en cours',
                            'bg-[#82AD84] text-white': selectedStatus === status && status === 'terminée'
                        }"
                        @mouseover="$event.target.style.backgroundColor = statusColors[status]; $event.target.style.color = status === 'planifiée' ? '#2D2D2D' : 'white';"
                        @mouseout="$event.target.style.backgroundColor = selectedStatus === status ? statusColors[status] : 'transparent'; $event.target.style.color = selectedStatus === status ? (status === 'planifiée' ? '#2D2D2D' : '#fff') : '#fff';"

                    >
                        {{ status }}
                    </button>
                </div>
            </div>
            
            <!-- Formulaire d'ajout d'intervention -->
            <div v-if="showAddForm" class="grid grid-cols-2 gap-8 w-full items-start">
                <div
                    class="flex p-8 flex-col items-start gap-6 rounded-lg border border-white/20 bg-[#242424] h-auto"
                >
                    <!-- En-tête avec Nouveau client à gauche et Services à droite -->
                    <div class="flex justify-between w-full items-center mb-4">
                        <div>
                            <button
                                @click="toggleClientMode"
                                class="text-[#FF8C42] hover:text-white transition-colors text-sm flex items-center gap-1"
                            >
                                <span v-if="!newIntervention.isNewClient">Nouveau client</span>
                                <span v-else>Client existant</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="relative group">
                            <div
                                class="flex py-1 px-[10px] items-start rounded-[4px] border border-white/5 bg-white/5 text-[#FF8C42] font-inter font-semibold text-sm cursor-pointer"
                            >
                                {{ newIntervention.services.length > 0 ? props.services.find(s => s.id === newIntervention.services[0])?.title || 'Services' : 'Services' }}
                                <span v-if="newIntervention.services.length > 1" class="ml-1">
                                    +{{ newIntervention.services.length - 1 }}
                                </span>
                            </div>
                            <!-- Popup qui apparaît au survol -->
                            <div
                                v-if="newIntervention.services.length > 1"
                                class="absolute z-10 top-full right-0 mt-1 bg-[#1A1A1A] border border-white/10 rounded-md p-2 shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 min-w-[150px]"
                            >
                                <div
                                    v-for="serviceId in newIntervention.services"
                                    :key="serviceId"
                                    class="py-1 px-2 text-[#FF8C42] font-inter font-semibold text-sm whitespace-nowrap"
                                >
                                    {{ props.services.find(s => s.id === serviceId)?.title }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section de sélection du client -->
                    <div class="w-full mb-4">
                        <!-- Sélection d'un client existant -->
                        <div v-if="!newIntervention.isNewClient">
                            <select
                                v-model="newIntervention.clients_id"
                                class="bg-[#242424] text-white font-poppins text-lg p-2 rounded-md border border-white/20 w-full"
                                required
                            >
                                <option value="" disabled>Sélectionner un client</option>
                                <option v-for="client in props.clients" :key="client.id" :value="client.id">
                                    {{ client.name }} {{ client.lastname }}
                                </option>
                            </select>
                        </div>

                        <!-- Création d'un nouveau client -->
                        <div v-else class="flex flex-col gap-2">
                            <div class="flex gap-2">
                                <input
                                    v-model="newIntervention.new_client_name"
                                    type="text"
                                    class="bg-[#242424] text-white font-poppins text-xl font-medium p-2 rounded-md border border-white/20 w-1/2"
                                    placeholder="Prénom"
                                    required
                                />
                                <input
                                    v-model="newIntervention.new_client_lastname"
                                    type="text"
                                    class="bg-[#242424] text-white font-poppins text-xl font-medium p-2 rounded-md border border-white/20 w-1/2"
                                    placeholder="Nom"
                                />
                            </div>
                            <div class="flex gap-2">
                                <div class="relative w-1/2">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#FF8C42" class="bi bi-telephone" viewBox="0 0 16 16">
                                            <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                                        </svg>
                                    </div>
                                    <input
                                        v-model="newIntervention.new_client_phone"
                                        type="tel"
                                        class="bg-[#242424] text-[#FF8C42] font-inter text-base p-2 pl-10 rounded-md border border-white/20 w-full"
                                        placeholder="Téléphone"
                                    />
                                </div>
                                <div class="relative w-1/2">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-envelope" viewBox="0 0 16 16">
                                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
                                        </svg>
                                    </div>
                                    <input
                                        v-model="newIntervention.new_client_email"
                                        type="email"
                                        class="bg-[#242424] text-white font-inter text-base p-2 pl-10 rounded-md border border-white/20 w-full"
                                        placeholder="Email"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col w-full gap-4">
                        <p class="text-white font-inter font-medium text-base">Services :</p>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <button
                                v-for="service in props.services"
                                :key="service.id"
                                @click="toggleService(service.id)"
                                :class="{
                                    'bg-[#FF8C42] text-white': newIntervention.services.includes(service.id),
                                    'bg-[#333] text-white': !newIntervention.services.includes(service.id)
                                }"
                                class="px-3 py-1 text-sm rounded-md border border-white/20 hover:bg-[#FF8C42] hover:text-white transition-colors duration-300"
                            >
                                {{ service.title }}
                            </button>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-2">
                        <button
                            @click="submitNewIntervention"
                            class="text-white bg-[#FF8C42] hover:bg-orange-700 transition-colors rounded-full px-4 py-1 text-sm"
                        >
                            Confirmer
                        </button>
                        <button
                            @click="showAddForm = false"
                            class="text-white hover:text-gray-400 transition-colors text-sm"
                        >
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Liste des interventions -->
            <div class="grid grid-cols-2 gap-8 w-full items-start mt-4">
                <div
                    v-for="intervention in sortedInterventions"
                    :key="intervention.id"
                    class="flex p-8 flex-col items-start gap-6 rounded-lg border border-white/20 bg-[#242424] h-auto"
                >
                    <div class="flex justify-between w-full">
                        <h4
                            class="text-white font-poppins text-2xl font-medium"
                        >
                            {{ intervention.client?.name }}
                            {{ intervention.client?.lastname }}
                        </h4>
                        <div class="relative group">
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
                                class="absolute z-10 top-full right-0 mt-1 bg-[#1A1A1A] border border-white/10 rounded-md p-2 shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 min-w-[150px]"
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
                    </div>
                    <div class="flex justify-between w-full">
                        <p class="text-white font-inter font-light text-lg">
                            Intervention #{{ intervention.id }}
                        </p>
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
                    <div class="flex gap-3">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <path
                                d="M18 17H16C15.7167 17 15.4793 16.904 15.288 16.712C15.0967 16.52 15.0007 16.2827 15 16C14.9993 15.7173 15.0953 15.48 15.288 15.288C15.4807 15.096 15.718 15 16 15H18V13C18 12.7167 18.096 12.4793 18.288 12.288C18.48 12.0967 18.7173 12.0007 19 12C19.2827 11.9993 19.5203 12.0953 19.713 12.288C19.9057 12.4807 20.0013 12.718 20 13V15H22C22.2833 15 22.521 15.096 22.713 15.288C22.905 15.48 23.0007 15.7173 23 16C22.9993 16.2827 22.9033 16.5203 22.712 16.713C22.5207 16.9057 22.2833 17.0013 22 17H20V19C20 19.2833 19.904 19.521 19.712 19.713C19.52 19.905 19.2827 20.0007 19 20C18.7173 19.9993 18.48 19.9033 18.288 19.712C18.096 19.5207 18 19.2833 18 19V17ZM3 21C2.45 21 1.97933 20.8043 1.588 20.413C1.19667 20.0217 1.00067 19.5507 1 19V5C1 4.45 1.196 3.97933 1.588 3.588C1.98 3.19667 2.45067 3.00067 3 3H17C17.55 3 18.021 3.196 18.413 3.588C18.805 3.98 19.0007 4.45067 19 5V9C19 9.28333 18.904 9.521 18.712 9.713C18.52 9.905 18.2827 10.0007 18 10C17.7173 9.99933 17.48 9.90333 17.288 9.712C17.096 9.52067 17 9.28333 17 9V8H3V19H15C15.2833 19 15.521 19.096 15.713 19.288C15.905 19.48 16.0007 19.7173 16 20C15.9993 20.2827 15.9033 20.5203 15.712 20.713C15.5207 20.9057 15.2833 21.0013 15 21H3Z"
                                fill="white"
                            />
                        </svg>
                        <span
                            class="text-white font-inter text-base font-light"
                        >
                            Crée le
                            {{ formatDate(intervention.created_at) }}</span
                        >
                    </div>
                    <div class="flex">
                        <a
                            v-if="intervention.client?.phone !== null"
                            :href="`tel:${intervention.client?.phone}`"
                            class="flex gap-3 text-[#FF8C42] hover:text-white transition-colors duration-300"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path
                                    d="M6.54 5C6.6 5.89 6.75 6.76 6.99 7.59L5.79 8.79C5.38 7.59 5.12 6.32 5.03 5H6.54ZM16.4 17.02C17.25 17.26 18.12 17.41 19 17.47V18.96C17.68 18.87 16.41 18.61 15.2 18.21L16.4 17.02ZM7.5 3H4C3.45 3 3 3.45 3 4C3 13.39 10.61 21 20 21C20.55 21 21 20.55 21 20V16.51C21 15.96 20.55 15.51 20 15.51C18.76 15.51 17.55 15.31 16.43 14.94C16.331 14.903 16.2256 14.886 16.12 14.89C15.86 14.89 15.61 14.99 15.41 15.18L13.21 17.38C10.3755 15.9303 8.06966 13.6245 6.62 10.79L8.82 8.59C9.1 8.31 9.18 7.92 9.07 7.57C8.69132 6.41789 8.4989 5.21274 8.5 4C8.5 3.45 8.05 3 7.5 3Z"
                                />
                            </svg>
                            {{ intervention.client?.phone }}
                        </a>
                        <div v-else class="flex gap-3 text-white hover:text-gray-300 transition-colors duration-300">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path
                                    d="M6.54 5C6.6 5.89 6.75 6.76 6.99 7.59L5.79 8.79C5.38 7.59 5.12 6.32 5.03 5H6.54ZM16.4 17.02C17.25 17.26 18.12 17.41 19 17.47V18.96C17.68 18.87 16.41 18.61 15.2 18.21L16.4 17.02ZM7.5 3H4C3.45 3 3 3.45 3 4C3 13.39 10.61 21 20 21C20.55 21 21 20.55 21 20V16.51C21 15.96 20.55 15.51 20 15.51C18.76 15.51 17.55 15.31 16.43 14.94C16.331 14.903 16.2256 14.886 16.12 14.89C15.86 14.89 15.61 14.99 15.41 15.18L13.21 17.38C10.3755 15.9303 8.06966 13.6245 6.62 10.79L8.82 8.59C9.1 8.31 9.18 7.92 9.07 7.57C8.69132 6.41789 8.4989 5.21274 8.5 4C8.5 3.45 8.05 3 7.5 3Z"
                                />
                            </svg>
                            Non renseigné
                        </div>
                    </div>
                    <secondaryButton
                        @click="
                            $inertia.visit(
                                route(
                                    'admin.interventions.show',
                                    intervention.id
                                )
                            )
                        "
                    >
                        Détails
                    </secondaryButton>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
