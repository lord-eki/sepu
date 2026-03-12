<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { ref, computed } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Wallet, Receipt, FileText, Download, Search } from "lucide-vue-next"

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
    search?: string
  }
}>()

// Filters
const filters = ref({
  type: props.filters.type || "",
  account_id: props.filters.account_id || "",
  search: props.filters.search || ""
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

// Modal
const showModal = ref(false)
const selectedTransaction = ref<any>(null)

function openTransactionModal(transaction: any) {
  selectedTransaction.value = transaction
  showModal.value = true
}

function closeModal() {
  selectedTransaction.value = null
  showModal.value = false
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/my-transactions' }]">

    <Head title="Transactions" />

    <div class="space-y-10 p-6 bg-gradient-to-br from-white via-gray-50 to-gray-100 min-h-screen">

      <!-- Header -->
      <div
        class="flex flex-col sm:flex-row bg-gradient-to-br from-[#0a2342] via-blue-900 to-orange-500 py-5 px-6 rounded-2xl sm:items-center sm:justify-between gap-4 shadow-xl">
        <div>
          <h1 class="text-xl sm:text-2xl font-semibold text-white">Transactions</h1>
          <p class="text-sm text-gray-200 mt-1">
            Review your transaction history, filter by account, type, or search by user, and download your statement.
          </p>
        </div>

        <button @click="downloadStatement"
          class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-white text-[#0a2342] rounded-lg shadow hover:shadow-lg transition">
          <Download class="w-4 h-4" /> Download Statement
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card class="hover:shadow-2xl transition rounded-2xl border border-gray-200 bg-white">
          <CardHeader class="flex items-center gap-2">
            <Wallet class="h-5 w-5 text-blue-700" />
            <CardTitle>Total Amount</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-lg font-semibold text-[#0a2342]">
              KES {{ Number(totalAmount).toLocaleString() }}
            </p>
          </CardContent>
        </Card>

        <Card class="hover:shadow-2xl transition rounded-2xl border border-gray-200 bg-white">
          <CardHeader class="flex items-center gap-2">
            <Receipt class="h-5 w-5 text-orange-600" />
            <CardTitle>Last Transaction</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="lastTransaction" class="text-sm space-y-1">
              <p class="capitalize font-medium text-gray-800">
                {{ lastTransaction.transaction_type }}
                <span class="font-semibold text-orange-600">
                  — KES {{ Number(lastTransaction.amount).toLocaleString() }}
                </span>
              </p>
              <p class="text-gray-500 text-xs">
                {{ new Date(lastTransaction.created_at).toLocaleDateString() }}
              </p>
            </div>
            <p v-else class="text-gray-400 text-sm">No transactions yet</p>
          </CardContent>
        </Card>

        <Card class="hover:shadow-2xl transition rounded-2xl border border-gray-200 bg-white">
          <CardHeader class="flex items-center gap-2">
            <FileText class="h-5 w-5 text-green-600" />
            <CardTitle>Total Transactions</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-lg font-semibold text-[#0a2342]">{{ totalTransactions }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Filters + Search -->
      <div class="flex flex-wrap gap-6 bg-white border border-gray-200 rounded-2xl shadow-md p-4">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-[#081642] mb-1">Account</label>
          <select v-model="filters.account_id" @change="applyFilters"
            class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500">
            <option value="">All Accounts</option>
            <option v-for="acc in props.accounts" :key="acc.id" :value="acc.id">
              {{ acc.account_number }} ({{ acc.account_type }})
            </option>
          </select>
        </div>

        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-[#081642] mb-1">Type</label>
          <select v-model="filters.type" @change="applyFilters"
            class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500">
            <option value="">All Types</option>
            <option value="deposit">Deposit</option>
            <option value="withdraw">Withdraw</option>
            <option value="transfer">Transfer</option>
          </select>
        </div>

        <div class="flex-1 min-w-[200px] relative">
          <label class="block text-sm font-medium text-[#081642] mb-1">Search</label>
          <div class="relative">
            <Search class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
            <input v-model="filters.search" @keyup.enter="applyFilters" placeholder="Search by user..."
              class="w-full border-gray-300 rounded-lg px-10 py-2 text-sm focus:ring-orange-500 focus:border-orange-500" />
          </div>
        </div>
      </div>

      <!-- Transactions Table -->
      <div class="bg-white border border-gray-200 shadow-lg rounded-2xl overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-600">
          <thead class="bg-[#081f42]/10 text-xs uppercase font-semibold text-[#081642] sticky top-0 z-10">
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
            <tr v-for="(t, index) in props.transactions.data" :key="t.id"
              class="hover:bg-orange-50 cursor-pointer transition" @click="openTransactionModal(t)">
              <td class="px-6 py-4 font-medium text-gray-800" title="Click number to view">{{ index + 1 }}</td>
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
              <td class="px-6 py-4 text-gray-500">{{ new Date(t.created_at).toLocaleString() }}</td>
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
        <div class="flex gap-2 flex-wrap">
          <Link v-for="link in props.transactions.links" :key="link.label" :href="link.url || '#'" v-html="link.label"
            class="px-3 py-1.5 rounded-lg text-sm border transition" :class="{
    'bg-[#0a2342] text-white border-[#0a2342] shadow': link.active,
    'text-gray-600 hover:bg-gray-100 border-gray-300': !link.active
  }" />
        </div>
      </div>

      <!-- Transaction Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-6 relative">
          <button @click="closeModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">X</button>
          <h2 class="text-lg font-semibold mb-4">Transaction Details</h2>
          <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <p><strong>ID:</strong> {{ selectedTransaction.id }}</p>
            <p><strong>Account:</strong> {{ selectedTransaction.account?.account_number }}</p>
            <p><strong>Type:</strong> {{ selectedTransaction.transaction_type }}</p>
            <p><strong>Amount:</strong> KES {{ Number(selectedTransaction.amount).toLocaleString() }}</p>
            <p><strong>Processed By:</strong> {{ selectedTransaction.processed_by?.name || "System" }}</p>
            <p><strong>Date:</strong> {{ new Date(selectedTransaction.created_at).toLocaleString() }}</p>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>