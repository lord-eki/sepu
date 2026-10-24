<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { CalendarDays } from 'lucide-vue-next';
import { ref } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    assets: Record<string, any>;
    liabilities: Record<string, any>;
    equity: Record<string, any>;
    totals: {
        total_assets: number;
        total_liabilities: number;
        total_equity: number;
    };
    date: string;
}>();

const selectedDate = ref(props.date);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Financial Reports', href: route('reports.financial.index') }, { title: 'Balance Sheet' }];

function applyDateFilter() {
    router.get(route('reports.financial.balance-sheet'), { date: selectedDate.value }, { preserveState: true, replace: true });
}

function money(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(value ?? 0);
}

const exporting = ref(false);

async function exportReport(format: 'pdf' | 'excel' | 'csv') {
    try {
        exporting.value = true;

        const response = await axios.post(
            route('reports.export'),
            {
                report_type: 'balance_sheet',
                format: format,
                date: selectedDate.value,
            },
            {
                responseType: 'blob',
            },
        );

        const blob = new Blob([response.data]);
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);

        const fileName = `balance_sheet_${selectedDate.value}.${format === 'excel' ? 'xlsx' : format}`;
        link.setAttribute('download', fileName);

        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Export failed:', error);
    } finally {
        exporting.value = false;
    }
}
</script>

<template>
    <Head title="Balance Sheet" />

    <AppLayout title="Balance Sheet" :breadcrumbs="breadcrumbs">
        <!-- Header -->
        <div class="mx-6 mt-6 mb-10 flex flex-col gap-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-extrabold text-[#0a2342] dark:text-blue-400">Balance Sheet</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Statement of financial position as at the selected date</p>
                </div>

                <div class="flex gap-4">
                    <!-- Date Picker -->
                    <div class="flex items-center gap-2">
                        <CalendarDays class="h-5 w-5 text-orange-500" />
                        <input
                            type="date"
                            v-model="selectedDate"
                            @change="applyDateFilter"
                            class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="exportReport('excel')"
                            class="rounded-xl bg-green-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-800"
                        >
                            Excel
                        </button>

                        <button
                            @click="exportReport('pdf')"
                            class="rounded-xl bg-red-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-800"
                        >
                            PDF
                        </button>

                        <button
                            @click="exportReport('csv')"
                            class="rounded-xl bg-blue-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-900"
                        >
                            CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Accounting Equation -->
            <div
                class="rounded-xl border border-dashed border-blue-200 bg-gray-50 px-4 py-6 text-sm text-[#0a2342] sm:text-base dark:border-blue-800 dark:bg-gray-800 dark:text-blue-300"
            >
                <span class="font-semibold">Assets</span>
                =
                <span class="font-semibold text-orange-500">Liabilities</span>
                +
                <span class="font-semibold">Equity</span>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="mx-6 grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- ASSETS -->
            <div class="rounded-3xl border border-gray-200 bg-white shadow-md dark:border-gray-700 dark:bg-gray-900">
                <div class="rounded-t-3xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-bold text-[#0a2342] dark:text-blue-400">Assets</h3>
                </div>

                <div class="space-y-6 p-6">
                    <div v-for="group in assets" :key="group.label">
                        <h4 class="mb-3 text-xs font-semibold tracking-wide text-orange-500 uppercase">
                            {{ group.label }}
                        </h4>

                        <div class="space-y-2">
                            <div
                                v-for="item in group.accounts"
                                :key="item.name"
                                class="flex items-center justify-between text-sm text-gray-700 dark:text-gray-300"
                            >
                                <span>{{ item.name }}</span>
                                <span class="font-medium">{{ money(item.balance) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Assets -->
                <div
                    class="flex items-center justify-between border-t-2 border-orange-400 bg-orange-50 px-6 py-4 text-base font-bold text-[#0a2342] dark:bg-gray-800 dark:text-orange-400"
                >
                    <span>Total Assets</span>
                    <span>{{ money(totals.total_assets) }}</span>
                </div>
            </div>

            <!-- LIABILITIES & EQUITY -->
            <div class="rounded-3xl border border-gray-200 bg-white shadow-md dark:border-gray-700 dark:bg-gray-900">
                <div class="rounded-t-3xl border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="text-lg font-bold text-[#0a2342] dark:text-blue-400">Liabilities & Equity</h3>
                </div>

                <div class="space-y-6 p-6">
                    <!-- Liabilities -->
                    <div v-for="group in liabilities" :key="group.label">
                        <h4 class="mb-3 text-xs font-semibold tracking-wide text-orange-500 uppercase">
                            {{ group.label }}
                        </h4>

                        <div class="space-y-2">
                            <div
                                v-for="item in group.accounts"
                                :key="item.name"
                                class="flex items-center justify-between text-sm text-gray-700 dark:text-gray-300"
                            >
                                <span>{{ item.name }}</span>
                                <span class="font-medium">{{ money(item.balance) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Liabilities -->
                    <div
                        class="flex items-center justify-between border-t border-gray-200 pt-3 text-sm font-semibold text-[#0a2342] dark:border-gray-700 dark:text-blue-300"
                    >
                        <span>Total Liabilities</span>
                        <span>{{ money(totals.total_liabilities) }}</span>
                    </div>

                    <!-- Equity -->
                    <div>
                        <h4 class="mb-3 text-xs font-semibold tracking-wide text-orange-500 uppercase">Equity</h4>

                        <div class="space-y-2">
                            <div
                                v-for="item in equity"
                                :key="item.name"
                                class="flex items-center justify-between text-sm text-gray-700 dark:text-gray-300"
                            >
                                <span>{{ item.name }}</span>
                                <span class="font-medium">{{ money(item.balance) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total Equity -->
                    <div
                        class="flex items-center justify-between border-t border-gray-200 pt-3 text-sm font-semibold text-[#0a2342] dark:border-gray-700 dark:text-blue-300"
                    >
                        <span>Total Equity</span>
                        <span>{{ money(totals.total_equity) }}</span>
                    </div>
                </div>

                <!-- Total L + E -->
                <div class="flex items-center justify-between rounded-b-3xl bg-[#0a2342] px-6 py-4 text-base font-bold text-white dark:bg-gray-800">
                    <span>Total Liabilities & Equity</span>
                    <span>{{ money(totals.total_liabilities + totals.total_equity) }}</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
