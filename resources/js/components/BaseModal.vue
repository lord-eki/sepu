<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Overlay -->
        <div class="bg-opacity-75 fixed inset-0 bg-gray-500" @click="close"></div>

        <!-- Modal -->
        <div class="z-10 w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
            <!-- Header -->
            <div class="flex items-center justify-between border-b pb-2">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ title }}
                </h3>
                <button @click="close" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <!-- Body -->
            <div class="mt-4">
                <slot />
            </div>

            <!-- Footer -->
            <div v-if="$slots.footer" class="mt-6 flex justify-end space-x-2">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
const emit = defineEmits(['close']);

const close = () => emit('close');
</script>
