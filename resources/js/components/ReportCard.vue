<template>
    <div class="flex flex-col justify-between rounded-xl border bg-white p-5 shadow-sm transition hover:shadow-md">
        <div>
            <h3 class="text-lg font-semibold text-blue-900">{{ title }}</h3>
            <p class="mt-1 text-sm text-gray-600">{{ description }}</p>
        </div>
        <button @click="$emit('generate')" class="mt-4 w-fit rounded-md bg-orange-500 px-4 py-2 font-medium text-white shadow hover:bg-orange-600">
            Generate Report
        </button>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
    color?: string;
}>();

defineEmits(['generate']);
</script>

<style scoped>
button:hover {
    cursor: pointer;
}
</style>
