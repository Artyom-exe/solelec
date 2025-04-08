<script setup lang="ts">
import { ref, reactive } from "vue";
import { VueFinalModal } from "vue-final-modal";
import "vue-final-modal/style.css";
import Services from "@/Components/Services.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

// Définition d'une prop optionnelle pour le titre du modal
defineProps<{
    title?: string;
}>();

// Définition d'un événement "submit" pour transmettre les données du formulaire
const emit = defineEmits<{
    (e: "submit", formData: Record<string, any>): void;
}>();

const step = ref(1);
const selectedServiceIds = ref<number[]>([]);

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

const handleServiceSelection = (serviceIds: number[]) => {
    selectedServiceIds.value = serviceIds;
    formData.services = [...serviceIds];
};

const nextStep = () => {
    if (step.value < 3) step.value++;
};
const prevStep = () => {
    if (step.value > 1) step.value--;
};

const submitForm = () => {
    emit("submit", formData);
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
                <Services
                    :selectable="true"
                    :selected-services="selectedServiceIds"
                    height="200px"
                    @service-selected="handleServiceSelection"
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
                    <textarea
                        v-model="formData.description"
                        rows="4"
                        placeholder="Décrivez votre projet..."
                        class="w-full h-[180px] bg-white/10 border border-white/20 text-white rounded-[6px] focus:ring-[#FF8C42] focus:border-[#FF8C42] p-2 font-inter text-base"
                    ></textarea>
                    <Datepicker
                        v-model="formData.desiredDate"
                        range
                        :enable-time-picker="false"
                        text-input
                        auto-apply
                        :format="'dd/MM/yyyy'"
                        input-class-name="w-full sm:w-max bg-white/10 border border-white/20 rounded-[6px] focus:ring-[#FF8C42] focus:border-[#FF8C42] p-2 text-white"
                        :theme-color="'#FF8C42'"
                        placeholder="Période souhaitée"
                        :teleport="true"
                    />
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
                            class="border rounded py-2 px-3 border-white/20 bg-[#0D07030D] w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
                        />
                    </div>
                    <div>
                        <label class="block text-[#2D2D2D] font-inter"
                            >Prénom*</label
                        >
                        <input
                            type="text"
                            v-model="formData.lastName"
                            class="border rounded py-2 px-3 border-white/20 bg-[#0D07030D] w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
                        />
                    </div>
                    <div>
                        <label class="block text-[#2D2D2D] font-inter"
                            >Téléphone*</label
                        >
                        <input
                            type="text"
                            v-model="formData.phone"
                            class="border rounded py-2 px-3 border-white/20 bg-[#0D07030D] w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
                        />
                    </div>
                    <div>
                        <label class="block text-[#2D2D2D] font-inter"
                            >Email*</label
                        >
                        <input
                            type="email"
                            v-model="formData.email"
                            class="border rounded py-2 px-3 border-white/20 bg-[#0D07030D] w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
                        />
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
                    :disabled="selectedServiceIds.length === 0"
                    class="transition"
                >
                    Suivant
                </SecondaryButton>
                <SecondaryButton
                    v-if="step === 2"
                    @click="prevStep"
                    :disabled="selectedServiceIds.length === 0"
                    class="transition"
                >
                    Précédent
                </SecondaryButton>
                <PrimaryButton
                    v-if="step === 2"
                    @click="nextStep"
                    :disabled="selectedServiceIds.length === 0"
                    class="transition"
                >
                    Suivant
                </PrimaryButton>
                <SecondaryButton
                    v-if="step === 3"
                    @click="prevStep"
                    variant="dark"
                    :disabled="selectedServiceIds.length === 0"
                    class="transition"
                >
                    Suivant
                </SecondaryButton>
                <PrimaryButton
                    v-if="step === 3"
                    @click="submitForm"
                    :disabled="selectedServiceIds.length === 0"
                    class="transition"
                >
                    Terminé
                </PrimaryButton>
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
