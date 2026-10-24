<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRightLeft, CalendarClock, CalendarDays, CalendarRange } from 'lucide-vue-next';

const props = defineProps<{
    summary?: {
        today: number;
        today_amount: number;
        this_month: number;
        this_month_amount: number;
        this_year: number;
        this_year_amount: number;
    };
}>();

// Always have a summary object
const summary = props.summary ?? {
    today: 0,
    today_amount: 0,
    this_month: 0,
    this_month_amount: 0,
    this_year: 0,
    this_year_amount: 0,
};

function money(value: number) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(value ?? 0);
}

const breadcrumbs = [{ title: 'Reports', href: route('reports.index') }, { title: 'Transaction Reports' }];
</script>

<template>
    <Head title="Transaction Reports" />

    <AppLayout title="Transaction Reports" :breadcrumbs="breadcrumbs">
        <!-- Header -->
        <div class="mx-5 mt-5 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Transaction Reports</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Analyze daily, monthly, and annual transaction performance</p>
        </div>

        <!-- Summary Cards -->
        <div class="mx-5 mb-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <ArrowRightLeft class="h-6 w-6 text-blue-500" />
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Today</p>
                        <p class="font-bold text-gray-900 dark:text-gray-100">{{ summary.today }} transactions</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ money(summary.today_amount) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <CalendarRange class="h-6 w-6 text-orange-500" />
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">This Month</p>
                        <p class="font-bold text-gray-900 dark:text-gray-100">{{ summary.this_month }} transactions</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ money(summary.this_month_amount) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <CalendarClock class="h-6 w-6 text-orange-500" />
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">This Year</p>
                        <p class="font-bold text-gray-900 dark:text-gray-100">{{ summary.this_year }} transactions</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ money(summary.this_year_amount) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Links -->
        <div class="mx-5 grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Daily -->
            <Link
                :href="route('reports.transactions.daily')"
                class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-orange-500 dark:border-gray-700 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <CalendarDays class="mb-3 h-8 w-8 text-orange-500" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Daily Transactions</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View all transactions for a selected day</p>
                    </div>
                </div>
            </Link>

            <!-- Monthly -->
            <Link
                :href="route('reports.transactions.monthly')"
                class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-orange-500 dark:border-gray-700 dark:bg-gray-900"
            >
                <CalendarRange class="mb-3 h-8 w-8 text-orange-500" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Monthly Transactions</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Analyze transaction trends by month</p>
            </Link>

            <!-- Annual -->
            <Link
                :href="route('reports.transactions.annual')"
                class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-orange-500 dark:border-gray-700 dark:bg-gray-900"
            >
                <CalendarClock class="mb-3 h-8 w-8 text-orange-500" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Annual Transactions</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Yearly transaction performance & growth</p>
            </Link>
        </div>
    </AppLayout>
</template>
