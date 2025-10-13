<template>
  <div
    class="p-5 bg-white border rounded-xl shadow-sm hover:shadow-md transition flex flex-col justify-between"
  >
    <div>
      <h3 class="text-lg font-semibold text-blue-900">{{ title }}</h3>
      <p class="text-sm text-gray-600 mt-1">{{ description }}</p>
    </div>
    <button
      @click="$emit('generate')"
      class="mt-4 px-4 py-2 bg-orange-500 w-fit hover:bg-orange-600 text-white rounded-md font-medium shadow"
    >
      Generate Report
    </button>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string
  description: string
  color?: string
}>()

defineEmits(['generate'])
</script>

<style scoped>
button:hover{
    cursor: pointer;
}
</style>
