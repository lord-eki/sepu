<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Calendar, CreditCard, Droplet, Landmark, LineChart, Shield, Users } from 'lucide-vue-next';

const props = defineProps<{
    reports: Record<string, any>;
    year: number;
    quarter?: string | null;
    periods: any;
}>();

const breadcrumbs = [{ title: 'Regulatory Reports', href: route('reports.regulatoryReport.index') }, { title: 'Statutory Reports' }];

const sections = [
    {
        key: 'membership_report',
        title: 'Membership Report',
        icon: Users,
        description: 'Overview of SACCO membership statistics',
    },
    {
        key: 'financial_performance',
        title: 'Financial Performance',
        icon: LineChart,
        description: 'Income, expenses, and profitability metrics',
    },
    {
        key: 'loan_portfolio_report',
        title: 'Loan Portfolio',
        icon: CreditCard,
        description: 'Loan disbursement, repayment, and risk analysis',
    },
    {
        key: 'liquidity_report',
        title: 'Liquidity Position',
        icon: Droplet,
        description: 'Cash availability and liquidity ratios',
    },
    {
        key: 'capital_adequacy',
        title: 'Capital Adequacy',
        icon: Landmark,
        description: 'Capital reserves and regulatory capital ratios',
    },
    {
        key: 'governance_report',
        title: 'Governance & Management',
        icon: Shield,
        description: 'Governance structure and compliance status',
    },
];

function formatValue(value: any) {
    if (typeof value === 'number') {
        return new Intl.NumberFormat('en-KE', {
            style: 'currency',
            currency: 'KES',
        }).format(value);
    }

    if (value === null || value === undefined || value === '') {
        return '-';
    }

    return value;
}

function hasData(sectionKey: string) {
    const data = props.reports?.[sectionKey];

    if (!data) return false;

    if (Array.isArray(data)) return data.length > 0;

    return Object.keys(data).length > 0;
}
</script>

<template>
    <Head title="Statutory Reports" />

    <AppLayout title="Statutory Reports" :breadcrumbs="breadcrumbs">
        <!-- Header -->
        <div class="mx-5 mt-5 mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Statutory Reports</h2>

                <p class="mt-1 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <Calendar class="h-4 w-4" />
                    Reporting Period:
                    <span class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ year }}
                        <span v-if="quarter"> — {{ quarter }}</span>
                    </span>
                </p>
            </div>
        </div>

        <!-- Sections -->
        <div class="mx-5 space-y-6">
            <div
                v-for="section in sections"
                :key="section.key"
                class="rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-900"
            >
                <!-- Section Header -->
                <div class="flex items-center justify-between border-b border-gray-100 p-6 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl bg-orange-50 p-2 text-orange-600 dark:bg-orange-900/20">
                            <component :is="section.icon" class="h-5 w-5" />
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ section.title }}
                            </h3>

                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ section.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Section Content -->
                <div class="p-6">
                    <!-- DATA EXISTS -->
                    <div v-if="hasData(section.key)">
                        <!-- OBJECT VIEW -->
                        <div v-if="!Array.isArray(reports[section.key])" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div v-for="(value, key) in reports[section.key]" :key="key" class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                                <div class="text-xs text-gray-500 capitalize dark:text-gray-400">
                                    {{ key.replaceAll('_', ' ') }}
                                </div>

                                <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ formatValue(value) }}
                                </div>
                            </div>
                        </div>

                        <!-- ARRAY VIEW -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th
                                            v-for="(val, key) in reports[section.key][0]"
                                            :key="key"
                                            class="px-4 py-3 text-left font-medium text-gray-600 capitalize dark:text-gray-400"
                                        >
                                            {{ key.replaceAll('_', ' ') }}
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr
                                        v-for="(row, index) in reports[section.key]"
                                        :key="index"
                                        class="border-b border-gray-100 transition hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                                    >
                                        <td v-for="(val, key) in row" :key="key" class="px-4 py-3 text-gray-800 dark:text-gray-200">
                                            {{ formatValue(val) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- EMPTY STATE -->
                    <div v-else class="py-10 text-center">
                        <div class="mb-2 text-gray-400 dark:text-gray-500">
                            <Shield class="mx-auto h-10 w-10 opacity-50" />
                        </div>

                        <div class="text-sm text-gray-500 dark:text-gray-400">No data available for this reporting period</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mx-5 mt-8 mb-6 rounded-xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-900/20">
            <p class="text-sm text-blue-800 dark:text-blue-200">
                These statutory reports are generated automatically based on SACCO system data and comply with regulatory reporting standards.
            </p>
        </div>
    </AppLayout>
</template>
