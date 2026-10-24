<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold">{{ title }}</h3>
                <button @click="$emit('close')" class="text-gray-500 hover:text-black">✕</button>
            </div>

            <div class="modal-body">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    show: Boolean,
    title: String,
});
</script>
