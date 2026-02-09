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
        <!-- Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-5 mx-5 mb-8">
            <div
                class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg text-center transition">
                <div class="text-sm text-blue-700 dark:text-orange-300">Loans in Arrears</div>
                <div class="mt-2 text-xl font-bold text-[#0a2342] dark:text-white">{{ summary.total_loans_in_arrears }}
                </div>
            </div>
            <div
                class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg text-center transition">
                <div class="text-sm text-blue-700 dark:text-orange-300">Total Amount in Arrears</div>
                <div class="mt-2 text-xl font-bold text-green-600">{{ money(summary.total_amount_in_arrears) }}</div>
            </div>
            <div
                class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg text-center transition">
                <div class="text-sm text-blue-700 dark:text-orange-300">Average Days in Arrears</div>
                <div class="mt-2 text-xl font-bold text-orange-600">{{ summary.average_days_in_arrears }}</div>
            </div>
            <div
                class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg text-center transition">
                <div class="text-sm text-blue-700 dark:text-orange-300">Portfolio at Risk %</div>
                <div class="mt-2 text-xl font-bold text-red-600">{{ summary.portfolio_at_risk_ratio }}%</div>
            </div>
        </div>

        <!-- Arrears Analysis -->
        <div class="mt-5 mx-5 mb-8">
            <h3 class="text-lg font-semibold text-[#0a2342] dark:text-orange-400 mb-4">Arrears Analysis by Days</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <div v-for="(group, key) in arrears_analysis" :key="key"
                    class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg text-center transition">
                    <div class="text-sm text-blue-700 dark:text-orange-300">{{ key.replace(/_/g, ' ').toUpperCase() }}
                    </div>
                    <div class="mt-2 font-bold text-[#0a2342] dark:text-white">{{ group.count }} loans</div>
                    <div class="text-gray-700 dark:text-gray-300">Outstanding: {{ money(group.total_outstanding) }}
                    </div>
                    <div class="text-gray-700 dark:text-gray-300">Percentage: {{ group.percentage.toFixed(2) }}%</div>
                </div>
                <div v-if="Object.keys(arrears_analysis).length === 0"
                    class="col-span-full text-center text-gray-500 dark:text-gray-400">
                    No arrears data available
                </div>
            </div>
        </div>

        <!-- Loan Details Table -->
        <div
            class="overflow-x-auto rounded-2xl border border-blue-200 dark:border-orange-600 bg-gray-50 dark:bg-[#0a2342] m-5 shadow-sm">
            <table class="min-w-full divide-y divide-blue-200 dark:divide-orange-600">
                <thead class="bg-blue-100 dark:bg-[#0a2342]">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-[#0a2342] dark:text-orange-300">Loan
                            Number</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-[#0a2342] dark:text-orange-300">Member
                        </th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-[#0a2342] dark:text-orange-300">
                            Disbursed</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-[#0a2342] dark:text-orange-300">
                            Outstanding</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-[#0a2342] dark:text-orange-300">Days
                            in
                            Arrears</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-200 dark:divide-orange-600">
                    <tr v-for="loan in loans" :key="loan.id"
                        class="hover:bg-blue-100 dark:hover:bg-[#0a2342] transition">
                        <td class="px-4 py-2 text-sm text-[#0a2342] dark:text-orange-200">{{ loan.loan_number }}</td>
                        <td class="px-4 py-2 text-sm text-[#0a2342] dark:text-orange-200">{{ loan.member.first_name }}
                            {{
        loan.member.last_name }}</td>
                        <td class="px-4 py-2 text-sm text-green-600">{{ money(loan.disbursed_amount) }}</td>
                        <td class="px-4 py-2 text-sm text-orange-600">{{ money(loan.outstanding_balance) }}</td>
                        <td class="px-4 py-2 text-sm text-red-600">{{ loan.days_in_arrears }}</td>
                    </tr>
                    <tr v-if="loans.length === 0" class="text-center text-gray-500 dark:text-gray-400">
                        <td colspan="5" class="py-4">No loans in arrears</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
