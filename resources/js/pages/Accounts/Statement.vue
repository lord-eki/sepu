<template>
  <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/my-accounts' }, { title: 'Statement' }]">

    <Head title="Account Statement" />

    <div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto space-y-6">

        <!-- HEADER -->
        <div
          class="bg-gradient-to-r from-blue-900 via-orange-300 to-blue-800 text-white rounded-2xl shadow-lg p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

          <div>
            <h1 class="text-xl font-bold tracking-tight">ACCOUNT STATEMENT</h1>

            <p class="text-blue-100 mt-1">
              {{ account.member?.first_name }} {{ account.member?.last_name }}
            </p>

            <p class="text-blue-200 text-sm">
              Account #: <span class="font-medium text-white">{{ account.account_number }}</span>
            </p>

            <p class="text-blue-200 text-sm mt-1">
              {{ formatDate(fromDate) }} → {{ formatDate(toDate) }}
            </p>
          </div>

          <div class="flex flex-col sm:flex-row gap-4">

            <div class="bg-white/10 backdrop-blur rounded-xl px-5 py-3 text-sm">
              <p class="text-blue-200 text-xs uppercase">Balance</p>
              <p class="text-lg font-semibold">
                KES {{ formatCurrency(account.balance) }}
              </p>
            </div>

            <a :href="route('my-accounts.statement.pdf', { account: account.id, from: fromDate, to: toDate })"
              target="_blank"
              class="inline-flex items-center gap-2 h-fit bg-white text-blue-900 font-semibold px-4 py-2 rounded-lg shadow hover:bg-blue-50 transition">
              <Download class="w-4 h-4" />
              Download
            </a>

          </div>
        </div>

        <!-- FILTER BAR -->
        <div class="bg-white rounded-xl shadow-sm border p-4 flex flex-col md:flex-row gap-3 md:items-end">

          <div class="flex flex-col">
            <label class="text-xs text-gray-500 mb-1">From</label>
            <input type="date" v-model="fromDate"
              class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-600" />
          </div>

          <div class="flex flex-col">
            <label class="text-xs text-gray-500 mb-1">To</label>
            <input type="date" v-model="toDate" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-600" />
          </div>

          <!-- BUTTON -->
          <button
            @click="applyFilter"
            :disabled="loading"
            class="bg-blue-900 text-white px-5 py-2 rounded-lg hover:bg-blue-800 transition font-medium inline-flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
          >
            <Loader2
              v-if="loading"
              class="w-4 h-4 animate-spin"
            />

            <span>
              {{ loading ? 'Refreshing...' : 'Apply Filter' }}
            </span>
          </button>

        </div>

        <!-- TRANSACTIONS -->
        <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">

          <!-- TABLE HEADER -->
          <div class="px-6 py-4 border-b bg-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">
              Transactions
            </h2>

            <p class="text-sm text-gray-500">
              {{ transactions.length }} record<span v-if="transactions.length !== 1">s</span>
            </p>
          </div>

          <!-- TABLE -->
          <div class="overflow-x-auto">

            <table class="min-w-full text-sm">

              <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                  <th class="px-6 py-3 text-left">Date</th>
                  <th class="px-6 py-3 text-left">Type</th>
                  <th class="px-6 py-3 text-right">Amount</th>
                  <th class="px-6 py-3 text-right">Balance</th>
                  <th class="px-6 py-3 text-center">Status</th>
                  <th class="px-6 py-3 text-left">Description</th>
                </tr>
              </thead>

              <tbody class="divide-y">

                <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-gray-50 transition">

                  <td class="px-6 py-4 text-gray-600">
                    {{ formatDateTime(tx.created_at) }}
                  </td>

                  <td class="px-6 py-4 capitalize text-gray-700">
                    {{ tx.transaction_type.replace('_', ' ') }}
                  </td>

                  <td class="px-6 py-4 text-right font-semibold" :class="amountColor(tx)">
                    KES {{ formatCurrency(tx.amount) }}
                  </td>

                  <td class="px-6 py-4 text-right text-gray-800 font-medium">
                    KES {{ formatCurrency(tx.balance_after) }}
                  </td>

                  <td class="px-6 py-4 text-center">
                    <span :class="statusClass(tx.status)">
                      {{ tx.status }}
                    </span>
                  </td>

                  <td class="px-6 py-4 text-gray-500">
                    {{ tx.description || '—' }}
                  </td>

                </tr>

                <tr v-if="!transactions.length">
                  <td colspan="6" class="text-center py-10 text-gray-500">
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
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Download, Loader2 } from 'lucide-vue-next'

const props = defineProps<{
  account: any
  transactions: any[]
  period: { from: string; to: string }
}>()

const fromDate = ref(props.period.from)
const toDate = ref(props.period.to)
const loading = ref(false)

const applyFilter = () => {
  loading.value = true

  router.get(
    route('my-accounts.statement', {
      member: props.account.member.id,
      account: props.account.id,
    }),
    {
      from: fromDate.value,
      to: toDate.value
    },
    {
      preserveScroll: true,
      preserveState: true,
      onFinish: () => loading.value = false,
      onError: () => loading.value = false
    }
  )
}

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-KE', { minimumFractionDigits: 0 }).format(amount)

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString('en-KE', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })

const formatDateTime = (date: string) =>
  new Date(date).toLocaleString('en-KE', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  })

const statusClass = (status: string) => {
  switch (status) {
    case 'completed':
      return 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium'
    case 'pending':
      return 'px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700 font-medium'
    case 'failed':
      return 'px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium'
    default:
      return 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600 font-medium'
  }
}

const amountColor = (tx: any) => {
  if (tx.transaction_type.includes('deposit') || tx.transaction_type.includes('credit')) {
    return 'text-green-600'
  }
  if (tx.transaction_type.includes('withdraw') || tx.transaction_type.includes('debit')) {
    return 'text-red-600'
  }
  return 'text-gray-800'
}
</script>