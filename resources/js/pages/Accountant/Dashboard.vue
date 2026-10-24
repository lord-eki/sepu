<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { CreditCard, FileCheck, FileWarning, Landmark, Wallet } from 'lucide-vue-next';

const props = defineProps<{
    stats: {
        accounts: {
            total_share_deposits: number;
            total_share_capital: number;
        };
        transactions: {
            pending: number;
            today_volume: number;
            today_count: number;
        };
        vouchers: {
            pending_approval: number;
            approved_unpaid: number;
            total_pending_amount: number;
        };
    };
    pendingTransactions: Array<any>;
    pendingVouchers: Array<any>;
    dailyTransactionSummary: Array<any>;
}>();

// Safe currency formatter
const fmt = (v: number | string | null | undefined) => {
    const num = parseFloat(v as string);
    if (isNaN(num)) return 'KES 0';
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        maximumFractionDigits: 0,
    }).format(num);
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <Head title="Accountant Dashboard" />

        <div class="space-y-8 p-6">
            <!-- Title -->
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-[#0A2342]">Accountant Dashboard</h1>
                <div class="mt-2 h-1 w-24 rounded bg-[#FF7A00]"></div>
            </div>

            <!-- Account Summary -->
            <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="item in [
                        {
                            title: 'Total Share Deposits',
                            value: fmt(stats.accounts.total_share_deposits),
                            icon: Wallet,
                            color: 'from-[#0A2342] to-[#163a73]',
                        },
                        {
                            title: 'Total Share Capital',
                            value: fmt(stats.accounts.total_share_capital),
                            icon: Landmark,
                            color: 'from-[#FF7A00] to-orange-500',
                        },
                        { title: 'Pending Transactions', value: stats.transactions.pending, icon: CreditCard, color: 'from-blue-900 to-blue-800' },
                    ]"
                    :key="item.title"
                    class="rounded-2xl border-0 bg-gradient-to-br text-white shadow-md transition-transform duration-300 hover:scale-[1.02]"
                    :class="item.color"
                >
                    <CardHeader class="flex items-center justify-between">
                        <CardTitle class="text-base font-medium tracking-wide">{{ item.title }}</CardTitle>
                        <div class="rounded-xl bg-white/20 p-2">
                            <component :is="item.icon" class="h-5 w-5" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl font-semibold">{{ item.value }}</div>
                    </CardContent>
                </Card>
            </section>

            <!-- Daily Transactions -->
            <section class="grid gap-6 sm:grid-cols-3">
                <Card class="rounded-2xl border-0 bg-white shadow-lg transition-all hover:shadow-xl">
                    <CardHeader>
                        <CardTitle class="text-[#0A2342]">Today's Volume</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold text-[#0A2342]">{{ fmt(stats.transactions.today_volume) }}</p>
                    </CardContent>
                </Card>

                <Card class="rounded-2xl border-0 bg-white shadow-lg transition-all hover:shadow-xl">
                    <CardHeader>
                        <CardTitle class="text-[#0A2342]">Today's Transactions</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold text-[#FF7A00]">{{ stats.transactions.today_count }}</p>
                    </CardContent>
                </Card>

                <Card class="rounded-2xl border-0 bg-white shadow-lg transition-all hover:shadow-xl">
                    <CardHeader>
                        <CardTitle class="text-[#0A2342]">Pending Approvals</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-2xl font-bold text-red-600">{{ stats.vouchers.pending_approval }}</p>
                    </CardContent>
                </Card>
            </section>

            <!-- Vouchers -->
            <section>
                <h2 class="mb-3 text-lg font-semibold text-[#0A2342]">Voucher Overview</h2>
                <div class="grid gap-6 sm:grid-cols-3">
                    <Card class="rounded-2xl border bg-white shadow-sm transition-all hover:shadow-md">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-[#0A2342]">
                                <FileWarning class="h-5 w-5 text-red-600" /> Pending Approval
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-2xl font-semibold text-red-600">{{ stats.vouchers.pending_approval }}</p>
                        </CardContent>
                    </Card>

                    <Card class="rounded-2xl border bg-white shadow-sm transition-all hover:shadow-md">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-[#0A2342]">
                                <FileCheck class="h-5 w-5 text-green-600" /> Approved (Unpaid)
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-2xl font-semibold text-green-600">{{ stats.vouchers.approved_unpaid }}</p>
                        </CardContent>
                    </Card>

                    <Card class="rounded-2xl border bg-white shadow-sm transition-all hover:shadow-md">
                        <CardHeader>
                            <CardTitle class="text-[#0A2342]">Total Pending Amount</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-2xl font-semibold text-[#FF7A00]">{{ fmt(stats.vouchers.total_pending_amount) }}</p>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <!-- Pending Vouchers Table -->
            <section>
                <h2 class="mb-3 text-lg font-semibold text-[#0A2342]">Recent Pending Vouchers</h2>
                <div class="overflow-x-auto rounded-2xl border bg-white shadow-sm transition-all hover:shadow-md">
                    <table class="w-full border-collapse text-left">
                        <thead class="bg-[#0A2342] text-white">
                            <tr class="text-sm">
                                <th class="p-3 font-medium">Voucher #</th>
                                <th class="p-3 font-medium">Created By</th>
                                <th class="p-3 font-medium">Amount</th>
                                <th class="p-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="v in pendingVouchers" :key="v.id" class="border-t transition-all hover:bg-orange-50">
                                <td class="p-3">{{ v.voucher_number }}</td>
                                <td class="p-3">{{ v.created_by?.name || '—' }}</td>
                                <td class="p-3">{{ fmt(v.amount) }}</td>
                                <td class="p-3 capitalize">{{ v.status }}</td>
                            </tr>
                            <tr v-if="!pendingVouchers.length">
                                <td colspan="4" class="py-4 text-center text-gray-400">No pending vouchers</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
