<template>
  <div
    v-if="show"
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
  >
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-lg">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">{{ title }}</h3>
        <button @click="$emit('close')" class="text-gray-500 hover:text-black">✕</button>
      </div>

      <div class="modal-body">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  title: String,
})
</script>
