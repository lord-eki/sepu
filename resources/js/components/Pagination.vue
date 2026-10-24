<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps({
    data: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
        <div class="flex items-center justify-between max-sm:flex-col">
            <div class="text-sm text-gray-700 dark:text-gray-300">Showing {{ data.from }} to {{ data.to }} of {{ data.total }} results</div>

            <div class="mt-2 flex space-x-1 max-sm:mt-3">
                <template v-for="link in data.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :class="[
                            'rounded-md border px-3 py-2 text-sm transition-colors duration-200',

                            link.active
                                ? 'border-blue-900 bg-blue-900 text-white dark:border-blue-700 dark:bg-blue-700'
                                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700',
                        ]"
                    />

                    <span
                        v-else
                        v-html="link.label"
                        class="cursor-not-allowed rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-500"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
