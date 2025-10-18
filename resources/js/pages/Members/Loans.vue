<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Link, usePage } from "@inertiajs/vue3"
import { ref, onMounted, computed } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'
import axios from "axios"
import { addMonths, isBefore } from "date-fns"
import { Plus } from "lucide-vue-next"

const props = defineProps<{
  member: any
  loans: any[]
}>()

// detect user role
const { props: pageProps } = usePage()
const isMemberRole = computed(() => pageProps.auth?.user?.role === 'member')

// === Eligibility ===
const isEligible = ref(true)
const reasons = ref<string[]>([])
const checking = ref(false)
const alertType = ref<"success" | "error" | "info" | null>(null)
const alertMessage = ref<string | null>(null)

const checkEligibility = async () => {
  checking.value = true
  alertType.value = null
  alertMessage.value = null
  try {
    const payload = {
      member_id: props.member.id,
      loan_product_id: props.loans[0]?.loan_product_id || 1,
      requested_amount: 1,
    }
    const response = await axios.post(
      route("members.loans.check-eligibility", props.member.id),
      payload
    )
    const data = response.data.data
    isEligible.value = data.eligible
    reasons.value = data.messages || []
    alertType.value = data.eligible ? "success" : "error"
    alertMessage.value = data.eligible
      ? "You are eligible for a new loan."
      : "You are currently not eligible for a loan."
  } catch (e) {
    console.error("Eligibility check failed:", e)
    isEligible.value = false
    reasons.value = ["Unable to verify eligibility at the moment."]
    alertType.value = "error"
    alertMessage.value = "Eligibility verification failed."
  } finally {
    checking.value = false
  }
}

onMounted(() => {
  if (isMemberRole.value) checkEligibility()
})

// === Helpers ===
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
      <div class="flex justify-between items-center border-b pb-4 border-gray-200">
        <div>
          <h1 class="text-xl font-semibold text-blue-900 tracking-tight">My Loans</h1>
          <p class="text-gray-600 text-sm mt-1">View, manage, and apply for new loans</p>
        </div>
        <div v-if="canApplyLoan && (!isMemberRole || isEligible)">
          <Link :href="route('loans.create', { member: props.member.id })">
            <Button class="bg-blue-800 hover:bg-blue-900 text-white px-5 py-2.5 rounded-xl shadow-sm transition-transform hover:scale-[1.02] flex items-center gap-2">
              <Plus class="w-5 h-5" />
              <span>Apply for Loan</span>
            </Button>
          </Link>
        </div>
      </div>

      <!-- Flash / Status Alerts -->
      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="alertType"
          :class="[
            'rounded-lg p-4 flex items-start gap-3 shadow-sm border',
            alertType === 'success' && 'bg-green-50 border-green-500 text-green-700',
            alertType === 'error' && 'bg-red-50 border-red-500 text-red-700',
            alertType === 'info' && 'bg-blue-50 border-blue-400 text-blue-700'
          ]"
        >
          <div class="flex-1">
            <p class="font-medium">{{ alertMessage }}</p>
            <ul v-if="!isEligible && reasons.length" class="list-disc list-inside text-sm mt-2 text-red-600">
              <li v-for="reason in reasons" :key="reason">{{ reason }}</li>
            </ul>
          </div>
        </div>
      </transition>

      <!-- Checking Eligibility Loader -->
      <div v-if="checking" class="flex items-center gap-2 text-gray-600 animate-pulse">
        <span class="loader w-4 h-4 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></span>
        Checking eligibility...
      </div>

      <!-- Summary Section -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <Card class="bg-white rounded-2xl border border-gray-200 hover:shadow-md transition">
          <CardHeader class="pb-2">
            <CardTitle class="text-gray-800 text-base font-semibold">Total Loans</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-xl font-semibold text-blue-900">{{ props.loans.length }}</p>
          </CardContent>
        </Card>

        <Card class="bg-white rounded-2xl border border-gray-200 hover:shadow-md transition">
          <CardHeader class="pb-2">
            <CardTitle class="text-gray-800 text-base font-semibold">Total Balance</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-xl font-medium text-blue-900">
              KES {{ formattedTotalAmount }}
            </p>
          </CardContent>
        </Card>

        <Card class="bg-white rounded-2xl border border-gray-200 hover:shadow-md transition">
          <CardHeader class="pb-2">
            <CardTitle class="text-gray-800 text-base font-semibold">Loan Status</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-base font-medium text-orange-600">
              {{ canApplyLoan ? "No Active Loan" : "Loan in progress" }}
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Loans Table -->
      <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm mt-6">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left">
            <thead class="bg-blue-50 text-blue-900 text-sm font-semibold tracking-wide border-b border-gray-200">
              <tr>
                <th class="px-6 py-3">Loan #</th>
                <th class="px-6 py-3">Product</th>
                <th class="px-6 py-3">Amount</th>
                <th class="px-6 py-3">Balance</th>
                <th class="px-6 py-3">Next Repayment</th>
                <th class="px-6 py-3">Status</th>
                <th class="px-6 py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="loan in props.loans"
                :key="loan.id"
                class="hover:bg-gray-50 transition-all duration-150"
              >
                <td class="px-6 py-4">{{ loan.loan_number }}</td>
                <td class="px-6 py-4">{{ loan.loan_product?.name }}</td>
                <td class="px-6 py-4 font-medium text-gray-700">
                  KES {{ Number(loan.applied_amount).toLocaleString() }}
                </td>
                <td class="px-6 py-4 font-medium text-gray-700">
                  KES {{ Number(loan.outstanding_balance).toLocaleString() }}
                </td>
                <td class="px-6 py-4">{{ formatDate(getNextRepaymentDate(loan)) }}</td>
                <td class="px-6 py-4">
                  <span
                    class="px-3 py-1 text-xs rounded-full font-medium capitalize"
                    :class="{
                      'text-green-700 bg-green-100': ['completed', 'approved'].includes(loan.status),
                      'text-orange-700 bg-orange-100': loan.status === 'active',
                      'text-yellow-700 bg-yellow-100': loan.status === 'pending',
                      'text-red-700 bg-red-100': loan.status === 'rejected',
                      'text-gray-700 bg-gray-100': loan.status === 'defaulted',
                      'text-blue-700 bg-blue-100': loan.status === 'disbursed'
                    }"
                  >
                    {{ loan.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <Link
                    :href="route('loans.show', loan.id)"
                    class="text-blue-700 hover:text-blue-900 font-medium hover:underline transition"
                  >
                    View
                  </Link>
                </td>
              </tr>

              <tr v-if="!props.loans.length">
                <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                  <div class="flex flex-col items-center space-y-2">
                    <p class="text-lg font-medium text-blue-900">No loans found</p>
                    <p class="text-sm text-gray-500">Once you apply for a loan, it will appear here.</p>
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
