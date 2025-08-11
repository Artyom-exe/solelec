<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Inscription - SOLELEC" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <!-- Titre -->
        <div class="text-center mb-8">
            <h1 class="text-white font-poppins text-2xl font-medium mb-2">
                Inscription
            </h1>
            <p class="text-white/70 font-inter text-sm">
                Créez votre compte administrateur
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="name" value="Nom complet" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-2"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Votre nom complet"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Adresse email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-2"
                    required
                    autocomplete="username"
                    placeholder="votre@email.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Mot de passe" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-2"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirmer le mot de passe"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-2"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature">
                <label class="flex items-start">
                    <Checkbox
                        id="terms"
                        v-model:checked="form.terms"
                        name="terms"
                        required
                    />
                    <div class="ms-3 text-sm">
                        <span class="text-white/70 font-inter">
                            J'accepte les
                            <a
                                target="_blank"
                                :href="route('terms.show')"
                                class="text-[#FF8C42] hover:text-[#FF8C42]/80 transition-colors duration-300"
                            >
                                Conditions d'utilisation
                            </a>
                            et la
                            <a
                                target="_blank"
                                :href="route('policy.show')"
                                class="text-[#FF8C42] hover:text-[#FF8C42]/80 transition-colors duration-300"
                            >
                                Politique de confidentialité
                            </a>
                        </span>
                    </div>
                </label>
                <InputError class="mt-2" :message="form.errors.terms" />
            </div>

            <div class="flex flex-col gap-4 pt-4">
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
                        Inscription en cours...
                    </span>
                    <span v-else>S'inscrire</span>
                </PrimaryButton>

                <div class="text-center pt-4 border-t border-white/10">
                    <Link
                        :href="route('login')"
                        class="text-sm text-[#FF8C42] hover:text-[#FF8C42]/80 font-inter transition-colors duration-300"
                    >
                        Déjà inscrit ? Se connecter
                    </Link>
                </div>
            </div>
        </form>
    </AuthenticationCard>
</template>
