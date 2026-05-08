<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import {
  Search,
  RefreshCw,
  HandCoins,
  Wallet,
  Landmark,
  ShieldCheck,
  ArrowRight,
  CheckCircle2,
  AlertTriangle,
  CalendarDays,
  Users,
  Banknote,
  ReceiptText,
} from 'lucide-vue-next'

interface LoanRow {
  id: number
  loan_number: string
  member_id: number
  membership_id: string
  member_name: string
  loan_product: string
  approved_amount: number
  disbursed_amount: number
  net_disbursement: number
  processing_fee: number
  insurance_fee: number
  approval_date: string
  approved_by: string | null
  term_months: number
}

interface Summary {
  total_loans: number
  total_approved: number
  total_net: number
  total_fees: number
}

interface Filters {
  date_from?: string
  date_to?: string
  member_id?: string
}

const props = defineProps<{
  loans: LoanRow[]
  summary: Summary
  filters: Filters
}>()

const search = ref('')
const selectedLoans = ref<number[]>([])
const showModal = ref(false)
const processing = ref(false)

const form = useForm({
  loan_ids: [] as number[],
  year: new Date().getFullYear(),
})

const filteredLoans = computed(() => {
  return props.loans.filter((loan) => {
    const term = search.value.toLowerCase()

    return (
      loan.member_name.toLowerCase().includes(term) ||
      loan.loan_number.toLowerCase().includes(term) ||
      loan.membership_id.toLowerCase().includes(term) ||
      loan.loan_product.toLowerCase().includes(term)
    )
  })
})

const selectedLoanData = computed(() => {
  return filteredLoans.value.filter((loan) =>
    selectedLoans.value.includes(loan.id),
  )
})

const allSelected = computed(() => {
  return (
    filteredLoans.value.length > 0 &&
    filteredLoans.value.every((loan) =>
      selectedLoans.value.includes(loan.id),
    )
  )
})

const totalSelectedApproved = computed(() => {
  return selectedLoanData.value.reduce(
    (sum, loan) => sum + loan.approved_amount,
    0,
  )
})

const totalSelectedNet = computed(() => {
  return selectedLoanData.value.reduce(
    (sum, loan) => sum + loan.net_disbursement,
    0,
  )
})

const totalSelectedFees = computed(() => {
  return selectedLoanData.value.reduce(
    (sum, loan) =>
      sum + loan.processing_fee + loan.insurance_fee,
    0,
  )
})

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedLoans.value = []
  } else {
    selectedLoans.value = filteredLoans.value.map(
      (loan) => loan.id,
    )
  }
}

const toggleLoan = (id: number) => {
  if (selectedLoans.value.includes(id)) {
    selectedLoans.value = selectedLoans.value.filter(
      (item) => item !== id,
    )
  } else {
    selectedLoans.value.push(id)
  }
}

const refreshPage = () => {
  router.reload()
}

const processDisbursements = () => {
  processing.value = true

  form.loan_ids = selectedLoans.value

  form.post('/schedule/loan-disbursement/run', {
    preserveScroll: true,
    onFinish: () => {
      processing.value = false
      showModal.value = false
    },
  })
}
</script>

<template>
  <AppLayout
    :breadcrumbs="[
      {
        title: 'Schedule Management',
        href: '/schedule',
      },
      {
        title: 'Loan Disbursement',
        href: '/schedule/loan-disbursement',
      },
    ]"
  >
    <Head title="Loan Disbursement Schedule" />

    <div
      class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-orange-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
    >
      
      <!-- HERO -->
      <section
        class="relative overflow-hidden rounded-2xl border-b border-slate-200 dark:border-slate-800"
      >
        <!-- Background Gradient Layer -->
        <div
          class="absolute inset-0 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
        ></div>

        <!-- Orange Glow Accent -->
        <div
          class="absolute -top-20 right-0 h-72 w-72 rounded-full bg-orange-500/20 blur-3xl"
        ></div>

        <div
          class="absolute -bottom-20 left-0 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"
        ></div>

        <div class="relative px-6 py-10 lg:px-10">
          <div
            class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
          >
            <!-- LEFT CONTENT -->
            <div>
              <h1
                class="text-3xl font-bold tracking-tight text-white lg:text-4xl"
              >
                Loan Disbursement Schedule
              </h1>

              <p
                class="mt-3 max-w-3xl text-base leading-7 text-slate-300"
              >
                Process approved SACCO loans, automate member disbursements,
                calculate fees, and activate loan accounts with precision and control.
              </p>
            </div>

            <!-- ACTIONS -->
            <div class="flex items-center gap-3">
              <button
                @click="refreshPage"
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-white backdrop-blur-md transition hover:bg-white/10"
              >
                <RefreshCw class="h-4 w-4" />
                Refresh
              </button>

              <button
                :disabled="selectedLoans.length === 0"
                @click="showModal = true"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.02] disabled:cursor-not-allowed disabled:opacity-40"
              >
                <HandCoins class="h-4 w-4" />
                Process Disbursements
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- SUMMARY -->
      <section class="px-6 py-8 lg:px-10">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
          <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Approved Loans
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                >
                  {{ summary.total_loans }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-blue-100 p-4 dark:bg-blue-500/10"
              >
                <Users
                  class="h-6 w-6 text-blue-600 dark:text-blue-400"
                />
              </div>
            </div>
          </div>

          <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Approved Amount
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                >
                  KES
                  {{ summary.total_approved.toLocaleString() }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-emerald-100 p-4 dark:bg-emerald-500/10"
              >
                <Wallet
                  class="h-6 w-6 text-emerald-600 dark:text-emerald-400"
                />
              </div>
            </div>
          </div>

          <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Net Disbursement
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-orange-600 dark:text-orange-400"
                >
                  KES
                  {{ summary.total_net.toLocaleString() }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-orange-100 p-4 dark:bg-orange-500/10"
              >
                <Landmark
                  class="h-6 w-6 text-orange-600 dark:text-orange-400"
                />
              </div>
            </div>
          </div>

          <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  Total Fees
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400"
                >
                  KES
                  {{ summary.total_fees.toLocaleString() }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-red-100 p-4 dark:bg-red-500/10"
              >
                <ReceiptText
                  class="h-6 w-6 text-red-600 dark:text-red-400"
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- TABLE -->
      <section class="px-6 pb-10 lg:px-10">
        <div
          class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
          <!-- HEADER -->
          <div
            class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
          >
            <div
              class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
              <div>
                <h2
                  class="text-xl font-bold text-slate-900 dark:text-white"
                >
                  Approved Loan Queue
                </h2>

                <p
                  class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                >
                  Select approved loans to disburse to member
                  accounts.
                </p>
              </div>

              <div class="relative">
                <Search
                  class="absolute left-3 top-3.5 h-4 w-4 text-slate-400"
                />

                <input
                  v-model="search"
                  type="text"
                  placeholder="Search loan/member..."
                  class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm shadow-sm outline-none focus:border-orange-500 dark:border-slate-700 dark:bg-slate-900"
                />
              </div>
            </div>
          </div>

          <!-- BULK -->
          <div
            class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40 lg:flex-row lg:items-center lg:justify-between"
          >
            <label class="flex items-center gap-3">
              <input
                :checked="allSelected"
                type="checkbox"
                class="h-5 w-5 rounded border-slate-300 text-orange-600"
                @change="toggleSelectAll"
              />

              <span
                class="text-sm font-medium text-slate-700 dark:text-slate-300"
              >
                Select All Loans
              </span>
            </label>

            <div
              class="flex flex-wrap items-center gap-4 text-sm font-medium"
            >
              <span class="text-slate-600 dark:text-slate-400">
                Selected:
                <strong>{{ selectedLoans.length }}</strong>
              </span>

              <span class="text-emerald-600 dark:text-emerald-400">
                Net:
                <strong>
                  KES
                  {{ totalSelectedNet.toLocaleString() }}
                </strong>
              </span>
            </div>
          </div>

          <!-- TABLE -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
              <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr>
                  <th class="px-6 py-4"></th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Member
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Loan Details
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Financials
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Approval
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Net Amount
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr
                  v-for="loan in filteredLoans"
                  :key="loan.id"
                  class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                >
                  <td class="px-6 py-5">
                    <input
                      :checked="selectedLoans.includes(loan.id)"
                      type="checkbox"
                      class="h-5 w-5 rounded border-slate-300 text-orange-600"
                      @change="toggleLoan(loan.id)"
                    />
                  </td>

                  <td class="px-6 py-5">
                    <div>
                      <div
                        class="font-semibold text-slate-900 dark:text-white"
                      >
                        {{ loan.member_name }}
                      </div>

                      <div
                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                      >
                        {{ loan.membership_id }}
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-5">
                    <div
                      class="font-semibold text-slate-900 dark:text-white"
                    >
                      {{ loan.loan_number }}
                    </div>

                    <div
                      class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                    >
                      {{ loan.loan_product }}
                    </div>

                    <div
                      class="mt-2 inline-flex rounded-full bg-blue-100 px-2 py-1 text-[10px] font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-400"
                    >
                      {{ loan.term_months }} Months
                    </div>
                  </td>

                  <td class="px-6 py-5">
                    <div class="space-y-1 text-xs">
                      <div
                        class="flex items-center justify-between gap-4"
                      >
                        <span class="text-slate-500">
                          Approved
                        </span>

                        <strong>
                          {{ loan.approved_amount.toLocaleString() }}
                        </strong>
                      </div>

                      <div
                        class="flex items-center justify-between gap-4"
                      >
                        <span class="text-slate-500">
                          Processing Fee
                        </span>

                        <strong class="text-red-500">
                          {{ loan.processing_fee.toLocaleString() }}
                        </strong>
                      </div>

                      <div
                        class="flex items-center justify-between gap-4"
                      >
                        <span class="text-slate-500">
                          Insurance Fee
                        </span>

                        <strong class="text-red-500">
                          {{ loan.insurance_fee.toLocaleString() }}
                        </strong>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-5">
                    <div
                      class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400"
                    >
                      <CheckCircle2 class="h-3.5 w-3.5" />

                      Approved
                    </div>

                    <div
                      class="mt-2 flex items-center gap-1 text-xs text-slate-500"
                    >
                      <CalendarDays class="h-3.5 w-3.5" />
                      {{ loan.approval_date }}
                    </div>

                    <div
                      class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                    >
                      {{ loan.approved_by || 'System' }}
                    </div>
                  </td>

                  <td class="px-6 py-5">
                    <div
                      class="text-lg font-bold text-orange-600 dark:text-orange-400"
                    >
                      KES
                      {{ loan.net_disbursement.toLocaleString() }}
                    </div>
                  </td>
                </tr>

                <!-- EMPTY -->
                <tr v-if="filteredLoans.length === 0">
                  <td colspan="6" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center">
                      <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"
                      >
                        <HandCoins
                          class="h-8 w-8 text-slate-400"
                        />
                      </div>

                      <h3
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                      >
                        No Approved Loans Found
                      </h3>

                      <p
                        class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                      >
                        No approved loan records available for
                        disbursement.
                      </p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- MODAL -->
      <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      >
        <div
          class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
        >
          <div
            class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-100 dark:bg-orange-500/10"
          >
            <ShieldCheck
              class="h-8 w-8 text-orange-600 dark:text-orange-400"
            />
          </div>

          <h2
            class="text-2xl font-bold text-slate-900 dark:text-white"
          >
            Confirm Loan Disbursement
          </h2>

          <p
            class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400"
          >
            Approved loans will be disbursed to member accounts
            and activated automatically.
          </p>

          <div
            class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800"
          >
            <div class="space-y-3 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-slate-500">
                  Selected Loans
                </span>

                <strong>{{ selectedLoans.length }}</strong>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-500">
                  Approved Amount
                </span>

                <strong>
                  KES
                  {{ totalSelectedApproved.toLocaleString() }}
                </strong>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-500">
                  Total Fees
                </span>

                <strong class="text-red-500">
                  KES
                  {{ totalSelectedFees.toLocaleString() }}
                </strong>
              </div>

              <div
                class="border-t border-slate-200 pt-3 dark:border-slate-700"
              >
                <div class="flex items-center justify-between">
                  <span
                    class="font-semibold text-slate-700 dark:text-slate-300"
                  >
                    Net Disbursement
                  </span>

                  <strong
                    class="text-lg text-emerald-600 dark:text-emerald-400"
                  >
                    KES
                    {{ totalSelectedNet.toLocaleString() }}
                  </strong>
                </div>
              </div>
            </div>
          </div>

          <!-- YEAR -->
          <div class="mt-5">
            <label
              class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
            >
              Processing Year
            </label>

            <input
              v-model="form.year"
              type="number"
              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-orange-500 dark:border-slate-700 dark:bg-slate-900"
            />
          </div>

          <div class="mt-8 flex items-center justify-end gap-3">
            <button
              @click="showModal = false"
              class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
            >
              Cancel
            </button>

            <button
              :disabled="processing"
              @click="processDisbursements"
              class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-red-500 px-5 py-3 text-sm font-semibold text-white shadow-lg disabled:opacity-50"
            >
              <RefreshCw
                v-if="processing"
                class="h-4 w-4 animate-spin"
              />

              <ArrowRight
                v-else
                class="h-4 w-4"
              />

              {{
                processing
                  ? 'Processing...'
                  : 'Confirm & Disburse'
              }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>