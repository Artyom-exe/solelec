<script setup>
import { nextTick, ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const recovery = ref(false);

const form = useForm({
    code: "",
    recovery_code: "",
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value ^= true;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value.focus();
        form.code = "";
    } else {
        codeInput.value.focus();
        form.recovery_code = "";
    }
};

const submit = () => {
    form.post(route("two-factor.login"));
};
</script>

<template>
    <Head title="Authentification à deux facteurs - SOLELEC" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <!-- Titre -->
        <div class="text-center mb-8">
            <h1 class="text-white font-poppins text-2xl font-medium mb-2">
                Authentification à deux facteurs
            </h1>
            <p class="text-white/70 font-inter text-sm">
                Confirmez votre identité pour continuer
            </p>
        </div>

        <div
            class="mb-6 text-sm text-white/70 font-inter p-4 bg-white/5 border border-white/10 rounded-lg"
        >
            <template v-if="!recovery">
                Veuillez confirmer l'accès à votre compte en saisissant le code
                d'authentification fourni par votre application
                d'authentification.
            </template>

            <template v-else>
                Veuillez confirmer l'accès à votre compte en saisissant l'un de
                vos codes de récupération d'urgence.
            </template>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div v-if="!recovery">
                <InputLabel for="code" value="Code d'authentification" />
                <TextInput
                    id="code"
                    ref="codeInput"
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    class="mt-2"
                    autofocus
                    autocomplete="one-time-code"
                    placeholder="123456"
                />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div v-else>
                <InputLabel for="recovery_code" value="Code de récupération" />
                <TextInput
                    id="recovery_code"
                    ref="recoveryCodeInput"
                    v-model="form.recovery_code"
                    type="text"
                    class="mt-2"
                    autocomplete="one-time-code"
                    placeholder="abcd-efgh-ijkl"
                />
                <InputError class="mt-2" :message="form.errors.recovery_code" />
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
                        Vérification en cours...
                    </span>
                    <span v-else>Se connecter</span>
                </PrimaryButton>

                <div class="text-center pt-4 border-t border-white/10">
                    <button
                        type="button"
                        class="text-sm text-[#FF8C42] hover:text-[#FF8C42]/80 font-inter transition-colors duration-300"
                        @click.prevent="toggleRecovery"
                    >
                        <template v-if="!recovery">
                            Utiliser un code de récupération
                        </template>

                        <template v-else>
                            Utiliser un code d'authentification
                        </template>
                    </button>
                </div>
            </div>
        </form>
    </AuthenticationCard>
</template>
