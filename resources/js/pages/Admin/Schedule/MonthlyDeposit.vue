<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Monthly Deposit Schedule" />

    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            Monthly Deposit Schedule
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Expected vs actual member deposits
          </p>
        </div>

        <input
          type="month"
          v-model="selectedMonth"
          @change="changeMonth"
          class="rounded-lg border px-3 py-2
                 bg-white dark:bg-slate-800
                 text-slate-900 dark:text-white
                 border-slate-300 dark:border-slate-600"
        />
      </div>

      <!-- Summary -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <SummaryCard label="Total Members" :value="summary.total_members" />
        <SummaryCard label="Deposited" :value="summary.deposited_count" />
        <SummaryCard label="Pending" :value="summary.pending_count" />
        <SummaryCard label="Expected" :value="currency(summary.total_expected)" />
        <SummaryCard label="Collection Rate" :value="summary.collection_rate + '%'" />
      </div>

      <!-- Weekly Progress -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border dark:border-slate-700 p-4">
        <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">
          Weekly Deposit Progress
        </h3>

        <table class="w-full text-sm">
          <thead class="bg-slate-100 dark:bg-slate-700">
            <tr>
              <th class="th">Week</th>
              <th class="th">Deposits</th>
              <th class="th">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="w in weeklyProgress" :key="w.week" class="tr">
              <td>Week {{ w.week }}</td>
              <td>{{ w.count }}</td>
              <td>{{ currency(w.amount) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Deposit Schedule -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border dark:border-slate-700 overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-100 dark:bg-slate-700">
            <tr>
              <th class="th">Member</th>
              <th class="th">Membership ID</th>
              <th class="th">Expected</th>
              <th class="th">Deposited</th>
              <th class="th">Variance</th>
              <th class="th">Status</th>
              <th class="th">Deposit Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in deposits" :key="d.member_id" class="tr">
              <td>{{ d.member_name }}</td>
              <td>{{ d.membership_id }}</td>
              <td>{{ currency(d.expected_amount) }}</td>
              <td>{{ currency(d.deposited_amount) }}</td>
              <td
                :class="d.variance >= 0
                  ? 'text-green-600 dark:text-green-400'
                  : 'text-red-600 dark:text-red-400'"
              >
                {{ currency(d.variance) }}
              </td>
              <td>
                <span
                  class="px-2 py-1 text-xs rounded-full font-semibold"
                  :class="d.status === 'deposited'
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                    : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'"
                >
                  {{ d.status }}
                </span>
              </td>
              <td>{{ d.deposit_date ?? '-' }}</td>
            </tr>
          </tbody>
        </table>
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
  deposits: Array,
  summary: Object,
  weeklyProgress: Array,
  month: String,
})

const breadcrumbs = [
  { title: 'Schedule', href: '#' },
  { title: 'Monthly Deposits' }
]

const selectedMonth = ref(props.month)

const changeMonth = () => {
  router.get(route('schedule.monthly-deposit'), {
    month: selectedMonth.value,
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
  padding: 0.75rem 1rem;   /* py-3 px-4 */
  text-align: left;
  font-weight: 600;       /* font-semibold */
  color: #334155;         /* slate-700 */
}

.dark .th {
  color: #e2e8f0;         /* slate-200 */
}

.tr {
  border-top: 1px solid #e5e7eb;
}

.dark .tr {
  border-top-color: #334155; /* slate-700 */
}

.tr:hover {
  background-color: #f8fafc; /* slate-50 */
}

.dark .tr:hover {
  background-color: #334155; /* slate-700 */
}

</style>
