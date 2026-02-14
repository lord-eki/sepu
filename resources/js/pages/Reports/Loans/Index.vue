<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'

const breadcrumbs = [
    { title: 'Reports', href: '/reports' },
    { title: 'Loan Reports' }
]

const reportLinks = [
    { title: 'Loan Portfolio', route: 'reports.loans.portfolio', description: 'Overview of loans by product and status' },
    { title: 'Loan Arrears', route: 'reports.loans.arrears', description: 'Loans in arrears and portfolio at risk' },
    { title: 'Loan Disbursement', route: 'reports.loans.disbursement', description: 'Daily and period disbursements' },
    { title: 'Loan Collection', route: 'reports.loans.collection', description: 'Loan repayments and collections' },
]

function navigateTo(routeName: string) {
    router.get(route(routeName))
}
</script>

<template>
    <Head title="Loan Reports" />

    <AppLayout title="Loan Reports" :breadcrumbs="breadcrumbs">

        <!-- Header Section -->
        <div class="mb-10 mx-6 mt-6">
            <h2 class="text-3xl font-extrabold text-[#0B1F3A] dark:text-orange-400 tracking-tight">
                Loan Reports
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mt-3 text-base max-w-2xl">
                Access comprehensive loan analytics, portfolio summaries, arrears tracking, 
                and repayment performance reports for your SACCO.
            </p>
        </div>

        <!-- Cards Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-8 mx-6 mb-10">

            <div 
                v-for="report in reportLinks" 
                :key="report.title"
                @click="navigateTo(report.route)"
                class="group cursor-pointer rounded-2xl p-8 
                       bg-gradient-to-br from-white to-orange-50 
                       dark:from-[#0B1F3A] dark:to-[#122C4F]
                       border border-orange-200 dark:border-[#1f3a63]
                       shadow-sm hover:shadow-2xl 
                       hover:-translate-y-1 
                       transition-all duration-300 ease-in-out"
            >

                <!-- Title -->
                <h3 class="text-xl font-semibold text-[#0B1F3A] 
                           dark:text-orange-400 
                           group-hover:text-orange-500 
                           transition-colors duration-300">
                    {{ report.title }}
                </h3>

                <!-- Description -->
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ report.description }}
                </p>

                <!-- Accent line -->
                <div class="mt-6 h-1 w-12 bg-orange-500 rounded-full 
                            group-hover:w-20 transition-all duration-300">
                </div>

            </div>
        </div>

    </AppLayout>
</template>
