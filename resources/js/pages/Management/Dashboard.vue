<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { AlertTriangle, ArrowUpRight, BarChart3, ClipboardCheck, HandCoins, TrendingUp, Wallet } from 'lucide-vue-next';

const props = defineProps<{
    stats: {
        financial_summary: {
            total_assets: number;
            loan_portfolio: number;
            monthly_income: number;
            monthly_expenses: number;
        };
        performance: {
            portfolio_at_risk: number;
            loan_recovery_rate: number;
            member_growth_rate: number;
        };
        compliance: {
            overdue_vouchers: number;
            pending_reviews: number;
        };
    };
    loanPortfolioAnalysis: Array<{ name: string; count: number; total_outstanding: number }>;
}>();

const fmt = (v: number) => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(v);
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <Head title="Management Dashboard" />

        <div class="min-h-screen space-y-10 bg-[#f5f7fb] p-6">
            <!-- Header Banner -->
            <div
                class="flex flex-col items-center justify-between rounded-3xl bg-gradient-to-r from-[#0B2B40] to-[#133263] p-6 text-white shadow-lg sm:flex-row"
            >
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Management <span class="text-orange-500">Dashboard</span></h1>
                    <p class="text-sm text-blue-100">Strategic overview and SACCO performance</p>
                </div>
                <div class="mt-3 flex items-center gap-3 sm:mt-0">
                    <div class="h-1.5 w-24 rounded-full bg-orange-500"></div>
                </div>
            </div>

            <!-- Financial Overview -->
            <section>
                <h2 class="mb-3 text-lg font-semibold text-[#0a2342]">Financial Summary</h2>
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <Card
                        v-for="item in [
                            { title: 'Total Assets', value: fmt(stats.financial_summary.total_assets), icon: Wallet },
                            { title: 'Loan Portfolio', value: fmt(stats.financial_summary.loan_portfolio), icon: HandCoins },
                            { title: 'Monthly Income', value: fmt(stats.financial_summary.monthly_income), icon: TrendingUp },
                            { title: 'Monthly Expenses', value: fmt(stats.financial_summary.monthly_expenses), icon: ArrowUpRight },
                        ]"
                        :key="item.title"
                        class="rounded-2xl border border-gray-100 bg-white shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
                    >
                        <CardHeader class="flex items-center justify-between">
                            <CardTitle class="text-sm font-medium text-gray-700">{{ item.title }}</CardTitle>
                            <div class="rounded-xl bg-orange-100 p-2 text-orange-600">
                                <component :is="item.icon" class="h-5 w-5" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="text-lg font-semibold text-[#0a2342]">{{ item.value }}</div>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <!-- Performance Overview -->
            <section>
                <h2 class="mb-3 text-lg font-semibold text-[#0a2342]">Performance Indicators</h2>
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <Card class="rounded-2xl border border-gray-100 bg-white shadow-md transition-all hover:shadow-lg">
                        <CardHeader class="flex items-center justify-between">
                            <CardTitle class="text-sm font-medium text-gray-700">Portfolio at Risk</CardTitle>
                            <AlertTriangle class="h-5 w-5 text-red-600" />
                        </CardHeader>
                        <CardContent>
                            <p class="text-3xl font-semibold text-red-600">{{ stats.performance.portfolio_at_risk }}%</p>
                            <p class="mt-1 text-sm text-gray-500">Lower is better</p>
                        </CardContent>
                    </Card>

                    <Card class="rounded-2xl border border-gray-100 bg-white shadow-md transition-all hover:shadow-lg">
                        <CardHeader class="flex items-center justify-between">
                            <CardTitle class="text-sm font-medium text-gray-700">Loan Recovery Rate</CardTitle>
                            <ClipboardCheck class="h-5 w-5 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <p class="text-3xl font-semibold text-green-600">{{ stats.performance.loan_recovery_rate }}%</p>
                            <p class="mt-1 text-sm text-gray-500">Higher is better</p>
                        </CardContent>
                    </Card>

                    <Card class="rounded-2xl border border-gray-100 bg-white shadow-md transition-all hover:shadow-lg">
                        <CardHeader class="flex items-center justify-between">
                            <CardTitle class="text-sm font-medium text-gray-700">Member Growth Rate</CardTitle>
                            <BarChart3 class="h-5 w-5 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <p class="text-3xl font-semibold text-blue-600">{{ stats.performance.member_growth_rate }}%</p>
                            <p class="mt-1 text-sm text-gray-500">Year-over-year growth</p>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <!-- Compliance Summary -->
            <section>
                <h2 class="mb-3 text-lg font-semibold text-[#0a2342]">Compliance Summary</h2>
                <div class="grid gap-6 sm:grid-cols-2">
                    <Card class="rounded-2xl border border-gray-100 bg-white shadow-md transition-all hover:shadow-lg">
                        <CardHeader class="flex items-center justify-between">
                            <CardTitle class="text-sm font-medium text-gray-700">Overdue Vouchers</CardTitle>
                            <AlertTriangle class="h-5 w-5 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <p class="text-3xl font-semibold text-[#0a2342]">{{ stats.compliance.overdue_vouchers }}</p>
                            <p class="mt-1 text-sm text-gray-500">Requires urgent action</p>
                        </CardContent>
                    </Card>

                    <Card class="rounded-2xl border border-gray-100 bg-white shadow-md transition-all hover:shadow-lg">
                        <CardHeader class="flex items-center justify-between">
                            <CardTitle class="text-sm font-medium text-gray-700">Pending Reviews</CardTitle>
                            <ClipboardCheck class="h-5 w-5 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <p class="text-3xl font-semibold text-[#0a2342]">{{ stats.compliance.pending_reviews }}</p>
                            <p class="mt-1 text-sm text-gray-500">Awaiting management feedback</p>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <!-- Loan Portfolio Analysis -->
            <section>
                <h2 class="mb-3 text-lg font-semibold text-[#0a2342]">Loan Portfolio Analysis</h2>
                <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-md">
                    <table class="w-full text-sm">
                        <thead class="bg-[#0B2B40] text-xs tracking-wide text-white uppercase">
                            <tr>
                                <th class="p-3 text-left">Product</th>
                                <th class="p-3 text-left">Count</th>
                                <th class="p-3 text-left">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in loanPortfolioAnalysis" :key="p.name" class="border-t transition hover:bg-orange-50">
                                <td class="p-3 font-medium text-[#0a2342]">{{ p.name }}</td>
                                <td class="p-3">{{ p.count }}</td>
                                <td class="p-3 font-semibold text-orange-600">{{ fmt(p.total_outstanding) }}</td>
                            </tr>
                            <tr v-if="!loanPortfolioAnalysis.length">
                                <td colspan="3" class="p-6 text-center text-gray-500">No data available</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
