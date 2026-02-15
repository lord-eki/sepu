<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CalendarDays } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'
import axios from 'axios'

const props = defineProps<{
  assets: Record<string, any>
  liabilities: Record<string, any>
  equity: Record<string, any>
  totals: {
    total_assets: number
    total_liabilities: number
    total_equity: number
  }
  date: string
}>()

const selectedDate = ref(props.date)

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Financial Reports', href: route('reports.financial.index') },
  { title: 'Balance Sheet' },
]

function applyDateFilter() {
  router.get(
    route('reports.financial.balance-sheet'),
    { date: selectedDate.value },
    { preserveState: true, replace: true }
  )
}

function money(value: number) {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(value ?? 0)
}

const exporting = ref(false)

async function exportReport(format: 'pdf' | 'excel' | 'csv') {
  try {
    exporting.value = true

    const response = await axios.post(
      route('reports.financial.export'),
      {
        report_type: 'balance_sheet',
        format: format,
        date: selectedDate.value,
      },
      {
        responseType: 'blob',
      }
    )

    const blob = new Blob([response.data])
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)

    const fileName = `balance_sheet_${selectedDate.value}.${format === 'excel' ? 'xlsx' : format}`
    link.setAttribute('download', fileName)

    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Export failed:', error)
  } finally {
    exporting.value = false
  }
}

</script>

<template>

  <Head title="Balance Sheet" />

  <AppLayout title="Balance Sheet" :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="flex flex-col gap-4 mx-6 mt-6 mb-10">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-2xl font-extrabold text-[#0a2342] dark:text-blue-400">
            Balance Sheet
          </h2>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Statement of financial position as at the selected date
          </p>
        </div>

        <div class="flex gap-4">
          <!-- Date Picker -->
          <div class="flex items-center gap-2">
            <CalendarDays class="h-5 w-5 text-orange-500" />
            <input type="date" v-model="selectedDate" @change="applyDateFilter" class="rounded-xl border border-gray-300 dark:border-gray-700
                   bg-white dark:bg-gray-900 px-4 py-2 text-sm
                   text-gray-700 dark:text-gray-200
                   focus:outline-none focus:ring-2 focus:ring-orange-500" />
          </div>
          <div class="flex items-center gap-2">
            <button @click="exportReport('excel')"
              class="px-4 py-2 rounded-xl bg-green-700 text-white text-sm font-medium hover:bg-green-800 transition">
              Excel
            </button>

            <button @click="exportReport('pdf')"
              class="px-4 py-2 rounded-xl bg-red-700 text-white text-sm font-medium hover:bg-red-800 transition">
              PDF
            </button>

            <button @click="exportReport('csv')"
              class="px-4 py-2 rounded-xl bg-blue-800 text-white text-sm font-medium hover:bg-blue-900 transition">
              CSV
            </button>
          </div>
        </div>
      </div>

      <!-- Accounting Equation -->
      <div class="rounded-xl border border-dashed border-blue-200 dark:border-blue-800
                  bg-gray-50 dark:bg-gray-800 px-4 py-6 text-sm sm:text-base
                  text-[#0a2342] dark:text-blue-300">
        <span class="font-semibold">Assets</span>
        =
        <span class="font-semibold text-orange-500">Liabilities</span>
        +
        <span class="font-semibold">Equity</span>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mx-6">
      <!-- ASSETS -->
      <div class="rounded-3xl border border-gray-200 dark:border-gray-700
                  bg-white dark:bg-gray-900 shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700
                    bg-gray-50 dark:bg-gray-800 rounded-t-3xl">
          <h3 class="text-lg font-bold text-[#0a2342] dark:text-blue-400">
            Assets
          </h3>
        </div>

        <div class="p-6 space-y-6">
          <div v-for="group in assets" :key="group.label">
            <h4 class="text-xs font-semibold uppercase tracking-wide
                       text-orange-500 mb-3">
              {{ group.label }}
            </h4>

            <div class="space-y-2">
              <div v-for="item in group.accounts" :key="item.name" class="flex justify-between items-center text-sm
                          text-gray-700 dark:text-gray-300">
                <span>{{ item.name }}</span>
                <span class="font-medium">{{ money(item.balance) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Assets -->
        <div class="flex justify-between items-center px-6 py-4
                    bg-orange-50 dark:bg-gray-800
                    border-t-2 border-orange-400
                    font-bold text-base text-[#0a2342] dark:text-orange-400">
          <span>Total Assets</span>
          <span>{{ money(totals.total_assets) }}</span>
        </div>
      </div>

      <!-- LIABILITIES & EQUITY -->
      <div class="rounded-3xl border border-gray-200 dark:border-gray-700
                  bg-white dark:bg-gray-900 shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700
                    bg-gray-50 dark:bg-gray-800 rounded-t-3xl">
          <h3 class="text-lg font-bold text-[#0a2342] dark:text-blue-400">
            Liabilities & Equity
          </h3>
        </div>

        <div class="p-6 space-y-6">
          <!-- Liabilities -->
          <div v-for="group in liabilities" :key="group.label">
            <h4 class="text-xs font-semibold uppercase tracking-wide
                       text-orange-500 mb-3">
              {{ group.label }}
            </h4>

            <div class="space-y-2">
              <div v-for="item in group.accounts" :key="item.name" class="flex justify-between items-center text-sm
                          text-gray-700 dark:text-gray-300">
                <span>{{ item.name }}</span>
                <span class="font-medium">{{ money(item.balance) }}</span>
              </div>
            </div>
          </div>

          <!-- Total Liabilities -->
          <div class="flex justify-between items-center pt-3
                      border-t border-gray-200 dark:border-gray-700
                      font-semibold text-sm text-[#0a2342] dark:text-blue-300">
            <span>Total Liabilities</span>
            <span>{{ money(totals.total_liabilities) }}</span>
          </div>

          <!-- Equity -->
          <div>
            <h4 class="text-xs font-semibold uppercase tracking-wide
                       text-orange-500 mb-3">
              Equity
            </h4>

            <div class="space-y-2">
              <div v-for="item in equity" :key="item.name" class="flex justify-between items-center text-sm
                          text-gray-700 dark:text-gray-300">
                <span>{{ item.name }}</span>
                <span class="font-medium">{{ money(item.balance) }}</span>
              </div>
            </div>
          </div>

          <!-- Total Equity -->
          <div class="flex justify-between items-center pt-3
                      border-t border-gray-200 dark:border-gray-700
                      font-semibold text-sm text-[#0a2342] dark:text-blue-300">
            <span>Total Equity</span>
            <span>{{ money(totals.total_equity) }}</span>
          </div>
        </div>

        <!-- Total L + E -->
        <div class="flex justify-between items-center px-6 py-4
                    bg-[#0a2342] dark:bg-gray-800
                    text-white font-bold text-base rounded-b-3xl">
          <span>Total Liabilities & Equity</span>
          <span>{{ money(totals.total_liabilities + totals.total_equity) }}</span>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
