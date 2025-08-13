<script setup>
import { router } from "@inertiajs/vue3";
import { ref } from "vue";

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
            'flex justify-center items-center md:px-6 px-4 py-[10px] rounded-md font-[500] md:text-base text-sm font-inter whitespace-nowrap w-full md:w-auto',
            'transition-all duration-300 ease-in-out',
            variant === 'default'
                ? [
                      'border border-white/20 text-white',
                      // État par défaut
                      !isPressed ? '' : '',
                      // État pressé
                      isPressed ? 'bg-white/20' : '',
                      // Hover uniquement pour desktop (non tactile)
                      '[@media(hover:hover)]:hover:bg-white/20',
                      // Focus pour accessibilité
                      'focus:outline-none focus:ring-2 focus:ring-white/50',
                  ]
                : [
                      'border border-[#0D070326/15] text-[#0D0703]',
                      // État par défaut
                      !isPressed ? '' : '',
                      // État pressé
                      isPressed ? 'bg-gray-200' : '',
                      // Hover uniquement pour desktop (non tactile)
                      '[@media(hover:hover)]:hover:bg-gray-200',
                      // Focus pour accessibilité
                      'focus:outline-none focus:ring-2 focus:ring-gray-300',
                  ],
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
