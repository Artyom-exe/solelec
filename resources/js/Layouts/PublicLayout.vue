<script setup>
import { Head } from '@inertiajs/vue3';
import NavBarPublic from '@/Components/NavBarPublic.vue';
// import Footer from '@/Components/Footer.vue';

defineProps({
    title: {
        type: String,
        default: 'Solelec',
    },
});
</script>

<template>
    <div class="flex flex-col min-h-screen bg-white">
        <Head :title="title" />

        <NavBarPublic />

        <main class="flex-grow">
            <slot />
        </main>

        <!-- <Footer /> -->
    </div>
</template>
