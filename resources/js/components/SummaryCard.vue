<template>
    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ label }}
        </p>
        <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
            {{ value }}
        </p>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number;
}>();
</script>
