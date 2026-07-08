<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

import AppearanceTabs from '@/components/AppearanceTabs.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { type BreadcrumbItem } from '@/types'

import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'

// Breadcrumb for AppLayout
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: '/settings/appearance',
    },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance Settings" />

        <SettingsLayout>
            <div class="mx-auto max-w-5xl space-y-8">

                <!-- Hero -->
                <div
                    class="overflow-hidden rounded-3xl bg-gradient-to-r from-orange-500 via-orange-600 to-amber-500 p-8 text-white shadow-xl">

                    <div class="flex items-center gap-5">

                        <div
                            class="flex h-16 sm:h-20 w-16 sm:w-20 items-center justify-center rounded-3xl bg-white/20 backdrop-blur">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 sm:h-10 w-8 sm:w-10"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20.354 15.354A9 9 0 118.646 3.646a9.003 9.003 0 0011.708 11.708z" />

                            </svg>

                        </div>

                        <div>

                            <h1 class="text-2xl sm:text-3xl font-bold">
                                Appearance Settings
                            </h1>

                            <p class="mt-2 text-orange-100">
                                Personalize the application's appearance to suit your preference.
                            </p>

                        </div>

                    </div>

                </div>

                <!-- Appearance Card -->
                <div
                    class="rounded-3xl border border-gray-200 bg-white p-8 shadow-lg transition-all dark:border-gray-700 dark:bg-gray-900">

                    <HeadingSmall
                        title="Theme Preferences"
                        description="Switch between Light, Dark, or System mode." />

                    <div class="mt-8">

                        <AppearanceTabs />

                    </div>

                </div>

            </div>
        </SettingsLayout>
    </AppLayout>
</template>