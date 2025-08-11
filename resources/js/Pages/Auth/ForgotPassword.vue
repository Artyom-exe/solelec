<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
    status: String,
});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <Head title="Mot de passe oublié - SOLELEC" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <!-- Titre -->
        <div class="text-center mb-8">
            <h1 class="text-white font-poppins text-2xl font-medium mb-2">
                Mot de passe oublié
            </h1>
            <p class="text-white/70 font-inter text-sm">
                Saisissez votre email pour recevoir un lien de réinitialisation
            </p>
        </div>

        <div
            class="mb-6 text-sm text-white/70 font-inter p-4 bg-white/5 border border-white/10 rounded-lg"
        >
            Vous avez oublié votre mot de passe ? Aucun problème. Indiquez-nous
            votre adresse email et nous vous enverrons un lien de
            réinitialisation qui vous permettra d'en choisir un nouveau.
        </div>

        <div
            v-if="status"
            class="mb-6 p-4 bg-green-500/20 border border-green-500/30 rounded-lg"
        >
            <p class="text-green-300 font-inter text-sm">{{ status }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Adresse email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-2"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="votre@email.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="pt-4">
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
                    <span v-else>Envoyer le lien de réinitialisation</span>
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
