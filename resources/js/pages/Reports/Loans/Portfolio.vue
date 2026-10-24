<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { CalendarDays } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    loans: Array<any>;
    portfolio_by_product: Array<{ name: string; count: number; total_disbursed: number; total_outstanding: number }>;
    portfolio_by_status: Array<{ key: string; count: number; total_disbursed: number; total_outstanding: number }>;
    summary: {
        total_loans: number;
        total_disbursed: number;
        total_outstanding: number;
        average_loan_size: number;
        portfolio_at_risk: number;
    };
    start_date: string;
    end_date: string;
}>();

const startDate = ref(props.start_date);
const endDate = ref(props.end_date);

function applyDateFilter() {
    router.get(route('reports.loans.portfolio'), { start_date: startDate.value, end_date: endDate.value }, { preserveState: true, replace: true });
}

function money(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(value ?? 0);
}

const breadcrumbs = [{ title: 'Loan Reports', href: route('reports.loansReport.index') }, { title: 'Portfolio' }];
</script>

<template>
    <Head title="Loan Portfolio" />

    <AppLayout title="Loan Portfolio" :breadcrumbs="breadcrumbs">
        <!-- HEADER -->
        <div class="mx-6 mt-6 mb-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight text-[#0B1F3A] dark:text-orange-400">Loan Portfolio</h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Overview of loans by product, performance and risk</p>
            </div>

            <!-- Date Filter -->
            <div
                class="flex items-center gap-3 rounded-2xl border border-orange-200 bg-white px-4 py-3 shadow-sm dark:border-[#1f3a63] dark:bg-[#0B1F3A]"
            >
                <CalendarDays class="h-5 w-5 text-orange-500" />

                <input
                    type="date"
                    v-model="startDate"
                    @change="applyDateFilter"
                    class="rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-orange-500 focus:outline-none dark:text-gray-200"
                />

                <span class="text-gray-400">-</span>

                <input
                    type="date"
                    v-model="endDate"
                    @change="applyDateFilter"
                    class="rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-orange-500 focus:outline-none dark:text-gray-200"
                />
            </div>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="mx-6 mb-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
            <!-- CARD -->
            <div
                class="rounded-2xl border border-orange-200 bg-gradient-to-br from-white to-orange-50 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-[#1f3a63] dark:from-[#0B1F3A] dark:to-[#122C4F]"
            >
                <div class="text-xs font-semibold tracking-wide text-orange-500 uppercase">Total Loans</div>
                <div class="mt-3 text-xl font-bold text-[#0B1F3A] dark:text-white">
                    {{ summary.total_loans }}
                </div>
            </div>

            <div
                class="rounded-2xl border border-orange-200 bg-gradient-to-br from-white to-orange-50 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-[#1f3a63] dark:from-[#0B1F3A] dark:to-[#122C4F]"
            >
                <div class="text-xs font-semibold tracking-wide text-orange-500 uppercase">Total Disbursed</div>
                <div class="mt-3 text-xl font-bold text-green-600">
                    {{ money(summary.total_disbursed) }}
                </div>
            </div>

            <div
                class="rounded-2xl border border-orange-200 bg-gradient-to-br from-white to-orange-50 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-[#1f3a63] dark:from-[#0B1F3A] dark:to-[#122C4F]"
            >
                <div class="text-xs font-semibold tracking-wide text-orange-500 uppercase">Total Outstanding</div>
                <div class="mt-3 text-xl font-bold text-orange-500">
                    {{ money(summary.total_outstanding) }}
                </div>
            </div>

            <div
                class="rounded-2xl border border-orange-200 bg-gradient-to-br from-white to-orange-50 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-[#1f3a63] dark:from-[#0B1F3A] dark:to-[#122C4F]"
            >
                <div class="text-xs font-semibold tracking-wide text-orange-500 uppercase">Average Loan Size</div>
                <div class="mt-3 text-xl font-bold text-green-600">
                    {{ money(summary.average_loan_size) }}
                </div>
            </div>

            <div
                class="rounded-2xl border border-orange-200 bg-gradient-to-br from-white to-orange-50 p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-[#1f3a63] dark:from-[#0B1F3A] dark:to-[#122C4F]"
            >
                <div class="text-xs font-semibold tracking-wide text-orange-500 uppercase">Portfolio at Risk</div>
                <div class="mt-3 text-xl font-bold text-red-600">
                    {{ money(summary.portfolio_at_risk) }}
                </div>
            </div>
        </div>

        <!-- PORTFOLIO BY PRODUCT -->
        <div class="mx-6 mb-12">
            <h3 class="mb-5 text-lg font-semibold text-[#0B1F3A] dark:text-orange-400">Portfolio by Product</h3>

            <div v-if="portfolio_by_product.length === 0" class="text-gray-500 italic dark:text-gray-400">
                No products found for the selected date range.
            </div>

            <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="product in portfolio_by_product"
                    :key="product.name"
                    class="relative rounded-2xl border border-orange-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-[#1f3a63] dark:bg-[#0B1F3A]"
                >
                    <!-- Accent Bar -->
                    <div class="absolute top-0 left-0 h-full w-2 rounded-l-2xl bg-orange-500"></div>

                    <div class="text-sm font-semibold text-[#0B1F3A] dark:text-orange-400">
                        {{ product.name }}
                    </div>

                    <div class="mt-3 text-sm dark:text-gray-300">
                        Loans: <span class="font-semibold">{{ product.count }}</span>
                    </div>

                    <div class="text-sm dark:text-gray-300">Disbursed: {{ money(product.total_disbursed) }}</div>

                    <div class="text-sm dark:text-gray-300">Outstanding: {{ money(product.total_outstanding) }}</div>
                </div>
            </div>
        </div>

        <!-- PORTFOLIO BY STATUS -->
        <div class="mx-6 mb-10">
            <h3 class="mb-5 text-lg font-semibold text-[#0B1F3A] dark:text-orange-400">Portfolio by Status</h3>

            <div v-if="portfolio_by_status.length === 0" class="text-gray-500 italic dark:text-gray-400">
                No statuses found for the selected date range.
            </div>

            <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="status in portfolio_by_status"
                    :key="status.key"
                    class="relative rounded-2xl border border-orange-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-[#1f3a63] dark:bg-[#0B1F3A]"
                >
                    <div class="absolute top-0 left-0 h-full w-2 rounded-l-2xl bg-orange-500"></div>

                    <div class="text-sm font-semibold text-[#0B1F3A] dark:text-orange-400">
                        {{ status.key.toUpperCase() }}
                    </div>

                    <div class="mt-3 text-sm dark:text-gray-300">
                        Loans: <span class="font-semibold">{{ status.count }}</span>
                    </div>

                    <div class="text-sm dark:text-gray-300">Disbursed: {{ money(status.total_disbursed) }}</div>

                    <div class="text-sm dark:text-gray-300">Outstanding: {{ money(status.total_outstanding) }}</div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
