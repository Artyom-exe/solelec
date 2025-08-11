<script setup>
import { onMounted, ref } from "vue";

defineProps({
    modelValue: String,
});

defineEmits(["update:modelValue"]);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="w-full bg-white/10 border border-white/20 focus:border-[#FF8C42] focus:ring-[#FF8C42] focus:ring-1 rounded-md px-4 py-3 text-white placeholder-white/50 font-inter transition-all duration-300 backdrop-blur-sm"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    />
</template>
