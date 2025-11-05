<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'

const props = defineProps({
  accounts: Array,     // [{code, name, debit, credit}]
  totals: Object,      // { debit: number, credit: number }
  start_date: String,
  end_date: String,
})

const breadcrumbs = [
  { title: 'Reports', href: route('reports.index') },
  { title: 'Financial Reports', href: route('reports.financial') },
  { title: 'Trial Balance' },
]

const filter = ref({
  start_date: props.start_date,
  end_date: props.end_date,
})

const exportReport = (format: string) => {
  router.visit(route('reports.financial.trial-balance'), {
    method: 'get',
    data: { ...filter.value, export: format },
    preserveScroll: true,
  })
}

const formatted = (val: number) =>
  val?.toLocaleString(undefined, { minimumFractionDigits: 2 })

const balanceDiff = computed(
  () => (props.totals.debit || 0) - (props.totals.credit || 0)
)
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Trial Balance" />

    <div class="p-6 space-y-6">
      <div class="flex justify-between items-center border-b pb-3">
        <h1 class="text-2xl font-bold text-gray-800">Trial Balance</h1>

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

      <!-- Filter -->
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
          @click="router.visit(route('reports.financial.trial-balance'), { method: 'get', data: filter.value })"
          class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
        >
          Filter
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto bg-white rounded-xl shadow border border-gray-100">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold">
            <tr>
              <th class="px-4 py-3 text-left">Account Code</th>
              <th class="px-4 py-3 text-left">Account Name</th>
              <th class="px-4 py-3 text-right">Debit (KES)</th>
              <th class="px-4 py-3 text-right">Credit (KES)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-sm">
            <tr v-for="(acc, i) in props.accounts" :key="i">
              <td class="px-4 py-2 font-mono">{{ acc.code }}</td>
              <td class="px-4 py-2">{{ acc.name }}</td>
              <td class="px-4 py-2 text-right text-gray-800">
                {{ formatted(acc.debit || 0) }}
              </td>
              <td class="px-4 py-2 text-right text-gray-800">
                {{ formatted(acc.credit || 0) }}
              </td>
            </tr>
          </tbody>

          <tfoot class="bg-gray-50 font-semibold text-gray-900">
            <tr>
              <td colspan="2" class="px-4 py-2 text-right">Totals:</td>
              <td class="px-4 py-2 text-right">{{ formatted(props.totals.debit) }}</td>
              <td class="px-4 py-2 text-right">{{ formatted(props.totals.credit) }}</td>
            </tr>
            <tr>
              <td colspan="2" class="px-4 py-2 text-right">Difference:</td>
              <td colspan="2" class="px-4 py-2 text-right">
                <span :class="balanceDiff === 0 ? 'text-green-600' : 'text-red-600'">
                  {{ formatted(balanceDiff) }}
                </span>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <p v-if="balanceDiff === 0" class="text-green-600 font-medium">
        ✅ Trial Balance is Balanced.
      </p>
      <p v-else class="text-red-600 font-medium">
        ⚠️ Trial Balance is Not Balanced — Please check entries.
      </p>
    </div>
  </AppLayout>
</template>
