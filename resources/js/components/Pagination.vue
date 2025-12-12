<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  data: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
    <div class="flex max-sm:flex-col items-center justify-between">

      <!-- Results Info -->
      <div class="text-sm text-gray-700 dark:text-gray-300">
        Showing {{ data.from }} to {{ data.to }} of {{ data.total }} results
      </div>

      <!-- Pagination Links -->
      <div class="flex space-x-1 mt-2 max-sm:mt-3">
        <template v-for="link in data.links" :key="link.label">

          <!-- Clickable links -->
          <Link
            v-if="link.url"
            :href="link.url"
            v-html="link.label"
            :class="[
              'px-3 py-2 text-sm border rounded-md transition-colors duration-200',

              link.active
                ? 'bg-blue-900 text-white border-blue-900 dark:bg-blue-700 dark:border-blue-700'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 \
                  dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 \
                  dark:hover:bg-gray-700'
            ]"
          />

          <!-- Disabled links -->
          <span
            v-else
            v-html="link.label"
            class="px-3 py-2 text-sm border rounded-md cursor-not-allowed
                   bg-gray-100 text-gray-400 border-gray-300
                   dark:bg-gray-800 dark:text-gray-500 dark:border-gray-600"
          />
        </template>
      </div>
    </div>
  </div>
</template>
