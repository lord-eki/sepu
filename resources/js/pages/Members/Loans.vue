<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Link, usePage } from "@inertiajs/vue3"
import { ref, onMounted, computed } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'
import axios from "axios"
import { addMonths, isBefore } from "date-fns"

const props = defineProps<{
  member: any
  loans: any[]
}>()

// page auth (to detect if user is a member)
const { props: pageProps } = usePage()
const isMemberRole = computed(() => pageProps.auth?.user?.role === 'member')

// === Eligibility ===
const isEligible = ref(true)
const reasons = ref<string[]>([])
const checking = ref(false)

const checkEligibility = async () => {
  checking.value = true
  try {
    const payload = {
    member_id: props.member.id, 
      loan_product_id: props.loans[0]?.loan_product_id || 1, // fallback
      requested_amount: 1, // just to trigger eligibility
    }
    const response = await axios.post(
      route("members.loans.check-eligibility", props.member.id),
      payload
    )
    const data = response.data.data
    isEligible.value = data.eligible
    reasons.value = data.messages || []
  } catch (e) {
    console.error("Eligibility check failed:", e)
    isEligible.value = false
    reasons.value = ["Unable to verify eligibility at the moment."]
  } finally {
    checking.value = false
  }
}

onMounted(() => {
  if (isMemberRole.value) checkEligibility()
})

// === Existing Computed Properties ===
const getNextRepaymentDate = (loan: any) => {
  if (!loan.first_repayment_date || !loan.term_months) return null
  let nextDate = new Date(loan.first_repayment_date)
  const today = new Date()
  while (isBefore(nextDate, today)) nextDate = addMonths(nextDate, 1)
  return nextDate
}

const formatDate = (date: Date | null) =>
  date ? date.toLocaleDateString("en-KE", { year: "numeric", month: "short", day: "numeric" }) : "N/A"

const totalAmount = computed(() => props.loans.reduce((sum, loan) => sum + Number(loan.outstanding_balance), 0))
const formattedTotalAmount = computed(() => Number(totalAmount.value).toLocaleString())
const canApplyLoan = computed(() => !props.loans.length || props.loans.every(l => ['completed', 'rejected'].includes(l.status)))
</script>


<template>
  <AppLayout :breadcrumbs="[{ title: 'Loans', href: '/my-loans' }]">
    <div class="space-y-8 p-6 min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="flex justify-between items-start sm:items-center gap-3 pb-4">
        <div>
          <h1 class="text-lg sm:text-xl font-bold text-blue-900">
            My Loans
          </h1>
          <div class="h-1 bg-gradient-to-r from-blue-800 to-orange-500 rounded-full mt-2"></div>
        </div>
        <div v-if="canApplyLoan && (!isMemberRole || isEligible)">
          <Link :href="route('loans.create', { member: props.member.id })">
            <Button
              class="bg-blue-800 hover:bg-blue-900 hover:cursor-pointer text-white px-5 py-2 rounded-lg shadow-md transition">
              <span>+ New Loan<span class="max-sm:hidden">&nbsp;Application</span></span>
            </Button>
          </Link>
        </div>

      </div>

      <!-- Summary Cards -->
      <div>
        <div v-if="!props.loans.length" class="grid grid-cols-1">
          <Card class="bg-white shadow-md rounded-xl border-t-4 border-blue-800">
            <CardHeader>
              <CardTitle class="text-blue-900">Status</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="font-medium text-orange-500">
                No active loans
              </p>
            </CardContent>
          </Card>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <Card class="bg-white shadow-md rounded-xl border-t-4 border-blue-800 hover:shadow-lg transition">
            <CardHeader>
              <CardTitle class="text-blue-900">Total Loans</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-lg font-semibold text-blue-800">{{ props.loans.length }}</p>
            </CardContent>
          </Card>

          <Card class="bg-white shadow-md rounded-xl border-t-4 border-orange-500 hover:shadow-lg transition">
            <CardHeader>
              <CardTitle class="text-blue-900">Total Balance</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-lg font-medium text-blue-900">
                KES {{ formattedTotalAmount }}
              </p>
            </CardContent>
          </Card>

          <Card class="bg-white shadow-md rounded-xl border-t-4 border-blue-600 hover:shadow-lg transition">
            <CardHeader>
              <CardTitle class="text-blue-900">Status</CardTitle>
            </CardHeader>
            <CardContent>
              <p class="font-medium text-orange-500">
                {{ canApplyLoan ? "No Loan" : "Loan available" }}
              </p>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Eligibility Notice -->
      <div v-if="isMemberRole && !isEligible && !checking" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
        <p class="text-red-700 font-semibold mb-1">
          You are currently not eligible for a loan.
        </p>
        <ul class="list-disc list-inside text-red-600 text-sm">
          <li v-for="reason in reasons" :key="reason">{{ reason }}</li>
        </ul>
      </div>

      <!-- Loading -->
      <div v-if="checking" class="text-gray-500 text-sm italic">
        Checking eligibility...
      </div>


      <!-- Loans Table -->
      <div class="bg-white border border-gray-200 shadow-md rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left">
            <thead class="bg-blue-100 text-blue-900 font-semibold sticky top-0">
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
                <td class="px-6 py-4 font-medium">
                  KES {{ Number(loan.applied_amount).toLocaleString() }}
                </td>
                <td class="px-6 py-4 font-medium">
                  KES {{ Number(loan.outstanding_balance).toLocaleString() }}
                </td>
                <td class="px-6 py-4 font-medium">
                  {{ formatDate(getNextRepaymentDate(loan)) }}
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs rounded-full" :class="{
    'text-green-600 bg-green-100': loan.status === 'completed',
    'text-green-700 bg-green-100': loan.status === 'approved',
    'text-blue-700 bg-blue-100': loan.status === 'active',
    'text-yellow-600 bg-yellow-100': loan.status === 'pending',
    'text-red-600 bg-red-100': loan.status === 'rejected',
    'text-gray-600 bg-gray-100': loan.status === 'defauted',
    'text-orange-600 bg-orange-100': loan.status === 'disbursed'
  }">
                    {{ loan.status }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <Link :href="route('loans.show', loan.id)"
                    class="text-orange-600 hover:text-orange-700 hover:underline transition">
                  View
                  </Link>
                </td>
              </tr>

              <tr v-if="!props.loans.length">
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                  <div class="flex flex-col items-center space-y-3">
                    <p class="text-base text-blue-900">No loans found.</p>
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
