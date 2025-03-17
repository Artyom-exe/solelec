<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="flex justify-center items-center px-6 py-[10px] border border-white/20 rounded-md font-[500] text-base font-inter text-white">
        <slot />
    </button>
</template>
