<script setup>
import { Splide, SplideSlide } from '@splidejs/vue-splide'
import { AutoScroll } from '@splidejs/splide-extension-auto-scroll'
import { ref, onMounted } from 'vue'
import axios from 'axios'

const reviews = ref([])

onMounted(async () => {
  const res = await axios.get('customer-reviews')
  reviews.value = res.data.filter(r => r.comment?.length > 0)
})
</script>

<template>
  <Splide
    :options="{
      type: 'loop',
      perPage: 3,
      gap: '32px',
      arrows: false,
      pagination: false,
      drag: false,
      autoScroll: { speed: 1 },
      breakpoints: {
        1280: { perPage: 2, gap: '1.5rem' },
        768: { perPage: 1, gap: '1rem' },
      },
    }"
    :extensions="{ AutoScroll }"
    class="w-full"
  >
    <SplideSlide v-for="review in reviews" :key="review.id" class="w-auto">
      <div class="h-[233px] flex p-8 flex-col justify-between gap-6 rounded-lg border border-white/20 bg-[#242424] text-white shadow-md">
        <!-- Étoiles -->
        <div class="text-orange-400 text-xl flex">
          <span v-for="n in review.note" :key="n">
            <img src="/assets/icons/vector.svg" alt="star" class="w-5 h-5 mr-1" />
          </span>
        </div>
        <!-- Commentaire -->
        <p class="font-inter text-base line-clamp-2">
          "{{ review.comment }}"
        </p>
        <!-- Auteur -->
        <p class="mt-2 text-sm text-gray-400">— {{ review.author }}</p>
      </div>
    </SplideSlide>
  </Splide>
</template>
