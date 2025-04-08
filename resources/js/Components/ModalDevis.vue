<script setup lang="ts">
import { ref, reactive } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import Services from "@/Components/Services.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import axios from "axios";

// Définition d'une prop optionnelle pour le titre du modal
const props = defineProps<{
    title?: string;
}>();

// Définition des événements émis
const emit = defineEmits<{
    (e: "submit", formData: Record<string, any>): void;
    (e: "close"): void;
    (e: "success", message: string): void;
    (e: "error", message: string): void;
}>();

const step = ref(1);
const isSubmitting = ref(false);
const selectedServiceIds = ref<number[]>([]);
const successMessage = ref("");
const errorMessage = ref("");

const formData = reactive({
    services: [] as number[],
    description: "",
    desiredDate: [],
    name: "",
    lastName: "",
    phone: "",
    email: "",
    address: "",
});

// Ajout du statut de formulaire pour gérer les erreurs
const formStatus = reactive({
    errors: {
        name: false,
        lastName: false,
        phone: false,
        email: false,
        address: false,
        description: false,
        desiredDate: false,
        general: false,
        services: false,
    },
    message: "",
    step1Message: "",
    step2Message: "",
});

const handleServiceSelection = (serviceIds: number[]) => {
    selectedServiceIds.value = serviceIds;
    formData.services = [...serviceIds];
    // Réinitialiser l'erreur quand l'utilisateur sélectionne des services
    if (serviceIds.length > 0) {
        formStatus.errors.services = false;
        formStatus.step1Message = "";
    }
};

const nextStep = () => {
    if (step.value === 1) {
        if (validateServiceSelection()) {
            step.value++;
        }
    } else if (step.value === 2) {
        if (validateProjectForm()) {
            step.value++;
        }
    } else {
        if (step.value < 3) step.value++;
    }
};

const prevStep = () => {
    if (step.value > 1) step.value--;
};

// Validation de l'étape 1 - Sélection des services
const validateServiceSelection = () => {
    // Réinitialiser les erreurs
    formStatus.errors.services = false;
    formStatus.step1Message = "";

    let isValid = true;

    // Au moins un service doit être sélectionné
    if (selectedServiceIds.value.length === 0) {
        formStatus.errors.services = true;
        formStatus.step1Message = "Veuillez sélectionner au moins un service.";
        isValid = false;
    }

    return isValid;
};

// Validation de l'étape 2 - Description du projet
const validateProjectForm = () => {
    // Réinitialiser les erreurs
    formStatus.errors.description = false;
    formStatus.errors.desiredDate = false;
    formStatus.step2Message = "";

    let isValid = true;

    // La description est obligatoire
    if (!formData.description.trim()) {
        formStatus.errors.description = true;
        isValid = false;
    }

    // Date souhaitée optionnelle mais si fournie, vérifier qu'elle est valide
    if (
        formData.desiredDate &&
        Array.isArray(formData.desiredDate) &&
        formData.desiredDate.length === 1
    ) {
        formStatus.errors.desiredDate = true;
        isValid = false;
    }

    return isValid;
};

// Ajout d'une fonction de validation pour l'étape 3
const validateContactForm = () => {
    // Réinitialiser les erreurs
    formStatus.errors = {
        name: false,
        lastName: false,
        phone: false,
        email: false,
        address: false,
        description: false,
        desiredDate: false,
        services: false,
        general: false,
    };
    formStatus.message = "";

    let isValid = true;

    // Validation des champs obligatoires
    if (!formData.name.trim()) {
        formStatus.errors.name = true;
        isValid = false;
    }

    if (!formData.lastName.trim()) {
        formStatus.errors.lastName = true;
        isValid = false;
    }

    if (!formData.phone.trim()) {
        formStatus.errors.phone = true;
        isValid = false;
    }

    if (!formData.email.trim()) {
        formStatus.errors.email = true;
        isValid = false;
    } else {
        // Validation basique du format email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(formData.email)) {
            formStatus.errors.email = true;
            isValid = false;
        }
    }

    return isValid;
};

// Formater une date pour le backend (format YYYY-MM-DD)
const formatDate = (date) => {
    if (!date) return null;

    const d = date instanceof Date ? date : new Date(date);

    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

// Nouvelle fonction de soumission avec Axios
const submitForm = async () => {
    // Valider le formulaire avant envoi
    if (!validateContactForm()) {
        return;
    }

    // Activer l'indicateur de chargement
    isSubmitting.value = true;
    errorMessage.value = "";
    successMessage.value = "";

    try {
        // Préparer les données pour l'API
        const requestData = {
            name: formData.name,
            lastname: formData.lastName,
            phone: formData.phone,
            email: formData.email,
            adress: formData.address, // Attention à l'orthographe différente (adress vs address)
            description: formData.description,
            services: formData.services,
            requested_date: null,
        };

        // Ajouter la date souhaitée si elle est définie
        if (
            formData.desiredDate &&
            Array.isArray(formData.desiredDate) &&
            formData.desiredDate.length === 2
        ) {
            requestData.requested_date = formatDate(formData.desiredDate[0]);
        }

        // Effectuer la requête POST vers l'API
        const response = await axios.post("/quotes", requestData);

        // En cas de succès
        successMessage.value =
            "Votre demande de devis a été envoyée avec succès !";
        emit("submit", formData);
        emit("success", successMessage.value);

        // Réinitialiser le formulaire
        resetForm();
    } catch (error) {
        console.error("Erreur lors de la soumission du devis:", error);

        // Gestion spécifique des erreurs de validation (422)
        if (error.response && error.response.status === 422) {
            handleValidationErrors(error.response.data.errors);
        } else {
            // Erreur générale
            errorMessage.value =
                "Une erreur est survenue lors de l'envoi de votre demande.";
            emit("error", errorMessage.value);
        }
    } finally {
        // Désactiver l'indicateur de chargement
        isSubmitting.value = false;
    }
};

// Gérer les erreurs de validation du backend
const handleValidationErrors = (errors) => {
    // Réinitialiser les erreurs
    formStatus.errors = {
        name: false,
        lastName: false,
        phone: false,
        email: false,
        address: false,
        description: false,
        desiredDate: false,
        services: false,
        general: false,
    };

    // Mapper les erreurs du backend vers notre structure
    if (errors.name) formStatus.errors.name = true;
    if (errors.lastname) formStatus.errors.lastName = true;
    if (errors.phone) formStatus.errors.phone = true;
    if (errors.email) formStatus.errors.email = true;
    if (errors.adress) formStatus.errors.address = true;
    if (errors.description) formStatus.errors.description = true;
    if (errors.services) formStatus.errors.services = true;
    if (errors.requested_date) formStatus.errors.desiredDate = true;

    // Message d'erreur global
    formStatus.message = "Veuillez corriger les erreurs dans le formulaire.";
};

// Réinitialiser le formulaire après soumission réussie
const resetForm = () => {
    step.value = 1;
    selectedServiceIds.value = [];

    // Réinitialiser toutes les données du formulaire
    Object.assign(formData, {
        services: [],
        description: "",
        desiredDate: [],
        name: "",
        lastName: "",
        phone: "",
        email: "",
        address: "",
    });

    // Réinitialiser les erreurs
    formStatus.errors = {
        name: false,
        lastName: false,
        phone: false,
        email: false,
        address: false,
        description: false,
        desiredDate: false,
        general: false,
        services: false,
    };

    formStatus.message = "";
    formStatus.step1Message = "";
    formStatus.step2Message = "";
};
</script>

<template>
    <VueFinalModal
        overlay-transition="vfm-fade"
        content-transition="vfm-scale-fade"
        overlay-class="bg-[rgba(0,0,0,0.4)]"
        class="flex justify-center items-center px-2"
        content-class="w-full max-w-5xl rounded-lg shadow-lg relative h-[min(720px,90vh)] flex flex-col"
        :reserve-scroll-bar-gap="false"
        :lock-scroll="false"
    >
        <!-- Message de succès -->
        <div
            v-if="successMessage"
            class="absolute top-0 left-0 right-0 bg-green-500 text-white p-3 text-center z-10"
        >
            {{ successMessage }}
        </div>

        <!-- Message d'erreur -->
        <div
            v-if="errorMessage"
            class="absolute top-0 left-0 right-0 bg-red-500 text-white p-3 text-center z-10"
        >
            {{ errorMessage }}
        </div>

        <div
            class="flex-1 overflow-y-auto rounded-t-lg flex justify-center"
            :class="{
                'bg-[#FBFAF6]': step === 1 || step === 3,
                'bg-[#2D2D2D]': step === 2,
            }"
        >
            <div
                v-if="step === 1"
                class="flex flex-col gap-11 w-full max-w-3xl pt-16"
            >
                <h3
                    class="text-3xl text-[#0D0703] font-poppins font-medium text-center"
                >
                    Je désire :
                </h3>

                <div
                    v-if="
                        formStatus.errors.services &&
                        selectedServiceIds.length === 0
                    "
                    class="mt-2 text-sm text-red-500"
                >
                    Veuillez sélectionner au moins un service
                </div>

                <Services
                    :selectable="true"
                    :selected-services="selectedServiceIds"
                    height="200px"
                    @service-selected="handleServiceSelection"
                    :class="{ 'border-red-500': formStatus.errors.services }"
                />
                <div
                    v-if="selectedServiceIds.length > 0"
                    class="mt-2 text-sm text-gray-600"
                >
                    {{ selectedServiceIds.length }} service{{
                        selectedServiceIds.length > 1 ? "s" : ""
                    }}
                    sélectionné{{ selectedServiceIds.length > 1 ? "s" : "" }}
                </div>
            </div>
            <div
                v-else-if="step === 2"
                class="flex flex-col gap-11 w-full justify-center text-center max-w-3xl"
            >
                <h3
                    class="text-[#ffff] text-3xl font-poppins font-medium text-center"
                >
                    Dites m'en plus :
                </h3>

                <div class="flex flex-col gap-4">
                    <div class="flex flex-col items-start">
                        <textarea
                            v-model="formData.description"
                            rows="4"
                            placeholder="Décrivez votre projet... *"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500':
                                    formStatus.errors.description,
                            }"
                            class="w-full h-[180px] bg-white/10 border border-white/20 text-white rounded-[6px] focus:ring-[#FF8C42] focus:border-[#FF8C42] p-2 font-inter text-base"
                        ></textarea>
                        <span
                            v-if="formStatus.errors.description"
                            class="text-red-400 text-sm mt-1 text-left"
                        >
                            Ce champ est requis
                        </span>
                    </div>
                    <div class="flex flex-col items-start">
                        <Datepicker
                            v-model="formData.desiredDate"
                            range
                            :enable-time-picker="false"
                            text-input
                            auto-apply
                            :format="'dd/MM/yyyy'"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500':
                                    formStatus.errors.desiredDate,
                            }"
                            input-class-name="w-full sm:w-max bg-white/10 border border-white/20 rounded-[6px] focus:ring-[#FF8C42] focus:border-[#FF8C42] p-2 text-white"
                            :theme-color="'#FF8C42'"
                            placeholder="Période souhaitée"
                            :teleport="true"
                        />
                        <span
                            v-if="formStatus.errors.desiredDate"
                            class="text-red-400 text-sm mt-1 text-left"
                        >
                            Veuillez sélectionner une date de début et de fin
                        </span>
                    </div>
                </div>
            </div>

            <div
                v-else-if="step === 3"
                class="flex flex-col gap-11 w-full max-w-3xl justify-center"
            >
                <h3
                    class="text-3xl text-[#0D0703] font-poppins font-medium text-center"
                >
                    Parlez-moi de vous :
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[#2D2D2D] font-inter"
                            >Nom*</label
                        >
                        <input
                            type="text"
                            v-model="formData.name"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500':
                                    formStatus.errors.name,
                            }"
                            class="border rounded py-2 px-3 border-white/20 bg-[#0D07030D] w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
                        />
                        <span
                            v-if="formStatus.errors.name"
                            class="text-red-500 text-sm mt-1"
                            >Ce champ est requis</span
                        >
                    </div>
                    <div>
                        <label class="block text-[#2D2D2D] font-inter"
                            >Prénom*</label
                        >
                        <input
                            type="text"
                            v-model="formData.lastName"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500':
                                    formStatus.errors.lastName,
                            }"
                            class="border rounded py-2 px-3 border-white/20 bg-[#0D07030D] w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
                        />
                        <span
                            v-if="formStatus.errors.lastName"
                            class="text-red-500 text-sm mt-1"
                            >Ce champ est requis</span
                        >
                    </div>
                    <div>
                        <label class="block text-[#2D2D2D] font-inter"
                            >Téléphone*</label
                        >
                        <input
                            type="text"
                            v-model="formData.phone"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500':
                                    formStatus.errors.phone,
                            }"
                            class="border rounded py-2 px-3 border-white/20 bg-[#0D07030D] w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
                        />
                        <span
                            v-if="formStatus.errors.phone"
                            class="text-red-500 text-sm mt-1"
                            >Ce champ est requis</span
                        >
                    </div>
                    <div>
                        <label class="block text-[#2D2D2D] font-inter"
                            >Email*</label
                        >
                        <input
                            type="email"
                            v-model="formData.email"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500':
                                    formStatus.errors.email,
                            }"
                            class="border rounded py-2 px-3 border-white/20 bg-[#0D07030D] w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
                        />
                        <span
                            v-if="formStatus.errors.email"
                            class="text-red-500 text-sm mt-1"
                            >Veuillez entrer un email valide</span
                        >
                    </div>
                    <div class="col-span-2">
                        <label class="block text-[#2D2D2D] font-inter"
                            >Adresse</label
                        >
                        <textarea
                            v-model="formData.address"
                            rows="3"
                            class="border rounded-[6px] py-2 px-3 border-white/20 bg-[#0D07030D] w-full focus:outline-none focus:ring-2 focus:ring-orange-500 h-[100px] max-h-[100px]"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="p-6 rounded-b-lg sticky bottom-0"
            :class="{
                'bg-[#FBFAF6]': step === 1 || step === 3,
                'bg-[#2D2D2D]': step === 2,
            }"
        >
            <div class="flex justify-between">
                <div v-if="step === 1"></div>
                <SecondaryButton
                    v-if="step === 1"
                    @click="nextStep"
                    variant="dark"
                    class="transition"
                >
                    Suivant
                </SecondaryButton>
                <SecondaryButton
                    v-if="step === 2"
                    @click="prevStep"
                    class="transition"
                >
                    Précédent
                </SecondaryButton>
                <PrimaryButton
                    v-if="step === 2"
                    @click="nextStep"
                    class="transition"
                >
                    Suivant
                </PrimaryButton>
                <SecondaryButton
                    v-if="step === 3"
                    @click="prevStep"
                    variant="dark"
                    class="transition"
                >
                    Précédent
                </SecondaryButton>
                <PrimaryButton
                    v-if="step === 3"
                    @click="submitForm"
                    class="transition"
                    :disabled="isSubmitting"
                >
                    <span v-if="isSubmitting">
                        <svg
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        Envoi en cours...
                    </span>
                    <span v-else>Terminé</span>
                </PrimaryButton>
            </div>

            <!-- Message d'erreur global -->
            <div
                v-if="formStatus.message"
                class="mt-4 text-red-500 text-center"
            >
                {{ formStatus.message }}
            </div>
        </div>
    </VueFinalModal>
</template>

<style scoped>
.vfm-fade-enter-active,
.vfm-fade-leave-active {
    transition: opacity 0.3s ease;
}
.vfm-fade-enter-from,
.vfm-fade-leave-to {
    opacity: 0;
}

.vfm-scale-fade-enter-active,
.vfm-scale-fade-leave-active {
    transition: opacity 0.4s cubic-bezier(0.25, 0.1, 0.25, 1),
        transform 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
    transform-origin: center center;
}
.vfm-scale-fade-enter-from,
.vfm-scale-fade-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
