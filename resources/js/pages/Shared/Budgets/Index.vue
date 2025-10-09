<template>
  <AppLayout :breadcrumbs="[{ title: 'Budgets', href: '/budgets' }]">
    <Head title="Budget Management" />

    <!-- Page Title -->
    <h2 class="font-semibold text-xl sm:text-2xl text-[#0a2342] ml-5 sm:ml-10 mt-6 tracking-tight border-l-4 border-orange-500 pl-3">
      Budget Management
    </h2>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="card in [
              {
                label: 'Total Budgets',
                value: stats.total_budgets,
                color: 'bg-[#0a2342]',
                icon: 'M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z M4 10h16 M10 4v16',
              },
              {
                label: 'Active Budgets',
                value: stats.active_budgets,
                color: 'bg-orange-500',
                icon: 'M5 13l4 4L19 7',
              },
              {
                label: 'Draft Budgets',
                value: stats.draft_budgets,
                color: 'bg-gray-400',
                icon: 'M3 7h18M3 12h18M3 17h18',
              },
              {
                label: 'Total Amount',
                value: formatCurrency(stats.total_budget_amount),
                color: 'bg-blue-900',
                icon: [
                  'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2',
                  'M12 8V7',
                  'M12 8v8',
                  'M12 16v1',
                  'M12 16c-1.11 0-2.08-.402-2.599-1',
                ],
              },
            ]"
            :key="card.label"
            class="bg-white/90 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300 p-6"
          >
            <div class="flex items-center space-x-4">
              <div
                :class="[card.color, 'w-10 h-10 sm:w-12 sm:h-12 rounded-full flex items-center justify-center shadow']"
              >
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <template v-if="Array.isArray(card.icon)">
                    <path
                      v-for="(d, i) in card.icon"
                      :key="i"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      :d="d"
                    />
                  </template>
                  <template v-else>
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      :d="card.icon"
                    />
                  </template>
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500">{{ card.label }}</p>
                <p class="text-base font-semibold text-[#0a2342]">{{ card.value }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters & Actions -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3 flex-1">
              <!-- Search -->
              <div class="relative w-full sm:w-64">
                <input
                  v-model="search"
                  type="text"
                  placeholder="Search budgets..."
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-[#0a2342] focus:ring focus:ring-blue-100 shadow-sm"
                  @input="filterBudgets"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                  </svg>
                </div>
              </div>

              <!-- Status Filter -->
              <select
                v-model="statusFilter"
                @change="filterBudgets"
                class="rounded-lg border-gray-300 text-sm px-3 py-2 shadow-sm focus:border-[#0a2342] focus:ring focus:ring-blue-100"
              >
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="approved">Approved</option>
                <option value="active">Active</option>
                <option value="closed">Closed</option>
              </select>

              <!-- Year Filter -->
              <select
                v-model="yearFilter"
                @change="filterBudgets"
                class="rounded-lg border-gray-300 text-sm px-3 py-2 shadow-sm focus:border-[#0a2342] focus:ring focus:ring-blue-100"
              >
                <option value="">All Years</option>
                <option v-for="year in stats.available_years" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>

            <!-- Create Button -->
            <Link
              :href="route('budgets.create')"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-500 text-white text-sm font-medium hover:bg-orange-600 transition shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Create Budget
            </Link>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full divide-y divide-gray-200 text-xs sm:text-sm">
              <thead class="bg-blue-50">
                <tr>
                  <th
                    v-for="head in ['Budget', 'Year', 'Total Amount', 'Status', 'Period', 'Created By', 'Actions']"
                    :key="head"
                    class="px-6 py-3 text-left text-xs font-semibold text-[#0a2342] uppercase tracking-wide"
                  >
                    {{ head }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="budget in budgets.data"
                  :key="budget.id"
                  class="hover:bg-orange-50 transition"
                >
                  <td class="px-6 py-4 font-medium text-[#0a2342]">
                    <div>{{ budget.title }}</div>
                    <div v-if="budget.description" class="text-gray-500 text-xs">{{ budget.description }}</div>
                  </td>
                  <td class="px-6 py-4 text-gray-700">{{ budget.budget_year }}</td>
                  <td class="px-6 py-4 font-medium text-[#0a2342]">{{ formatCurrency(budget.total_budget) }}</td>
                  <td class="px-6 py-4">
                    <span
                      :class="getStatusClass(budget.status)"
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ getStatusLabel(budget.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-500">
                    {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
                  </td>
                  <td class="px-6 py-4 text-[#0a2342]">
                    <div>{{ budget.creator?.name }}</div>
                    <div class="text-xs text-gray-500">{{ formatDate(budget.created_at) }}</div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-3">
                      <Link :href="route('budgets.show', budget.id)" class="text-blue-700 hover:underline">
                        View
                      </Link>
                      <Link
                        v-if="budget.status === 'draft'"
                        :href="route('budgets.edit', budget.id)"
                        class="text-orange-600 hover:underline"
                      >
                        Edit
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="budgets.links" class="px-6 py-4 border-t border-gray-100">
            <Pagination :data="budgets" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'

const props = defineProps({
  budgets: Object,
  stats: Object,
  filters: Object
})

const search = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || '')
const yearFilter = ref(props.filters.year || '')

const filterBudgets = () => {
  router.get(
    route('budgets.index'),
    {
      search: search.value,
      status: statusFilter.value,
      year: yearFilter.value,
    },
    { preserveState: true, replace: true }
  )
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(amount || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-KE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    approved: 'bg-blue-100 text-blue-800',
    active: 'bg-green-100 text-green-800',
    closed: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    approved: 'Approved',
    active: 'Active',
    closed: 'Closed',
  }
  return labels[status] || status
}
</script>
