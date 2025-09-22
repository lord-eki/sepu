<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="close"></div>

    <!-- Modal -->
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full z-10 p-6">
      <!-- Header -->
      <div class="flex justify-between items-center border-b pb-2">
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

<script setup>
const props = defineProps({
  show: Boolean,
  title: String,
})
const emit = defineEmits(["close"])

const close = () => emit("close")
</script>
