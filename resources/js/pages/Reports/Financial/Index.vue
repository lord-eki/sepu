<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BarChart3, Scale, TrendingUp, Wallet } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const breadcrumbs = [{ title: 'Reports', href: '/reports' }, { title: 'Financial Reports' }];

const reports = [
    {
        title: 'Balance Sheet',
        description: 'Assets, Liabilities & Equity',
        icon: Scale,
        href: route('reports.financial.balance-sheet'),
    },
    {
        title: 'Income Statement',
        description: 'Revenue & Expenses',
        icon: TrendingUp,
        href: route('reports.financial.income-statement'),
    },
    {
        title: 'Cash Flow',
        description: 'Operating, Investing & Financing',
        icon: Wallet,
        href: route('reports.financial.cash-flow'),
    },
    {
        title: 'Trial Balance',
        description: 'Debits & Credits',
        icon: BarChart3,
        href: route('reports.financial.trial-balance'),
    },
];
</script>

<template>
    <Head title="Financial Reports" />

    <AppLayout :breadcrumbs="breadcrumbs" title="Financial Reports">
        <!-- Header -->
        <div class="mx-6 mt-5 mb-6">
            <h2 class="text-xl font-bold text-gray-900 sm:text-2xl dark:text-gray-100">Financial Reports</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and analyze financial performance and statements</p>
        </div>

        <!-- Reports Grid -->
        <div class="m-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:m-10">
            <Link
                v-for="report in reports"
                :key="report.title"
                :href="report.href"
                class="group relative overflow-hidden rounded-2xl border border-[#0a2342]/20 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-gray-700 dark:bg-gray-900"
            >
                <!-- Gradient Glow -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-orange-500/10 via-transparent to-blue-500/10 opacity-0 transition group-hover:opacity-100"
                />

                <!-- Icon -->
                <div class="relative mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-orange-50 dark:bg-orange-500/10">
                    <component :is="report.icon" class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                </div>

                <!-- Text -->
                <div class="relative">
                    <h3
                        class="text-base font-semibold text-gray-900 transition group-hover:text-orange-600 dark:text-gray-100 dark:group-hover:text-orange-400"
                    >
                        {{ report.title }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ report.description }}
                    </p>
                </div>

                <!-- Arrow -->
                <span class="absolute right-5 bottom-5 text-gray-300 transition group-hover:text-orange-500 dark:text-gray-600"> → </span>
            </Link>
        </div>
    </AppLayout>
</template>
