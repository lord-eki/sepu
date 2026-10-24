<!-- resources/js/components/ConfirmationModal.vue -->
<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay -->
        <div class="bg-opacity-75 fixed inset-0 bg-gray-500 transition-opacity"></div>

        <!-- Modal panel -->
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="w-full transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:max-w-lg">
                <!-- Header -->
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        <slot name="title" />
                    </h3>
                </div>

                <!-- Body -->
                <div class="px-6 py-4">
                    <slot name="content" />
                </div>

                <!-- Footer -->
                <div class="flex justify-end space-x-2 bg-gray-50 px-6 py-4">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close']);
</script>
