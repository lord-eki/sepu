<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { format } from 'date-fns'

const props = defineProps<{
  members: Array<{
    member_id: number
    membership_id: string
    name: string
    shares_balance: number
    account_number?: string
    membership_date: string
  }>
  summary: {
    total_members: number
    total_shares: number
    average_shares: number
    highest_shares: number
  }
}>()

const breadcrumbs = [
  { title: 'Member Reports', href: route('reports.membersReport.index') },
  { title: 'Member Shares' },
]

function formatCurrency(value: number) {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(value ?? 0)
}

function formatDate(date: string | null) {
  if (!date) return '-'
  try {
    return format(new Date(date), 'dd MMM yyyy')
  } catch {
    return date
  }
}
</script>

<template>
  <Head title="Member Shares" />

  <AppLayout title="Member Shares" :breadcrumbs="breadcrumbs">

    <!-- HEADER -->
    <div class="mx-8 mt-8 mb-8">
      <h1 class="text-3xl font-bold text-[#0a2342] dark:text-white">
        Member Shares
      </h1>
      <p class="text-gray-500 dark:text-gray-400 mt-2">
        Overview of member share balances and statistics
      </p>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="mx-8 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">

      <div class="bg-white dark:bg-[#0a2342] border border-orange-200 dark:border-orange-500/30 rounded-xl p-6 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-orange-500">Total Members</p>
        <p class="text-2xl font-bold mt-3 text-[#0a2342] dark:text-white">
          {{ summary.total_members }}
        </p>
      </div>

      <div class="bg-white dark:bg-[#0a2342] border border-orange-200 dark:border-orange-500/30 rounded-xl p-6 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-orange-500">Total Shares</p>
        <p class="text-2xl font-bold mt-3 text-orange-500">
          {{ formatCurrency(summary.total_shares) }}
        </p>
      </div>

      <div class="bg-white dark:bg-[#0a2342] border border-blue-200 dark:border-blue-900 rounded-xl p-6 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-gray-500 dark:text-gray-400">Average Shares</p>
        <p class="text-2xl font-bold mt-3 text-[#0a2342] dark:text-white">
          {{ formatCurrency(summary.average_shares) }}
        </p>
      </div>

      <div class="bg-white dark:bg-[#0a2342] border border-orange-200 dark:border-orange-500/30 rounded-xl p-6 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-orange-500">Highest Shares</p>
        <p class="text-2xl font-bold mt-3 text-orange-500">
          {{ formatCurrency(summary.highest_shares) }}
        </p>
      </div>

    </div>

    <!-- TABLE -->
    <div class="mx-8 mb-12 bg-white dark:bg-[#0a2342] border border-blue-100 dark:border-blue-900 rounded-xl shadow-sm overflow-hidden">

      <div class="overflow-x-auto">

        <table class="w-full text-sm">

          <!-- TABLE HEADER -->
          <thead class="bg-[#0a2342] text-white">
            <tr>
              <th class="px-6 py-4 text-left font-semibold">Member</th>
              <th class="px-6 py-4 text-left font-semibold">Membership ID</th>
              <th class="px-6 py-4 text-left font-semibold">Shares Balance</th>
              <th class="px-6 py-4 text-left font-semibold">Account Number</th>
              <th class="px-6 py-4 text-left font-semibold">Membership Date</th>
            </tr>
          </thead>

          <!-- TABLE BODY -->
          <tbody class="divide-y divide-gray-100 dark:divide-blue-900">

            <!-- EMPTY STATE -->
            <tr v-if="members.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-300">
                No member shares found.
              </td>
            </tr>

            <!-- DATA ROWS -->
            <tr
              v-for="member in members"
              :key="member.member_id"
              class="hover:bg-orange-50 dark:hover:bg-blue-950 transition"
            >
              <td class="px-6 py-4 font-medium text-[#0a2342] dark:text-white">
                {{ member.name }}
              </td>

              <td class="px-6 py-4">
                <span class="bg-orange-100 text-orange-600 px-3 py-1 rounded-md text-xs font-mono">
                  {{ member.membership_id }}
                </span>
              </td>

              <td class="px-6 py-4 font-semibold text-orange-600">
                {{ formatCurrency(member.shares_balance) }}
              </td>

              <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                {{ member.account_number ?? '-' }}
              </td>

              <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                {{ formatDate(member.membership_date) }}
              </td>
            </tr>

          </tbody>

        </table>

      </div>

    </div>

  </AppLayout>
</template>
