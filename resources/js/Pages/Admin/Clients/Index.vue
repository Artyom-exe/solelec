<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Link } from "@inertiajs/vue3";
import { ref, onMounted, nextTick, inject, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";

// Import des fonctions de validation
import { validateClient, getErrorMessage } from "@/utils/validation";

// Injection des fonctions de notification
const showNotification = inject("showNotification");
const page = usePage();

const props = defineProps({
    clients: Array,
    errors: Object,
    flash: Object,
});

// Liste de couleurs
const colors = [
    "#65558F",
    "#82AD84",
    "#4A90E2",
    "#DAD9D9",
    "#886FB5",
    "#6E8BD1",
    "#5EADD4",
    "#5ABFAD",
    "#73C096",
    "#9BBC6D",
    "#CBCF86",
    "#F2D678",
    "#F0B77A",
    "#E68B7B",
    "#D77B93",
    "#C17B9C",
    "#9A8FA3",
    "#A7AABD",
    "#94A5B2",
    "#8DB0A6",
    "#B1BAA3",
    "#BFB59A",
    "#C4A68D",
    "#AA8879",
];

// Stockage des couleurs assignées aux clients avec persistance via localStorage
const clientColors = ref({});

// Fonction pour assigner une couleur à un client de manière déterministe
const getColorForClient = (clientId) => {
    // Vérifier si le client a déjà une couleur sauvegardée
    const savedColors = localStorage.getItem("clientColors");
    const savedColorsObj = savedColors ? JSON.parse(savedColors) : {};

    // Si le client a déjà une couleur, l'utiliser
    if (savedColorsObj[clientId]) {
        return savedColorsObj[clientId];
    }

    // Sinon, assigner une nouvelle couleur basée sur l'ID (déterministe)
    const colorIndex = clientId % colors.length;
    const assignedColor = colors[colorIndex];

    // Sauvegarder cette couleur pour ce client
    savedColorsObj[clientId] = assignedColor;
    localStorage.setItem("clientColors", JSON.stringify(savedColorsObj));

    return assignedColor;
};

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

// Initialiser les couleurs des clients au chargement du composant
onMounted(() => {
    if (props.clients && props.clients.length) {
        const tempColors = {};
        props.clients.forEach((client) => {
            tempColors[client.id] = getColorForClient(client.id);
        });
        clientColors.value = tempColors;
    }
});

// État d'édition pour chaque client
const editingClient = ref(null);
const editForm = ref({});

// État pour la création d'un nouveau client
const isCreatingClient = ref(false);
const newClientForm = ref({
    name: "",
    lastname: "",
    email: "",
    phone: "",
    adress: "",
});
const newClientColor = ref("");

// État pour la gestion des erreurs
const formErrors = ref({});

// État pour gérer l'affichage des actions mobiles
const showMobileActions = ref({});

// Fonction pour gérer l'appui long sur mobile
let longPressTimer = null;
const startLongPress = (clientId) => {
    longPressTimer = setTimeout(() => {
        showMobileActions.value = { [clientId]: true };
    }, 500); // 500ms pour déclencher l'appui long
};

const cancelLongPress = () => {
    if (longPressTimer) {
        clearTimeout(longPressTimer);
        longPressTimer = null;
    }
};

const hideMobileActions = (clientId) => {
    showMobileActions.value = { ...showMobileActions.value, [clientId]: false };
};

// Fonction pour commencer la création d'un client
const startCreatingClient = () => {
    // Réinitialiser le formulaire
    newClientForm.value = {
        name: "",
        lastname: "",
        email: "",
        phone: "",
        adress: "",
    };

    // Assigner une couleur aléatoire au nouveau client
    const randomColorIndex = Math.floor(Math.random() * colors.length);
    newClientColor.value = colors[randomColorIndex];

    isCreatingClient.value = true;
};

// Fonction pour annuler la création
const cancelCreatingClient = () => {
    isCreatingClient.value = false;
};

// Fonction pour sauvegarder le nouveau client
const saveNewClient = () => {
    // Valider le formulaire avant soumission
    const validationErrors = validateClient(newClientForm.value);

    if (Object.keys(validationErrors).length > 0) {
        formErrors.value = validationErrors;
        showNotification(getErrorMessage(validationErrors), "error");
        return;
    }

    formErrors.value = {};
    router.post("/admin/clients", newClientForm.value, {
        onSuccess: () => {
            isCreatingClient.value = false;

            // Récupérer les couleurs existantes
            const savedColors = localStorage.getItem("clientColors") || "{}";
            const savedColorsObj = JSON.parse(savedColors);

            // On attend que les props soient mises à jour avec le nouveau client
            nextTick(() => {
                const newClient = props.clients.find(
                    (c) =>
                        c.name === newClientForm.value.name &&
                        c.lastname === newClientForm.value.lastname
                );

                if (newClient) {
                    savedColorsObj[newClient.id] = newClientColor.value;
                    localStorage.setItem(
                        "clientColors",
                        JSON.stringify(savedColorsObj)
                    );
                    clientColors.value[newClient.id] = newClientColor.value;
                }
            });
        },
        onError: (errors) => {
            formErrors.value = errors;
            showNotification(getErrorMessage(errors), "error");
        },
    });
};

// Fonction pour commencer l'édition d'un client
const startEditing = (client) => {
    // Copier les données du client dans le formulaire d'édition
    editForm.value = { ...client };
    editingClient.value = client.id;
};

// Fonction pour annuler l'édition
const cancelEditing = () => {
    editingClient.value = null;
};

// Fonction pour sauvegarder les modifications
const saveClient = () => {
    // Valider le formulaire avant soumission
    const validationErrors = validateClient(editForm.value);

    if (Object.keys(validationErrors).length > 0) {
        formErrors.value = validationErrors;
        showNotification(getErrorMessage(validationErrors), "error");
        return;
    }

    formErrors.value = {};
    router.put(`/admin/clients/${editForm.value.id}`, editForm.value, {
        onSuccess: () => {
            editingClient.value = null;
        },
        onError: (errors) => {
            formErrors.value = errors;
            showNotification(getErrorMessage(errors), "error");
        },
    });
};

// Fonction pour supprimer un client
const deleteClient = (clientId) => {
    if (
        confirm(
            "Êtes-vous sûr de vouloir supprimer ce client ? Cette action est irréversible."
        )
    ) {
        router.delete(`/admin/clients/${clientId}`, {
            preserveScroll: true, // Ajout pour préserver la position de défilement
            preserveState: true, // Ajout pour préserver l'état
            onSuccess: (page) => {
                // Vérifier si un message est présent dans la réponse
                const flashMessages = page?.props?.flash || {};

                if (flashMessages.error) {
                    showNotification(flashMessages.error, "error");
                } else if (flashMessages.success) {
                    showNotification(flashMessages.success, "success");
                }
            },
            onError: (errors) => {
                showNotification(
                    "Une erreur est survenue lors de la suppression du client.",
                    "error"
                );
            },
        });
    }
};
</script>
<template>
    <AdminLayout>
        <section
            class="flex md:py-28 md:px-16 py-16 px-5 flex-col md:gap-20 gap-12 md:mt-14 mt-16 bg-[#2D2D2D]"
        >
            <div class="flex md:flex-row flex-col justify-between self-stretch">
                <div
                    class="flex md:max-w-[768px] flex-col items-start gap-4 text-white"
                >
                    <h2
                        class="md:text-center font-inter text-base font-semibold"
                    >
                        Clients
                    </h2>
                    <div class="relative flex flex-col gap-6">
                        <h3
                            class="font-poppins text-4xl md:text-5xl md:text-center font-medium md:leading-[57.6px] leading-[43.2px] tracking-[-0.36px] md:tracking-[-0.48px] mb-6"
                        >
                            Liste de mes clients
                        </h3>
                        <div
                            class="absolute md:bottom-0 bottom-10 md:left-1/3 left-32 border-2 border-[#FF8C42] md:w-[80%] w-[61%] max-w-[353px] md:min-w-[200px] min-w-[188px]"
                        ></div>
                    </div>
                </div>
                <div class="flex items-end">
                    <PrimaryButton @click="startCreatingClient" class="h-max"
                        >Ajouter</PrimaryButton
                    >
                </div>
            </div>

            <!-- Formulaire de création d'un nouveau client -->
            <transition name="fade">
                <div
                    v-if="isCreatingClient"
                    class="client-card flex flex-col md:flex-row items-center gap-8 w-full relative"
                >
                    <div
                        class="w-full md:w-1/2 rounded-lg"
                        :style="{
                            'background-color': newClientColor,
                            'aspect-ratio': '148/125',
                            'max-width': '300px',
                            'min-width': '150px',
                        }"
                    ></div>

                    <div
                        class="flex flex-col items-start gap-2 w-full md:w-1/2"
                    >
                        <div
                            class="font-medium font-poppins text-white text-2xl flex flex-col items-start"
                        >
                            <input
                                v-model="newClientForm.name"
                                type="text"
                                class="w-auto bg-transparent border-0 shadow-none text-white font-medium font-poppins text-2xl focus:outline-none focus:ring-0 focus:shadow-none p-0 m-0"
                                placeholder="Prénom"
                                style="
                                    width: auto;
                                    min-width: 10px;
                                    display: inline-block;
                                "
                            />
                            <input
                                v-model="newClientForm.lastname"
                                type="text"
                                class="w-auto bg-transparent border-0 shadow-none text-white font-medium font-poppins text-2xl focus:outline-none focus:ring-0 focus:shadow-none p-0 m-0"
                                placeholder="Nom"
                                style="
                                    width: auto;
                                    min-width: 10px;
                                    display: inline-block;
                                "
                            />
                        </div>
                        <div
                            class="font-inter text-white text-base font-normal flex items-center gap-2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                fill="currentColor"
                                class="bi bi-envelope flex-shrink-0"
                                viewBox="0 0 16 16"
                            >
                                <path
                                    d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"
                                />
                            </svg>
                            <input
                                v-model="newClientForm.email"
                                type="email"
                                class="bg-transparent border-0 shadow-none text-white font-inter text-base font-normal focus:outline-none focus:ring-0 focus:shadow-none p-0 inline-size-auto"
                                placeholder="Email"
                                style="width: min-content"
                            />
                        </div>
                        <div
                            class="font-inter text-[#FF8C42] text-base font-normal flex items-center gap-2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                fill="currentColor"
                                class="bi bi-telephone flex-shrink-0"
                                viewBox="0 0 16 16"
                            >
                                <path
                                    d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                />
                            </svg>
                            <input
                                v-model="newClientForm.phone"
                                type="tel"
                                class="bg-transparent border-0 shadow-none text-[#FF8C42] font-inter text-base font-normal focus:outline-none focus:ring-0 focus:shadow-none p-0 inline-size-auto"
                                placeholder="Téléphone"
                                style="width: min-content"
                            />
                        </div>
                        <div
                            class="font-inter text-blue-300 text-base font-normal flex items-center gap-2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                fill="currentColor"
                                class="bi bi-geo-alt flex-shrink-0"
                                viewBox="0 0 16 16"
                            >
                                <path
                                    d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"
                                />
                                <path
                                    d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"
                                />
                            </svg>
                            <input
                                v-model="newClientForm.adress"
                                type="text"
                                class="bg-transparent border-0 shadow-none text-blue-300 font-inter text-base font-normal focus:outline-none focus:ring-0 focus:shadow-none p-0 inline-size-auto"
                                placeholder="Adresse"
                                style="width: min-content"
                            />
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button
                                @click="saveNewClient"
                                class="text-white bg-[#FF8C42] hover:bg-orange-700 transition-colors rounded-full px-4 py-1 text-sm"
                            >
                                Confirmer
                            </button>
                            <button
                                @click="cancelCreatingClient"
                                class="text-white hover:text-gray-400 transition-colors text-sm"
                            >
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </transition>

            <div class="flex flex-wrap gap-6 md:gap-12">
                <transition-group
                    name="list"
                    tag="div"
                    class="flex flex-wrap gap-6 md:gap-12 w-full"
                >
                    <div
                        v-for="client in clients"
                        :key="client.id"
                        class="client-card flex flex-col md:flex-row items-center gap-6 md:gap-8 w-full md:w-[calc(50%-1.5rem)] relative group"
                    >
                        <!-- Version desktop : layout horizontal classique -->
                        <div
                            class="hidden md:block w-1/2 rounded-lg"
                            :style="{
                                'background-color':
                                    clientColors[client.id] || '#65558F',
                                'aspect-ratio': '148/125',
                                'max-width': '300px',
                                'min-width': '150px',
                            }"
                        ></div>

                        <!-- Version mobile : bande colorée à gauche + infos à droite -->
                        <div
                            class="md:hidden flex w-full relative cursor-pointer bg-gray-800/30 backdrop-blur-sm rounded-xl p-4 border-l-4 border-r border-t border-b border-r-gray-700/50 border-t-gray-700/50 border-b-gray-700/50 hover:border-r-gray-600/70 hover:border-t-gray-600/70 hover:border-b-gray-600/70 transition-all duration-300 active:scale-[0.98] active:bg-gray-800/50"
                            :style="{
                                'border-left-color':
                                    clientColors[client.id] || '#65558F',
                            }"
                            @touchstart="startLongPress(client.id)"
                            @touchend="cancelLongPress"
                            @touchcancel="cancelLongPress"
                            @mousedown="startLongPress(client.id)"
                            @mouseup="cancelLongPress"
                            @mouseleave="cancelLongPress"
                        >
                            <!-- Actions mobiles qui apparaissent après appui long -->
                            <div
                                v-if="showMobileActions[client.id]"
                                class="absolute top-2 right-2 bg-gray-900/95 backdrop-blur-md rounded-2xl p-3 flex gap-3 shadow-2xl border border-gray-700/50 z-10 animate-in slide-in-from-right-2 duration-200"
                                @click.stop
                                @touchstart.stop
                                @mousedown.stop
                            >
                                <button
                                    @click="
                                        startEditing(client);
                                        hideMobileActions(client.id);
                                    "
                                    title="Modifier"
                                    class="p-2 rounded-xl bg-blue-600/20 hover:bg-blue-600/30 border border-blue-500/30 transition-all duration-200 active:scale-95"
                                >
                                    <img
                                        src="/assets/icons/clients/edit-icon.svg"
                                        alt="Edit"
                                        class="w-5 h-5 filter brightness-110"
                                    />
                                </button>
                                <button
                                    @click="
                                        deleteClient(client.id);
                                        hideMobileActions(client.id);
                                    "
                                    title="Supprimer"
                                    class="p-2 rounded-xl bg-red-600/20 hover:bg-red-600/30 border border-red-500/30 transition-all duration-200 active:scale-95"
                                >
                                    <img
                                        src="/assets/icons/clients/delete-icon.svg"
                                        alt="Delete"
                                        class="w-4 h-4 filter brightness-110"
                                    />
                                </button>
                                <button
                                    @click="hideMobileActions(client.id)"
                                    title="Fermer"
                                    class="p-2 rounded-xl bg-gray-600/20 hover:bg-gray-600/30 border border-gray-500/30 transition-all duration-200 active:scale-95"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        fill="currentColor"
                                        class="bi bi-x text-gray-300"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <div class="w-full space-y-3">
                                <!-- Mode affichage normal mobile -->
                                <div
                                    v-if="editingClient !== client.id"
                                    class="flex flex-col gap-3 pointer-events-none"
                                >
                                    <div class="space-y-1">
                                        <p
                                            class="font-semibold font-poppins text-white text-xl leading-tight tracking-tight"
                                        >
                                            {{ client.name }}
                                        </p>
                                        <p
                                            class="font-medium font-poppins text-gray-300 text-lg leading-tight"
                                        >
                                            {{ client.lastname }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col gap-2.5">
                                        <a
                                            v-if="client.email"
                                            :href="`mailto:${client.email}`"
                                            class="font-inter text-gray-200 text-sm font-medium hover:text-[#FF8C42] transition-all duration-200 cursor-pointer flex items-center gap-2.5 truncate pointer-events-auto group active:scale-95"
                                            @click.stop
                                            @touchstart.stop
                                        >
                                            <div
                                                class="flex-shrink-0 w-8 h-8 bg-gray-700/50 rounded-lg flex items-center justify-center group-hover:bg-[#FF8C42]/20 transition-colors duration-200"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="14"
                                                    height="14"
                                                    fill="currentColor"
                                                    class="bi bi-envelope"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"
                                                    />
                                                </svg>
                                            </div>
                                            <span
                                                class="truncate font-medium"
                                                >{{ client.email }}</span
                                            >
                                        </a>
                                        <a
                                            v-if="client.phone"
                                            :href="`tel:${client.phone}`"
                                            class="font-inter text-[#FF8C42] text-sm font-medium hover:text-white transition-all duration-200 cursor-pointer flex items-center gap-2.5 pointer-events-auto group active:scale-95"
                                            @click.stop
                                            @touchstart.stop
                                        >
                                            <div
                                                class="flex-shrink-0 w-8 h-8 bg-[#FF8C42]/20 rounded-lg flex items-center justify-center group-hover:bg-[#FF8C42]/30 transition-colors duration-200"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="14"
                                                    height="14"
                                                    fill="currentColor"
                                                    class="bi bi-telephone"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                                    />
                                                </svg>
                                            </div>
                                            <span class="font-medium">{{
                                                client.phone
                                            }}</span>
                                        </a>
                                        <a
                                            v-if="client.adress"
                                            :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
                                                client.adress
                                            )}`"
                                            target="_blank"
                                            rel="noopener"
                                            class="font-inter text-blue-300 text-sm font-medium hover:text-[#FF8C42] transition-all duration-200 cursor-pointer flex items-center gap-2.5 truncate pointer-events-auto group active:scale-95"
                                            @click.stop
                                            @touchstart.stop
                                        >
                                            <div
                                                class="flex-shrink-0 w-8 h-8 bg-blue-600/20 rounded-lg flex items-center justify-center group-hover:bg-[#FF8C42]/20 transition-colors duration-200"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="14"
                                                    height="14"
                                                    fill="currentColor"
                                                    class="bi bi-geo-alt"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"
                                                    />
                                                    <path
                                                        d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"
                                                    />
                                                </svg>
                                            </div>
                                            <span
                                                class="truncate font-medium"
                                                >{{ client.adress }}</span
                                            >
                                        </a>
                                    </div>
                                </div>

                                <!-- Mode édition mobile -->
                                <div
                                    v-else
                                    class="flex flex-col gap-4 bg-gray-800/50 rounded-xl p-4 border border-gray-600/50"
                                >
                                    <div class="flex flex-col gap-3">
                                        <div class="space-y-2">
                                            <label
                                                class="text-xs font-medium text-gray-400 uppercase tracking-wider"
                                                >Prénom</label
                                            >
                                            <input
                                                v-model="editForm.name"
                                                type="text"
                                                class="bg-gray-700/50 border border-gray-600/50 rounded-lg text-white font-medium font-poppins text-lg focus:outline-none focus:border-[#FF8C42] focus:ring-2 focus:ring-[#FF8C42]/20 px-3 py-2 transition-all duration-200"
                                                placeholder="Prénom"
                                            />
                                        </div>
                                        <div class="space-y-2">
                                            <label
                                                class="text-xs font-medium text-gray-400 uppercase tracking-wider"
                                                >Nom</label
                                            >
                                            <input
                                                v-model="editForm.lastname"
                                                type="text"
                                                class="bg-gray-700/50 border border-gray-600/50 rounded-lg text-white font-medium font-poppins text-lg focus:outline-none focus:border-[#FF8C42] focus:ring-2 focus:ring-[#FF8C42]/20 px-3 py-2 transition-all duration-200"
                                                placeholder="Nom"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-3">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex-shrink-0 w-8 h-8 bg-gray-700/50 rounded-lg flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="14"
                                                    height="14"
                                                    fill="currentColor"
                                                    class="bi bi-envelope text-gray-300"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"
                                                    />
                                                </svg>
                                            </div>
                                            <input
                                                v-model="editForm.email"
                                                type="email"
                                                class="flex-1 bg-gray-700/50 border border-gray-600/50 rounded-lg text-white font-inter text-sm focus:outline-none focus:border-[#FF8C42] focus:ring-2 focus:ring-[#FF8C42]/20 px-3 py-2 transition-all duration-200"
                                                placeholder="Email"
                                            />
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex-shrink-0 w-8 h-8 bg-[#FF8C42]/20 rounded-lg flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="14"
                                                    height="14"
                                                    fill="currentColor"
                                                    class="bi bi-telephone text-[#FF8C42]"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                                    />
                                                </svg>
                                            </div>
                                            <input
                                                v-model="editForm.phone"
                                                type="tel"
                                                class="flex-1 bg-gray-700/50 border border-gray-600/50 rounded-lg text-[#FF8C42] font-inter text-sm focus:outline-none focus:border-[#FF8C42] focus:ring-2 focus:ring-[#FF8C42]/20 px-3 py-2 transition-all duration-200"
                                                placeholder="Téléphone"
                                            />
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex-shrink-0 w-8 h-8 bg-blue-600/20 rounded-lg flex items-center justify-center"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="14"
                                                    height="14"
                                                    fill="currentColor"
                                                    class="bi bi-geo-alt text-blue-300"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"
                                                    />
                                                    <path
                                                        d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"
                                                    />
                                                </svg>
                                            </div>
                                            <input
                                                v-model="editForm.adress"
                                                type="text"
                                                class="flex-1 bg-gray-700/50 border border-gray-600/50 rounded-lg text-blue-300 font-inter text-sm focus:outline-none focus:border-[#FF8C42] focus:ring-2 focus:ring-[#FF8C42]/20 px-3 py-2 transition-all duration-200"
                                                placeholder="Adresse"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex gap-3 mt-2">
                                        <button
                                            @click="saveClient"
                                            class="flex-1 text-white bg-[#FF8C42] hover:bg-orange-600 transition-all duration-200 rounded-xl px-4 py-2.5 text-sm font-medium active:scale-95 flex items-center justify-center gap-2"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                fill="currentColor"
                                                class="bi bi-check2"
                                                viewBox="0 0 16 16"
                                            >
                                                <path
                                                    d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"
                                                />
                                            </svg>
                                            Confirmer
                                        </button>
                                        <button
                                            @click="cancelEditing"
                                            class="px-4 py-2.5 text-gray-300 hover:text-white transition-all duration-200 text-sm font-medium active:scale-95 flex items-center justify-center gap-2"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                fill="currentColor"
                                                class="bi bi-x"
                                                viewBox="0 0 16 16"
                                            >
                                                <path
                                                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
                                                />
                                            </svg>
                                            Annuler
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Zone de clic pour fermer les actions mobiles quand on clique ailleurs -->
                            <div
                                v-if="showMobileActions[client.id]"
                                class="fixed inset-0 z-0 bg-black/20 backdrop-blur-sm"
                                @click="hideMobileActions(client.id)"
                                @touchstart="hideMobileActions(client.id)"
                            ></div>
                        </div>

                        <!-- Actions d'édition/suppression qui apparaissent au survol (desktop uniquement) -->
                        <div
                            class="hidden md:flex absolute top-0 right-2 opacity-0 group-hover:opacity-100 transition-opacity gap-2"
                        >
                            <button
                                @click="startEditing(client)"
                                title="Modifier"
                            >
                                <img
                                    src="/assets/icons/clients/edit-icon.svg"
                                    alt="Edit"
                                    class="w-6 h-6 hover:scale-110 transition-transform"
                                />
                            </button>
                            <button
                                @click="deleteClient(client.id)"
                                title="Supprimer"
                            >
                                <img
                                    src="/assets/icons/clients/delete-icon.svg"
                                    alt="Delete"
                                    class="w-5 h-5 hover:scale-110 transition-transform"
                                />
                            </button>
                        </div>

                        <!-- Mode affichage normal (desktop uniquement) -->
                        <transition name="fade" mode="out-in">
                            <div
                                v-if="editingClient !== client.id"
                                class="hidden md:flex flex-col items-start gap-2 w-1/2"
                            >
                                <p
                                    class="font-medium font-poppins text-white text-2xl"
                                >
                                    {{ client.name }} {{ client.lastname }}
                                </p>
                                <a
                                    v-if="client.email"
                                    :href="`mailto:${client.email}`"
                                    class="font-inter text-white text-base font-normal hover:text-[#FF8C42] transition-colors cursor-pointer flex items-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        fill="currentColor"
                                        class="bi bi-envelope"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"
                                        />
                                    </svg>
                                    {{ client.email }}
                                </a>
                                <a
                                    v-if="client.phone"
                                    :href="`tel:${client.phone}`"
                                    class="font-inter text-[#FF8C42] text-base font-normal hover:text-white transition-colors cursor-pointer flex items-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        fill="currentColor"
                                        class="bi bi-telephone"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                        />
                                    </svg>
                                    {{ client.phone }}
                                </a>
                                <a
                                    v-if="client.adress"
                                    :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
                                        client.adress
                                    )}`"
                                    target="_blank"
                                    rel="noopener"
                                    class="font-inter text-blue-300 text-base font-normal hover:text-[#FF8C42] transition-colors cursor-pointer flex items-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        fill="currentColor"
                                        class="bi bi-geo-alt"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"
                                        />
                                        <path
                                            d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"
                                        />
                                    </svg>
                                    {{ client.adress }}
                                </a>
                            </div>

                            <!-- Mode édition (desktop uniquement) -->
                            <div
                                v-else
                                class="hidden md:flex flex-col items-start gap-2 w-1/2"
                            >
                                <div
                                    class="font-medium font-poppins text-white text-2xl flex flex-col items-start"
                                >
                                    <input
                                        v-model="editForm.name"
                                        type="text"
                                        class="w-auto bg-transparent border-0 shadow-none text-white font-medium font-poppins text-2xl focus:outline-none focus:ring-0 focus:shadow-none p-0 m-0"
                                        placeholder="Prénom"
                                        style="
                                            width: auto;
                                            min-width: 10px;
                                            display: inline-block;
                                        "
                                    />
                                    <input
                                        v-model="editForm.lastname"
                                        type="text"
                                        class="w-auto bg-transparent border-0 shadow-none text-white font-medium font-poppins text-2xl focus:outline-none focus:ring-0 focus:shadow-none p-0 m-0"
                                        placeholder="Nom"
                                        style="
                                            width: auto;
                                            min-width: 10px;
                                            display: inline-block;
                                        "
                                    />
                                </div>
                                <div
                                    class="font-inter text-white text-base font-normal flex items-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        fill="currentColor"
                                        class="bi bi-envelope flex-shrink-0"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"
                                        />
                                    </svg>
                                    <input
                                        v-model="editForm.email"
                                        type="email"
                                        class="bg-transparent border-0 shadow-none text-white font-inter text-base font-normal focus:outline-none focus:ring-0 focus:shadow-none p-0 inline-size-auto"
                                        placeholder="Email"
                                        style="width: min-content"
                                    />
                                </div>
                                <div
                                    class="font-inter text-[#FF8C42] text-base font-normal flex items-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        fill="currentColor"
                                        class="bi bi-telephone flex-shrink-0"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                        />
                                    </svg>
                                    <input
                                        v-model="editForm.phone"
                                        type="tel"
                                        class="bg-transparent border-0 shadow-none text-[#FF8C42] font-inter text-base font-normal focus:outline-none focus:ring-0 focus:shadow-none p-0 inline-size-auto"
                                        placeholder="Téléphone"
                                        style="width: min-content"
                                    />
                                </div>
                                <div
                                    class="font-inter text-blue-300 text-base font-normal flex items-center gap-2"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        fill="currentColor"
                                        class="bi bi-geo-alt flex-shrink-0"
                                        viewBox="0 0 16 16"
                                    >
                                        <path
                                            d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"
                                        />
                                        <path
                                            d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"
                                        />
                                    </svg>
                                    <input
                                        v-model="editForm.adress"
                                        type="text"
                                        class="bg-transparent border-0 shadow-none text-blue-300 font-inter text-base font-normal focus:outline-none focus:ring-0 focus:shadow-none p-0 inline-size-auto"
                                        placeholder="Adresse"
                                        style="width: min-content"
                                    />
                                </div>
                                <div class="flex gap-2 mt-2">
                                    <button
                                        @click="saveClient"
                                        class="text-white bg-[#FF8C42] hover:bg-orange-700 transition-colors rounded-full px-4 py-1 text-sm"
                                    >
                                        Confirmer
                                    </button>
                                    <button
                                        @click="cancelEditing"
                                        class="text-white hover:text-gray-400 transition-colors text-sm"
                                    >
                                        Annuler
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>
                </transition-group>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

.list-enter-active,
.list-leave-active {
    transition: all 0.4s ease;
}
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(20px) scale(0.95);
}
.list-move {
    transition: transform 0.4s ease;
}

@keyframes slide-in-from-left {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slide-in-from-right {
    from {
        opacity: 0;
        transform: translateX(10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-in {
    animation-duration: 0.2s;
    animation-timing-function: ease-out;
    animation-fill-mode: both;
}

.slide-in-from-left-2 {
    animation-name: slide-in-from-left;
}

.slide-in-from-right-2 {
    animation-name: slide-in-from-right;
}

/* Amélioration des focus states pour l'accessibilité */
.client-card:focus-within {
    outline: 2px solid #ff8c42;
    outline-offset: 2px;
}

/* Effet de pulsation pour indiquer l'appui long */
.client-card:active {
    animation: pulse-subtle 0.5s ease-in-out;
}

@keyframes pulse-subtle {
    0%,
    100% {
        transform: scale(1);
    }
    50% {
        transform: scale(0.98);
    }
}

/* Amélioration des transitions pour les boutons */
button {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Effet de survol plus doux pour les liens */
a {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
