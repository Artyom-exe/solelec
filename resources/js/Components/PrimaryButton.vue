<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
    navStyle: {
        type: Boolean,
        default: false,
    }
});
</script>

<template>
    <button :type="type" :class="[
        'relative flex justify-center items-center px-6 bg-[#FF8C42] rounded-md font-[500] text-base font-inter text-white border-2 border-transparent',
        'transition-all duration-300 ease-in-out',
        'hover:bg-transparent hover:text-[#FF8C42] hover:border-[#FF8C42]',
        'focus:outline-none focus:bg-transparent focus:text-[#FF8C42] focus:border-[#FF8C42]',
        navStyle ? 'py-2' : 'py-[10px]'
    ]">
        <slot />
    </button>
</template>
