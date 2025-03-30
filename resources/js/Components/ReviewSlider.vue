<script setup>
import { Splide, SplideSlide } from '@splidejs/vue-splide'
import { AutoScroll } from '@splidejs/splide-extension-auto-scroll'
import { ref, onMounted } from 'vue'
import axios from 'axios'

const reviews = ref([])

onMounted(async () => {
  const res = await axios.get('customer-reviews')
  reviews.value = res.data
})
</script>

<template>
  <Splide
    :options="{
      type: 'loop',
      perPage: 1,
      arrows: false,
      pagination: false,
      drag: false,
      autoScroll: {
        speed: 1,
      },
    }"
    :extensions="{ AutoScroll }"
    class="max-w-xl mx-auto"
  >
    <SplideSlide v-for="review in reviews" :key="review.id">
      <div class="p-6 bg-white shadow rounded-lg">
        <p class="text-yellow-500 font-bold text-lg">★ {{ review.note }}/5</p>
        <p class="italic text-gray-700">"{{ review.comment }}"</p>
        <p class="mt-2 text-sm text-gray-500">— {{ review.author }}</p>
      </div>
    </SplideSlide>
  </Splide>
</template>
