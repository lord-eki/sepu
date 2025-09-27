<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Input } from '@/components/ui/input'
import {
  Bell, Wallet, Handshake, PiggyBank, BadgeDollarSign, Landmark, TrendingUp,
  ArrowUpRight, ArrowDownRight, Clock, Receipt, FileText, Info
} from 'lucide-vue-next'

// ---- Types ----
interface Member { id: number; first_name: string; last_name: string }
interface Stats {
  accounts: { savings_balance: number; shares_balance: number; deposits_balance: number }
  loans: { active_loans: number; total_outstanding: number; next_payment_due: string | null }
}
interface Account { id: number; account_type: 'savings' | 'shares' | 'deposits'; balance: number }
interface RecentTransaction { id: number; transaction_type: string; amount: number; created_at: string; account?: Account }
interface LoanProduct { id: number; name: string }
interface Loan { id: number; status: string; outstanding_balance: number; disbursed_amount?: number; first_repayment_date?: string | null; loanProduct?: LoanProduct }
interface NotificationItem { id: number; message: string; created_at: string }

const props = defineProps<{
  member: Member
  stats: Stats
  accounts: Account[]
  recentTransactions: RecentTransaction[]
  activeLoans: Loan[]
  notifications: NotificationItem[]
}>()

// ---- Helpers ----
const fmtMoney = (v: number) =>
  new Intl.NumberFormat(undefined, { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(v || 0)

const fmtDate = (d?: string | null) => (d ? new Date(d).toLocaleDateString() : '—')

const fullName = computed(() => `${props.member.first_name} ${props.member.last_name}`)

const totals = computed(() => {
  const s = Number(props.stats?.accounts?.savings_balance) || 0
  const sh = Number(props.stats?.accounts?.shares_balance) || 0
  const d = Number(props.stats?.accounts?.deposits_balance) || 0
  return { balances: s + sh + d }
})

// ---- Transactions filter ----
const txFilter = ref('')
const filteredTx = computed(() => {
  const q = txFilter.value.trim().toLowerCase()
  if (!q) return props.recentTransactions
  return props.recentTransactions.filter(t =>
    t.transaction_type.toLowerCase().includes(q) || String(t.amount).includes(q)
  )
})

// ---- Toggle balances ----
const showBalances = ref(false)
const toggleBalances = () => {
  showBalances.value = !showBalances.value
}

const showNotifications = ref(false)
const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
}

</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
    <Head title="Dashboard" />
    <div class="p-6 bg-gray-50 min-h-screen">
     <div class="w-full rounded-lg bg-gradient-to-r from-blue-600 to-orange-500 mb-4 overflow-hidden">
      <div class="flex items-center justify-between py-3 px-3 gap-2 text-white text-sm sm:text-base">
        <div class="flex gap-1 items-center">
          <span class="text-white tracking-wide">SEPU SACCO</span>
        </div>
        <div class="flex gap-1 items-center italic">
          <span>Save & grow together</span>
          <Handshake class="w-3 h-3 text-white" />
        </div>
      </div>
      </div>

      <div class="space-y-10">
        <!-- Header -->
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 class="text-lg md:text-xl font-bold tracking-tight text-blue-900">
              Welcome, {{ fullName }}
            </h1>
            <p class="text-gray-600 text-sm md:text-base">Your personal account overview</p>
          </div>
          <div class="flex items-center gap-3">
            <!-- Toggle balances -->
            <Button size="sm" variant="outline" class="rounded-full border-blue-600 text-blue-600 hover:bg-blue-50" @click="toggleBalances">
              {{ showBalances ? 'Hide Balances' : 'Show Balances' }}
            </Button>

            <!-- Notifications -->
            <div class="relative">
              <button
                class="relative p-1.5 rounded-full bg-blue-600 hover:bg-blue-700 transition"
                @click="toggleNotifications"
              >
                <Bell class="h-4.5 w-4.5 text-white" />
                <span
                  class="absolute -top-1 -right-1 bg-orange-500 text-white text-[10px] rounded-full min-h-[16px] min-w-[16px] px-1 flex items-center justify-center"
                >
                  {{ notifications?.length ?? 0 }}
                </span>
              </button>

              <!-- Notifications Panel -->
              <div
                v-if="showNotifications"
                class="absolute mt-2 w-[95vw] max-w-sm sm:w-80 sm:right-0 sm:left-auto left-1/2 -translate-x-1/2 sm:translate-x-0 bg-white border border-gray-200 rounded-lg shadow-lg z-50"
              >
                <!-- Header -->
                <div class="flex items-center justify-between p-2 border-b bg-blue-600 text-white rounded-t-xl">
                  <span class="font-semibold">Notifications</span>
                  <button
                    @click="showNotifications = false"
                    class="text-orange-300 hover:text-orange-500"
                  >
                    ✕
                  </button>
                </div>

                <!-- Notifications List -->
                <ul class="max-h-60 overflow-y-auto divide-y divide-gray-200">
                  <li
                    v-for="n in notifications"
                    :key="n.id"
                    class="p-3 text-sm text-gray-700 hover:bg-blue-50"
                  >
                    <div class="font-medium">{{ n.message }}</div>
                    <div class="text-xs text-gray-400">{{ fmtDate(n.created_at) }}</div>
                  </li>
                  <li v-if="!notifications?.length" class="p-6 text-sm text-gray-500 text-center">
                    No notifications
                  </li>
                </ul>

                <!-- Footer -->
                <div class="p-2 text-right" v-if="notifications?.length">
                  <Button as-child size="sm" variant="ghost" class="text-blue-600 hover:text-blue-800">
                    <Link href="#">View all</Link>
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </header>

        <!-- Top Stats -->
        <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
          <Card
            v-for="stat in [
              { title: 'Savings Balance', value: fmtMoney(stats.accounts.savings_balance), subLabel: 'Total balances:', subValue: fmtMoney(totals.balances), icon: PiggyBank },
              { title: 'Shares Balance', value: fmtMoney(stats.accounts.shares_balance), subLabel: 'Equity position', subValue: null, icon: BadgeDollarSign },
              { title: 'Deposits Balance', value: fmtMoney(stats.accounts.deposits_balance), subLabel: 'Locked deposits', subValue: null, icon: Wallet },
              { title: 'Active Loans', value: stats.loans.active_loans, subLabel: 'Outstanding:', subValue: fmtMoney(stats.loans.total_outstanding), icon: TrendingUp }
            ]"
            :key="stat.title"
            class="rounded-xl border border-gray-200 bg-white shadow hover:shadow-md transition overflow-hidden"
          >
            <CardHeader class="flex flex-row items-center justify-between pb-2">
              <CardTitle class="text-sm font-medium text-blue-900">{{ stat.title }}</CardTitle>
              <component :is="stat.icon" class="h-5 w-5 text-orange-500" />
            </CardHeader>
            <CardContent>
              <div class="text-base md:text-xl font-bold">
                <span v-if="showBalances" class="text-blue-900">{{ stat.value }}</span>
                <span v-else class="text-gray-300">*******</span>
              </div>
              <p class="text-xs text-gray-500">
                {{ stat.subLabel }}
                <span v-if="stat.subValue">
                  <span v-if="showBalances">{{ stat.subValue }}</span>
                  <span v-else class="blur-sm text-blue-800">Hidden Bal</span>
                </span>
              </p>
            </CardContent>
          </Card>
        </section>

        <!-- Tabs -->
        <section>
          <Tabs default-value="loans" class="w-full">
            <TabsList class="flex flex-wrap gap-2 bg-blue-50 rounded-lg">
              <TabsTrigger value="loans" class="px-4 rounded-lg data-[state=active]:bg-blue-600 data-[state=active]:text-white">
                <Landmark class="h-4 w-4" /> Loans
              </TabsTrigger>
              <TabsTrigger value="transactions" class="px-4 rounded-lg data-[state=active]:bg-blue-600 data-[state=active]:text-white">
                <Receipt class="h-4 w-4" /> Transactions
              </TabsTrigger>
            </TabsList>

            <!-- Loans Tab -->
            <TabsContent value="loans" class="mt-6">
              <!-- same loan cards, just styled -->
            </TabsContent>

            <!-- Transactions Tab -->
            <TabsContent value="transactions" class="mt-6">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <div class="text-sm text-gray-600">Showing latest {{ filteredTx.length }} transactions</div>
                <div class="w-full sm:w-64">
                  <Input v-model="txFilter" placeholder="Filter by type or amount" class="border-blue-300 focus:ring-blue-600" />
                </div>
              </div>

              <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="w-full text-sm">
                  <thead class="bg-blue-100 text-blue-900">
                    <tr>
                      <th class="text-left p-2 font-medium">Date</th>
                      <th class="text-left p-2 font-medium">Type</th>
                      <th class="text-left p-2 font-medium">Account</th>
                      <th class="text-right p-2 font-medium">Amount</th>
                      <th class="text-right p-2 font-medium">Direction</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="t in filteredTx" :key="t.id" class="border-t hover:bg-blue-50 transition">
                      <td class="p-3">{{ fmtDate(t.created_at) }}</td>
                      <td class="p-3 capitalize">{{ t.transaction_type.replace('_', ' ') }}</td>
                      <td class="p-3 capitalize">{{ t.account?.account_type || '—' }}</td>
                      <td class="p-3 text-right font-medium text-blue-900">{{ fmtMoney(t.amount) }}</td>
                      <td class="p-3 text-right">
                        <Badge
                          v-if="['deposit','loan_disbursement','dividend_payment','interest_payment'].includes(t.transaction_type)"
                          class="inline-flex items-center gap-1 bg-green-100 text-green-800"
                        >
                          <ArrowDownRight class="h-3.5 w-3.5" /> Credit
                        </Badge>
                        <Badge
                          v-else
                          class="inline-flex items-center gap-1 bg-red-100 text-red-800"
                        >
                          <ArrowUpRight class="h-3.5 w-3.5" /> Debit
                        </Badge>
                      </td>
                    </tr>
                    <tr v-if="!filteredTx.length">
                      <td colspan="5" class="p-6 text-center text-gray-500">No transactions found.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </TabsContent>
          </Tabs>
        </section>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
tbody tr:nth-child(even) {
  background: color-mix(in oklab, #e0f2fe, transparent 85%);
}

</style>

