<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import {
  Search,
  RefreshCw,
  AlertTriangle,
  CheckCircle2,
  Wallet,
  Landmark,
  CalendarDays,
  ShieldCheck,
  ArrowRight,
  Clock3,
  TrendingUp,
  CircleDollarSign,
} from 'lucide-vue-next'

interface Row {
  repayment_id: number
  due_date: string
  loan_id: number
  loan_number: string
  member_id: number
  membership_id: string
  member_name: string
  loan_product: string
  principal_amount: number
  interest_amount: number
  penalty_amount: number
  expected_amount: number
  paid_amount: number
  outstanding_amount: number
  deduct_amount: number
  override_set: boolean
  outstanding_balance: number
  status: 'pending' | 'partial' | 'overdue' | 'paid'
  days_late: number
}

interface Summary {
  total_repayments: number
  total_expected: number
  total_deductable: number
  total_outstanding: number
  total_principal: number
  total_interest: number
  overdue_count: number
}

const props = defineProps<{
  rows: Row[]
  summary: Summary
  month: string
  alreadyRun: boolean
}>()

const search = ref('')
const selectedRows = ref<number[]>([])
const processing = ref(false)
const showModal = ref(false)

const form = useForm({
  month: props.month,
  entries: [] as {
    repayment_id: number
    loan_id: number
    member_id: number
    deduct_amount: number
  }[],
})

const filteredRows = computed(() => {
  return props.rows.filter((row) => {
    const searchTerm = search.value.toLowerCase()

    return (
      row.member_name.toLowerCase().includes(searchTerm) ||
      row.membership_id.toLowerCase().includes(searchTerm) ||
      row.loan_number.toLowerCase().includes(searchTerm)
    )
  })
})

const selectableRows = computed(() => {
  return filteredRows.value.filter(
    (row) => row.status !== 'paid',
  )
})

const selectedData = computed(() => {
  return selectableRows.value.filter((row) =>
    selectedRows.value.includes(row.repayment_id),
  )
})

const totalSelectedAmount = computed(() => {
  return selectedData.value.reduce(
    (sum, row) => sum + row.deduct_amount,
    0,
  )
})

const totalSelectedPrincipal = computed(() => {
  return selectedData.value.reduce(
    (sum, row) => sum + row.principal_amount,
    0,
  )
})

const totalSelectedInterest = computed(() => {
  return selectedData.value.reduce(
    (sum, row) => sum + row.interest_amount,
    0,
  )
})

const totalSelectedPenalty = computed(() => {
  return selectedData.value.reduce(
    (sum, row) => sum + row.penalty_amount,
    0,
  )
})

const allSelected = computed(() => {
  return (
    selectableRows.value.length > 0 &&
    selectableRows.value.every((row) =>
      selectedRows.value.includes(row.repayment_id),
    )
  )
})

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedRows.value = []
  } else {
    selectedRows.value = selectableRows.value.map(
      (r) => r.repayment_id,
    )
  }
}

const toggleRow = (id: number) => {
  if (selectedRows.value.includes(id)) {
    selectedRows.value = selectedRows.value.filter(
      (item) => item !== id,
    )
  } else {
    selectedRows.value.push(id)
  }
}

const refreshPage = () => {
  router.reload()
}

const processRepayments = () => {
  processing.value = true

  form.entries = selectedData.value.map((row) => ({
    repayment_id: row.repayment_id,
    loan_id: row.loan_id,
    member_id: row.member_id,
    deduct_amount: row.deduct_amount,
  }))

  form.post('/schedule/loan-repayment/run', {
    preserveScroll: true,
    onFinish: () => {
      processing.value = false
      showModal.value = false
    },
  })
}

const statusClasses = (status: string) => {
  switch (status) {
    case 'paid':
      return 'bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400'

    case 'partial':
      return 'bg-orange-100 text-orange-700 border border-orange-200 dark:bg-orange-500/10 dark:text-orange-400'

    case 'overdue':
      return 'bg-red-100 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400'

    default:
      return 'bg-blue-100 text-blue-700 border border-blue-200 dark:bg-blue-500/10 dark:text-blue-400'
  }
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
        title: 'Loan Repayments',
        href: '/schedule/loan-repayment',
      },
    ]"
  >
    <Head title="Loan Repayments" />

    <div
      class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
    >
      <!-- HERO -->
      <section class="relative overflow-hidden border-b border-slate-200 dark:border-slate-800 rounded-2xl bg-gradient-to-br from-blue-950 via-blue-900 to-orange-500">
        <div class="px-6 py-8 lg:px-10">
          <div
            class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
          >
            <div>
             <h1
                class="text-3xl font-bold tracking-tight text-white dark:text-white"
              >
                Loan Repayment Schedule
              </h1>

              <p
                class="mt-3 max-w-3xl text-sm leading-7 text-orange-300 dark:text-slate-400"
              >
                Process automatic loan deductions, overdue
                recoveries, principal reductions, and scheduled
                repayment collections.
              </p>
            </div>

            <div class="flex items-center gap-3">
              <button
                @click="refreshPage"
                class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
              >
                <RefreshCw class="h-4 w-4" />
                Refresh
              </button>

              <button
                :disabled="selectedRows.length === 0 || alreadyRun"
                @click="showModal = true"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-orange-500 to-red-500 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.02] disabled:cursor-not-allowed disabled:opacity-50"
              >
                <CircleDollarSign class="h-4 w-4" />
                Process Repayments
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- WARNING -->
      <section
        v-if="alreadyRun"
        class="px-6 pt-6 lg:px-10"
      >
        <div
          class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/20 dark:bg-amber-500/10"
        >
          <AlertTriangle
            class="mt-0.5 h-5 w-5 text-amber-600 dark:text-amber-400"
          />

          <div>
            <h3
              class="font-semibold text-amber-800 dark:text-amber-300"
            >
              Schedule Already Executed
            </h3>

            <p
              class="mt-1 text-sm text-amber-700 dark:text-amber-400"
            >
              Loan repayment schedule for this month has already
              been processed.
            </p>
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
                  Total Repayments
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                >
                  {{ summary.total_repayments }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-blue-100 p-4 dark:bg-blue-500/10"
              >
                <Wallet
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
                  Deductable Amount
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400"
                >
                  KES
                  {{ summary.total_deductable.toLocaleString() }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-emerald-100 p-4 dark:bg-emerald-500/10"
              >
                <TrendingUp
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
                  Outstanding Balance
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-orange-600 dark:text-orange-400"
                >
                  KES
                  {{ summary.total_outstanding.toLocaleString() }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-orange-100 p-4 dark:bg-orange-500/10"
              >
                <Clock3
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
                  Overdue Loans
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400"
                >
                  {{ summary.overdue_count }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-red-100 p-4 dark:bg-red-500/10"
              >
                <AlertTriangle
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
          <!-- TOP -->
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
                  Scheduled Loan Recoveries
                </h2>

                <p
                  class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                >
                  Process due loan repayments and automatic
                  deductions.
                </p>
              </div>

              <div class="flex flex-col gap-3 md:flex-row">
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

                <input
                  v-model="form.month"
                  type="month"
                  class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-orange-500 dark:border-slate-700 dark:bg-slate-900"
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
                Select All Pending
              </span>
            </label>

            <div
              class="flex flex-wrap items-center gap-4 text-sm font-medium"
            >
              <span class="text-slate-600 dark:text-slate-400">
                Selected:
                <strong>{{ selectedRows.length }}</strong>
              </span>

              <span class="text-emerald-600 dark:text-emerald-400">
                KES
                <strong>
                  {{ totalSelectedAmount.toLocaleString() }}
                </strong>
              </span>
            </div>
          </div>

          <!-- DATA TABLE -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
              <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr>
                  <th class="px-6 py-4"></th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Member
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Loan
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Due Date
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Breakdown
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Deduction
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Status
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr
                  v-for="row in filteredRows"
                  :key="row.repayment_id"
                  class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                >
                  <td class="px-6 py-5">
                    <input
                      :checked="selectedRows.includes(row.repayment_id)"
                      :disabled="row.status === 'paid'"
                      type="checkbox"
                      class="h-5 w-5 rounded border-slate-300 text-orange-600 disabled:opacity-40"
                      @change="toggleRow(row.repayment_id)"
                    />
                  </td>

                  <td class="px-6 py-5">
                    <div>
                      <div
                        class="font-semibold text-slate-900 dark:text-white"
                      >
                        {{ row.member_name }}
                      </div>

                      <div
                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                      >
                        {{ row.membership_id }}
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-5">
                    <div>
                      <div
                        class="font-semibold text-slate-900 dark:text-white"
                      >
                        {{ row.loan_number }}
                      </div>

                      <div
                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                      >
                        {{ row.loan_product }}
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-5">
                    <div
                      class="text-sm font-medium text-slate-700 dark:text-slate-300"
                    >
                      {{ row.due_date }}
                    </div>

                    <div
                      v-if="row.days_late > 0"
                      class="mt-1 text-xs text-red-500"
                    >
                      {{ row.days_late }} days late
                    </div>
                  </td>

                  <td class="px-6 py-5">
                    <div class="space-y-1 text-xs">
                      <div
                        class="flex items-center justify-between gap-4"
                      >
                        <span class="text-slate-500">Principal</span>

                        <strong>
                          {{ row.principal_amount.toLocaleString() }}
                        </strong>
                      </div>

                      <div
                        class="flex items-center justify-between gap-4"
                      >
                        <span class="text-slate-500">Interest</span>

                        <strong>
                          {{ row.interest_amount.toLocaleString() }}
                        </strong>
                      </div>

                      <div
                        class="flex items-center justify-between gap-4"
                      >
                        <span class="text-slate-500">Penalty</span>

                        <strong class="text-red-500">
                          {{ row.penalty_amount.toLocaleString() }}
                        </strong>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-5">
                    <div
                      class="text-sm font-bold text-emerald-600 dark:text-emerald-400"
                    >
                      KES
                      {{ row.deduct_amount.toLocaleString() }}
                    </div>

                    <div
                      class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                    >
                      Outstanding:
                      {{ row.outstanding_balance.toLocaleString() }}
                    </div>
                  </td>

                  <td class="px-6 py-5">
                    <div
                      :class="statusClasses(row.status)"
                      class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold"
                    >
                      <CheckCircle2
                        v-if="row.status === 'paid'"
                        class="h-3.5 w-3.5"
                      />

                      <AlertTriangle
                        v-else-if="row.status === 'overdue'"
                        class="h-3.5 w-3.5"
                      />

                      <Clock3
                        v-else
                        class="h-3.5 w-3.5"
                      />

                      {{ row.status }}
                    </div>

                    <div
                      v-if="row.override_set"
                      class="mt-2 inline-flex rounded-full bg-purple-100 px-2 py-1 text-[10px] font-semibold text-purple-700 dark:bg-purple-500/10 dark:text-purple-400"
                    >
                      Auto Deduction
                    </div>
                  </td>
                </tr>

                <!-- EMPTY -->
                <tr v-if="filteredRows.length === 0">
                  <td colspan="7" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center">
                      <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"
                      >
                        <Landmark
                          class="h-8 w-8 text-slate-400"
                        />
                      </div>

                      <h3
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                      >
                        No Repayment Records Found
                      </h3>

                      <p
                        class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                      >
                        No matching loan repayment schedules found.
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
            Confirm Loan Repayment Processing
          </h2>

          <p
            class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400"
          >
            You are about to process scheduled loan deductions and
            repayments for selected members.
          </p>

          <div
            class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800"
          >
            <div class="space-y-3 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-slate-500">
                  Selected Repayments
                </span>

                <strong>{{ selectedRows.length }}</strong>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-500">
                  Principal Recovery
                </span>

                <strong>
                  KES
                  {{ totalSelectedPrincipal.toLocaleString() }}
                </strong>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-500">
                  Interest Recovery
                </span>

                <strong>
                  KES
                  {{ totalSelectedInterest.toLocaleString() }}
                </strong>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-500">
                  Penalty Recovery
                </span>

                <strong class="text-red-500">
                  KES
                  {{ totalSelectedPenalty.toLocaleString() }}
                </strong>
              </div>

              <div
                class="border-t border-slate-200 pt-3 dark:border-slate-700"
              >
                <div class="flex items-center justify-between">
                  <span
                    class="font-semibold text-slate-700 dark:text-slate-300"
                  >
                    Total Deduction
                  </span>

                  <strong
                    class="text-lg text-emerald-600 dark:text-emerald-400"
                  >
                    KES
                    {{ totalSelectedAmount.toLocaleString() }}
                  </strong>
                </div>
              </div>
            </div>
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
              @click="processRepayments"
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
                  : 'Confirm & Process'
              }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>