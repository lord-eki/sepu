<template>
  <div class="space-y-1">
    <label v-if="label" class="text-sm font-medium text-slate-700 dark:text-slate-300">
      {{ label }}
    </label>

    <input
      v-bind="$attrs"
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="w-full rounded-lg border px-3 py-2
             bg-white dark:bg-slate-800
             text-slate-900 dark:text-white
             border-slate-300 dark:border-slate-600
             focus:outline-none focus:ring-2 focus:ring-blue-500"
    />
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue: string | number
  label?: string
  type?: string
}>()

defineEmits(['update:modelValue'])
</script>
