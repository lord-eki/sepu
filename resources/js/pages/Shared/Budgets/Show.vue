<template>
  <AppLayout :breadcrumbs="[{ title: `Budget - ${budget.title}` }]">
    <!-- Flash Message -->
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
      <div v-if="flashMessage" :class="[
    flashType === 'success'
      ? 'bg-green-50 border border-green-200 text-green-800'
      : 'bg-red-50 border border-red-200 text-red-800',
    'max-w-3xl mx-auto px-6 py-3 rounded-xl flex items-center shadow-sm mb-8 backdrop-blur-sm',
  ]">
        <span class="flex-1 font-medium">{{ flashMessage }}</span>
        <button type="button" class="ml-3 text-gray-500 hover:text-gray-700" @click="flashMessage = null">
          ✕
        </button>
      </div>
    </transition>

    <!-- Header -->
    <div
      class="flex items-start sm:items-center justify-between gap-3 max-sm:mx-2 p-4 bg-gradient-to-r from-[#0a2342] to-[#0e2e5c] rounded-2xl shadow-md text-white">
      <div class="flex items-center gap-3">
        <Link :href="route('budgets.index')"
          class="flex items-center justify-center w-10 h-10 bg-white/10 rounded-full hover:bg-white/20 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        </Link>
        <div>
          <h2 class="text-xl sm:text-2xl font-semibold">{{ budget.title }}</h2>
          <p class="text-sm text-gray-200">{{ budget.description }}</p>
        </div>
      </div>
      <span :class="getStatusClass(budget.status)"
        class="inline-flex px-4 py-1.5 text-sm font-semibold rounded-full bg-white/70">
        {{ getStatusLabel(budget.status) }}
      </span>
    </div>

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
      <!-- Budget Overview -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="card in overviewCards" :key="card.label"
          class="bg-white shadow-md rounded-xl px-4 py-6 hover:shadow-lg transition">
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
              <Link v-if="can_edit" :href="route('budgets.edit', budget.id)"
                class="action-btn border bg-blue-900 border-gray-300 text-white hover:bg-blue-600">
              ✏️ Edit Budget
              </Link>

              <button v-if="can_approve && budget.status === 'draft'" @click="approveBudget" :disabled="processing"
                class="action-btn bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                ✅ Approve Budget
              </button>

              <button v-if="can_activate && budget.status === 'approved'" @click="activateBudget" :disabled="processing"
                class="action-btn bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                ⚡ Activate Budget
              </button>

              <button v-if="can_close && budget.status === 'active'" @click="closeBudget" :disabled="processing"
                class="action-btn bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                🔒 Close Budget
              </button>

              <div class="border-t border-gray-200 pt-3 space-y-2">
                <Link v-for="(link, i) in viewLinks" :key="i" :href="link.href"
                  class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
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
                <span class="text-xs text-green-700 font-semibold">{{ utilization.utilization_percentage.toFixed(1)
                  }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="h-2 rounded-full transition-all"
                  :style="`width:${Math.min(utilization.utilization_percentage, 100)}%`" :class="{
    'bg-green-600': utilization.utilization_percentage <= 75,
    'bg-yellow-500': utilization.utilization_percentage > 75 && utilization.utilization_percentage <= 90,
    'bg-red-500': utilization.utilization_percentage > 90
  }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Budget Items -->
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center flex-wrap gap-2">
          <h3 class="text-lg font-semibold text-gray-900">Budget Items</h3>
          <Link :href="route('budgets.items', budget.id)" class="text-sm text-indigo-600 hover:text-indigo-800">View All
          →</Link>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th v-for="header in ['Category', 'Item', 'Budgeted', 'Spent', 'Remaining']" :key="header"
                  class="px-6 py-3 text-left font-medium text-gray-600">
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="item in budget.budget_items.slice(0, 5)" :key="item.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-3 text-gray-800">{{ item.category }}</td>
                <td class="px-6 py-3 text-gray-800">
                  <div class="font-semibold">{{ item.item_name }}</div>
                  <div v-if="item.description" class="text-gray-500 text-xs">{{ item.description }}</div>
                </td>
                <td class="px-6 py-3 text-right">{{ formatCurrency(item.budgeted_amount) }}</td>
                <td class="px-6 py-3 text-right">{{ formatCurrency(item.spent_amount) }}</td>
                <td class="px-6 py-3 text-right font-semibold"
                  :class="item.remaining_amount < 0 ? 'text-red-600' : 'text-green-600'">
                  {{ formatCurrency(item.remaining_amount) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- GLOBAL LOADING OVERLAY -->
<transition
  enter-active-class="duration-200 ease-out"
  leave-active-class="duration-150 ease-in"
>
  <div
    v-if="showOverlay"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center"
  >
    <div class="bg-white/90 px-6 py-4 rounded-xl shadow-xl flex flex-col items-center gap-3">
      <svg
        class="animate-spin h-8 w-8 text-blue-600"
        xmlns="http://www.w3.org/2000/svg"
        fill="none" viewBox="0 0 24 24"
      >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor"
          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
        </path>
      </svg>

      <p class="text-gray-700 text-sm font-medium">Processing...</p>
    </div>
  </div>
</transition>

  </AppLayout>
  <!-- Modern Confirmation Modal -->
<transition
  enter-active-class="duration-200 ease-out"
  enter-from-class="opacity-0"
  enter-to-class="opacity-100"
  leave-active-class="duration-150 ease-in"
  leave-from-class="opacity-100"
  leave-to-class="opacity-0"
>
  <div
    v-if="confirmDialog.visible"
    class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
  >
    <div
      class="bg-white max-w-md w-full rounded-2xl shadow-xl p-6 animate-fadeIn"
    >
      <div class="flex items-start gap-4">
        <div class="p-3 bg-blue-100 text-blue-600 rounded-xl">
          ⚠️
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900">
            {{ confirmDialog.title }}
          </h3>
          <p class="text-gray-600 mt-1">
            {{ confirmDialog.message }}
          </p>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button
          @click="confirmDialog.visible = false"
          class="px-4 py-2 rounded-lg border text-gray-700 hover:bg-gray-100"
        >
          Cancel
        </button>

        <button
          @click="confirmDialog.confirm"
          class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
        >
          Confirm
        </button>
      </div>
    </div>
  </div>
</transition>

</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { DollarSign, TrendingUp, TrendingDown, PieChart } from 'lucide-vue-next'
const page = usePage()

const props = defineProps({
  budget: Object,
  utilization: Object,
  recent_vouchers: Array,
  can_approve: Boolean,
  can_activate: Boolean,
  can_close: Boolean,
  can_edit: Boolean
})



const flashMessage = ref(page.props.flash.success || page.props.flash.error);
const flashType = ref(page.props.flash.success ? 'success' : 'error');

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      flashMessage.value = flash.success
      flashType.value = 'success'
    } else if (flash?.error) {
      flashMessage.value = flash.error
      flashType.value = 'error'
    }

    if (flashMessage.value) {
      // Scroll to top instantly
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      })

      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)


const processing = ref(false)

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

const confirmDialog = ref({
  visible: false,
  title: '',
  message: '',
  confirm: null
})

function openConfirm({ title, message, onConfirm }) {
  confirmDialog.value = {
    visible: true,
    title,
    message,
    confirm: () => {
      confirmDialog.value.visible = false
      onConfirm()
    }
  }
}

const showOverlay = ref(false)

const approveBudget = () => {
  if (processing.value) return

  openConfirm({
    title: "Approve Budget?",
    message: "Are you sure you want to approve this budget?",
    onConfirm: () => {
      processing.value = true
      showOverlay.value = true

      router.post(route('budgets.approve', props.budget.id), {}, {
        onFinish: () => {
          processing.value = false
          showOverlay.value = false
        }
      })
    }
  })
}


const activateBudget = () => {
  if (processing.value) return

  openConfirm({
    title: "Activate Budget?",
    message: "Activating this budget will deactivate any other active budgets for this year.",
    onConfirm: () => {
      processing.value = true
      showOverlay.value = true

      router.post(route('budgets.activate', props.budget.id), {}, {
        onFinish: () => {
          processing.value = false
          showOverlay.value = false
        }
      })
    }
  })
}


const closeBudget = () => {
  if (processing.value) return

  openConfirm({
    title: "Close Budget?",
    message: "Closing a budget cannot be undone. Continue?",
    onConfirm: () => {
      processing.value = true
      showOverlay.value = true

      router.post(route('budgets.close', props.budget.id), {}, {
        onFinish: () => {
          processing.value = false
          showOverlay.value = false
        }
      })
    }
  })
}


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
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  transition: all 0.2s ease-in-out;
}

@keyframes fadeIn {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
.animate-fadeIn {
  animation: fadeIn 0.15s ease-out;
}
</style>