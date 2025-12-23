<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Dividend Payment Schedule" />

    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            Dividend Payment Schedule
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Monitor and manage dividend payouts
          </p>
        </div>

        <select
          v-model="selectedPeriod"
          @change="changePeriod"
          class="rounded-lg border px-3 py-2
                 bg-white dark:bg-slate-800
                 text-slate-900 dark:text-white
                 border-slate-300 dark:border-slate-600"
        >
          <option v-for="p in periods" :key="p" :value="p">
            {{ p }}
          </option>
        </select>
      </div>

      <!-- Summary -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <SummaryCard label="Total Members" :value="summary.total_members" />
        <SummaryCard label="Paid" :value="summary.paid_members" />
        <SummaryCard label="Pending" :value="summary.pending_members" />
        <SummaryCard label="Total Dividends" :value="currency(summary.total_dividends)" />
        <SummaryCard label="Payment Progress" :value="summary.progress + '%'" />
      </div>

      <!-- Payment Readiness -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border dark:border-slate-700 p-4">
        <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">
          Payment Readiness
        </h3>

        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
          <div
            class="bg-blue-600 h-full transition-all"
            :style="{ width: summary.progress + '%' }"
          ></div>
        </div>

        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
          {{ summary.paid_members }} of {{ summary.total_members }} members paid
        </p>
      </div>

      <!-- Dividend Table -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border dark:border-slate-700 overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-100 dark:bg-slate-700">
            <tr>
              <th class="th">Member</th>
              <th class="th">Membership ID</th>
              <th class="th">Dividend Amount</th>
              <th class="th">Status</th>
              <th class="th">Payment Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in dividends" :key="d.member_id" class="tr">
              <td>{{ d.member_name }}</td>
              <td>{{ d.membership_id }}</td>
              <td>{{ currency(d.amount) }}</td>
              <td>
                <span
                  class="px-2 py-1 text-xs rounded-full font-semibold"
                  :class="d.status === 'paid'
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                    : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'"
                >
                  {{ d.status }}
                </span>
              </td>
              <td>{{ d.payment_date ?? '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Batch Action -->
      <div class="flex justify-end">
        <button
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold"
        >
          Process Pending Payments
        </button>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SummaryCard from '@/components/SummaryCard.vue'

const props = defineProps({
  dividends: Array,
  summary: Object,
  periods: Array,
  period: String,
})

const breadcrumbs = [
  { title: 'Schedule', href: '#' },
  { title: 'Dividend Payments' }
]

const selectedPeriod = ref(props.period)

const changePeriod = () => {
  router.get(route('schedule.dividends'), {
    period: selectedPeriod.value,
  }, {
    preserveState: true,
    replace: true,
  })
}

const currency = (v) =>
  new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(v || 0)
</script>

<style scoped>
.th {
  padding-left: 1rem;   /* px-4 */
  padding-right: 1rem;
  padding-top: 0.75rem; /* py-3 */
  padding-bottom: 0.75rem;
  text-align: left;
  font-weight: 600;     /* font-semibold */
  color: #334155;       /* slate-700 */
}

.dark .th {
  color: #e2e8f0;       /* slate-200 */
}

.tr {
  border-top: 1px solid #e5e7eb; /* default border */
}

.dark .tr {
  border-top-color: #334155;     /* slate-700 */
}

.tr:hover {
  background-color: #f8fafc;    /* slate-50 */
}

.dark .tr:hover {
  background-color: #334155;    /* slate-700 */
}
</style>

