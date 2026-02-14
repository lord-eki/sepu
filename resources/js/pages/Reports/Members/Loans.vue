<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

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
}>()

const breadcrumbs = [
    { title: 'Member Reports', href: route('reports.membersReport.index') },
    { title: 'Member Loans' },
]

function formatCurrency(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(value ?? 0)
}
</script>

<template>
    <Head title="Member Loans" />

    <AppLayout title="Member Loans" :breadcrumbs="breadcrumbs">

        <!-- Page Header -->
        <div class="px-6 pt-6">
            <h1 class="text-2xl font-bold text-[#0f172a] dark:text-white">
                Member Loans Overview
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Summary and detailed breakdown of all member loan accounts
            </p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 px-6 mt-6 mb-10">

            <!-- Members -->
            <div class="rounded-2xl p-6 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-sm text-slate-500">Members with Loans</p>
                <p class="text-2xl font-bold text-[#0f172a] dark:text-white mt-2">
                    {{ summary.total_members }}
                </p>
            </div>

            <!-- Total Loans -->
            <div class="rounded-2xl p-6 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-sm text-slate-500">Total Loans</p>
                <p class="text-2xl font-bold text-[#ea580c] mt-2">
                    {{ summary.total_loans }}
                </p>
            </div>

            <!-- Disbursed -->
            <div class="rounded-2xl p-6 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-sm text-slate-500">Total Disbursed</p>
                <p class="text-2xl font-bold text-green-600 mt-2">
                    {{ formatCurrency(summary.total_disbursed) }}
                </p>
            </div>

            <!-- Outstanding -->
            <div class="rounded-2xl p-6 bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-sm text-slate-500">Total Outstanding</p>
                <p class="text-2xl font-bold text-[#ea580c] mt-2">
                    {{ formatCurrency(summary.total_outstanding) }}
                </p>
            </div>

        </div>

        <!-- Table Wrapper -->
        <div class="px-6 pb-10">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#0f172a] shadow-sm overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">

                        <!-- Table Head -->
                        <thead class="bg-[#0f172a] text-white">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Member</th>
                                <th class="px-6 py-4 text-left font-semibold">Membership ID</th>
                                <th class="px-6 py-4 text-left font-semibold">Loans</th>
                                <th class="px-6 py-4 text-left font-semibold">Disbursed</th>
                                <th class="px-6 py-4 text-left font-semibold">Outstanding</th>
                                <th class="px-6 py-4 text-left font-semibold">Loan Details</th>
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">

                            <tr v-if="members.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                    No member loans found.
                                </td>
                            </tr>

                            <tr
                                v-for="member in members"
                                :key="member.member_id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-800 transition"
                            >
                                <td class="px-6 py-5 font-semibold text-[#0f172a] dark:text-white">
                                    {{ member.name }}
                                </td>

                                <td class="px-6 py-5 text-slate-600 dark:text-slate-300">
                                    {{ member.membership_id }}
                                </td>

                                <td class="px-6 py-5">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-xs font-semibold">
                                        {{ member.total_loans }} Loans
                                    </span>
                                </td>

                                <td class="px-6 py-5 font-semibold text-green-600">
                                    {{ formatCurrency(member.total_disbursed) }}
                                </td>

                                <td class="px-6 py-5 font-semibold text-[#ea580c]">
                                    {{ formatCurrency(member.total_outstanding) }}
                                </td>

                                <!-- Loan Details -->
                                <td class="px-6 py-5">
                                    <div class="space-y-2">
                                        <div
                                            v-for="loan in member.loans"
                                            :key="loan.loan_number"
                                            class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                                        >
                                            <div class="flex flex-wrap gap-2 items-center text-xs">

                                                <span class="font-semibold">
                                                    {{ loan.loan_number }}
                                                </span>

                                                <span class="text-slate-500">
                                                    {{ loan.product_name }}
                                                </span>

                                                <span class="text-green-600 font-semibold">
                                                    {{ formatCurrency(loan.disbursed_amount) }}
                                                </span>

                                                <span class="text-[#ea580c] font-semibold">
                                                    {{ formatCurrency(loan.outstanding_balance) }}
                                                </span>

                                                <!-- Status Badge -->
                                                <span
                                                    class="px-2 py-1 rounded-full text-[10px] font-bold"
                                                    :class="{
                                                        'bg-green-100 text-green-700': loan.status === 'active',
                                                        'bg-red-100 text-red-700': loan.status === 'defaulted',
                                                        'bg-slate-200 text-slate-700': loan.status === 'closed'
                                                    }"
                                                >
                                                    {{ loan.status.toUpperCase() }}
                                                </span>

                                                <span
                                                    v-if="loan.days_in_arrears > 0"
                                                    class="text-red-600 text-[10px] font-semibold"
                                                >
                                                    {{ loan.days_in_arrears }} days arrears
                                                </span>

                                            </div>
                                        </div>
                                    </div>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </AppLayout>
</template>
