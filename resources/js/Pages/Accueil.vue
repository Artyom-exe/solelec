<script setup>
import SecondaryButton from '@/Components/SecondaryButton.vue';
import primaryButton from '../Components/PrimaryButton.vue';
</script>

<template>
    <header class="flex flex-col items-center gap-2 bg-[#2D2D2D] min-h-screen px-16">
        <div class="flex items-center flex-1 self-stretch">
            <div class="flex flex-col pr-20 justify-center items-start gap-8 flex-1">
                <div class="flex flex-col items-start gap-6 text-white">
                    <h1 class="text-white font-poppins text-[56px] font-medium leading-[120%] tracking-[-0.56px]">S<span class="text-[#FF8C42]">o</span>lelec - Votre expert en électricité & énergies renouvelables</h1>

                    <p class="font-inter text-lg font-normal leading-[150%]">Découvrez des solutions électriques innovantes et éco-responsables. Faites confiance à notre expertise pour vos projets d'énergie renouvelable.</p>
                </div>

                <div class="flex gap-4">
                    <primaryButton>Demander un devis</primaryButton>
                    <SecondaryButton>Nous contacter</SecondaryButton>
                </div>
            </div>
            <div class="flex gap-4 flex-1">
                <div class="flex flex-col w-1/2 gap-4">
                </div>
                <div class="flex flex-col w-1/2 gap-4">
                </div>
            </div>
        </div>
    </header>
</template>
