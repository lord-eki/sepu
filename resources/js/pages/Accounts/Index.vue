<template>
  <Head title="Accounts" />
  <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/accounts' }]">
    <div class="accounts-page min-h-screen pb-10">

      <!-- Page container -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 space-y-6">

        <!-- Header: gradient banner -->
        <header class="rounded-2xl overflow-hidden shadow-md border border-transparent">
          <div class="bg-gradient-to-r from-[#06203a] to-[#0a2342] p-6 sm:p-8 rounded-2xl text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m4 4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
              </div>
              <div>
                <h1 class="text-xl sm:text-2xl font-semibold leading-tight">Accounts Management</h1>
                <p class="text-orange-200 text-xs sm:text-sm mt-0.5">View and manage member accounts, balances and transactions</p>
              </div>
            </div>

            <div class="flex gap-2 items-center text-sm sm:text-base">
              <button @click="openQuickExport" class="inline-flex items-center gap-2 bg-orange-100 text-[#0a2342] px-4 py-2 rounded-lg hover:bg-orange-200 transition shadow-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" /></svg>
                Export
              </button>

              <Link :href="route('accounts.create')" class="inline-flex items-center gap-2 bg-white text-[#0a2342] px-4 py-2 rounded-lg hover:scale-[1.02] transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                New Account
              </Link>
            </div>
          </div>
        </header>

        <!-- Stats cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <transition-group name="stat-fade" tag="div" class="contents">
            <div v-for="card in cards" :key="card.label" class="stat-card rounded-2xl p-4 shadow-lg border">
              <div class="flex items-center gap-4">
                <div :class="['w-10 h-10 rounded-lg flex items-center justify-center', card.gradient]" >
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :d="card.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500">{{ card.label }}</p>
                  <p class="text-lg font-bold text-[#0a2342]">{{ card.value }}</p>
                </div>
              </div>
            </div>
          </transition-group>
        </section>

        <!-- Filters -->
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-[#0a2342]">Filters</h3>
            <div class="text-sm text-gray-500">Refine accounts</div>
          </div>

          <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="col-span-1">
              <label class="block text-sm font-medium text-[#0a2342] mb-1">Search</label>
              <div class="relative">
                <input v-model="filters.search" @input="debouncedSearch" type="text" placeholder="Search accounts..."
                  class="w-full rounded-lg border border-gray-200 p-2 pl-10 focus:outline-none focus:ring-2 focus:ring-orange-50" />
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"></path>
                </svg>
              </div>
            </div>

            <!-- Account Type -->
            <div>
              <label class="block text-sm font-medium text-[#0a2342] mb-1">Account Type</label>
              <select v-model="filters.account_type" @change="applyFilters"
                class="w-full rounded-lg border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-orange-50">
                <option value="">All Types</option>
                <option v-for="(label, value) in accountTypes" :key="value" :value="value">{{ label }}</option>
              </select>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-[#0a2342] mb-1">Status</label>
              <select v-model="filters.status" @change="applyFilters"
                class="w-full rounded-lg border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-orange-50">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-3">
              <button type="button" @click="clearFilters" class="bg-gray-100 text-[#0a2342] px-4 py-2 rounded-lg hover:bg-gray-200 transition">Clear</button>
              <button type="submit" class="ml-auto bg-[#0a2342] text-white px-4 py-2 rounded-lg hover:shadow-[0_6px_20px_rgba(249,115,22,0.12)] transition">Apply</button>
            </div>
          </form>
        </section>

        <!-- Mobile List (visible on small screens) -->
        <section class="sm:hidden space-y-4">
          <div v-for="account in accounts.data" :key="account.id" class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
            <div class="flex justify-between items-start gap-3">
              <div>
                <p class="font-semibold text-[#0a2342]">{{ account.account_number }} • {{ account.account_type }}</p>
                <p class="text-xs text-gray-500">Created: {{ formatDate(account.created_at) }}</p>
                <p class="text-sm text-gray-700 mt-2">{{ account.member.first_name }} {{ account.member.last_name }}</p>
                <p class="text-xs text-gray-400">{{ account.member.membership_id }}</p>
              </div>
              <div class="text-right">
                <div :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold">
                  {{ account.is_active ? 'Active' : 'Inactive' }}
                </div>
                <div class="text-lg font-semibold text-[#0a2342] mt-4">KES {{ formatCurrency(account.balance) }}</div>
              </div>
            </div>

            <div class="mt-3 flex flex-wrap gap-3">
              <Link :href="route('accounts.show', account.id)" class="text-[#0a2342] hover:underline">View</Link>
              <Link :href="route('accounts.deposit.show', account.id)" class="text-green-600 hover:underline">Deposit</Link>
              <Link :href="route('accounts.withdrawal.show', account.id)" class="text-orange-600 hover:underline">Withdraw</Link>
              <Link :href="route('accounts.edit', account.id)" class="text-gray-600 hover:underline">Edit</Link>
            </div>
          </div>
        </section>

        <!-- Desktop table -->
        <section class="hidden sm:block bg-white rounded-2xl shadow-sm border border-gray-100">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-blue-50 text-blue-900 sticky top-0 z-10">
                <tr>
                  <th class="px-6 py-3 text-left text-sm font-semibold">Account Number</th>
                  <th class="px-6 py-3 text-left text-sm font-semibold">Member</th>
                  <th class="px-6 py-3 text-left text-sm font-semibold">Account Type</th>
                  <th class="px-6 py-3 text-left text-sm font-semibold">Balance</th>
                  <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                  <th class="px-6 py-3 text-left text-sm font-semibold">Recent Transactions</th>
                  <th class="px-6 py-3 text-left text-sm font-semibold">Actions</th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y divide-gray-100">
                <tr v-for="account in accounts.data" :key="account.id" class="hover:bg-blue-50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-[#0a2342]">{{ account.account_number }}</div>
                    <div class="text-xs text-gray-500">Created: {{ formatDate(account.created_at) }}</div>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-orange-600">{{ getInitials(account.member.first_name + ' ' + account.member.last_name) }}</span>
                      </div>
                      <div>
                        <div class="text-sm font-semibold text-[#0a2342]">{{ account.member.first_name }} {{ account.member.last_name }}</div>
                        <div class="text-xs text-gray-500">{{ account.member.membership_id }}</div>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4">
                    <span :class="getAccountTypeClass(account.account_type) + ' inline-flex px-2 py-1 text-xs font-semibold rounded-full'">
                      {{ accountTypes[account.account_type] || account.account_type }}
                    </span>
                  </td>

                  <td class="px-6 py-4 text-sm font-semibold text-[#0a2342]">KES {{ formatCurrency(account.balance) }}</td>

                  <td class="px-6 py-4">
                    <span :class="account.is_active ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ account.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>

                  <td class="px-6 py-4 text-sm text-gray-600">
                    <div v-if="account.transactions?.length">
                      <div v-for="t in account.transactions.slice(0,2)" :key="t.id" class="text-xs">
                        <span class="font-medium text-[#0a2342]">{{ t.transaction_type.replace('_',' ') }}</span> - KES {{ formatCurrency(t.amount) }}
                      </div>
                      <div v-if="account.transactions.length > 2" class="text-xs text-gray-400">+{{ account.transactions.length - 2 }} more</div>
                    </div>
                    <div v-else class="text-xs text-gray-400">No transactions</div>
                  </td>

                  <td class="px-6 py-4 text-sm">
                    <div class="flex gap-3">
                      <Link :href="route('accounts.show', account.id)" class="text-[#0a2342] hover:underline">View</Link>
                      <Link :href="route('accounts.deposit.show', account.id)" class="text-green-600 hover:underline">Deposit</Link>
                      <Link :href="route('accounts.withdrawal.show', account.id)" class="text-orange-600 hover:underline">Withdraw</Link>
                      <Link :href="route('accounts.edit', account.id)" class="text-gray-600 hover:underline">Edit</Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="p-4">
            <Pagination :data="accounts" />
          </div>
        </section>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'
import { debounce } from 'lodash'

const props = defineProps({
  accounts: Object,
  filters: Object,
  stats: Object,
  accountTypes: Object
})

// Local filters (keeps original behavior)
const filters = reactive({
  search: props.filters?.search || '',
  account_type: props.filters?.account_type || '',
  status: props.filters?.status || '',
  sortBy: props.filters?.sortBy || '',
  sortDirection: props.filters?.sortDirection || 'desc'
})

// Debounced search
const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

// Apply filters — same logic
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

// Helpers
const formatCurrency = (amount) => {
  if (amount === null || amount === undefined) return '0.00'
  return new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-KE', { year: 'numeric', month: 'short', day: '2-digit' })
}

const getInitials = (name) => {
  if (!name) return ''
  return name.split(' ').map(n => n[0] || '').join('').slice(0,2).toUpperCase()
}

const getAccountTypeClass = (type) => {
  const classes = {
    'savings': 'bg-blue-100 text-blue-800',
    'shares': 'bg-purple-100 text-purple-800',
    'deposits': 'bg-green-100 text-green-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

// Placeholder export action
const openQuickExport = () => {
  // Hook this to your server export endpoint, or implement client CSV generation
  alert('Export: implement export endpoint or client CSV here.')
}

// Cards derived from stats prop
const cards = [
  { label: 'Total Accounts', value: props.stats?.total_accounts ?? 0, gradient: 'bg-gradient-to-tr from-[#0a2342] to-[#133b56]', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16h14zm-8-4h2v-2h-2v2zm0-4h2v-2h-2v2zm0-4h2V7h-2v2z' },
  { label: 'Active Accounts', value: props.stats?.active_accounts ?? 0, gradient: 'bg-green-500', icon: 'M5 13l4 4L19 7' },
  { label: 'Total Balance', value: `KES ${formatCurrency(props.stats?.total_balance ?? 0)}`, gradient: 'bg-gradient-to-tr from-[#f97316] to-[#f59e0b]', icon: 'M12 8c-1.657 0-3 1.343-3 3v1H8a1 1 0 000 2h1v1a3 3 0 006 0h1a1 1 0 000-2h-1v-1c0-1.657-1.343-3-3-3z' },
  { label: 'Shares Balance', value: `KES ${formatCurrency(props.stats?.shares_balance ?? 0)}`, gradient: 'bg-indigo-600', icon: 'M13 10V3L4 14h7v7l9-11h-7z' }
]
</script>

<style scoped>
/* page bg for premium depth */
.accounts-page {
  background-color: #f9fafb;
}

/* stat card glass effect */
.stat-card {
  background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(255,255,255,0.92));
  border: 1px solid rgba(10,35,66,0.06);
  transition: transform .18s ease, box-shadow .18s ease;
}
.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(10, 35, 66, 0.08);
}

/* small animations */
.stat-fade-enter-active, .stat-fade-leave-active { transition: all .25s ease; }
.stat-fade-enter-from, .stat-fade-leave-to { opacity: 0; transform: translateY(6px); }
.stat-fade-enter-to, .stat-fade-leave-from { opacity: 1; transform: translateY(0); }

/* table rounding fix */
table thead tr th:first-child { border-top-left-radius: 0.5rem; }
table thead tr th:last-child { border-top-right-radius: 0.5rem; }
</style>
