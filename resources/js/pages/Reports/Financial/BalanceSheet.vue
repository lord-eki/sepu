<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  assets: Array,
  liabilities: Array,
  equity: Array,
  totals: Object,
  asOfDate: String,
})

const breadcrumbs = [
  { title: 'Reports', href: route('reports.index') },
  { title: 'Financial Overview', href: route('reports.financial') },
  { title: 'Balance Sheet' },
]

const exportData = (format: string) => {
  window.open(route('reports.financial.balance-sheet') + `?export=${format}`, '_blank')
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Balance Sheet" />

    <div class="p-6 space-y-8">
      <!-- Header -->
      <div class="flex justify-between items-center flex-wrap gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-semibold text-gray-800">Balance Sheet</h1>
          <p class="text-gray-600">Financial position of the SACCO as of {{ asOfDate }}</p>
        </div>

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

      <!-- Assets Section -->
      <section class="bg-white rounded-2xl shadow p-6 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Assets</h2>
        <table class="min-w-full text-sm text-left text-gray-600">
          <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
              <th class="py-3 px-4">Account</th>
              <th class="py-3 px-4 text-right">Amount (KES)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="a in assets" :key="a.id" class="border-b hover:bg-gray-50">
              <td class="py-3 px-4">{{ a.name }}</td>
              <td class="py-3 px-4 text-right font-medium text-gray-800">
                {{ new Intl.NumberFormat().format(a.balance) }}
              </td>
            </tr>
            <tr class="font-semibold text-gray-800 border-t">
              <td class="py-3 px-4">Total Assets</td>
              <td class="py-3 px-4 text-right">
                {{ new Intl.NumberFormat().format(totals.total_assets) }}
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Liabilities Section -->
      <section class="bg-white rounded-2xl shadow p-6 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Liabilities</h2>
        <table class="min-w-full text-sm text-left text-gray-600">
          <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
              <th class="py-3 px-4">Account</th>
              <th class="py-3 px-4 text-right">Amount (KES)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="l in liabilities" :key="l.id" class="border-b hover:bg-gray-50">
              <td class="py-3 px-4">{{ l.name }}</td>
              <td class="py-3 px-4 text-right font-medium text-gray-800">
                {{ new Intl.NumberFormat().format(l.balance) }}
              </td>
            </tr>
            <tr class="font-semibold text-gray-800 border-t">
              <td class="py-3 px-4">Total Liabilities</td>
              <td class="py-3 px-4 text-right">
                {{ new Intl.NumberFormat().format(totals.total_liabilities) }}
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Equity Section -->
      <section class="bg-white rounded-2xl shadow p-6 border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-700 mb-4">Equity</h2>
        <table class="min-w-full text-sm text-left text-gray-600">
          <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
              <th class="py-3 px-4">Account</th>
              <th class="py-3 px-4 text-right">Amount (KES)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in equity" :key="e.id" class="border-b hover:bg-gray-50">
              <td class="py-3 px-4">{{ e.name }}</td>
              <td class="py-3 px-4 text-right font-medium text-gray-800">
                {{ new Intl.NumberFormat().format(e.balance) }}
              </td>
            </tr>
            <tr class="font-semibold text-gray-800 border-t">
              <td class="py-3 px-4">Total Equity</td>
              <td class="py-3 px-4 text-right">
                {{ new Intl.NumberFormat().format(totals.total_equity) }}
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Totals Summary -->
      <section class="text-right">
        <p class="text-sm text-gray-500">Assets = Liabilities + Equity</p>
        <p
          class="text-lg font-semibold mt-2"
          :class="totals.total_assets === totals.total_liabilities + totals.total_equity
            ? 'text-green-600'
            : 'text-rose-600'"
        >
          {{
            totals.total_assets === totals.total_liabilities + totals.total_equity
              ? 'Balanced ✅'
              : 'Unbalanced ⚠️'
          }}
        </p>
      </section>
    </div>
  </AppLayout>
</template>
