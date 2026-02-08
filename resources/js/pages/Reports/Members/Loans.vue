<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import { format } from 'date-fns'

const props = defineProps<{
    members: Array<{
        member_id: number
        membership_id: string
        name: string
        total_loans: number
        total_disbursed: number
        total_outstanding: number
        loans: Array<{
            loan_number: string
            product_name: string
            disbursed_amount: number
            outstanding_balance: number
            status: string
            days_in_arrears: number
        }>
    }>
    summary: {
        total_members: number
        total_loans: number
        total_disbursed: number
        total_outstanding: number
    }
    filters: {
        status?: string
    }
}>()

const breadcrumbs = [
    { title: 'Member Reports', href: route('reports.membersReport.index') },
    { title: 'Member Loans' },
]

function formatCurrency(value: number) {
    return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(value ?? 0)
}
</script>

<template>

    <Head title="Member Loans" />

    <AppLayout title="Member Loans" :breadcrumbs="breadcrumbs">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-5 mx-5 mb-8">
            <div class="rounded-2xl bg-blue-50 dark:bg-blue-900 p-4 border border-blue-200 dark:border-blue-800">
                <p class="text-sm text-blue-800 dark:text-blue-300">Members with Loans</p>
                <p class="text-xl font-bold text-blue-900 dark:text-white">{{ summary.total_members }}</p>
            </div>
            <div
                class="rounded-2xl bg-purple-50 dark:bg-purple-900 p-4 border border-purple-200 dark:border-purple-800">
                <p class="text-sm text-purple-800 dark:text-purple-300">Total Loans</p>
                <p class="text-xl font-bold text-purple-600">{{ summary.total_loans }}</p>
            </div>
            <div class="rounded-2xl bg-green-50 dark:bg-green-900 p-4 border border-green-200 dark:border-green-800">
                <p class="text-sm text-green-800 dark:text-green-300">Total Disbursed</p>
                <p class="text-xl font-bold text-green-600">{{ formatCurrency(summary.total_disbursed) }}</p>
            </div>
            <div
                class="rounded-2xl bg-orange-50 dark:bg-orange-900 p-4 border border-orange-200 dark:border-orange-800">
                <p class="text-sm text-orange-800 dark:text-orange-300">Total Outstanding</p>
                <p class="text-xl font-bold text-orange-600">{{ formatCurrency(summary.total_outstanding) }}</p>
            </div>
        </div>

        <!-- Members Table -->
        <div
            class="overflow-x-auto rounded-lg m-5 border border-blue-200 dark:border-blue-700 bg-white dark:bg-blue-900 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-blue-50 dark:bg-blue-800 text-blue-900 dark:text-white sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left">Member</th>
                        <th class="px-4 py-3 text-left">Membership ID</th>
                        <th class="px-4 py-3 text-left">Loans Count</th>
                        <th class="px-4 py-3 text-left">Total Disbursed</th>
                        <th class="px-4 py-3 text-left">Total Outstanding</th>
                        <th class="px-4 py-3 text-left">Loan Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-200 dark:divide-blue-700">
                    <tr v-if="members.length === 0" class="text-center">
                        <td colspan="6" class="px-4 py-6 text-gray-500 dark:text-gray-300">No member loans found.</td>
                    </tr>

                    <tr v-for="member in members" :key="member.member_id"
                        class="hover:bg-blue-50 dark:hover:bg-blue-800 transition-colors">
                        <td class="px-4 py-3 font-medium">{{ member.name }}</td>
                        <td class="px-4 py-3">{{ member.membership_id }}</td>
                        <td class="px-4 py-3">{{ member.total_loans }}</td>
                        <td class="px-4 py-3 font-medium">{{ formatCurrency(member.total_disbursed) }}</td>
                        <td class="px-4 py-3 font-medium text-orange-600">{{ formatCurrency(member.total_outstanding) }}
                        </td>
                        <td class="px-4 py-3">
                            <ul class="space-y-1">
                                <li v-for="loan in member.loans" :key="loan.loan_number" class="text-xs">
                                    <span class="font-semibold">{{ loan.loan_number }}</span> -
                                    {{ loan.product_name }} |
                                    <span class="text-green-600">{{ formatCurrency(loan.disbursed_amount) }}</span> /
                                    <span class="text-orange-600">{{ formatCurrency(loan.outstanding_balance) }}</span>
                                    |
                                    <span :class="{
        'text-red-600 font-semibold': loan.status === 'defaulted',
        'text-green-600 font-semibold': loan.status === 'active',
        'text-gray-500 font-semibold': loan.status === 'closed'
    }">{{ loan.status.toUpperCase() }}</span>
                                    <span v-if="loan.days_in_arrears > 0" class="text-red-500">({{ loan.days_in_arrears
                                        }} days in arrears)</span>
                                </li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
