<template>
    <button
        class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none disabled:opacity-25"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>

<script setup lang="ts">
defineProps({
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>
