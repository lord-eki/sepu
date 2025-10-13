<script setup>
import { ref, onMounted } from "vue";

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: "",
  },
  type: {
    type: String,
    default: "text",
  },
  id: {
    type: String,
    default: null,
  },
  class: {
    type: String,
    default: "",
  },
  autofocus: {
    type: Boolean,
    default: false,
  }
});

const emit = defineEmits(["update:modelValue"]);

const input = ref(null);

onMounted(() => {
  if (props.autofocus && input.value) {
    input.value.focus();
  }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
  <input
    ref="input"
    :id="id"
    :type="type"
    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
    :class="class"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>
