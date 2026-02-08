<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CalendarDays } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'

const props = defineProps<{
  operating_activities: Record<string, number>
  investing_activities: Record<string, number>
  financing_activities: Record<string, number>
  totals: {
    operating: number
    investing: number
    financing: number
    net_cash_flow: number
  }
  start_date: string
  end_date: string
}>()

const startDate = ref(props.start_date)
const endDate = ref(props.end_date)

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Financial Reports', href: route('reports.financial.index') },
  { title: 'Cash Flow' },
]

function applyDateFilter() {
  router.get(
    route('reports.financial.cash-flow'),
    { start_date: startDate.value, end_date: endDate.value },
    { preserveState: true, replace: true }
  )
}

function money(value: number) {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(value ?? 0)
}
</script>

<template>
  <Head title="Cash Flow" />

  <AppLayout title="Cash Flow" :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 mx-6 mt-6">
      <div>
        <h2 class="text-2xl font-extrabold text-[#0a2342] dark:text-blue-400">
          Cash Flow
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Statement of cash flows for the selected period
        </p>
      </div>

      <!-- Date Range Picker -->
      <div class="flex items-center gap-2">
        <CalendarDays class="h-5 w-5 text-orange-500" />
        <input
          type="date"
          v-model="startDate"
          @change="applyDateFilter"
          class="rounded-xl border border-gray-300 dark:border-gray-700
                 bg-white dark:bg-gray-900 px-3 py-2 text-sm
                 text-gray-700 dark:text-gray-200
                 focus:outline-none focus:ring-2 focus:ring-orange-500"
        />
        <span class="text-gray-500 dark:text-gray-400">to</span>
        <input
          type="date"
          v-model="endDate"
          @change="applyDateFilter"
          class="rounded-xl border border-gray-300 dark:border-gray-700
                 bg-white dark:bg-gray-900 px-3 py-2 text-sm
                 text-gray-700 dark:text-gray-200
                 focus:outline-none focus:ring-2 focus:ring-orange-500"
        />
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mx-6">
      <!-- Operating Activities -->
      <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-md hover:shadow-lg transition-all">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-[#0a2342] rounded-t-3xl">
          <h3 class="text-lg font-semibold text-[#0a2342] dark:text-blue-400">
            Operating Activities
          </h3>
        </div>

        <div class="p-6 space-y-3">
          <div
            v-for="(value, key) in operating_activities"
            :key="key"
            class="flex justify-between text-sm text-gray-700 dark:text-gray-300 hover:text-orange-500 transition-colors"
          >
            <span>{{ key.replace(/_/g, ' ').toUpperCase() }}</span>
            <span class="font-medium">{{ money(value) }}</span>
          </div>
        </div>

        <div class="flex justify-between items-center px-6 py-4 border-t-2 border-orange-400 font-bold text-base text-[#0a2342] dark:text-orange-400">
          <span>Total Operating</span>
          <span>{{ money(totals.operating) }}</span>
        </div>
      </div>

      <!-- Investing Activities -->
      <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-md hover:shadow-lg transition-all">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-[#0a2342] rounded-t-3xl">
          <h3 class="text-lg font-semibold text-[#0a2342] dark:text-blue-400">
            Investing Activities
          </h3>
        </div>

        <div class="p-6 space-y-3">
          <div
            v-for="(value, key) in investing_activities"
            :key="key"
            class="flex justify-between text-sm text-gray-700 dark:text-gray-300 hover:text-orange-500 transition-colors"
          >
            <span>{{ key.replace(/_/g, ' ').toUpperCase() }}</span>
            <span class="font-medium">{{ money(value) }}</span>
          </div>
        </div>

        <div class="flex justify-between items-center px-6 py-4 border-t-2 border-orange-400 font-bold text-base text-[#0a2342] dark:text-orange-400">
          <span>Total Investing</span>
          <span>{{ money(totals.investing) }}</span>
        </div>
      </div>

      <!-- Financing Activities -->
      <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-md hover:shadow-lg transition-all">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-[#0a2342] rounded-t-3xl">
          <h3 class="text-lg font-semibold text-[#0a2342] dark:text-blue-400">
            Financing Activities
          </h3>
        </div>

        <div class="p-6 space-y-3">
          <div
            v-for="(value, key) in financing_activities"
            :key="key"
            class="flex justify-between text-sm text-gray-700 dark:text-gray-300 hover:text-orange-500 transition-colors"
          >
            <span>{{ key.replace(/_/g, ' ').toUpperCase() }}</span>
            <span class="font-medium">{{ money(value) }}</span>
          </div>
        </div>

        <div class="flex justify-between items-center px-6 py-4 border-t-2 border-orange-400 font-bold text-base text-[#0a2342] dark:text-orange-400">
          <span>Total Financing</span>
          <span>{{ money(totals.financing) }}</span>
        </div>
      </div>
    </div>

    <!-- Net Cash Flow -->
    <div class="mt-8 rounded-3xl border border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-[#0a2342] p-6 shadow-md flex justify-between items-center font-bold text-lg text-[#0a2342] dark:text-orange-400">
      <span>Net Cash Flow</span>
      <span>{{ money(totals.net_cash_flow) }}</span>
    </div>
  </AppLayout>
</template>
