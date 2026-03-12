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

        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="max-w-2xl mx-auto space-y-6">
                <!-- Heading Section -->
                <HeadingSmall title="Appearance settings" description="Customize your account’s look and feel" />

                <!-- Card Container for Tabs -->
                <div
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-md rounded-xl p-6">
                    <AppearanceTabs />
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>