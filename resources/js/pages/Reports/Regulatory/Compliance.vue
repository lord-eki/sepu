<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { AlertTriangle, Calendar, CheckCircle2, Filter, ShieldCheck, TrendingUp, XCircle } from 'lucide-vue-next';

const props = defineProps<{
    compliance: Record<string, any>;
    compliance_score: number;
    start_date: string;
    end_date: string;
}>();

const breadcrumbs = [{ title: 'Regulatory Reports', href: route('reports.regulatoryReport.index') }, { title: 'Compliance Reports' }];

const submitFilter = (e: Event) => {
    e.preventDefault();

    const form = e.target as HTMLFormElement;
    const data = new FormData(form);

    router.get(
        route('reports.regulatory.compliance'),
        {
            start_date: data.get('start_date'),
            end_date: data.get('end_date'),
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const getStatusIcon = (value: boolean | number) => {
    if (value === true || value >= 80) return CheckCircle2;
    if (value === false || value < 50) return XCircle;
    return AlertTriangle;
};

const getStatusColor = (value: boolean | number) => {
    if (value === true || value >= 80) return 'text-green-600 bg-green-50 dark:bg-green-900/20';

    if (value === false || value < 50) return 'text-red-600 bg-red-50 dark:bg-red-900/20';

    return 'text-orange-600 bg-orange-50 dark:bg-orange-900/20';
};

const formatValue = (value: any) => {
    if (value === null || value === undefined) return '-';

    // boolean
    if (typeof value === 'boolean') return value ? 'Compliant' : 'Non-Compliant';

    // number
    if (typeof value === 'number') return `${value}%`;

    // array
    if (Array.isArray(value)) {
        if (value.length === 0) return 'No issues';

        return `${value.length} item(s)`;
    }

    // object
    if (typeof value === 'object') {
        const keys = Object.keys(value);

        if (keys.length === 0) return 'No issues';

        return `${keys.length} record(s)`;
    }

    // string
    return value;
};

const scoreColor = () => {
    if (props.compliance_score >= 80) return 'from-green-600 to-emerald-500';

    if (props.compliance_score >= 50) return 'from-orange-500 to-amber-500';

    return 'from-red-600 to-rose-500';
};
</script>

<template>
    <Head title="Compliance Reports" />

    <AppLayout title="Compliance Reports" :breadcrumbs="breadcrumbs">
        <div class="mx-5 mt-5 space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Compliance Reports</h1>

                <p class="text-sm text-gray-500 dark:text-gray-400">Regulatory compliance monitoring, audit readiness, and risk assessment</p>
            </div>

            <!-- Filters -->
            <form
                @submit="submitFilter"
                class="flex flex-col items-end gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm md:flex-row dark:border-gray-700 dark:bg-gray-900"
            >
                <div class="flex-1">
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300"> Start Date </label>

                    <input
                        type="date"
                        name="start_date"
                        :value="start_date"
                        class="mt-1 w-full rounded-xl border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <div class="flex-1">
                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300"> End Date </label>

                    <input
                        type="date"
                        name="end_date"
                        :value="end_date"
                        class="mt-1 w-full rounded-xl border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800"
                    />
                </div>

                <button
                    type="submit"
                    class="flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-medium text-white transition hover:opacity-90 dark:bg-white dark:text-gray-900"
                >
                    <Filter class="h-4 w-4" />
                    Apply Filter
                </button>
            </form>

            <!-- Compliance Score Card -->
            <div class="rounded-2xl bg-gradient-to-r p-6 text-white shadow-md" :class="scoreColor()">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="rounded-xl bg-white/20 p-3">
                            <ShieldCheck class="h-8 w-8" />
                        </div>

                        <div>
                            <p class="text-sm opacity-90">Overall Compliance Score</p>

                            <p class="text-3xl font-bold">{{ compliance_score }}%</p>
                        </div>
                    </div>

                    <div class="hidden items-center gap-2 text-sm opacity-90 md:flex">
                        <TrendingUp class="h-4 w-4" />
                        Regulatory Health Indicator
                    </div>
                </div>
            </div>

            <!-- Compliance Items Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="(value, key) in compliance"
                    :key="key"
                    class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-900"
                >
                    <!-- Header -->
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg p-2" :class="getStatusColor(value)">
                            <component :is="getStatusIcon(value)" class="h-5 w-5" />
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 capitalize dark:text-white">
                                {{ key.replaceAll('_', ' ') }}
                            </h3>

                            <p class="text-xs text-gray-500 dark:text-gray-400">Compliance indicator</p>
                        </div>
                    </div>

                    <!-- Value -->
                    <div class="mt-4">
                        <div class="text-base text-gray-900 dark:text-white">
                            {{ formatValue(value) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <Calendar class="h-4 w-4" />
                Showing compliance data from
                <span class="font-medium">
                    {{ start_date }}
                </span>
                to
                <span class="font-medium">
                    {{ end_date }}
                </span>
            </div>
        </div>
    </AppLayout>
</template>
