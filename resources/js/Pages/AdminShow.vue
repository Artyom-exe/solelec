<template>
    <AdminLayout title="Gérer mon profil">
        <div
            class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 lg:mt-[72px] mt-16"
        >
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 font-poppins">
                    Gérer mon profil
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Modifiez vos informations personnelles et votre mot de passe
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Informations du profil -->
                <div
                    class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2
                            class="text-lg font-semibold text-gray-900 font-poppins"
                        >
                            Informations du profil
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Mettez à jour vos informations personnelles
                        </p>
                    </div>

                    <form @submit.prevent="updateProfile" class="p-6 space-y-6">
                        <div>
                            <label
                                for="name"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Nom complet
                            </label>
                            <input
                                v-model="profileForm.name"
                                type="text"
                                id="name"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF8C42] focus:border-transparent transition-colors"
                                :class="{
                                    'border-red-500': profileForm.errors.name,
                                }"
                            />
                            <div
                                v-if="profileForm.errors.name"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ profileForm.errors.name }}
                            </div>
                        </div>

                        <div>
                            <label
                                for="email"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Adresse email
                            </label>
                            <input
                                v-model="profileForm.email"
                                type="email"
                                id="email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF8C42] focus:border-transparent transition-colors"
                                :class="{
                                    'border-red-500': profileForm.errors.email,
                                }"
                            />
                            <div
                                v-if="profileForm.errors.email"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ profileForm.errors.email }}
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <PrimaryButton
                                type="submit"
                                :disabled="profileForm.processing"
                                class="!w-auto"
                            >
                                <span
                                    v-if="profileForm.processing"
                                    class="flex items-center"
                                >
                                    <svg
                                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
                                    Enregistrement...
                                </span>
                                <span v-else
                                    >Enregistrer les modifications</span
                                >
                            </PrimaryButton>
                        </div>
                    </form>
                </div>

                <!-- Changer le mot de passe -->
                <div
                    class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2
                            class="text-lg font-semibold text-gray-900 font-poppins"
                        >
                            Changer le mot de passe
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Assurez-vous d'utiliser un mot de passe sécurisé
                        </p>
                    </div>

                    <form
                        @submit.prevent="updatePassword"
                        class="p-6 space-y-6"
                    >
                        <div>
                            <label
                                for="current_password"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Mot de passe actuel
                            </label>
                            <input
                                v-model="passwordForm.current_password"
                                type="password"
                                id="current_password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF8C42] focus:border-transparent transition-colors"
                                :class="{
                                    'border-red-500':
                                        passwordForm.errors.current_password,
                                }"
                            />
                            <div
                                v-if="passwordForm.errors.current_password"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ passwordForm.errors.current_password }}
                            </div>
                        </div>

                        <div>
                            <label
                                for="password"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Nouveau mot de passe
                            </label>
                            <input
                                v-model="passwordForm.password"
                                type="password"
                                id="password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF8C42] focus:border-transparent transition-colors"
                                :class="{
                                    'border-red-500':
                                        passwordForm.errors.password,
                                }"
                            />
                            <div
                                v-if="passwordForm.errors.password"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ passwordForm.errors.password }}
                            </div>
                        </div>

                        <div>
                            <label
                                for="password_confirmation"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Confirmer le nouveau mot de passe
                            </label>
                            <input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                id="password_confirmation"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF8C42] focus:border-transparent transition-colors"
                            />
                        </div>

                        <div class="flex justify-end">
                            <PrimaryButton
                                type="submit"
                                :disabled="passwordForm.processing"
                                class="!w-auto"
                            >
                                <span
                                    v-if="passwordForm.processing"
                                    class="flex items-center"
                                >
                                    <svg
                                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
                                    Mise à jour...
                                </span>
                                <span v-else
                                    >Mettre à jour le mot de passe</span
                                >
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Messages de succès -->
            <div
                v-if="$page.props.flash.success"
                class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg"
            >
                {{ $page.props.flash.success }}
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

// Props
const props = defineProps({
    user: Object,
});

// Formulaire pour le profil
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

// Formulaire pour le mot de passe
const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

// Méthodes
const updateProfile = () => {
    profileForm.put(route("profile.admin.update"), {
        preserveScroll: true,
        onSuccess: () => {
            // Le message de succès sera affiché via flash
        },
    });
};

const updatePassword = () => {
    passwordForm.put(route("profile.admin.password"), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};
</script>
