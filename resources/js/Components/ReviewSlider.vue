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
      perPage: 4,
      gap: '32px',
      arrows: false,
      pagination: false,
      drag: false,
      autoScroll: {
        speed: 1,
      },
    }"
    :extensions="{ AutoScroll }"
    class="w-full"
  >
    <SplideSlide v-for="review in reviews" :key="review.id">
      <div class="max-w-[416px] h-[233px] flex p-8 flex-col gap-6 rounded-lg border border-white/20 bg-[#242424] text-white">
        <p class="text-yellow-500 font-bold text-lg">★ {{ review.note }}/5</p>
        <p class="italic text-gray-700">"{{ review.comment }}"</p>
        <p class="mt-2 text-sm text-gray-500">— {{ review.author }}</p>
      </div>
    </SplideSlide>
  </Splide>
</template>
