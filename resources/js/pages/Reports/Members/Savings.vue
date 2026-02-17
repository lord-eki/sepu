<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { format } from 'date-fns'
import { computed, ref } from 'vue'

const props = defineProps<{
    members: Array<{
        member_id: number
        membership_id: string
        name: string
        savings_balance: number
        account_number?: string
        last_transaction?: string
    }>
    summary: {
        total_members: number
        total_savings: number
        average_savings: number
        highest_savings: number
    }
}>()

const breadcrumbs = [
    { title: 'Member Reports', href: route('reports.membersReport.index') },
    { title: 'Member Savings' },
]

const search = ref('')

const filteredMembers = computed(() => {
    if (!search.value) return props.members

    return props.members.filter(member =>
        member.name.toLowerCase().includes(search.value.toLowerCase()) ||
        member.membership_id.toLowerCase().includes(search.value.toLowerCase())
    )
})

function formatCurrency(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(value ?? 0)
}

function formatDate(date: string | undefined) {
    if (!date) return '-'
    try {
        return format(new Date(date), 'dd MMM yyyy')
    } catch {
        return date
    }
}
</script>

<template>
<Head title="Member Savings" />

<AppLayout title="Member Savings" :breadcrumbs="breadcrumbs">
<!-- Page Header -->
    <div class="mx-6 mt-6 mb-4">
        <h1 class="text-2xl font-bold text-darkblue-700 dark:text-white">
            Member Savings
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Overview of all member savings balances
        </p>
    </div>


    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mx-6 mt-6 mb-8">

        <div class="rounded-2xl bg-white dark:bg-slate-900 p-5 shadow-md border border-slate-200 dark:border-slate-700">
            <p class="text-xs uppercase tracking-wide text-slate-500">Total Members</p>
            <p class="text-2xl font-bold text-darkblue-700 dark:text-white mt-1">
                {{ summary.total_members }}
            </p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 p-5 shadow-md border border-slate-200 dark:border-slate-700">
            <p class="text-xs uppercase tracking-wide text-slate-500">Total Savings</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">
                {{ formatCurrency(summary.total_savings) }}
            </p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 p-5 shadow-md border border-slate-200 dark:border-slate-700">
            <p class="text-xs uppercase tracking-wide text-slate-500">Average Savings</p>
            <p class="text-2xl font-bold text-darkblue-700 dark:text-white mt-1">
                {{ formatCurrency(summary.average_savings) }}
            </p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-900 p-5 shadow-md border border-slate-200 dark:border-slate-700">
            <p class="text-xs uppercase tracking-wide text-slate-500">Highest Savings</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">
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
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 
                       bg-white dark:bg-slate-900 
                       px-4 py-2.5 text-sm 
                       focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
        </div>
    </div>

    <!-- Members Table -->
    <div class="mx-6 mb-10 rounded-2xl border border-slate-200 dark:border-slate-700 
                bg-white dark:bg-slate-900 shadow-md overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm table-auto">
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
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                            No member savings found.
                        </td>
                    </tr>

                    <tr
                        v-for="member in filteredMembers"
                        :key="member.member_id"
                        class="hover:bg-orange-50 dark:hover:bg-slate-800 transition"
                    >
                        <td class="px-4 py-3 font-medium text-darkblue-700 dark:text-white">
                            {{ member.name }}
                        </td>

                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ member.membership_id }}
                        </td>

                        <td class="px-4 py-3 font-semibold text-orange-600 whitespace-nowrap">
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
