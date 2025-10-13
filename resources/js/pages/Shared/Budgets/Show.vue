<template>
  <AppLayout :breadcrumbs="[{ title: `Budget - ${budget.title}` }]">
    <!-- Header -->
    <div class="flex items-start sm:items-center justify-between gap-3 max-sm:mx-2 p-4 bg-gradient-to-r from-[#0a2342] to-[#0e2e5c] rounded-2xl shadow-md text-white">
      <div class="flex items-center gap-3">
        <Link
          :href="route('budgets.index')"
          class="flex items-center justify-center w-10 h-10 bg-white/10 rounded-full hover:bg-white/20 transition"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </Link>
        <div>
          <h2 class="text-xl sm:text-2xl font-semibold">{{ budget.title }}</h2>
          <p class="text-sm text-gray-200">{{ budget.description }}</p>
        </div>
      </div>
      <span
        :class="getStatusClass(budget.status)"
        class="inline-flex px-4 py-1.5 text-sm font-semibold rounded-full bg-white/70"
      >
        {{ getStatusLabel(budget.status) }}
      </span>
    </div>

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
      <!-- Budget Overview -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="card in overviewCards"
          :key="card.label"
          class="bg-white shadow-md rounded-xl px-4 py-6 hover:shadow-lg transition"
        >
          <div class="flex items-center gap-4">
            <div :class="`p-3 rounded-full ${card.color}`">
              <component :is="card.icon" class="w-5 h-5 text-white" />
            </div>
            <div>
              <p class="text-sm text-gray-500">{{ card.label }}</p>
              <p class="text-lg font-semibold text-gray-800">{{ card.value }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Budget Info & Actions -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Budget Info -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Budget Information</h3>
          </div>
          <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
            <div v-for="(info, i) in infoData" :key="i">
              <label class="block text-gray-500 font-medium">{{ info.label }}</label>
              <p class="mt-1 text-gray-800">{{ info.value }}</p>
              <p v-if="info.sub" class="text-xs text-gray-400">{{ info.sub }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="space-y-6">
          <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Actions</h3>
            </div>
            <div class="p-6 flex flex-col gap-3">
              <Link
                v-if="can_edit"
                :href="route('budgets.edit', budget.id)"
                class="action-btn border-gray-300 text-gray-700 hover:bg-gray-100"
              >
                ✏️ Edit Budget
              </Link>

              <button
                v-if="can_approve && budget.status === 'draft'"
                @click="approveBudget"
                class="action-btn bg-green-600 text-white hover:bg-green-700"
              >
                ✅ Approve Budget
              </button>

              <button
                v-if="can_activate && budget.status === 'approved'"
                @click="activateBudget"
                class="action-btn bg-blue-600 text-white hover:bg-blue-700"
              >
                ⚡ Activate Budget
              </button>

              <button
                v-if="budget.status === 'active'"
                @click="closeBudget"
                class="action-btn bg-red-600 text-white hover:bg-red-700"
              >
                🔒 Close Budget
              </button>

              <div class="border-t border-gray-200 pt-3 space-y-2">
                <Link
                  v-for="(link, i) in viewLinks"
                  :key="i"
                  :href="link.href"
                  class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-2"
                >
                  <component :is="link.icon" class="w-4 h-4" /> {{ link.label }}
                </Link>
              </div>
            </div>
          </div>

          <!-- Utilization -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Budget Utilization</h3>
            <div class="relative">
              <div class="flex justify-between mb-1">
                <span class="text-xs text-gray-600 font-medium">Progress</span>
                <span class="text-xs text-green-700 font-semibold">{{ utilization.utilization_percentage.toFixed(1) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="h-2 rounded-full transition-all"
                  :style="`width:${Math.min(utilization.utilization_percentage, 100)}%`"
                  :class="{
                    'bg-green-600': utilization.utilization_percentage <= 75,
                    'bg-yellow-500': utilization.utilization_percentage > 75 && utilization.utilization_percentage <= 90,
                    'bg-red-500': utilization.utilization_percentage > 90
                  }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Budget Items -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center flex-wrap gap-2">
          <h3 class="text-lg font-semibold text-gray-900">Budget Items</h3>
          <Link :href="route('budgets.items', budget.id)" class="text-sm text-indigo-600 hover:text-indigo-800">View All →</Link>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th v-for="header in ['Category', 'Item', 'Budgeted', 'Spent', 'Remaining']" :key="header" class="px-6 py-3 text-left font-medium text-gray-600">
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="item in budget.budget_items.slice(0, 5)"
                :key="item.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-3 text-gray-800">{{ item.category }}</td>
                <td class="px-6 py-3 text-gray-800">
                  <div class="font-semibold">{{ item.item_name }}</div>
                  <div v-if="item.description" class="text-gray-500 text-xs">{{ item.description }}</div>
                </td>
                <td class="px-6 py-3 text-right">{{ formatCurrency(item.budgeted_amount) }}</td>
                <td class="px-6 py-3 text-right">{{ formatCurrency(item.spent_amount) }}</td>
                <td class="px-6 py-3 text-right font-semibold" :class="item.remaining_amount < 0 ? 'text-red-600' : 'text-green-600'">
                  {{ formatCurrency(item.remaining_amount) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { DollarSign, TrendingUp, TrendingDown, PieChart } from 'lucide-vue-next'

const props = defineProps({
  budget: Object,
  utilization: Object,
  recent_vouchers: Array,
  can_approve: Boolean,
  can_activate: Boolean,
  can_edit: Boolean
})

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0)

const getStatusClass = (status) => ({
  draft: 'bg-gray-100 text-gray-700',
  approved: 'bg-blue-100 text-blue-800',
  active: 'bg-green-100 text-green-800',
  closed: 'bg-red-100 text-red-800'
}[status] || 'bg-gray-100 text-gray-700')

const getStatusLabel = (status) => ({
  draft: 'Draft',
  approved: 'Approved',
  active: 'Active',
  closed: 'Closed'
}[status] || 'Unknown')

const overviewCards = [
  { label: 'Total Budget', value: formatCurrency(props.budget.total_budget), color: 'bg-blue-500', icon: DollarSign },
  { label: 'Amount Spent', value: formatCurrency(props.utilization.total_spent), color: 'bg-red-500', icon: TrendingDown },
  { label: 'Remaining', value: formatCurrency(props.utilization.total_remaining), color: 'bg-green-500', icon: TrendingUp },
  { label: 'Utilization', value: `${props.utilization.utilization_percentage.toFixed(1)}%`, color: 'bg-indigo-500', icon: PieChart }
]

const infoData = [
  { label: 'Budget Year', value: props.budget.budget_year },
  { label: 'Status', value: getStatusLabel(props.budget.status) },
  { label: 'Budget Period', value: `${new Date(props.budget.start_date).toLocaleDateString()} - ${new Date(props.budget.end_date).toLocaleDateString()}` },
  { label: 'Total Items', value: `${props.utilization.items_count} items in ${props.utilization.categories_count} categories` },
  { label: 'Created By', value: props.budget.creator?.name, sub: new Date(props.budget.created_at).toLocaleDateString() },
  ...(props.budget.approver ? [{ label: 'Approved By', value: props.budget.approver.name, sub: new Date(props.budget.approval_date).toLocaleDateString() }] : [])
]

const viewLinks = [
  { label: 'Budget Items', href: route('budgets.items', props.budget.id), icon: PieChart },
  { label: 'Variance Analysis', href: route('budgets.variance', props.budget.id), icon: TrendingUp },
  { label: 'Utilization Report', href: route('budgets.utilization', props.budget.id), icon: DollarSign }
]
</script>

<style scoped>
.action-btn {
  width: 100%;
  display: inline-flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem; /* gap-2 */
  padding: 0.5rem 1rem; /* py-2 px-4 */
  border-radius: 0.375rem; /* rounded-md */
  font-size: 0.875rem; /* text-sm */
  font-weight: 500; /* font-medium */
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); /* shadow-sm */
  transition: all 0.2s ease-in-out;
}
</style>

