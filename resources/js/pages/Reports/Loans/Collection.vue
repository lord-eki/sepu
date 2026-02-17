<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CalendarDays } from 'lucide-vue-next'

const props = defineProps<{
    repayments: Array<any>
    daily_collections: Record<string, { count: number; total_amount: number; principal: number; interest: number; penalty: number }>
    summary: {
        total_repayments: number
        total_collected: number
        total_principal: number
        total_interest: number
        total_penalty: number
        collection_rate: number
    }
    start_date: string
    end_date: string
}>()

const startDate = ref(props.start_date)
const endDate = ref(props.end_date)

function applyDateFilter() {
    router.get(
        route('reports.loans.collection'),
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
    { title: 'Collection' },
]
</script>

<template>
<Head title="Loan Collection" />

<AppLayout title="Loan Collection" :breadcrumbs="breadcrumbs">

    <!-- PAGE HEADER -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mx-6 mt-6 mb-10">
        <div>
            <h2 class="text-3xl font-extrabold text-[#0B1F3A] dark:text-orange-400 tracking-tight">
                Loan Collection
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Monitor and analyze loan repayments for the selected period.
            </p>
        </div>

        <!-- DATE FILTER -->
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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mx-6 mb-12">
        <div class="p-6 rounded-2xl 
                    bg-gradient-to-br from-white to-orange-50
                    dark:from-[#0B1F3A] dark:to-[#122C4F]
                    border border-orange-200 dark:border-[#1f3a63]
                    shadow-sm hover:shadow-xl hover:-translate-y-1
                    transition-all duration-300 text-center">
            <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                Total Repayments
            </div>
            <div class="mt-3 text-2xl font-bold text-[#0B1F3A] dark:text-white">
                {{ summary.total_repayments }}
            </div>
        </div>

        <div class="p-6 rounded-2xl 
                    bg-gradient-to-br from-white to-orange-50
                    dark:from-[#0B1F3A] dark:to-[#122C4F]
                    border border-orange-200 dark:border-[#1f3a63]
                    shadow-sm hover:shadow-xl hover:-translate-y-1
                    transition-all duration-300 text-center">
            <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                Total Collected
            </div>
            <div class="mt-3 text-2xl font-bold text-green-600">
                {{ money(summary.total_collected) }}
            </div>
        </div>

        <div class="p-6 rounded-2xl 
                    bg-gradient-to-br from-white to-orange-50
                    dark:from-[#0B1F3A] dark:to-[#122C4F]
                    border border-orange-200 dark:border-[#1f3a63]
                    shadow-sm hover:shadow-xl hover:-translate-y-1
                    transition-all duration-300 text-center">
            <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                Collection Rate
            </div>
            <div class="mt-3 text-2xl font-bold text-[#0B1F3A] dark:text-orange-400">
                {{ summary.collection_rate.toFixed(2) }}%
            </div>
        </div>
    </div>

    <!-- DAILY COLLECTIONS -->
    <div class="mx-6 mb-12">
        <h3 class="text-lg font-semibold text-[#0B1F3A] dark:text-orange-400 mb-6">
            Daily Collections
        </h3>

        <div v-if="Object.keys(daily_collections).length === 0"
            class="p-6 text-center text-orange-500 italic 
                   bg-orange-50 dark:bg-[#122C4F]
                   rounded-2xl border border-orange-200 dark:border-[#1f3a63]">
            No daily collections found for the selected period.
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            <div v-for="(day, date) in daily_collections" :key="date"
                class="relative p-5 rounded-2xl
                       bg-white dark:bg-[#0B1F3A]
                       border border-orange-200 dark:border-[#1f3a63]
                       shadow-sm hover:shadow-xl hover:-translate-y-1
                       transition-all duration-300 text-center">

                <div class="absolute top-0 left-0 w-2 h-full bg-orange-500 rounded-l-2xl"></div>

                <div class="text-sm font-semibold text-[#0B1F3A] dark:text-orange-400">{{ date }}</div>
                <div class="mt-2 text-sm dark:text-gray-300">{{ day.count }} repayments</div>
                <div class="text-green-600 font-semibold">{{ money(day.total_amount) }}</div>
                <div class="text-blue-700 text-xs">Principal: {{ money(day.principal) }}</div>
                <div class="text-blue-700 text-xs">Interest: {{ money(day.interest) }}</div>
                <div class="text-blue-700 text-xs">Penalty: {{ money(day.penalty) }}</div>
            </div>
        </div>
    </div>

    <!-- REPAYMENTS TABLE -->
    <div class="mx-6 mb-10 overflow-x-auto rounded-2xl
                border border-orange-200 dark:border-[#1f3a63]
                bg-white dark:bg-[#0B1F3A]
                shadow-sm">

        <table v-if="repayments.length > 0" class="min-w-full text-sm">
            <thead class="bg-orange-50 dark:bg-[#122C4F] border-b border-orange-200 dark:border-[#1f3a63]">
                <tr>
                    <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Loan Number</th>
                    <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Member</th>
                    <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Payment Date</th>
                    <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Paid Amount</th>
                    <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Principal</th>
                    <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Interest</th>
                    <th class="px-6 py-4 text-left font-semibold text-[#0B1F3A] dark:text-orange-300">Penalty</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-orange-100 dark:divide-[#1f3a63]">
                <tr v-for="repayment in repayments" :key="repayment.id" class="hover:bg-orange-50 dark:hover:bg-[#122C4F] transition">
                    <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">{{ repayment.loan.loan_number }}</td>
                    <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">{{ repayment.loan.member.first_name }} {{ repayment.loan.member.last_name }}</td>
                    <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">{{ repayment.payment_date }}</td>
                    <td class="px-6 py-4 font-semibold text-green-600">{{ money(repayment.paid_amount) }}</td>
                    <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">{{ money(repayment.principal_amount) }}</td>
                    <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">{{ money(repayment.interest_amount) }}</td>
                    <td class="px-6 py-4 text-[#0B1F3A] dark:text-orange-200">{{ money(repayment.penalty_amount) }}</td>
                </tr>
            </tbody>
        </table>

        <div v-else class="p-6 text-center text-orange-500 italic">
            No loan repayments found for the selected period.
        </div>
    </div>

</AppLayout>
</template>
