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
  return new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(value ?? 0)
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
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mx-5 mt-5 mb-8">
      <div class="rounded-2xl bg-blue-50 dark:bg-blue-900 p-4 border border-blue-200 dark:border-blue-800">
        <p class="text-sm text-blue-800 dark:text-blue-300">Total Members</p>
        <p class="text-xl font-bold text-blue-900 dark:text-white">{{ summary.total_members }}</p>
      </div>
      <div class="rounded-2xl bg-green-50 dark:bg-green-900 p-4 border border-green-200 dark:border-green-800">
        <p class="text-sm text-green-800 dark:text-green-300">Total Shares</p>
        <p class="text-xl font-bold text-green-600">{{ formatCurrency(summary.total_shares) }}</p>
      </div>
      <div class="rounded-2xl bg-gray-50 dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-800 dark:text-gray-300">Average Shares</p>
        <p class="text-xl font-bold text-gray-600">{{ formatCurrency(summary.average_shares) }}</p>
      </div>
      <div class="rounded-2xl bg-orange-50 dark:bg-orange-900 p-4 border border-orange-200 dark:border-orange-800">
        <p class="text-sm text-orange-800 dark:text-orange-300">Highest Shares</p>
        <p class="text-xl font-bold text-orange-600">{{ formatCurrency(summary.highest_shares) }}</p>
      </div>
    </div>

    <!-- Members Table -->
    <div
      class="overflow-x-auto rounded-lg m-5 border border-blue-200 dark:border-blue-700 bg-white dark:bg-blue-900 shadow-sm">
      <table class="min-w-full text-sm">
        <thead class="bg-blue-50 dark:bg-blue-800 text-blue-900 dark:text-white sticky top-0">
          <tr>
            <th class="px-4 py-3 text-left">Member</th>
            <th class="px-4 py-3 text-left">Membership ID</th>
            <th class="px-4 py-3 text-left">Shares Balance</th>
            <th class="px-4 py-3 text-left">Account Number</th>
            <th class="px-4 py-3 text-left">Membership Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-blue-200 dark:divide-blue-700">
          <tr v-if="members.length === 0" class="text-center">
            <td colspan="5" class="px-4 py-6 text-gray-500 dark:text-gray-300">No member shares found.</td>
          </tr>
          <tr v-for="member in members" :key="member.member_id"
            class="hover:bg-blue-50 dark:hover:bg-blue-800 transition-colors">
            <td class="px-4 py-3 font-medium">{{ member.name }}</td>
            <td class="px-4 py-3">{{ member.membership_id }}</td>
            <td class="px-4 py-3 font-medium">{{ formatCurrency(member.shares_balance) }}</td>
            <td class="px-4 py-3">{{ member.account_number ?? '-' }}</td>
            <td class="px-4 py-3">{{ formatDate(member.membership_date) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
