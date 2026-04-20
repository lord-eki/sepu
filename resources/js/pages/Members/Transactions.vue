<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3"
import { ref, computed } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent } from "@/components/ui/card"
import { FileText, Search } from "lucide-vue-next"

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

/* ================= STATE ================= */

// Filters
const filters = ref({
  type: props.filters.type || "",
  account_id: props.filters.account_id || "",
  search: props.filters.search || ""
})

const formatDate = (date: string) => {
  if (!date) return ''

  return new Date(date).toLocaleDateString('en-KE', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

// Statement Dates
const today = new Date()
const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)

const fromDate = ref(startOfMonth.toISOString().split('T')[0])
const toDate = ref(today.toISOString().split('T')[0])

// Modal
const showModal = ref(false)
const selectedTransaction = ref<any>(null)

/* ================= COMPUTED ================= */

const totalTransactions = computed(() => props.transactions.total)

const totalAmount = computed(() =>
  props.transactions.data.reduce((sum, t) => sum + Number(t.amount || 0), 0)
)

const lastTransaction = computed(() =>
  props.transactions.data.length ? props.transactions.data[0] : null
)

/* ================= METHODS ================= */

// Apply filters
function applyFilters() {
  router.get(route("members.transactions", props.member.id), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

// View statement page
function viewStatement() {
  router.get(route('my-transactions.statement'), {
    from: fromDate.value || undefined,
    to: toDate.value || undefined
  })
}

// Modal
function openTransactionModal(transaction: any) {
  selectedTransaction.value = transaction
  showModal.value = true
}

function closeModal() {
  selectedTransaction.value = null
  showModal.value = false
}

/* ================= HELPERS ================= */

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-KE').format(amount)

const formatDateTime = (date: string) =>
  new Date(date).toLocaleString('en-KE')

</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/my-transactions' }]">

    <Head title="Transactions" />

    <div class="p-6 space-y-8 bg-gradient-to-br from-white via-gray-50 to-gray-100 min-h-screen">

    
      <!-- ================= HEADER ================= -->
      <div class="relative overflow-hidden rounded-2xl shadow-xl">

        <!-- Background Gradient -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#0a2342] via-blue-900 to-orange-500"></div>

        <!-- Decorative Glow -->
        <div class="absolute -top-12 -right-12 w-52 h-52 bg-orange-400/30 blur-3xl rounded-full"></div>
        <div class="absolute -bottom-12 -left-12 w-52 h-52 bg-blue-400/20 blur-3xl rounded-full"></div>

        <!-- Content -->
        <div class="relative z-10 p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

          <!-- LEFT: Title Section -->
          <div class="space-y-2">

            <h1 class="text-2xl font-semibold text-white tracking-tight">
              Transactions
            </h1>

            <p class="text-sm text-blue-100 max-w-md">
              Track deposits, withdrawals, transfers, and generate detailed financial statements.
            </p>

           

          </div>

          <!-- RIGHT: Controls -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 w-full lg:w-auto">
            
            <!-- DATE PICKER GROUP -->
            <div class="flex items-center gap-2 bg-white/10 backdrop-blur-lg px-3 py-2 rounded-xl border border-white/20 shadow-inner">

              <input
                type="date"
                v-model="fromDate"
                class="bg-transparent text-white text-sm focus:outline-none"
              />

              <span class="text-white/60 text-sm">→</span>

              <input
                type="date"
                v-model="toDate"
                class="bg-transparent text-white text-sm focus:outline-none"
              />

            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex gap-2">

    

              <!-- VIEW STATEMENT -->
              <button
                @click="viewStatement"
                class="flex items-center gap-2 px-4 py-2 bg-white text-[#0a2342] rounded-lg shadow hover:shadow-lg hover:scale-[1.02] transition font-medium"
              >
                <FileText class="w-4 h-4" />
                View Statement
              </button>

            </div>

          </div>

        </div>
      </div>

      <!-- ================= SUMMARY ================= -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <Card class="rounded-2xl shadow-sm">
          <CardContent class="p-5">
            <p class="text-xs text-gray-500">Total Amount</p>
            <p class="text-lg font-semibold text-[#0a2342]">
              KES {{ formatCurrency(totalAmount) }}
            </p>
          </CardContent>
        </Card>

        <Card class="rounded-2xl shadow-sm">
          <CardContent class="p-5">
            <p class="text-xs text-gray-500">Transactions</p>
            <p class="text-lg font-semibold text-[#0a2342]">
              {{ totalTransactions }}
            </p>
          </CardContent>
        </Card>

        <Card class="rounded-2xl shadow-sm">
          <CardContent class="p-5">
            <p class="text-xs text-gray-500">Last Activity</p>
            <p v-if="lastTransaction" class="text-sm font-medium text-gray-700">
              {{ lastTransaction.transaction_type }} •
              KES {{ formatCurrency(lastTransaction.amount) }}
            </p>
            <p v-else class="text-sm text-gray-400">No activity</p>
          </CardContent>
        </Card>

      </div>

      <!-- ================= FILTERS ================= -->
      <div class="bg-white rounded-2xl shadow-sm border p-4 flex flex-wrap gap-4 items-end">

        <div class="flex flex-col min-w-[180px]">
          <label class="text-xs text-gray-500 mb-1">Account</label>
          <select v-model="filters.account_id" @change="applyFilters"
            class="border rounded-lg px-3 py-2 text-sm">
            <option value="">All</option>
            <option v-for="acc in props.accounts" :key="acc.id" :value="acc.id">
              {{ acc.account_number }}
            </option>
          </select>
        </div>

        <div class="flex flex-col min-w-[160px]">
          <label class="text-xs text-gray-500 mb-1">Type</label>
          <select v-model="filters.type" @change="applyFilters"
            class="border rounded-lg px-3 py-2 text-sm">
            <option value="">All</option>
            <option value="deposit">Deposit</option>
            <option value="withdrawal">Withdraw</option>
            <option value="transfer">Transfer</option>
          </select>
        </div>

        <div class="flex-1 min-w-[200px] relative">
          <Search class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
          <input v-model="filters.search" @keyup.enter="applyFilters"
            placeholder="Search..."
            class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm" />
        </div>

      </div>

      <!-- ================= TABLE ================= -->
      <div class="bg-white rounded-2xl shadow border overflow-x-auto">

        <table class="w-full text-sm">

      
          <thead class="bg-gray-100 text-xs uppercase">
            <tr>
              <th class="p-3 text-left">#</th>
              <th class="p-3 text-left">Account</th>
              <th class="p-3">Type</th>
              <th class="p-3 text-right">Amount</th>
              <th class="p-3 text-right">Balance</th>
              <th class="p-3">Status</th>
              <th class="p-3">User</th>
              <th class="p-3">Date</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(t, i) in props.transactions.data"
              :key="t.id"
              class="border-b hover:bg-gray-50 cursor-pointer"
              @click="openTransactionModal(t)"
            >
              <td class="p-3">{{ i + 1 }}</td>
              <td class="p-3">{{ t.account?.account_number }}</td>

              <!-- TYPE -->
              <td class="p-3 capitalize">
                <span
                  class="px-2 py-1 text-xs rounded-full"
                  :class="{
                    'bg-green-100 text-green-700': t.transaction_type === 'deposit',
                    'bg-red-100 text-red-700': t.transaction_type === 'withdrawal',
                    'bg-blue-100 text-blue-700': t.transaction_type === 'transfer'
                  }"
                >
                  {{ t.transaction_type }}
                </span>
              </td>

              <!-- AMOUNT -->
              <td class="p-3 text-right font-semibold">
                KES {{ formatCurrency(t.amount) }}
              </td>

              <!-- BALANCE -->
              <td class="p-3 text-right text-gray-700">
                KES {{ formatCurrency(t.balance_after) }}
              </td>

              <!-- STATUS -->
              <td class="p-3 text-center">
                <span
                  class="px-2 py-1 text-xs rounded-full font-medium"
                  :class="{
                    'bg-green-100 text-green-700': t.status === 'completed',
                    'bg-yellow-100 text-yellow-700': t.status === 'pending',
                    'bg-red-100 text-red-700': t.status === 'failed',
                    'bg-gray-100 text-gray-700': !t.status
                  }"
                >
                  {{ t.status || 'completed' }}
                </span>
              </td>

              <!-- USER -->
              <td class="p-3">{{ t.processed_by?.name || 'System' }}</td>

              <!-- DATE -->
              <td class="p-3 text-gray-500">
                {{ formatDateTime(t.created_at) }}
              </td>
            </tr>

            <tr v-if="!props.transactions.data.length">
              <td colspan="8" class="text-center py-8 text-gray-500">
                No transactions found
              </td>
            </tr>
          </tbody>

        </table>

      </div>

      <!-- ================= PAGINATION ================= -->
      <div class="flex flex-col sm:flex-row justify-between items-center gap-4">

        <p class="text-sm text-gray-600">
          Showing {{ props.transactions.data.length }} of {{ props.transactions.total }}
        </p>

        <div class="flex gap-2 flex-wrap">
          <Link v-for="link in props.transactions.links" :key="link.label"
            :href="link.url || '#'" v-html="link.label"
            class="px-3 py-1.5 rounded-lg text-sm border transition"
            :class="{
              'bg-[#0a2342] text-white border-[#0a2342]': link.active,
              'text-gray-600 hover:bg-gray-100 border-gray-300': !link.active
            }" />
        </div>

      </div>

      <!-- ================= MODAL ================= -->
      <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative">

          <button @click="closeModal"
            class="absolute top-4 right-4 text-gray-500 hover:text-gray-800">✕</button>

          <h2 class="text-lg font-semibold mb-4">Transaction Details</h2>

          <div class="space-y-2 text-sm text-gray-700">
            <p><strong>ID:</strong> {{ selectedTransaction.id }}</p>
            <p><strong>Account:</strong> {{ selectedTransaction.account?.account_number }}</p>
            <p><strong>Type:</strong> {{ selectedTransaction.transaction_type }}</p>
            <p><strong>Amount:</strong> KES {{ formatCurrency(selectedTransaction.amount) }}</p>
            <p><strong>Balance:</strong> KES {{ formatCurrency(selectedTransaction.balance_after) }}</p>
            <p><strong>User:</strong> {{ selectedTransaction.processed_by?.name || 'System' }}</p>
            <p><strong>Date:</strong> {{ formatDateTime(selectedTransaction.created_at) }}</p>
          </div>

        </div>
      </div>

    </div>
  </AppLayout>
</template>