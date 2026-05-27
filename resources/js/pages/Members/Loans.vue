<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Link, usePage, Head } from "@inertiajs/vue3"
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
    case 'pending_guarantor_approval':
      return 'Awaiting guarantor approval'
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
      return 'In process'
    default:
      return 'No active loan'
  }
})

</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Loans', href: '/my-loans' }]">
    <Head title="My loans" />

    <div
      class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 p-4 sm:p-6"
    >
      <!-- HERO -->
      <section
        class="relative overflow-hidden rounded-[32px] bg-gradient-to-br from-[#0F172A] via-[#132F57] to-[#1E3A8A] p-6 sm:p-8 shadow-2xl"
      >
        <!-- Glow -->
        <div
          class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-blue-400/20 blur-3xl"
        ></div>

        <div
          class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
        >
          <div>
            <div
              class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-orange-500/90 px-3 py-1 backdrop-blur"
            >
              <span
                class="h-2 w-2 rounded-full bg-emerald-400"
              ></span>

              <span class="text-xs text-white">
                SEPU SACCO
              </span>
            </div>

            <h1
              class="mt-4 text-2xl font-bold tracking-tight text-white"
            >
              My Loans
            </h1>

            <p class="mt-2 text-sm text-slate-300">
              Track, manage and monitor your loans easily.
            </p>
          </div>

          <div
            v-if="canApplyLoan && (!isMemberRole || isEligible)"
          >
            <Link
              :href="route('loans.create', { member: props.member.id })"
            >
              <Button
                class="h-12 rounded-2xl bg-orange-500 px-6 text-white shadow-lg transition-all hover:scale-[1.02] hover:bg-orange-600"
              >
                <Plus class="mr-2 h-5 w-5" />
                Apply for Loan
              </Button>
            </Link>
          </div>
        </div>
      </section>

      <!-- ALERT -->
      <transition
        enter-active-class="transition duration-300"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="alertType"
          :class="[
            'mt-6 rounded-2xl border p-5 shadow-sm',
            alertType === 'success' &&
              'border-emerald-200 bg-emerald-50 text-emerald-700',
            alertType === 'error' &&
              'border-rose-200 bg-rose-50 text-rose-700',
            alertType === 'info' &&
              'border-blue-200 bg-blue-50 text-blue-700',
          ]"
        >
          <p class="font-semibold">
            {{ alertMessage }}
          </p>

          <ul
            v-if="!isEligible && reasons.length"
            class="mt-3 list-disc space-y-1 pl-5 text-sm"
          >
            <li
              v-for="reason in reasons"
              :key="reason"
            >
              {{ reason }}
            </li>
          </ul>

          <p
            v-else-if="isEligible"
            class="mt-2 text-sm opacity-80"
          >
            NB: Only applicable if you have no active unpaid loans.
          </p>
        </div>
      </transition>

      <!-- LOADING -->
      <div
        v-if="checking"
        class="mt-5 flex items-center gap-3 text-slate-600"
      >
        <span
          class="h-5 w-5 animate-spin rounded-full border-2 border-orange-400 border-t-transparent"
        ></span>

        Checking eligibility...
      </div>

      <!-- STATS -->
      <section
        class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3"
      >
        <!-- CARD -->
        <Card
          class="rounded-[28px] border-0 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl"
        >
          <CardContent class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-slate-500">
                  Active Loans
                </p>

                <h3
                  class="mt-3 text-2xl font-bold text-slate-900"
                >
                  {{ activeLoans.length }}
                </h3>
              </div>

              <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 text-white shadow-lg"
              >
                <Plus class="h-6 w-6" />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- CARD -->
        <Card
          class="rounded-[28px] border-0 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl"
        >
          <CardContent class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-slate-500">
                  Total Balance Due
                </p>

                <h3
                  class="mt-3 text-2xl font-bold text-slate-900"
                >
                  KES {{ formattedTotalAmount }}
                </h3>
              </div>

              <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 text-white shadow-lg"
              >
                <span class="font-bold">Ksh</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- CARD -->
        <Card
          class="rounded-[28px] border-0 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl"
        >
          <CardContent class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-slate-500">
                  Current Status
                </p>

                <h3
                  class="mt-3 text-sm font-semibold capitalize"
                  :class="{
                    'text-green-700':
                      currentLoanStatus === 'completed' ||
                      currentLoanStatus === 'approved',
                    'text-orange-600':
                      currentLoanStatus === 'active',
                    'text-blue-600':
                      currentLoanStatus === 'disbursed',
                    'text-yellow-600':
                      currentLoanStatus === 'pending' ||
                      currentLoanStatus === 'under_review',
                    'text-red-600':
                      currentLoanStatus === 'rejected' ||
                      currentLoanStatus === 'defaulted',
                    'text-slate-500': !currentLoanStatus,
                  }"
                >
                  {{ currentLoanMessage }}
                </h3>
              </div>

              <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-green-500 text-white shadow-lg"
              >
                ⚙️
              </div>
            </div>
          </CardContent>
        </Card>
      </section>

      <!-- LOANS -->
      <section
        class="mt-10 overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-sm"
      >
        <!-- HEADER -->
        <div
          class="flex items-center justify-between border-b p-6"
        >
          <div>
            <h2
              class="text-lg font-semibold text-slate-900"
            >
              Loan History
            </h2>

            <p class="mt-1 text-sm text-slate-500">
              All your loan applications and repayments.
            </p>
          </div>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
              <tr class="text-slate-500">
                <th class="px-6 py-4 text-left font-medium">
                  Loan #
                </th>

                <th class="px-6 py-4 text-left font-medium">
                  Product
                </th>

                <th class="px-6 py-4 text-left font-medium">
                  Amount
                </th>

                <th class="px-6 py-4 text-left font-medium">
                  Balance
                </th>

                <th class="px-6 py-4 text-left font-medium">
                  Next Repayment
                </th>

                <th class="px-6 py-4 text-left font-medium">
                  Status
                </th>

                <th class="px-6 py-4 text-center font-medium">
                  Actions
                </th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="loan in props.loans"
                :key="loan.id"
                class="border-t transition hover:bg-slate-50"
              >
                <td class="px-6 py-5 font-medium text-slate-800">
                  {{ loan.loan_number }}
                </td>

                <td class="px-6 py-5">
                  {{ loan.loan_product?.name }}
                </td>

                <td class="px-6 py-5 font-semibold">
                  KES
                  {{
                    Number(
                      loan.approved_amount ||
                        loan.applied_amount
                    ).toLocaleString()
                  }}
                </td>

                <td class="px-6 py-5 font-semibold">
                  KES
                  {{
                    ['active', 'disbursed'].includes(
                      loan.status
                    )
                      ? Number(
                          loan.outstanding_balance
                        ).toLocaleString()
                      : '0'
                  }}
                </td>

                <td class="px-6 py-5">
                  {{
                    formatDate(
                      getNextRepaymentDate(loan)
                    )
                  }}
                </td>

                <td class="px-6 py-5">
                  <span
                    class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                    :class="{
                      'bg-green-100 text-green-700':
                        ['completed', 'approved'].includes(
                          loan.status
                        ),
                      'bg-orange-100 text-orange-700':
                        ['active', 'under_review'].includes(
                          loan.status
                        ),
                      'bg-yellow-100 text-yellow-700':
                        loan.status === 'pending',
                      'bg-red-100 text-red-700':
                        loan.status === 'rejected',
                      'bg-slate-100 text-slate-700':
                        loan.status === 'defaulted',
                      'bg-blue-100 text-blue-700':
                        loan.status === 'disbursed',
                    }"
                  >
                    {{ loan.status }}
                  </span>
                </td>

                <td class="px-6 py-5 text-center">
                  <Link
                    :href="route('loans.show', loan.id)"
                    class="inline-flex items-center rounded-xl bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-600 transition hover:bg-orange-100"
                  >
                    View
                  </Link>
                </td>
              </tr>

              <!-- EMPTY -->
              <tr v-if="!props.loans.length">
                <td
                  colspan="7"
                  class="px-6 py-16 text-center"
                >
                  <div
                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100"
                  >
                    <Plus
                      class="h-8 w-8 text-slate-400"
                    />
                  </div>

                  <h3
                    class="mt-5 text-lg font-semibold text-slate-800"
                  >
                    No Loans Found
                  </h3>

                  <p
                    class="mx-auto mt-2 max-w-md text-sm text-slate-500"
                  >
                    Once you apply for a loan, it will appear here.
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AppLayout>
</template>