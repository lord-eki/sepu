<!-- resources/js/components/ConfirmationModal.vue -->
<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
  >
    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <!-- Modal panel -->
    <div class="flex items-center justify-center min-h-screen px-4">
      <div
        class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg w-full"
      >
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">
            <slot name="title" />
          </h3>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
          <slot name="content" />
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-2">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close'])
</script>
