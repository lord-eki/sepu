<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { ref, computed } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Wallet, Receipt, FileText, Download } from "lucide-vue-next"

const props = defineProps<{
  member: any
  transactions: {
    data: any[]
    links: any[]
    total: number
  }
  accounts: any[]
  filters: {
    type?: string
    account_id?: number
  }
}>()

// Filters
const filters = ref({
  type: props.filters.type || "",
  account_id: props.filters.account_id || "",
})

// Stats
const totalTransactions = computed(() => props.transactions.total)
const totalAmount = computed(() =>
  props.transactions.data.reduce((sum, t) => sum + Number(t.amount || 0), 0)
)
const lastTransaction = computed(() =>
  props.transactions.data.length ? props.transactions.data[0] : null
)

function applyFilters() {
  router.get(route("members.transactions", props.member.id), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

// Download statement
function downloadStatement() {
  window.open(route("members.transactions.download", props.member.id), "_blank")
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/my-transactions' }]">
    <div class="space-y-10 p-6">

      <Head title="Transactions" />

      <!-- Header -->
      <div
        class="flex bg-blue-50 py-5 px-6 rounded-xl sm:items-center sm:justify-between gap-4"
      >
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-700">
          Transactions
        </h1>

        <!-- Download Statement Button -->
        <button
          @click="downloadStatement"
          class="hidden sm:inline-flex items-center w-fit gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow transition"
        >
          <Download class="h-4 w-4" />
          Download
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card class="hover:shadow-lg transition rounded-2xl border border-indigo-100">
          <CardHeader class="flex items-center gap-2">
            <Wallet class="h-5 w-5 text-green-600" />
            <CardTitle class="text-gray-700">Total Amount</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-base sm:text-lg font-medium text-green-800">
              KES {{ Number(totalAmount).toLocaleString() }}
            </p>
          </CardContent>
        </Card>

        <Card class="hover:shadow-lg transition rounded-2xl border border-indigo-100">
          <CardHeader class="flex items-center gap-2">
            <Receipt class="h-5 w-5 text-blue-600" />
            <CardTitle class="text-gray-700">Last Transaction</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="lastTransaction" class="text-sm space-y-1">
              <p class="capitalize font-medium text-gray-800">
                {{ lastTransaction.transaction_type }}
                <span class="font-semibold text-indigo-600">
                  — KES
                  {{ Number(lastTransaction.amount).toLocaleString() }}
                </span>
              </p>
              <p class="text-gray-500 text-xs">
                {{ new Date(lastTransaction.created_at).toLocaleDateString() }}
              </p>
            </div>
            <p v-else class="text-gray-400 text-sm">No transactions yet</p>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <div
        class="flex flex-wrap gap-6 bg-white border border-gray-200 rounded-2xl shadow-sm p-4"
      >
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-gray-600 mb-1"
            >Account</label
          >
          <select
            v-model="filters.account_id"
            @change="applyFilters"
            class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Accounts</option>
            <option
              v-for="acc in props.accounts"
              :key="acc.id"
              :value="acc.id"
            >
              {{ acc.account_number }} ({{ acc.account_type }})
            </option>
          </select>
        </div>

        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-gray-600 mb-1">Type</label>
          <select
            v-model="filters.type"
            @change="applyFilters"
            class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">All Types</option>
            <option value="deposit">Deposit</option>
            <option value="withdraw">Withdraw</option>
            <option value="transfer">Transfer</option>
          </select>
        </div>
      </div>

      <div class="sm:hidden flex justify-end">
          <button
            @click="downloadStatement"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow transition"
          >
            <Download class="h-4 w-4" />
            Download
          </button>
        </div>
<!-- Transactions Table -->
      <div class="bg-white border border-gray-200 shadow-sm rounded-2xl overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-600">
          <thead class="bg-blue-50 text-xs uppercase font-semibold text-gray-500 sticky top-0 z-10">
            <tr>
              <th class="px-6 py-3">#</th>
              <th class="px-6 py-3">Account</th>
              <th class="px-6 py-3">Type</th>
              <th class="px-6 py-3">Amount</th>
              <th class="px-6 py-3">Processed By</th>
              <th class="px-6 py-3">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="t in props.transactions.data" :key="t.id" class="hover:bg-indigo-50/40 transition">
              <td class="px-6 py-4 font-medium text-gray-800">{{ t.id }}</td>
              <td class="px-6 py-4">{{ t.account?.account_number }}</td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 rounded-full text-xs font-semibold capitalize" :class="{
    'bg-green-100 text-green-700': t.transaction_type === 'deposit',
    'bg-red-100 text-red-700': t.transaction_type === 'withdrawal',
    'bg-blue-100 text-blue-700': t.transaction_type === 'transfer'
  }">
                  {{ t.transaction_type }}
                </span>
              </td>
              <td class="px-6 py-4 font-semibold">KES {{ Number(t.amount).toLocaleString() }}</td>
              <td class="px-6 py-4">{{ t.processed_by?.name || "System" }}</td>
              <td class="px-6 py-4 text-gray-500">
                {{ new Date(t.created_at).toLocaleString() }}
              </td>
            </tr>

            <tr v-if="!props.transactions.data.length">
              <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                <p class="text-lg">No transactions found.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex flex-col sm:flex-row justify-between items-center mt-6 gap-4">
        <div class="text-sm text-gray-600">
          Showing {{ props.transactions.data.length }} of {{ props.transactions.total }}
        </div>
        <div class="flex gap-2">
          <Link v-for="link in props.transactions.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
            class="px-3 py-1.5 rounded-lg text-sm border transition" :class="{
    'bg-indigo-600 text-white border-indigo-600 shadow': link.active,
    'text-gray-600 hover:bg-gray-100 border-gray-300': !link.active
  }" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
