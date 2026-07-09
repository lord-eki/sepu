<template>
    <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/my-accounts' }, { title: 'Statement' }]">
        <Head title="Account Statement" />

        <div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
            <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

                <!-- ================= HEADER ================= -->
                <div
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-900 via-slate-800 to-orange-500 p-6 shadow-2xl"
                >
                    <div
                        class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-3xl"
                    ></div>

                    <div
                        class="absolute -bottom-20 left-10 h-56 w-56 rounded-full bg-orange-300/20 blur-3xl"
                    ></div>

                    <div
                        class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <!-- Left -->
                        <div class="space-y-2 text-white">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 backdrop-blur"
                                >
                                    <Wallet class="h-7 w-7" />
                                </div>

                                <div>
                                    <h1 class="text-2xl font-bold tracking-wide">
                                        Account Statement
                                    </h1>

                                    <p class="text-blue-100">
                                        {{ account.member?.first_name }}
                                        {{ account.member?.last_name }}
                                    </p>
                                </div>

                            </div>

                            <div class="flex flex-wrap gap-4 pt-2 text-sm text-blue-100">

                                <div>
                                    <span class="font-medium text-white">
                                        Account:
                                    </span>

                                    {{ account.account_number }}
                                </div>

                                <div>
                                    <span class="font-medium text-white">
                                        Period:
                                    </span>

                                    {{ formatDate(fromDate) }}
                                    -
                                    {{ formatDate(toDate) }}
                                </div>

                            </div>

                        </div>

                        <!-- Right -->
                        <div class="flex flex-wrap items-center gap-4">

                            <div
                                class="rounded-2xl border border-white/20 bg-white/10 px-6 py-4 text-center backdrop-blur"
                            >
                                <p
                                    class="text-xs uppercase tracking-widest text-blue-100"
                                >
                                    Current Balance
                                </p>

                                <p class="mt-1 text-2xl font-bold text-white">
                                    KES {{ formatCurrency(account.balance) }}
                                </p>
                            </div>

                            <a
                                :href="route('my-accounts.statement.pdf',{
                                    account:account.id,
                                    from:fromDate,
                                    to:toDate
                                })"
                                target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 font-semibold text-blue-900 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:bg-blue-50"
                            >
                                <Download class="h-5 w-5" />
                                Download PDF
                            </a>

                        </div>

                    </div>
                </div>

                <!-- ================= FILTER ================= -->

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div
                        class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-5"
                    >
                        <div>
                            <label
                                class="mb-2 block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                From
                            </label>

                            <input
                                type="date"
                                v-model="fromDate"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"
                            >
                                To
                            </label>

                            <input
                                type="date"
                                v-model="toDate"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>

                        <div class="flex items-end">

                            <button
                                @click="applyFilter"
                                :disabled="loading"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 py-3 font-medium text-white transition hover:bg-blue-800 disabled:opacity-60"
                            >
                                <Loader2
                                    v-if="loading"
                                    class="h-4 w-4 animate-spin"
                                />

                                {{ loading ? 'Refreshing...' : 'Apply Filter' }}
                            </button>

                        </div>
                    </div>
                </div>

                <!-- ================= TRANSACTIONS ================= -->

                <div
                    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >

                    <div
                        class="flex flex-col gap-3 border-b border-slate-200 bg-slate-100 px-6 py-5 dark:border-slate-800 dark:bg-slate-900/60 sm:flex-row sm:items-center sm:justify-between"
                    >

                        <div>
                            <h2
                                class="text-lg font-semibold text-slate-800 dark:text-white"
                            >
                                Transaction History
                            </h2>

                            <p
                                class="text-sm text-slate-500 dark:text-slate-400"
                            >
                                {{ transactions.length }} Transaction<span
                                    v-if="transactions.length != 1"
                                    >s</span
                                >
                            </p>
                        </div>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="min-w-full">

                            <thead
                                class="bg-slate-50 text-xs uppercase tracking-wider text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                            >
                                <tr>
                                    <th class="px-6 py-4 text-left">Date</th>
                                    <th class="px-6 py-4 text-left">Type</th>
                                    <th class="px-6 py-4 text-right">Amount</th>
                                    <th class="px-6 py-4 text-right">Balance</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-left">
                                        Description
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-slate-200 dark:divide-slate-800"
                            >

                                <tr
                                    v-for="tx in transactions"
                                    :key="tx.id"
                                    class="transition hover:bg-blue-50 dark:hover:bg-slate-800/60"
                                >

                                    <td
                                        class="px-6 py-5 text-slate-600 dark:text-slate-300"
                                    >
                                        {{ formatDateTime(tx.created_at) }}
                                    </td>

                                    <td
                                        class="px-6 py-5 font-medium capitalize text-slate-700 dark:text-white"
                                    >
                                        {{ tx.transaction_type.replace('_',' ') }}
                                    </td>

                                    <td
                                        class="px-6 py-5 text-right font-bold"
                                        :class="amountColor(tx)"
                                    >
                                        KES {{ formatCurrency(tx.amount) }}
                                    </td>

                                    <td
                                        class="px-6 py-5 text-right font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        KES {{ formatCurrency(tx.balance_after) }}
                                    </td>

                                    <td class="px-6 py-5 text-center">

                                        <span
                                            :class="statusClass(tx.status)"
                                        >
                                            {{ tx.status }}
                                        </span>

                                    </td>

                                    <td
                                        class="px-6 py-5 text-slate-500 dark:text-slate-400"
                                    >
                                        {{ tx.description || "—" }}
                                    </td>

                                </tr>

                                <tr v-if="!transactions.length">

                                    <td
                                        colspan="6"
                                        class="py-16 text-center text-slate-500 dark:text-slate-400"
                                    >
                                        No transactions found for this period.
                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Download, Loader2, Wallet } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    account: any;
    transactions: any[];
    period: { from: string; to: string };
}>();

const fromDate = ref(props.period.from);
const toDate = ref(props.period.to);
const loading = ref(false);

const applyFilter = () => {
    loading.value = true;

    router.get(
        route('my-accounts.statement', {
            member: props.account.member.id,
            account: props.account.id,
        }),
        {
            from: fromDate.value,
            to: toDate.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => (loading.value = false),
            onError: () => (loading.value = false),
        },
    );
};

const formatCurrency = (amount: number) => new Intl.NumberFormat('en-KE', { minimumFractionDigits: 0 }).format(amount);

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-KE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });

const formatDateTime = (date: string) =>
    new Date(date).toLocaleString('en-KE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });

const statusClass = (status: string) => {
    switch (status) {
        case 'completed':
            return 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium';
        case 'pending':
            return 'px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-medium';
        case 'failed':
            return 'px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium';
        default:
            return 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600 font-medium';
    }
};

const amountColor = (tx: any) => {
    if (tx.transaction_type.includes('deposit') || tx.transaction_type.includes('credit')) {
        return 'text-green-600';
    }
    if (tx.transaction_type.includes('withdraw') || tx.transaction_type.includes('debit')) {
        return 'text-red-600';
    }
    return 'text-gray-800';
};
</script>
