<script setup>
import { ref, onMounted } from "vue";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  id: {
    type: String,
    default: null,
  },
  rows: {
    type: Number,
    default: 3,
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

const textarea = ref(null);

onMounted(() => {
  if (props.autofocus && textarea.value) {
    textarea.value.focus();
  }
});

defineExpose({ focus: () => textarea.value?.focus() });
</script>

<template>
  <textarea
    ref="textarea"
    :id="id"
    :rows="rows"
    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full"
    :class="class"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
  ></textarea>
</template>
