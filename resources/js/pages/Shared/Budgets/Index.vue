<template>
  <AppLayout :breadcrumbs="[{ title: 'Budgets', href: '/budgets' }]">
    <Head title="Budget Management" />

    <div class="min-h-screen p-6 space-y-10 transition-colors duration-300 bg-gray-50 dark:bg-gray-900">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-3xl p-6 flex flex-col md:flex-row md:items-center md:justify-between bg-gradient-to-r from-[#0a2342] to-orange-500 shadow-lg border-l-4 border-orange-500">
        <div class="relative z-10">
          <h2 class="text-2xl font-bold text-white">Budget Management</h2>
          <p class="text-blue-100 dark:text-gray-300 text-sm mt-1">Manage, monitor, and control your financial budgets</p>
        </div>
        <Link
          :href="route('budgets.create')"
          class="relative z-10 mt-4 md:mt-0 inline-flex items-center gap-2 bg-white dark:bg-gray-800 text-[#0a2342] dark:text-white font-medium text-sm px-5 py-2 w-fit rounded-full shadow hover:bg-orange-50 dark:hover:bg-orange-700 transition"
        >
          <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Budget
        </Link>
      </div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="card in statsCards" :key="card.label" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md p-6 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-center gap-4">
              <div class="p-3 rounded-full bg-gradient-to-br" :class="card.color">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ card.label }}</p>
                <p class="text-lg font-semibold text-[#0a2342] dark:text-white">{{ card.value }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-col sm:flex-row gap-3 flex-1">
              <div class="relative w-full sm:w-64">
                <input v-model="search" type="text" placeholder="Search budgets..." class="w-full pl-10 pr-4 py-2 rounded-full border border-gray-300 dark:border-gray-600 text-sm focus:border-orange-500 focus:ring focus:ring-orange-100 dark:focus:ring-orange-500 dark:bg-gray-700 dark:text-white" @input="filterBudgets" />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                  <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>
              <select v-model="statusFilter" @change="filterBudgets" class="rounded-full border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm focus:border-orange-500 focus:ring focus:ring-orange-100 dark:bg-gray-700 dark:text-white dark:focus:ring-orange-500">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="active">Active</option>
                <option value="closed">Closed</option>
              </select>
              <select v-model="yearFilter" @change="filterBudgets" class="rounded-full border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm focus:border-orange-500 focus:ring focus:ring-orange-100 dark:bg-gray-700 dark:text-white dark:focus:ring-orange-500">
                <option value="">All Years</option>
                <option v-for="year in stats.available_years" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Budgets Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
              <thead class="bg-blue-50 dark:bg-gray-700">
                <tr>
                  <th v-for="head in tableHeaders" :key="head" class="px-6 py-3 text-left text-xs font-semibold text-[#0a2342] dark:text-gray-200 uppercase tracking-wide">{{ head }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="budget in budgets.data" :key="budget.id" class="hover:bg-orange-50 dark:hover:bg-gray-700 transition">
                  <td class="px-6 py-4 font-medium text-[#0a2342] dark:text-white">
                    <div>{{ budget.title }}</div>
                    <div v-if="budget.description" class="text-gray-500 dark:text-gray-400 text-xs">{{ budget.description }}</div>
                  </td>
                  <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ budget.budget_year }}</td>
                  <td class="px-6 py-4 font-semibold text-[#0a2342] dark:text-white">{{ formatCurrency(budget.total_budget) }}</td>
                  <td class="px-6 py-4"><span :class="getStatusClass(budget.status)" class="px-2 py-1 text-xs font-semibold rounded-full">{{ getStatusLabel(budget.status) }}</span></td>
                  <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ formatDate(budget.start_date) }} - {{ formatDate(budget.end_date) }}</td>
                  <td class="px-6 py-4 text-[#0a2342] dark:text-white">
                    <div>{{ budget.creator?.name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(budget.created_at) }}</div>
                  </td>
                  <td class="px-6 py-4 text-right flex justify-end gap-3">
                    <Link :href="route('budgets.show', budget.id)" class="text-blue-700 dark:text-blue-400 hover:underline">View</Link>
                    <Link v-if="budget.status === 'draft'" :href="route('budgets.edit', budget.id)" class="text-orange-600 dark:text-orange-400 hover:underline">Edit</Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="budgets.links" class="sm:px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            <Pagination :data="budgets" />
          </div>
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

const props = defineProps({ budgets: Object, stats: Object, filters: Object })

const search = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || '')
const yearFilter = ref(props.filters.year || '')

const filterBudgets = () => {
  router.get(route('budgets.index'), { search: search.value, status: statusFilter.value, year: yearFilter.value }, { preserveState: true, replace: true })
}

const formatCurrency = (amount) => new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0)
const formatDate = (date) => new Date(date).toLocaleDateString('en-KE', { year: 'numeric', month: 'short', day: 'numeric' })
const getStatusClass = (status) => ({ draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200', pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-200', approved: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200', active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200', closed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }[status] || 'bg-gray-100 text-gray-800')
const getStatusLabel = (status) => ({ draft: 'Draft', approved: 'Approved', active: 'Active', closed: 'Closed' }[status] || status)

const statsCards = computed(() => [
  { label: 'Total Budgets', value: props.stats.total_budgets, color: 'from-blue-900 to-blue-700', icon: 'M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z M4 10h16 M10 4v16' },
  { label: 'Active Budgets', value: props.stats.active_budgets, color: 'from-orange-500 to-orange-400', icon: 'M5 13l4 4L19 7' },
  { label: 'Draft Budgets', value: props.stats.draft_budgets, color: 'from-gray-400 to-gray-300', icon: 'M3 7h18M3 12h18M3 17h18' },
  { label: 'Total Amount', value: formatCurrency(props.stats.total_budget_amount), color: 'from-[#0a2342] to-blue-800', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2' }
])

const tableHeaders = ['Budget', 'Year', 'Total Amount', 'Status', 'Period', 'Created By', 'Actions']
</script>