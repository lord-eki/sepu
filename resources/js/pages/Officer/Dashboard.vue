<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, ClipboardList, Clock, HandCoins } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    stats: any;
    pendingApplications: any[];
    overdueLoans: any[];
    loanPerformanceMetrics: any;
}>();

// Currency Formatter
const fmt = (v: any) => {
    const num = Number(v);
    if (isNaN(num)) return 'Ksh 0';
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        maximumFractionDigits: 0,
    }).format(num);
};

// Computed Summary Stats
const dashboardStats = computed(() => ({
    pending: props.stats.loans?.pending_applications || 0,
    underReview: props.stats.loans?.under_review || 0,
    approved: props.stats.loans?.approved_pending_disbursement || 0,
    active: props.stats.loans?.active_loans || 0,
    totalDisbursed: props.stats.portfolio?.total_disbursed || 0,
    outstanding: props.stats.portfolio?.outstanding_balance || 0,
    overdue: props.stats.portfolio?.overdue_amount || 0,
    target: props.stats.targets?.monthly_disbursement_target || 0,
    achieved: props.stats.targets?.monthly_disbursed || 0,
}));
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <Head title="Loan Officer Dashboard" />

        <div
            class="animate-fadeIn min-h-screen space-y-10 bg-gradient-to-b from-white via-blue-50 to-orange-50 p-6 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900"
        >
            <!-- Header -->
            <header class="space-y-2 text-center">
                <div class="rounded-2xl bg-gradient-to-r from-[#0b1b3f] to-blue-900 p-5">
                    <h1 class="text-2xl font-bold tracking-tight text-white dark:text-white">Loan Officer Dashboard</h1>
                    <p class="text-base text-orange-500 dark:text-orange-400">Monitor loan performance, portfolio health, and monthly targets.</p>
                </div>
            </header>

            <!-- Loan Summary Cards -->
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                <Card
                    v-for="(card, i) in [
                        { title: 'Pending Applications', icon: ClipboardList, color: 'from-blue-700 to-blue-900', value: dashboardStats.pending },
                        { title: 'Under Review', icon: Clock, color: 'from-orange-500 to-orange-700', value: dashboardStats.underReview },
                        {
                            title: 'Approved (Pending Disbursement)',
                            icon: CheckCircle2,
                            color: 'from-green-600 to-green-800',
                            value: dashboardStats.approved,
                        },
                        { title: 'Active Loans', icon: HandCoins, color: 'from-[#0b1b3f] to-blue-900', value: dashboardStats.active },
                    ]"
                    :key="i"
                    class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white/80 shadow-sm backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800/60"
                >
                    <div
                        class="absolute inset-0 bg-gradient-to-r opacity-10 transition-all duration-500 group-hover:opacity-20"
                        :class="card.color"
                    ></div>

                    <CardHeader class="relative z-10 flex flex-row items-center justify-between pb-1">
                        <CardTitle class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                            {{ card.title }}
                        </CardTitle>
                        <component :is="card.icon" class="h-6 w-6 rounded-md bg-gradient-to-r p-1.5 text-white" :class="card.color" />
                    </CardHeader>

                    <CardContent class="relative z-10">
                        <div class="text-3xl font-bold text-[#0b1b3f] dark:text-white">
                            {{ card.value }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Portfolio & Progress Section -->
            <div class="grid gap-6 xl:grid-cols-2">
                <!-- Portfolio Summary -->
                <Card
                    class="rounded-2xl border border-gray-200 bg-white/80 shadow-sm backdrop-blur-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800/60"
                >
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-[#0b1b3f] dark:text-white"> Portfolio Overview </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                            <div class="rounded-xl bg-blue-50 p-4 transition hover:bg-blue-100 dark:bg-gray-800 dark:hover:bg-gray-700">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Disbursed</p>
                                <p class="mt-1 text-lg font-semibold text-[#0b1b3f] dark:text-blue-400">
                                    {{ fmt(dashboardStats.totalDisbursed) }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-indigo-50 p-4 transition hover:bg-indigo-100 dark:bg-gray-800 dark:hover:bg-gray-700">
                                <p class="text-sm font-medium text-gray-600">Outstanding Balance</p>
                                <p class="mt-1 text-lg font-semibold text-blue-900 dark:text-indigo-400">
                                    {{ fmt(dashboardStats.outstanding) }}
                                </p>
                            </div>
                            <div class="rounded-xl bg-orange-50 p-4 transition hover:bg-orange-100 dark:bg-gray-800 dark:hover:bg-gray-700">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Overdue Amount</p>
                                <p class="mt-1 text-lg font-semibold text-orange-700 dark:text-orange-400">
                                    {{ fmt(dashboardStats.overdue) }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Monthly Target Progress -->
                <Card
                    class="rounded-2xl border border-gray-200 bg-white/80 shadow-sm backdrop-blur-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800/60"
                >
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold text-[#0b1b3f] dark:text-white"> Monthly Disbursement Progress </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="mb-2 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                            <p>Target: {{ fmt(dashboardStats.target) }}</p>
                            <p>Achieved: {{ fmt(dashboardStats.achieved) }}</p>
                        </div>

                        <div class="h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div
                                class="h-3 rounded-full bg-gradient-to-r from-[#ff7b00] to-orange-600 transition-all duration-700 ease-in-out"
                                :style="{ width: `${Math.min((dashboardStats.achieved / dashboardStats.target) * 100, 100)}%` }"
                            ></div>
                        </div>

                        <p class="mt-1 text-right text-xs text-gray-500">
                            {{ Math.min((dashboardStats.achieved / dashboardStats.target) * 100, 100).toFixed(1) }}% of target reached
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Pending Applications -->
            <div>
                <h2 class="mb-3 text-lg font-semibold text-[#0b1b3f] dark:text-white">Recent Pending Loan Applications</h2>
                <Card
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white/90 shadow-sm backdrop-blur-sm dark:border-gray-700 dark:bg-gray-800/70"
                >
                    <CardContent class="p-0">
                        <table class="min-w-full border-collapse text-left text-sm">
                            <thead class="bg-gradient-to-r from-[#0b1b3f] to-blue-900 text-white">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Member</th>
                                    <th class="px-4 py-3 font-medium">Product</th>
                                    <th class="px-4 py-3 font-medium">Amount</th>
                                    <th class="px-4 py-3 font-medium">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(loan, i) in pendingApplications"
                                    :key="i"
                                    class="border-b transition-colors hover:bg-orange-50 dark:border-gray-700 dark:hover:bg-gray-700"
                                >
                                    <td class="px-4 py-3">{{ loan.member?.first_name }} {{ loan.member?.last_name }}</td>
                                    <td class="px-4 py-3">{{ loan.loan_product?.name || '—' }}</td>
                                    <td class="px-4 py-3">{{ fmt(loan.applied_amount) }}</td>
                                    <td class="px-4 py-3">{{ new Date(loan.created_at).toLocaleDateString() }}</td>
                                </tr>

                                <tr v-if="!pendingApplications?.length">
                                    <td colspan="4" class="py-5 text-center text-gray-400">No pending loan applications</td>
                                </tr>
                            </tbody>
                        </table>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fadeIn {
    animation: fadeIn 0.6s ease-out;
}
</style>
