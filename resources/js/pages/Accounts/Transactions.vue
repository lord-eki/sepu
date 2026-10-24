<template>
    <AppLayout :breadcrumbs="[{ title: 'Accounts', href: route('accounts.index') }, { title: 'Transactions' }]">
        <Head title="Account Transactions" />

        <div class="min-h-screen bg-slate-50 py-8">
            <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- HEADER -->
                <div
                    class="flex flex-col rounded-2xl bg-gradient-to-r from-blue-900 to-blue-800 p-6 text-white shadow-xl sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="text-xl font-bold tracking-tight">Account Transactions</h2>

                        <p class="mt-1 text-sm text-blue-200">
                            {{ account.account_number }} — {{ account.member.first_name }} {{ account.member.last_name }}
                        </p>
                    </div>

                    <div class="mt-4 flex gap-3 sm:mt-0">
                        <Link
                            :href="route('accounts.show', account.id)"
                            class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-blue-900 shadow-sm transition hover:bg-slate-100"
                        >
                            Back to Account
                        </Link>

                        <button
                            @click="exportTransactions"
                            class="rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600"
                        >
                            Export
                        </button>
                    </div>
                </div>

                <!-- FILTER CARD -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="mb-6 text-lg font-semibold text-slate-800">Filter Transactions</h3>

                    <form @submit.prevent="applyFilters" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="text-sm font-medium text-slate-600"> Transaction Type </label>

                                <select
                                    v-model="filters.transaction_type"
                                    class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="(label, value) in transactionTypes" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-600"> Status </label>

                                <select
                                    v-model="filters.status"
                                    class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">All Statuses</option>

                                    <option v-for="(label, value) in statuses" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-600"> Date From </label>

                                <input
                                    v-model="filters.date_from"
                                    type="date"
                                    class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div>
                                <label class="text-sm font-medium text-slate-600"> Date To </label>

                                <input
                                    v-model="filters.date_to"
                                    type="date"
                                    class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <button
                                @click="clearFilters"
                                type="button"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                            >
                                Clear Filters
                            </button>

                            <button
                                type="submit"
                                class="rounded-xl bg-blue-900 px-5 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-blue-800"
                            >
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TRANSACTION TABLE -->
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h3 class="font-semibold text-slate-800">Transactions ({{ transactions.total }})</h3>

                        <span class="text-sm text-slate-500">
                            Showing {{ transactions.from || 0 }} –
                            {{ transactions.to || 0 }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-100 text-xs text-slate-600 uppercase">
                                <tr>
                                    <th class="px-6 py-3 text-left">Transaction</th>
                                    <th class="px-6 py-3 text-left">Type</th>
                                    <th class="px-6 py-3 text-left">Amount</th>
                                    <th class="px-6 py-3 text-left">Balance</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                    <th class="px-6 py-3 text-left">Date</th>
                                    <th class="px-6 py-3 text-left">Processed By</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="7" class="py-8 text-center text-slate-400">No transactions found</td>
                                </tr>

                                <tr v-for="transaction in transactions.data" :key="transaction.id" class="transition hover:bg-slate-50">
                                    <!-- Transaction -->

                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-full"
                                                :class="getTransactionIconClass(transaction.transaction_type)"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        v-if="isDebitTransaction(transaction.transaction_type)"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 8l4 4m0 0l-4 4m4-4H3"
                                                    />

                                                    <path
                                                        v-else
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 16l-4-4m0 0l4-4m-4 4h18"
                                                    />
                                                </svg>
                                            </div>

                                            <div class="ml-4">
                                                <p class="font-semibold text-slate-800">
                                                    {{ transaction.transaction_id }}
                                                </p>

                                                <p class="text-sm text-slate-500">
                                                    {{ transaction.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- TYPE -->

                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-medium"
                                            :class="getTransactionTypeBadgeClass(transaction.transaction_type)"
                                        >
                                            {{ transactionTypes[transaction.transaction_type] }}
                                        </span>
                                    </td>

                                    <!-- AMOUNT -->

                                    <td
                                        class="px-6 py-4 font-semibold"
                                        :class="isDebitTransaction(transaction.transaction_type) ? 'text-red-600' : 'text-emerald-600'"
                                    >
                                        {{ isDebitTransaction(transaction.transaction_type) ? '-' : '+' }}
                                        {{ formatCurrency(transaction.amount) }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-700">
                                        {{ formatCurrency(transaction.balance_after) }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-medium"
                                            :class="getTransactionStatusClass(transaction.status)"
                                        >
                                            {{ statuses[transaction.status] }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-slate-500">
                                        {{ formatDate(transaction.created_at) }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-500">
                                        {{ transaction.processed_by?.name || 'System' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->

                    <div v-if="transactions.links.length > 3" class="border-t border-slate-200 px-6 py-4">
                        <nav class="flex justify-center gap-2">
                            <template v-for="(link, index) in transactions.links" :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    v-html="link.label"
                                    class="rounded-lg border px-3 py-1 text-sm font-medium"
                                    :class="[
                                        link.active
                                            ? 'border-blue-900 bg-blue-900 text-white'
                                            : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-100',
                                    ]"
                                />

                                <span v-else v-html="link.label" class="rounded-lg border border-slate-200 px-3 py-1 text-slate-400" />
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    account: Object,
    transactions: Object,
    filters: Object,
    transactionTypes: Object,
    statuses: Object,
});

const filters = reactive({
    transaction_type: props.filters.transaction_type || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(amount || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const isDebitTransaction = (type) => {
    return ['withdrawal', 'share_sale', 'share_transfer_out', 'loan_repayment'].includes(type);
};

const getTransactionIconClass = (type) => (isDebitTransaction(type) ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600');

const getTransactionTypeBadgeClass = (type) => {
    const classes = {
        deposit: 'bg-green-100 text-green-800',
        withdrawal: 'bg-red-100 text-red-800',
        share_purchase: 'bg-purple-100 text-purple-800',
        share_sale: 'bg-orange-100 text-orange-800',
        share_transfer_in: 'bg-blue-100 text-blue-800',
        share_transfer_out: 'bg-indigo-100 text-indigo-800',
        loan_disbursement: 'bg-yellow-100 text-yellow-800',
        loan_repayment: 'bg-pink-100 text-pink-800',
        dividend_payment: 'bg-cyan-100 text-cyan-800',
        account_opening: 'bg-gray-100 text-gray-800',
        account_closure: 'bg-gray-100 text-gray-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};

const getTransactionStatusClass = (status) => {
    const classes = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
        reversed: 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const applyFilters = () => {
    router.get(route('accounts.transactions', props.account.id), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filters.transaction_type = '';
    filters.status = '';
    filters.date_from = '';
    filters.date_to = '';
    applyFilters();
};

const exportTransactions = () => {
    const params = new URLSearchParams({
        ...filters,
        export: 'true',
    });
    window.open(`${route('accounts.transactions', props.account.id)}?${params}`, '_blank');
};
</script>
