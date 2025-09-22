<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { useSidebar } from './utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const { toggleSidebar } = useSidebar()
</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    size="icon"
    :class="cn('h-8 w-8', props.class)"
    @click="toggleSidebar"
  >
    <!-- Two equal-length lines + centered downward triangle -->
   <svg
  xmlns="http://www.w3.org/2000/svg"
  viewBox="0 0 34 28"
  fill="none"
  stroke="currentColor"
  stroke-width="2.4"
  stroke-linecap="round"
  stroke-linejoin="round"
  class="text-blue-900 mt-0.5 !w-6 !h-6"
>
  <!-- Top horizontal line -->
  <line x1="4" y1="5" x2="30" y2="5" />
  <!-- Middle horizontal line -->
  <line x1="4" y1="11" x2="30" y2="11" />
  <!-- One long trapezium -->
  <path d="M3.5 16 L5 21 L29 21 L30.5 16 Z" fill="currentColor" stroke="none" />
</svg>



    <span class="sr-only">Toggle Sidebar</span>
  </Button>
</template>