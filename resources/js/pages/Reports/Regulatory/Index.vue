<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { FileText, ShieldCheck } from 'lucide-vue-next';

const breadcrumbs = [{ title: 'Reports', href: route('reports.index') }, { title: 'Regulatory Reports' }];

const reports = [
    {
        title: 'Statutory Reports',
        description: 'Official statutory and regulator-required reports',
        icon: FileText,
        route: 'reports.regulatory.statutory',
    },
    {
        title: 'Compliance Reports',
        description: 'KYC, AML, liquidity, capital and compliance monitoring',
        icon: ShieldCheck,
        route: 'reports.regulatory.compliance',
    },
];

function goTo(routeName: string) {
    router.get(route(routeName));
}
</script>

<template>
    <Head title="Regulatory Reports" />

    <AppLayout title="Regulatory Reports" :breadcrumbs="breadcrumbs">
        <!-- Intro -->
        <div class="mx-5 mt-5 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Regulatory & Compliance Reports</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Reports required for regulators, audits, and compliance oversight</p>
        </div>

        <!-- Report Cards -->
        <div class="mx-5 grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div
                v-for="report in reports"
                :key="report.title"
                @click="goTo(report.route)"
                class="cursor-pointer rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:shadow-lg dark:border-gray-700 dark:bg-gray-900"
            >
                <div class="flex items-start gap-4">
                    <div class="rounded-xl bg-orange-50 p-3 text-orange-600 dark:bg-orange-900/20">
                        <component :is="report.icon" class="h-6 w-6" />
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ report.title }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ report.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compliance Notice -->
        <div class="mx-5 mt-10 rounded-xl border border-yellow-200 bg-yellow-50 p-5 dark:border-yellow-800 dark:bg-yellow-900/20">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                ⚠️ Regulatory reports should be reviewed before submission to regulators. Ensure all data is verified and approved.
            </p>
        </div>
    </AppLayout>
</template>
