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
  <div class="px-6 py-4 border-t border-gray-200">
    <div class="flex items-center justify-between">
      <!-- Results Info -->
      <div class="text-sm text-gray-700">
        Showing {{ data.from }} to {{ data.to }} of {{ data.total }} results
      </div>

      <!-- Pagination Links -->
      <div class="flex space-x-1">
        <template v-for="link in data.links" :key="link.label">
          <Link
            v-if="link.url"
            :href="link.url"
            v-html="link.label"
            :class="[
              'px-3 py-2 text-sm border rounded-md',
              link.active
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
            ]"
          />
          <span
            v-else
            v-html="link.label"
            class="px-3 py-2 text-sm border rounded-md bg-gray-100 text-gray-400 border-gray-300 cursor-not-allowed"
          />
        </template>
      </div>
    </div>
  </div>
</template>
