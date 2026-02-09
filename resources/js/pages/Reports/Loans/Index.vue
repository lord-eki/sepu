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

        <div class="mb-8 mx-5 mt-5">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                Loan Reports
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mt-2 text-base">
                Access detailed loan reports for your SACCO
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 m-6 gap-6">
            <div v-for="report in reportLinks" :key="report.title" @click="navigateTo(report.route)"
                class="cursor-pointer rounded-2xl border border-blue-200 bg-gray-50 dark:bg-[#0a2342] p-6 sm:p-10 hover:scale-105 hover:shadow-xl transition-transform duration-200">
                <h3 class="text-lg font-bold text-[#0a2342] dark:text-orange-400 mb-2">
                    {{ report.title }}
                </h3>
                <p class="text-sm text-orange-500 dark:text-gray-300">
                    {{ report.description }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
