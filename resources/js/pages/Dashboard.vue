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

const fmtMoney = (v: number) =>
  new Intl.NumberFormat(undefined, { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(v || 0)
const fmtDate = (d?: string | null) => (d ? new Date(d).toLocaleDateString() : '—')
const fullName = computed(() => `${props.member.first_name} ${props.member.last_name}`)
const totals = computed(() => {
  const s = Number(props.stats.accounts.savings_balance) || 0
  const sh = Number(props.stats.accounts.shares_balance) || 0
  const d = Number(props.stats.accounts.deposits_balance) || 0
  return { balances: s + sh + d }
})

const txFilter = ref('')
const filteredTx = computed(() => {
  const q = txFilter.value.trim().toLowerCase()
  if (!q) return props.recentTransactions
  return props.recentTransactions.filter(t =>
    t.transaction_type.toLowerCase().includes(q) || String(t.amount).includes(q)
  )
})

const showBalances = ref(false)
const toggleBalances = () => (showBalances.value = !showBalances.value)

const showNotifications = ref(false)
const toggleNotifications = () => (showNotifications.value = !showNotifications.value)
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
    <Head title="Dashboard" />

    <div class="min-h-screen bg-[#f5f7fb] p-5 sm:p-6 space-y-10">

      <!-- Header Banner -->
      <div class="relative bg-gradient-to-r from-[#0B2B40] to-[#133263] rounded-3xl p-6 text-white shadow-xl">
        <div class="flex flex-col sm:flex-row items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold tracking-tight">SEPU <span class="text-orange-500">SACCO</span></h1>
            <p class="text-blue-100 text-sm">Save & Grow Together</p>
          </div>
          <div class="flex flex-col items-center gap-2 mt-3 sm:mt-0">
            <Handshake class="w-6 h-6 text-orange-400" />
            <div class=" h-1.5 w-24 bg-orange-500 rounded-full"></div>
          </div>
        </div>
      </div>

      <!-- Welcome -->
      <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-xl font-semibold text-[#0a2342]">Welcome, {{ fullName }}</h2>
          <p class="text-sm text-gray-500">Here’s your financial snapshot</p>
        </div>
        <div class="flex items-center gap-3 mt-3 sm:mt-0">
          <Button size="sm" variant="outline" class="border-[#0a2342] text-[#0a2342]" @click="toggleBalances">
            {{ showBalances ? 'Hide Balances' : 'Show Balances' }}
          </Button>

          <!-- Notifications -->
          <div class="relative">
            <button
              class="relative p-2 rounded-full bg-[#0a2342] hover:bg-orange-500 transition"
              @click="toggleNotifications"
            >
              <Bell class="h-4 w-4 text-white" />
              <span
                class="absolute -top-1 -right-1 bg-orange-500 text-white text-[10px] rounded-full px-1 min-w-[16px] text-center"
              >
                {{ notifications?.length ?? 0 }}
              </span>
            </button>

            <div
              v-if="showNotifications"
              class="absolute right-0 mt-2 w-72 bg-white border border-gray-200 rounded-2xl shadow-lg z-50 overflow-hidden"
            >
              <div class="p-3 bg-[#0B2B40] text-white flex items-center justify-between">
                <span class="font-medium">Notifications</span>
                <button @click="showNotifications = false" class="text-orange-400 hover:text-white">✕</button>
              </div>
              <ul class="max-h-60 overflow-y-auto divide-y divide-gray-100">
                <li
                  v-for="n in notifications"
                  :key="n.id"
                  class="p-3 text-sm text-gray-700 hover:bg-orange-50 transition"
                >
                  <div class="font-medium">{{ n.message }}</div>
                  <div class="text-xs text-gray-400">{{ fmtDate(n.created_at) }}</div>
                </li>
                <li v-if="!notifications?.length" class="p-6 text-sm text-gray-500 text-center">
                  No notifications
                </li>
              </ul>
            </div>
          </div>
        </div>
      </header>

      <!-- Account Stats -->
      <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <Card
          v-for="stat in [
            { title: 'Savings Balance', value: fmtMoney(stats.accounts.savings_balance), icon: PiggyBank },
            { title: 'Shares Balance', value: fmtMoney(stats.accounts.shares_balance), icon: BadgeDollarSign },
            { title: 'Deposits Balance', value: fmtMoney(stats.accounts.deposits_balance), icon: Wallet },
            { title: 'Active Loans', value: stats.loans.active_loans, icon: TrendingUp }
          ]"
          :key="stat.title"
          class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
        >
          <CardHeader class="flex justify-between items-center">
            <CardTitle class="text-sm font-medium text-gray-700">{{ stat.title }}</CardTitle>
            <div class="p-2 rounded-xl bg-orange-100 text-orange-600">
              <component :is="stat.icon" class="h-5 w-5" />
            </div>
          </CardHeader>
          <CardContent>
            <div class="text-lg font-semibold text-[#0a2342]">
              <span v-if="showBalances">{{ stat.value }}</span>
              <span v-else class="text-gray-300">*******</span>
            </div>
          </CardContent>
        </Card>
      </section>

      <!-- Loans & Transactions Tabs -->
      <section>
        <Tabs default-value="loans" class="w-full">
          <TabsList class="bg-blue-50 p-1 rounded-xl flex gap-2">
            <TabsTrigger value="loans" class="data-[state=active]:bg-[#0a2342] data-[state=active]:text-white px-4 py-2 rounded-lg">
              <Landmark class="h-4 w-4 mr-1" /> Loans
            </TabsTrigger>
            <TabsTrigger value="transactions" class="data-[state=active]:bg-[#0a2342] data-[state=active]:text-white px-4 py-2 rounded-lg">
              <Receipt class="h-4 w-4 mr-1" /> Transactions
            </TabsTrigger>
          </TabsList>

          <!-- Loans Tab -->
          <TabsContent value="loans" class="mt-4">
            <div v-if="activeLoans?.length" class="grid gap-4 md:grid-cols-2">
              <Card
                v-for="loan in activeLoans"
                :key="loan.id"
                class="bg-white border border-gray-100 rounded-2xl shadow-md hover:shadow-lg transition-all duration-300"
              >
                <CardHeader class="flex justify-between items-start">
                  <div>
                    <CardTitle class="text-base text-gray-800">{{ loan.loanProduct?.name || 'Loan' }}</CardTitle>
                    <p class="text-xs text-gray-500">Status: <span class="capitalize">{{ loan.status }}</span></p>
                  </div>
                  <Badge variant="secondary" class="bg-orange-100 text-orange-700">Outstanding {{ fmtMoney(loan.outstanding_balance) }}</Badge>
                </CardHeader>
                <CardContent class="space-y-3">
                  <div class="flex justify-between text-sm text-gray-600">
                    <span class="flex items-center gap-2"><Clock class="h-4 w-4" /> Next payment</span>
                    <span>{{ fmtDate(loan.first_repayment_date) }}</span>
                  </div>
                  <div class="flex justify-between text-sm text-gray-600">
                    <span class="flex items-center gap-2"><TrendingUp class="h-4 w-4" /> Disbursed</span>
                    <span>{{ fmtMoney(loan.disbursed_amount || 0) }}</span>
                  </div>
                  <div class="flex gap-2 pt-2">
                    <Button as-child size="sm">
                      <Link :href="route('loans.show', loan.id)">View</Link>
                    </Button>
                    <Button as-child size="sm" class="bg-blue-200 hover:bg-blue-100 text-[#0a2342]">
                      <Link :href="route('loans.repayments', loan.id)">Repay</Link>
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>
            <Alert v-else class="mt-6 bg-orange-50 border-orange-200">
              <Info class="h-4 w-4 text-orange-600" />
              <AlertTitle>No Active Loans</AlertTitle>
              <AlertDescription>Apply for a new loan from the loans page.</AlertDescription>
            </Alert>
          </TabsContent>

          <!-- Transactions Tab -->
          <TabsContent value="transactions" class="mt-6">
            <div class="flex flex-col sm:flex-row sm:justify-between gap-3 mb-3">
              <div class="text-sm text-gray-600">Showing latest {{ filteredTx.length }} transactions</div>
              <Input v-model="txFilter" placeholder="Filter by type or amount" class="border-blue-300 focus:ring-blue-600" />
            </div>
            <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-md">
              <table class="w-full text-sm">
                <thead class="bg-blue-100 text-[#0a2342]">
                  <tr>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Account</th>
                    <th class="p-3 text-right">Amount</th>
                    <th class="p-3 text-right">Direction</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="t in filteredTx" :key="t.id" class="border-t hover:bg-orange-50 transition">
                    <td class="p-3">{{ fmtDate(t.created_at) }}</td>
                    <td class="p-3 capitalize">{{ t.transaction_type.replace('_', ' ') }}</td>
                    <td class="p-3 capitalize">{{ t.account?.account_type || '—' }}</td>
                    <td class="p-3 text-right font-medium text-[#0a2342]">{{ fmtMoney(t.amount) }}</td>
                    <td class="p-3 text-right">
                      <Badge
                        v-if="['deposit', 'loan_disbursement', 'dividend_payment', 'interest_payment'].includes(t.transaction_type)"
                        class="inline-flex items-center gap-1 bg-green-100 text-green-800"
                      >
                        <ArrowDownRight class="h-3.5 w-3.5" /> Credit
                      </Badge>
                      <Badge v-else class="inline-flex items-center gap-1 bg-red-100 text-red-800">
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
  </AppLayout>
</template>
