<script setup lang="ts">
import { ref, reactive, inject, onMounted, watch, nextTick } from "vue";
import { VueFinalModal, useVfm } from "vue-final-modal";
import "vue-final-modal/style.css";
import axios from "axios";
import ServicesModal from "@/Components/ServicesModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { marked } from "marked";
// Remplacer l'importation de Quill par TipTap
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Link from "@tiptap/extension-link";
import Placeholder from "@tiptap/extension-placeholder";

// Configuration de marked pour une conversion sécurisée
marked.setOptions({
    gfm: true,
    breaks: true,
    sanitize: true,
});

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

type NotificationFunction = (message: string, type?: string) => void;

// Récupération des fonctions de notification depuis le contexte
const showNotification = inject<NotificationFunction>("showNotification");

// Initialisation de l'API vue-final-modal
const vfm = useVfm();

// Variable pour contrôler l'animation
const modalVisible = ref(false);

// Fonction pour animer l'apparition du modal
onMounted(() => {
    // Attendre que le DOM soit mis à jour
    nextTick(() => {
        // Déclencher l'animation après un court délai
        setTimeout(() => {
            modalVisible.value = true;
        }, 50);
    });
});

// Fonction pour fermer le modal avec animation
const closeWithAnimation = () => {
    // D'abord, déclencher l'animation de fermeture
    modalVisible.value = false;

    // Puis fermer le modal après que l'animation soit terminée
    setTimeout(() => {
        vfm.closeAll();
    }, 200); // Durée réduite pour une fermeture plus rapide
};

const step = ref(1);
const isSubmitting = ref(false);
const selectedServiceIds = ref<number[]>([]);
const successMessage = ref("");
const errorMessage = ref("");
const markdownContent = ref("");

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

// Configuration de l'éditeur TipTap
const editor = useEditor({
    content: "",
    extensions: [
        StarterKit.configure({
            bulletList: {
                keepMarks: true,
                keepAttributes: true,
            },
            orderedList: {
                keepMarks: true,
                keepAttributes: true,
            },
            heading: false, // Désactiver les titres pour simplifier l'interface
        }),
        Link.configure({
            openOnClick: false,
        }),
        Placeholder.configure({
            placeholder: "Décrivez votre projet... *",
        }),
    ],
    onUpdate: ({ editor }) => {
        // Convertir le contenu HTML en Markdown
        const content = editor.getHTML();
        markdownContent.value = content;
        formData.description = content;
    },
    editorProps: {
        handleKeyDown: (view, event) => {
            // Gérer Tab pour les indentations
            if (event.key === "Tab") {
                // Utiliser les commandes de base au lieu de outdent/indent
                if (event.shiftKey) {
                    // Reculer une liste
                    editor.value
                        ?.chain()
                        .focus()
                        .liftListItem("listItem")
                        .run();
                } else {
                    // Avancer une liste
                    editor.value
                        ?.chain()
                        .focus()
                        .sinkListItem("listItem")
                        .run();
                }
                return true;
            }
            return false;
        },
    },
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

// Fonction de soumission avec Axios et notifications
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
            end_date: null,
        };

        // Ajouter les dates souhaitées si elles sont définies
        if (
            formData.desiredDate &&
            Array.isArray(formData.desiredDate) &&
            formData.desiredDate.length === 2
        ) {
            requestData.requested_date = formatDate(formData.desiredDate[0]);
            requestData.end_date = formatDate(formData.desiredDate[1]);
        }

        // Effectuer la requête POST vers l'API
        const response = await axios.post("/quotes", requestData);

        // En cas de succès
        const successMsg = "Votre demande de devis a été envoyée avec succès !";

        // Émettre les événements pour la compatibilité
        emit("submit", formData);
        emit("success", successMsg);

        // Afficher la notification si la fonction est disponible
        if (showNotification) {
            showNotification(successMsg, "success");
        } else {
            // Fallback au message interne si la fonction n'est pas disponible
            successMessage.value = successMsg;
        }

        // Réinitialiser le formulaire et fermer la modal
        resetForm();
        emit("close");
    } catch (error) {
        console.error("Erreur lors de la soumission du devis:", error);

        // Gestion spécifique des erreurs de validation (422)
        if (error.response && error.response.status === 422) {
            handleValidationErrors(error.response.data.errors);
        } else {
            // Erreur générale
            const errorMsg =
                "Une erreur est survenue lors de l'envoi de votre demande.";

            // Émettre l'événement d'erreur
            emit("error", errorMsg);

            // Afficher la notification si la fonction est disponible
            if (showNotification) {
                showNotification(errorMsg, "error");
            } else {
                // Fallback au message interne si la fonction n'est pas disponible
                errorMessage.value = errorMsg;
            }
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

    // Notification d'erreur
    if (showNotification) {
        showNotification(
            "Veuillez corriger les erreurs dans le formulaire.",
            "error"
        );
    }
};

// Réinitialiser le formulaire après soumission réussie
const resetForm = () => {
    step.value = 1;
    selectedServiceIds.value = [];
    markdownContent.value = "";

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

    // Réinitialiser les messages
    successMessage.value = "";
    errorMessage.value = "";
};

// Synchroniser le contenu Markdown avec le champ description du formulaire
const syncMarkdownContent = (content) => {
    formData.description = content;
};

// Watcher pour la mise à jour du contenu markdown
watch(markdownContent, (newContent) => {
    syncMarkdownContent(newContent);
});

// Fonction pour précharger les services
const preloadServices = async () => {
    try {
        // Précharger les services pour qu'ils soient disponibles immédiatement
        await axios.get("services");
    } catch (error) {
        console.error("Erreur lors du préchargement des services:", error);
    }
};

onMounted(() => {
    // Réinitialiser le formulaire quand le composant est monté
    resetForm();

    // Précharger les services dès que possible
    preloadServices();
});
</script>

<template>
    <VueFinalModal
        :overlay-transition="{
            'enter-active-class': 'transition-opacity duration-200 ease-out',
            'enter-from-class': 'opacity-0',
            'enter-to-class': 'opacity-100',
            'leave-active-class': 'transition-opacity duration-150 ease-in',
            'leave-from-class': 'opacity-100',
            'leave-to-class': 'opacity-0'
        }"
        content-transition=""
        overlay-class="bg-[rgba(0,0,0,0.4)]"
        class="flex justify-center items-end md:items-center md:px-2 px-0"
        :content-class="[
            'w-full md:max-w-5xl shadow-lg relative md:h-[min(720px,90vh)] md:max-h-[90vh] h-screen max-h-screen flex flex-col transition-all duration-300',
            modalVisible ? 'translate-y-0 opacity-100 md:scale-100 md:translate-y-0' : 'translate-y-full md:scale-95 md:translate-y-0 opacity-0',
            'md:ease-[cubic-bezier(0.19,1,0.22,1)] ease-out md:rounded-xl overflow-hidden'
        ]"
        :reserve-scroll-bar-gap="false"
        :lock-scroll="false"
    >
        <!-- Bouton de fermeture (croix) - pour mobile et desktop -->
        <button
            @click="closeWithAnimation()"
            class="absolute top-3 right-3 z-20 bg-white/10 hover:bg-white/20 rounded-full p-2 transition-all duration-300 md:top-4 md:right-4"
            aria-label="Fermer"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <!-- Message de succès (fallback) -->
        <div
            v-if="successMessage"
            class="absolute top-0 left-0 right-0 bg-green-500 text-white p-3 text-center z-10"
        >
            {{ successMessage }}
        </div>

        <!-- Message d'erreur (fallback) -->
        <div
            v-if="errorMessage"
            class="absolute top-0 left-0 right-0 bg-red-500 text-white p-3 text-center z-10"
        >
            {{ errorMessage }}
        </div>

        <div
            class="flex-1 overflow-y-auto flex justify-center overflow-x-hidden"
            :class="{
                'bg-[#FBFAF6]': step === 1 || step === 3,
                'bg-[#2D2D2D]': step === 2,
            }"
        >
            <div
                v-if="step === 1"
                class="flex flex-col gap-11 w-full max-w-3xl md:pt-16 pt-12 px-5 pb-5"
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
                    class="text-sm text-red-500"
                >
                    Veuillez sélectionner au moins un service
                </div>

                <ServicesModal
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
                class="flex flex-col gap-11 w-full justify-center text-center max-w-3xl md:pt-16 pt-12 px-5 pb-5"
            >
                <h3
                    class="text-[#ffff] text-3xl font-poppins font-medium text-center"
                >
                    Dites m'en plus :
                </h3>

                <div class="flex flex-col gap-4">
                    <div class="flex flex-col items-start">
                        <!-- Barre d'outils TipTap -->
                        <div
                            class="toolbar bg-white/10 border border-white/20 border-b-0 rounded-t-lg p-2 flex gap-2 relative"
                            style="width: 100%; max-width: 100%; display: flex; justify-content: flex-start;"
                        >
                            <button
                                @click="
                                    editor?.chain().focus().toggleBold().run()
                                "
                                :class="{
                                    'bg-[#FF8C42]/20': editor?.isActive('bold'),
                                }"
                                class="p-1 rounded hover:bg-[#FF8C42]/10 transition-colors"
                                title="Gras"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="white"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"
                                    ></path>
                                    <path
                                        d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"
                                    ></path>
                                </svg>
                            </button>
                            <button
                                @click="
                                    editor?.chain().focus().toggleItalic().run()
                                "
                                :class="{
                                    'bg-[#FF8C42]/20':
                                        editor?.isActive('italic'),
                                }"
                                class="p-1 rounded hover:bg-[#FF8C42]/10 transition-colors"
                                title="Italique"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="white"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <line x1="19" y1="4" x2="10" y2="4"></line>
                                    <line x1="14" y1="20" x2="5" y2="20"></line>
                                    <line x1="15" y1="4" x2="9" y2="20"></line>
                                </svg>
                            </button>
                            <button
                                @click="
                                    editor
                                        ?.chain()
                                        .focus()
                                        .toggleBulletList()
                                        .run()
                                "
                                :class="{
                                    'bg-[#FF8C42]/20':
                                        editor?.isActive('bulletList'),
                                }"
                                class="p-1 rounded hover:bg-[#FF8C42]/10 transition-colors"
                                title="Liste à puces"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="white"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line
                                        x1="3"
                                        y1="12"
                                        x2="3.01"
                                        y2="12"
                                    ></line>
                                    <line
                                        x1="3"
                                        y1="18"
                                        x2="3.01"
                                        y2="18"
                                    ></line>
                                </svg>
                            </button>
                            <button
                                @click="
                                    editor
                                        ?.chain()
                                        .focus()
                                        .toggleOrderedList()
                                        .run()
                                "
                                :class="{
                                    'bg-[#FF8C42]/20':
                                        editor?.isActive('orderedList'),
                                }"
                                class="p-1 rounded hover:bg-[#FF8C42]/10 transition-colors"
                                title="Liste numérotée"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="white"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <line x1="10" y1="6" x2="21" y2="6"></line>
                                    <line
                                        x1="10"
                                        y1="12"
                                        x2="21"
                                        y2="12"
                                    ></line>
                                    <line
                                        x1="10"
                                        y1="18"
                                        x2="21"
                                        y2="18"
                                    ></line>
                                    <path d="M4 6h1v4"></path>
                                    <path d="M4 10h2"></path>
                                    <path
                                        d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <EditorContent
                            :editor="editor"
                            class="w-full min-h-[180px max-h-[250px] overflow-y-auto bg-white/10 border border-white/20 text-white rounded-b-[6px] focus:ring-[#FF8C42] focus:border-[#FF8C42] p-2 font-inter text-base text-left"
                        />
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
                            calendar-class-name="bg-[#2D2D2D] border border-white/10 rounded-md shadow-lg"
                            dark
                            :menu-class-name="'dp-menu'"
                            :preview-class-name="'text-white'"
                            :year-picker-class-name="'text-white bg-[#2D2D2D]'"
                            :month-picker-class-name="'text-white bg-[#2D2D2D]'"
                            :action-row-class-name="'bg-[#2D2D2D] border-t border-white/10'"
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
                class="flex flex-col gap-11 w-full max-w-3xl justify-center md:pt-16 pt-12 px-5 pb-5"
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
            class="p-6 md:rounded-b-lg sticky bottom-0 w-full"
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
    transition: opacity 0.4s cubic-bezier(0.25, 0.1, 0.25, 0.25),
        transform 0.4s cubic-bezier(0.25, 0.1, 0.25, 0.25);
    transform-origin: center center;
}
.vfm-scale-fade-enter-from,
.vfm-scale-fade-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

/* Animation pour le modal */
.slide-bottom-enter-active,
.slide-bottom-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.slide-bottom-enter-from,
.slide-bottom-leave-to {
    transform: translateY(100%);
    opacity: 0;
}

@media (min-width: 768px) {
    .slide-bottom-enter-from,
    .slide-bottom-leave-to {
        transform: scale(0.95);
        opacity: 0;
    }
}

/* Styles pour l'éditeur TipTap */
:deep(.ProseMirror) {
    min-height: 150px;
    padding: 0.75rem;
    color: white;
    outline: none !important;
    overflow-y: auto;
    outline: none;
    width: 100%;
}

:deep(.ProseMirror p) {
    margin-bottom: 0.5rem;
}

:deep(.ProseMirror ul),
:deep(.ProseMirror ol) {
    padding-left: 1.5rem;
    margin-bottom: 0.5rem;
}

:deep(.ProseMirror ul li) {
    list-style-type: disc;
}

:deep(.ProseMirror ol li) {
    list-style-type: decimal;
}

:deep(.ProseMirror li p) {
    margin: 0;
}

:deep(.ProseMirror a) {
    color: #FF8C42;
    text-decoration: underline;
}

/* Styles pour le placeholder */
:deep(.ProseMirror p.is-editor-empty:first-child::before) {
    content: attr(data-placeholder);
    float: left;
    color: rgba(255, 255, 255, 0.5);
    pointer-events: none;
    height: 0;
}

/* Styles personnalisés pour le calendrier */
:deep(.dp__theme_dark) {
    --dp-background-color: #2D2D2D;
    --dp-text-color: #fff;
    --dp-hover-color: rgba(255, 255, 255, 0.1);
    --dp-hover-text-color: #fff;
    --dp-hover-icon-color: #fff;
    --dp-primary-color: #FF8C42;
    --dp-primary-text-color: #fff;
    --dp-secondary-color: rgba(255, 140, 66, 0.2);
    --dp-border-color: rgba(255, 255, 255, 0.1);
    --dp-menu-border-color: rgba(255, 255, 255, 0.1);
    --dp-border-color-hover: #FF8C42;
    --dp-disabled-color: rgba(255, 255, 255, 0.3);
    --dp-scroll-bar-background: rgba(255, 255, 255, 0.1);
    --dp-scroll-bar-color: rgba(255, 255, 255, 0.2);
    --dp-success-color: #82AD84;
    --dp-success-color-disabled: rgba(130, 173, 132, 0.3);
    --dp-icon-color: #fff;
    --dp-danger-color: #e53935;
}

:deep(.dp__action_buttons) {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem;
}

:deep(.dp__action_button) {
    background-color: transparent;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    color: #fff;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

:deep(.dp__action_select) {
    background-color: #FF8C42 !important;
    border-color: #FF8C42 !important;
}

:deep(.dp__action_button:hover) {
    background-color: rgba(255, 255, 255, 0.1);
}

:deep(.dp__month_year_row) {
    margin-bottom: 0.5rem;
}

:deep(.dp__month_year_select) {
    color: #fff;
}

:deep(.dp__cell_inner) {
    border-radius: 50%;
    transition: all 0.2s;
}

:deep(.dp__active_date) {
    background-color: #FF8C42 !important;
    color: #fff !important;
}

:deep(.dp__date_hover) {
    background-color: rgba(255, 255, 255, 0.1) !important;
}

:deep(.dp__today) {
    border: 1px solid #FF8C42 !important;
}

:deep(.dp__arrow_bottom) {
    border-color: #FF8C42 transparent transparent transparent !important;
}

:deep(.dp__arrow_top) {
    border-color: transparent transparent #FF8C42 transparent !important;
}
</style>
