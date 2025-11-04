<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { ArrowLeft } from 'lucide-vue-next'

// Props from backend
const props = defineProps({
  loan: Object,
  repayments: Array,
  message: String
})

// Access logged-in user from shared props
const page = usePage()
const user = computed(() => page.props.auth.user)
const role = computed(() => user.value?.role)
console.log(role.value)

// Safe number parser
const toNumber = (val: any) => {
  const num = parseFloat(val)
  return isNaN(num) ? 0 : num
}

// Format repayments safely
const formattedRepayments = computed(() => {
  return props.repayments.map((item: any) => ({
    ...item,
    due_date: item.due_date ? new Date(item.due_date).toLocaleDateString() : '—',
    principal_amount: toNumber(item.principal_amount).toLocaleString(),
    interest_amount: toNumber(item.interest_amount).toLocaleString(),
    total_amount: toNumber(item.total_amount).toLocaleString(),
    balance_after_payment: toNumber(item.balance_after_payment).toLocaleString(),
  }))
})

// Totals summary (safe)
const totals = computed(() => {
  let principal = 0
  let interest = 0
  let total = 0
  props.repayments.forEach((r: any) => {
    principal += toNumber(r.principal_amount)
    interest += toNumber(r.interest_amount)
    total += toNumber(r.total_amount)
  })
  return {
    principal: principal.toLocaleString(),
    interest: interest.toLocaleString(),
    total: total.toLocaleString(),
  }
})

// Dynamic back route based on user role
const backRoute = computed(() => {
  return role.value === 'admin'
    ? route('my-loans')
    : route('loans.index')
})
</script>

<template>
  <Head title="Loan Schedule" />

  <AppLayout :breadcrumbs="[{ title: 'Loan Schedule', href: backRoute }]">
    <div class="min-h-screen bg-gray-50 py-10 px-6">
      <!-- Header -->
      <div class="max-w-6xl mx-auto mb-8 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Loan Repayment Schedule</h1>
        <Link
          :href="backRoute"
          class="flex items-center gap-1 text-blue-600 hover:text-blue-500 font-medium transition-colors"
        >
          <ArrowLeft class="w-4 h-4" /> Back to Loans
        </Link>
      </div>

      <!-- Loan Details -->
      <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-md border border-gray-100 p-6 mb-10">
        <h2 class="text-lg font-semibold text-blue-700 mb-4">Loan Details</h2>
        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-y-3 text-sm text-gray-700">
          <p><span class="font-medium text-gray-900">Loan Number:</span> {{ loan.loan_number }}</p>
          <p><span class="font-medium text-gray-900">Member:</span> {{ loan.member?.name }}</p>
          <p><span class="font-medium text-gray-900">Loan Product:</span> {{ loan.loanProduct?.name }}</p>
          <p><span class="font-medium text-gray-900">Principal:</span> Ksh {{ loan.approved_amount.toLocaleString() }}</p>
          <p><span class="font-medium text-gray-900">Interest Rate:</span> {{ loan.interest_rate }}%</p>
          <p><span class="font-medium text-gray-900">Term:</span> {{ loan.term_months }} months</p>
        </div>
      </div>

      <!-- Repayment Schedule Table -->
      <div
        v-if="repayments.length"
        class="max-w-6xl mx-auto overflow-x-auto bg-white rounded-xl shadow-md border border-gray-100"
      >
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-blue-600 text-white uppercase text-xs">
            <tr>
              <th class="px-4 py-3 font-semibold">#</th>
              <th class="px-4 py-3 font-semibold">Due Date</th>
              <th class="px-4 py-3 font-semibold">Principal</th>
              <th class="px-4 py-3 font-semibold">Interest</th>
              <th class="px-4 py-3 font-semibold">Total</th>
              <th class="px-4 py-3 font-semibold">Balance</th>
              <th class="px-4 py-3 font-semibold">Status</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(item, index) in formattedRepayments"
              :key="index"
              class="border-t hover:bg-blue-50 transition-colors"
            >
              <td class="px-4 py-2">{{ item.installment_number }}</td>
              <td class="px-4 py-2">{{ item.due_date }}</td>
              <td class="px-4 py-2">Ksh {{ item.principal_amount }}</td>
              <td class="px-4 py-2">Ksh {{ item.interest_amount }}</td>
              <td class="px-4 py-2 font-medium text-blue-700">Ksh {{ item.total_amount }}</td>
              <td class="px-4 py-2">Ksh {{ item.balance_after_payment }}</td>
              <td class="px-4 py-2 capitalize">
                <span
                  :class="item.status === 'paid'
                    ? 'text-green-600 font-medium'
                    : 'text-orange-500 font-medium'"
                >
                  {{ item.status }}
                </span>
              </td>
            </tr>
          </tbody>

          <!-- Totals Row -->
          <tfoot class="bg-gray-100 border-t text-gray-800">
            <tr>
              <td colspan="2" class="px-4 py-3 font-semibold text-right">Totals:</td>
              <td class="px-4 py-3">Ksh {{ totals.principal }}</td>
              <td class="px-4 py-3">Ksh {{ totals.interest }}</td>
              <td class="px-4 py-3">Ksh {{ totals.total }}</td>
              <td colspan="2"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Empty message -->
      <div
        v-else
        class="max-w-6xl mx-auto bg-white text-center text-gray-600 py-10 rounded-xl border border-gray-100 shadow-sm"
      >
        {{ message }}
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
table th,
table td {
  white-space: nowrap;
}
</style>
