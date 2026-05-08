# MonthlyDeposit.vue

```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import {
  Wallet,
  CalendarDays,
  Search,
  CheckCircle2,
  AlertTriangle,
  RefreshCw,
  Users,
  Landmark,
  ArrowRight,
  CircleDollarSign,
  ShieldCheck,
} from 'lucide-vue-next'

import { computed, ref } from 'vue'

interface Row {
  config_id: number
  member_id: number
  account_id: number
  membership_id: string
  member_name: string
  account_number: string
  account_type: string
  account_balance: number
  amount: number
  already_deposited_this_month: boolean
}

interface Summary {
  total_eligible: number
  already_done: number
  pending: number
  total_amount: number
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
const showConfirmModal = ref(false)

const form = useForm({
  month: props.month,
  entries: [] as {
    member_id: number
    account_id: number
    amount: number
  }[],
})

const filteredRows = computed(() => {
  return props.rows.filter((row) => {
    const matchesSearch =
      row.member_name
        .toLowerCase()
        .includes(search.value.toLowerCase()) ||
      row.membership_id
        .toLowerCase()
        .includes(search.value.toLowerCase()) ||
      row.account_number
        .toLowerCase()
        .includes(search.value.toLowerCase())

    return matchesSearch
  })
})

const selectableRows = computed(() => {
  return filteredRows.value.filter(
    (row) => !row.already_deposited_this_month,
  )
})

const selectedData = computed(() => {
  return selectableRows.value.filter((row) =>
    selectedRows.value.includes(row.member_id),
  )
})

const totalSelectedAmount = computed(() => {
  return selectedData.value.reduce((sum, row) => sum + row.amount, 0)
})

const allSelected = computed(() => {
  return (
    selectableRows.value.length > 0 &&
    selectableRows.value.every((row) =>
      selectedRows.value.includes(row.member_id),
    )
  )
})

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedRows.value = []
  } else {
    selectedRows.value = selectableRows.value.map((r) => r.member_id)
  }
}

const toggleRow = (memberId: number) => {
  if (selectedRows.value.includes(memberId)) {
    selectedRows.value = selectedRows.value.filter(
      (id) => id !== memberId,
    )
  } else {
    selectedRows.value.push(memberId)
  }
}

const submitSchedule = () => {
  processing.value = true

  form.entries = selectedData.value.map((row) => ({
    member_id: row.member_id,
    account_id: row.account_id,
    amount: row.amount,
  }))

  form.post('/schedule/monthly-deposit/run', {
    preserveScroll: true,
    onFinish: () => {
      processing.value = false
      showConfirmModal.value = false
    },
  })
}

const refreshPage = () => {
  router.reload()
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
        title: 'Monthly Deposits',
        href: '/schedule/monthly-deposit',
      },
    ]"
  >
    <Head title="Monthly Deposits" />

    <div
      class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
    >
      <!-- HERO -->
      <section class="relative overflow-hidden rounded-2xl border-b border-slate-200 dark:border-slate-800 bg-gradient-to-br from-blue-950 via-blue-900 to-orange-500">
        <div class="px-6 py-8 lg:px-10">
          <div
            class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
          >
            <div>
           <h1
                class="text-3xl font-bold tracking-tight text-white dark:text-white"
              >
                Monthly Deposit Schedule
              </h1>

              <p
                class="mt-3 max-w-3xl text-sm leading-7 text-orange-300 dark:text-slate-400"
              >
                Process recurring member monthly contributions, share deposits,
                and scheduled savings collections with automatic posting and
                audit tracking.
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
                @click="showConfirmModal = true"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.02] disabled:cursor-not-allowed disabled:opacity-50"
              >
                <CircleDollarSign class="h-4 w-4" />
                Run Schedule
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- ALERT -->
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
              Schedule Already Processed
            </h3>

            <p
              class="mt-1 text-sm text-amber-700 dark:text-amber-400"
            >
              Monthly deposits for this selected period have already been
              processed. Duplicate execution has been blocked for financial
              integrity.
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
                <p
                  class="text-sm font-medium text-slate-500 dark:text-slate-400"
                >
                  Total Eligible
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                >
                  {{ summary.total_eligible }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-blue-100 p-4 dark:bg-blue-500/10"
              >
                <Users class="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
            </div>
          </div>

          <div
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-center justify-between">
              <div>
                <p
                  class="text-sm font-medium text-slate-500 dark:text-slate-400"
                >
                  Pending Processing
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-orange-600 dark:text-orange-400"
                >
                  {{ summary.pending }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-orange-100 p-4 dark:bg-orange-500/10"
              >
                <CalendarDays
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
                <p
                  class="text-sm font-medium text-slate-500 dark:text-slate-400"
                >
                  Already Posted
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400"
                >
                  {{ summary.already_done }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-emerald-100 p-4 dark:bg-emerald-500/10"
              >
                <CheckCircle2
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
                <p
                  class="text-sm font-medium text-slate-500 dark:text-slate-400"
                >
                  Pending Amount
                </p>

                <h2
                  class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                >
                  KES {{ summary.total_amount.toLocaleString() }}
                </h2>
              </div>

              <div
                class="rounded-2xl bg-purple-100 p-4 dark:bg-purple-500/10"
              >
                <Landmark
                  class="h-6 w-6 text-purple-600 dark:text-purple-400"
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
                  Scheduled Member Contributions
                </h2>

                <p
                  class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                >
                  Review and process recurring member monthly deposits.
                </p>
              </div>

              <div class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="relative">
                  <Search
                    class="absolute left-3 top-3.5 h-4 w-4 text-slate-400"
                  />

                  <input
                    v-model="search"
                    type="text"
                    placeholder="Search member, membership ID..."
                    class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm shadow-sm outline-none transition focus:border-blue-500 dark:border-slate-700 dark:bg-slate-900"
                  />
                </div>

                <input
                  v-model="form.month"
                  type="month"
                  class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-blue-500 dark:border-slate-700 dark:bg-slate-900"
                />
              </div>
            </div>
          </div>

          <!-- BULK ACTION -->
          <div
            class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40 lg:flex-row lg:items-center lg:justify-between"
          >
            <div class="flex items-center gap-4">
              <label class="flex items-center gap-3">
                <input
                  :checked="allSelected"
                  type="checkbox"
                  class="h-5 w-5 rounded border-slate-300 text-blue-600"
                  @change="toggleSelectAll"
                />

                <span
                  class="text-sm font-medium text-slate-700 dark:text-slate-300"
                >
                  Select All Pending
                </span>
              </label>
            </div>

            <div
              class="flex flex-wrap items-center gap-4 text-sm font-medium"
            >
              <span class="text-slate-600 dark:text-slate-400">
                Selected:
                <strong>{{ selectedRows.length }}</strong>
              </span>

              <span class="text-emerald-600 dark:text-emerald-400">
                Total:
                <strong>
                  KES {{ totalSelectedAmount.toLocaleString() }}
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
                    Account
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Balance
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Contribution
                  </th>

                  <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    Status
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr
                  v-for="row in filteredRows"
                  :key="row.member_id"
                  class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                >
                  <td class="px-6 py-5">
                    <input
                      :disabled="row.already_deposited_this_month"
                      :checked="selectedRows.includes(row.member_id)"
                      type="checkbox"
                      class="h-5 w-5 rounded border-slate-300 text-blue-600 disabled:opacity-40"
                      @change="toggleRow(row.member_id)"
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
                        class="font-medium text-slate-800 dark:text-slate-200"
                      >
                        {{ row.account_number }}
                      </div>

                      <div
                        class="mt-1 text-xs capitalize text-slate-500 dark:text-slate-400"
                      >
                        {{ row.account_type.replace('_', ' ') }}
                      </div>
                    </div>
                  </td>

                  <td
                    class="px-6 py-5 text-sm font-semibold text-slate-700 dark:text-slate-300"
                  >
                    KES {{ row.account_balance.toLocaleString() }}
                  </td>

                  <td
                    class="px-6 py-5 text-sm font-bold text-emerald-600 dark:text-emerald-400"
                  >
                    KES {{ row.amount.toLocaleString() }}
                  </td>

                  <td class="px-6 py-5">
                    <div
                      v-if="row.already_deposited_this_month"
                      class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400"
                    >
                      <CheckCircle2 class="h-3.5 w-3.5" />
                      Already Posted
                    </div>

                    <div
                      v-else
                      class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-xs font-semibold text-orange-700 dark:border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-400"
                    >
                      <AlertTriangle class="h-3.5 w-3.5" />
                      Pending
                    </div>
                  </td>
                </tr>

                <!-- EMPTY -->
                <tr v-if="filteredRows.length === 0">
                  <td colspan="6" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center justify-center">
                      <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"
                      >
                        <Wallet class="h-8 w-8 text-slate-400" />
                      </div>

                      <h3
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                      >
                        No Records Found
                      </h3>

                      <p
                        class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                      >
                        No matching member contribution schedules found.
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
        v-if="showConfirmModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      >
        <div
          class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
        >
          <div
            class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-100 dark:bg-blue-500/10"
          >
            <ShieldCheck
              class="h-8 w-8 text-blue-600 dark:text-blue-400"
            />
          </div>

          <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
            Confirm Schedule Processing
          </h2>

          <p
            class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400"
          >
            You are about to process monthly member deposits for the selected
            financial period.
          </p>

          <div
            class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800"
          >
            <div class="space-y-3 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">
                  Selected Members
                </span>

                <strong class="text-slate-900 dark:text-white">
                  {{ selectedRows.length }}
                </strong>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">
                  Total Posting Amount
                </span>

                <strong class="text-emerald-600 dark:text-emerald-400">
                  KES {{ totalSelectedAmount.toLocaleString() }}
                </strong>
              </div>
            </div>
          </div>

          <div class="mt-8 flex items-center justify-end gap-3">
            <button
              @click="showConfirmModal = false"
              class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300"
            >
              Cancel
            </button>

            <button
              :disabled="processing"
              @click="submitSchedule"
              class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg disabled:opacity-50"
            >
              <RefreshCw
                v-if="processing"
                class="h-4 w-4 animate-spin"
              />

              <ArrowRight v-else class="h-4 w-4" />

              {{ processing ? 'Processing...' : 'Confirm & Process' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

