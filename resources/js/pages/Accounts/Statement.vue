<template>
  <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/my-accounts' }, { title: 'Statement' }]">
    <Head title="Account Statement" />

    <div class="min-h-screen bg-white py-10 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto space-y-8">

        <!-- Header Banner -->
        <div class="rounded-2xl bg-blue-900 text-white shadow-lg p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-semibold tracking-wide mb-1">Account Statement</h1>
            <p class="text-sm sm:text-base opacity-90">
              {{ account.member?.first_name }} {{ account.member?.last_name }} •
              <span class="font-medium">{{ account.account_number }}</span>
            </p>
            <p class="text-sm sm:text-base opacity-90 mt-1">
              Period:
              <span class="font-medium">{{ formatDate(period.from) }}</span> -
              <span class="font-medium">{{ formatDate(period.to) }}</span>
            </p>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div class="text-sm bg-white/10 px-4 py-2 rounded-lg shadow-inner">
              <span class="opacity-90">Balance:</span>
              <span class="font-semibold">KES {{ formatCurrency(account.balance) }}</span>
            </div>

            <a
              :href="route('my-accounts.statement.pdf', { account: account.id, from: period.from, to: period.to })"
              target="_blank"
              rel="noopener"
              class="inline-flex items-center gap-2 bg-white text-blue-900 font-medium px-4 py-2 rounded-lg shadow hover:bg-blue-50 transition"
            >
              <Download class="w-4 h-4" />
              Download PDF
            </a>
          </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
          <div class="px-4 py-3 border-b border-gray-100 bg-blue-900 text-white rounded-t-2xl">
            <h2 class="text-lg font-semibold">Transactions</h2>
            <p class="text-sm text-blue-100">Showing {{ transactions.length }} record<span v-if="transactions.length !== 1">s</span></p>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-gray-700">
              <thead class="bg-blue-50 text-xs uppercase font-medium text-blue-900">
                <tr>
                  <th class="px-4 py-3 text-left">Date & Time</th>
                  <th class="px-4 py-3 text-left">Type</th>
                  <th class="px-4 py-3 text-right">Amount (KES)</th>
                  <th class="px-4 py-3 text-right">Balance After</th>
                  <th class="px-4 py-3 text-center">Status</th>
                  <th class="px-4 py-3 text-left">Description</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="tx in transactions"
                  :key="tx.id"
                  class="hover:bg-blue-50 transition duration-150"
                >
                  <td class="px-4 py-3">{{ formatDateTime(tx.created_at) }}</td>
                  <td class="px-4 py-3 capitalize">{{ tx.transaction_type.replace('_', ' ') }}</td>
                  <td class="px-4 py-3 text-right font-medium text-blue-900">
                    {{ formatCurrency(tx.amount) }}
                  </td>
                  <td class="px-4 py-3 text-right font-medium text-gray-800">
                    {{ formatCurrency(tx.balance_after) }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span
                      :class="[
                        'px-2 py-1 rounded-full text-xs font-semibold border',
                        tx.status === 'completed'
                          ? 'border-blue-900 text-blue-900 bg-blue-50'
                          : tx.status === 'pending'
                          ? 'border-gray-400 text-gray-600 bg-gray-100'
                          : 'border-red-700 text-red-700 bg-red-50',
                      ]"
                    >
                      {{ tx.status }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-gray-600">
                    {{ tx.description || '—' }}
                  </td>
                </tr>

                <tr v-if="!transactions.length">
                  <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                    No transactions found in this period.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Download } from 'lucide-vue-next'

const props = defineProps<{
  account: any
  transactions: any[]
  period: { from: string; to: string }
}>()

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-KE', { minimumFractionDigits: 0 }).format(amount)

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric' })

const formatDateTime = (date: string) =>
  new Date(date).toLocaleString('en-KE', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })
</script>
