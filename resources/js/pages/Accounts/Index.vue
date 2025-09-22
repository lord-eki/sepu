<template>
  <Head title="Accounts" />

  <AppLayout>
    
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Accounts Management
      </h2>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Accounts</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total_accounts }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Active Accounts</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.active_accounts }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Balance</p>
                <p class="text-2xl font-bold text-gray-900">KES {{ formatCurrency(stats.total_balance) }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Shares Balance</p>
                <p class="text-2xl font-bold text-gray-900">KES {{ formatCurrency(stats.shares_balance) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- Search -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Search accounts..."
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"
                  @input="debouncedSearch"
                />
              </div>

              <!-- Account Type Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                <select
                  v-model="filters.account_type"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"
                  @change="applyFilters"
                >
                  <option value="">All Types</option>
                  <option v-for="(label, value) in accountTypes" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
              </div>

              <!-- Status Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select
                  v-model="filters.status"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"
                  @change="applyFilters"
                >
                  <option value="">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <!-- Actions -->
              <div class="flex items-end">
                <Link
                  :href="route('accounts.create')"
                  class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium mr-2"
                >
                  New Account
                </Link>
                <button
                  @click="clearFilters"
                  class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium"
                >
                  Clear
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Accounts Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                    @click="sortBy('account_number')"
                  >
                    Account Number
                    <span v-if="filters.sortBy === 'account_number'" class="ml-1">
                      {{ filters.sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Member
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                    @click="sortBy('account_type')"
                  >
                    Account Type
                    <span v-if="filters.sortBy === 'account_type'" class="ml-1">
                      {{ filters.sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                    @click="sortBy('balance')"
                  >
                    Balance
                    <span v-if="filters.sortBy === 'balance'" class="ml-1">
                      {{ filters.sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Recent Transactions
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="account in accounts.data" :key="account.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ account.account_number }}</div>
                    <div class="text-sm text-gray-500">Created: {{ formatDate(account.created_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                          <span class="text-sm font-medium text-gray-700">
                            {{ getInitials(account.member.first_name + ' ' + account.member.last_name) }}
                          </span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">
                          {{ account.member.first_name }} {{ account.member.last_name }}
                        </div>
                        <div class="text-sm text-gray-500">{{ account.member.membership_id }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getAccountTypeClass(account.account_type)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ accountTypes[account.account_type] }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    KES {{ formatCurrency(account.balance) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ account.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div v-if="account.transactions && account.transactions.length > 0">
                      <div v-for="transaction in account.transactions.slice(0, 2)" :key="transaction.id" class="mb-1">
                        <span class="font-medium">{{ transaction.transaction_type.replace('_', ' ') }}</span>
                        - KES {{ formatCurrency(transaction.amount) }}
                      </div>
                      <div v-if="account.transactions.length > 2" class="text-xs text-gray-400">
                        +{{ account.transactions.length - 2 }} more
                      </div>
                    </div>
                    <div v-else class="text-gray-400">No transactions</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <Link
                        :href="route('accounts.show', account.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        View
                      </Link>
                      <Link
                        :href="route('accounts.deposit.show', account.id)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Deposit
                      </Link>
                      <Link
                        :href="route('accounts.withdrawal.show', account.id)"
                        class="text-orange-600 hover:text-orange-900"
                      >
                        Withdraw
                      </Link>
                      <Link
                        :href="route('accounts.edit', account.id)"
                        class="text-gray-600 hover:text-gray-900"
                      >
                        Edit
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>


          <Pagination :data="accounts" class="mt-4" />

         
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'

const props = defineProps({
  accounts: Object,
  filters: Object,
  stats: Object,
  accountTypes: Object
})

// Reactive filters
const filters = reactive({
  search: props.filters.search || '',
  account_type: props.filters.account_type || '',
  status: props.filters.status || '',
  sortBy: props.filters.sortBy || '',
  sortDirection: props.filters.sortDirection || 'desc'
})

// Debounced search
const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

// Apply filters
const applyFilters = () => {
  router.get(route('accounts.index'), filters, {
    preserveState: true,
    replace: true
  })
}

// Clear filters
const clearFilters = () => {
  filters.search = ''
  filters.account_type = ''
  filters.status = ''
  filters.sortBy = ''
  filters.sortDirection = 'desc'
  applyFilters()
}

// Sort by column
const sortBy = (column) => {
  if (filters.sortBy === column) {
    filters.sortDirection = filters.sortDirection === 'asc' ? 'desc' : 'asc'
  } else {
    filters.sortBy = column
    filters.sortDirection = 'asc'
  }
  applyFilters()
}

// Helper functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE').format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-KE')
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getAccountTypeClass = (type) => {
  const classes = {
    'savings': 'bg-blue-100 text-blue-800',
    'shares': 'bg-purple-100 text-purple-800',
    'deposits': 'bg-green-100 text-green-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}
</script>