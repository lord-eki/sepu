<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { CalendarDays } from 'lucide-vue-next';
import { ref } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    revenue: Record<string, number>;
    expenses: Record<string, number>;
    totals: {
        revenue: number;
        expenses: number;
        net_income: number;
    };
    start_date: string;
    end_date: string;
}>();

const startDate = ref(props.start_date);
const endDate = ref(props.end_date);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Financial Reports', href: route('reports.financial.index') }, { title: 'Income Statement' }];

function applyDateFilter() {
    router.get(
        route('reports.financial.income-statement'),
        { start_date: startDate.value, end_date: endDate.value },
        { preserveState: true, replace: true },
    );
}

function money(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(value ?? 0);
}

function exportReport(format: string) {
    const url = route('reports.export');

    window.location.href =
        url + `?report_type=income_statement` + `&format=${format}` + `&start_date=${startDate.value}` + `&end_date=${endDate.value}`;
}
</script>

<template>
    <Head title="Income Statement" />

    <AppLayout title="Income Statement" :breadcrumbs="breadcrumbs">
        <!-- Header -->
        <div class="mx-6 mt-6 mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-extrabold text-[#0a2342] dark:text-blue-400">Income Statement</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Performance report from selected period</p>
            </div>

            <!-- Date Range Picker -->
            <div class="flex items-center gap-2">
                <CalendarDays class="h-5 w-5 text-orange-500" />
                <input
                    type="date"
                    v-model="startDate"
                    @change="applyDateFilter"
                    class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                />
                <span class="text-gray-500 dark:text-gray-400">to</span>
                <input
                    type="date"
                    v-model="endDate"
                    @change="applyDateFilter"
                    class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                />
            </div>
            <!-- Export Buttons -->
            <div class="flex gap-2">
                <button
                    @click="exportReport('excel')"
                    class="rounded-xl bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700"
                >
                    Excel
                </button>

                <button
                    @click="exportReport('csv')"
                    class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    CSV
                </button>

                <button
                    @click="exportReport('pdf')"
                    class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
                >
                    PDF
                </button>
            </div>
        </div>

        <!-- Main Cards -->
        <div class="mx-6 grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Revenue Card -->
            <div class="rounded-3xl border border-gray-200 bg-white shadow-md dark:border-gray-700 dark:bg-gray-900">
                <div class="rounded-t-3xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-bold text-[#0a2342] dark:text-blue-400">Revenue</h3>
                </div>

                <div class="space-y-3 p-6">
                    <div v-for="(value, key) in revenue" :key="key" class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                        <span>{{ key.replace(/_/g, ' ').toUpperCase() }}</span>
                        <span class="font-medium">{{ money(value) }}</span>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-b-3xl border-t-2 border-orange-400 bg-orange-50 px-6 py-4 text-base font-bold text-[#0a2342] dark:bg-gray-800 dark:text-orange-400"
                >
                    <span>Total Revenue</span>
                    <span>{{ money(totals.revenue) }}</span>
                </div>
            </div>

            <!-- Expenses Card -->
            <div class="rounded-3xl border border-gray-200 bg-white shadow-md dark:border-gray-700 dark:bg-gray-900">
                <div class="rounded-t-3xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-bold text-[#0a2342] dark:text-blue-400">Expenses</h3>
                </div>

                <div class="space-y-3 p-6">
                    <div v-for="(value, key) in expenses" :key="key" class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                        <span>{{ key.replace(/_/g, ' ').toUpperCase() }}</span>
                        <span class="font-medium">{{ money(value) }}</span>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between rounded-b-3xl border-t-2 border-orange-400 bg-orange-50 px-6 py-4 text-base font-bold text-[#0a2342] dark:bg-gray-800 dark:text-orange-400"
                >
                    <span>Total Expenses</span>
                    <span>{{ money(totals.expenses) }}</span>
                </div>
            </div>
        </div>

        <!-- Net Income -->
        <div
            class="mx-6 mt-8 flex items-center justify-between rounded-3xl border border-gray-200 bg-gray-50 p-6 text-lg font-bold text-[#0a2342] shadow-md dark:border-gray-700 dark:bg-gray-800 dark:text-blue-400"
        >
            <span>Net Income</span>
            <span>{{ money(totals.net_income) }}</span>
        </div>
    </AppLayout>
</template>
