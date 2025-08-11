<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent"
);
</script>

<template>
    <Head title="Vérification email - SOLELEC" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <!-- Titre -->
        <div class="text-center mb-8">
            <h1 class="text-white font-poppins text-2xl font-medium mb-2">
                Vérification email
            </h1>
            <p class="text-white/70 font-inter text-sm">
                Confirmez votre adresse email pour continuer
            </p>
        </div>

        <div
            class="mb-6 text-sm text-white/70 font-inter p-4 bg-white/5 border border-white/10 rounded-lg"
        >
            Avant de continuer, pourriez-vous vérifier votre adresse email en
            cliquant sur le lien que nous venons de vous envoyer ? Si vous
            n'avez pas reçu l'email, nous vous en enverrons volontiers un autre.
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-6 p-4 bg-green-500/20 border border-green-500/30 rounded-lg"
        >
            <p class="text-green-300 font-inter text-sm">
                Un nouveau lien de vérification a été envoyé à l'adresse email
                fournie dans vos paramètres de profil.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="flex flex-col gap-4">
                <PrimaryButton
                    class="w-full justify-center py-3"
                    :class="{
                        'opacity-50 cursor-not-allowed': form.processing,
                    }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center">
                        <svg
                            class="animate-spin -ml-1 mr-3 h-4 w-4 text-white"
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
                    <span v-else>Renvoyer l'email de vérification</span>
                </PrimaryButton>

                <div class="flex flex-col gap-2 pt-4 border-t border-white/10">
                    <Link
                        :href="route('profile.show')"
                        class="text-center text-sm text-[#FF8C42] hover:text-[#FF8C42]/80 font-inter transition-colors duration-300"
                    >
                        Modifier le profil
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-center text-sm text-white/70 hover:text-white font-inter transition-colors duration-300"
                    >
                        Se déconnecter
                    </Link>
                </div>
            </div>
        </form>
    </AuthenticationCard>
</template>
