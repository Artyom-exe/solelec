<script setup lang="ts">
import { ref, reactive } from 'vue'
import { VueFinalModal } from 'vue-final-modal'
import 'vue-final-modal/style.css'

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

// Données du formulaire regroupant les infos pour le devis et le client
const formData = reactive({
  services: [] as string[], // tableau des services sélectionnés
  description: '',
  desiredDate: '',
  name: '',
  phone: '',
  email: '',
  address: '',
})

// Fonctions de navigation entre les étapes
const nextStep = () => {
  if (step.value < 3) step.value++
}
const prevStep = () => {
  if (step.value > 1) step.value--
}

// Fonction appelée au clic sur "Valider"
// Elle émet l'événement "submit" avec les données du formulaire
const submitForm = () => {
  // Ici, tu peux éventuellement ajouter une validation locale avant d'émettre
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
    content-class="bg-white w-full max-w-4xl rounded-lg p-6 space-y-6 shadow-lg relative"
  >
    <!-- Titre du modal -->
    <h2 v-if="title" class="text-2xl font-bold mb-4">{{ title }}</h2>

    <!-- Étape 1 : Choix du/des service(s) -->
    <div v-if="step === 1" class="flex flex-col gap-6">
      <h3 class="text-xl font-semibold">Je désire :</h3>
      <div class="flex flex-col sm:flex-row gap-4">
        <!-- Pour chaque service proposé, on affiche une carte avec checkbox -->
        <label
          v-for="srv in ['Panneaux photovoltaïques', 'Mise en conformité électrique', 'Bornes de recharge']"
          :key="srv"
          class="flex-1 border rounded-lg p-4 cursor-pointer hover:shadow transition"
        >
          <input
            type="checkbox"
            class="mr-2"
            :value="srv"
            v-model="formData.services"
          />
          <span class="font-medium">{{ srv }}</span>
          <p class="text-sm text-gray-500 mt-2">
            Description rapide du service.
          </p>
        </label>
      </div>
      <div class="flex justify-end gap-2 mt-4">
        <button
          class="px-4 py-2 rounded bg-orange-500 text-white font-medium hover:bg-orange-600 transition"
          @click="nextStep"
        >
          Suivant
        </button>
      </div>
    </div>

    <!-- Étape 2 : Description du projet -->
    <div v-else-if="step === 2" class="flex flex-col gap-6">
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
      <div class="flex justify-between mt-4">
        <button
          class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 transition"
          @click="prevStep"
        >
          Précédent
        </button>
        <button
          class="px-4 py-2 rounded bg-orange-500 text-white font-medium hover:bg-orange-600 transition"
          @click="nextStep"
        >
          Suivant
        </button>
      </div>
    </div>

    <!-- Étape 3 : Coordonnées du client -->
    <div v-else-if="step === 3" class="flex flex-col gap-6">
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
      <div class="flex justify-between mt-4">
        <button
          class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 transition"
          @click="prevStep"
        >
          Précédent
        </button>
        <button
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
/* Transitions overlay */
.vfm-fade-enter-active,
.vfm-fade-leave-active {
  transition: opacity 0.3s ease;
}
.vfm-fade-enter-from,
.vfm-fade-leave-to {
  opacity: 0;
}

/* Transitions contenu : scale et fade */
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

/* Pour éviter que le contenu du modal déborde */
.vfm__content {
  max-height: 90vh;
  overflow-y: auto;
}
</style>
