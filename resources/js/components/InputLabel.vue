<script setup lang="ts">

</script>

<template>
    <label v-bind="$attrs" :for="for" class="block text-sm font-medium text-gray-700">
        <span v-if="value">{{ value }}</span>
        <slot v-else />
    </label>
</template>
 