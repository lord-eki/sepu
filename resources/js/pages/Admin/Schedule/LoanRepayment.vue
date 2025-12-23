<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Loan Repayment Schedule" />

    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            Loan Repayment Schedule
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Due, overdue and upcoming loan repayments
          </p>
        </div>

        <a
          :href="route('schedule.loan-repayment.export', filters)"
          class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600
                 text-white px-4 py-2 rounded-lg shadow"
        >
          Export CSV
        </a>
      </div>

      <!-- Summary -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <SummaryCard label="Total Expected" :value="currency(summary.total_expected)" />
        <SummaryCard label="Total Paid" :value="currency(summary.total_paid)" />
        <SummaryCard label="Outstanding" :value="currency(summary.total_outstanding)" />
        <SummaryCard label="Overdue Amount" :value="currency(summary.overdue_amount)" />
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 border dark:border-slate-700 rounded-xl p-4">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <Input label="Start Date" type="date" v-model="filters.start_date" />
          <Input label="End Date" type="date" v-model="filters.end_date" />

          <Select label="Status" v-model="filters.status">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="partial">Partial</option>
            <option value="overdue">Overdue</option>
          </Select>

          <Input label="Member ID" v-model="filters.member_id" />

          <div class="flex items-end gap-2">
            <button type="button" class="btn-secondary" @click="clearFilters">Clear</button>
            <button class="btn-primary ml-auto">Apply</button>
          </div>
        </form>
      </div>

      <!-- Daily Schedule -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border dark:border-slate-700 p-4">
        <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">
          Daily Repayment Plan
        </h3>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-100 dark:bg-slate-700">
              <tr>
                <th class="th">Date</th>
                <th class="th">Repayments</th>
                <th class="th">Expected</th>
                <th class="th">Outstanding</th>
                <th class="th">Overdue</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="day in dailySchedule" :key="day.date" class="tr">
                <td>
                  {{ day.date }}
                  <div class="text-xs text-slate-500">{{ day.day_name }}</div>
                </td>
                <td>{{ day.count }}</td>
                <td>{{ currency(day.expected_amount) }}</td>
                <td>{{ currency(day.outstanding_amount) }}</td>
                <td>{{ day.overdue_count }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Member Summary -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border dark:border-slate-700 p-4">
        <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">
          Members With Multiple Dues
        </h3>

        <table class="w-full text-sm">
          <thead class="bg-slate-100 dark:bg-slate-700">
            <tr>
              <th class="th">Member</th>
              <th class="th">Membership ID</th>
              <th class="th">Repayments</th>
              <th class="th">Total Due</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in memberSummary" :key="m.member_id" class="tr">
              <td>{{ m.member_name }}</td>
              <td>{{ m.membership_id }}</td>
              <td>{{ m.repayment_count }}</td>
              <td>{{ currency(m.total_due) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Repayments Table -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border dark:border-slate-700 overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-100 dark:bg-slate-700">
            <tr>
              <th class="th">Due Date</th>
              <th class="th">Loan</th>
              <th class="th">Member</th>
              <th class="th">Expected</th>
              <th class="th">Paid</th>
              <th class="th">Outstanding</th>
              <th class="th">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in repayments.data" :key="r.id" class="tr">
              <td>{{ formatDate(r.due_date) }}</td>
              <td>{{ r.loan.loan_number }}</td>
              <td>
                {{ r.loan.member.first_name }} {{ r.loan.member.last_name }}<br>
                <span class="text-xs text-slate-500">
                  {{ r.loan.member.membership_id }}
                </span>
              </td>
              <td>{{ currency(r.expected_amount) }}</td>
              <td>{{ currency(r.paid_amount) }}</td>
              <td>{{ currency(r.outstanding_amount) }}</td>
              <td>
                <span
                  class="px-2 py-1 rounded-full text-xs font-semibold"
                  :class="statusClass(r.status)"
                >
                  {{ r.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <Pagination :data="repayments" />
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'
import SummaryCard from '@/components/SummaryCard.vue'
import Input from '@/components/Form/Input.vue'
import Select from '@/components/Form/Select.vue'

const props = defineProps({
  repayments: Object,
  summary: Object,
  dailySchedule: Array,
  memberSummary: Array,
  filters: Object,
})

const breadcrumbs = [
  { title: 'Schedule', href: '#' },
  { title: 'Loan Repayment' }
]

const filters = reactive({ ...props.filters })

const applyFilters = () => {
  router.get(route('schedule.loan-repayment'), filters, {
    preserveState: true,
    replace: true,
  })
}

const clearFilters = () => {
  Object.keys(filters).forEach(k => filters[k] = '')
  applyFilters()
}

const currency = (v) =>
  new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(v || 0)

const formatDate = d =>
  d ? new Date(d).toLocaleDateString() : ''

const statusClass = status => ({
  pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
  partial: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
  overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
}[status])
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

/* Primary button */
.btn-primary {
  background-color: #0f172a; /* slate-900 */
  color: #ffffff;
  padding: 0.5rem 1rem;      /* py-2 px-4 */
  border-radius: 0.5rem;     /* rounded-lg */
}

.dark .btn-primary {
  background-color: #f97316; /* orange-500 */
}

/* Secondary button */
.btn-secondary {
  background-color: #e5e7eb; /* slate-200 */
  color: #0f172a;            /* slate-900 */
  padding: 0.5rem 1rem;      /* py-2 px-4 */
  border-radius: 0.5rem;     /* rounded-lg */
}

.dark .btn-secondary {
  background-color: #334155; /* slate-700 */
  color: #ffffff;
}

</style>
