<script setup lang="ts">
import { ref, reactive } from 'vue'
import { VueFinalModal } from 'vue-final-modal'
import 'vue-final-modal/style.css'
import Services from '@/Components/Services.vue'

// Définition d'une prop optionnelle pour le titre du modal
defineProps<{
  title?: string
}>()

// Définition d'un événement "submit" pour transmettre les données du formulaire
const emit = defineEmits<{
  (e: 'submit', formData: Record<string, any>): void
}>()

// Étape courante (1 : services, 2 : description, 3 : coordonnées)
const step = ref(1)

// Services sélectionnés (IDs)
const selectedServiceIds = ref<number[]>([]);

// Données du formulaire regroupant les infos pour le devis et le client
const formData = reactive({
  services: [] as number[], // tableau des IDs de services sélectionnés
  description: '',
  desiredDate: '',
  name: '',
  phone: '',
  email: '',
  address: '',
})

// Gérer la sélection des services
const handleServiceSelection = (serviceIds: number[]) => {
  selectedServiceIds.value = serviceIds;
  formData.services = [...serviceIds];
}

// Fonctions de navigation entre les étapes
const nextStep = () => {
  if (step.value < 3) step.value++
}
const prevStep = () => {
  if (step.value > 1) step.value--
}

// Fonction appelée au clic sur "Valider"
const submitForm = () => {
  emit('submit', formData)
}
</script>

<template>
  <!-- Modal principal -->
  <VueFinalModal
    overlay-transition="vfm-fade"
    content-transition="vfm-scale-fade"
    overlay-class="bg-[rgba(0,0,0,0.4)]"
    class="flex justify-center items-center px-2"
    content-class="bg-white w-full max-w-5xl rounded-lg shadow-lg relative max-h-[90vh] flex flex-col"
    :reserve-scroll-bar-gap="false"
  >

    <!-- Zone de contenu défilable -->
    <div class="flex-1 overflow-y-auto p-6 rounded-lg">
      <!-- Étape 1 : Choix du/des service(s) avec le composant Services -->
      <div v-if="step === 1" class="flex flex-col gap-6">
        <h3 class="text-[#0D0703] font-medium text-2xl font-poppins">Je désire :</h3>

        <!-- Services avec une hauteur réduite et une marge en bas -->
        <div class="services-container">
          <Services
            :selectable="true"
            :selected-services="selectedServiceIds"
            height="200px"
            @service-selected="handleServiceSelection"
          />
        </div>

        <!-- Affichage du nombre de services sélectionnés -->
        <div v-if="selectedServiceIds.length > 0" class="mt-2 text-sm text-gray-600">
          {{ selectedServiceIds.length }} service{{ selectedServiceIds.length > 1 ? 's' : '' }} sélectionné{{ selectedServiceIds.length > 1 ? 's' : '' }}
        </div>
      </div>

      <!-- Étapes 2 et 3 inchangées -->
      <div v-else-if="step === 2" class="flex flex-col gap-6">
        <!-- Contenu étape 2 inchangé -->
        <h3 class="text-xl font-semibold">Dites m'en plus :</h3>
        <textarea
          v-model="formData.description"
          rows="4"
          placeholder="Décrivez votre projet ou intervention..."
          class="border rounded p-3 w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
        ></textarea>
        <div>
          <label class="block mb-1 font-medium">Date désirée (optionnel)</label>
          <input
            type="date"
            v-model="formData.desiredDate"
            class="border rounded p-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
      </div>

      <div v-else-if="step === 3" class="flex flex-col gap-6">
        <!-- Contenu étape 3 inchangé -->
        <h3 class="text-xl font-semibold">Parlez-moi de vous :</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block mb-1 font-medium">Nom *</label>
            <input
              type="text"
              v-model="formData.name"
              class="border rounded p-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <div>
            <label class="block mb-1 font-medium">Téléphone *</label>
            <input
              type="text"
              v-model="formData.phone"
              class="border rounded p-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <div>
            <label class="block mb-1 font-medium">Email *</label>
            <input
              type="email"
              v-model="formData.email"
              class="border rounded p-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
          <div>
            <label class="block mb-1 font-medium">Adresse (optionnel)</label>
            <input
              type="text"
              v-model="formData.address"
              class="border rounded p-2 w-full focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Barre de boutons fixe en bas du modal -->
    <div class="p-6 border-t bg-gray-50 rounded-b-lg sticky bottom-0">
      <div class="flex justify-between">
        <button
          v-if="step > 1"
          class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 transition"
          @click="prevStep"
        >
          Précédent
        </button>
        <div v-else></div>

        <button
          v-if="step < 3"
          class="px-4 py-2 rounded bg-orange-500 text-white font-medium hover:bg-orange-600 transition"
          @click="nextStep"
          :disabled="step === 1 && selectedServiceIds.length === 0"
        >
          Suivant
        </button>
        <button
          v-else
          class="px-4 py-2 rounded bg-orange-500 text-white font-medium hover:bg-orange-600 transition"
          @click="submitForm"
        >
          Valider
        </button>
      </div>
    </div>
  </VueFinalModal>
</template>

<style scoped>
/* Styles inchangés */
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
  transition: opacity 0.4s cubic-bezier(0.25, 0.1, 0.25, 1), transform 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
  transform-origin: center center;
}
.vfm-scale-fade-enter-from,
.vfm-scale-fade-leave-to {
  opacity: 0;
  transform: scale(0.90);
}
</style>
