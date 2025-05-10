<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
    type: {
        type: String,
        default: "button",
    },
    variant: {
        type: String,
        default: "default", // 'default' ou 'dark'
    },
    to: {
        type: String,
        default: null,
    },
});

const handleClick = (event) => {
    const button = event.currentTarget;
    button.blur();

    if (props.to) {
        const isExternal = props.to.startsWith("http");
        if (isExternal) {
            window.open(props.to, "_blank");
        } else {
            router.visit(props.to);
        }
    }
};
</script>

<template>
    <button
        :type="type"
        :class="[
            'flex justify-center items-center md:px-6 px-4 py-[10px] rounded-md font-[500] md:text-base text-sm font-inter whitespace-nowrap w-full md:w-auto',
            'transition-all duration-300 ease-in-out',
            variant === 'default'
                ? [
                      'border border-white/20 text-white',
                      'hover:bg-white/20',
                      'focus:outline-none focus:bg-white/20',
                  ]
                : [
                      'border border-[#0D070326/15] text-[#0D0703]',
                      'hover:bg-gray-200',
                      'focus:outline-none focus:bg-gray-100',
                  ],
        ]"
        @click="handleClick"
    >
        <slot />
    </button>
</template>
