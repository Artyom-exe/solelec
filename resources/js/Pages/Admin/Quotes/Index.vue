<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { marked } from "marked";

const props = defineProps({
    clients: Object,
    quotes: Array,
    quoteService: Object,
    errors: Object,
    flash: Object,
});

// État pour suivre le filtre actuel
const currentFilter = ref("all"); // 'all', 'type', ou 'client'
const selectedType = ref(null);
const selectedClient = ref(null);

// État pour gérer l'affichage des actions mobiles
const showMobileActions = ref({});

// Fonction pour gérer l'appui long sur mobile
let longPressTimer = null;
const startLongPress = (quoteId) => {
    longPressTimer = setTimeout(() => {
        showMobileActions.value = { [quoteId]: true };
    }, 500); // 500ms pour déclencher l'appui long
};

const cancelLongPress = () => {
    if (longPressTimer) {
        clearTimeout(longPressTimer);
        longPressTimer = null;
    }
};

const hideMobileActions = (quoteId) => {
    showMobileActions.value = { ...showMobileActions.value, [quoteId]: false };
};

// Fonction pour supprimer un devis
const deleteQuote = (id) => {
    if (confirm("Êtes-vous sûr de vouloir supprimer ce devis ?")) {
        router.delete(`/admin/devis/${id}`, {
            preserveScroll: true,
            onSuccess: () => {
                // Le serveur s'occupe de la redirection ou du rechargement des données
            },
        });
    }
};

// Récupération des types de services uniques depuis les devis
const serviceTypes = computed(() => {
    const types = new Set();
    props.quotes.forEach((quote) => {
        quote.services?.forEach((service) => {
            types.add(service.title);
        });
    });
    return Array.from(types);
});

// Récupération des clients uniques
const uniqueClients = computed(() => {
    const clients = new Set();
    props.quotes.forEach((quote) => {
        if (quote.client?.name && quote.client?.lastname) {
            clients.add(`${quote.client.name} ${quote.client.lastname}`);
        }
    });
    return Array.from(clients);
});

// Filtrage des devis en fonction du filtre actuel
const filteredQuotes = computed(() => {
    if (currentFilter.value === "all") {
        return props.quotes;
    } else if (currentFilter.value === "type" && selectedType.value) {
        return props.quotes.filter((quote) =>
            quote.services?.some(
                (service) => service.title === selectedType.value
            )
        );
    } else if (currentFilter.value === "client" && selectedClient.value) {
        return props.quotes.filter(
            (quote) =>
                `${quote.client?.name} ${quote.client?.lastname}` ===
                selectedClient.value
        );
    }
    return props.quotes;
});

// Fonction pour définir le filtre
const setFilter = (filter) => {
    if (currentFilter.value === filter) {
        // Si on clique sur le même filtre, on revient à "tout"
        currentFilter.value = "all";
        selectedType.value = null;
        selectedClient.value = null;
    } else {
        currentFilter.value = filter;
    }
};

// Fonction pour formater une date au format jour/mois/année
const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return `${String(date.getDate()).padStart(2, "0")}/${String(
        date.getMonth() + 1
    ).padStart(2, "0")}/${date.getFullYear()}`;
};

// Fonction pour convertir le markdown en HTML
const renderMarkdown = (text) => {
    if (!text) return "";
    return marked(text);
};
</script>

<template>
    <AdminLayout>
        <section
            class="flex md:flex-row flex-col md:py-28 md:px-16 py-16 px-5 items-start gap-20 bg-[#2D2D2D] md:mt-14 mt-16 min-h-screen"
        >
            <div class="flex flex-col items-start gap-4 text-white md:w-3/4">
                <h2 class="text-center font-inter text-base font-semibold">
                    Devis
                </h2>
                <div class="relative flex flex-col gap-6">
                    <h3
                        class="font-poppins md:text-5xl text-4xl text-left font-medium md:leading-[57.6px] leading-[43.2px] tracking-[-0.36px] md:tracking-[-0.48px] mb-6"
                    >
                        Mes demande de devis
                    </h3>
                    <div
                        class="absolute bottom-0 right border-2 border-[#FF8C42] w-[70%] max-w-[353px] min-w-[200px]"
                    ></div>
                </div>
            </div>
            <article class="flex flex-col items-start gap-12 w-full">
                <div class="flex flex-col w-full gap-4">
                    <div class="flex items-center w-full">
                        <button
                            @click="setFilter('all')"
                            :class="{
                                'bg-[#0D0703] border border-white/20':
                                    currentFilter === 'all',
                            }"
                            class="flex py-2 px-4 justify-center items-center gap-2 text-white font-inter border-transparent text-base transition-all duration-300 ease-in-out hover:bg-[#0D0703] hover:border hover:border-white/20"
                        >
                            Voir tout
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
                                'bg-[#FF8C42] text-white':
                                    selectedType === type,
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
                </div>

                <!-- Liste des devis -->
                <div class="flex flex-col gap-8 items-start w-full">
                    <div
                        v-for="quote in filteredQuotes"
                        :key="quote.id"
                        class="bg-[#242424] rounded-lg border border-white/20 text-white p-4 md:p-8 gap-6 items-start w-full group relative cursor-pointer md:cursor-default transition-all duration-300 active:scale-[0.98] active:bg-gray-800/50"
                        @touchstart="startLongPress(quote.id)"
                        @touchend="cancelLongPress"
                        @touchcancel="cancelLongPress"
                        @mousedown="startLongPress(quote.id)"
                        @mouseup="cancelLongPress"
                        @mouseleave="cancelLongPress"
                    >
                        <!-- Actions d'édition/suppression qui apparaissent au survol sur desktop et après appui long sur mobile -->
                        <div
                            class="absolute top-[-0.5rem] right-[-0.5rem] transition-opacity flex gap-2 z-10"
                            :class="
                                showMobileActions[quote.id]
                                    ? 'opacity-100 md:opacity-0 md:group-hover:opacity-100'
                                    : 'opacity-0 md:group-hover:opacity-100'
                            "
                            @click.stop
                            @touchstart.stop
                            @mousedown.stop
                        >
                            <button
                                @click="deleteQuote(quote.id)"
                                title="Supprimer"
                                class="p-2 rounded-xl bg-red-600/20 hover:bg-red-600/30 border border-red-500/30 transition-all duration-200 active:scale-95"
                            >
                                <img
                                    src="/assets/icons/clients/delete-icon.svg"
                                    alt="Delete"
                                    class="w-5 h-5 hover:scale-110 transition-transform"
                                />
                            </button>
                        </div>

                        <!-- Zone de clic pour fermer les actions mobiles quand on clique ailleurs -->
                        <div
                            v-if="showMobileActions[quote.id]"
                            class="fixed inset-0 z-0 bg-black/20 backdrop-blur-sm md:hidden"
                            @click="hideMobileActions(quote.id)"
                            @touchstart="hideMobileActions(quote.id)"
                        ></div>
                        <div
                            class="flex flex-col items-start gap-8 self-stretch"
                        >
                            <div class="flex gap-4 self-stretch">
                                <div
                                    class="flex items-center gap-4 flex-1 flex-wrap"
                                >
                                    <h4
                                        class="font-poppins md:text-2xl text-xl font-medium tracking-[-0.24px]"
                                    >
                                        {{ quote.client?.name }}
                                        {{ quote.client?.lastname }}
                                    </h4>
                                    <span
                                        v-for="service in quote.services"
                                        :key="service.id"
                                        class="flex py-1 px-[10px] items-start rounded-[4px] border border-white/5 bg-white/5 text-[#FF8C42] font-inter font-semibold text-sm"
                                    >
                                        {{ service.title }}
                                    </span>
                                </div>
                                <div class="flex gap-2 h-max items-center">
                                    <Link
                                        :href="`/admin/devis/${quote.id}/convert`"
                                        class="text-[#FF8C42] font-inter text-base font-medium flex items-center convert-link"
                                    >
                                        Convertir
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 256 512"
                                            class="w-4 h-4 fill-current text-[#FF8C42] ml-1 transition-transform duration-300"
                                        >
                                            <path
                                                d="M246.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-160 160c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L178.7 256 41.4 118.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l160 160z"
                                            />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                            <div
                                class="font-inter text-base font-normal markdown-content"
                                v-html="renderMarkdown(quote.description)"
                            ></div>
                            <div
                                class="flex flex-start content-start gap-6 self-stretch font-inter text-base font-normal"
                            >
                                <div class="flex flex-col gap-4">
                                    <div class="flex gap-6 flex-wrap">
                                        <div class="flex items-center gap-3">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="24"
                                                height="24"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    clip-rule="evenodd"
                                                    d="M17.297 6.57202C16.887 6.57202 16.547 6.23202 16.547 5.82202V4.59802C15.773 4.57202 14.864 4.57202 13.797 4.57202H11.797C10.729 4.57202 9.821 4.57202 9.047 4.59802V5.82202C9.047 6.23202 8.707 6.57202 8.297 6.57202C7.887 6.57202 7.547 6.23202 7.547 5.82202V4.70602C6.602 4.82902 5.953 5.06602 5.497 5.52202C4.895 6.12402 4.675 7.06202 4.594 8.57202H21C20.919 7.06202 20.698 6.12402 20.097 5.52202C19.641 5.06602 18.992 4.82902 18.047 4.70602V5.82202C18.047 6.23202 17.707 6.57202 17.297 6.57202ZM21.041 10.072C21.0463 10.6007 21.0483 11.184 21.047 11.822V12.822C21.047 13.232 21.387 13.572 21.797 13.572C22.207 13.572 22.547 13.232 22.547 12.822V11.822C22.547 7.84202 22.547 5.85202 21.157 4.46202C20.385 3.69002 19.427 3.34702 18.047 3.19402V2.32202C18.047 1.91202 17.707 1.57202 17.297 1.57202C16.887 1.57202 16.547 1.91202 16.547 2.32202V3.09702C15.751 3.07202 14.842 3.07202 13.797 3.07202H11.797C10.751 3.07202 9.843 3.07202 9.047 3.09702V2.32202C9.047 1.91202 8.707 1.57202 8.297 1.57202C7.887 1.57202 7.547 1.91202 7.547 2.32202V3.19402C6.167 3.34702 5.209 3.69002 4.437 4.46202C3.047 5.85202 3.047 7.85202 3.047 11.822V13.822C3.047 17.802 3.047 19.792 4.437 21.182C5.827 22.572 7.817 22.572 11.797 22.572C12.207 22.572 12.547 22.232 12.547 21.822C12.547 21.412 12.207 21.072 11.797 21.072C8.237 21.072 6.447 21.072 5.497 20.122C4.547 19.172 4.547 17.382 4.547 13.822V11.822C4.54633 11.1847 4.548 10.6014 4.552 10.072H21.041ZM17.797 23.072C15.177 23.072 13.047 20.942 13.047 18.322C13.047 15.702 15.177 13.572 17.797 13.572C20.417 13.572 22.547 15.702 22.547 18.322C22.547 20.942 20.417 23.072 17.797 23.072ZM17.797 15.072C16.007 15.072 14.547 16.532 14.547 18.322C14.547 20.112 16.007 21.572 17.797 21.572C19.587 21.572 21.047 20.112 21.047 18.322C21.047 16.532 19.587 15.072 17.797 15.072ZM18.267 19.852C18.417 20.002 18.607 20.072 18.797 20.072C18.987 20.072 19.177 20.002 19.327 19.852C19.617 19.562 19.617 19.082 19.327 18.792L18.547 18.012V16.322C18.547 15.912 18.207 15.572 17.797 15.572C17.387 15.572 17.047 15.912 17.047 16.322V18.322C17.047 18.522 17.127 18.712 17.267 18.852L18.267 19.852Z"
                                                />
                                            </svg>
                                            <span class="text-sm md:text-base">
                                                {{
                                                    quote.requested_date !==
                                                    null
                                                        ? quote.end_date !==
                                                          null
                                                            ? `${formatDate(
                                                                  quote.requested_date
                                                              )} - ${formatDate(
                                                                  quote.end_date
                                                              )}`
                                                            : formatDate(
                                                                  quote.requested_date
                                                              )
                                                        : "non renseignée"
                                                }}
                                            </span>
                                        </div>
                                        <div class="flex">
                                            <a
                                                v-if="
                                                    quote.client?.phone !== null
                                                "
                                                :href="`tel:${quote.client?.phone}`"
                                                class="flex gap-3 hover:text-[#FF8C42] transition-colors duration-300 text-sm md:text-base"
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
                                                {{ quote.client?.phone }}
                                            </a>
                                            <template v-else
                                                >non renseigné</template
                                            >
                                        </div>
                                    </div>
                                    <div class="flex gap-6 flex-wrap">
                                        <div class="flex">
                                            <a
                                                v-if="
                                                    quote.client?.email !== null
                                                "
                                                :href="`mailto:${quote.client?.email}`"
                                                class="flex gap-3 items-center hover:text-[#FF8C42] transition-colors duration-300 text-sm md:text-base"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="22"
                                                    height="22"
                                                    fill="currentColor"
                                                    class="bi bi-envelope"
                                                    viewBox="0 0 16 16"
                                                >
                                                    <path
                                                        d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"
                                                    />
                                                </svg>
                                                {{ quote.client?.email }}
                                            </a>
                                            <template v-else
                                                >non renseigné</template
                                            >
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <a
                                                v-if="quote.client?.adress"
                                                :href="`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
                                                    quote.client?.adress
                                                )}`"
                                                target="_blank"
                                                rel="noopener"
                                                class="font-inter text-blue-300 font-normal hover:text-[#FF8C42] transition-colors cursor-pointer flex items-center gap-3 text-sm md:text-base"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="22"
                                                    height="22"
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
                                                {{ quote.client?.adress }}
                                            </a>
                                            <template v-else
                                                >non renseigné</template
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message si aucun devis -->
                <div
                    v-if="filteredQuotes.length === 0"
                    class="text-white text-center w-full py-10"
                >
                    <p class="font-inter text-lg">
                        Aucun devis disponible pour le moment{{
                            currentFilter !== "all" ? " avec ce filtre" : ""
                        }}.
                    </p>
                </div>
            </article>
        </section>
    </AdminLayout>
</template>

<style>
.convert-link:hover svg {
    transform: translateX(4px);
}

/* Styles pour le contenu Markdown */
.markdown-content {
    width: 100%;
}

.markdown-content ul {
    list-style-type: disc;
    margin-left: 1.5rem;
    margin-bottom: 0.5rem;
}

.markdown-content ol {
    list-style-type: decimal;
    margin-left: 1.5rem;
    margin-bottom: 0.5rem;
}

.markdown-content p {
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.markdown-content h1,
.markdown-content h2,
.markdown-content h3,
.markdown-content h4,
.markdown-content h5,
.markdown-content h6 {
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.markdown-content a {
    color: #ff8c42;
    text-decoration: underline;
}

.markdown-content code {
    font-family: monospace;
    background-color: rgba(255, 255, 255, 0.1);
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
}

.markdown-content blockquote {
    border-left: 4px solid #ff8c42;
    padding-left: 1rem;
    margin-left: 0;
    margin-right: 0;
    font-style: italic;
    color: rgba(255, 255, 255, 0.8);
}

/* Animations pour les actions mobiles */
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

/* Effet de pulsation pour indiquer l'appui long */
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

/* Amélioration des transitions pour les boutons */
button {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
