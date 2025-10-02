<template>
  <Head title="Accounts" />
  <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/accounts' }]">
    <!-- Header -->
    <h2 class="font-bold text-lg sm:text-xl text-blue-900 px-4 pt-4 tracking-tight">
      Accounts Management
    </h2>

    <div class="py-8 max-w-7xl px-4 sm:px-6 lg:px-8">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div
          v-for="card in [
              { label: 'Total Accounts', value: stats.total_accounts, color: 'bg-blue-900', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16h14zm-8-4h2v-2h-2v2zm0-4h2v-2h-2v2zm0-4h2V7h-2v2z' },
              { label: 'Active Accounts', value: stats.active_accounts, color: 'bg-green-600', icon: 'M5 13l4 4L19 7' },
              { label: 'Total Balance', value: `KES ${formatCurrency(stats.total_balance)}`, color: 'bg-orange-500', icon: 'M12 8c-1.657 0-3 1.343-3 3v1H8a1 1 0 000 2h1v1a3 3 0 006 0h1a1 1 0 000-2h-1v-1c0-1.657-1.343-3-3-3z' },
              { label: 'Shares Balance', value: `KES ${formatCurrency(stats.shares_balance)}`, color: 'bg-indigo-600', icon: 'M13 10V3L4 14h7v7l9-11h-7z' }
            ]"
          :key="card.label"
          class="bg-white shadow-md hover:shadow-lg rounded-2xl p-5 flex items-center gap-4 transition"
        >
          <div :class="['w-10 h-10 flex items-center justify-center rounded-full text-white', card.color]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path :d="card.icon" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">{{ card.label }}</p>
            <p class="text-lg font-semibold text-blue-900">{{ card.value }}</p>
          </div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white shadow-md rounded-2xl mb-6 border border-gray-100">
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-blue-900 mb-1">Search</label>
              <div class="relative text-sm">
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Search accounts..."
                  class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-blue-900 focus:ring-blue-900 pl-10 p-2"
                  @input="debouncedSearch"
                />
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                </svg>
              </div>
            </div>

            <!-- Account Type Filter -->
            <div>
              <label class="block text-sm font-medium text-blue-900 mb-1">Account Type</label>
              <select
                v-model="filters.account_type"
                class="w-full border border-gray-300 text-sm rounded-lg shadow-sm focus:border-blue-900 focus:ring-blue-900 p-2"
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
              <label class="block text-sm font-medium text-blue-900 mb-1">Status</label>
              <select
                v-model="filters.status"
                class="w-full border border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900 p-2"
                @change="applyFilters"
              >
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
            <button
                @click="clearFilters"
                class="bg-gray-300 hover:bg-gray-400 text-blue-900 px-4 py-2 text-sm rounded-lg font-medium transition"
              >
                Clear
              </button>
              <Link
                :href="route('accounts.create')"
                class="bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 text-sm rounded-lg font-medium transition"
              >
                New Account
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile Cards -->
      <div class="sm:hidden">
        <div
          v-for="account in accounts.data"
          :key="account.id"
          class="bg-white p-4 mb-4 shadow-md rounded-xl border border-gray-100"
        >
          <div class="flex justify-between items-center">
            <div>
              <p class="font-semibold text-blue-900">{{ account.account_number }} - {{ account.account_type }}</p>
              <p class="text-xs text-gray-500">Created: {{ formatDate(account.created_at) }}</p>
            </div>
            <span
              :class="account.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              class="text-xs px-2 py-1 rounded-full"
            >
              {{ account.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
          <p class="text-sm text-gray-700 mt-2">
            {{ account.member.first_name }} {{ account.member.last_name }}
          </p>
          <p class="text-xs text-gray-500">{{ account.member.membership_id }}</p>
          <p class="text-sm font-semibold text-blue-900 mt-1">KES {{ formatCurrency(account.balance) }}</p>
          <div class="mt-3 flex gap-2 flex-wrap">
            <Link :href="route('accounts.show', account.id)" class="text-blue-900 hover:underline">View</Link>
            <Link :href="route('accounts.deposit.show', account.id)" class="text-green-600 hover:underline">Deposit</Link>
            <Link :href="route('accounts.withdrawal.show', account.id)" class="text-orange-600 hover:underline">Withdraw</Link>
            <Link :href="route('accounts.edit', account.id)" class="text-gray-600 hover:underline">Edit</Link>
          </div>
        </div>
      </div>

      <!-- Desktop Table -->
      <div class="hidden sm:block bg-white text-sm shadow-md rounded-2xl border border-gray-100">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class=" rounded-lg text-white sticky top-0 z-10">
              <tr class="bg-blue-900">
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Account Number</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Member</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Account Type</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Balance</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Recent Transactions</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="account in accounts.data"
                :key="account.id"
                class="hover:bg-blue-50 transition"
              >
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-blue-900">{{ account.account_number }}</div>
                  <div class="text-xs text-gray-500">Created: {{ formatDate(account.created_at) }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                      <span class="text-sm font-medium text-orange-600">
                        {{ getInitials(account.member.first_name + ' ' + account.member.last_name) }}
                      </span>
                    </div>
                    <div>
                      <div class="text-sm font-semibold text-blue-900">
                        {{ account.member.first_name }} {{ account.member.last_name }}
                      </div>
                      <div class="text-xs text-gray-500">{{ account.member.membership_id }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="getAccountTypeClass(account.account_type)"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ accountTypes[account.account_type] }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm font-semibold text-blue-900">
                  KES {{ formatCurrency(account.balance) }}
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                  >
                    {{ account.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  <div v-if="account.transactions?.length">
                    <div v-for="t in account.transactions.slice(0, 2)" :key="t.id" class="text-xs">
                      <span class="font-medium text-blue-900">{{ t.transaction_type.replace('_', ' ') }}</span> - KES {{ formatCurrency(t.amount) }}
                    </div>
                    <div v-if="account.transactions.length > 2" class="text-xs text-gray-400">
                      +{{ account.transactions.length - 2 }} more
                    </div>
                  </div>
                  <span v-else class="text-xs text-gray-400">No transactions</span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex gap-2">
                    <Link :href="route('accounts.show', account.id)" class="text-blue-900 hover:underline">View</Link>
                    <Link :href="route('accounts.deposit.show', account.id)" class="text-green-600 hover:underline">Deposit</Link>
                    <Link :href="route('accounts.withdrawal.show', account.id)" class="text-red-800 hover:underline">Withdraw</Link>
                    <Link :href="route('accounts.edit', account.id)" class="text-gray-600 hover:underline">Edit</Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <Pagination :data="accounts" class="p-4" />
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