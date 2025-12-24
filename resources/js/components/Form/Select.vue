<template>
    <div>
      <label v-if="label" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
        {{ label }}
      </label>
  
      <select
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        class="w-full rounded-lg border border-gray-300 dark:border-gray-600
               bg-white dark:bg-gray-800
               text-gray-900 dark:text-gray-100
               px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"
      >
        <slot />
      </select>
    </div>
  </template>
  
  <script setup>
  defineProps({
    modelValue: [String, Number],
    label: String,
  })
  
  defineEmits(['update:modelValue'])
  </script>
  