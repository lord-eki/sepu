<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
    lastBackup: Object,
    systemInfo: Object,
    recentActivity: Array,
});
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Settings Overview' }]">
        <Head title="System Settings" />

        <div class="space-y-6 p-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">System Settings Overview</h1>

            <!-- System Information -->
            <div class="rounded-xl bg-white p-6 shadow dark:bg-slate-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">System Info</h2>
                <ul class="grid gap-4 text-sm md:grid-cols-2">
                    <li><strong>PHP:</strong> {{ systemInfo.php_version }}</li>
                    <li><strong>Laravel:</strong> {{ systemInfo.laravel_version }}</li>
                    <li><strong>Database:</strong> {{ systemInfo.database_version }}</li>
                    <li><strong>Disk Used:</strong> {{ systemInfo.disk_space.percentage }}%</li>
                </ul>
            </div>

            <!-- Last Backup -->
            <div class="rounded-xl bg-white p-6 shadow dark:bg-slate-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">Last Backup</h2>
                <p v-if="lastBackup">
                    <strong>{{ lastBackup.name }}</strong> —
                    {{ new Date(lastBackup.created_at).toLocaleString() }}
                </p>
                <p v-else class="text-gray-500">No backup found.</p>
            </div>

            <!-- Settings Links -->
            <div class="rounded-xl bg-white p-6 shadow dark:bg-slate-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">Settings Categories</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <Link
                        v-for="link in links"
                        :key="link.href"
                        :href="link.href"
                        class="block rounded-lg border p-4 transition hover:bg-blue-50 dark:hover:bg-slate-700"
                    >
                        <h3 class="font-semibold">{{ link.title }}</h3>
                        <p class="text-xs text-gray-500">{{ link.desc }}</p>
                    </Link>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="rounded-xl bg-white p-6 shadow dark:bg-slate-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">Recent Activity</h2>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li v-for="log in recentActivity" :key="log.id" class="py-2 text-sm">
                        <span class="font-medium">{{ log.user?.name }}</span> — {{ log.action.replaceAll('_', ' ') }}
                        <span class="ml-2 text-gray-500">({{ new Date(log.created_at).toLocaleString() }})</span>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

<script lang="ts">
const links = [
    { title: 'General', href: route('admin.settings.general'), desc: 'Organization name, timezone, currency, etc.' },
    { title: 'Financial', href: route('admin.settings.financial'), desc: 'Interest rates, fees, and limits.' },
    { title: 'Loan', href: route('admin.settings.loan'), desc: 'Loan policies and parameters.' },
    { title: 'Notification', href: route('admin.settings.notification'), desc: 'Email, SMS, and push settings.' },
    { title: 'Security', href: route('admin.settings.security'), desc: 'Passwords, sessions, and IP restrictions.' },
    { title: 'Backup', href: route('admin.settings.backup'), desc: 'Database and file backups.' },
];
</script>
