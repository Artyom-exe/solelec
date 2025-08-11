<template>
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#2D2D2D] relative"
    >
        <!-- Background pattern overlay -->
        <div
            class="absolute inset-0 bg-gradient-to-br from-[#2D2D2D] via-[#1F1F1F] to-[#2D2D2D] opacity-90"
        ></div>

        <div class="relative z-10 mb-8">
            <slot name="logo" />
        </div>

        <div
            class="relative z-10 w-full sm:max-w-md px-6 py-8 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl shadow-2xl"
        >
            <slot />
        </div>
    </div>
</template>
