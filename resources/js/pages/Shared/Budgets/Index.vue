<template>
  <AppLayout :breadcrumbs="[{ title: 'Budgets', href: '/budgets' }]">
    <Head title="Budget Management" />

    <div class="min-h-screen bg-[#F8FAFC] p-4 md:p-6">
      <div class="mx-auto max-w-7xl space-y-6">

        <!-- Header -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0B1F3A] via-[#133263] to-orange-500 p-6 shadow-xl">
          <div class="absolute right-0 top-0 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
          <div class="absolute bottom-0 left-1/3 h-28 w-28 rounded-full bg-orange-300/20 blur-2xl"></div>

          <div class="relative z-10 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <h1 class="text-2xl font-bold text-white md:text-3xl">Budget Management</h1>
              <p class="mt-1 text-sm text-blue-100">
                Manage, monitor and control budget allocations across the organization
              </p>
            </div>

            <Link
              :href="route('budgets.create')"
              class="inline-flex items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-[#0B1F3A] shadow-lg transition hover:bg-orange-50"
            >
              <svg class="h-5 w-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              New Budget
            </Link>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
          <div
            v-for="card in statsCards"
            :key="card.label"
            class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
          >
            <div class="absolute right-0 top-0 h-24 w-24 rounded-full bg-slate-100 blur-2xl opacity-60"></div>

            <div class="relative flex items-center gap-4">
              <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br shadow-md"
                :class="card.color"
              >
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon" />
                </svg>
              </div>

              <div>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                  {{ card.label }}
                </p>
                <p class="mt-1 text-base font-bold text-[#0B1F3A] px-1">
                  {{ card.value }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
              <!-- Search -->
              <div class="relative">
                <input
                  v-model="search"
                  type="text"
                  placeholder="Search budgets..."
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
                  @input="filterBudgets"
                />
                <div class="absolute inset-y-0 left-0 flex items-center pl-4">
                  <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>

              <!-- Status -->
              <select
                v-model="statusFilter"
                @change="filterBudgets"
                class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
              >
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="active">Active</option>
                <option value="closed">Closed</option>
              </select>

              <!-- Year -->
              <select
                v-model="yearFilter"
                @change="filterBudgets"
                class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
              >
                <option value="">All Years</option>
                <option
                  v-for="year in stats.available_years"
                  :key="year"
                  :value="year"
                >
                  {{ year }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-slate-100">
                <tr>
                  <th
                    v-for="head in tableHeaders"
                    :key="head"
                    class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-[#0B1F3A]"
                  >
                    {{ head }}
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="budget in budgets.data"
                  :key="budget.id"
                  class="transition hover:bg-slate-50"
                >
                  <td class="px-6 py-4">
                    <div class="font-semibold text-[#0B1F3A]">{{ budget.title }}</div>
                    <div v-if="budget.description" class="mt-1 text-xs text-slate-500">
                      {{ budget.description }}
                    </div>
                  </td>

                  <td class="px-6 py-4 text-slate-600">
                    {{ budget.budget_year }}
                  </td>

                  <td class="px-6 py-4 font-semibold text-[#0B1F3A]">
                    {{ formatCurrency(budget.total_budget) }}
                  </td>

                  <td class="px-6 py-4">
                    <span
                      :class="getStatusClass(budget.status)"
                      class="rounded-full px-3 py-1 text-xs font-semibold"
                    >
                      {{ getStatusLabel(budget.status) }}
                    </span>
                  </td>

                  <td class="px-6 py-4 text-slate-500">
                    {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
                  </td>

                  <td class="px-6 py-4">
                    <div class="font-medium text-slate-700">
                      {{ budget.creator?.name }}
                    </div>
                    <div class="text-xs text-slate-400">
                      {{ formatDate(budget.created_at) }}
                    </div>
                  </td>

                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <Link
                        :href="route('budgets.show', budget.id)"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-100"
                      >
                        View
                      </Link>

                      <Link
                        v-if="budget.status === 'draft'"
                        :href="route('budgets.edit', budget.id)"
                        class="rounded-xl bg-orange-500 px-3 py-2 text-xs font-medium text-white transition hover:bg-orange-600"
                      >
                        Edit
                      </Link>
                    </div>
                  </td>
                </tr>

                <tr v-if="!budgets.data.length">
                  <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-400">
                    No budgets found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="budgets.links" class="border-t border-slate-200 p-4">
            <Pagination :data="budgets" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'

const props = defineProps({
  budgets: Object,
  stats: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
const statusFilter = ref(props.filters?.status || '')
const yearFilter = ref(props.filters?.year || '')

const filterBudgets = () => {
  router.get(
    route('budgets.index'),
    {
      search: search.value,
      status: statusFilter.value,
      year: yearFilter.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(amount || 0)

const formatDate = (date) =>
  new Date(date).toLocaleDateString('en-KE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })

const getStatusClass = (status) =>
  ({
    draft: 'bg-slate-100 text-slate-700',
    pending: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-blue-100 text-blue-700',
    active: 'bg-emerald-100 text-emerald-700',
    closed: 'bg-rose-100 text-rose-700',
  }[status] || 'bg-slate-100 text-slate-700')

const getStatusLabel = (status) =>
  ({
    draft: 'Draft',
    pending: 'Pending',
    approved: 'Approved',
    active: 'Active',
    closed: 'Closed',
  }[status] || status)

const statsCards = computed(() => [
  {
    label: 'Total Budgets',
    value: props.stats?.total_budgets ?? 0,
    color: 'from-blue-900 to-blue-700',
    icon: 'M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm0 4h16M10 4v16',
  },
  {
    label: 'Active Budgets',
    value: props.stats?.active_budgets ?? 0,
    color: 'from-orange-500 to-orange-400',
    icon: 'M5 13l4 4L19 7',
  },
  {
    label: 'Draft Budgets',
    value: props.stats?.draft_budgets ?? 0,
    color: 'from-slate-500 to-slate-400',
    icon: 'M3 7h18M3 12h18M3 17h18',
  },
  {
    label: 'Total Amount',
    value: formatCurrency(props.stats?.total_budget_amount ?? 0),
    color: 'from-[#0B1F3A] to-blue-800',
    icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v2m0 12v2',
  },
])

const tableHeaders = [
  'Budget',
  'Year',
  'Total Amount',
  'Status',
  'Period',
  'Created By',
  'Actions',
]
</script>