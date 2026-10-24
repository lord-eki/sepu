<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const breadcrumbs = [{ title: 'Reports', href: '/reports' }, { title: 'Loan Reports' }];

const reportLinks = [
    { title: 'Loan Portfolio', route: 'reports.loans.portfolio', description: 'Overview of loans by product and status' },
    { title: 'Loan Arrears', route: 'reports.loans.arrears', description: 'Loans in arrears and portfolio at risk' },
    { title: 'Loan Disbursement', route: 'reports.loans.disbursement', description: 'Daily and period disbursements' },
    { title: 'Loan Collection', route: 'reports.loans.collection', description: 'Loan repayments and collections' },
];

function navigateTo(routeName: string) {
    router.get(route(routeName));
}
</script>

<template>
    <Head title="Loan Reports" />

    <AppLayout title="Loan Reports" :breadcrumbs="breadcrumbs">
        <!-- Header Section -->
        <div class="mx-6 mt-6 mb-10">
            <h2 class="text-3xl font-extrabold tracking-tight text-[#0B1F3A] dark:text-orange-400">Loan Reports</h2>
            <p class="mt-3 max-w-2xl text-base text-gray-600 dark:text-gray-300">
                Access comprehensive loan analytics, portfolio summaries, arrears tracking, and repayment performance reports for your SACCO.
            </p>
        </div>

        <!-- Cards Section -->
        <div class="mx-6 mb-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-2">
            <div
                v-for="report in reportLinks"
                :key="report.title"
                @click="navigateTo(report.route)"
                class="group cursor-pointer rounded-2xl border border-orange-200 bg-gradient-to-br from-white to-orange-50 p-8 shadow-sm transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-2xl dark:border-[#1f3a63] dark:from-[#0B1F3A] dark:to-[#122C4F]"
            >
                <!-- Title -->
                <h3 class="text-xl font-semibold text-[#0B1F3A] transition-colors duration-300 group-hover:text-orange-500 dark:text-orange-400">
                    {{ report.title }}
                </h3>

                <!-- Description -->
                <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    {{ report.description }}
                </p>

                <!-- Accent line -->
                <div class="mt-6 h-1 w-12 rounded-full bg-orange-500 transition-all duration-300 group-hover:w-20"></div>
            </div>
        </div>
    </AppLayout>
</template>
