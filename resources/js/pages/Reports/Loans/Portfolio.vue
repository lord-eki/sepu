<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CalendarDays } from 'lucide-vue-next'

const props = defineProps<{
    loans: Array<any>
    portfolio_by_product: Array<{ name: string, count: number, total_disbursed: number, total_outstanding: number }>
    portfolio_by_status: Array<{ key: string, count: number, total_disbursed: number, total_outstanding: number }>
    summary: {
        total_loans: number
        total_disbursed: number
        total_outstanding: number
        average_loan_size: number
        portfolio_at_risk: number
    }
    start_date: string
    end_date: string
}>()

const startDate = ref(props.start_date)
const endDate = ref(props.end_date)

function applyDateFilter() {
    router.get(
        route('reports.loans.portfolio'),
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
    { title: 'Portfolio' },
]
</script>

<template>

    <Head title="Loan Portfolio" />

    <AppLayout title="Loan Portfolio" :breadcrumbs="breadcrumbs">

        <!-- Header + Date Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mx-5 mt-5 mb-8">
            <div>
                <h2 class="text-2xl font-bold text-[#0a2342] dark:text-orange-400">Loan Portfolio</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Overview of loans by product and status
                </p>
            </div>

            <div class="flex items-center gap-2">
                <CalendarDays class="h-5 w-5 text-orange-500" />
                <input type="date" v-model="startDate" @change="applyDateFilter"
                    class="rounded-xl border border-blue-900 dark:border-orange-500 bg-white dark:bg-blue-950 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500" />
                <span class="text-gray-500 dark:text-gray-400">-</span>
                <input type="date" v-model="endDate" @change="applyDateFilter"
                    class="rounded-xl border border-blue-900 dark:border-orange-500 bg-white dark:bg-blue-950 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mx-5 mb-8">
            <div
                class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg transition">
                <div class="text-sm text-blue-900 dark:text-orange-300">Total Loans</div>
                <div class="mt-2 text-xl font-bold text-[#0a2342] dark:text-white">{{ summary.total_loans }}</div>
            </div>
            <div
                class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg transition">
                <div class="text-sm text-blue-900 dark:text-orange-300">Total Disbursed</div>
                <div class="mt-2 text-xl font-bold text-green-600">{{ money(summary.total_disbursed) }}</div>
            </div>
            <div
                class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg transition">
                <div class="text-sm text-blue-900 dark:text-orange-300">Total Outstanding</div>
                <div class="mt-2 text-xl font-bold text-orange-600">{{ money(summary.total_outstanding) }}</div>
            </div>
            <div
                class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg transition">
                <div class="text-sm text-blue-900 dark:text-orange-300">Average Loan Size</div>
                <div class="mt-2 text-xl font-bold text-green-600">{{ money(summary.average_loan_size) }}</div>
            </div>
            <div
                class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg transition">
                <div class="text-sm text-blue-900 dark:text-orange-300">Portfolio at Risk</div>
                <div class="mt-2 text-xl font-bold text-red-600">{{ money(summary.portfolio_at_risk) }}</div>
            </div>
        </div>

        <!-- Portfolio by Product -->
        <div class="mx-5 mb-8">
            <h3 class="text-lg font-semibold text-[#0a2342] dark:text-orange-400 mb-4">Portfolio by Product</h3>
            <div v-if="portfolio_by_product.length === 0" class="text-gray-500 dark:text-gray-300 italic">
                No products found for the selected date range.
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="product in portfolio_by_product" :key="product.name"
                    class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg transition">
                    <div class="text-sm text-blue-900 dark:text-orange-300">{{ product.name }}</div>
                    <div class="mt-2 font-bold text-[#0a2342] dark:text-white">Loans: {{ product.count }}</div>
                    <div class="text-gray-700 dark:text-gray-300">Disbursed: {{ money(product.total_disbursed) }}</div>
                    <div class="text-gray-700 dark:text-gray-300">Outstanding: {{ money(product.total_outstanding) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Portfolio by Status -->
        <div>
            <h3 class="text-lg font-semibold text-[#0a2342] dark:text-orange-400 mx-5 mb-4">Portfolio by Status</h3>
            <div v-if="portfolio_by_status.length === 0" class="text-gray-500 mx-5 dark:text-gray-300 italic">
                No statuses found for the selected date range.
            </div>
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="status in portfolio_by_status" :key="status.key"
                    class="p-5 rounded-2xl bg-gray-50 dark:bg-[#0a2342] border border-blue-200 dark:border-orange-600 shadow hover:shadow-lg transition">
                    <div class="text-sm text-blue-900 dark:text-orange-300">{{ status.key.toUpperCase() }}</div>
                    <div class="mt-2 font-bold text-[#0a2342] dark:text-white">Loans: {{ status.count }}</div>
                    <div class="text-gray-700 dark:text-gray-300">Disbursed: {{ money(status.total_disbursed) }}</div>
                    <div class="text-gray-700 dark:text-gray-300">Outstanding: {{ money(status.total_outstanding) }}
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
