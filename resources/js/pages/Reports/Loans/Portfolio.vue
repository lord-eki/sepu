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

        <!-- HEADER -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mx-6 mt-6 mb-10">

            <div>
                <h2 class="text-3xl font-extrabold text-[#0B1F3A] dark:text-orange-400 tracking-tight">
                    Loan Portfolio
                </h2>
                <p class="text-gray-600 dark:text-gray-300 mt-2 text-sm">
                    Overview of loans by product, performance and risk
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
                           border border-transparent
                           focus:outline-none focus:ring-2 focus:ring-orange-500" />

                <span class="text-gray-400">-</span>

                <input type="date"
                    v-model="endDate"
                    @change="applyDateFilter"
                    class="rounded-lg bg-transparent px-3 py-2 text-sm 
                           text-gray-700 dark:text-gray-200 
                           border border-transparent
                           focus:outline-none focus:ring-2 focus:ring-orange-500" />
            </div>
        </div>


        <!-- SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mx-6 mb-12">

            <!-- CARD -->
            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Total Loans
                </div>
                <div class="mt-3 text-xl font-bold text-[#0B1F3A] dark:text-white">
                    {{ summary.total_loans }}
                </div>
            </div>

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Total Disbursed
                </div>
                <div class="mt-3 text-xl font-bold text-green-600">
                    {{ money(summary.total_disbursed) }}
                </div>
            </div>

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Total Outstanding
                </div>
                <div class="mt-3 text-xl font-bold text-orange-500">
                    {{ money(summary.total_outstanding) }}
                </div>
            </div>

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Average Loan Size
                </div>
                <div class="mt-3 text-xl font-bold text-green-600">
                    {{ money(summary.average_loan_size) }}
                </div>
            </div>

            <div class="p-6 rounded-2xl 
                        bg-gradient-to-br from-white to-orange-50
                        dark:from-[#0B1F3A] dark:to-[#122C4F]
                        border border-orange-200 dark:border-[#1f3a63]
                        shadow-sm hover:shadow-xl hover:-translate-y-1
                        transition-all duration-300">

                <div class="text-xs uppercase tracking-wide text-orange-500 font-semibold">
                    Portfolio at Risk
                </div>
                <div class="mt-3 text-xl font-bold text-red-600">
                    {{ money(summary.portfolio_at_risk) }}
                </div>
            </div>

        </div>


        <!-- PORTFOLIO BY PRODUCT -->
        <div class="mx-6 mb-12">
            <h3 class="text-lg font-semibold text-[#0B1F3A] dark:text-orange-400 mb-5">
                Portfolio by Product
            </h3>

            <div v-if="portfolio_by_product.length === 0"
                class="text-gray-500 dark:text-gray-400 italic">
                No products found for the selected date range.
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <div v-for="product in portfolio_by_product"
                    :key="product.name"
                    class="relative p-6 rounded-2xl
                           bg-white dark:bg-[#0B1F3A]
                           border border-orange-200 dark:border-[#1f3a63]
                           shadow-sm hover:shadow-xl hover:-translate-y-1
                           transition-all duration-300">

                    <!-- Accent Bar -->
                    <div class="absolute top-0 left-0 w-2 h-full bg-orange-500 rounded-l-2xl"></div>

                    <div class="text-sm font-semibold text-[#0B1F3A] dark:text-orange-400">
                        {{ product.name }}
                    </div>

                    <div class="mt-3 text-sm dark:text-gray-300">
                        Loans: <span class="font-semibold">{{ product.count }}</span>
                    </div>

                    <div class="text-sm dark:text-gray-300">
                        Disbursed: {{ money(product.total_disbursed) }}
                    </div>

                    <div class="text-sm dark:text-gray-300">
                        Outstanding: {{ money(product.total_outstanding) }}
                    </div>
                </div>

            </div>
        </div>


        <!-- PORTFOLIO BY STATUS -->
        <div class="mx-6 mb-10">
            <h3 class="text-lg font-semibold text-[#0B1F3A] dark:text-orange-400 mb-5">
                Portfolio by Status
            </h3>

            <div v-if="portfolio_by_status.length === 0"
                class="text-gray-500 dark:text-gray-400 italic">
                No statuses found for the selected date range.
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <div v-for="status in portfolio_by_status"
                    :key="status.key"
                    class="relative p-6 rounded-2xl
                           bg-white dark:bg-[#0B1F3A]
                           border border-orange-200 dark:border-[#1f3a63]
                           shadow-sm hover:shadow-xl hover:-translate-y-1
                           transition-all duration-300">

                    <div class="absolute top-0 left-0 w-2 h-full bg-orange-500 rounded-l-2xl"></div>

                    <div class="text-sm font-semibold text-[#0B1F3A] dark:text-orange-400">
                        {{ status.key.toUpperCase() }}
                    </div>

                    <div class="mt-3 text-sm dark:text-gray-300">
                        Loans: <span class="font-semibold">{{ status.count }}</span>
                    </div>

                    <div class="text-sm dark:text-gray-300">
                        Disbursed: {{ money(status.total_disbursed) }}
                    </div>

                    <div class="text-sm dark:text-gray-300">
                        Outstanding: {{ money(status.total_outstanding) }}
                    </div>
                </div>

            </div>
        </div>

    </AppLayout>
</template>
