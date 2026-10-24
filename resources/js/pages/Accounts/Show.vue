<template>
    <AppLayout :breadcrumbs="[{ title: 'Accounts', href: route('accounts.index') }, { title: `${account.account_number}` }]">
        <!-- Flash messages -->
        <div class="mx-auto mt-4 max-w-2xl px-4 sm:mt-6">
            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="flashMessage"
                    :class="[
                        flashType === 'success'
                            ? 'border border-green-300 bg-green-50 text-green-800'
                            : 'border border-red-300 bg-red-50 text-red-800',
                        'relative mb-4 flex w-full items-center rounded-lg px-6 py-3 shadow-lg',
                    ]"
                >
                    <span class="flex-1 font-medium">{{ flashMessage }}</span>
                    <button type="button" class="ml-3 text-gray-500 hover:text-gray-800" @click="flashMessage = null">✕</button>
                </div>
            </transition>
        </div>

        <div class="bg-gray-50 py-1">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col justify-between max-sm:gap-3 max-sm:pl-5 sm:flex-row sm:items-center">
                    <div>
                        <div class="flex max-sm:gap-4 sm:flex-col">
                            <Link :href="route('accounts.index')" class="inline-flex items-center text-blue-800 hover:text-blue-800">
                                <ArrowLeft class="mr-1 h-5 w-5" />
                                Back
                            </Link>
                            <h2 class="text-lg font-bold text-blue-900 sm:text-xl">Account Details</h2>
                        </div>
                        <p class="mt-1 self-center text-sm text-gray-600">
                            {{ account.account_number }}
                            - {{ account.member?.first_name || 'Unknown' }}
                            {{ account.member?.last_name || 'Member' }}
                        </p>
                    </div>
                    <div v-if="account.member" class="flex space-x-3">
                        <!-- Deposit for Share Deposits AND Share Capital -->
                        <Link
                            v-if="['share_deposits', 'share_capital'].includes(account.account_type)"
                            :href="route('accounts.deposit.show', account.id)"
                            class="inline-flex items-center rounded-lg bg-blue-800 px-4 py-2 text-xs font-semibold tracking-wider text-white uppercase shadow-md transition hover:bg-blue-900"
                        >
                            {{ account.account_type === 'share_capital' ? 'Add Capital' : 'Deposit' }}
                        </Link>

                        <!-- Edit button -->
                        <Link
                            :href="route('accounts.edit', account.id)"
                            class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2 text-xs font-semibold tracking-wider text-white uppercase shadow-md transition hover:bg-gray-900"
                        >
                            Edit
                        </Link>
                    </div>
                    <div v-else class="text-sm text-gray-500 italic">Member deleted — actions unavailable</div>
                </div>

                <!-- Account Summary Cards -->
                <div class="grid grid-cols-1 gap-6 px-5 md:grid-cols-4">
                    <div class="overflow-hidden rounded-xl bg-white shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5 flex-1">
                                    <dt class="truncate text-sm font-medium text-gray-500">Current Balance</dt>
                                    <dd class="text-lg font-bold text-blue-900">{{ formatCurrency(account.balance) }}</dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl bg-white shadow-lg">
                        <div class="flex items-center p-6">
                            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                            </svg>
                            <div class="ml-5 flex-1">
                                <dt class="truncate text-sm font-medium text-gray-500">Total Deposits</dt>
                                <dd class="text-lg font-bold text-gray-900">{{ formatCurrency(stats.total_deposits) }}</dd>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl bg-white shadow-lg">
                        <div class="flex items-center p-6">
                            <svg class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                            <div class="ml-5 flex-1">
                                <dt class="truncate text-sm font-medium text-gray-500">Total Withdrawals</dt>
                                <dd class="text-lg font-bold text-gray-900">{{ formatCurrency(stats.total_withdrawals) }}</dd>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl bg-white shadow-lg">
                        <div class="flex items-center p-6">
                            <svg class="h-8 w-8 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                            <div class="ml-5 flex-1">
                                <dt class="truncate text-sm font-medium text-gray-500">Transactions</dt>
                                <dd class="text-lg font-bold text-gray-900">{{ stats.transaction_count }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="overflow-hidden rounded-xl bg-white shadow-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-blue-900">Account Information</h3>
                        <dl class="divide-y divide-gray-200">
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="text-sm font-medium text-gray-500">Account Number</dt>
                                <dd class="col-span-2 text-sm text-gray-900">{{ account.account_number }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                                <dd class="col-span-2">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="getAccountTypeBadgeClass(account.account_type)"
                                    >
                                        {{ getAccountTypeLabel(account.account_type) }}
                                    </span>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ getAccountTypeDescription(account.account_type) }}
                                    </p>
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="text-sm font-medium text-gray-500">Member</dt>
                                <dd class="col-span-2">
                                    <!-- If member exists -->
                                    <Link
                                        v-if="account.member"
                                        :href="route('members.show', account.member.id)"
                                        class="text-blue-700 hover:underline"
                                    >
                                        {{ account.member.first_name }} {{ account.member.last_name }}
                                    </Link>

                                    <!-- If member is missing/deleted -->
                                    <span v-else class="text-gray-500 italic">Member Removed</span>
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="col-span-2">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ account.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="text-sm font-medium text-gray-500">Available Balance</dt>
                                <dd class="col-span-2 text-sm text-gray-900">{{ formatCurrency(account.available_balance) }}</dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="text-sm font-medium text-gray-500">Last Transaction</dt>
                                <dd class="col-span-2 text-sm text-gray-900">
                                    {{ account.last_transaction_at ? formatDate(account.last_transaction_at) : 'No transactions yet' }}
                                </dd>
                            </div>
                            <div class="grid grid-cols-3 gap-4 py-3">
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="col-span-2 text-sm text-gray-900">{{ formatDate(account.created_at) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="overflow-hidden rounded-xl bg-white shadow-lg">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-blue-900">Recent Transactions</h3>
                        <Link :href="route('accounts.transactions', account.id)" class="text-sm font-medium text-blue-700 hover:underline">
                            View All
                        </Link>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-if="recentTransactions.length === 0" class="p-6 text-center text-gray-500">No transactions found.</div>
                        <div v-else>
                            <div v-for="transaction in recentTransactions" :key="transaction.id" class="px-6 py-4 transition hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full"
                                            :class="getTransactionIconClass(transaction.transaction_type)"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ transaction.description }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ formatDate(transaction.created_at) }}
                                                <span v-if="transaction.payment_method" class="ml-2">
                                                    via {{ transaction.payment_method.replace('_', ' ') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div
                                            class="text-sm font-semibold"
                                            :class="isDebitTransaction(transaction.transaction_type) ? 'text-orange-600' : 'text-green-600'"
                                        >
                                            {{ isDebitTransaction(transaction.transaction_type) ? '-' : '+' }}{{ formatCurrency(transaction.amount) }}
                                        </div>
                                        <div class="text-xs text-gray-500">Balance: {{ formatCurrency(transaction.balance_after) }}</div>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="getTransactionStatusClass(transaction.status)"
                                    >
                                        {{ transaction.status }}
                                    </span>
                                    <span class="text-xs text-gray-500">ID: {{ transaction.transaction_id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Actions (Only for Share Capital) -->
                <div v-if="account.account_type === 'share_capital'" class="overflow-hidden rounded-xl bg-white shadow-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-blue-900">Share Capital Operations</h3>
                        <p class="mb-4 text-sm text-gray-600">Share capital can be transferred to other members when exiting the SACCO.</p>
                        <div class="flex space-x-4">
                            <Link
                                :href="route('accounts.share-transfer.show', account.id)"
                                class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 text-xs font-semibold tracking-wider text-white uppercase transition hover:bg-blue-800"
                            >
                                Transfer Shares
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const flashMessage = ref(null);
const flashType = ref('success');
const page = usePage();

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            flashMessage.value = flash.success;
            flashType.value = 'success';
        } else if (flash?.error) {
            flashMessage.value = flash.error;
            flashType.value = 'error';
        }

        // Auto hide after 3 seconds
        if (flashMessage.value) {
            setTimeout(() => {
                flashMessage.value = null;
            }, 3000);
        }
    },
    { immediate: true, deep: true },
);

const props = defineProps({
    account: Object,
    recentTransactions: Array,
    stats: Object,
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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getAccountTypeLabel = (type) => {
    const labels = {
        share_capital: 'Share Capital',
        share_deposits: 'Share Deposits',
    };
    return labels[type] || type;
};

const getAccountTypeDescription = (type) => {
    const descriptions = {
        share_capital: 'Paid once on registration and when purchasing shares from exiting members',
        share_deposits: 'Monthly share deposits - Auto-deducted for staff, manual for non-staff',
    };
    return descriptions[type] || '';
};

const getAccountTypeBadgeClass = (type) => {
    const classes = {
        share_capital: 'bg-purple-100 text-purple-800',
        share_deposits: 'bg-blue-100 text-blue-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};

const getTransactionIconClass = (type) => {
    if (isDebitTransaction(type)) {
        return 'bg-red-100 text-red-600';
    }
    return 'bg-green-100 text-green-600';
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

const isDebitTransaction = (type) => {
    return ['withdrawal', 'share_sale', 'share_transfer_out', 'loan_repayment'].includes(type);
};
</script>
