<template>
    <AppLayout :breadcrumbs="[
        { title: 'Accounts', href: route('accounts.index') },
        { title: 'Transactions' }
    ]">
        <div class="py-5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Header + Actions -->
                <div
                    class="flex flex-col sm:flex-row sm:justify-between sm:items-center bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-2xl shadow py-4 px-3 text-white">
                    <div class="flex flex-col">
                        <h2 class="font-bold text-lg sm:text-xl">Account Transactions</h2>
                        <p class="text-sm sm:text-base text-indigo-100 mt-1">
                            {{ account.account_number }} — {{ account.member.first_name }} {{ account.member.last_name
                            }}
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <Link :href="route('accounts.show', account.id)"
                            class="inline-flex items-center px-4 py-2 bg-white text-indigo-700 hover:cursor-pointer rounded-xl font-semibold text-sm shadow hover:bg-gray-100 transition">
                        Back &nbsp;<span class="max-sm:hidden">to Account</span>
                        </Link>
                        <button @click="exportTransactions"
                            class="inline-flex items-center hover:cursor-pointer px-4 py-2 bg-green-500 text-white rounded-xl font-semibold text-sm shadow hover:bg-green-600 transition">
                            Export
                        </button>
                    </div>
                </div>

                <!-- Filters Card -->
                <div class="bg-white shadow-sm rounded-2xl p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Filter Transactions</h3>
                    <form @submit.prevent="applyFilters" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Transaction Type -->
                            <div>
                                <label for="transaction_type"
                                    class="block text-sm font-medium text-gray-700">Transaction Type</label>
                                <select id="transaction_type" v-model="filters.transaction_type"
                                    class="mt-2 block w-full rounded-xl p-2 border border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <option value="">All Types</option>
                                    <option v-for="(label, value) in transactionTypes" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" v-model="filters.status"
                                    class="mt-2 block w-full rounded-xl p-2 border border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <option value="">All Statuses</option>
                                    <option v-for="(label, value) in statuses" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Date From -->
                            <div>
                                <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
                                <input id="date_from" v-model="filters.date_from" type="date"
                                    class="mt-2 block w-full rounded-xl p-2 border border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                            </div>

                            <!-- Date To -->
                            <div>
                                <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
                                <input id="date_to" v-model="filters.date_to" type="date"
                                    class="mt-2 block w-full rounded-xl p-2 border border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between">
                            <button @click="clearFilters" type="button"
                                class="px-4 py-2 rounded-xl hover:cursor-pointer border border-gray-300 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-100 shadow-sm">
                                Clear Filters
                            </button>
                            <button type="submit"
                                class="inline-flex items-center hover:cursor-pointer px-5 py-2 rounded-xl text-sm font-semibold shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Transactions List -->
                <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
                    <!-- Table Header -->
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Transactions ({{ transactions.total }})</h3>
                        <div class="text-sm text-gray-500">
                            Showing {{ transactions.from || 0 }} to {{ transactions.to || 0 }} of {{ transactions.total
                            }} results
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="w-full overflow-x-auto">
                        <table class="w-full min-w-max divide-y divide-gray-200 text-sm">

                            <thead class="bg-blue-100 text-gray-600 uppercase tracking-wider text-xs">
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
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="7" class="px-6 py-6 text-center text-gray-500">No transactions found.
                                    </td>
                                </tr>
                                <tr v-for="transaction in transactions.data" :key="transaction.id"
                                    class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center"
                                                :class="getTransactionIconClass(transaction.transaction_type)">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path v-if="isDebitTransaction(transaction.transaction_type)"
                                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                    <path v-else stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <p class="font-medium text-gray-900">{{ transaction.transaction_id }}
                                                </p>
                                                <p class="text-gray-500">{{ transaction.description }}</p>
                                                <p v-if="transaction.payment_method" class="text-xs text-gray-400 mt-1">
                                                    {{ transaction.payment_method.replace('_', ' ') }}
                                                    <span v-if="transaction.payment_reference">({{
        transaction.payment_reference }})</span>
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                            :class="getTransactionTypeBadgeClass(transaction.transaction_type)">
                                            {{ transactionTypes[transaction.transaction_type] }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 font-semibold"
                                        :class="isDebitTransaction(transaction.transaction_type) ? 'text-red-600' : 'text-green-600'">
                                        {{ isDebitTransaction(transaction.transaction_type) ? '-' : '+' }}
                                        {{ formatCurrency(transaction.amount) }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-900">
                                        {{ formatCurrency(transaction.balance_after) }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                                            :class="getTransactionStatusClass(transaction.status)">
                                            {{ statuses[transaction.status] }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-gray-500">
                                        {{ formatDate(transaction.created_at) }}
                                    </td>

                                    <td class="px-6 py-4 text-gray-500">
                                        {{ transaction.processed_by?.name || 'System' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.links.length > 3" class="px-6 py-4 border-t border-gray-100">
                        <nav class="flex justify-center space-x-2">
                            <template v-for="(link, index) in transactions.links" :key="index">
                                <Link v-if="link.url" :href="link.url"
                                    class="px-3 py-1 rounded-lg border text-sm font-medium" :class="[
        link.active
            ? 'bg-indigo-600 text-white border-indigo-600'
            : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50'
    ]" v-html="link.label" />
                                <span v-else
                                    class="px-3 py-1 rounded-lg border border-gray-200 bg-gray-100 text-sm text-gray-400"
                                    v-html="link.label"></span>
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


<script setup>
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
    account: Object,
    transactions: Object,
    filters: Object,
    transactionTypes: Object,
    statuses: Object,
})

const filters = reactive({
    transaction_type: props.filters.transaction_type || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
})

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(amount || 0)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const isDebitTransaction = (type) => {
    return ['withdrawal', 'share_sale', 'share_transfer_out', 'loan_repayment'].includes(type)
}

const getTransactionIconClass = (type) => {
    if (isDebitTransaction(type)) {
        return 'bg-red-100 text-red-600'
    }
    return 'bg-green-100 text-green-600'
}

const getTransactionTypeBadgeClass = (type) => {
    const classes = {
        'deposit': 'bg-green-100 text-green-800',
        'withdrawal': 'bg-red-100 text-red-800',
        'share_purchase': 'bg-purple-100 text-purple-800',
        'share_sale': 'bg-orange-100 text-orange-800',
        'share_transfer_in': 'bg-blue-100 text-blue-800',
        'share_transfer_out': 'bg-indigo-100 text-indigo-800',
        'loan_disbursement': 'bg-yellow-100 text-yellow-800',
        'loan_repayment': 'bg-pink-100 text-pink-800',
        'dividend_payment': 'bg-cyan-100 text-cyan-800',
        'account_opening': 'bg-gray-100 text-gray-800',
        'account_closure': 'bg-gray-100 text-gray-800',
    }
    return classes[type] || 'bg-gray-100 text-gray-800'
}

const getTransactionStatusClass = (status) => {
    const classes = {
        'completed': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'failed': 'bg-red-100 text-red-800',
        'reversed': 'bg-gray-100 text-gray-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const applyFilters = () => {
    router.get(route('accounts.transactions', props.account.id), filters, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    filters.transaction_type = ''
    filters.status = ''
    filters.date_from = ''
    filters.date_to = ''
    applyFilters()
}

const exportTransactions = () => {
    const params = new URLSearchParams({
        ...filters,
        export: 'true'
    })
    window.open(`${route('accounts.transactions', props.account.id)}?${params}`, '_blank')
}
</script>