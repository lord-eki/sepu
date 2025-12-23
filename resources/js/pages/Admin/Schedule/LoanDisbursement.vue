<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Loan Disbursement Schedule" />

    <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            Loan Disbursement Schedule
          </h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">
            Approved loans pending disbursement
          </p>
        </div>

        <a
          :href="route('schedule.loan-disbursement.export')"
          class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600
                 text-white px-4 py-2 rounded-lg shadow"
        >
          Export CSV
        </a>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <SummaryCard label="Total Loans" :value="summary.total_loans" />
        <SummaryCard label="Approved Amount" :value="currency(summary.total_amount)" />
        <SummaryCard label="Total Fees" :value="currency(summary.total_fees)" />
        <SummaryCard label="Net Disbursement" :value="currency(summary.total_net_disbursement)" />
      </div>

      <!-- Filters -->
      <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <Input label="From Date" type="date" v-model="filters.date_from" />
          <Input label="To Date" type="date" v-model="filters.date_to" />
          <Input label="Member ID" v-model="filters.member_id" />
          <Input label="Loan Product ID" v-model="filters.loan_product_id" />

          <div class="flex items-end gap-2">
            <button class="btn-secondary" type="button" @click="clearFilters">Clear</button>
            <button class="btn-primary ml-auto">Apply</button>
          </div>
        </form>
      </div>

      <!-- Weekly Schedule -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border dark:border-slate-700 p-4">
        <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">
          Weekly Planning
        </h3>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-slate-100 dark:bg-slate-700">
              <tr>
                <th class="th">Week</th>
                <th class="th">Period</th>
                <th class="th">Loans</th>
                <th class="th">Total Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="week in weeklySchedule" :key="week.week" class="tr">
                <td>{{ week.week }} / {{ week.year }}</td>
                <td>{{ week.start_date }} → {{ week.end_date }}</td>
                <td>{{ week.count }}</td>
                <td>{{ currency(week.total_amount) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Loans Table -->
      <div class="bg-white dark:bg-slate-800 rounded-xl border dark:border-slate-700 overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-100 dark:bg-slate-700">
            <tr>
              <th class="th">Loan #</th>
              <th class="th">Member</th>
              <th class="th">Product</th>
              <th class="th">Approved</th>
              <th class="th">Net</th>
              <th class="th">Approval Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="loan in loans.data" :key="loan.id" class="tr">
              <td>{{ loan.loan_number }}</td>
              <td>
                {{ loan.member.first_name }} {{ loan.member.last_name }}<br />
                <span class="text-xs text-slate-500">
                  {{ loan.member.membership_id }}
                </span>
              </td>
              <td>{{ loan.loan_product.name }}</td>
              <td>{{ currency(loan.approved_amount) }}</td>
              <td>
                {{ currency(loan.approved_amount - loan.processing_fee - loan.insurance_fee) }}
              </td>
              <td>{{ formatDate(loan.approval_date) }}</td>
            </tr>
          </tbody>
        </table>

        <Pagination :data="loans" />
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

const props = defineProps({
  loans: Object,
  summary: Object,
  weeklySchedule: Array,
  filters: Object,
})

const breadcrumbs = [
  { title: 'Schedule', href: '#' },
  { title: 'Loan Disbursement' }
]

const filters = reactive({ ...props.filters })

const applyFilters = () => {
  router.get(route('schedule.loan-disbursement'), filters, {
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

const formatDate = (d) =>
  d ? new Date(d).toLocaleDateString() : ''
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

/* Primary button */
.btn-primary {
  background-color: #0f172a;    /* slate-900 */
  color: #ffffff;
  padding: 0.5rem 1rem;         /* py-2 px-4 */
  border-radius: 0.5rem;        /* rounded-lg */
}

.dark .btn-primary {
  background-color: #f97316;    /* orange-500 */
}

/* Secondary button */
.btn-secondary {
  background-color: #e5e7eb;    /* slate-200 */
  color: #0f172a;               /* slate-900 */
  padding: 0.5rem 1rem;         /* py-2 px-4 */
  border-radius: 0.5rem;        /* rounded-lg */
}

.dark .btn-secondary {
  background-color: #334155;    /* slate-700 */
  color: #ffffff;
}

</style>
