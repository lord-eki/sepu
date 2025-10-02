<template>
<AppLayout 
    :breadcrumbs="[
      { title: 'Accounts', href: route('accounts.index') },
      { title: `${account.account_number}`, href: route('accounts.show', account.id) }
    ]"
  >

    <!-- Flash messages -->
    <div class="max-w-2xl mx-auto mt-4 sm:mt-6 px-4">
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
              ? 'bg-blue-50 text-blue-900 border border-blue-300'
              : 'bg-orange-50 text-orange-900 border border-orange-300',
            'relative w-full px-6 py-3 rounded-lg mb-4 flex items-center shadow-lg'
          ]"
        >
          <span class="flex-1 font-medium">{{ flashMessage }}</span>
          <button
            type="button"
            class="ml-3 text-gray-500 hover:text-gray-800"
            @click="flashMessage = null"
          >
            ✕
          </button>
        </div>
      </transition>
    </div>

    <div class="py-1 bg-gray-50">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row max-sm:gap-3 justify-between max-sm:pl-5 sm:items-center">
          <div>
          <div class="flex sm:flex-col max-sm:gap-4">
          <Link 
              :href="route('accounts.index')" 
              class="inline-flex items-center text-blue-800 hover:text-blue-800"
            >
              <ArrowLeft class="w-5 h-5 mr-1" />
              Back
            </Link>
            <h2 class="font-bold text-lg sm:text-xl text-blue-900">Account Details</h2>
          </div>
            <p class="self-center text-sm text-gray-600 mt-1">
              {{ account.account_number }} - {{ account.member.first_name }} {{ account.member.last_name }}
            </p>
          </div>
          <div class="flex space-x-3">
            <Link
              :href="route('accounts.deposit.show', account.id)"
              class="inline-flex items-center px-4 py-2 bg-blue-800 rounded-lg font-semibold text-xs text-white uppercase tracking-wider shadow-md hover:bg-blue-900 transition"
            >
              Deposit
            </Link>
            <Link
              :href="route('accounts.withdrawal.show', account.id)"
              class="inline-flex items-center px-4 py-2 bg-orange-600 rounded-lg font-semibold text-xs text-white uppercase tracking-wider shadow-md hover:bg-orange-700 transition"
            >
              Withdraw
            </Link>
            <Link
              :href="route('accounts.edit', account.id)"
              class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-lg font-semibold text-xs text-white uppercase tracking-wider shadow-md hover:bg-gray-900 transition"
            >
              Edit
            </Link>
          </div>
        </div>

        <!-- Account Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 px-5">
          <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <svg class="h-8 w-8 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                  </svg>
                </div>
                <div class="ml-5 flex-1">
                  <dt class="text-sm font-medium text-gray-500 truncate">Current Balance</dt>
                  <dd class="text-lg font-bold text-blue-900">{{ formatCurrency(account.balance) }}</dd>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="p-6 flex items-center">
              <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
              </svg>
              <div class="ml-5 flex-1">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Deposits</dt>
                <dd class="text-lg font-bold text-gray-900">{{ formatCurrency(stats.total_deposits) }}</dd>
              </div>
            </div>
          </div>

          <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="p-6 flex items-center">
              <svg class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
              <div class="ml-5 flex-1">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Withdrawals</dt>
                <dd class="text-lg font-bold text-gray-900">{{ formatCurrency(stats.total_withdrawals) }}</dd>
              </div>
            </div>
          </div>

          <div class="bg-white shadow-lg rounded-xl overflow-hidden">
            <div class="p-6 flex items-center">
              <svg class="h-8 w-8 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <div class="ml-5 flex-1">
                <dt class="text-sm font-medium text-gray-500 truncate">Transactions</dt>
                <dd class="text-lg font-bold text-gray-900">{{ stats.transaction_count }}</dd>
              </div>
            </div>
          </div>
        </div>

        <!-- Account Information -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">Account Information</h3>
            <dl class="divide-y divide-gray-200">
              <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Account Number</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ account.account_number }}</dd>
              </div>
              <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                <dd class="col-span-2">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getAccountTypeBadgeClass(account.account_type)"
                  >
                    {{ getAccountTypeLabel(account.account_type) }}
                  </span>
                </dd>
              </div>
              <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Member</dt>
                <dd class="col-span-2">
                  <Link
                    :href="route('members.show', account.member.id)"
                    class="text-blue-700 hover:underline"
                  >
                    {{ account.member.first_name }} {{ account.member.last_name }}
                  </Link>
                </dd>
              </div>
              <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Status</dt>
                <dd class="col-span-2">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  >
                    {{ account.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </dd>
              </div>
              <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Available Balance</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ formatCurrency(account.available_balance) }}</dd>
              </div>
              <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Last Transaction</dt>
                <dd class="col-span-2 text-sm text-gray-900">
                  {{ account.last_transaction_at ? formatDate(account.last_transaction_at) : 'No transactions yet' }}
                </dd>
              </div>
              <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ formatDate(account.created_at) }}</dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-blue-900">Recent Transactions</h3>
            <Link
              :href="route('accounts.transactions', account.id)"
              class="text-sm font-medium text-blue-700 hover:underline"
            >
              View All
            </Link>
          </div>
          <div class="divide-y divide-gray-200">
            <div v-if="recentTransactions.length === 0" class="p-6 text-center text-gray-500">
              No transactions found.
            </div>
            <div v-else>
              <div
                v-for="transaction in recentTransactions"
                :key="transaction.id"
                class="px-6 py-4 hover:bg-gray-50 transition"
              >
                <div class="flex justify-between items-center">
                  <div class="flex items-center">
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
                    <div class="text-xs text-gray-500">
                      Balance: {{ formatCurrency(transaction.balance_after) }}
                    </div>
                  </div>
                </div>
                <div class="mt-2 flex justify-between items-center">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
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

        <!-- Additional Actions -->
        <div v-if="account.account_type === 'shares'" class="bg-white shadow-lg rounded-xl overflow-hidden">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">Share Operations</h3>
            <div class="flex space-x-4">
              <Link
                :href="route('accounts.share-transfer.show', account.id)"
                class="inline-flex items-center px-4 py-2 bg-blue-700 rounded-lg font-semibold text-xs text-white uppercase tracking-wider hover:bg-blue-800 transition"
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
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, watch } from "vue"
import { ArrowLeft } from 'lucide-vue-next'

const flashMessage = ref(null)
const flashType = ref('success')
const page = usePage()

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      flashMessage.value = flash.success
      flashType.value = 'success'
    } else if (flash?.error) {
      flashMessage.value = flash.error
      flashType.value = 'error'
    }

    // Auto hide after 3 seconds
    if (flashMessage.value) {
      setTimeout(() => {
        flashMessage.value = null
      }, 3000)
    }
  },
  { immediate: true, deep: true }
)

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