<script setup lang="ts">
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Download } from 'lucide-vue-next'

const props = defineProps<{
  member: any
  transactions: any[]
  period: { from: string; to: string }
}>()

const fromDate = ref(props.period.from)
const toDate = ref(props.period.to)

const applyFilter = () => {
  router.get(route('my-transactions.statement'), {
    from: fromDate.value,
    to: toDate.value
  })
}

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-KE').format(amount)

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString('en-KE')

const formatDateTime = (date: string) =>
  new Date(date).toLocaleString('en-KE')

const amountColor = (tx: any) => {
  if (tx.transaction_type.includes('deposit')) return 'text-green-600'
  if (tx.transaction_type.includes('withdraw')) return 'text-red-600'
  return 'text-gray-800'
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'My Transactions', href: '/my-transactions' }, { title: 'Statement' }]">

    <Head title="Transaction Statement" />

    <div class="p-6 space-y-6 bg-gray-50 min-h-screen">

      <!-- HEADER -->
      <div class="bg-gradient-to-r from-[#0a2342] via-blue-900 to-orange-500 text-white p-6 rounded-2xl shadow-lg flex justify-between">

        <div>
          <h1 class="text-xl font-bold">TRANSACTION STATEMENT</h1>
          <p>{{ member.first_name }} {{ member.last_name }}</p>
          <p class="text-sm mt-1">
            {{ formatDate(fromDate) }} → {{ formatDate(toDate) }}
          </p>
        </div>

        <a
          :href="route('my-transactions.statement.pdf', { from: fromDate, to: toDate })"
          target="_blank"
          class="bg-white text-[#0a2342] px-4 py-2 h-fit rounded-lg flex items-center gap-2"
        >
          <Download class="w-4 h-4" />
          Download PDF
        </a>

      </div>

      <!-- FILTER -->
      <div class="bg-white p-4 rounded-xl shadow flex gap-4">

        <input type="date" v-model="fromDate" class="border px-3 py-2 rounded" />
        <input type="date" v-model="toDate" class="border px-3 py-2 rounded" />

        <button @click="applyFilter"
          class="bg-[#0a2342] text-white px-4 py-2 rounded">
          Apply
        </button>

      </div>

     
      <!-- TABLE -->
        <div class="bg-white rounded-2xl shadow overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">Date</th>
                <th class="p-3">Type</th>
                <th class="p-3 text-right">Amount</th>
                <th class="p-3 text-right">Balance</th>
                <th class="p-3 text-center">Status</th>
            </tr>
            </thead>

            <tbody>
            <tr v-for="tx in transactions" :key="tx.id" class="border-b">

                <td class="p-3">{{ formatDateTime(tx.created_at) }}</td>

                <td class="p-3 capitalize">
                {{ tx.transaction_type }}
                </td>

                <td class="p-3 text-right font-semibold" :class="amountColor(tx)">
                KES {{ formatCurrency(tx.amount) }}
                </td>

                <td class="p-3 text-right">
                KES {{ formatCurrency(tx.balance_after) }}
                </td>

                <!-- STATUS -->
                <td class="p-3 text-center">
                <span
                    class="px-2 py-1 rounded-full text-xs font-medium"
                    :class="{
                    'bg-green-100 text-green-700': tx.status === 'completed',
                    'bg-yellow-100 text-yellow-700': tx.status === 'pending',
                    'bg-red-100 text-red-700': tx.status === 'failed',
                    'bg-gray-100 text-gray-700': !tx.status
                    }"
                >
                    {{ tx.status || 'completed' }}
                </span>
                </td>

            </tr>

            <tr v-if="!transactions.length">
                <td colspan="5" class="text-center p-6 text-gray-500">
                No transactions found
                </td>
            </tr>

            </tbody>

        </table>

        </div>

    </div>

  </AppLayout>
</template>