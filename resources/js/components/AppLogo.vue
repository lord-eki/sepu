<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import { useSidebar } from '@/components/ui/sidebar/utils'

const { state } = useSidebar()
</script>

<template>
  <div
    class="
      flex aspect-square items-center justify-center rounded-md
      transition-all duration-200 ease-linear
      size-12
      group-data-[state=collapsed]:size-8
      group-data-[state=collapsed]:mx-auto
    "
  >
    <AppLogoIcon
      class="
        fill-current transition-all duration-200 ease-linear
        size-20
        group-data-[state=collapsed]:size-8
        text-white dark:text-black
      "
    />
  </div>

  <div
    class="
      ml-1 grid flex-1 text-left text-sm
      transition-opacity duration-200
      group-data-[state=collapsed]:opacity-0
      group-data-[state=collapsed]:w-0
      group-data-[state=collapsed]:overflow-hidden
    "
  >
    <!-- <span class="truncate font-semibold">Sepu Sacco</span> -->
  </div>
</template>
