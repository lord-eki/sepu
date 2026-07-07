<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, Link, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import {
  Bell,
  Wallet,
  BadgeDollarSign,
  Landmark,
  TrendingUp,
  Receipt,
  Eye,
  EyeOff,
  ArrowUpRight,
  X,
} from 'lucide-vue-next'
import axios from 'axios'

interface Member {
  id: number
  first_name: string
  last_name: string
}

interface Stats {
  accounts: {
    share_deposits_balance: number
    share_capital_balance: number
  }
  loans: {
    active_loans: number
    total_outstanding: number
    next_payment_due: string | null
  }
}

interface Account {
  id: number
  account_type: 'share_deposits' | 'share_capital'
  balance: number
}

interface RecentTransaction {
  id: number
  transaction_type: string
  amount: number
  created_at: string
  account?: Account
}

interface LoanProduct {
  id: number
  name: string
}

interface Loan {
  id: number
  status: string
  outstanding_balance: number
  disbursed_amount?: number
  first_repayment_date?: string | null
  loanProduct?: LoanProduct
}

interface NotificationItem {
  id: number
  message: string
  created_at: string
}

const page = usePage()

const flash = computed(() => page.props.flash || {})

const showFlash = ref(false)
const flashType = ref<'success' | 'error'>('success')
const flashMessage = ref('')

watch(
  flash,
  (val) => {
    if (val?.success) {
      flashType.value = 'success'
      flashMessage.value = val.success
      showFlash.value = true

      setTimeout(() => {
        showFlash.value = false
      }, 5000)
    } else if (val?.error) {
      flashType.value = 'error'
      flashMessage.value = val.error
      showFlash.value = true

      setTimeout(() => {
        showFlash.value = false
      }, 5000)
    }
  },
  { immediate: true }
)

const props = defineProps<{
  member: Member
  stats: Stats
  accounts: Account[]
  recentTransactions: RecentTransaction[]
  activeLoans: Loan[]
  notifications: NotificationItem[]
}>()

const fmtMoney = (v: number) =>
  new Intl.NumberFormat(undefined, {
    style: 'currency',
    currency: 'KES',
    maximumFractionDigits: 0,
  }).format(v || 0)

const fmtDate = (d?: string | null) =>
  d ? new Date(d).toLocaleDateString() : '—'

const fullName = computed(
  () => `${props.member.first_name} ${props.member.last_name}`
)

const totalBalance = computed(() => {
  const deposits = Number(
    props.stats?.accounts?.share_deposits_balance || 0
  )

  const capital = Number(
    props.stats?.accounts?.share_capital_balance || 0
  )

  return deposits + capital
})

const showBalances = ref(true)

const toggleBalances = () => {
  showBalances.value = !showBalances.value
}

const showNotifications = ref(false)

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
}

const recentTx = computed(() =>
  props.recentTransactions.slice(0, 5)
)

const openNotification = (n: any) => {
  const data = n.metadata || n.data || {}

  axios.post(`/notifications/${n.id}/read`).catch(() => { })

  if (n.type === 'guarantor_request' && data.loan_id) {
    router.visit(`/guarantor-requests/${data.loan_id}`)
    return
  }

  if (data.loan_id) {
    router.visit(`/guarantor-requests/${data.loan_id}`)
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="[
    {
      title: 'Dashboard',
      href: '/dashboard',
    },
  ]">

    <Head title="Dashboard" />

    <div class="min-h-screen overflow-x-hidden bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-950 dark:via-slate-900
dark:to-slate-950 px-4 py-5 sm:px-6lg:px-8">
      <!-- HEADER -->
      <div
        class="relative max-sm:overflow-hidden rounded-[28px] bg-gradient-to-br from-[#0F172A] via-[#132F57] to-[#1E3A8A] p-5 sm:p-7 shadow-xl">
        <!-- glow -->
        <div
          class="absolute right-0 top-0 h-56 w-56 translate-x-1/4 -translate-y-1/4 rounded-full bg-blue-400/20 blur-3xl">
        </div>

        <div class="relative z-10">
          <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <!-- LEFT -->
            <div>
              <div
                class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-orange-500/90 px-3 py-1 backdrop-blur">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>

                <span class="text-xs text-slate-100">
                  SEPU SACCO
                </span>
              </div>

              <h1 class="mt-4 text-xl font-bold tracking-tight text-white sm:text-2xl">
                Welcome,
                <span class="text-blue-300">
                  {{ fullName }}
                </span>
              </h1>

              <p class="mt-2 text-sm text-slate-300">
                Here's your SACCO financial overview.
              </p>
            </div>

            <!-- RIGHT -->
            <div class="flex flex-wrap items-center gap-3">
              <!-- TOTAL BALANCE -->
              <div
                class="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/10 dark:bg-slate-900 px-4 py-3 backdrop-blur-xl">
                <div>
                  <p class="text-[11px] uppercase tracking-wider text-slate-300">
                    Total Balance
                  </p>

                  <div class="mt-1 flex items-center gap-2 text-white">
                    <h2 class="text-lg font-bold tracking-tight sm:text-xl">
                      {{
    showBalances
      ? fmtMoney(totalBalance)
      : '••••••••'
  }}
                    </h2>

                    <div class="hidden items-center gap-1 text-xs text-emerald-300 sm:flex">
                      <ArrowUpRight class="h-3.5 w-3.5" />
                      Active
                    </div>
                  </div>
                </div>
              </div>

              <!-- EYE -->
              <button @click="toggleBalances"
                class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/15 bg-white/10 dark:bg-slate-900 backdrop-blur transition hover:bg-white/20">
                <Eye v-if="showBalances" class="h-5 w-5 text-white" />

                <EyeOff v-else class="h-5 w-5 text-white" />
              </button>

              <!-- NOTIFICATIONS -->
              <div class="relative">
                <button @click="toggleNotifications"
                  class="relative flex h-11 w-11 items-center justify-center rounded-2xl border border-white/15 bg-white/10 dark:bg-slate-900 backdrop-blur transition hover:bg-white/20">
                  <Bell class="h-5 w-5 text-white" />

                  <span
                    class="absolute -right-1 -top-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-orange-500 px-1 text-[10px] text-white">
                    {{ notifications?.length ?? 0 }}
                  </span>
                </button>

                <!-- Notification Dropdown -->
                <div v-if="showNotifications" class="fixed top-20 left-1/2 -translate-x-1/2 sm:absolute sm:left-auto sm:translate-x-0 sm:right-0 sm:top-14 z-50 w-[95vw] max-w-md rounded-2xl
                  border
                  bg-white
                  dark:bg-slate-900`
                  dark:border-slate-700
                  shadow-2xl
                  ">
                  <!-- Backdrop -->
                  <div @click="showNotifications = false"></div>

                  <!-- Notification Box -->
                  <div
                    class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white dark:bg-slate-900 shadow-2xl">
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b p-4">
                      <h3 class="font-semibold text-slate-700">
                        Notifications
                      </h3>

                      <!-- Close -->
                      <button @click="showNotifications = false" class="rounded-full p-1 transition hover:bg-slate-100">
                        <X class="h-5 w-5 text-slate-500" />
                      </button>
                    </div>

                    <!-- Notifications -->
                    <div v-if="notifications?.length" class="max-h-[70vh] overflow-y-auto">
                      <div v-for="n in notifications" :key="n.id" @click="openNotification(n)"
                        class="cursor-pointer border-b p-4 transition hover:bg-slate-50 dark:bg-slate-800">
                        <p class="text-sm text-slate-700">
                          {{ n.message }}
                        </p>

                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                          {{ fmtDate(n.created_at) }}
                        </p>
                      </div>
                    </div>

                    <!-- Empty -->
                    <div v-else class="p-8 text-center text-sm text-slate-500">
                      No notifications
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- STATS -->
      <div class="mt-8 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        <Card v-for="stat in [
    {
      title: 'Share Deposits',
      value: fmtMoney(stats.accounts.share_deposits_balance || 0),
      icon: Wallet,
      color: 'from-blue-500 to-cyan-500',
    },
    {
      title: 'Share Capital',
      value: fmtMoney(stats.accounts.share_capital_balance || 0),
      icon: BadgeDollarSign,
      color: 'from-orange-500 to-amber-500',
    },
    {
      title: 'Active Loans',
      value: stats.loans.active_loans || 0,
      icon: TrendingUp,
      color: 'from-emerald-500 to-green-500',
    },
  ]" :key="stat.title"
          class="rounded-[28px] border-0bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-700 shadow-sm transition-all duration-300 hover:shadow-xl">
          <CardContent class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-slate-500">
                  {{ stat.title }}
                </p>

                <h3 class="mt-3 text-xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
                  <span v-if="showBalances">
                    {{ stat.value }}
                  </span>

                  <span v-else>
                    •••••••
                  </span>
                </h3>
              </div>

              <div :class="`bg-gradient-to-br ${stat.color}`"
                class="flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-lg">
                <component :is="stat.icon" class="h-6 w-6" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- TABS -->
      <section class="mt-10">
        <Tabs default-value="loans" class="w-full">
          <div class="overflow-x-auto">
            <TabsList class="h-auto rounded-2xl border border-slate-200 bg-white dark:bg-slate-900 p-1">
              <TabsTrigger value="loans"
                class="rounded-xl px-5 py-3 data-[state=active]:bg-blue-800 data-[state=active]:text-white">
                <Landmark class="mr-2 h-4 w-4" />
                Loans
              </TabsTrigger>

              <TabsTrigger value="transactions"
                class="rounded-xl px-5 py-3 data-[state=active]:bg-blue-900 data-[state=active]:text-white">
                <Receipt class="mr-2 h-4 w-4" />
                Transactions
              </TabsTrigger>
            </TabsList>
          </div>

          <!-- LOANS -->
          <TabsContent value="loans" class="mt-6">
            <div v-if="activeLoans?.length" class="grid gap-6 lg:grid-cols-2">
              <Card v-for="loan in activeLoans" :key="loan.id"
                class="rounded-[28px] border-0 bg-white dark:bg-slate-900 shadow-sm transition hover:shadow-xl">
                <CardContent class="p-6">
                  <div class="flex items-start justify-between">
                    <div>
                      <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        {{
    loan.loanProduct?.name ||
    'Loan Facility'
  }}
                      </h3>

                      <p class="mt-1 text-sm capitalize text-slate-500 dark:text-slate-400">
                        {{ loan.status }}
                      </p>
                    </div>

                    <Badge class="rounded-full bg-orange-100 px-4 py-1 text-orange-700">
                      {{
    fmtMoney(
      loan.outstanding_balance || 0
    )
  }}
                    </Badge>
                  </div>

                  <div class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                      <span class="text-slate-500 dark:text-slate-400">
                        Next Payment
                      </span>

                      <span class="font-medium text-slate-800">
                        {{
      fmtDate(
        loan.first_repayment_date
      )
    }}
                      </span>
                    </div>

                    <div class="flex justify-between">
                      <span class="text-slate-500 dark:text-slate-400">
                        Disbursed
                      </span>

                      <span class="font-medium text-slate-800">
                        {{
      fmtMoney(
        loan.disbursed_amount || 0
      )
    }}
                      </span>
                    </div>
                  </div>

                  <div class="mt-6 flex gap-3">
                    <Button as-child class="rounded-xl">
                      <Link :href="route('loans.show', loan.id)
    ">
                      View
                      </Link>
                    </Button>

                    <Button as-child variant="outline" class="rounded-xl">
                      <Link :href="route(
    'loans.repayments',
    loan.id
  )
    ">
                      Repay
                      </Link>
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>

            <!-- empty -->
            <div v-else
              class="rounded-[32px] border border-dashed border-slate-300  dark:bg-slate-900 p-12 text-center">
              <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100">
                <Landmark class="h-8 w-8 text-slate-400 dark:text-slate-500" />
              </div>

              <h3 class="mt-5 text-lg font-semibold text-slate-800">
                No Active Loans
              </h3>

              <p class="mx-auto max-sm:text-sm mt-2 max-w-md text-slate-500 dark:text-slate-400">
                You currently don’t have any active loans.
              </p>

              <Button as-child class="bg-blue-800 mt-6 rounded-xl">
                <Link :href="route('my-loans')">
                Apply for Loan
                </Link>
              </Button>
            </div>
          </TabsContent>

          <!-- TRANSACTIONS -->
          <TabsContent value="transactions" class="mt-6">
            <div class="overflow-hidden rounded-[30px] border border-slate-200 bg-white dark:bg-slate-900 shadow-sm">
              <div class="flex items-center justify-between border-b p-5">
                <h3 class="font-semibold text-slate-800">
                  Recent Transactions
                </h3>

                <Button as-child variant="outline" size="sm" class="rounded-xl">
                  <Link :href="route(
    'members.transactions',
    member.id
  )
    ">
                  View All
                  </Link>
                </Button>
              </div>

              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr class="text-slate-500 dark:text-slate-400">
                      <th class="p-4 text-left font-medium">
                        Date
                      </th>

                      <th class="p-4 text-left font-medium">
                        Type
                      </th>

                      <th class="p-4 text-left font-medium">
                        Account
                      </th>

                      <th class="p-4 text-right font-medium">
                        Amount
                      </th>

                      <th class="p-4 text-right font-medium">
                        Status
                      </th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr v-for="t in recentTx" :key="t.id"
                      class="border-t transition hover:bg-slate-50 dark:bg-slate-800">
                      <td class="p-4">
                        {{ fmtDate(t.created_at) }}
                      </td>

                      <td class="p-4 capitalize">
                        {{
    t.transaction_type.replace(
      '_',
      ' '
    )
                        }}
                      </td>

                      <td class="p-4 capitalize">
                        {{
                        t.account?.account_type ||
                        '—'
                        }}
                      </td>

                      <td class="p-4 text-right font-semibold text-slate-800">
                        {{ fmtMoney(t.amount) }}
                      </td>

                      <td class="p-4 text-right">
                        <Badge v-if="[
    'deposit',
      'loan_disbursement',
      'dividend_payment',
      'interest_payment',
      'share_capital_contribution',
    ].includes(
      t.transaction_type
    )
    " class="bg-emerald-100 text-emerald-700">
                          Credit
                        </Badge>

                        <Badge v-else class="bg-rose-100 text-rose-700">
                          Debit
                        </Badge>
                      </td>
                    </tr>

                    <tr v-if="!recentTx.length">
                      <td colspan="5" class="p-8 text-center  dark:text-slate-400 ">
                        No transactions found
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </TabsContent>
        </Tabs>
      </section>
    </div>
  </AppLayout>
</template>