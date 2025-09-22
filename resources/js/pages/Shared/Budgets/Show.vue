<template>
  <AppLayout :title="`Budget - ${budget.title}`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link :href="route('budgets.index')" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ budget.title }}</h2>
            <p class="text-sm text-gray-600">{{ budget.description }}</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <span :class="getStatusClass(budget.status)" class="inline-flex px-3 py-1 text-sm font-semibold rounded-full">
            {{ getStatusLabel(budget.status) }}
          </span>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Budget Overview -->
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
                  <p class="text-sm font-medium text-gray-600">Total Budget</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(budget.total_budget) }}</p>
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
                  <p class="text-sm font-medium text-gray-600">Amount Spent</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(utilization.total_spent) }}</p>
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
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Remaining</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(utilization.total_remaining) }}</p>
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
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Utilization</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ utilization.utilization_percentage.toFixed(1) }}%</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Budget Details and Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- Budget Information -->
          <div class="lg:col-span-2">
            <div class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Budget Information</h3>
              </div>
              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-600">Budget Year</label>
                    <p class="mt-1 text-sm text-gray-900">{{ budget.budget_year }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-600">Status</label>
                    <span :class="getStatusClass(budget.status)" class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getStatusLabel(budget.status) }}
                    </span>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-600">Budget Period</label>
                    <p class="mt-1 text-sm text-gray-900">
                      {{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}
                    </p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-600">Total Budget Items</label>
                    <p class="mt-1 text-sm text-gray-900">{{ utilization.items_count }} items in {{ utilization.categories_count }} categories</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-600">Created By</label>
                    <p class="mt-1 text-sm text-gray-900">{{ budget.creator?.name }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(budget.created_at) }}</p>
                  </div>
                  <div v-if="budget.approver">
                    <label class="block text-sm font-medium text-gray-600">Approved By</label>
                    <p class="mt-1 text-sm text-gray-900">{{ budget.approver?.name }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(budget.approval_date) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions Panel -->
          <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Actions</h3>
              </div>
              <div class="p-6 space-y-3">
                
                <!-- Edit Budget -->
                <Link
                  v-if="can_edit"
                  :href="route('budgets.edit', budget.id)"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  Edit Budget
                </Link>

                <!-- Approve Budget -->
                <button
                  v-if="can_approve && budget.status === 'draft'"
                  @click="approveBudget"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-green-600 text-sm font-medium text-white hover:bg-green-700"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  Approve Budget
                </button>

                <!-- Activate Budget -->
                <button
                  v-if="can_activate && budget.status === 'approved'"
                  @click="activateBudget"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-blue-600 text-sm font-medium text-white hover:bg-blue-700"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                  Activate Budget
                </button>

                <!-- Close Budget -->
                <button
                  v-if="budget.status === 'active'"
                  @click="closeBudget"
                  class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-red-600 text-sm font-medium text-white hover:bg-red-700"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                  </svg>
                  Close Budget
                </button>

                <!-- View Links -->
                <div class="border-t border-gray-200 pt-3 space-y-2">
                  <Link
                    :href="route('budgets.items', budget.id)"
                    class="w-full inline-flex items-center px-3 py-2 text-sm text-indigo-600 hover:text-indigo-900"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h5.586a1 1 0 00.707-.293l5.414-5.414a1 1 0 00.293-.707V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    View Budget Items
                  </Link>
                  
                  <Link
                    :href="route('budgets.variance', budget.id)"
                    class="w-full inline-flex items-center px-3 py-2 text-sm text-indigo-600 hover:text-indigo-900"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Variance Analysis
                  </Link>
                  
                  <Link
                    :href="route('budgets.utilization', budget.id)"
                    class="w-full inline-flex items-center px-3 py-2 text-sm text-indigo-600 hover:text-indigo-900"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Utilization Report
                  </Link>
                </div>
              </div>
            </div>

            <!-- Utilization Chart -->
            <div class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Budget Utilization</h3>
              </div>
              <div class="p-6">
                <div class="relative pt-1">
                  <div class="flex mb-2 items-center justify-between">
                    <div>
                      <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-600 bg-green-200">
                        Progress
                      </span>
                    </div>
                    <div class="text-right">
                      <span class="text-xs font-semibold inline-block text-green-600">
                        {{ utilization.utilization_percentage.toFixed(1) }}%
                      </span>
                    </div>
                  </div>
                  <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
                    <div 
                      :style="`width: ${Math.min(utilization.utilization_percentage, 100)}%`"
                      class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center"
                      :class="utilization.utilization_percentage > 90 ? 'bg-red-500' : utilization.utilization_percentage > 75 ? 'bg-yellow-500' : 'bg-green-500'"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Budget Items Summary -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-medium text-gray-900">Budget Items</h3>
              <Link
                :href="route('budgets.items', budget.id)"
                class="text-sm text-indigo-600 hover:text-indigo-900"
              >
                View All Items →
              </Link>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Budgeted</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Spent</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in budget.budget_items.slice(0, 5)" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                      {{ item.category }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ item.item_name }}</div>
                    <div class="text-sm text-gray-500" v-if="item.description">{{ item.description }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                    {{ formatCurrency(item.budgeted_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                    {{ formatCurrency(item.spent_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <span :class="item.remaining_amount < 0 ? 'text-red-600' : 'text-green-600'">
                      {{ formatCurrency(item.remaining_amount) }}
                    </span>
                  </td>
                </tr>
                <tr v-if="budget.budget_items.length > 5" class="bg-gray-50">
                  <td colspan="5" class="px-6 py-3 text-center text-sm text-gray-500">
                    ... and {{ budget.budget_items.length - 5 }} more items.
                    <Link :href="route('budgets.items', budget.id)" class="text-indigo-600 hover:text-indigo-900 ml-1">
                      View all
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Vouchers -->
        <div v-if="recent_vouchers.length > 0" class="bg-white shadow-sm sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Payment Vouchers</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget Item</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="voucher in recent_vouchers" :key="voucher.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ voucher.voucher_number }}</div>
                    <div class="text-sm text-gray-500">{{ voucher.payee_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ voucher.budget_item?.item_name }}</div>
                    <div class="text-sm text-gray-500">{{ voucher.budget_item?.category }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                    {{ formatCurrency(voucher.amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getVoucherStatusClass(voucher.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getVoucherStatusLabel(voucher.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(voucher.created_at) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modals -->
    <div v-if="showApprovalModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                  Approve Budget
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to approve this budget? This action cannot be undone and will move the budget to approved status.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="confirmApproval"
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Approve
            </button>
            <button
              @click="showApprovalModal = false"
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancel
            </button>
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

const props = defineProps({
  budget: Object,
  utilization: Object,
  recent_vouchers: Array,
  can_approve: Boolean,
  can_activate: Boolean,
  can_edit: Boolean
})

const showApprovalModal = ref(false)

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
  return labels[status] || 'Unknown'
}

const getVoucherStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getVoucherStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    pending: 'Pending',
    approved: 'Approved',
    paid: 'Paid',
    rejected: 'Rejected'
  }
  return labels[status] || 'Unknown'
}

const approveBudget = () => {
  showApprovalModal.value = true
}

const confirmApproval = () => {
  router.post(route('budgets.approve', props.budget.id), {}, {
    onSuccess: () => {
      showApprovalModal.value = false
    },
    onError: (errors) => {
      console.error('Approval failed:', errors)
      showApprovalModal.value = false
    }
  })
}

const activateBudget = () => {
  if (confirm('Are you sure you want to activate this budget? This will deactivate any other active budgets for the same year.')) {
    router.post(route('budgets.activate', props.budget.id))
  }
}

const closeBudget = () => {
  if (confirm('Are you sure you want to close this budget? This action cannot be undone.')) {
    router.post(route('budgets.close', props.budget.id))
  }
}
</script>