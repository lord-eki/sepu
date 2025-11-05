<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed } from 'vue'

// Props from backend (ReportController@cashFlow)
const props = defineProps({
  operations: Array,
  investing: Array,
  financing: Array,
  totals: Object,
  start_date: String,
  end_date: String
})

const breadcrumbs = [
  { title: 'Reports', href: route('reports.index') },
  { title: 'Financial Reports', href: route('reports.financial') },
  { title: 'Cash Flow Report' }
]

// local filter state
const filter = ref({
  start_date: props.start_date,
  end_date: props.end_date
})

// download/export handler
const exportReport = (format: string) => {
  router.visit(route('reports.financial.cash-flow'), {
    method: 'get',
    data: { ...filter.value, export: format },
    preserveScroll: true,
  })
}

const formattedTotal = (amount: number) => {
  return amount?.toLocaleString(undefined, { minimumFractionDigits: 2 })
}

const netFlow = computed(() => {
  const { operating, investing, financing } = props.totals || {}
  return (operating || 0) + (investing || 0) + (financing || 0)
})
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Cash Flow Report" />

    <div class="p-6 space-y-6">
      <div class="flex justify-between items-center border-b pb-3">
        <h1 class="text-2xl font-bold text-gray-800">Cash Flow Report</h1>

        <div class="flex gap-2">
          <button
            @click="exportReport('csv')"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
          >
            Export CSV
          </button>
          <button
            @click="exportReport('pdf')"
            class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600"
          >
            Export PDF
          </button>
        </div>
      </div>

      <!-- Date Filter -->
      <div class="flex flex-wrap gap-3 items-end">
        <div>
          <label class="text-sm text-gray-600">Start Date</label>
          <input v-model="filter.start_date" type="date" class="border rounded px-3 py-2" />
        </div>
        <div>
          <label class="text-sm text-gray-600">End Date</label>
          <input v-model="filter.end_date" type="date" class="border rounded px-3 py-2" />
        </div>
        <button
          @click="router.visit(route('reports.financial.cash-flow'), { method: 'get', data: filter.value })"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
        >
          Filter
        </button>
      </div>

      <!-- Cash Flow Sections -->
      <div class="grid md:grid-cols-3 gap-6">
        <!-- Operating Activities -->
        <div class="bg-white rounded-xl shadow p-5">
          <h2 class="font-semibold text-gray-800 mb-3">Operating Activities</h2>
          <ul class="space-y-1 text-sm text-gray-600 mb-3">
            <li v-for="(item, i) in props.operations" :key="i" class="flex justify-between">
              <span>{{ item.label }}</span>
              <span class="font-medium">{{ formattedTotal(item.amount) }}</span>
            </li>
          </ul>
          <div class="border-t pt-2 text-right font-bold text-gray-800">
            Total: {{ formattedTotal(props.totals.operating) }}
          </div>
        </div>

        <!-- Investing Activities -->
        <div class="bg-white rounded-xl shadow p-5">
          <h2 class="font-semibold text-gray-800 mb-3">Investing Activities</h2>
          <ul class="space-y-1 text-sm text-gray-600 mb-3">
            <li v-for="(item, i) in props.investing" :key="i" class="flex justify-between">
              <span>{{ item.label }}</span>
              <span class="font-medium">{{ formattedTotal(item.amount) }}</span>
            </li>
          </ul>
          <div class="border-t pt-2 text-right font-bold text-gray-800">
            Total: {{ formattedTotal(props.totals.investing) }}
          </div>
        </div>

        <!-- Financing Activities -->
        <div class="bg-white rounded-xl shadow p-5">
          <h2 class="font-semibold text-gray-800 mb-3">Financing Activities</h2>
          <ul class="space-y-1 text-sm text-gray-600 mb-3">
            <li v-for="(item, i) in props.financing" :key="i" class="flex justify-between">
              <span>{{ item.label }}</span>
              <span class="font-medium">{{ formattedTotal(item.amount) }}</span>
            </li>
          </ul>
          <div class="border-t pt-2 text-right font-bold text-gray-800">
            Total: {{ formattedTotal(props.totals.financing) }}
          </div>
        </div>
      </div>

      <!-- Net Cash Flow Summary -->
      <div class="bg-gray-50 border-t rounded-xl p-4 text-right font-semibold text-lg text-gray-900">
        Net Cash Flow:
        <span
          :class="netFlow >= 0 ? 'text-green-600' : 'text-red-600'"
        >
          {{ formattedTotal(netFlow) }}
        </span>
      </div>
    </div>
  </AppLayout>
</template>
