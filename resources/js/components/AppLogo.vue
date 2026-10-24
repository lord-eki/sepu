<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

</script>

<template>
    <div
        class="flex aspect-square size-12 items-center justify-center rounded-md transition-all duration-200 ease-linear group-data-[state=collapsed]:mx-auto group-data-[state=collapsed]:size-8"
    >
        <AppLogoIcon
            class="size-20 fill-current text-white transition-all duration-200 ease-linear group-data-[state=collapsed]:size-8 dark:text-black"
        />
    </div>

    <div
        class="ml-1 grid flex-1 text-left text-sm transition-opacity duration-200 group-data-[state=collapsed]:w-0 group-data-[state=collapsed]:overflow-hidden group-data-[state=collapsed]:opacity-0"
    >
    </div>
</template>
