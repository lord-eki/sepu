<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, Link, usePage } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Input } from '@/components/ui/input'
import { Bell, Wallet, Handshake, BadgeDollarSign, Landmark, TrendingUp, Receipt } from 'lucide-vue-next'
import axios from 'axios';


interface Member { id: number; first_name: string; last_name: string }
interface Stats {
  accounts: { share_deposits_balance: number; share_capital_balance: number }
  loans: { active_loans: number; total_outstanding: number; next_payment_due: string | null }
}
interface Account { id: number; account_type: 'share_deposits' | 'share_capital'; balance: number }
interface RecentTransaction { id: number; transaction_type: string; amount: number; created_at: string; account?: Account }
interface LoanProduct { id: number; name: string }
interface Loan { id: number; status: string; outstanding_balance: number; disbursed_amount?: number; first_repayment_date?: string | null; loanProduct?: LoanProduct }
interface NotificationItem { id: number; message: string; created_at: string }

const page = usePage()
const flash = computed(() => page.props.flash || {})
const showFlash = ref(false)
const flashType = ref<'success' | 'error'>('success')
const flashMessage = ref('')

watch(flash, (val) => {
  if (val?.success) {
    flashType.value = 'success'
    flashMessage.value = val.success
    showFlash.value = true
    setTimeout(() => (showFlash.value = false), 5000)
  } else if (val?.error) {
    flashType.value = 'error'
    flashMessage.value = val.error
    showFlash.value = true
    setTimeout(() => (showFlash.value = false), 5000)
  }
}, { immediate: true })

const props = defineProps<{
  member: Member
  stats: Stats
  accounts: Account[]
  recentTransactions: RecentTransaction[]
  activeLoans: Loan[]
  notifications: NotificationItem[]
}>()

const fmtMoney = (v: number) =>
  new Intl.NumberFormat(undefined, { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(v || 0)
const fmtDate = (d?: string | null) => (d ? new Date(d).toLocaleDateString() : '—')
const fullName = computed(() => `${props.member.first_name} ${props.member.last_name}`)
const totals = computed(() => {
  const sd = Number(props.stats.accounts.share_deposits_balance) || 0
  const sc = Number(props.stats.accounts.share_capital_balance) || 0
  return { balances: sd + sc }
})

const txFilter = ref('')
const filteredTx = computed(() => {
  const q = txFilter.value.trim().toLowerCase()
  if (!q) return props.recentTransactions
  return props.recentTransactions.filter(t =>
    t.transaction_type.toLowerCase().includes(q) || String(t.amount).includes(q)
  )
})

const recentTx = computed(() => props.recentTransactions.slice(0, 5))

const showBalances = ref(false)
const toggleBalances = () => (showBalances.value = !showBalances.value)

const showNotifications = ref(false)
const toggleNotifications = () => (showNotifications.value = !showNotifications.value)

const openNotification = (n: any) => {
    const data = n.metadata || n.data || {}

    // mark notification as read
    if (typeof axios !== 'undefined') {
        axios.post(`/notifications/${n.id}/read`).catch(() => {})
    }

    // open guarantor request
    if (n.type === 'guarantor_request' && data.loan_id) {
        router.visit(`/guarantor-requests/${data.loan_id}`)
        return
    }

    // generic fallback
    if (data.loan_id) {
        router.visit(`/guarantor-requests/${data.loan_id}`)
    }
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">

    <Head title="Dashboard" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 p-6 space-y-10 overflow-x-hidden">

      <!-- ========== EXECUTIVE HEADER ========== -->
      <div class="relative rounded-2xl bg-gradient-to-r from-[#102F55] via-blue-900 to-orange-900 p-6 shadow-2xl">

        <!-- subtle glow -->
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-orange-500/20 rounded-full blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row lg:flex-wrap lg:items-center justify-between gap-6 text-white">

          <div>
            <h1 class="text-3xl font-bold tracking-tight">
              SEPU <span class="text-orange-400">SACCO</span>
            </h1>
            <p class="text-slate-200 mt-1 text-sm">
              Welcome back, <span class="font-medium">{{ fullName }}</span>
            </p>
            <p class="text-slate-300 hidden text-xs mt-1">
              Your consolidated financial overview
            </p>
          </div>

          <div class="flex items-center gap-3 flex-shrink-0">
            <Button size="sm" class="bg-white/10 backdrop-blur border border-white/20 hover:bg-white/20 text-white"
              @click="toggleBalances">
              {{ showBalances ? 'Hide Balances' : 'Show Balances' }}
            </Button>

            <div class="relative">
              <button @click="toggleNotifications"
                class="relative p-2.5 rounded-xl bg-white/10 hover:bg-white/20 backdrop-blur border border-white/20 transition">
                <Bell class="h-4 w-4 text-white" />

                <span
                  class="absolute -top-1 -right-1 bg-orange-500 text-white text-[10px] rounded-full px-1 min-w-[16px]">
                  {{ notifications?.length ?? 0 }}
                </span>
              </button>

              <!-- Notification Dropdown -->
              <div v-if="showNotifications" class="
                  absolute mt-3 z-50
                  w-[90vw] max-w-sm
                  left-1/2 -translate-x-1/2
                  sm:left-auto sm:translate-x-0 sm:right-0 sm:w-80
                  bg-white rounded-2xl shadow-2xl border border-slate-200
                ">
                <div class="p-4 border-b font-semibold text-slate-700">
                  Notifications
                </div>

                <div v-if="notifications?.length">
                  <div
                        v-for="n in notifications"
                        :key="n.id"
                        class="p-4 border-b hover:bg-slate-50 text-sm cursor-pointer"
                        @click="openNotification(n)"
                      >
                    <p class="text-slate-700">{{ n.message }}</p>
                    <p class="text-xs text-slate-400 mt-1">
                      {{ fmtDate(n.created_at) }}
                    </p>
                  </div>
                </div>

                <div v-else class="p-6 text-center text-sm text-slate-500">
                  No notifications
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ========== KPI SECTION ========== -->
      <section class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <Card v-for="stat in [
    { title: 'Share Deposits', value: fmtMoney(stats.accounts.share_deposits_balance), icon: Wallet },
    { title: 'Share Capital', value: fmtMoney(stats.accounts.share_capital_balance), icon: BadgeDollarSign },
    { title: 'Active Loans', value: stats.loans.active_loans, icon: TrendingUp }
  ]" :key="stat.title"
          class="group bg-white/70 backdrop-blur-xl border border-slate-200 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-500 max-w-full break-words">
          <CardHeader class="flex justify-between items-center">
            <CardTitle class="text-sm font-medium text-slate-600">
              {{ stat.title }}
            </CardTitle>

            <div
              class="p-2 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-500 text-white shadow-lg group-hover:scale-110 transition">
              <component :is="stat.icon" class="h-5 w-5" />
            </div>
          </CardHeader>

          <CardContent>
            <div class="text-xl sm:text-2xl font-semibold tracking-tight text-slate-800">
              <span v-if="showBalances">{{ stat.value }}</span>
              <span v-else class="text-slate-300">•••••••</span>
            </div>
          </CardContent>
        </Card>
      </section>

      <!-- ========== TABS ========== -->
      <section>
        <Tabs default-value="loans" class="w-full">

          <TabsList class="bg-blue-100 p-1 rounded-xl max-w-full overflow-x-auto flex gap-2">
            <TabsTrigger value="loans"
              class="data-[state=active]:bg-white data-[state=active]:shadow-md rounded-lg px-5 py-3 flex items-center">
              <Landmark class="h-4 w-4 mr-2" />
              Loans
            </TabsTrigger>

            <TabsTrigger value="transactions"
              class="data-[state=active]:bg-white data-[state=active]:shadow-md rounded-lg px-5 py-3 flex items-center">
              <Receipt class="h-4 w-4 mr-2" />
              Transactions
            </TabsTrigger>
          </TabsList>

          <!-- LOANS -->
          <TabsContent value="loans" class="mt-6">
            <div v-if="activeLoans?.length" class="grid gap-6 lg:grid-cols-2">
              <Card v-for="loan in activeLoans" :key="loan.id"
                class="bg-white border border-slate-200 rounded-3xl shadow-sm hover:shadow-xl transition-all duration-500 max-w-full">
                <CardHeader class="flex justify-between items-start">
                  <div>
                    <CardTitle class="text-base font-semibold text-slate-800">
                      {{ loan.loanProduct?.name || 'Loan Facility' }}
                    </CardTitle>
                    <p class="text-xs text-slate-500 mt-1 capitalize">
                      {{ loan.status }}
                    </p>
                  </div>

                  <Badge class="bg-orange-100 text-orange-700 rounded-full px-3">
                    {{ fmtMoney(loan.outstanding_balance) }}
                  </Badge>
                </CardHeader>

                <CardContent class="space-y-4">
                  <div class="flex justify-between text-sm text-slate-600">
                    <span>Next Payment</span>
                    <span>{{ fmtDate(loan.first_repayment_date) }}</span>
                  </div>

                  <div class="flex justify-between text-sm text-slate-600">
                    <span>Disbursed</span>
                    <span>{{ fmtMoney(loan.disbursed_amount || 0) }}</span>
                  </div>

                  <div class="flex gap-3 pt-3 flex-wrap">
                    <Button as-child size="sm">
                      <Link :href="route('loans.show', loan.id)">View</Link>
                    </Button>

                    <Button as-child size="sm" class="bg-slate-100 hover:bg-slate-200 text-slate-800">
                      <Link :href="route('loans.repayments', loan.id)">Repay</Link>
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>

            <div v-else
              class="flex flex-col items-center justify-center text-center py-20 bg-white border border-dashed border-slate-300 rounded-3xl">
              <div class="p-5 rounded-full bg-slate-100 mb-5">
                <Landmark class="h-8 w-8 text-slate-400" />
              </div>

              <h3 class="text-sm sm:text-base font-semibold text-slate-800">
                No Active Loans
              </h3>

              <p class="max-sm:text-xs text-sm mx-2 text-slate-500 mt-2 max-w-md">
                You currently don’t have any active loan facilities.
                Apply for a loan to access quick and affordable financing.
              </p>

              <Button as-child class="mt-6 max-sm:text-sm bg-blue-900 hover:bg-blue-800 text-white px-6 rounded-xl">
                <Link :href="route('my-loans')">Apply for a Loan</Link>
              </Button>
            </div>
          </TabsContent>

          <!-- TRANSACTIONS -->
          <TabsContent value="transactions" class="mt-2">
            <div class="hidden flex justify-between mb-4">
              <Input v-model="txFilter" placeholder="Search transactions..."
                class="max-w-xs rounded-xl border-slate-300 focus:ring-2 focus:ring-slate-400" />
            </div>

            <h3 class="font-bold mb-4">Recent transactions</h3>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
              <div class="overflow-x-auto max-w-full">
                <table class="table-auto w-full text-sm">
                  <thead class="bg-slate-100 text-slate-600 uppercase text-xs tracking-wide">
                    <tr>
                      <th class="p-4 text-left">Date</th>
                      <th class="p-4 text-left">Type</th>
                      <th class="p-4 text-left">Account</th>
                      <th class="p-4 text-right">Amount</th>
                      <th class="p-4 text-right">Direction</th>
                    </tr>
                  </thead>

                  <tbody>
                    <template v-if="filteredTx.length">
                      <tr v-for="t in recentTx" :key="t.id" class="border-t hover:bg-slate-50 transition">
                        <td class="p-4">{{ fmtDate(t.created_at) }}</td>
                        <td class="p-4 capitalize">{{ t.transaction_type.replace('_', ' ') }}</td>
                        <td class="p-4 capitalize">{{ t.account?.account_type || '—' }}</td>
                        <td class="p-4 text-right font-medium text-slate-800">{{ fmtMoney(t.amount) }}</td>
                        <td class="p-4 text-right">
                          <Badge v-if="[
    'deposit',
    'loan_disbursement',
    'dividend_payment',
    'interest_payment',
    'share_capital_contribution'
  ].includes(t.transaction_type)" class="bg-emerald-100 text-emerald-700">Credit</Badge>
                          <Badge v-else class="bg-rose-100 text-rose-700">Debit</Badge>
                        </td>
                      </tr>
                    </template>
                    <tr v-else>
                      <td colspan="5" class="p-6 text-center text-slate-500 text-sm">No transactions</td>
                    </tr>
                  </tbody>
                </table>

                <div class="flex justify-end mt-4">
                  <Button as-child variant="outline">
                    <Link :href="route('members.transactions', member.id)">View All →</Link>
                  </Button>
                </div>
              </div>
            </div>

          </TabsContent>

        </Tabs>
      </section>

    </div>
  </AppLayout>
</template>