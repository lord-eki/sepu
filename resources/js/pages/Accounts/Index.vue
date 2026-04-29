<template>
  <Head title="Accounts" />

  <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/accounts' }]">
    <div class="min-h-screen bg-slate-50 pb-10 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
      <div class="mx-auto max-w-7xl space-y-6 px-4 pt-6 sm:px-6 lg:px-8">
        <!-- Hero -->
        <header
          class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 shadow-2xl shadow-blue-950/20"
        >
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,146,60,0.16),transparent_30%),radial-gradient(circle_at_left,rgba(59,130,246,0.12),transparent_35%)]"></div>

          <div class="relative flex flex-col gap-5 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
            <div class="flex items-start gap-4">
              <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/10 backdrop-blur">
                <Landmark class="h-6 w-6 text-white" />
              </div>

              <div>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                  Accounts Management
                </h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-300 sm:text-base">
                  View and manage member accounts, balances, account activity and financial movement in one place.
                </p>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <button
                @click="openQuickExport"
                class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-100"
              >
                <Download class="h-4 w-4" />
                Export
              </button>

              <Link
                :href="route('accounts.create')"
                class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:-translate-y-0.5 hover:bg-orange-600"
              >
                <Plus class="h-4 w-4" />
                New Account
              </Link>
            </div>
          </div>
        </header>

        <!-- Stats -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
          <div
            v-for="card in cards"
            :key="card.label"
            class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                <p class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                  {{ card.value }}
                </p>
              </div>

              <div :class="['rounded-2xl p-3 text-white shadow-lg', card.gradient]">
                <component :is="card.icon" class="h-4 w-4" />
              </div>
            </div>
          </div>
        </section>

        <!-- Filters -->
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="mb-5 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Filters</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">Refine accounts by member, type and status.</p>
            </div>
          </div>

          <form @submit.prevent="applyFilters" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Search</label>
              <div class="relative">
                <Search class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" />
                <input
                  v-model="filters.search"
                  @input="debouncedSearch"
                  type="text"
                  placeholder="Search accounts..."
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm outline-none transition focus:border-blue-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                />
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Account Type</label>
              <select
                v-model="filters.account_type"
                @change="applyFilters"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option value="">All Types</option>
                <option v-for="(label, value) in accountTypes" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
              <select
                v-model="filters.status"
                @change="applyFilters"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <div class="flex items-end gap-3">
              <button
                type="button"
                @click="clearFilters"
                class="rounded-2xl bg-slate-100 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200"
              >
                Clear
              </button>

              <button
                type="submit"
                class="ml-auto rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-orange-500 dark:hover:bg-orange-600"
              >
                Apply
              </button>
            </div>
          </form>
        </section>

        <!-- Mobile Cards -->
        <section class="space-y-4 sm:hidden">
          <div
            v-for="account in accounts.data"
            :key="account.id"
            class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-semibold text-slate-900 dark:text-white">
                  {{ account.account_number }} • {{ account.account_type }}
                </p>
                <p class="text-xs text-slate-500">Created: {{ formatDate(account.created_at) }}</p>

                <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                  {{ account.member?.first_name || 'Deleted' }}
                  {{ account.member?.last_name || 'Member' }}
                </p>
                <p class="text-xs text-slate-400">{{ account.member?.membership_id || 'N/A' }}</p>
              </div>

              <div class="text-right">
                <span
                  :class="account.is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'"
                  class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                >
                  {{ account.is_active ? 'Active' : 'Inactive' }}
                </span>

                <div class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                  KES {{ formatCurrency(account.balance) }}
                </div>
              </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-4 text-sm font-medium">
              <Link :href="route('accounts.show', account.id)" class="text-blue-600 dark:text-blue-400">View</Link>
              <Link :href="route('accounts.deposit.show', account.id)" class="text-emerald-600 dark:text-emerald-400">Deposit</Link>
              <Link :href="route('accounts.withdrawal.show', account.id)" class="text-orange-600 dark:text-orange-400">Withdraw</Link>
              <Link :href="route('accounts.edit', account.id)" class="text-slate-600 dark:text-slate-300">Edit</Link>
            </div>
          </div>
        </section>

        <!-- Desktop Table -->
        <section class="hidden overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:block">
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-blue-950 text-left text-white">
                <tr>
                  <th class="px-4 py-4 text-sm font-semibold">Account Number</th>
                  <th class="px-4 py-4 text-sm font-semibold">Member</th>
                  <th class="px-4 py-4 text-sm font-semibold">Account Type</th>
                  <th class="px-4 py-4 text-sm font-semibold">Balance</th>
                  <th class="px-4 py-4 text-sm font-semibold">Status</th>
                  <th class="px-4 py-4 text-sm font-semibold">Recent Transactions</th>
                  <th class="px-4 py-4 text-sm font-semibold">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                <tr
                  v-for="account in accounts.data"
                  :key="account.id"
                  class="transition hover:bg-slate-50 dark:hover:bg-slate-800/50"
                >
                  <td class="px-6 py-4">
                    <div class="font-semibold text-slate-900 dark:text-white">{{ account.account_number }}</div>
                    <div class="text-xs text-slate-500">Created: {{ formatDate(account.created_at) }}</div>
                  </td>

                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-100 dark:bg-orange-900/30">
                        <span class="text-sm font-bold text-orange-600 dark:text-orange-300">
                          {{ account.member ? getInitials(account.member.first_name + ' ' + account.member.last_name) : 'NA' }}
                        </span>
                      </div>

                      <div>
                        <div class="font-semibold text-slate-900 dark:text-white">
                          <template v-if="account.member">
                            {{ account.member.first_name }} {{ account.member.last_name }}
                          </template>
                          <template v-else>
                            Deleted Member
                          </template>
                        </div>
                        <div class="text-xs text-slate-500">
                          {{ account.member?.membership_id || 'N/A' }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4">
                    <span
                      :class="getAccountTypeClass(account.account_type)"
                      class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                    >
                      {{ accountTypes[account.account_type] || account.account_type }}
                    </span>
                  </td>

                  <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">
                    KES {{ formatCurrency(account.balance) }}
                  </td>

                  <td class="px-6 py-4">
                    <span
                      :class="account.is_active
                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                        : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'"
                      class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                    >
                      {{ account.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>

                  <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                    <div v-if="account.transactions?.length">
                      <div v-for="t in account.transactions.slice(0, 2)" :key="t.id" class="text-xs">
                        <span class="font-semibold text-slate-900 dark:text-white">
                          {{ t.transaction_type.replace('_', ' ') }}
                        </span>
                        — KES {{ formatCurrency(t.amount) }}
                      </div>
                      <div v-if="account.transactions.length > 2" class="text-xs text-slate-400">
                        +{{ account.transactions.length - 2 }} more
                      </div>
                    </div>
                    <div v-else class="text-xs text-slate-400">No transactions</div>
                  </td>

                  <td class="px-6 py-4 text-sm">
                    <div class="flex gap-4 font-medium">
                      <Link :href="route('accounts.show', account.id)" class="text-blue-600 hover:underline dark:text-blue-400">View</Link>
                      <Link :href="route('accounts.deposit.show', account.id)" class="text-emerald-600 hover:underline dark:text-emerald-400">Deposit</Link>
                      <Link :href="route('accounts.edit', account.id)" class="text-slate-600 hover:underline dark:text-slate-300">Edit</Link>
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
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'
import {
  Landmark,
  Wallet,
  Download,
  Plus,
  Search,
  Banknote,
  CheckCircle2,
  PiggyBank,
  Coins,
  Layers3
} from 'lucide-vue-next'

const props = defineProps({
  accounts: Object,
  filters: Object,
  stats: Object,
  accountTypes: Object
})

const filters = reactive({
  search: props.filters?.search || '',
  account_type: props.filters?.account_type || '',
  status: props.filters?.status || '',
  sortBy: props.filters?.sortBy || '',
  sortDirection: props.filters?.sortDirection || 'desc'
})

const debouncedSearch = debounce(() => applyFilters(), 300)

const applyFilters = () => {
  router.get(route('accounts.index'), filters, {
    preserveState: true,
    replace: true
  })
}

const clearFilters = () => {
  filters.search = ''
  filters.account_type = ''
  filters.status = ''
  filters.sortBy = ''
  filters.sortDirection = 'desc'
  applyFilters()
}

const formatCurrency = (amount) => {
  if (amount === null || amount === undefined) return '0.00'
  return new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-KE', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

const getInitials = (name) => {
  if (!name) return 'NA'
  return name
    .split(' ')
    .map((n) => (n[0] || '').toUpperCase())
    .join('')
    .slice(0, 2)
}

const getAccountTypeClass = (type) => {
  const classes = {
    savings: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    shares: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    deposits: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
  }
  return classes[type] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
}

const openQuickExport = () => {
  alert('Export endpoint not connected yet.')
}

const cards = [
  {
    label: 'Total Accounts',
    value: props.stats?.total_accounts ?? 0,
    gradient: 'bg-gradient-to-br from-slate-950 to-blue-900',
    icon: Layers3
  },
  {
    label: 'Active Accounts',
    value: props.stats?.active_accounts ?? 0,
    gradient: 'bg-gradient-to-br from-emerald-500 to-emerald-600',
    icon: CheckCircle2
  },
  {
    label: 'Total Balance',
    value: `KES ${formatCurrency(props.stats?.total_balance ?? 0)}`,
    gradient: 'bg-gradient-to-br from-orange-500 to-amber-500',
    icon: Banknote
  },
  {
    label: 'Share Capital',
    value: `KES ${formatCurrency(props.stats?.share_capital_balance ?? 0)}`,
    gradient: 'bg-gradient-to-br from-purple-500 to-purple-700',
    icon: Coins
  },
  {
    label: 'Share Deposits',
    value: `KES ${formatCurrency(props.stats?.share_deposits_balance ?? 0)}`,
    gradient: 'bg-gradient-to-br from-blue-500 to-blue-700',
    icon: PiggyBank
  }
]
</script>

<style scoped>
/* page bg for premium depth */
.accounts-page {
  background-color: #f9fafb;
}

/* stat card glass effect */
.stat-card {
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.92));
  border: 1px solid rgba(10, 35, 66, 0.06);
  transition: transform .18s ease, box-shadow .18s ease;
}

.stat-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(10, 35, 66, 0.08);
}

/* small animations */
.stat-fade-enter-active,
.stat-fade-leave-active {
  transition: all .25s ease;
}

.stat-fade-enter-from,
.stat-fade-leave-to {
  opacity: 0;
  transform: translateY(6px);
}

.stat-fade-enter-to,
.stat-fade-leave-from {
  opacity: 1;
  transform: translateY(0);
}

/* table rounding fix */
table thead tr th:first-child {
  border-top-left-radius: 0.5rem;
}

table thead tr th:last-child {
  border-top-right-radius: 0.5rem;
}
</style>
