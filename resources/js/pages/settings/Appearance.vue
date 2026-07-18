<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

import AppearanceTabs from '@/components/AppearanceTabs.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { type BreadcrumbItem } from '@/types'
import { Moon } from 'lucide-vue-next'

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
                    class="overflow-hidden rounded-3xl bg-gradient-to-r from-orange-500 via-orange-600 to-amber-500 p-4 text-white shadow-xl">

                    <div class="flex items-center gap-5">

                        <div
                            class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-3xl bg-white/20 backdrop-blur">

                            <Moon class="h-6 w-6 sm:h-8 sm:w-8" />

                        </div>

                        <div>

                            <h1 class="text-xl sm:text-2xl font-bold">
                                Appearance Settings
                            </h1>

                            <p class="mt-2 text-sm text-orange-100">
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