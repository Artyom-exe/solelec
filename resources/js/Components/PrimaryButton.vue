<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    type: {
        type: String,
        default: "submit",
    },
    navStyle: {
        type: Boolean,
        default: false,
    },
    to: {
        type: String,
        default: null,
    },
});

const isPressed = ref(false);

const handleMouseDown = () => {
    isPressed.value = true;
};

const handleMouseUp = () => {
    isPressed.value = false;
};

const handleMouseLeave = () => {
    isPressed.value = false;
};

const handleTouchStart = () => {
    isPressed.value = true;
};

const handleTouchEnd = () => {
    isPressed.value = false;
};

const handleClick = (event) => {
    const button = event.currentTarget;
    button.blur();

    // Reset l'état après un délai court pour s'assurer qu'il revient à l'état initial
    setTimeout(() => {
        isPressed.value = false;
    }, 100);

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
            'relative flex justify-center items-center md:px-6 px-4 border border-[#FF8C42] rounded-md font-[500] md:text-base text-sm font-inter whitespace-nowrap',
            'transition-all duration-300 ease-in-out',
            'w-full md:w-auto',
            navStyle ? 'py-2' : 'py-[10px]',
            // État par défaut
            !isPressed ? 'bg-[#FF8C42] text-white' : '',
            // État pressé (pour mobile et desktop)
            isPressed ? 'bg-transparent text-[#FF8C42] border-[#FF8C42]' : '',
            // Hover uniquement pour desktop (non tactile)
            '[@media(hover:hover)]:hover:bg-transparent [@media(hover:hover)]:hover:text-[#FF8C42] [@media(hover:hover)]:hover:border-[#FF8C42]',
            // Focus pour accessibilité
            'focus:outline-none focus:ring-2 focus:ring-[#FF8C42] focus:ring-opacity-50',
        ]"
        @click="handleClick"
        @mousedown="handleMouseDown"
        @mouseup="handleMouseUp"
        @mouseleave="handleMouseLeave"
        @touchstart="handleTouchStart"
        @touchend="handleTouchEnd"
    >
        <slot />
    </button>
</template>
