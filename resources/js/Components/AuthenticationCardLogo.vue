<script setup>
import { Link } from "@inertiajs/vue3";
import Logo from "@/Components/logo.vue";
</script>

<template>
    <Link :href="'/'">
        <Logo />
    </Link>
</template>
