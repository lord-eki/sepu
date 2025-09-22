<template>
  <AppLayout :title="`Account ${account.account_number}`">
      
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Account Details
          </h2>
          <p class="text-sm text-gray-600 mt-1">
            {{ account.account_number }} - {{ account.member.first_name }} {{ account.member.last_name }}
          </p>
        </div>
        <div class="flex space-x-3">
          <Link 
            :href="route('accounts.deposit.show', account.id)"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Deposit
          </Link>
          <Link 
            :href="route('accounts.withdrawal.show', account.id)"
            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Withdraw
          </Link>
          <Link 
            :href="route('accounts.edit', account.id)"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Edit
          </Link>
        </div>
      </div>


        <!-- Account Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Current Balance</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(account.balance) }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Deposits</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(stats.total_deposits) }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Withdrawals</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ formatCurrency(stats.total_withdrawals) }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Transactions</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ stats.transaction_count }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Account Information -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="px-4 py-5 sm:p-0">
              <dl class="sm:divide-y sm:divide-gray-200">
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Account Number</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ account.account_number }}</dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="getAccountTypeBadgeClass(account.account_type)">
                      {{ getAccountTypeLabel(account.account_type) }}
                    </span>
                  </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Member</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <Link 
                      :href="route('members.show', account.member.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      {{ account.member.first_name }} {{ account.member.last_name }}
                    </Link>
                  </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Status</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                      {{ account.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Available Balance</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ formatCurrency(account.available_balance) }}</dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Last Transaction</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ account.last_transaction_at ? formatDate(account.last_transaction_at) : 'No transactions yet' }}
                  </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                  <dt class="text-sm font-medium text-gray-500">Created</dt>
                  <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ formatDate(account.created_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
              <Link 
                :href="route('accounts.transactions', account.id)"
                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
              >
                View All
              </Link>
            </div>
          </div>
          <div class="divide-y divide-gray-200">
            <div v-if="recentTransactions.length === 0" class="p-6 text-center text-gray-500">
              No transactions found.
            </div>
            <div v-else>
              <div 
                v-for="transaction in recentTransactions" 
                :key="transaction.id" 
                class="px-6 py-4 hover:bg-gray-50"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="h-8 w-8 rounded-full flex items-center justify-center"
                           :class="getTransactionIconClass(transaction.transaction_type)">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path v-if="isDebitTransaction(transaction.transaction_type)" 
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                          <path v-else 
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
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
                    <div class="text-sm font-medium"
                         :class="isDebitTransaction(transaction.transaction_type) ? 'text-red-600' : 'text-green-600'">
                      {{ isDebitTransaction(transaction.transaction_type) ? '-' : '+' }}{{ formatCurrency(transaction.amount) }}
                    </div>
                    <div class="text-sm text-gray-500">
                      Balance: {{ formatCurrency(transaction.balance_after) }}
                    </div>
                  </div>
                </div>
                <div class="mt-2 flex justify-between items-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getTransactionStatusClass(transaction.status)">
                    {{ transaction.status }}
                  </span>
                  <div class="text-xs text-gray-500">
                    ID: {{ transaction.transaction_id }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Additional Actions -->
        <div v-if="account.account_type === 'shares'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Share Operations</h3>
            <div class="flex space-x-4">
              <Link 
                :href="route('accounts.share-transfer.show', account.id)"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
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
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  account: Object,
  recentTransactions: Array,
  stats: Object,
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
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getAccountTypeLabel = (type) => {
  const labels = {
    'savings': 'Savings Account',
    'shares': 'Shares Account',
    'deposits': 'Deposits Account'
  }
  return labels[type] || type
}

const getAccountTypeBadgeClass = (type) => {
  const classes = {
    'savings': 'bg-blue-100 text-blue-800',
    'shares': 'bg-purple-100 text-purple-800',
    'deposits': 'bg-green-100 text-green-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getTransactionIconClass = (type) => {
  if (isDebitTransaction(type)) {
    return 'bg-red-100 text-red-600'
  }
  return 'bg-green-100 text-green-600'
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

const isDebitTransaction = (type) => {
  return ['withdrawal', 'share_sale', 'share_transfer_out', 'loan_repayment'].includes(type)
}
</script>