<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { computed, inject, ref, onMounted, onUnmounted, watch } from "vue";
import { marked } from "marked";
import PrimaryButton from "@/Components/PrimaryButton.vue";

// Import Splide
import { Splide, SplideSlide } from "@splidejs/vue-splide";
// Import Splide styles
import "@splidejs/vue-splide/css";

// Import TipTap
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Placeholder from "@tiptap/extension-placeholder";

// Injection des fonctions de notification
const showNotification = inject("showNotification");
const { intervention, services } = defineProps(["intervention", "services"]);

// Variable pour stocker les IDs des services sélectionnés
const selectedServices = ref(
    intervention.devis?.services?.map((service) => service.id) || []
);

// Variable pour contrôler l'affichage du sélecteur de services
const showServiceSelector = ref(false);

// Ajouter un écouteur d'événement pour fermer le sélecteur de services lors d'un clic en dehors
onMounted(() => {
    document.addEventListener("click", closeServiceSelectorOnClickOutside);
    document.addEventListener("click", handleGlobalClick);
    document.addEventListener("touchstart", handleGlobalClick);

    // Initialiser la détection mobile
    checkMobile();
    window.addEventListener("resize", checkMobile);

    // Écouter les événements de clavier pour la navigation d'images
    const handleKeyDown = (e) => {
        if (showImageModal.value) {
            if (e.key === "Escape") {
                closeImageModal();
            } else if (e.key === "ArrowLeft") {
                navigateImage("prev");
            } else if (e.key === "ArrowRight") {
                navigateImage("next");
            }
        }
    };

    document.addEventListener("keydown", handleKeyDown);

    // Nettoyer les event listeners au démontage
    onUnmounted(() => {
        document.removeEventListener(
            "click",
            closeServiceSelectorOnClickOutside
        );
        document.removeEventListener("click", handleGlobalClick);
        document.removeEventListener("touchstart", handleGlobalClick);
        window.removeEventListener("resize", checkMobile);
        document.removeEventListener("keydown", handleKeyDown);
        document.body.style.overflow = "auto";
    });
});

// Fonction pour gérer les clics globaux et masquer les boutons de suppression
function handleGlobalClick(e) {
    // Si on clique en dehors d'une image, masquer tous les boutons de suppression
    const imageContainers = document.querySelectorAll(
        ".image-container-mobile"
    );
    let clickedOutside = true;

    imageContainers.forEach((container) => {
        if (container.contains(e.target)) {
            clickedOutside = false;
        }
    });

    if (clickedOutside) {
        hideAllDeleteButtons();
    }

    // Vérifier si le clic est en dehors des boutons d'action des notes
    const noteActionButton = e.target.closest(".mobile-action-button-note");
    const noteActionContainer = e.target.closest(
        ".mobile-action-container-note"
    );

    if (!noteActionButton && !noteActionContainer) {
        hideAllMobileActionsNotes();
    }
}

// Supprimer l'écouteur d'événement lors du démontage du composant
onUnmounted(() => {
    document.removeEventListener("click", closeServiceSelectorOnClickOutside);
    document.removeEventListener("click", handleGlobalClick);
    document.removeEventListener("touchstart", handleGlobalClick);
    window.removeEventListener("resize", checkMobile);
    document.body.style.overflow = "auto";
});

// Fonction pour fermer le sélecteur de services quand on clique en dehors
function closeServiceSelectorOnClickOutside(e) {
    if (showServiceSelector.value) {
        // Vérifier si le clic est sur le bouton de service ou sur un badge de service
        const serviceBtn = document.getElementById("service-selector-btn");
        const serviceContainer = document.getElementById(
            "service-selector-container"
        );

        // Ne pas fermer si le clic est sur le bouton ou dans le conteneur de services
        if (
            (serviceBtn && serviceBtn.contains(e.target)) ||
            (serviceContainer && serviceContainer.contains(e.target))
        ) {
            return;
        }

        // Fermer le sélecteur dans tous les autres cas
        showServiceSelector.value = false;
    }
}

// Fonction pour basculer la sélection d'un service
function toggleService(serviceId) {
    const index = selectedServices.value.indexOf(serviceId);

    if (index === -1) {
        // Si le service n'est pas déjà sélectionné, l'ajouter
        selectedServices.value.push(serviceId);
        // Mettre à jour les services de l'intervention
        updateInterventionServices();
    } else {
        // Vérifier s'il reste au moins un service sélectionné
        if (selectedServices.value.length > 1) {
            // Retirer le service
            selectedServices.value.splice(index, 1);
            // Mettre à jour les services de l'intervention
            updateInterventionServices();
        } else {
            // Afficher une notification si l'utilisateur tente de désélectionner le dernier service
            showNotification(
                "Au moins un service doit être sélectionné",
                "error"
            );
        }
    }
}

// Fonction pour mettre à jour les services de l'intervention
function updateInterventionServices() {
    router.put(
        route("interventions.services.update", {
            intervention: intervention.id,
        }),
        { services: selectedServices.value },
        {
            preserveScroll: true,
            preserveState: true,
            // Les notifications sont gérées par le contrôleur via les messages flash
            // et seront affichées automatiquement par le système de notification existant
            onError: (errors) => {
                console.error(errors);
                showNotification(
                    "Erreur lors de la mise à jour des services",
                    "error"
                );
            },
        }
    );
}

// Détails du devis (à remplacer par les vraies données)
const devisDetails = computed(() => {
    return intervention.devis || {};
});

// Référence pour l'input file
const fileInput = ref(null);
const selectedFiles = ref([]);

// Variables pour le modal d'image
const showImageModal = ref(false);
const selectedImage = ref(null);

// Variable pour détecter si on est sur mobile
const isMobile = ref(false);

// Variables pour la pagination mobile avec Splide
const imagesPerPage = 9;

// Computed pour grouper les images par pages pour Splide
const imagePages = computed(() => {
    if (!isMobile.value || !intervention.images)
        return [intervention.images || []];

    const pages = [];
    for (let i = 0; i < intervention.images.length; i += imagesPerPage) {
        pages.push(intervention.images.slice(i, i + imagesPerPage));
    }
    return pages.length > 0 ? pages : [[]];
});

const totalPages = computed(() => {
    if (!intervention.images) return 0;
    return Math.ceil(intervention.images.length / imagesPerPage);
});

// Fonction pour détecter la taille d'écran
const checkMobile = () => {
    isMobile.value = window.innerWidth <= 768;
};

// Initialiser la détection mobile au montage
onMounted(() => {
    checkMobile();
    window.addEventListener("resize", checkMobile);
});

// Nettoyer l'event listener au démontage
onUnmounted(() => {
    window.removeEventListener("resize", checkMobile);
});

// Variable pour la nouvelle note
const newNote = ref("");

// État pour gérer l'affichage des boutons de suppression sur mobile
const showDeleteButtons = ref({});

// État pour gérer l'affichage des actions mobiles pour les notes
const showMobileActionsNotes = ref({});

// Fonction pour gérer l'appui long sur mobile pour les images
let longPressTimer = null;
let touchStartTime = 0;
let hasMoved = false;

// Fonction pour gérer l'appui long sur mobile pour les notes
let longPressTimerNotes = null;
const startLongPressNote = (noteId) => {
    // Annuler le timer précédent s'il existe
    if (longPressTimerNotes) {
        clearTimeout(longPressTimerNotes);
    }

    longPressTimerNotes = setTimeout(() => {
        showMobileActionsNotes.value = { [noteId]: true };
        longPressTimerNotes = null; // Reset du timer après utilisation
    }, 500); // 500ms pour déclencher l'appui long
};

const cancelLongPressNote = () => {
    if (longPressTimerNotes) {
        clearTimeout(longPressTimerNotes);
        longPressTimerNotes = null;
    }
};

const hideMobileActionsNote = (noteId) => {
    showMobileActionsNotes.value = {
        ...showMobileActionsNotes.value,
        [noteId]: false,
    };
};

const hideAllMobileActionsNotes = () => {
    showMobileActionsNotes.value = {};
};

const startLongPress = (imageId, event) => {
    // Ne pas interférer avec le glissement de Splide
    touchStartTime = Date.now();
    hasMoved = false;

    // Annuler le timer précédent s'il existe
    if (longPressTimer) {
        clearTimeout(longPressTimer);
    }

    longPressTimer = setTimeout(() => {
        // Seulement si l'utilisateur n'a pas bougé le doigt
        if (!hasMoved) {
            showDeleteButtons.value = {
                ...showDeleteButtons.value,
                [imageId]: true,
            };
            longPressTimer = null;

            // Auto-masquer le bouton après 3 secondes d'inactivité
            setTimeout(() => {
                hideDeleteButton(imageId);
            }, 3000);
        }
    }, 500); // 500ms pour déclencher l'appui long
};

const cancelLongPress = () => {
    if (longPressTimer) {
        clearTimeout(longPressTimer);
        longPressTimer = null;
    }
};

const handleTouchMove = () => {
    hasMoved = true;
    cancelLongPress();
};

const hideDeleteButton = (imageId) => {
    showDeleteButtons.value = { ...showDeleteButtons.value, [imageId]: false };
};

const hideAllDeleteButtons = () => {
    showDeleteButtons.value = {};
};

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
        Placeholder.configure({
            placeholder: "Écrivez votre note ici...",
        }),
    ],
    onUpdate: ({ editor }) => {
        // Mettre à jour la variable newNote avec le contenu HTML de l'éditeur
        newNote.value = editor.getHTML();
    },
    editorProps: {
        attributes: {
            class: "prose prose-sm focus:outline-none w-full",
            style: "width: 100%; max-width: 100%; display: block;",
        },
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

// Fonction pour gérer la sélection de fichiers et uploader directement
function handleFileSelect(event) {
    selectedFiles.value = Array.from(event.target.files);

    // Upload automatique dès la sélection
    if (selectedFiles.value.length > 0) {
        uploadPhotos();
    }
}

// Fonction pour uploader les photos
function uploadPhotos() {
    if (selectedFiles.value.length === 0) {
        showNotification("Veuillez sélectionner au moins une photo", "error");
        return;
    }

    const formData = new FormData();
    selectedFiles.value.forEach((file) => {
        formData.append("images[]", file);
    });

    // Désactiver le bouton pendant l'upload
    const isUploading = ref(true);

    // Revenir à l'utilisation de router.post mais en mode preserveState pour éviter le rechargement
    router.post(
        route("interventions.upload-images", { intervention: intervention.id }),
        formData,
        {
            onSuccess: (response) => {
                // Les données sont dans response.props.flash.data
                const data = response.props.flash.data || {};

                showNotification("Photos ajoutées avec succès", "success");

                // Recharger uniquement les données de l'intervention
                router.reload({ only: ["intervention"] });

                // Réinitialiser les fichiers sélectionnés
                selectedFiles.value = [];
                isUploading.value = false;

                // Les nouvelles images seront automatiquement affichées dans Splide
                // Pas besoin de gérer manuellement la pagination
            },
            onError: (errors) => {
                console.error(errors);
                showNotification("Erreur lors de l'ajout des photos", "error");
                isUploading.value = false;
            },
            preserveScroll: true, // Conserver la position de défilement
            preserveState: true, // Éviter un rechargement complet
        }
    );
}

// Fonction pour ouvrir le modal d'image
function openImageModal(image) {
    selectedImage.value = image;
    showImageModal.value = true;

    // Bloquer le défilement du corps de la page
    document.body.style.overflow = "hidden";
}

// Fonction pour fermer le modal d'image
function closeImageModal() {
    showImageModal.value = false;
    selectedImage.value = null;

    // Rétablir le défilement du corps de la page
    document.body.style.overflow = "auto";
}

// Navigation entre les images
function navigateImage(direction) {
    if (!intervention.images || intervention.images.length <= 1) return;

    const currentIndex = intervention.images.findIndex(
        (img) => img.id === selectedImage.value?.id
    );
    let newIndex;

    if (direction === "next") {
        newIndex = (currentIndex + 1) % intervention.images.length;
    } else {
        newIndex =
            currentIndex <= 0
                ? intervention.images.length - 1
                : currentIndex - 1;
    }

    selectedImage.value = intervention.images[newIndex];
}

// Gestion des événements de swipe pour mobile
let touchStartX = 0;
let touchEndX = 0;

function handleTouchStart(e) {
    touchStartX = e.changedTouches[0].screenX;
}

function handleTouchEnd(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
}

function handleSwipe() {
    const swipeThreshold = 50;
    const swipeDistance = touchEndX - touchStartX;

    if (Math.abs(swipeDistance) > swipeThreshold) {
        if (swipeDistance > 0) {
            // Swipe vers la droite - image précédente
            navigateImage("prev");
        } else {
            // Swipe vers la gauche - image suivante
            navigateImage("next");
        }
    }
}

// Fonction pour ajouter une note
function addNote() {
    if (!newNote.value.trim()) return;

    router.post(
        route("interventions.notes.store", { intervention: intervention.id }),
        {
            content: newNote.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showNotification("Note ajoutée avec succès", "success");
                // Recharger uniquement les données de l'intervention
                router.reload({ only: ["intervention"] });
                // Réinitialiser le champ de note
                newNote.value = "";
                // Réinitialiser l'éditeur TipTap
                editor.value?.commands.setContent("");
            },
            onError: (errors) => {
                console.error(errors);
                showNotification("Erreur lors de l'ajout de la note", "error");
            },
        }
    );
}

// Fonction pour supprimer une note
function deleteNote(noteId) {
    if (!confirm("Voulez-vous vraiment supprimer cette note ?")) {
        return;
    }

    router.delete(route("notes.destroy", { note: noteId }), {
        preserveScroll: true,
        onSuccess: () => {
            showNotification("Note supprimée avec succès", "success");
            // Recharger uniquement les données de l'intervention
            router.reload({ only: ["intervention"] });
        },
        onError: (errors) => {
            console.error(errors);
            showNotification(
                "Erreur lors de la suppression de la note",
                "error"
            );
        },
    });
}

// Fonction pour supprimer une image
function deleteImage(imageId) {
    if (!confirm("Voulez-vous vraiment supprimer cette image ?")) {
        return;
    }

    router.delete(route("interventions.delete-image", { image: imageId }), {
        preserveScroll: true,
        onSuccess: () => {
            showNotification("Image supprimée avec succès", "success");

            // Mettre à jour localement la liste des images
            if (intervention.images) {
                const index = intervention.images.findIndex(
                    (img) => img.id === imageId
                );
                if (index !== -1) {
                    intervention.images.splice(index, 1);
                }
            }

            // Ajuster la pagination n'est plus nécessaire avec Splide
            // Splide se met à jour automatiquement quand imagePages change
        },
        onError: (errors) => {
            console.error(errors);
            showNotification(
                "Erreur lors de la suppression de l'image",
                "error"
            );
        },
    });
}

// Fonction pour formater la date au format JJ/MM/AA (sans l'heure)
function formatDate(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = String(date.getFullYear()).slice(-2);
    return `${day}/${month}/${year}`;
}

// Fonction pour formater la date avec l'heure au format JJ/MM/AA HH:MM
function formatDateWithTime(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = String(date.getFullYear()).slice(-2);
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");
    return `${day}/${month}/${year} ${hours}:${minutes}`;
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
                showNotification(
                    `Le statut a été mis à jour en "${newStatus}"`,
                    "success"
                );
            },
            onError: (errors) => {
                showNotification(
                    "Une erreur est survenue lors de la mise à jour du statut.",
                    "error"
                );
            },
        }
    );
}

// Fonction pour formater la date pour l'API
function formatDateForApi(date) {
    if (!date) return null;

    // Si c'est déjà une chaîne de caractères au format YYYY-MM-DD, la retourner telle quelle
    if (typeof date === "string" && /^\d{4}-\d{2}-\d{2}$/.test(date))
        return date;

    try {
        // Sinon, formater la date au format YYYY-MM-DD
        const d = new Date(date);

        // Vérifier si la date est valide
        if (isNaN(d.getTime())) {
            console.error("Date invalide:", date);
            return null;
        }

        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, "0");
        const day = String(d.getDate()).padStart(2, "0");
        return `${year}-${month}-${day}`;
    } catch (error) {
        console.error("Erreur lors du formatage de la date:", error);
        return null;
    }
}

// Variables pour le calendrier personnalisé
const showCustomCalendar = ref(false);
const currentDate = ref(new Date());
const selectedDate = ref(
    intervention.date ? new Date(intervention.date) : null
);
const tempSelectedDate = ref(null);

// Calcul des propriétés du calendrier
const currentYear = computed(() => currentDate.value.getFullYear());
const currentMonth = computed(() => currentDate.value.getMonth());
const daysInMonth = computed(() => {
    const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);
    return lastDay.getDate();
});

// Calcul du premier jour du mois (0 = Dimanche, 1 = Lundi, etc.)
const firstDayOfMonth = computed(() => {
    const firstDay = new Date(
        currentYear.value,
        currentMonth.value,
        1
    ).getDay();
    // Convertir de 0-6 (Dim-Sam) à 1-7 (Lun-Dim)
    return firstDay === 0 ? 6 : firstDay - 1;
});

// Noms des mois en français
const monthNames = [
    "Janvier",
    "Février",
    "Mars",
    "Avril",
    "Mai",
    "Juin",
    "Juillet",
    "Août",
    "Septembre",
    "Octobre",
    "Novembre",
    "Décembre",
];

const currentMonthName = computed(() => monthNames[currentMonth.value]);

// Fonction pour ouvrir le calendrier personnalisé
function openCustomCalendar() {
    // Initialiser la date actuelle avec la date de l'intervention
    if (intervention.date) {
        currentDate.value = new Date(intervention.date);
        selectedDate.value = new Date(intervention.date);
    } else {
        currentDate.value = new Date();
        selectedDate.value = null;
    }

    tempSelectedDate.value = selectedDate.value;
    showCustomCalendar.value = true;

    // Ajouter un écouteur de clic pour fermer le calendrier quand on clique en dehors
    setTimeout(() => {
        document.addEventListener("click", closeDatePickerOnClickOutside);
    }, 0);
}

// Fonction pour fermer le datepicker quand on clique en dehors
function closeDatePickerOnClickOutside(e) {
    const datepicker = document.querySelector(
        ".date-picker-button"
    )?.parentNode;
    if (datepicker && !datepicker.contains(e.target)) {
        showCustomCalendar.value = false;
        document.removeEventListener("click", closeDatePickerOnClickOutside);
    }
}

// Fonction pour passer au mois précédent
function previousMonth() {
    currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1);
}

// Fonction pour passer au mois suivant
function nextMonth() {
    currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1);
}

// Fonction pour sélectionner une date
function selectDate(day) {
    tempSelectedDate.value = new Date(
        currentYear.value,
        currentMonth.value,
        day
    );
}

// Fonction pour vérifier si une date est sélectionnée
function isSelectedDate(day) {
    if (!tempSelectedDate.value) return false;

    const date = new Date(currentYear.value, currentMonth.value, day);
    return (
        date.getDate() === tempSelectedDate.value.getDate() &&
        date.getMonth() === tempSelectedDate.value.getMonth() &&
        date.getFullYear() === tempSelectedDate.value.getFullYear()
    );
}

// Fonction pour annuler la sélection de date
function cancelDateSelection() {
    showCustomCalendar.value = false;
    document.removeEventListener("click", closeDatePickerOnClickOutside);
}

// Fonction pour confirmer la sélection de date
function confirmDateSelection() {
    if (tempSelectedDate.value) {
        selectedDate.value = tempSelectedDate.value;
        updateInterventionDate(selectedDate.value);
    }
    showCustomCalendar.value = false;
    document.removeEventListener("click", closeDatePickerOnClickOutside);
}

// Fonction pour mettre à jour la date d'intervention
function updateInterventionDate(date) {
    // Vérifier si la date est valide
    if (!date || !(date instanceof Date) || isNaN(date.getTime())) {
        showNotification("Date invalide", "error");
        return;
    }

    // Formater la date au format YYYY-MM-DD
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const formattedDate = `${year}-${month}-${day}`;

    console.log("Date formatée envoyée à l'API:", formattedDate);

    // Sauvegarder l'ancienne date pour pouvoir revenir en arrière si nécessaire
    const oldDate = intervention.date;

    // Mettre à jour la date localement sans attendre la réponse du serveur
    intervention.date = formattedDate;

    // Utiliser la route spécifique pour mettre à jour uniquement la date
    router.put(
        route("interventions.updateDate", { intervention: intervention.id }),
        {
            date: formattedDate,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                // Vérifier si la réponse contient un message de succès
                if (page.props.flash && page.props.flash.success) {
                    showNotification(page.props.flash.success, "success");
                } else {
                    showNotification("Date mise à jour", "success");
                }
            },
            onError: (errors) => {
                console.error("Erreurs:", errors);
                showNotification(
                    "Erreur lors de la mise à jour de la date",
                    "error"
                );

                // Restaurer l'ancienne date en cas d'erreur
                intervention.date = oldDate;
            },
        }
    );
}

function compiledMarkdown(text) {
    if (!text) return "";

    // Configuration de marked pour un meilleur rendu
    marked.setOptions({
        gfm: true, // GitHub Flavored Markdown
        breaks: true, // Convertir les retours à la ligne en <br>
        sanitize: false, // Désactivé car marked gère cela automatiquement dans les versions récentes
        smartLists: true, // Listes plus intelligentes
        smartypants: true, // Guillemets typographiques et tirets
        xhtml: false, // Ne pas fermer les balises vides comme XHTML
    });

    return marked(text);
}
</script>

<template>
    <AdminLayout>
        <!-- En-tête noir avec les informations principales -->
        <header
            class="bg-[#2D2D2D] text-white md:py-28 py-16 md:px-16 px-5 md:mt-[72px] mt-[64px]"
        >
            <div
                class="container flex md:flex-row flex-col items-start md:gap-20 gap-12 self-stretch"
            >
                <!-- Côté gauche: Titre et statut -->
                <div class="flex flex-col items-start gap-6 flex-1">
                    <h1
                        class="md:text-[3.5rem] text-[2.5rem] font-medium font-poppins leading-[120%] md:tracking-[-0.56px] tracking-[-0.4px]"
                    >
                        Intervention #{{ intervention.id }}
                    </h1>
                    <div
                        class="flex items-start content-start gap-2 self-stretch flex-wrap"
                    >
                        <!-- Bouton principal pour les services -->
                        <div class="relative service-group">
                            <button
                                id="service-selector-btn"
                                class="flex py-1 px-[10px] items-start rounded-[4px] border border-white/5 bg-white/5 text-[#FF8C42] font-inter font-semibold text-sm cursor-pointer"
                                @click.stop="
                                    showServiceSelector = !showServiceSelector
                                "
                            >
                                {{
                                    intervention.devis?.services &&
                                    intervention.devis.services.length > 0
                                        ? intervention.devis.services[0].title
                                        : "Aucun service"
                                }}
                                <span
                                    v-if="
                                        intervention.devis?.services &&
                                        intervention.devis.services.length > 1
                                    "
                                    class="ml-1"
                                >
                                    +{{
                                        intervention.devis.services.length - 1
                                    }}
                                </span>
                            </button>
                            <!-- Popup qui apparaît au survol -->
                            <div
                                v-if="
                                    intervention.devis?.services &&
                                    intervention.devis.services.length > 1
                                "
                                class="absolute z-10 bottom-full right-0 mb-2 bg-[#1A1A1A] border border-white/10 rounded-md p-2 shadow-lg opacity-0 invisible service-popup transition-all duration-200 min-w-[150px]"
                            >
                                <div
                                    v-for="service in intervention.devis
                                        .services"
                                    :key="service.id"
                                    class="py-1 px-2 text-[#FF8C42] font-inter font-semibold text-sm whitespace-nowrap"
                                >
                                    {{ service.title }}
                                </div>
                            </div>
                        </div>
                        <!-- Liste des services qui apparaît sous les badges principaux -->
                        <div
                            v-if="showServiceSelector"
                            id="service-selector-container"
                            class="w-full mt-2 flex flex-wrap gap-2"
                            @click.stop
                        >
                            <button
                                v-for="service in services"
                                :key="service.id"
                                @click="toggleService(service.id)"
                                :class="{
                                    'bg-[#FF8C42] text-white':
                                        selectedServices.includes(service.id),
                                }"
                                class="px-3 py-1 text-sm rounded-md border border-white/20 text-white hover:bg-[#FF8C42] hover:text-white transition-colors duration-300"
                            >
                                {{ service.title }}
                            </button>
                        </div>

                        <!-- Bouton pour changer le statut -->
                        <button
                            class="flex py-1 px-[10px] items-start rounded border border-white/5 font-inter font-semibold text-sm transition-colors duration-300"
                            :style="{
                                color: statusColors[intervention.status],
                            }"
                            @mouseover="
                                $event.target.style.backgroundColor =
                                    statusColors[intervention.status];
                                $event.target.style.color =
                                    intervention.status === 'planifiée'
                                        ? '#2D2D2D'
                                        : 'white';
                            "
                            @mouseout="
                                $event.target.style.backgroundColor =
                                    'transparent';
                                $event.target.style.color =
                                    statusColors[intervention.status];
                            "
                            @click="updateStatus(intervention)"
                        >
                            {{ intervention.status }}
                        </button>

                        <!-- Badge pour sélectionner la date d'intervention -->
                        <div class="relative service-group">
                            <button
                                class="flex py-1 px-[10px] items-start rounded border border-white/5 bg-white/5 text-white font-inter font-semibold text-sm transition-colors duration-300 cursor-pointer date-picker-button"
                                @click.stop="openCustomCalendar"
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
                                    class="mr-1"
                                >
                                    <rect
                                        x="3"
                                        y="4"
                                        width="18"
                                        height="18"
                                        rx="2"
                                        ry="2"
                                    ></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                {{
                                    intervention.date
                                        ? formatDate(intervention.date)
                                        : "Planifier"
                                }}
                            </button>

                            <!-- Calendrier simple -->
                            <div
                                v-if="showCustomCalendar"
                                class="absolute top-full left-0 z-50 mt-1 bg-[#2D2D2D] border border-white/10 rounded-md p-4 shadow-lg"
                            >
                                <!-- En-tête du calendrier avec mois et année -->
                                <div
                                    class="flex justify-between items-center mb-2"
                                >
                                    <button
                                        @click="previousMonth"
                                        class="text-white hover:text-[#FF8C42]"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <polyline
                                                points="15 18 9 12 15 6"
                                            ></polyline>
                                        </svg>
                                    </button>
                                    <div class="text-white font-medium">
                                        {{ currentMonthName }} {{ currentYear }}
                                    </div>
                                    <button
                                        @click="nextMonth"
                                        class="text-white hover:text-[#FF8C42]"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <polyline
                                                points="9 18 15 12 9 6"
                                            ></polyline>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Jours de la semaine -->
                                <div class="grid grid-cols-7 gap-1 mb-1">
                                    <div
                                        v-for="day in [
                                            'Lu',
                                            'Ma',
                                            'Me',
                                            'Je',
                                            'Ve',
                                            'Sa',
                                            'Di',
                                        ]"
                                        :key="day"
                                        class="text-center text-white/70 text-xs"
                                    >
                                        {{ day }}
                                    </div>
                                </div>

                                <!-- Jours du mois -->
                                <div class="grid grid-cols-7 gap-1">
                                    <!-- Espaces vides pour l'alignement -->
                                    <div
                                        v-for="n in firstDayOfMonth"
                                        :key="'empty-' + n"
                                        class="h-7"
                                    ></div>

                                    <!-- Jours du mois -->
                                    <button
                                        v-for="day in daysInMonth"
                                        :key="day"
                                        @click="selectDate(day)"
                                        :class="[
                                            'h-7 w-7 rounded-full flex items-center justify-center text-sm transition-colors',
                                            isSelectedDate(day)
                                                ? 'bg-[#FF8C42] text-white'
                                                : 'text-white hover:bg-white/10',
                                        ]"
                                    >
                                        {{ day }}
                                    </button>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="flex justify-between mt-4">
                                    <button
                                        @click="cancelDateSelection"
                                        class="px-3 py-1 text-sm text-white/70 hover:text-white bg-transparent border border-white/10 rounded transition-colors"
                                    >
                                        Annuler
                                    </button>
                                    <button
                                        @click="confirmDateSelection"
                                        class="px-3 py-1 text-sm text-white bg-[#FF8C42] hover:bg-[#e67e3a] rounded transition-colors"
                                    >
                                        Confirmer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Côté droit -->
                <div
                    class="flex md:max-w-[464px] w-full flex-col items-start md:gap-8 gap-6"
                >
                    <div class="flex w-full md:gap-8 gap-6">
                        <div class="flex flex-col gap-2 items-start w-1/2">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M12 12C10.9 12 9.95833 11.6083 9.175 10.825C8.39167 10.0417 8 9.1 8 8C8 6.9 8.39167 5.95833 9.175 5.175C9.95833 4.39167 10.9 4 12 4C13.1 4 14.0417 4.39167 14.825 5.175C15.6083 5.95833 16 6.9 16 8C16 9.1 15.6083 10.0417 14.825 10.825C14.0417 11.6083 13.1 12 12 12ZM4 20V17.2C4 16.6333 4.146 16.1127 4.438 15.638C4.73 15.1633 5.11733 14.8007 5.6 14.55C6.63333 14.0333 7.68333 13.646 8.75 13.388C9.81667 13.13 10.9 13.0007 12 13C13.1 12.9993 14.1833 13.1287 15.25 13.388C16.3167 13.6473 17.3667 14.0347 18.4 14.55C18.8833 14.8 19.271 15.1627 19.563 15.638C19.855 16.1133 20.0007 16.634 20 17.2V20H4ZM6 18H18V17.2C18 17.0167 17.9543 16.85 17.863 16.7C17.7717 16.55 17.6507 16.4333 17.5 16.35C16.6 15.9 15.6917 15.5627 14.775 15.338C13.8583 15.1133 12.9333 15.0007 12 15C11.0667 14.9993 10.1417 15.112 9.225 15.338C8.30833 15.564 7.4 15.9013 6.5 16.35C6.35 16.4333 6.229 16.55 6.137 16.7C6.045 16.85 5.99933 17.0167 6 17.2V18ZM12 10C12.55 10 13.021 9.80433 13.413 9.413C13.805 9.02167 14.0007 8.55067 14 8C13.9993 7.44933 13.8037 6.97867 13.413 6.588C13.0223 6.19733 12.5513 6.00133 12 6C11.4487 5.99867 10.978 6.19467 10.588 6.588C10.198 6.98133 10.002 7.452 10 8C9.998 8.548 10.194 9.019 10.588 9.413C10.982 9.807 11.4527 10.0027 12 10ZM18.267 19.8518C18.417 20.0018 18.607 20.0718 18.797 20.0718C18.987 20.0718 19.177 20.0018 19.327 19.8518C19.617 19.5618 19.617 19.0818 19.327 18.7918L18.547 18.0118V16.3218C18.547 15.9118 18.207 15.5718 17.797 15.5718C17.387 15.5718 17.047 15.9118 17.047 16.3218V18.3218C17.047 18.5218 17.127 18.7118 17.267 18.8518L18.267 19.8518Z"
                                    fill="white"
                                />
                            </svg>
                            <span
                                class="font-inter md:text-base text-sm font-normal"
                                >{{ intervention.client.name }}
                                {{ intervention.client.lastname }}</span
                            >
                        </div>
                        <div class="flex flex-col gap-2 items-start w-1/2">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M17.297 6.57178C16.887 6.57178 16.547 6.23178 16.547 5.82178V4.59778C15.773 4.57178 14.864 4.57178 13.797 4.57178H11.797C10.729 4.57178 9.821 4.57178 9.047 4.59778V5.82178C9.047 6.23178 8.707 6.57178 8.297 6.57178C7.887 6.57178 7.547 6.23178 7.547 5.82178V4.70578C6.602 4.82878 5.953 5.06578 5.497 5.52178C4.895 6.12378 4.675 7.06178 4.594 8.57178H21C20.919 7.06178 20.698 6.12378 20.097 5.52178C19.641 5.06578 18.992 4.82878 18.047 4.70578V5.82178C18.047 6.23178 17.707 6.57178 17.297 6.57178ZM21.041 10.0718C21.0463 10.6004 21.0483 11.1838 21.047 11.8218V12.8218C21.047 13.2318 21.387 13.5718 21.797 13.5718C22.207 13.5718 22.547 13.2318 22.547 12.8218V11.8218C22.547 7.84178 22.547 5.85178 21.157 4.46178C20.385 3.68978 19.427 3.34678 18.047 3.19378V2.32178C18.047 1.91178 17.707 1.57178 17.297 1.57178C16.887 1.57178 16.547 1.91178 16.547 2.32178V3.09678C15.751 3.07178 14.842 3.07178 13.797 3.07178H11.797C10.751 3.07178 9.843 3.07178 9.047 3.09678V2.32178C9.047 1.91178 8.707 1.57178 8.297 1.57178C7.887 1.57178 7.547 1.91178 7.547 2.32178V3.19378C6.167 3.34678 5.209 3.68978 4.437 4.46178C3.047 5.85178 3.047 7.85178 3.047 11.8218V13.8218C3.047 17.8018 3.047 19.7918 4.437 21.1818C5.827 22.5718 7.817 22.5718 11.797 22.5718C12.207 22.5718 12.547 22.2318 12.547 21.8218C12.547 21.4118 12.207 21.0718 11.797 21.0718C8.237 21.0718 6.447 21.0718 5.497 20.1218C4.547 19.1718 4.547 17.3818 4.547 13.8218V11.8218C4.54633 11.1844 4.548 10.6011 4.552 10.0718H21.041ZM17.797 23.0718C15.177 23.0718 13.047 20.9418 13.047 18.3218C13.047 15.7018 15.177 13.5718 17.797 13.5718C20.417 13.5718 22.547 15.7018 22.547 18.3218C22.547 20.9418 20.417 23.0718 17.797 23.0718ZM17.797 15.0718C16.007 15.0718 14.547 16.5318 14.547 18.3218C14.547 20.1118 16.007 21.5718 17.797 21.5718C19.587 21.5718 21.047 20.1118 21.047 18.3218C21.047 16.5318 19.587 15.0718 17.797 15.0718ZM18.267 19.8518C18.417 20.0018 18.607 20.0718 18.797 20.0718C18.987 20.0718 19.177 20.0018 19.327 19.8518C19.617 19.5618 19.617 19.0818 19.327 18.7918L18.547 18.0118V16.3218C18.547 15.9118 18.207 15.5718 17.797 15.5718C17.387 15.5718 17.047 15.9118 17.047 16.3218V18.3218C17.047 18.5218 17.127 18.7118 17.267 18.8518L18.267 19.8518Z"
                                    fill="white"
                                />
                            </svg>
                            <span
                                class="font-inter md:text-base text-sm font-normal"
                            >
                                <span
                                    :class="{
                                        'text-gray-400':
                                            !intervention.devis.requested_date,
                                    }"
                                    >{{
                                        intervention.devis.requested_date
                                            ? formatDate(
                                                  intervention.devis
                                                      .requested_date
                                              )
                                            : "Non renseigné"
                                    }}</span
                                >
                                -
                                <span
                                    :class="{
                                        'text-gray-400':
                                            !intervention.devis.end_date,
                                    }"
                                    >{{
                                        intervention.devis.end_date
                                            ? formatDate(
                                                  intervention.devis.end_date
                                              )
                                            : "Non renseigné"
                                    }}</span
                                ></span
                            >
                        </div>
                    </div>
                    <div class="flex items-start md:gap-8 gap-6 w-full">
                        <div
                            class="font-inter w-1/2 flex-col items-start md:text-base text-sm font-normal flex gap-2"
                        >
                            <a
                                :href="
                                    intervention.client.phone
                                        ? `tel:${intervention.client.phone}`
                                        : '#'
                                "
                                :class="
                                    intervention.client.phone
                                        ? 'text-[#FF8C42] hover:text-white transition-colors cursor-pointer flex gap-2 items-start flex-col'
                                        : 'text-gray-400 flex gap-2 items-start flex-col'
                                "
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    fill="currentColor"
                                    class="bi bi-telephone"
                                    viewBox="0 0 22 22"
                                >
                                    <path
                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"
                                    />
                                </svg>
                                {{
                                    intervention.client.phone
                                        ? intervention.client.phone
                                        : "Non renseigné"
                                }}
                            </a>
                        </div>
                        <div
                            class="font-inter w-1/2 flex-col items-start md:text-base text-sm font-normal flex gap-2"
                        >
                            <a
                                :href="
                                    intervention.client.email
                                        ? `mailto:${intervention.client.email}`
                                        : '#'
                                "
                                :class="
                                    intervention.client.email
                                        ? 'text-white hover:text-[#FF8C42] transition-colors cursor-pointer flex gap-2 items-start flex-col'
                                        : 'text-gray-400 flex gap-2 items-start flex-col '
                                "
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    fill="currentColor"
                                    class="bi bi-envelope"
                                    viewBox="0 0 22 22"
                                >
                                    <path
                                        d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"
                                    />
                                </svg>
                                {{
                                    intervention.client.email
                                        ? intervention.client.email
                                        : "Non renseigné"
                                }}
                            </a>
                        </div>
                    </div>
                    <div class="flex items-start md:gap-8 gap-6 w-full">
                        <div
                            class="font-inter flex-col items-start md:text-base text-sm font-normal flex gap-2 w-full"
                        >
                            <a
                                :href="
                                    intervention.client.adress
                                        ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
                                              intervention.client.adress
                                          )}`
                                        : '#'
                                "
                                :target="
                                    intervention.client.adress ? '_blank' : ''
                                "
                                :rel="
                                    intervention.client.adress ? 'noopener' : ''
                                "
                                :class="
                                    intervention.client.adress
                                        ? 'text-blue-300 hover:text-[#FF8C42] transition-colors cursor-pointer flex gap-2 items-start flex-col'
                                        : 'text-gray-400 flex gap-2 items-start flex-col'
                                "
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    fill="currentColor"
                                    class="bi bi-geo-alt"
                                    viewBox="0 0 22 22"
                                >
                                    <path
                                        d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A32 32 0 0 1 8 14.58a32 32 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10"
                                    />
                                    <path
                                        d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4m0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6"
                                    />
                                </svg>
                                {{
                                    intervention.client.adress
                                        ? intervention.client.adress
                                        : "Non renseigné"
                                }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenu principal -->
        <main class="w-full max-w-full">
            <!-- Détails de l'intervention -->
            <section
                class="flex content-center items-start md:gap-20 gap-12 md:py-28 py-16 md:px-16 px-5 flex-wrap"
            >
                <div class="relative flex flex-col gap-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-[2px] bg-gradient-to-r from-transparent via-[#FF8C42] to-[#FF8C42]"
                        ></div>
                        <span
                            class="font-inter text-sm font-semibold uppercase tracking-[2px] text-[#FF8C42]"
                        >
                            Suivi
                        </span>
                    </div>

                    <h3
                        class="text-[#0D0703] font-poppins md:text-[2.5rem] text-[2rem] font-medium leading-[120%] tracking-[-.4px] mb-3"
                    >
                        Détails de l'<span class="relative inline-block">
                            intervention
                            <div
                                class="absolute bottom-1 left-0 w-full h-3 bg-[#FF8C42] bg-opacity-20 -z-10"
                            ></div>
                        </span>
                    </h3>
                </div>
                <div
                    class="flex md:w-[672px] md:h-[304px] h-[400px] md:p-8 p-6 flex-col items-start gap-6 rounded-lg border border-white/20 bg-[#F2F2F2] overflow-y-auto hide-scrollbar"
                >
                    <div
                        class="text-[#0D0703] font-inter md:text-xl text-lg font-normal markdown-content"
                        v-html="
                            compiledMarkdown(
                                intervention.devis.description || ''
                            )
                        "
                    ></div>
                </div>
            </section>

            <!-- Photos -->
            <section
                class="flex md:py-28 py-16 md:px-16 px-5 flex-col md:gap-20 gap-12 content-center items-start bg-[#2D2D2D] w-screen"
            >
                <div
                    class="flex md:flex-row flex-col justify-between self-stretch"
                >
                    <div
                        class="flex flex-col md:max-w-[768px] items-start md:gap-4 gap-3 text-white"
                    >
                        <div class="relative w-full">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-[2px] bg-gradient-to-r from-transparent via-[#FF8C42] to-[#FF8C42]"
                                ></div>
                                <span
                                    class="font-inter text-sm font-semibold uppercase tracking-[2px] text-[#FF8C42]"
                                >
                                    Documentation
                                </span>
                            </div>
                        </div>
                        <div class="relative flex flex-col md:gap-6 gap-0">
                            <h3
                                class="font-poppins md:text-5xl text-4xl md:text-center font-medium md:leading-[57.6px] leading-[120%] md:tracking-[-0.48px] tracking-[-0.36px] mb-6 relative z-10"
                            >
                                Mes
                                <span class="relative inline-block">
                                    photos
                                    <div
                                        class="absolute bottom-1 left-0 w-full h-3 bg-[#FF8C42] bg-opacity-50 -z-10"
                                    ></div>
                                </span>
                            </h3>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <input
                            ref="fileInput"
                            type="file"
                            multiple
                            accept="image/*"
                            @change="handleFileSelect"
                            class="hidden"
                        />
                        <PrimaryButton @click="fileInput.click()" class="h-max">
                            Ajouter
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Galerie de photos avec affichage responsive -->
                <div
                    v-if="intervention.images && intervention.images.length > 0"
                    :class="isMobile ? 'w-full' : 'w-screen -mx-16 relative'"
                >
                    <!-- Affichage mobile : Splide avec pages de 9 images -->
                    <div v-if="isMobile" class="pb-10">
                        <Splide
                            :options="{
                                type: 'slide',
                                perPage: 1,
                                perMove: 1,
                                gap: '2rem',
                                pagination: true,
                                arrows: false,
                                drag: true,
                                snap: true,
                                speed: 400,
                                easing: 'cubic-bezier(.25,.46,.45,.94)',
                                classes: {
                                    pagination:
                                        'splide__pagination splide__pagination--mobile',
                                    page: 'splide__pagination__page splide__pagination__page--mobile',
                                },
                            }"
                        >
                            <SplideSlide
                                v-for="(page, pageIndex) in imagePages"
                                :key="`page-${pageIndex}`"
                            >
                                <!-- Grille de 9 images maximum par slide (3x3) -->
                                <div
                                    class="grid grid-cols-3 gap-2 min-h-[300px]"
                                >
                                    <div
                                        v-for="image in page"
                                        :key="image.id"
                                        class="relative aspect-square group image-container-mobile"
                                        @touchstart.passive="
                                            startLongPress(image.id, $event)
                                        "
                                        @touchmove.passive="handleTouchMove"
                                        @touchend.passive="cancelLongPress"
                                        @touchcancel.passive="cancelLongPress"
                                        @click="
                                            !showDeleteButtons[image.id] &&
                                                openImageModal(image)
                                        "
                                    >
                                        <!-- Bouton de suppression pour mobile - affiché seulement lors d'appui long -->
                                        <div
                                            v-if="showDeleteButtons[image.id]"
                                            class="absolute top-1 right-1 z-10"
                                        >
                                            <button
                                                @click.prevent.stop="
                                                    deleteImage(image.id)
                                                "
                                                title="Supprimer"
                                                class="bg-white bg-opacity-80 rounded-full p-1 shadow-md transition-colors"
                                            >
                                                <img
                                                    src="/assets/icons/clients/delete-icon.svg"
                                                    alt="Delete"
                                                    class="w-4 h-4"
                                                />
                                            </button>
                                        </div>

                                        <img
                                            :src="`/storage/${image.url_image}`"
                                            :alt="`Intervention ${intervention.id}`"
                                            class="w-full h-full object-cover rounded-lg cursor-pointer transition-transform"
                                            :class="{
                                                'active:scale-95':
                                                    !showDeleteButtons[
                                                        image.id
                                                    ],
                                            }"
                                        />

                                        <!-- Overlay pour indiquer que c'est cliquable - seulement si pas en mode suppression -->
                                        <div
                                            v-if="!showDeleteButtons[image.id]"
                                            class="absolute inset-0 bg-black bg-opacity-0 active:bg-opacity-20 transition-all rounded-lg flex items-center justify-center"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-6 w-6 text-white opacity-0 group-active:opacity-100 transition-opacity"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"
                                                />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Cases vides pour compléter la grille si moins de 9 images -->
                                    <div
                                        v-for="n in Math.max(
                                            0,
                                            9 - page.length
                                        )"
                                        :key="`empty-${pageIndex}-${n}`"
                                        class="aspect-square opacity-0"
                                    ></div>
                                </div>
                            </SplideSlide>
                        </Splide>
                    </div>

                    <!-- Affichage desktop : Splide horizontal -->
                    <Splide
                        v-else
                        :options="{
                            perPage: 1,
                            perMove: 1,
                            gap: '1rem',
                            pagination: false,
                            arrows: false,
                            drag: 'free',
                            snap: false,
                            fixedWidth: '300px',
                            height: '200px',
                            trimSpace: false,
                            focus: 0,
                            omitEnd: true,
                            padding: { left: '4rem', right: '4rem' },
                        }"
                    >
                        <SplideSlide
                            v-for="image in intervention.images"
                            :key="image.id"
                            class="relative group hover-card"
                        >
                            <!-- Bouton de suppression qui apparaît au survol -->
                            <div
                                class="absolute top-[0.5rem] right-[0.5rem] opacity-0 delete-button transition-opacity z-10"
                            >
                                <button
                                    @click.prevent="deleteImage(image.id)"
                                    title="Supprimer"
                                    class="rounded-full p-1 shadow-md transition-colors"
                                >
                                    <img
                                        src="/assets/icons/clients/delete-icon.svg"
                                        alt="Delete"
                                        class="w-5 h-5 hover:scale-110 transition-transform"
                                    />
                                </button>
                            </div>

                            <img
                                :src="`/storage/${image.url_image}`"
                                :alt="`Intervention ${intervention.id}`"
                                class="w-full h-full object-cover rounded-md"
                            />
                            <div
                                class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
                            >
                                <button
                                    @click.prevent="openImageModal(image)"
                                    class="text-white hover:text-[#FF8C42] cursor-pointer"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                    >
                                        <path
                                            d="M15 3l2.3 2.3-2.89 2.87 1.42 1.42L18.7 6.7 21 9V3h-6zM3 9l2.3-2.3 2.87 2.89 1.42-1.42L6.7 5.3 9 3H3v6zm6 12l-2.3-2.3 2.89-2.87-1.42-1.42L5.3 17.3 3 15v6h6zm12-6l-2.3 2.3-2.87-2.89-1.42 1.42 2.89 2.87L15 21h6v-6z"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </SplideSlide>
                    </Splide>
                </div>

                <!-- Message si pas de photos -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center w-full"
                >
                    <p
                        class="font-inter text-white text-center text-lg font-light"
                    >
                        Aucune photo disponible
                    </p>
                    <p
                        class="font-inter text-xs text-[#FF8C42] mt-3 text-center font-medium tracking-wide"
                    >
                        Cliquez sur Ajouter pour documenter l'intervention
                    </p>
                </div>
            </section>

            <!-- Notes -->
            <section
                class="w-full md:py-28 py-16 md:px-16 px-5 flex flex-col md:gap-20 gap-12"
            >
                <div
                    class="w-full flex-col items-start md:gap-4 gap-3 text-[#0D0703]"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-[2px] bg-gradient-to-r from-transparent via-[#FF8C42] to-[#FF8C42]"
                        ></div>
                        <span
                            class="font-inter text-sm font-semibold uppercase tracking-[2px] text-[#FF8C42]"
                        >
                            Gestion
                        </span>
                    </div>

                    <div class="relative flex flex-col gap-6">
                        <h3
                            class="font-poppins md:text-5xl text-4xl text-left font-medium md:leading-[57.6px] leading-[43.2px] tracking-[-0.36px] md:tracking-[-0.48px] md:mb-6 mb-4"
                        >
                            Mes
                            <span class="relative inline-block">
                                notes
                                <div
                                    class="absolute bottom-1 left-0 w-full h-3 bg-[#FF8C42] bg-opacity-20 -z-10"
                                ></div>
                            </span>
                        </h3>
                    </div>
                </div>
                <div style="width: 100%; max-width: 100%; display: block">
                    <!-- Barre d'outils TipTap -->
                    <div
                        style="
                            width: 100%;
                            max-width: 100%;
                            display: block;
                            position: relative;
                        "
                    >
                        <div
                            class="toolbar bg-[#F2F2F2] border border-white/20 border-b-0 rounded-t-lg pt-2 pl-6 flex gap-2 relative"
                            style="width: 100%; max-width: 100%; display: block"
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
                                    stroke="currentColor"
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
                                    stroke="currentColor"
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
                                    stroke="currentColor"
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
                                    stroke="currentColor"
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

                        <!-- Conteneur de l'éditeur TipTap -->
                        <div
                            style="
                                width: 100%;
                                max-width: 100%;
                                display: block;
                                height: 100%;
                                cursor: text;
                            "
                            @click="editor?.commands.focus()"
                        >
                            <EditorContent
                                :editor="editor"
                                class="px-6 pb-6 pt-3 rounded-b-lg border border-white/20 bg-[#F2F2F2] font-inter text-base font-normal min-h-[120px] overflow-auto"
                                style="
                                    width: 100%;
                                    max-width: 100%;
                                    display: block;
                                    box-sizing: border-box;
                                    height: 100%;
                                "
                            />
                        </div>
                        <div class="absolute bottom-2 right-4">
                            <button
                                @click="addNote"
                                :disabled="!newNote.trim()"
                                class="hover:scale-110 transition-transform duration-200"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="33"
                                    height="33"
                                    viewBox="0 0 33 33"
                                    fill="none"
                                >
                                    <path
                                        d="M6.85653 26.644C6.41209 26.8218 5.98987 26.7827 5.58987 26.5267C5.18987 26.2707 4.98987 25.8987 4.98987 25.4107V19.4107L15.6565 16.744L4.98987 14.0773V8.07733C4.98987 7.58844 5.18987 7.21644 5.58987 6.96133C5.98987 6.70622 6.41209 6.66711 6.85653 6.844L27.3899 15.5107C27.9454 15.7551 28.2232 16.1662 28.2232 16.744C28.2232 17.3218 27.9454 17.7329 27.3899 17.9773L6.85653 26.644Z"
                                        fill="#FF8C42"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Affichage des notes existantes -->
                    <div
                        v-if="
                            intervention.notes && intervention.notes.length > 0
                        "
                        class="w-full flex flex-col md:gap-8 gap-6 mt-8"
                    >
                        <div
                            v-for="note in intervention.notes"
                            :key="note.id"
                            class="flex p-6 flex-col items-start gap-6 rounded-lg border border-white/20 bg-[#F2F2F2] font-inter text-base font-normal text-[#0D0703] relative group w-full cursor-pointer md:cursor-default transition-all duration-300 active:scale-[0.98] active:bg-gray-200"
                            style="width: 100%; max-width: 100%"
                            @touchstart="startLongPressNote(note.id)"
                            @touchend="cancelLongPressNote"
                            @touchcancel="cancelLongPressNote"
                        >
                            <!-- Actions d'édition/suppression qui apparaissent au survol sur desktop et après appui long sur mobile -->
                            <div
                                class="absolute top-[-0.5rem] right-[-0.5rem] transition-opacity flex gap-2 z-20 mobile-action-container-note"
                                :class="
                                    showMobileActionsNotes[note.id]
                                        ? 'opacity-100 md:opacity-0 md:group-hover:opacity-100'
                                        : 'opacity-0 md:group-hover:opacity-100'
                                "
                                @click.stop
                                @touchstart.stop
                            >
                                <button
                                    @click="deleteNote(note.id)"
                                    title="Supprimer"
                                    class="p-2 rounded-xl bg-white/80 hover:bg-white border border-gray-300 transition-all duration-200 active:scale-95 mobile-action-button-note"
                                >
                                    <img
                                        src="/assets/icons/clients/delete-icon.svg"
                                        alt="Delete"
                                        class="w-5 h-5 hover:scale-110 transition-transform"
                                    />
                                </button>
                            </div>

                            <!-- Overlay de flou qui apparaît uniquement sur mobile -->
                            <div
                                v-if="showMobileActionsNotes[note.id]"
                                class="absolute inset-0 z-10 bg-black/20 backdrop-blur-sm md:hidden rounded-lg pointer-events-none"
                            ></div>

                            <div
                                class="text-gray-700 w-full overflow-hidden break-words prose prose-sm"
                                v-html="compiledMarkdown(note.content)"
                                style="
                                    word-wrap: break-word;
                                    overflow-wrap: break-word;
                                    white-space: normal;
                                    width: 100%;
                                    max-width: 100%;
                                "
                            ></div>
                            <div class="flex items-center gap-2">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="17"
                                    viewBox="0 0 16 17"
                                    fill="none"
                                >
                                    <path
                                        d="M7.99998 1.83301C4.32398 1.83301 1.33331 4.82367 1.33331 8.49967C1.33331 12.1757 4.32398 15.1663 7.99998 15.1663C11.676 15.1663 14.6666 12.1757 14.6666 8.49967C14.6666 4.82367 11.676 1.83301 7.99998 1.83301ZM7.99998 13.833C5.05931 13.833 2.66665 11.4403 2.66665 8.49967C2.66665 5.55901 5.05931 3.16634 7.99998 3.16634C10.9406 3.16634 13.3333 5.55901 13.3333 8.49967C13.3333 11.4403 10.9406 13.833 7.99998 13.833Z"
                                        fill="#6B7280"
                                    />
                                    <path
                                        d="M8.66665 5.16634H7.33331V8.77567L9.52865 10.971L10.4713 10.0283L8.66665 8.22367V5.16634Z"
                                        fill="#6B7280"
                                    />
                                </svg>
                                <span class="text-sm text-gray-500">{{
                                    formatDateWithTime(note.created_at)
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Message si pas de notes -->
                    <div
                        v-else
                        class="bg-gray-100 p-8 rounded-md text-center font-inter w-full mt-8"
                    >
                        <p class="text-gray-500">
                            Aucune note disponible pour cette intervention
                        </p>
                        <p class="text-sm text-[#FF8C42] mt-2">
                            Utilisez le formulaire ci-dessus pour ajouter des
                            notes
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <!-- Modal pour afficher l'image en grand format -->
        <div
            v-if="showImageModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 overflow-hidden"
            @click="closeImageModal"
        >
            <!-- Desktop modal -->
            <div
                v-if="!isMobile"
                class="relative max-w-4xl w-full max-h-[90vh] bg-white rounded-lg shadow-xl overflow-hidden"
                @click.stop
            >
                <!-- Bouton de fermeture -->
                <button
                    @click="closeImageModal"
                    class="absolute top-2 right-2 z-10 p-2 bg-white rounded-full shadow-md hover:bg-gray-100 transition-colors"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6 text-gray-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <!-- Image agrandie -->
                <div
                    class="w-full h-full flex items-center justify-center bg-gray-100"
                >
                    <img
                        v-if="selectedImage"
                        :src="`/storage/${selectedImage.url_image}`"
                        :alt="`Intervention ${intervention.id}`"
                        class="max-w-full max-h-[80vh] object-contain"
                    />
                </div>
            </div>

            <!-- Mobile modal plein écran -->
            <div
                v-else
                class="relative w-full h-full flex flex-col bg-black"
                @click.stop
                @touchstart="handleTouchStart"
                @touchend="handleTouchEnd"
            >
                <!-- Header mobile avec bouton fermer -->
                <div
                    class="absolute top-0 left-0 right-0 z-20 flex justify-between items-center p-4 bg-gradient-to-b from-black/50 to-transparent"
                >
                    <button
                        @click="closeImageModal"
                        class="p-2 bg-white/20 backdrop-blur-sm rounded-full text-white hover:bg-white/30 transition-colors"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                    <div class="text-white text-sm font-medium">
                        {{
                            intervention.images?.findIndex(
                                (img) => img.id === selectedImage?.id
                            ) + 1
                        }}
                        / {{ intervention.images?.length }}
                    </div>
                </div>

                <!-- Image plein écran avec zoom et pan -->
                <div
                    class="flex-1 flex items-center justify-center overflow-hidden"
                    @touchstart="handleTouchStart"
                    @touchend="handleTouchEnd"
                >
                    <img
                        v-if="selectedImage"
                        :src="`/storage/${selectedImage.url_image}`"
                        :alt="`Intervention ${intervention.id}`"
                        class="max-w-full max-h-full object-contain touch-manipulation select-none"
                        style="
                            user-select: none;
                            -webkit-user-select: none;
                            -webkit-touch-callout: none;
                        "
                        draggable="false"
                    />
                </div>

                <!-- Zones de navigation invisible (swipe) -->
                <div class="absolute inset-0 flex">
                    <!-- Zone gauche pour image précédente -->
                    <div
                        class="w-1/3 h-full flex items-center justify-start pl-4"
                        @click="navigateImage('prev')"
                        v-if="
                            intervention.images &&
                            intervention.images.length > 1
                        "
                    >
                        <div
                            class="text-white/50 hover:text-white transition-colors"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 19l-7-7 7-7"
                                />
                            </svg>
                        </div>
                    </div>

                    <!-- Zone centrale (aucune action) -->
                    <div class="w-1/3 h-full"></div>

                    <!-- Zone droite pour image suivante -->
                    <div
                        class="w-1/3 h-full flex items-center justify-end pr-4"
                        @click="navigateImage('next')"
                        v-if="
                            intervention.images &&
                            intervention.images.length > 1
                        "
                    >
                        <div
                            class="text-white/50 hover:text-white transition-colors"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Navigation mobile -->
                <div
                    v-if="intervention.images && intervention.images.length > 1"
                    class="absolute bottom-0 left-0 right-0 z-20 p-4 bg-gradient-to-t from-black/50 to-transparent"
                >
                    <div class="flex justify-center gap-2">
                        <button
                            v-for="(image, index) in intervention.images"
                            :key="image.id"
                            @click="selectedImage = image"
                            :class="[
                                'w-3 h-3 rounded-full transition-all duration-200',
                                selectedImage?.id === image.id
                                    ? 'bg-white scale-125'
                                    : 'bg-white/50 hover:bg-white/75',
                            ]"
                        ></button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
/* Styles pour cacher la barre de défilement tout en gardant la fonctionnalité */
/* Style pour le popup de services qui apparaît uniquement au survol de l'élément de service */
.service-group:hover .service-popup {
    opacity: 1 !important;
    visibility: visible !important;
}

.hide-scrollbar {
    -ms-overflow-style: none; /* Pour Internet Explorer et Edge */
    scrollbar-width: none; /* Pour Firefox */
    overflow-y: auto;
}

/* Mobile image gallery styles */
@media (max-width: 768px) {
    /* Grille responsive pour les images */
    .mobile-image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 0.5rem;
    }

    /* Amélioration tactile pour mobile */
    .mobile-image-item {
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
    }

    /* Animation de pression pour feedback tactile */
    .mobile-image-item:active {
        transform: scale(0.95);
        transition: transform 0.1s ease;
    }

    /* Modal plein écran sur mobile */
    .mobile-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #000;
        z-index: 9999;
    }

    /* Image avec support zoom natif mobile */
    .mobile-modal img {
        max-width: none;
        min-width: 100%;
        min-height: 100%;
        object-fit: contain;
        touch-action: pinch-zoom;
    }
}

/* Animations pour les interactions */
.image-hover-effect {
    transition: all 0.2s ease-in-out;
}

.image-hover-effect:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Style pour les indicateurs de navigation */
.nav-indicator {
    transition: all 0.2s ease;
    cursor: pointer;
}

.nav-indicator:hover {
    transform: scale(1.2);
}

/* Amélioration du contraste pour les boutons sur images */
.overlay-button {
    backdrop-filter: blur(4px);
    background: rgba(255, 255, 255, 0.9);
}

.overlay-button:hover {
    background: rgba(255, 255, 255, 1);
}

/* Styles personnalisés pour la pagination mobile Splide */
.splide__pagination--mobile {
    bottom: -4rem !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    display: flex !important;
    justify-content: center !important;
    gap: 0.75rem !important;
    padding: 1rem 0 !important;
}

.splide__pagination__page--mobile {
    width: 10px !important;
    height: 10px !important;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.3) !important;
    border: none !important;
    transition: all 0.3s ease !important;
    opacity: 1 !important;
    transform: scale(1) !important;
}

.splide__pagination__page--mobile.is-active {
    background: #ff8c42 !important;
    transform: scale(1.3) !important;
}

.splide__pagination__page--mobile:hover {
    background: rgba(255, 255, 255, 0.6) !important;
    transform: scale(1.2) !important;
}

.splide__pagination__page--mobile.is-active:hover {
    background: #ff8c42 !important;
    transform: scale(1.2) !important;
}

/* Styles pour l'affichage compact sur mobile */
@media (max-width: 480px) {
    .mobile-pagination {
        flex-direction: column;
        gap: 1rem;
    }

    .mobile-pagination .pagination-controls {
        justify-content: space-between;
        width: 100%;
    }

    /* Ajustement pour très petits écrans */
    .splide__pagination--mobile {
        bottom: -3rem !important;
    }

    .splide__pagination__page--mobile {
        width: 10px !important;
        height: 10px !important;
    }
}

.hide-scrollbar::-webkit-scrollbar {
    display: none; /* Pour Chrome, Safari et Opera */
}

/* Style pour le bouton de suppression qui apparaît au survol de la note */
.group:hover .delete-button {
    opacity: 1;
}

/* Style pour le bouton de suppression qui apparaît au survol de l'image */
.hover-card:hover .delete-button {
    opacity: 1;
}

/* Styles pour le carrousel Splide */
:deep(.splide) {
    width: 100%;
    overflow: visible;
}

/* Empêcher le défilement horizontal tout en permettant le dépassement des éléments */
body,
html {
    overflow-x: hidden;
    max-width: 100%;
}

main {
    overflow-x: clip;
    position: relative;
}

:deep(.splide__arrow) {
    background: rgba(255, 255, 255, 0.8);
}

:deep(.splide__arrow svg) {
    fill: #ff8c42;
}

:deep(.splide__pagination__page.is-active) {
    background: #ff8c42;
}

/* Styles pour le popup de services qui apparaît uniquement au survol de l'élément de service */
.service-group:hover .service-popup {
    opacity: 1 !important;
    visibility: visible !important;
}

/* Styles pour l'éditeur TipTap */
:deep(.ProseMirror) {
    min-height: 150px;
    padding: 0.75rem;
    color: #0d0703;
    outline: none !important;
    overflow-y: auto;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

:deep(.ProseMirror p.is-editor-empty:first-child::before) {
    color: rgba(255, 255, 255, 0.5);
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}

/* Styles pour les cartes d'intervention au survol */
.hover-card:hover .delete-button {
    opacity: 1;
}

/* Styles pour le bouton de suppression */
.delete-button {
    opacity: 0;
    transition: opacity 0.3s ease;
}

/* Styles pour les badges de service */
.service-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
    background-color: rgba(255, 140, 66, 0.1);
    color: #ff8c42;
    border: 1px solid #ff8c42;
}

/* Styles pour le contenu Markdown */
.markdown-content {
    width: 100%;
}

.markdown-content ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.markdown-content ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.markdown-content li {
    margin-bottom: 0.5rem;
}

.markdown-content p {
    margin-bottom: 1rem;
}

.markdown-content a {
    color: #ff8c42;
    text-decoration: underline;
}

.markdown-content strong {
    font-weight: bold;
}

.markdown-content em {
    font-style: italic;
}

.markdown-content code {
    font-family: monospace;
    background-color: rgba(0, 0, 0, 0.05);
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
}

.markdown-content pre {
    background-color: rgba(0, 0, 0, 0.05);
    padding: 1rem;
    border-radius: 5px;
    overflow-x: auto;
    margin-bottom: 1rem;
}

.markdown-content blockquote {
    border-left: 3px solid #ff8c42;
    padding-left: 1rem;
    margin-left: 0;
    margin-bottom: 1rem;
    color: rgba(13, 7, 3, 0.7);
}

/* Styles pour s'assurer que le contenu des notes ne dépasse pas son parent */
.markdown-content .text-gray-700 *,
main .text-gray-700 * {
    max-width: 100%;
    width: 100%;
    overflow-wrap: break-word;
    word-wrap: break-word;
    word-break: break-word;
}

.markdown-content .text-gray-700 p,
main .text-gray-700 p {
    width: 100% !important;
    display: block !important;
    box-sizing: border-box !important;
}

/* Surcharger les styles de prose pour les notes */
.markdown-content .prose p,
main .prose p {
    width: 100% !important;
    display: block !important;
    box-sizing: border-box !important;
}

.markdown-content .prose *,
main .prose * {
    width: 100% !important;
    max-width: 100% !important;
}

.text-gray-700 pre,
.text-gray-700 code {
    white-space: pre-wrap;
    max-width: 100%;
    overflow-x: auto;
}

.text-gray-700 img {
    max-width: 100%;
    height: auto;
}

/* Animations pour les actions mobiles des notes */
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

.slide-in-from-right-2 {
    animation-name: slide-in-from-right;
}

/* Effet de pulsation pour indiquer l'appui long sur les notes */
.group:active {
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

/* Amélioration des transitions pour les boutons de notes */
.mobile-action-button-note {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
