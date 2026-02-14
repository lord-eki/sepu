<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
    loans: Array<any>
    arrears_analysis: Record<string, { count: number; total_outstanding: number; percentage: number }>
    summary: {
        total_loans_in_arrears: number
        total_amount_in_arrears: number
        average_days_in_arrears: number
        portfolio_at_risk_ratio: number
    }
}>()

function money(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(value ?? 0)
}

const breadcrumbs = [
    { title: 'Loan Reports', href: route('reports.loansReport.index') },
    { title: 'Arrears' },
]
</script>

<template>
    <Head title="Loan Arrears" />

    <AppLayout title="Loan Arrears" :breadcrumbs="breadcrumbs">
    <!-- PAGE HEADER -->
    <div class="mx-6 mt-6 mb-10">
        <h2 class="text-3xl font-extrabold text-[#0B1F3A] dark:text-orange-400 tracking-tight">
            Loan Arrears
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 max-w-2xl">
            Monitor overdue loans, analyze risk exposure, and track portfolio performance in arrears.
        </p>
    </div>


        <!-- SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-6 mx-6 mb-12">

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300 text-center">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Loans in Arrears
                </div>
                <div class="mt-3 text-2xl font-bold text-[#0B1F3A] dark:text-white">
                    {{ summary.total_loans_in_arrears }}
                </div>
            </div>

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300 text-center">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Total Amount in Arrears
                </div>
                <div class="mt-3 text-2xl font-bold text-green-600">
                    {{ money(summary.total_amount_in_arrears) }}
                </div>
            </div>

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300 text-center">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Avg Days in Arrears
                </div>
                <div class="mt-3 text-2xl font-bold text-orange-500">
                    {{ summary.average_days_in_arrears }}
                </div>
            </div>

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300 text-center">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Portfolio at Risk %
                </div>
                <div class="mt-3 text-2xl font-bold text-red-600">
                    {{ summary.portfolio_at_risk_ratio }}%
                </div>
            </div>

        </div>


        <!-- ARREARS ANALYSIS -->
        <div class="mx-6 mb-12">
            <h3 class="text-lg font-semibold text-[#0B1F3A] dark:text-orange-400 mb-6">
                Arrears Analysis by Days
            </h3>

            <div v-if="Object.keys(arrears_analysis).length === 0"
                class="text-center text-gray-500 dark:text-gray-400">
                No arrears data available
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">

                <div v-for="(group, key) in arrears_analysis"
                    :key="key"
                    class="relative p-6 rounded-2xl
                           bg-white dark:bg-[#0B1F3A]
                           border border-orange-200 dark:border-[#1f3a63]
                           shadow-sm hover:shadow-xl hover:-translate-y-1
                           transition-all duration-300 text-center">

                    <!-- Accent bar -->
                    <div class="absolute top-0 left-0 w-2 h-full bg-orange-500 rounded-l-2xl"></div>

                    <div class="text-sm font-semibold text-[#0B1F3A] dark:text-orange-400">
                        {{ key.replace(/_/g, ' ').toUpperCase() }}
                    </div>

                    <div class="mt-3 text-sm dark:text-gray-300">
                        <span class="font-semibold">{{ group.count }}</span> loans
                    </div>

                    <div class="text-sm dark:text-gray-300">
                        {{ money(group.total_outstanding) }}
                    </div>

                    <div class="text-sm text-red-500 font-semibold">
                        {{ group.percentage.toFixed(2) }}%
                    </div>
                </div>

            </div>
        </div>


        <!-- LOAN DETAILS TABLE -->
        <div class="mx-6 mb-10 overflow-x-auto rounded-2xl
                    border border-orange-200 dark:border-[#1f3a63]
                    bg-white dark:bg-[#0B1F3A]
                    shadow-sm">

            <table class="min-w-full text-sm">

                <!-- TABLE HEAD -->
                <thead class="bg-orange-50 dark:bg-[#122C4F] border-b border-orange-200 dark:border-[#1f3a63]">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">
                            Loan Number
                        </th>
                        <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">
                            Member
                        </th>
                        <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">
                            Disbursed
                        </th>
                        <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">
                            Outstanding
                        </th>
                        <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">
                            Days in Arrears
                        </th>
                    </tr>
                </thead>

                <!-- TABLE BODY -->
                <tbody class="divide-y divide-orange-100 dark:divide-[#1f3a63]">

                    <tr v-for="loan in loans"
                        :key="loan.id"
                        class="hover:bg-orange-50 dark:hover:bg-[#122C4F] transition">

                        <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">
                            {{ loan.loan_number }}
                        </td>

                        <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">
                            {{ loan.member.first_name }} {{ loan.member.last_name }}
                        </td>

                        <td class="px-6 py-4 font-semibold text-green-600">
                            {{ money(loan.disbursed_amount) }}
                        </td>

                        <td class="px-6 py-4 font-semibold text-orange-500">
                            {{ money(loan.outstanding_balance) }}
                        </td>

                        <td class="px-6 py-4 font-semibold text-red-600">
                            {{ loan.days_in_arrears }}
                        </td>
                    </tr>

                    <tr v-if="loans.length === 0">
                        <td colspan="5"
                            class="py-6 text-center text-gray-500 dark:text-gray-400">
                            No loans in arrears
                        </td>
                    </tr>

                </tbody>
            </table>

        </div>

    </AppLayout>
</template>
