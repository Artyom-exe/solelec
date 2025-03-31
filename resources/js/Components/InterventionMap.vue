<script setup>
import { onMounted, ref } from 'vue'

const mapElement = ref(null)
const map = ref(null)

const cities = [
  { name: 'Ottignies', coords: { lat: 50.6683, lng: 4.6144 } },
  { name: 'Louvain-la-Neuve', coords: { lat: 50.6689, lng: 4.6111 } },
  { name: 'Wavre', coords: { lat: 50.7172, lng: 4.6014 } },
  { name: 'Nivelles', coords: { lat: 50.5956, lng: 4.3286 } },
  { name: 'Gembloux', coords: { lat: 50.5639, lng: 4.6984 } },
  { name: 'Namur', coords: { lat: 50.4674, lng: 4.8718 } },
  { name: 'Charleroi', coords: { lat: 50.4108, lng: 4.4446 } },
  { name: 'Bruxelles', coords: { lat: 50.8503, lng: 4.3517 } },
]

const centerOn = (coords) => {
  if (map.value) {
    map.value.setCenter(coords)
    map.value.setZoom(12)
  }
}

onMounted(() => {
  window.initMap = () => {
    map.value = new google.maps.Map(mapElement.value, {
      center: cities[0].coords,
      zoom: 9,
      disableDefaultUI: true,
    })

    cities.forEach((city) => {
      new google.maps.Marker({
        position: city.coords,
        map: map.value,
        title: city.name,
      })
    })
  }

  if (window.google && window.google.maps) {
    window.initMap()
  }
})
</script>

<template>
  <artic class="flex flex-col gap-8">
    <div class="flex flex-col md:flex-row gap-8">
      <!-- Deux colonnes -->
    <div class="grid grid-cols-2 gap-6 md:w-1/2">
        <button
            v-for="city in cities"
            :key="city.name"
            @click="centerOn(city.coords)"
            class="group flex flex-col items-start gap-2 px-6 py-5 rounded-lg border border-transparent transition-all duration-300 ease-in-out hover:bg-[#f9f9f9] hover:border-[#FF8C42] hover:shadow-md"
        >
            <h3 class="text-left text-xl font-semibold text-[#0D0703] group-hover:text-[#FF8C42] transition-colors duration-300 font-poppins">
            {{ city.name }}
            </h3>
            <div class="flex gap-3 items-center">
                <p class="text-left text-sm font-medium text-gray-500 group-hover:text-[#0D0703] font-inter">
                Voir sur la carte
                </p>
                <svg
                class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1 text-[#0D0703] group-hover:text-[#FF8C42]"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 448 512"
                fill="currentColor"
                >
                <path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
                </svg>
            </div>
        </button>
    </div>

      <!-- Carte -->
      <div class="md:w-1/2 h-[440px] rounded-lg overflow-hidden shadow">
        <div ref="mapElement" class="w-full h-full"></div>
      </div>
    </div>

    <!-- Texte SEO caché (optionnel) -->
    <p class="sr-only">
      Solelec intervient en Wallonie et à Bruxelles, notamment à Ottignies, Louvain-la-Neuve, Wavre, Nivelles, Gembloux,
      Namur, Charleroi, Liège, Mons, Rixensart, Court-Saint-Étienne, Mont-Saint-Guibert, Eghezée, Sombreffe, Perwez,
      Bastogne, Spa, Marche-en-Famenne, Tournai et bien d’autres localités.
    </p>
  </artic>
</template>
