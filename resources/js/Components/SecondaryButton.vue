<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'default', // 'default' ou 'dark'
    },
});
</script>

<template>
    <button :type="type" :class="[
        'flex justify-center items-center px-6 py-[10px] rounded-md font-[500] text-base font-inter',
        'transition-all duration-300 ease-in-out',
        variant === 'default' ? [
            'border border-white/20 text-white',
            'hover:bg-white/20',
            'focus:outline-none focus:bg-white/20'
        ] : [
            'border border-[#0D070326/15] text-[#0D0703]',
            'hover:bg-gray-200',
            'focus:outline-none focus:bg-gray-100'
        ]
    ]">
        <slot />
    </button>
</template>
