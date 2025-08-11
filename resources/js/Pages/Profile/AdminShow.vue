<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    user: Object,
    status: String,
});

// Formulaire pour mettre à jour l'email
const emailForm = useForm({
    name: $page.props.auth.user.name,
    email: $page.props.auth.user.email,
});

// Formulaire pour mettre à jour le mot de passe
const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updateEmail = () => {
    emailForm.put(route("user-profile-information.update"), {
        errorBag: "updateProfileInformation",
        preserveScroll: true,
    });
};

const updatePassword = () => {
    passwordForm.put(route("user-password.update"), {
        errorBag: "updatePassword",
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset("password", "password_confirmation");
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset("current_password");
            }
        },
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Profil - SOLELEC" />

        <header
            class="flex md:py-28 py-16 md:px-16 px-5 flex-col items-start gap-20 bg-[#2D2D2D] md:mt-[3.5rem] mt-16"
        >
            <div
                class="flex md:flex-row flex-col items-start md:gap-20 gap-5 self-stretch"
            >
                <h1
                    class="text-white font-poppins text-[2rem] md:text-[2.5rem] font-medium md:leading-[3rem] leading-[2.4rem] md:w-1/2"
                >
                    Gestion de votre profil
                </h1>
                <div class="flex flex-col items-start gap-8 flex-1 md:w-1/2">
                    <p class="text-white font-inter md:text-lg text-base">
                        Modifiez vos informations personnelles et sécurisez
                        votre compte en mettant à jour votre mot de passe.
                    </p>
                </div>
            </div>
        </header>

        <!-- Section principale -->
        <section class="bg-white md:py-28 py-16 md:px-16 px-5">
            <div class="max-w-4xl mx-auto space-y-12">
                <!-- Formulaire de modification de l'email -->
                <div
                    class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm"
                >
                    <div class="mb-6">
                        <h2
                            class="text-[#2D2D2D] font-poppins text-2xl font-medium mb-2"
                        >
                            Informations du compte
                        </h2>
                        <p class="text-gray-600 font-inter">
                            Mettez à jour vos informations de base.
                        </p>
                    </div>

                    <form @submit.prevent="updateEmail" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="name"
                                    class="block text-sm font-medium text-[#2D2D2D] font-inter mb-2"
                                >
                                    Nom complet
                                </label>
                                <input
                                    id="name"
                                    v-model="emailForm.name"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF8C42] focus:border-[#FF8C42] transition-colors font-inter"
                                    required
                                />
                                <div
                                    v-if="emailForm.errors.name"
                                    class="mt-1 text-sm text-red-600 font-inter"
                                >
                                    {{ emailForm.errors.name }}
                                </div>
                            </div>

                            <div>
                                <label
                                    for="email"
                                    class="block text-sm font-medium text-[#2D2D2D] font-inter mb-2"
                                >
                                    Adresse email
                                </label>
                                <input
                                    id="email"
                                    v-model="emailForm.email"
                                    type="email"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF8C42] focus:border-[#FF8C42] transition-colors font-inter"
                                    required
                                />
                                <div
                                    v-if="emailForm.errors.email"
                                    class="mt-1 text-sm text-red-600 font-inter"
                                >
                                    {{ emailForm.errors.email }}
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="emailForm.processing"
                                class="px-6 py-3 bg-[#FF8C42] text-white font-inter font-medium rounded-lg hover:bg-[#FF8C42]/90 focus:outline-none focus:ring-2 focus:ring-[#FF8C42]/50 transition-colors duration-200"
                                :class="{
                                    'opacity-50 cursor-not-allowed':
                                        emailForm.processing,
                                }"
                            >
                                <span
                                    v-if="emailForm.processing"
                                    class="flex items-center"
                                >
                                    <svg
                                        class="animate-spin -ml-1 mr-2 h-4 w-4"
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
                                    Mise à jour...
                                </span>
                                <span v-else>Mettre à jour</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Formulaire de modification du mot de passe -->
                <div
                    class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm"
                >
                    <div class="mb-6">
                        <h2
                            class="text-[#2D2D2D] font-poppins text-2xl font-medium mb-2"
                        >
                            Sécurité du compte
                        </h2>
                        <p class="text-gray-600 font-inter">
                            Changez votre mot de passe pour sécuriser votre
                            compte.
                        </p>
                    </div>

                    <form @submit.prevent="updatePassword" class="space-y-6">
                        <div>
                            <label
                                for="current_password"
                                class="block text-sm font-medium text-[#2D2D2D] font-inter mb-2"
                            >
                                Mot de passe actuel
                            </label>
                            <input
                                id="current_password"
                                v-model="passwordForm.current_password"
                                type="password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF8C42] focus:border-[#FF8C42] transition-colors font-inter"
                                required
                                autocomplete="current-password"
                            />
                            <div
                                v-if="passwordForm.errors.current_password"
                                class="mt-1 text-sm text-red-600 font-inter"
                            >
                                {{ passwordForm.errors.current_password }}
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    for="password"
                                    class="block text-sm font-medium text-[#2D2D2D] font-inter mb-2"
                                >
                                    Nouveau mot de passe
                                </label>
                                <input
                                    id="password"
                                    v-model="passwordForm.password"
                                    type="password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF8C42] focus:border-[#FF8C42] transition-colors font-inter"
                                    required
                                    autocomplete="new-password"
                                />
                                <div
                                    v-if="passwordForm.errors.password"
                                    class="mt-1 text-sm text-red-600 font-inter"
                                >
                                    {{ passwordForm.errors.password }}
                                </div>
                            </div>

                            <div>
                                <label
                                    for="password_confirmation"
                                    class="block text-sm font-medium text-[#2D2D2D] font-inter mb-2"
                                >
                                    Confirmer le nouveau mot de passe
                                </label>
                                <input
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#FF8C42] focus:border-[#FF8C42] transition-colors font-inter"
                                    required
                                    autocomplete="new-password"
                                />
                                <div
                                    v-if="
                                        passwordForm.errors
                                            .password_confirmation
                                    "
                                    class="mt-1 text-sm text-red-600 font-inter"
                                >
                                    {{
                                        passwordForm.errors
                                            .password_confirmation
                                    }}
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="passwordForm.processing"
                                class="px-6 py-3 bg-[#FF8C42] text-white font-inter font-medium rounded-lg hover:bg-[#FF8C42]/90 focus:outline-none focus:ring-2 focus:ring-[#FF8C42]/50 transition-colors duration-200"
                                :class="{
                                    'opacity-50 cursor-not-allowed':
                                        passwordForm.processing,
                                }"
                            >
                                <span
                                    v-if="passwordForm.processing"
                                    class="flex items-center"
                                >
                                    <svg
                                        class="animate-spin -ml-1 mr-2 h-4 w-4"
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
                                    Mise à jour...
                                </span>
                                <span v-else>Changer le mot de passe</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
