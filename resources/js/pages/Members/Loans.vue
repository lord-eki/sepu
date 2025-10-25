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

const totalAmount = computed(() =>
  props.loans
    .filter(l => ['active', 'disbursed'].includes(l.status))
    .reduce((sum, loan) => sum + Number(loan.outstanding_balance || 0), 0)
)

const activeLoans = computed(() =>
  props.loans.filter(l => ['active', 'disbursed'].includes(l.status))
)

const formattedTotalAmount = computed(() => Number(totalAmount.value).toLocaleString())
const canApplyLoan = computed(() => !props.loans.length || props.loans.every(l => ['completed', 'rejected'].includes(l.status)))

// Determine most recent or relevant loan status
const currentLoanStatus = computed(() => {
  if (!props.loans.length) return null
  // Prioritize the most recent loan (by created_at or id)
  const latestLoan = [...props.loans].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0]
  return latestLoan.status
})

const currentLoanMessage = computed(() => {
  switch (currentLoanStatus.value) {
    case 'pending':
      return 'Pending Loan'
    case 'approved':
      return 'Loan approved'
    case 'disbursed':
      return 'Loan disbursed'
    case 'active':
      return 'Active loan'
    case 'completed':
      return 'Loan repaid'
    case 'rejected':
      return 'Loan rejected'
    case 'defaulted':
      return 'Loan defaulted'
    case 'under_review':
      return 'in process'
    default:
      return 'No active loan'
  }
})

</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Loans', href: '/my-loans' }]">
    <div class="min-h-screen bg-[#f9fafb] p-6 space-y-10">

      <!-- HEADER -->
      <header class="bg-gradient-to-r from-[#0B2B40] to-[#123A5A] text-white p-6 rounded-2xl shadow-md flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">My Loans</h1>
          <p class="text-sm text-blue-100">Track, manage, and apply for SEPU SACCO loans</p>
        </div>

        <div v-if="canApplyLoan && (!isMemberRole || isEligible)" class="mt-4 sm:mt-0">
          <Link :href="route('loans.create', { member: props.member.id })">
            <Button class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-xl shadow-md flex items-center gap-2 transition-transform hover:scale-[1.02]">
              <Plus class="w-5 h-5" />
              <span>Apply for Loan</span>
            </Button>
          </Link>
        </div>
      </header>

      <!-- ELIGIBILITY ALERT -->
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
            'rounded-lg p-4 flex items-start gap-3 border-l-4',
            alertType === 'success' && 'bg-green-50 border-green-500 text-green-700',
            alertType === 'error' && 'bg-red-50 border-red-500 text-red-700',
            alertType === 'info' && 'bg-blue-50 border-blue-400 text-blue-700'
          ]"
        >
          <div class="flex-1">
            <p class="font-semibold">{{ alertMessage }}</p>

            <!-- Show reasons if not eligible -->
            <ul v-if="!isEligible && reasons.length" class="list-disc list-inside text-sm mt-2 text-red-600">
              <li v-for="reason in reasons" :key="reason">{{ reason }}</li>
            </ul>

            <!-- Show note if eligible -->
            <p v-else-if="isEligible" class="text-sm mt-2 text-gray-600 italic">
              NB: Only applicable if you have no active unpaid loans.
            </p>
          </div>
        </div>
      </transition>

      <div v-if="checking" class="flex items-center gap-2 text-gray-600 animate-pulse">
        <span class="w-4 h-4 border-2 border-orange-400 border-t-transparent rounded-full animate-spin"></span>
        Checking eligibility...
      </div>


      <!-- SUMMARY CARDS -->
      <section class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <Card class="bg-white rounded-2xl border border-gray-200 hover:shadow-md transition">
          <CardHeader class="pb-2 flex items-center justify-between">
            <CardTitle class="text-gray-800 text-base font-semibold">Total Active Loans</CardTitle>
            <div class="p-2 bg-orange-100 text-orange-600 rounded-xl">
              <Plus class="h-5 w-5" />
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-xl font-bold text-[#0B2B40]">{{ activeLoans.length }}</p>
          </CardContent>
        </Card>

        <Card class="bg-white rounded-2xl border border-gray-200 hover:shadow-md transition">
          <CardHeader class="pb-2 flex items-center justify-between">
            <CardTitle class="text-gray-800 text-base font-semibold">Total Balance Due</CardTitle>
            <div class="p-2 bg-blue-50 text-[#0B2B40] rounded-xl">
              <span class="font-semibold">Ksh</span>
            </div>
          </CardHeader>
          <CardContent>
            <p class="text-xl font-bold text-[#0B2B40]">KES {{ formattedTotalAmount }}</p>
          </CardContent>
        </Card>

        <Card class="bg-white rounded-2xl border border-gray-200 hover:shadow-md transition">
          <CardHeader class="pb-2 flex items-center justify-between">
            <CardTitle class="text-gray-800 text-base font-semibold">Current Loan Status</CardTitle>
            <div class="p-2 bg-orange-100 text-orange-600 rounded-xl">
              <span class="font-bold">⚙️</span>
            </div>
          </CardHeader>
          <CardContent>
            <p
              class="text-base font-medium capitalize"
              :class="{
                'text-green-700': currentLoanStatus === 'completed' || currentLoanStatus === 'approved',
                'text-orange-600': currentLoanStatus === 'active',
                'text-blue-600': currentLoanStatus === 'disbursed',
                'text-yellow-600': currentLoanStatus === 'pending' || currentLoanStatus === 'under_review',
                'text-red-600': currentLoanStatus === 'rejected' || currentLoanStatus === 'defaulted',
                'text-slate-500': !currentLoanStatus
              }"
            >
              {{ currentLoanMessage }}
            </p>

          </CardContent>
        </Card>
      </section>

      <!-- LOANS TABLE -->
      <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left">
            <thead class="bg-[#0B2B40] text-white uppercase text-xs tracking-wide">
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
              <tr v-for="loan in props.loans" :key="loan.id" class="hover:bg-orange-50 transition">
                <td class="px-6 py-4">{{ loan.loan_number }}</td>
                <td class="px-6 py-4">{{ loan.loan_product?.name }}</td>
                <td class="px-6 py-4 font-medium text-gray-700">
                  KES {{ Number(loan.applied_amount).toLocaleString() }}
                </td>
                <td class="px-6 py-4 font-medium text-gray-700">
                  KES {{
                    ['active', 'disbursed'].includes(loan.status)
                      ? Number(loan.outstanding_balance).toLocaleString()
                      : '0'
                  }}
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
                      'text-orange-700 bg-orange-100': loan.status === 'under_review',
                      'text-blue-700 bg-blue-100': loan.status === 'disbursed'
                    }"
                  >
                    {{ loan.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <Link
                    :href="route('loans.show', loan.id)"
                    class="text-orange-600 hover:text-orange-700 font-semibold hover:underline transition"
                  >
                    View
                  </Link>
                </td>
              </tr>

              <tr v-if="!props.loans.length">
                <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                  <div class="flex flex-col items-center space-y-2">
                    <p class="text-lg font-semibold text-[#0B2B40]">No loans found</p>
                    <p class="text-sm text-gray-500">Once you apply for a loan, it will appear here.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
