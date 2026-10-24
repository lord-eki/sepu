<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    notifications: Object,
    stats: Object,
    filters: Object,
});

// Local filter state
const filterType = ref(props.filters?.type || 'all');
const filterStatus = ref(props.filters?.status || 'all');
const filterChannel = ref(props.filters?.channel || 'all');

// Build query URL dynamically for Inertia navigation
const buildUrl = (type: string, status: string, channel: string) => {
    const query = [];
    if (type !== 'all') query.push(`type=${type}`);
    if (status !== 'all') query.push(`status=${status}`);
    if (channel !== 'all') query.push(`channel=${channel}`);
    return `/notifications?${query.join('&')}`;
};

// Format time difference
const timeAgo = (dateStr: string) => {
    const date = new Date(dateStr);
    const diff = Math.floor((Date.now() - date.getTime()) / 60000);
    if (diff < 60) return `${diff} min ago`;
    if (diff < 1440) return `${Math.floor(diff / 60)} hrs ago`;
    return date.toLocaleDateString();
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Notifications' }]">
        <Head title="Notifications" />

        <div class="px-6 py-4">
            <!-- Header -->
            <div class="mb-6 flex flex-col items-start justify-between sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Notifications</h1>
                    <p class="text-sm text-gray-500">View and manage your latest updates</p>
                </div>
                <Link
                    :href="route('notifications.settings')"
                    class="mt-3 rounded-lg bg-orange-500 px-4 py-2 text-white transition hover:bg-orange-600 sm:mt-0"
                >
                    Notification Settings
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <h3 class="text-sm font-medium text-gray-700">Total Notifications</h3>
                    <p class="text-2xl font-semibold text-blue-700">{{ props.stats.total }}</p>
                </div>
                <div class="rounded-xl border border-orange-100 bg-orange-50 p-4">
                    <h3 class="text-sm font-medium text-gray-700">Unread Notifications</h3>
                    <p class="text-2xl font-semibold text-orange-700">{{ props.stats.unread }}</p>
                </div>
                <div class="rounded-xl border border-green-100 bg-green-50 p-4">
                    <h3 class="text-sm font-medium text-gray-700">Types</h3>
                    <p class="text-2xl font-semibold text-green-700">{{ Object.keys(props.stats.by_type || {}).length }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex flex-col gap-3 sm:flex-row">
                <select v-model="filterType" class="rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    <option value="all">All Types</option>
                    <option value="transaction">Transaction</option>
                    <option value="loan">Loan</option>
                    <option value="dividend">Dividend</option>
                    <option value="general">General</option>
                    <option value="system">System</option>
                </select>

                <select v-model="filterStatus" class="rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    <option value="all">All Status</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                </select>

                <select v-model="filterChannel" class="rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    <option value="all">All Channels</option>
                    <option value="sms">SMS</option>
                    <option value="email">Email</option>
                    <option value="system">System</option>
                </select>

                <Link
                    :href="buildUrl(filterType, filterStatus, filterChannel)"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-white transition hover:bg-blue-700"
                >
                    Apply Filters
                </Link>
            </div>

            <!-- Notifications Table -->
            <div class="overflow-hidden rounded-xl bg-white shadow">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Title</th>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Channel</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in props.notifications.data"
                            :key="item.id"
                            :class="item.is_read ? 'bg-white' : 'bg-orange-50'"
                            class="border-b border-gray-100 transition hover:bg-gray-50"
                        >
                            <td class="px-6 py-3">
                                <Link :href="`/notifications/${item.id}`" class="font-medium text-blue-700 hover:underline">
                                    {{ item.title }}
                                </Link>
                                <p class="text-xs text-gray-500">{{ item.message.slice(0, 60) }}...</p>
                            </td>
                            <td class="px-6 py-3 capitalize">{{ item.type }}</td>
                            <td class="px-6 py-3 uppercase">{{ item.channel }}</td>
                            <td class="px-6 py-3">
                                <span :class="item.is_read ? 'text-green-700' : 'text-orange-600'" class="font-medium">
                                    {{ item.is_read ? 'Read' : 'Unread' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ timeAgo(item.created_at) }}</td>
                            <td class="px-6 py-3 text-right">
                                <Link :href="`/notifications/${item.id}`" class="text-sm font-medium text-blue-600 hover:underline"> View </Link>
                            </td>
                        </tr>

                        <tr v-if="!props.notifications.data.length">
                            <td colspan="6" class="py-6 text-center text-gray-500">No notifications found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
