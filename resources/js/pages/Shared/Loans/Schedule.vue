<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
  loan: Object,
  repayments: Array,
  message: String
})

const page = usePage()
const user = computed(() => page.props.auth?.user || {})
const role = computed(() => user.value?.role || '')

const safeRepayments = computed(() => props.repayments ?? [])

const toNumber = (val: any) => {
  const n = Number(val)
  return isNaN(n) ? 0 : n
}

const formatNumber = (n: any) =>
  new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(toNumber(n))

const formatDate = (d: any) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-KE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const totals = computed(() => {
  let principal = 0
  let interest = 0
  let total = 0

  safeRepayments.value.forEach((r: any) => {
    principal += toNumber(r.principal_amount)
    interest += toNumber(r.interest_amount)
    total += toNumber(r.payment_amount)
  })

  return { principal, interest, total }
})

const backRoute = computed(() => {
  return role.value === 'admin'
    ? route('my-loans')
    : route('loans.index')
})
</script>

<template>
  <Head title="Loan Schedule" />

  <AppLayout :breadcrumbs="[
    { title: 'Loans', href: backRoute },
    { title: 'Loan Schedule' }
  ]">

    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6">

      <!-- HEADER -->
      <div class="max-w-7xl mx-auto mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-[#0a2342]">
            Loan Repayment Schedule
          </h1>
          <p class="text-sm text-gray-500">
            Detailed amortization breakdown
          </p>
        </div>

        <Link
          :href="backRoute"
          class="flex items-center gap-2 text-orange-600 hover:text-orange-500 font-medium"
        >
          <ArrowLeft class="w-4 h-4" />
          Back
        </Link>
      </div>

      <!-- LOAN SUMMARY -->
      <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow border border-gray-100 p-6 mb-8">
        <h2 class="text-lg font-semibold text-[#0a2342] mb-4">
          Loan Summary
        </h2>

        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
          <p><b>Loan No:</b> {{ loan?.loan_number }}</p>
          <p><b>Member:</b> {{ loan?.member?.first_name }} {{ loan?.member?.last_name }}</p>
          <p><b>Product:</b> {{ loan?.loanProduct?.name }}</p>
          <p><b>Principal:</b> KSh {{ formatNumber(loan?.approved_amount || loan?.disbursed_amount) }}</p>
          <p><b>Interest:</b> {{ loan?.interest_rate }}% p.m</p>
          <p><b>Term:</b> {{ loan?.term_months }} months</p>
        </div>
      </div>

      <!-- TABLE -->
      <div
        v-if="safeRepayments.length"
        class="max-w-7xl mx-auto bg-white rounded-2xl shadow border border-gray-100 overflow-hidden"
      >

        <div class="overflow-x-auto max-h-[650px] overflow-y-auto">

          <table class="min-w-full text-sm">

            <!-- HEAD -->
            <thead class="bg-[#0a2342] text-white sticky top-0 z-10">
              <tr>
                <th class="px-4 py-3 text-center">#</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-right">Opening</th>
                <th class="px-4 py-3 text-right">Principal</th>
                <th class="px-4 py-3 text-right">Interest</th>
                <th class="px-4 py-3 text-right">Installment</th>
                <th class="px-4 py-3 text-right">Balance</th>
                <th class="px-4 py-3 text-center">Status</th>
              </tr>
            </thead>

            <!-- BODY -->
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="row in safeRepayments"
                :key="row.payment_number"
                class="hover:bg-blue-50 transition"
              >
                <td class="px-4 py-3 text-center font-medium">
                  {{ row.payment_number }}
                </td>

                <td class="px-4 py-3">
                  {{ formatDate(row.payment_date || row.due_date) }}
                </td>

                <td class="px-4 py-3 text-right">
                  {{ formatNumber(row.opening_balance) }}
                </td>

                <td class="px-4 py-3 text-right">
                  {{ formatNumber(row.principal_amount) }}
                </td>

                <td class="px-4 py-3 text-right text-blue-700">
                  {{ formatNumber(row.interest_amount) }}
                </td>

                <td class="px-4 py-3 text-right font-semibold text-green-700">
                  {{ formatNumber(row.payment_amount || row.installment) }}
                </td>

                <td class="px-4 py-3 text-right">
                  {{ formatNumber(row.closing_balance) }}
                </td>

                <td class="px-4 py-3 text-center">
                  <span
                    :class="row.status === 'paid'
                      ? 'text-green-600 font-semibold'
                      : 'text-orange-500 font-semibold'"
                  >
                    {{ row.status || 'pending' }}
                  </span>
                </td>
              </tr>
            </tbody>

            <!-- FOOTER -->
            <tfoot class="bg-[#0a2342] text-white font-semibold sticky bottom-0">
              <tr>
                <td colspan="3" class="px-4 py-3 text-right">
                  TOTALS
                </td>

                <td class="px-4 py-3 text-right">
                  {{ formatNumber(totals.principal) }}
                </td>

                <td class="px-4 py-3 text-right">
                  {{ formatNumber(totals.interest) }}
                </td>

                <td class="px-4 py-3 text-right">
                  {{ formatNumber(totals.total) }}
                </td>

                <td colspan="2"></td>
              </tr>
            </tfoot>

          </table>
        </div>
      </div>

      <!-- EMPTY -->
      <div
        v-else
        class="max-w-7xl mx-auto bg-white text-center py-12 rounded-2xl shadow border"
      >
        <p class="text-gray-500">
          {{ message || 'No repayment schedule found' }}
        </p>
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