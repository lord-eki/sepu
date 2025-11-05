<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  revenues: Array,
  expenses: Array,
  totals: Object,
  dateRange: Object,
})

const breadcrumbs = [
  { title: 'Reports', href: route('reports.index') },
  { title: 'Financial Overview', href: route('reports.financial') },
  { title: 'Income Statement' },
]

// Date filter
const filters = ref({
  from: props.dateRange?.from || '',
  to: props.dateRange?.to || '',
})

// Apply filter (re-fetch data)
const applyFilter = () => {
  router.get(route('reports.financial.income-statement'), filters.value, { preserveScroll: true })
}

// Export functionality
const exportData = (format: string) => {
  const query = new URLSearchParams(filters.value).toString()
  window.open(route('reports.financial.income-statement') + `?${query}&export=${format}`, '_blank')
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Income Statement" />

    <div class="p-6 space-y-8">
      <!-- Header -->
      <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-semibold text-gray-800">Income Statement</h1>
          <p class="text-gray-600">
            Summary of revenues and expenses between selected dates.
          </p>
        </div>

        <!-- Export Buttons -->
        <div class="flex gap-3">
          <button
            @click="exportData('csv')"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow"
          >
            Export CSV
          </button>
          <button
            @click="exportData('pdf')"
            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg shadow"
          >
            Export PDF
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap gap-3 bg-white p-4 rounded-xl shadow border border-gray-100">
        <div>
          <label class="text-gray-600 text-sm">From:</label>
          <input
            v-model="filters.from"
            type="date"
            class="ml-2 p-2 border rounded-lg text-sm focus:ring focus:ring-indigo-200"
          />
        </div>
        <div>
          <label class="text-gray-600 text-sm">To:</label>
          <input
            v-model="filters.to"
            type="date"
            class="ml-2 p-2 border rounded-lg text-sm focus:ring focus:ring-indigo-200"
          />
        </div>
        <button
          @click="applyFilter"
          class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow"
        >
          Apply
        </button>
      </div>

      <!-- Revenue Section -->
      <section class="bg-white rounded-2xl shadow p-6 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Revenues</h2>
        <table class="min-w-full text-sm text-left text-gray-600">
          <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
              <th class="py-3 px-4">Account</th>
              <th class="py-3 px-4 text-right">Amount (KES)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in revenues" :key="r.id" class="border-b hover:bg-gray-50">
              <td class="py-3 px-4">{{ r.name }}</td>
              <td class="py-3 px-4 text-right font-medium text-gray-800">
                {{ new Intl.NumberFormat().format(r.amount) }}
              </td>
            </tr>
            <tr class="font-semibold text-gray-800 border-t">
              <td class="py-3 px-4">Total Revenues</td>
              <td class="py-3 px-4 text-right">
                {{ new Intl.NumberFormat().format(totals.total_revenue) }}
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Expenses Section -->
      <section class="bg-white rounded-2xl shadow p-6 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Expenses</h2>
        <table class="min-w-full text-sm text-left text-gray-600">
          <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
              <th class="py-3 px-4">Account</th>
              <th class="py-3 px-4 text-right">Amount (KES)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in expenses" :key="e.id" class="border-b hover:bg-gray-50">
              <td class="py-3 px-4">{{ e.name }}</td>
              <td class="py-3 px-4 text-right font-medium text-gray-800">
                {{ new Intl.NumberFormat().format(e.amount) }}
              </td>
            </tr>
            <tr class="font-semibold text-gray-800 border-t">
              <td class="py-3 px-4">Total Expenses</td>
              <td class="py-3 px-4 text-right">
                {{ new Intl.NumberFormat().format(totals.total_expense) }}
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Net Income -->
      <section class="text-right mt-6">
        <h3
          class="text-2xl font-semibold"
          :class="totals.net_income >= 0 ? 'text-green-600' : 'text-rose-600'"
        >
          Net {{ totals.net_income >= 0 ? 'Profit' : 'Loss' }}:
          {{ new Intl.NumberFormat().format(totals.net_income) }} KES
        </h3>
      </section>
    </div>
  </AppLayout>
</template>
