<script setup>
import { Head } from '@inertiajs/vue3';
import NavBarPublic from '@/Components/NavBarPublic.vue';
import logo from '@/Components/logo.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    title: {
        type: String,
        default: 'Solelec',
    },
});

const navItems = [
    { name: 'Accueil', route: 'accueil' },
    { name: 'Services', route: 'services' },
    { name: 'Réalisations', route: 'realisations' },
];

const aboutSubItems = [
    { name: 'Contact', anchor: '#contact', route: 'accueil' },
    { name: 'Zones d\'intervention', anchor: '#zones', route: 'accueil' }
];
</script>

<template>
    <div class="flex flex-col min-h-screen bg-white">
        <Head :title="title" />

        <NavBarPublic />

        <main class="flex-grow">
            <slot />
        </main>

        <footer class="flex py-20 px-16 flex-col items-center gap-20 bg-[#2D2D2D]">
            <div class="flex justify-between w-full items-center self-stretch">
                <div class="flex flex-col items-start gap-8">
                    <logo />
                    <div class="flex flex-col text-[#ffff] font-inter items-start gap-6 self-stretch">
                        <div class="flex flex-col items-start gap-1 self-stretch">
                            <p class="self-stretch text-base font-semibold">Adresse:</p>
                            <p class="self-stretch text-sm">Rue de Neufmoustier 4,</p>
                            <p class="self-stretch text-sm">1348 Ottignies-Louvain-la-Neuve, Belgium</p>
                        </div>
                        <div class="flex flex-col items-start gap-1 self-stretch text-[#ffff] font-inter">
                            <p class="self-stretch text-base font-semibold">Contact:</p>
                            <p class="self-stretch text-sm">
                                <a href="tel:0492510931" class="underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors">0492 51 09 31</a>
                            </p>
                            <p class="self-stretch text-sm">
                                <a href="mailto:solelec.lmbt@gmail.com" class="underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors">solelec.lmbt@gmail.com</a>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-start w-auto">
                    <ul class="flex flex-col gap-2">
                        <li v-for="item in navItems" :key="item.name">
                            <a :href="'/' + item.route" class="text-white text-sm underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors">
                                {{ item.name }}
                            </a>
                        </li>
                    </ul>
                    <ul class="flex flex-col gap-2 mt-2">
                        <li v-for="item in aboutSubItems" :key="item.name">
                            <a :href="'/' + item.route + item.anchor" class="text-white text-sm underline underline-offset-2 decoration-solid decoration-1 hover:text-[#FF8C42] transition-colors">
                                {{ item.name }}
                            </a>
                        </li>
                    </ul>
                    <PrimaryButton class="mt-4" navStyle :to="'/devis'">Devis</PrimaryButton>
                </div>
            </div>
        </footer>
    </div>
</template>
