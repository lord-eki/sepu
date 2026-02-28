<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue'
import { Link } from '@inertiajs/vue3'

defineProps<{
    title?: string
    description?: string
}>()
</script>

<template>
  <!-- Container -->
  <div class="relative flex min-h-screen items-center justify-center
              bg-gradient-to-br from-slate-50 via-white to-blue-50
              px-6 py-12 overflow-hidden">

    <!-- Decorative Blobs -->
    <div class="absolute -top-32 -right-32 w-[400px] h-[400px]
                bg-blue-200 rounded-full blur-[120px] opacity-40
                pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-[400px] h-[400px]
                bg-orange-200 rounded-full blur-[120px] opacity-40
                pointer-events-none"></div>

    <!-- Glass Card -->
    <div class="relative w-full max-w-md
                bg-white/80 backdrop-blur-xl
                border border-gray-200
                rounded-3xl
                shadow-2xl
                p-8 md:p-10
                space-y-8">

      <!-- Logo + Header -->
      <div class="flex flex-col items-center gap-4">
        <Link :href="route('home')"
              class="flex items-center justify-center
                     w-16 h-16 rounded-2xl
                     bg-blue-600/10 p-1 text-white
                     shadow-sm shadow-blue-100
                     transition-transform hover:scale-105">

          <AppLogoIcon class="size-6 fill-current" />
        </Link>

        <div class="space-y-2 text-center">
          <h1 v-if="title" class="text-2xl font-bold text-gray-900">
            {{ title }}
          </h1>
          <p v-if="description" class="text-sm text-gray-500 leading-relaxed">
            {{ description }}
          </p>
        </div>
      </div>

      <!-- Slot Content (Login/Register Form) -->
      <div>
        <slot />
      </div>

    </div>
  </div>
</template>