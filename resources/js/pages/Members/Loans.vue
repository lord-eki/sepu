<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Link } from "@inertiajs/vue3"
import { computed } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
  member: any
  loans: any[]
}>()

import { addMonths, isBefore } from "date-fns"

const getNextRepaymentDate = (loan: any) => {
  if (!loan.first_repayment_date || !loan.term_months) return null

  const start = new Date(loan.first_repayment_date)
  const today = new Date()

  let nextDate = start
  // keep adding 1 month until we reach a date after today
  while (isBefore(nextDate, today)) {
    nextDate = addMonths(nextDate, 1)
  }

  return nextDate
}

const formatDate = (date: Date | null) => {
  if (!date) return "N/A"
  return date.toLocaleDateString("en-KE", {
    year: "numeric",
    month: "short",
    day: "numeric",
  })
}

// Total amount
const totalAmount = computed(() =>
  props.loans.reduce((sum, loan) => sum + Number(loan.outstanding_balance), 0)
)
const formattedTotalAmount = computed(() =>
  Number(totalAmount.value).toLocaleString()
)

// Can apply for loan?
const canApplyLoan = computed(() => {
  if (!props.loans.length) return true
  return props.loans.every(
    (loan) => loan.status === "completed" || loan.status === "closed"
  )
})


</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Loans', href: '/my-loans' }]">
    <div class="space-y-10 p-6 min-h-screen">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-gray-200 pb-4">
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">Loans Overview</h1>
        <div v-if="canApplyLoan">
          <Link :href="route('loans.create', { member: props.member.id })">
            <Button
              class="bg-blue-700 hover:bg-blue-800 text-white px-5 py-2 rounded-lg shadow-sm transition">
              + Apply for Loan
            </Button>
          </Link>
        </div>
      </div>

      <!-- Summary Cards -->
      <div>
        <div v-if="!props.loans.length" class="grid grid-cols-1">
          <Card class="bg-gradient-to-br from-gray-50 to-gray-100 shadow-md rounded-xl">
            <CardHeader>
              <CardTitle class="text-gray-700">Status</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-base sm:text-lg font-medium text-blue-900">
                No active loans
              </p>
            </CardContent>
          </Card>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <Card class="bg-gradient-to-br from-gray-50 to-gray-100 shadow-md rounded-xl hover:shadow-lg transition">
            <CardHeader>
              <CardTitle class="text-gray-700">Total Loans</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-base sm:text-lg font-medium text-gray-900">{{ props.loans.length }}</p>
            </CardContent>
          </Card>

          <Card class="bg-gradient-to-br from-gray-50 to-gray-100 shadow-md rounded-xl border hover:shadow-lg transition">
            <CardHeader>
              <CardTitle class="text-gray-700">Total Balance</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-base sm:text-lg font-medium text-green-700">KES {{ formattedTotalAmount }}</p>
            </CardContent>
          </Card>

          <Card class="bg-gradient-to-br from-gray-50 to-gray-100 shadow-md rounded-xl border hover:shadow-lg transition">
            <CardHeader>
              <CardTitle class="text-gray-700">Status</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-base sm:text-lg font-medium  text-blue-800">
                {{ canApplyLoan ? "No Loan" : "Loan available" }}
              </p>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Loans Table -->
      <div class="bg-white border border-gray-200 shadow-md rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gradient-to-r from-blue-50 to-blue-100 text-xs uppercase font-semibold text-gray-700 sticky top-0">
              <tr>
                <th class="px-6 py-3">Loan #</th>
                <th class="px-6 py-3">Product</th>
                <th class="px-6 py-3">Amount</th>
                <th class="px-6 py-3">Balance</th>
                <th class="px-6 py-3">Next Repayment</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="loan in props.loans" :key="loan.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">{{ loan.loan_number }}</td>
                <td class="px-6 py-4">{{ loan.loan_product?.name }}</td>
                <td class="px-6 py-4 font-medium">KES {{ Number(loan.applied_amount).toLocaleString() }}</td>
                <td class="px-6 py-4 font-medium">KES {{ Number(loan.outstanding_balance).toLocaleString() }}</td>
                <td class="px-6 py-4 font-medium">
                  {{ formatDate(getNextRepaymentDate(loan)) }}
                </td>
                <td class="px-6 py-4">
                  <span :class="{
                    'text-green-600 font-semibold': loan.status === 'completed',
                    'text-green-700 font-semibold': loan.status === 'approved',
                    'text-blue-600 font-semibold': loan.status === 'active',
                    'text-yellow-600 font-semibold': loan.status === 'pending',
                    'text-red-600 font-semibold': loan.status === 'rejected',
                    'text-gray-600 font-semibold': loan.status === 'defauted',
                    'text-indigo-600 font-semibold': loan.status === 'disbursed'
                  }">
                    {{ loan.status }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <Link :href="route('loans.show', loan.id)"
                    class="text-blue-600 hover:text-blue-800 hover:underline transition">
                    View
                  </Link>
                </td>
              </tr>

              <tr v-if="!props.loans.length">
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                  <div class="flex flex-col items-center space-y-3">
                    <p class="text-base sm:text-lg">No loans found.</p>
                    <Link v-if="canApplyLoan" :href="route('loans.create', { member: props.member.id })">
                      <Button
                        class="bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md px-6 py-2 rounded-lg hover:shadow-lg">
                        Apply for Loan
                      </Button>
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
