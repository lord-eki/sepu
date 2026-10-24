<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { CalendarDays } from 'lucide-vue-next';
import { ref } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    accounts: { name: string; debit: number; credit: number }[];
    totals: {
        debits: number;
        credits: number;
        difference: number;
    };
    date: string;
}>();

const selectedDate = ref(props.date);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Financial Reports', href: route('reports.financial.index') }, { title: 'Trial Balance' }];

function applyDateFilter() {
    router.get(route('reports.financial.trial-balance'), { date: selectedDate.value }, { preserveState: true, replace: true });
}

function money(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(value ?? 0);
}

function exportReport(format: string) {
    window.location.href = route('reports.export', {
        report_type: 'trial_balance',
        format: format,
        date: selectedDate.value,
    });
}
</script>

<template>
    <Head title="Trial Balance" />

    <AppLayout title="Trial Balance" :breadcrumbs="breadcrumbs">
        <!-- Header -->
        <div class="mx-6 mt-6 mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-extrabold text-[#0a2342] dark:text-blue-400">Trial Balance</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Debits and Credits as at selected date</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <CalendarDays class="h-5 w-5 text-orange-500" />

                <input
                    type="date"
                    v-model="selectedDate"
                    @change="applyDateFilter"
                    class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-orange-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                />

                <!-- Export Buttons -->
                <button @click="exportReport('excel')" class="rounded-xl bg-green-600 px-4 py-2 text-sm text-white shadow hover:bg-green-700">
                    Excel
                </button>

                <button @click="exportReport('csv')" class="rounded-xl bg-blue-600 px-4 py-2 text-sm text-white shadow hover:bg-blue-700">CSV</button>

                <button @click="exportReport('pdf')" class="rounded-xl bg-red-600 px-4 py-2 text-sm text-white shadow hover:bg-red-700">PDF</button>
            </div>
        </div>

        <!-- Table Card -->
        <div class="mx-6 overflow-x-auto rounded-3xl border border-gray-200 bg-white shadow-md dark:border-gray-700 dark:bg-gray-900">
            <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-blue-50 dark:bg-[#0a2342]">
                    <tr>
                        <th class="px-6 py-3 font-semibold text-[#0a2342] dark:text-blue-400">Account</th>
                        <th class="px-6 py-3 font-semibold text-[#0a2342] dark:text-blue-400">Debit</th>
                        <th class="px-6 py-3 font-semibold text-[#0a2342] dark:text-blue-400">Credit</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="account in accounts" :key="account.name" class="transition-colors hover:bg-blue-50 dark:hover:bg-gray-800">
                        <td class="px-6 py-3">{{ account.name }}</td>
                        <td class="px-6 py-3 font-medium">{{ money(account.debit) }}</td>
                        <td class="px-6 py-3 font-medium">{{ money(account.credit) }}</td>
                    </tr>
                </tbody>

                <!-- Totals Row -->
                <tfoot class="bg-orange-50 font-bold text-[#0a2342] dark:bg-gray-800 dark:text-orange-400">
                    <tr>
                        <td class="px-6 py-3">Totals</td>
                        <td class="px-6 py-3">{{ money(totals.debits) }}</td>
                        <td class="px-6 py-3">{{ money(totals.credits) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-3" colspan="2">Difference</td>
                        <td class="px-6 py-3">{{ money(totals.difference) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </AppLayout>
</template>
