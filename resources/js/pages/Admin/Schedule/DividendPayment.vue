# DividendPayment.vue

```vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import {
  Search,
  RefreshCw,
  Wallet,
  Users,
  CircleDollarSign,
  CheckCircle2,
  AlertTriangle,
  ArrowRight,
  CalendarDays,
  Landmark,
  BadgeCheck,
  Ban,
  ShieldCheck,
  Gift,
} from 'lucide-vue-next'

interface Dividend {
  id: number
  dividend_year: number
  dividend_rate: number
  total_dividends: number
  status: string
  approval_date: string
}

interface MemberDividend {
  id: number
  member_id: number
  membership_id: string
  member_name: string
  shares_balance: number
  dividend_amount: number
  status: string
  payment_date: string | null
  eligible: boolean
  dividend_account_id: number | null
}

interface Summary {
  total_members: number
  pending_count: number
  pending_amount: number
  paid_count: number
  paid_amount: number
  ineligible: number
  dividend_rate: number
}

const props = defineProps<{
  dividend: Dividend | null
  memberDividends: MemberDividend[]
  summary: Summary | null
  year: number
  alreadyRun: boolean
  message?: string
}>()

const search = ref('')
const selectedRows = ref<number[]>([])
const showModal = ref(false)
const processing = ref(false)

const form = useForm({
  dividend_id: props.dividend?.id ?? null,
  year: props.year,
  entries: [] as any[],
})

const filteredRows = computed(() => {
  return props.memberDividends.filter((row) => {
    const term = search.value.toLowerCase()

    return (
      row.member_name.toLowerCase().includes(term) ||
      row.membership_id.toLowerCase().includes(term)
    )
  })
})

const selectableRows = computed(() => {
  return filteredRows.value.filter(
    (row) => row.status === 'pending' && row.eligible,
  )
})

const allSelected = computed(() => {
  return (
    selectableRows.value.length > 0 &&
    selectableRows.value.every((row) =>
      selectedRows.value.includes(row.id),
    )
  )
})

const selectedData = computed(() => {
  return filteredRows.value.filter((row) =>
    selectedRows.value.includes(row.id),
  )
})

const totalSelectedAmount = computed(() => {
  return selectedData.value.reduce(
    (sum, row) => sum + row.dividend_amount,
    0,
  )
})

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedRows.value = []
  } else {
    selectedRows.value = selectableRows.value.map(
      (row) => row.id,
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

const processPayments = () => {
  processing.value = true

  form.entries = selectedData.value.map((row) => ({
    member_dividend_id: row.id,
    member_id: row.member_id,
    account_id: row.dividend_account_id,
    dividend_amount: row.dividend_amount,
  }))

  form.post('/schedule/dividend-payment/run', {
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
        title: 'Dividend Payment',
        href: '/schedule/dividend-payment',
      },
    ]"
  >
    <Head title="Dividend Payment Schedule" />

    <div
      class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
    >
      <!-- HERO -->
      <section
        class="relative overflow-hidden rounded-2xl border-b border-slate-200 dark:border-slate-800"
      >
        <!-- Background -->
        <div
          class="absolute inset-0 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
        ></div>

        <!-- Glow Effects -->
        <div
          class="absolute -top-24 right-0 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"
        ></div>

        <div
          class="absolute -bottom-24 left-0 h-80 w-80 rounded-full bg-teal-500/10 blur-3xl"
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
                Dividend Payment Schedule
              </h1>

              <p
                class="mt-3 max-w-3xl text-base leading-7 text-slate-300"
              >
                Process approved annual dividends, credit member accounts
                automatically, and manage dividend distributions with accuracy
                and transparency.
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
                :disabled="selectedRows.length === 0 || alreadyRun"
                @click="showModal = true"
                class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-500 to-teal-500 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.02] disabled:cursor-not-allowed disabled:opacity-40"
              >
                <Wallet class="h-4 w-4" />
                Process Payments
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- NO DIVIDEND -->
      <section
        v-if="!dividend"
        class="px-6 py-10 lg:px-10"
      >
        <div
          class="rounded-3xl border border-amber-200 bg-amber-50 p-8 dark:border-amber-500/20 dark:bg-amber-500/10"
        >
          <div class="flex items-start gap-4">
            <div
              class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 dark:bg-amber-500/20"
            >
              <AlertTriangle class="h-7 w-7 text-amber-600" />
            </div>

            <div>
              <h2 class="text-xl font-bold text-amber-700 dark:text-amber-400">
                No Approved Dividend Found
              </h2>

              <p class="mt-2 text-sm text-amber-700/80 dark:text-amber-300">
                {{ message }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <template v-else>
        <!-- SUMMARY -->
        <section class="px-6 py-8 lg:px-10">
          <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div
              class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Total Members
                  </p>

                  <h2
                    class="mt-2 text-3xl font-bold text-slate-900 dark:text-white"
                  >
                    {{ summary?.total_members }}
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
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Pending Amount
                  </p>

                  <h2
                    class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400"
                  >
                    KES
                    {{ summary?.pending_amount.toLocaleString() }}
                  </h2>
                </div>

                <div
                  class="rounded-2xl bg-emerald-100 p-4 dark:bg-emerald-500/10"
                >
                  <CircleDollarSign
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
                    Paid Members
                  </p>

                  <h2
                    class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400"
                  >
                    {{ summary?.paid_count }}
                  </h2>
                </div>

                <div
                  class="rounded-2xl bg-blue-100 p-4 dark:bg-blue-500/10"
                >
                  <BadgeCheck
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
                    Dividend Rate
                  </p>

                  <h2
                    class="mt-2 text-3xl font-bold text-orange-600 dark:text-orange-400"
                  >
                    {{ summary?.dividend_rate }}%
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
          </div>
        </section>

        <!-- TABLE -->
        <section class="px-6 pb-10 lg:px-10">
          <div
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
          >
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
                    Member Dividend Distribution
                  </h2>

                  <p
                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                  >
                    Process eligible member dividend payouts.
                  </p>
                </div>

                <div class="relative">
                  <Search
                    class="absolute left-3 top-3.5 h-4 w-4 text-slate-400"
                  />

                  <input
                    v-model="search"
                    type="text"
                    placeholder="Search member..."
                    class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm shadow-sm outline-none focus:border-emerald-500 dark:border-slate-700 dark:bg-slate-900"
                  />
                </div>
              </div>
            </div>

            <div
              class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/40 lg:flex-row lg:items-center lg:justify-between"
            >
              <label class="flex items-center gap-3">
                <input
                  :checked="allSelected"
                  type="checkbox"
                  class="h-5 w-5 rounded border-slate-300 text-emerald-600"
                  @change="toggleSelectAll"
                />

                <span
                  class="text-sm font-medium text-slate-700 dark:text-slate-300"
                >
                  Select All Eligible Members
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
                  Total:
                  <strong>
                    KES
                    {{ totalSelectedAmount.toLocaleString() }}
                  </strong>
                </span>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                  <tr>
                    <th class="px-6 py-4"></th>

                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                      Member
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                      Shares
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                      Dividend
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                      Status
                    </th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                  <tr
                    v-for="row in filteredRows"
                    :key="row.id"
                    class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                  >
                    <td class="px-6 py-5">
                      <input
                        v-if="row.status === 'pending' && row.eligible"
                        :checked="selectedRows.includes(row.id)"
                        type="checkbox"
                        class="h-5 w-5 rounded border-slate-300 text-emerald-600"
                        @change="toggleRow(row.id)"
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
                      <div
                        class="font-semibold text-slate-900 dark:text-white"
                      >
                        KES {{ row.shares_balance.toLocaleString() }}
                      </div>
                    </td>

                    <td class="px-6 py-5">
                      <div
                        class="text-lg font-bold text-emerald-600 dark:text-emerald-400"
                      >
                        KES {{ row.dividend_amount.toLocaleString() }}
                      </div>
                    </td>

                    <td class="px-6 py-5">
                      <div
                        v-if="row.status === 'paid'"
                        class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:bg-blue-500/10 dark:text-blue-400"
                      >
                        <CheckCircle2 class="h-3.5 w-3.5" />
                        Paid
                      </div>

                      <div
                        v-else-if="!row.eligible"
                        class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1.5 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400"
                      >
                        <Ban class="h-3.5 w-3.5" />
                        Ineligible
                      </div>

                      <div
                        v-else
                        class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400"
                      >
                        <CalendarDays class="h-3.5 w-3.5" />
                        Pending
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </template>

      <!-- MODAL -->
      <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      >
        <div
          class="w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
        >
          <div
            class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 dark:bg-emerald-500/10"
          >
            <ShieldCheck
              class="h-8 w-8 text-emerald-600 dark:text-emerald-400"
            />
          </div>

          <h2
            class="text-2xl font-bold text-slate-900 dark:text-white"
          >
            Confirm Dividend Payment
          </h2>

          <p
            class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400"
          >
            Selected dividend payments will be credited directly to member accounts.
          </p>

          <div
            class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800"
          >
            <div class="space-y-3 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-slate-500">
                  Selected Members
                </span>

                <strong>{{ selectedRows.length }}</strong>
              </div>

              <div
                class="border-t border-slate-200 pt-3 dark:border-slate-700"
              >
                <div class="flex items-center justify-between">
                  <span
                    class="font-semibold text-slate-700 dark:text-slate-300"
                  >
                    Total Amount
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
              @click="processPayments"
              class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-3 text-sm font-semibold text-white shadow-lg disabled:opacity-50"
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
                  : 'Confirm Payments'
              }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
```
