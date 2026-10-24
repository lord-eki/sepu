<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { ref } from 'vue';

const props = defineProps<{
    members: any;
    summary: {
        total_members: number;
        active_members: number;
        inactive_members: number;
        suspended_members: number;
        new_members_this_month: number;
    };
    filters: {
        status?: string;
        county?: string;
        start_date?: string;
        end_date?: string;
        search?: string;
    };
}>();

const status = ref(props.filters.status || '');
const county = ref(props.filters.county || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const search = ref(props.filters.search || '');

const breadcrumbs = [{ title: 'Member Reports', href: route('reports.membersReport.index') }, { title: 'Member Register' }];

function applyFilters() {
    router.get(
        route('reports.members.register'),
        {
            status: status.value || undefined,
            county: county.value || undefined,
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
            search: search.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function onEnterKey(event: KeyboardEvent) {
    if (event.key === 'Enter') applyFilters();
}

function formatDate(date: string | null) {
    if (!date) return '-';
    try {
        return format(new Date(date), 'dd MMM yyyy');
    } catch {
        return date;
    }
}
</script>

<template>
    <Head title="Member Register" />

    <AppLayout title="Member Register" :breadcrumbs="breadcrumbs">
        <!-- HEADER -->
        <div class="mx-8 mt-8 mb-8">
            <h1 class="text-3xl font-bold text-[#0a2342] dark:text-white">Member Register</h1>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Manage, search and filter registered members</p>
        </div>

        <!-- STAT CARDS -->
        <div class="mx-8 mb-10 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-5">
            <div
                class="rounded-xl border border-orange-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-orange-500/30 dark:bg-[#0a2342]"
            >
                <p class="text-sm text-orange-500">Total Members</p>
                <p class="mt-3 text-2xl font-bold text-[#0a2342] dark:text-white">
                    {{ summary.total_members }}
                </p>
            </div>

            <div
                class="rounded-xl border border-orange-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-orange-500/30 dark:bg-[#0a2342]"
            >
                <p class="text-sm text-orange-500">Active</p>
                <p class="mt-3 text-2xl font-bold text-orange-500">
                    {{ summary.active_members }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-blue-900 dark:bg-[#0a2342]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Inactive</p>
                <p class="mt-3 text-2xl font-bold text-gray-600 dark:text-gray-300">
                    {{ summary.inactive_members }}
                </p>
            </div>

            <div class="rounded-xl border border-red-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-red-500/30 dark:bg-[#0a2342]">
                <p class="text-sm text-red-500">Suspended</p>
                <p class="mt-3 text-2xl font-bold text-red-500">
                    {{ summary.suspended_members }}
                </p>
            </div>

            <div
                class="rounded-xl border border-orange-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-orange-500/30 dark:bg-[#0a2342]"
            >
                <p class="text-sm text-orange-500">New This Month</p>
                <p class="mt-3 text-2xl font-bold text-orange-500">
                    {{ summary.new_members_this_month }}
                </p>
            </div>
        </div>

        <!-- SEARCH + FILTERS -->
        <div class="mx-8 mb-8 rounded-xl border border-blue-100 bg-white p-6 shadow-sm dark:border-blue-900 dark:bg-[#0a2342]">
            <!-- SEARCH -->
            <div class="mb-5">
                <input
                    v-model="search"
                    @keydown.enter="onEnterKey"
                    type="text"
                    placeholder="Search member or Membership ID..."
                    class="w-full rounded-lg border border-orange-300 bg-white px-4 py-2.5 text-sm text-[#0a2342] focus:border-orange-500 focus:ring-2 focus:ring-orange-500/30 dark:bg-blue-950 dark:text-white"
                />
            </div>

            <!-- FILTERS -->
            <div class="flex flex-wrap items-end gap-4">
                <select
                    v-model="status"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-[#0a2342] dark:bg-blue-950 dark:text-white"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>

                <input
                    type="text"
                    v-model="county"
                    placeholder="County"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-[#0a2342] dark:bg-blue-950 dark:text-white"
                />

                <input
                    type="date"
                    v-model="startDate"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-[#0a2342] dark:bg-blue-950 dark:text-white"
                />

                <input
                    type="date"
                    v-model="endDate"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-[#0a2342] dark:bg-blue-950 dark:text-white"
                />

                <button
                    @click="applyFilters"
                    class="rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-orange-600 hover:shadow-md"
                >
                    Apply
                </button>
            </div>
        </div>

        <!-- TABLE -->
        <div class="mx-8 mb-12 overflow-hidden rounded-xl border border-blue-100 bg-white shadow-sm dark:border-blue-900 dark:bg-[#0a2342]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-[#0a2342] text-white">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Member</th>
                            <th class="px-6 py-4 text-left font-semibold">Membership ID</th>
                            <th class="px-6 py-4 text-left font-semibold">Phone</th>
                            <th class="px-6 py-4 text-left font-semibold">County</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-left font-semibold">Joined</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-blue-900">
                        <tr v-for="member in members.data" :key="member.id" class="transition hover:bg-orange-50 dark:hover:bg-blue-950">
                            <td class="px-6 py-4 font-medium text-[#0a2342] dark:text-white">{{ member.first_name }} {{ member.last_name }}</td>

                            <td class="px-6 py-4">
                                <span class="rounded-md bg-orange-100 px-3 py-1 font-mono text-xs text-orange-600">
                                    {{ member.membership_id }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ member.user?.phone ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ member.county }}
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    class="rounded-full px-3 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-orange-100 text-orange-600': member.membership_status === 'active',
                                        'bg-gray-100 text-gray-600': member.membership_status === 'inactive',
                                        'bg-red-100 text-red-600': member.membership_status === 'suspended',
                                    }"
                                >
                                    {{ member.membership_status }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                {{ formatDate(member.membership_date) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
