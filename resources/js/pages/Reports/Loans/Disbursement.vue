<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CalendarDays } from 'lucide-vue-next'

const props = defineProps<{
    disbursements: Array<any>
    daily_disbursements: Record<string, { count: number; total_amount: number }>
    summary: {
        total_disbursements: number
        total_amount: number
        average_loan_size: number
        daily_average: number
    }
    start_date: string
    end_date: string
}>()

const startDate = ref(props.start_date)
const endDate = ref(props.end_date)

function applyDateFilter() {
    router.get(
        route('reports.loans.disbursement'),
        { start_date: startDate.value, end_date: endDate.value },
        { preserveState: true, replace: true }
    )
}

function money(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(value ?? 0)
}

const breadcrumbs = [
    { title: 'Loan Reports', href: route('reports.loansReport.index') },
    { title: 'Disbursement' },
]
</script>

<template>
    <Head title="Loan Disbursement" />

    <AppLayout title="Loan Disbursement" :breadcrumbs="breadcrumbs">

        <!-- PAGE HEADER -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mx-6 mt-6 mb-10">

            <div>
                <h2 class="text-3xl font-extrabold text-[#0B1F3A] dark:text-orange-400 tracking-tight">
                    Loan Disbursement
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    View and analyze loan disbursement activity for the selected period.
                </p>
            </div>

            <!-- Date Filter -->
            <div class="flex items-center gap-3 bg-white dark:bg-[#0B1F3A] 
                        border border-orange-200 dark:border-[#1f3a63]
                        rounded-2xl px-4 py-3 shadow-sm">

                <CalendarDays class="h-5 w-5 text-orange-500" />

                <input type="date"
                    v-model="startDate"
                    @change="applyDateFilter"
                    class="rounded-lg bg-transparent px-3 py-2 text-sm 
                           text-gray-700 dark:text-gray-200
                           focus:outline-none focus:ring-2 focus:ring-orange-500" />

                <span class="text-gray-400">-</span>

                <input type="date"
                    v-model="endDate"
                    @change="applyDateFilter"
                    class="rounded-lg bg-transparent px-3 py-2 text-sm 
                           text-gray-700 dark:text-gray-200
                           focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>

        </div>


        <!-- SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mx-6 mb-12">

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300 text-center">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Total Disbursements
                </div>
                <div class="mt-3 text-2xl font-bold text-[#0B1F3A] dark:text-white">
                    {{ summary.total_disbursements }}
                </div>
            </div>

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300 text-center">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Total Amount
                </div>
                <div class="mt-3 text-2xl font-bold text-green-600">
                    {{ money(summary.total_amount) }}
                </div>
            </div>

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300 text-center">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Average Loan Size
                </div>
                <div class="mt-3 text-2xl font-bold text-orange-500">
                    {{ money(summary.average_loan_size) }}
                </div>
            </div>

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300 text-center">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Daily Average
                </div>
                <div class="mt-3 text-2xl font-bold text-[#0B1F3A] dark:text-white">
                    {{ money(summary.daily_average) }}
                </div>
            </div>

        </div>


        <!-- DAILY DISBURSEMENTS -->
        <div class="mx-6 mb-12">
            <h3 class="text-lg font-semibold text-[#0B1F3A] dark:text-orange-400 mb-6">
                Daily Disbursements
            </h3>

            <div v-if="Object.keys(daily_disbursements).length === 0"
                class="p-6 text-center text-orange-500 italic 
                       bg-orange-50 dark:bg-[#122C4F]
                       rounded-2xl border border-orange-200 dark:border-[#1f3a63]">
                No daily disbursements found for the selected period.
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">

                <div v-for="(day, date) in daily_disbursements"
                    :key="date"
                    class="relative p-5 rounded-2xl
                           bg-white dark:bg-[#0B1F3A]
                           border border-orange-200 dark:border-[#1f3a63]
                           shadow-sm hover:shadow-xl hover:-translate-y-1
                           transition-all duration-300 text-center">

                    <div class="absolute top-0 left-0 w-2 h-full bg-orange-500 rounded-l-2xl"></div>

                    <div class="text-sm font-semibold text-[#0B1F3A] dark:text-orange-400">
                        {{ date }}
                    </div>

                    <div class="mt-2 text-sm dark:text-gray-300">
                        {{ day.count }} loans
                    </div>

                    <div class="text-green-600 font-semibold">
                        {{ money(day.total_amount) }}
                    </div>
                </div>

            </div>
        </div>


        <!-- DISBURSEMENT TABLE -->
        <div class="mx-6 mb-10 overflow-x-auto rounded-2xl
                    border border-orange-200 dark:border-[#1f3a63]
                    bg-white dark:bg-[#0B1F3A]
                    shadow-sm">

            <table v-if="disbursements.length > 0" class="min-w-full text-sm">

                <thead class="bg-orange-50 dark:bg-[#122C4F] border-b border-orange-200 dark:border-[#1f3a63]">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Loan Number</th>
                        <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Member</th>
                        <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Product</th>
                        <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Amount</th>
                        <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Date</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-orange-100 dark:divide-[#1f3a63]">

                    <tr v-for="loan in disbursements"
                        :key="loan.id"
                        class="hover:bg-orange-50 dark:hover:bg-[#122C4F] transition">

                        <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">
                            {{ loan.loan_number }}
                        </td>

                        <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">
                            {{ loan.member.first_name }} {{ loan.member.last_name }}
                        </td>

                        <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">
                            {{ loan.loanProduct.name }}
                        </td>

                        <td class="px-6 py-4 font-semibold text-green-600">
                            {{ money(loan.disbursed_amount) }}
                        </td>

                        <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">
                            {{ loan.disbursement_date }}
                        </td>
                    </tr>

                </tbody>
            </table>

            <div v-else class="p-6 text-center text-orange-500 italic">
                No loan disbursements found for the selected period.
            </div>

        </div>

    </AppLayout>
</template>
