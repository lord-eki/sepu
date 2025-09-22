<template>
  <AppLayout :title="`Budget Variance - ${budget.title}`">
    <template #header>
      <div class="flex items-center space-x-4">
        <Link :href="route('budgets.show', budget.id)" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </Link>
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Budget Variance Analysis - {{ budget.title }}
          </h2>
          <p class="text-sm text-gray-600">{{ budget.budget_year }} Budget</p>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="p-3 bg-blue-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Budgeted</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(totals.budgeted) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="p-3 bg-red-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Spent</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(totals.spent) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="p-3 rounded-full" :class="totals.variance >= 0 ? 'bg-green-500' : 'bg-red-500'">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Variance</p>
                  <p class="text-2xl font-semibold" :class="totals.variance >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ formatCurrency(totals.variance) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="p-3 rounded-full" :class="totals.variance_percentage >= 0 ? 'bg-green-500' : 'bg-red-500'">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Variance %</p>
                  <p class="text-2xl font-semibold" :class="totals.variance_percentage >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ totals.variance_percentage.toFixed(1) }}%
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Variance Analysis Table -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Detailed Variance Analysis</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Budgeted</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Spent</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance %</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in variance_data" :key="`${item.category}-${item.item_name}`" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                      {{ item.category }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ item.item_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                    {{ formatCurrency(item.budgeted_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                    {{ formatCurrency(item.spent_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm" :class="item.variance >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ formatCurrency(item.variance) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" :class="item.variance_percentage >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ item.variance_percentage.toFixed(1) }}%
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span :class="getVarianceStatusClass(item.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getVarianceStatusLabel(item.status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Variance Summary by Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                  <p class="text-sm font-medium text-gray-600">Under Budget</p>
                  <p class="text-2xl font-semibold text-green-600">{{ getItemsByStatus('under_budget').length }}</p>
                  <p class="text-xs text-gray-500">{{ ((getItemsByStatus('under_budget').length / variance_data.length) * 100).toFixed(0) }}% of items</p>
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
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">On Track</p>
                  <p class="text-2xl font-semibold text-yellow-600">{{ getItemsByStatus('on_track').length }}</p>
                  <p class="text-xs text-gray-500">{{ ((getItemsByStatus('on_track').length / variance_data.length) * 100).toFixed(0) }}% of items</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="p-3 bg-red-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Over Budget</p>
                  <p class="text-2xl font-semibold text-red-600">{{ getItemsByStatus('over_budget').length }}</p>
                  <p class="text-xs text-gray-500">{{ ((getItemsByStatus('over_budget').length / variance_data.length) * 100).toFixed(0) }}% of items</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  budget: Object,
  variance_data: Array,
  totals: Object
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount || 0)
}

const getVarianceStatusClass = (status) => {
  const classes = {
    under_budget: 'bg-green-100 text-green-800',
    on_track: 'bg-yellow-100 text-yellow-800',
    over_budget: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getVarianceStatusLabel = (status) => {
  const labels = {
    under_budget: 'Under Budget',
    on_track: 'On Track',
    over_budget: 'Over Budget'
  }
  return labels[status] || status
}

const getItemsByStatus = (status) => {
  return props.variance_data.filter(item => item.status === status)
}
</script>