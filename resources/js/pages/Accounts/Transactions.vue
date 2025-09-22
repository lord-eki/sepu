<template>
    <AppLayout :title="`Transactions - ${account.account_number}`">


        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center pb-6">
                            <div>
                                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                                    Account Transactions
                                </h2>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ account.account_number }} - {{ account.member.first_name }} {{
                                    account.member.last_name }}
                                </p>
                            </div>
                            <div class="flex space-x-3">
                                <Link :href="route('accounts.show', account.id)"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Back to Account
                                </Link>
                                <button @click="exportTransactions"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Export
                                </button>
                            </div>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Transactions</h3>
                        <form @submit.prevent="applyFilters" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <label for="transaction_type" class="block text-sm font-medium text-gray-700">
                                        Transaction Type
                                    </label>
                                    <select id="transaction_type" v-model="filters.transaction_type"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">All Types</option>
                                        <option v-for="(label, value) in transactionTypes" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">
                                        Status
                                    </label>
                                    <select id="status" v-model="filters.status"
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">All Statuses</option>
                                        <option v-for="(label, value) in statuses" :key="value" :value="value">
                                            {{ label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="date_from" class="block text-sm font-medium text-gray-700">
                                        Date From
                                    </label>
                                    <input id="date_from" v-model="filters.date_from" type="date"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2.5">
                                </div>

                                <div>
                                    <label for="date_to" class="block text-sm font-medium text-gray-700">
                                        Date To
                                    </label>
                                    <input id="date_to" v-model="filters.date_to" type="date"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2.5">
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <button @click="clearFilters" type="button"
                                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Clear Filters
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Transactions List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">
                                Transactions ({{ transactions.total }})
                            </h3>
                            <div class="text-sm text-gray-500">
                                Showing {{ transactions.from || 0 }} to {{ transactions.to || 0 }} of {{
                                transactions.total }} results
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Transaction
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Balance
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Processed By
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="transactions.data.length === 0">
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No transactions found.
                                    </td>
                                </tr>
                                <tr v-for="transaction in transactions.data" :key="transaction.id"
                                    class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center"
                                                    :class="getTransactionIconClass(transaction.transaction_type)">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path v-if="isDebitTransaction(transaction.transaction_type)"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                                        <path v-else stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ transaction.transaction_id }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ transaction.description }}
                                                </div>
                                                <div v-if="transaction.payment_method"
                                                    class="text-xs text-gray-400 mt-1">
                                                    {{ transaction.payment_method.replace('_', ' ') }}
                                                    <span v-if="transaction.payment_reference" class="ml-1">
                                                        ({{ transaction.payment_reference }})
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="getTransactionTypeBadgeClass(transaction.transaction_type)">
                                            {{ transactionTypes[transaction.transaction_type] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                        :class="isDebitTransaction(transaction.transaction_type) ? 'text-red-600' : 'text-green-600'">
                                        {{ isDebitTransaction(transaction.transaction_type) ? '-' : '+' }}{{
                                        formatCurrency(transaction.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(transaction.balance_after) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="getTransactionStatusClass(transaction.status)">
                                            {{ statuses[transaction.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(transaction.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ transaction.processed_by?.name || 'System' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.links.length > 3"
                        class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link v-if="transactions.prev_page_url" :href="transactions.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                            </Link>
                            <Link v-if="transactions.next_page_url" :href="transactions.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ transactions.from || 0 }}</span>
                                    to
                                    <span class="font-medium">{{ transactions.to || 0 }}</span>
                                    of
                                    <span class="font-medium">{{ transactions.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    aria-label="Pagination">
                                    <template v-for="(link, index) in transactions.links" :key="index">
                                        <Link v-if="link.url" :href="link.url"
                                            class="relative inline-flex items-center px-2 py-2 border text-sm font-medium"
                                            :class="[
                                                link.active
                                                    ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                index === 0 ? 'rounded-l-md' : '',
                                                index === transactions.links.length - 1 ? 'rounded-r-md' : ''
                                            ]" v-html="link.label">
                                        </Link>
                                        <span v-else
                                            class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500"
                                            :class="[
                                                index === 0 ? 'rounded-l-md' : '',
                                                index === transactions.links.length - 1 ? 'rounded-r-md' : ''
                                            ]" v-html="link.label"></span>
                                    </template>
                                </nav>
                            </div>
                        </div>
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