<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Activity,
  Users,
  Banknote,
  FileWarning,
  ArrowRightCircle,
  Clock,
  Database,
  AlertTriangle,
  Settings,
  ShieldCheck,
  BarChart3,
  Handshake,
} from 'lucide-vue-next'

interface Stats {
  members: { total: number; active: number; new_this_month: number }
  financial: { total_savings: number; total_shares: number; total_deposits: number }
  loans: { total_portfolio: number; active_loans: number; pending_applications: number; arrears_amount: number }
  transactions: { today: number; this_week: number; this_month: number }
}

defineProps<{
  stats: Stats
  recentActivities: Array<any>
  pendingApprovals: Record<string, number>
  systemHealth: Record<string, any>
}>()
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
    <Head title="Admin Dashboard" />

    <div class="min-h-screen bg-[#f5f7fb] p-6 space-y-10">

      <!-- Header -->
      <header
        class="bg-gradient-to-r from-[#0a2342] to-[#133263] rounded-3xl p-6 text-white shadow-xl flex flex-col sm:flex-row sm:items-center sm:justify-between"
      >
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">
            SEPU <span class="text-orange-500">SACCO</span>
          </h1>
          <p class="text-sm text-blue-100 mt-1">
            Welcome to SEPU SACCO Admin Dashboard — Comprehensive System Overview & Insights
          </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-col items-center gap-2">
          <Handshake class="w-6 h-6 text-orange-400" />
          <div class="h-1.5 w-24 bg-orange-500 rounded-full animate-pulse"></div>
        </div>
      </header>

      <!-- Quick Stats -->
      <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <Card
          v-for="stat in [
            { title: 'Total Members', value: stats.members.total, sub: `Active: ${stats.members.active}`, icon: Users, color: 'bg-blue-900/10 text-blue-900' },
            { title: 'Total Savings', value: stats.financial.total_savings.toLocaleString(), sub: `Shares: ${stats.financial.total_shares}`, icon: Banknote, color: 'bg-orange-100 text-orange-600' },
            { title: 'Active Loans', value: stats.loans.active_loans, sub: `Pending: ${stats.loans.pending_applications}`, icon: Activity, color: 'bg-blue-900/10 text-blue-900' },
            { title: 'Transactions Today', value: stats.transactions.today, sub: `This Month: ${stats.transactions.this_month}`, icon: FileWarning, color: 'bg-orange-100 text-orange-600' },
          ]"
          :key="stat.title"
          class="bg-white/90 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
        >
          <CardHeader class="flex items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-gray-700">{{ stat.title }}</CardTitle>
            <div class="p-2 rounded-xl" :class="stat.color">
              <component :is="stat.icon" class="h-5 w-5" />
            </div>
          </CardHeader>
          <CardContent>
            <div class="text-xl font-semibold text-[#0a2342]">{{ stat.value }}</div>
            <p class="text-xs text-gray-500">{{ stat.sub }}</p>
          </CardContent>
        </Card>
      </section>

      <!-- Activities & System Info -->
      <section class="grid gap-6 lg:grid-cols-3">
        <!-- Recent Activities -->
        <div class="lg:col-span-2">
          <h2 class="text-lg font-semibold mb-3 text-[#0a2342]">Recent Activities</h2>
          <div class="bg-white/90 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md h-[350px] overflow-y-auto custom-scroll divide-y">
            <div
              v-for="(item, i) in recentActivities"
              :key="i"
              class="flex items-center justify-between p-4 hover:bg-orange-50 transition"
            >
              <div class="flex items-start gap-3">
                <ArrowRightCircle class="h-5 w-5 text-orange-500 mt-0.5" />
                <div>
                  <p class="text-sm font-medium text-gray-800 leading-tight">{{ item.description }}</p>
                  <p class="text-xs text-gray-500">{{ new Date(item.time).toLocaleString() }}</p>
                </div>
              </div>
              <span class="text-sm font-semibold text-[#0a2342]">Ksh. {{ item.amount }}</span>
            </div>
          </div>
        </div>

        <!-- Combined Approvals + System Health -->
        <Card class="bg-white/90 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md flex flex-col">
          <CardHeader>
            <CardTitle class="text-base font-semibold text-[#0a2342] flex items-center gap-2">
              <Clock class="h-4 w-4 text-orange-500" /> Pending Approvals
            </CardTitle>
          </CardHeader>
          <CardContent class="pb-0">
            <ul class="space-y-2 text-sm mb-4">
              <li class="flex justify-between items-center">
                Loans
                <span class="px-2 pb-0.5 rounded-lg bg-orange-100 text-orange-700 font-semibold">{{ pendingApprovals.loans }}</span>
              </li>
              <li class="flex justify-between items-center">
                Vouchers
                <span class="px-2 py-0.5 rounded-lg bg-blue-900/10 text-blue-900 font-semibold">{{ pendingApprovals.vouchers }}</span>
              </li>
              <li class="flex justify-between items-center">
                Members
                <span class="px-2 py-0.5 rounded-lg bg-green-100 text-green-700 font-semibold">{{ pendingApprovals.member_applications }}</span>
              </li>
            </ul>

            <hr class="border-gray-200 mb-4" />

            <CardTitle class="text-base font-semibold text-[#0a2342] flex items-center gap-2">
              <Database class="h-4 w-4 text-blue-800" /> System Health
            </CardTitle>
            <ul class="space-y-3 text-sm mt-5">
              <li class="flex items-center gap-2">
                <Database class="h-4 w-4 text-green-600" /> Status:
                <span class="font-medium text-green-700">{{ systemHealth.database_status }}</span>
              </li>
              <li class="flex items-center gap-2">
                <Clock class="h-4 w-4 text-gray-500" /> Last Backup:
                <span>{{ new Date(systemHealth.last_backup).toLocaleString() }}</span>
              </li>
              <li class="flex items-center gap-2">
                <Users class="h-4 w-4 text-[#0a2342]" /> Active Users:
                <span class="font-medium">{{ systemHealth.active_users }}</span>
              </li>
              <li class="flex items-center gap-2">
                <AlertTriangle class="h-4 w-4 text-red-600" /> Errors:
                <span class="font-medium text-red-600">{{ systemHealth.system_errors }}</span>
              </li>
            </ul>
          </CardContent>
        </Card>
      </section>

      <!-- System Setup -->
      <section>
        <h2 class="text-lg font-semibold mb-4 text-[#0a2342] flex items-center gap-2">
          <Settings class="h-5 w-5 text-orange-500" /> System Setup & Configuration
        </h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <Card
            v-for="setup in [
              { title: 'Loan Rates', desc: 'Define loan interest and limits', icon: BarChart3, color: 'bg-orange-100 text-orange-600', link: '/admin/setup/loan-rates' },
              { title: 'Repayment Periods', desc: 'Set repayment durations', icon: Clock, color: 'bg-blue-900/10 text-blue-900', link: '/admin/setup/repayment-periods' },
              { title: 'System Users', desc: 'Manage user roles and permissions', icon: Users, color: 'bg-green-100 text-green-700', link: '/admin/setup/users' },
              { title: 'System Approvals', desc: 'Define approval workflows', icon: ShieldCheck, color: 'bg-blue-100 text-blue-800', link: '/admin/setup/approvals' },
            ]"
            :key="setup.title"
            class="bg-white/90 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer"
            @click="$inertia.visit(setup.link)"
          >
            <CardHeader class="flex items-center justify-between pb-2">
              <CardTitle class="text-sm font-medium text-gray-700">{{ setup.title }}</CardTitle>
              <div class="p-2 rounded-xl" :class="setup.color">
                <component :is="setup.icon" class="h-5 w-5" />
              </div>
            </CardHeader>
            <CardContent>
              <p class="text-xs text-gray-500">{{ setup.desc }}</p>
            </CardContent>
          </Card>
        </div>
      </section>

      <!-- Footer -->
      <footer class="text-center text-xs text-gray-500 pt-8 pb-4">
        © {{ new Date().getFullYear() }} <span class="font-semibold text-[#0a2342]">SEPU SACCO</span>. All Rights Reserved.
      </footer>
    </div>
  </AppLayout>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar {
  width: 6px;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background-color: #f97316;
  border-radius: 9999px;
}
.custom-scroll::-webkit-scrollbar-track {
  background-color: #f5f7fb;
}
</style>
