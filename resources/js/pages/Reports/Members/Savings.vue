<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { computed, ref } from 'vue';

const props = defineProps<{
    members: Array<{
        member_id: number;
        membership_id: string;
        name: string;
        savings_balance: number;
        account_number?: string;
        last_transaction?: string;
    }>;
    summary: {
        total_members: number;
        total_savings: number;
        average_savings: number;
        highest_savings: number;
    };
}>();

const breadcrumbs = [{ title: 'Member Reports', href: route('reports.membersReport.index') }, { title: 'Member Savings' }];

const search = ref('');

const filteredMembers = computed(() => {
    if (!search.value) return props.members;

    return props.members.filter(
        (member) =>
            member.name.toLowerCase().includes(search.value.toLowerCase()) || member.membership_id.toLowerCase().includes(search.value.toLowerCase()),
    );
});

function formatCurrency(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(value ?? 0);
}

function formatDate(date: string | undefined) {
    if (!date) return '-';
    try {
        return format(new Date(date), 'dd MMM yyyy');
    } catch {
        return date;
    }
}
</script>

<template>
    <Head title="Member Savings" />

    <AppLayout title="Member Savings" :breadcrumbs="breadcrumbs">
        <!-- Page Header -->
        <div class="mx-6 mt-6 mb-4">
            <h1 class="text-darkblue-700 text-2xl font-bold dark:text-white">Member Savings</h1>
            <p class="mt-1 text-sm text-slate-500">Overview of all member savings balances</p>
        </div>

        <!-- Summary Cards -->
        <div class="mx-6 mt-6 mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-md dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs tracking-wide text-slate-500 uppercase">Total Members</p>
                <p class="text-darkblue-700 mt-1 text-2xl font-bold dark:text-white">
                    {{ summary.total_members }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-md dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs tracking-wide text-slate-500 uppercase">Total Savings</p>
                <p class="mt-1 text-2xl font-bold text-orange-600">
                    {{ formatCurrency(summary.total_savings) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-md dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs tracking-wide text-slate-500 uppercase">Average Savings</p>
                <p class="text-darkblue-700 mt-1 text-2xl font-bold dark:text-white">
                    {{ formatCurrency(summary.average_savings) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-md dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs tracking-wide text-slate-500 uppercase">Highest Savings</p>
                <p class="mt-1 text-2xl font-bold text-orange-600">
                    {{ formatCurrency(summary.highest_savings) }}
                </p>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="mx-6 mb-4">
            <div class="relative max-w-md">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name or Membership ID..."
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900"
                />
            </div>
        </div>

        <!-- Members Table -->
        <div class="mx-6 mb-10 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md dark:border-slate-700 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-sm">
                    <thead class="bg-darkblue-700 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Member</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Membership ID</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Savings Balance</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Account Number</th>
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap">Last Transaction</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-if="filteredMembers.length === 0">
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">No member savings found.</td>
                        </tr>

                        <tr v-for="member in filteredMembers" :key="member.member_id" class="transition hover:bg-orange-50 dark:hover:bg-slate-800">
                            <td class="text-darkblue-700 px-4 py-3 font-medium dark:text-white">
                                {{ member.name }}
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ member.membership_id }}
                            </td>

                            <td class="px-4 py-3 font-semibold whitespace-nowrap text-orange-600">
                                {{ formatCurrency(member.savings_balance) }}
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ member.account_number ?? '-' }}
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ formatDate(member.last_transaction) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
