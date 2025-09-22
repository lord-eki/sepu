<script setup>
const props = defineProps({
  for: {
    type: String,
    required: false,
  },
  value: {
    type: String,
    required: false,
  }
})
</script>

<template>
  <label
    v-bind="$attrs"
    :for="for"
    class="block font-medium text-sm text-gray-700"
  >
    <!-- If a `value` prop is passed, render it -->
    <span v-if="value">{{ value }}</span>
    <!-- Otherwise, render the slot content -->
    <slot v-else />
  </label>
</template>
