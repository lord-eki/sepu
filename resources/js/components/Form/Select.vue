<template>
    <div>
        <label v-if="label" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }}
        </label>

        <select
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:ring-2 focus:ring-orange-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
        >
            <slot />
        </select>
    </div>
</template>

<script setup lang="ts">
defineProps({
    modelValue: [String, Number],
    label: String,
});

defineEmits(['update:modelValue']);
</script>
