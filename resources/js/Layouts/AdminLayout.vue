<script setup>
import NavBarAdmin from "@/Components/NavBarAdmin.vue";
</script>
<template>
    <div class="flex flex-col min-h-screen">
        <NavBarAdmin />
        <main class="flex-grow">
            <slot />
        </main>
    </div>
</template>
