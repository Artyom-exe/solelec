<script setup>
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Chemin corrigé
import { Head } from '@inertiajs/vue3'; // Importation pour SEO
</script>

<template>
    <!-- Ajout des balises meta pour SEO -->
    <Head>
        <title>Solelec - Expert en électricité & énergies renouvelables</title>
        <meta name="description" content="Solutions électriques innovantes et éco-responsables pour vos projets. Expertise en électricité et énergies renouvelables par Solelec." />
        <meta name="keywords" content="électricité, énergies renouvelables, installation électrique, panneau solaire" />
    </Head>

    <header class="flex flex-col items-center gap-2 bg-[#2D2D2D] h-screen px-16">
        <div class="flex flex-col lg:flex-row items-center flex-1 self-stretch h-full w-full">
            <div class="flex flex-col pr-20 justify-center items-start gap-8 flex-1">
                <div class="flex flex-col items-start gap-6 text-white">
                    <h1 class="text-white font-poppins text-[56px] font-medium leading-[120%] tracking-[-0.56px]">S<span class="text-[#FF8C42]">o</span>lelec - Votre expert en électricité & énergies renouvelables</h1>

                    <p class="font-inter text-lg font-normal leading-[150%]">Découvrez des solutions électriques innovantes et éco-responsables. Faites confiance à notre expertise pour vos projets d'énergie renouvelable.</p>
                </div>

                <div class="flex gap-4">
                    <PrimaryButton>Demander un devis</PrimaryButton>
                    <SecondaryButton>Nous contacter</SecondaryButton>
                </div>
            </div>
            <div class="flex gap-4 flex-1 h-full overflow-hidden">
                <div class="flex flex-col w-1/2 gap-4 -translate-y-60">
                    <div class="h-[340px]">
                        <img src="/images/header/ouvrier-dos.jpg" alt="Électricien Solelec de dos travaillant sur une installation" class="w-full h-full object-cover rounded-lg" />
                    </div>
                    <div class="h-[340px]">
                        <img src="/images/header/electricien.jpg" alt="Technicien qualifié Solelec en intervention" class="w-full h-full object-cover rounded-lg" loading="lazy" />
                    </div>
                    <div class="h-[340px]">
                        <img src="/images/header/ouvrier-panneau.jpg" alt="Installation de panneau solaire par l'équipe Solelec" class="w-full h-full object-cover rounded-lg" loading="lazy" />
                    </div>
                </div>
                <div class="flex flex-col w-1/2 gap-4 -translate-y-40">
                    <div class="h-[340px]">
                        <img src="/images/header/ouvrier-tableau-electrique.jpg" alt="Expert Solelec travaillant sur un tableau électrique" class="w-full h-full object-cover rounded-lg" loading="lazy" />
                    </div>
                    <div class="h-[340px]">
                        <img src="/images/header/plan-travail.jpg" alt="Plan d'installation électrique étudié par Solelec" class="w-full h-full object-cover rounded-lg" loading="lazy" />
                    </div>
                    <div class="h-[340px]">
                        <img src="/images/header/tableau-electrique-zoom.jpg" alt="Gros plan sur tableau électrique installé par Solelec" class="w-full h-full object-cover rounded-lg" loading="lazy" />
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>
