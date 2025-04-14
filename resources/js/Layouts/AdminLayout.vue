<script setup>
import NavBarAdmin from "@/Components/NavBarAdmin.vue";
</script>
<template>
    <NavBarAdmin />

    <main class="flex-grow">
        <slot />
    </main>
</template>
