<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Dividend Payment Schedule" />

    <div class="max-w-7xl px-4 py-6 space-y-6">

      <!-- PAGE HEADER -->
      <div
        class="bg-white dark:bg-slate-800 rounded-2xl
               border border-slate-200 dark:border-slate-700
               p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            Dividend Payment Schedule
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
            Monitor, review, and process member dividend payouts
          </p>
        </div>

        <!-- YEAR SELECT -->
        <div class="flex items-center gap-3">
          <span class="text-sm text-slate-500 dark:text-slate-400">
            Year
          </span>

          <select
            v-model="selectedYear"
            @change="changeYear"
            class="rounded-lg border px-4 py-2 text-sm font-medium
                   bg-white dark:bg-slate-900
                   text-slate-900 dark:text-white
                   border-slate-300 dark:border-slate-600
                   focus:ring-2 focus:ring-blue-500 focus:outline-none"
          >
            <option
              v-for="y in availableYears"
              :key="y"
              :value="y"
            >
              {{ y }}
            </option>
          </select>
        </div>
      </div>

      <!-- SUMMARY CARDS -->
      <div
        v-if="summary"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4"
      >
        <SummaryCard label="Total Members" :value="summary.total_members" />
        <SummaryCard label="Paid Members" :value="summary.paid_count" />
        <SummaryCard label="Pending Payments" :value="summary.pending_count" />
        <SummaryCard
          label="Total Dividends"
          :value="currency(summary.total_dividends)"
        />
        <SummaryCard
          label="Completion"
          :value="summary.payment_progress + '%'"
        />
      </div>

      <!-- EMPTY STATE -->
      <div
        v-else
        class="rounded-xl border border-amber-200 dark:border-amber-800
               bg-amber-50 dark:bg-amber-900/20
               p-5 text-amber-800 dark:text-amber-300"
      >
        No approved dividend data found for the selected year.
      </div>

      <!-- PAYMENT PROGRESS -->
      <div
        v-if="summary"
        class="bg-white dark:bg-slate-800 rounded-2xl
               border border-slate-200 dark:border-slate-700 p-6"
      >
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-slate-900 dark:text-white">
            Payment Progress
          </h3>
          <span class="text-sm font-medium text-slate-600 dark:text-slate-400">
            {{ summary.payment_progress }}%
          </span>
        </div>

        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
          <div
            class="bg-blue-600 h-full transition-all duration-500"
            :style="{ width: summary.payment_progress + '%' }"
          ></div>
        </div>

        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
          {{ summary.paid_count }} of {{ summary.total_members }} members paid
        </p>
      </div>

      <!-- DIVIDENDS TABLE -->
      <div
        v-if="dividends.length"
        class="bg-white dark:bg-slate-800 rounded-2xl
               border border-slate-200 dark:border-slate-700 overflow-hidden"
      >
        <table class="w-full text-sm">
          <thead class="bg-slate-50 dark:bg-slate-700">
            <tr>
              <th class="th">Member</th>
              <th class="th">Membership ID</th>
              <th class="th">Dividend Amount</th>
              <th class="th">Status</th>
              <th class="th">Payment Date</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="d in dividends"
              :key="d.member_id"
              class="tr"
            >
              <td class="font-medium text-slate-900 dark:text-white">
                {{ d.member_name }}
              </td>
              <td>{{ d.membership_id }}</td>
              <td>{{ currency(d.amount) }}</td>
              <td>
                <span
                  class="inline-flex items-center px-2.5 py-1 text-xs
                         rounded-full font-semibold"
                  :class="d.status === 'paid'
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300'
                    : 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300'"
                >
                  {{ d.status }}
                </span>
              </td>
              <td>{{ d.payment_date || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- NO DATA -->
      <div
        v-else
        class="text-center text-slate-500 dark:text-slate-400 py-12"
      >
        No dividend records available.
      </div>

      <!-- ACTION -->
      <div class="flex justify-end">
        <button
          class="inline-flex items-center gap-2
                 bg-blue-600 hover:bg-blue-700
                 text-white px-6 py-2.5 rounded-lg
                 font-semibold shadow-sm
                 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        >
          Process Pending Payments
        </button>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SummaryCard from '@/components/SummaryCard.vue'

/* PROPS */
const props = defineProps({
  dividends: {
    type: Array,
    default: () => [],
  },
  summary: {
    type: Object,
    default: null,
  },
  periods: {
    type: Array,
    default: () => [],
  },
  year: {
    type: [String, Number],
    default: '',
  },
})

/* BREADCRUMBS */
const breadcrumbs = [
  { title: 'Schedule', href: '#' },
  { title: 'Dividend Payments' },
]

/* FALLBACK YEARS (UI SAFETY) */
const fallbackYears = [
  new Date().getFullYear() - 3,
  new Date().getFullYear() - 2,
  new Date().getFullYear() - 1,
  new Date().getFullYear(),
]

/* YEARS TO SHOW */
const availableYears = computed(() =>
  props.periods.length ? props.periods : fallbackYears
)

/* SELECTED YEAR */
const selectedYear = ref(props.year || availableYears.value[0])

/* METHODS */
const changeYear = () => {
  router.get(
    route('schedule.dividend-payment'),
    { year: selectedYear.value },
    { preserveState: true, replace: true }
  )
}

const currency = (value) =>
  new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(value || 0)
</script>

<style scoped>
.th {
  padding: 0.75rem 1rem;
  text-align: left;
  font-weight: 600;
  color: #334155;
}

.dark .th {
  color: #e2e8f0;
}

.tr {
  border-top: 1px solid #e5e7eb;
}

.dark .tr {
  border-top-color: #334155;
}

.tr:hover {
  background-color: #f8fafc;
}

.dark .tr:hover {
  background-color: #334155;
}
</style>
