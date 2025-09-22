<template>
  <AppLayout title="Budget Management">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Budget Management
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="p-3 bg-blue-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                      </path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Budgets</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ stats.total_budgets }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="p-3 bg-green-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Active Budgets</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ stats.active_budgets }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="p-3 bg-yellow-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                      </path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Draft Budgets</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ stats.draft_budgets }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="p-3 bg-indigo-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                      </path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Budget</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(stats.total_budget_amount) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters and Actions -->
        <div class="bg-white shadow-sm sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
              <div class="flex flex-col sm:flex-row gap-4 flex-1">
                <!-- Search -->
                <div class="relative">
                  <input v-model="search" type="text" placeholder="Search budgets..."
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                    @input="filterBudgets">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                  </div>
                </div>

                <!-- Status Filter -->
                <select v-model="statusFilter" @change="filterBudgets"
                  class="border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                  <option value="">All Statuses</option>
                  <option value="draft">Draft</option>
                  <option value="approved">Approved</option>
                  <option value="active">Active</option>
                  <option value="closed">Closed</option>
                </select>

                <!-- Year Filter -->
                <select v-model="yearFilter" @change="filterBudgets"
                  class="border border-gray-300 rounded-md px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                  <option value="">All Years</option>
                  <option v-for="year in stats.available_years" :key="year" :value="year">{{ year }}</option>
                </select>
              </div>

              <!-- Create Budget Button -->
              <Link :href="route('budgets.create')"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Create Budget
              </Link>
            </div>
          </div>
        </div>

        <!-- Budgets Table -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                    Amount
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="budget in budgets.data" :key="budget.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ budget.title }}</div>
                      <div class="text-sm text-gray-500" v-if="budget.description">{{ budget.description }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ budget.budget_year }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ formatCurrency(budget.total_budget) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusClass(budget.status)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getStatusLabel(budget.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                      {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ budget.creator?.name }}</div>
                    <div class="text-sm text-gray-500">{{ formatDate(budget.created_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center space-x-2">
                      <Link :href="route('budgets.show', budget.id)" class="text-indigo-600 hover:text-indigo-900">
                      View
                      </Link>
                      <Link v-if="budget.status === 'draft'" :href="route('budgets.edit', budget.id)"
                        class="text-green-600 hover:text-green-900">
                      Edit
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <Pagination :data="budgets" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
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
  router.get(route('budgets.index'), {
    search: search.value,
    status: statusFilter.value,
    year: yearFilter.value,
  }, {
    preserveState: true,
    replace: true,
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-KE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    approved: 'bg-blue-100 text-blue-800',
    active: 'bg-green-100 text-green-800',
    closed: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    approved: 'Approved',
    active: 'Active',
    closed: 'Closed'
  }
  return labels[status] || status
}
</script>