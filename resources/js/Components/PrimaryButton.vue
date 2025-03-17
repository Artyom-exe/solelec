<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="flex justify-center items-center px-6 py-[10px] bg-[#FF8C42] border border-[#FF8C42] rounded-md font-[500] text-base font-inter text-white">
        <slot />
    </button>
</template>
