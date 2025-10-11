<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: route('transactions.index') }]">
    <Head title="Transactions" />

    <!-- Header Section -->
    <div
      class="bg-gradient-to-r from-[#0a2342] via-blue-900 to-orange-500 rounded-2xl shadow-md p-5 mx-5 mt-6 flex flex-col md:flex-row md:items-center md:justify-between"
    >
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-white">Transactions</h1>
        <p class="text-sm text-blue-100">Manage and record your SACCO transactions easily</p>
      </div>

      <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
        <button
          @click="showMakeTxnModal = true"
          class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-5 py-2 rounded-full shadow transition"
        >
          + Make Transaction
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 mx-5 flex flex-wrap gap-3 justify-between bg-white shadow-sm p-4 rounded-xl border border-blue-100">
      <div class="flex flex-wrap gap-3">
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search transaction..."
          class="border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-blue-700 rounded-full px-4 py-2 w-60 text-sm"
        />

        <select
          v-model="filters.transaction_type"
          class="border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-blue-700 rounded-full px-4 py-2 text-sm"
        >
          <option value="">All Types</option>
          <option v-for="(label, key) in transactionTypes" :key="key" :value="key">
            {{ label }}
          </option>
        </select>

        <select
          v-model="filters.status"
          class="border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-blue-700 rounded-full px-4 py-2 text-sm"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="completed">Completed</option>
          <option value="failed">Failed</option>
          <option value="reversed">Reversed</option>
        </select>

        <button
          @click="applyFilters"
          class="bg-blue-900 hover:bg-blue-800 hover:cursor-pointer text-white text-sm font-semibold rounded-full px-6 py-2 transition"
        >
          Apply
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid md:grid-cols-3 sm:grid-cols-2 gap-4 mb-8 mt-8 px-5">
      <div
        v-for="(value, key) in statistics"
        :key="key"
        class="bg-white border-l-2 p-5 rounded-2xl shadow hover:shadow-lg transition-all"
        :class="{
          'border-orange-500': key.includes('pending'),
          'border-green-500': key.includes('completed'),
          'border-blue-700': key.includes('total'),
          'border-red-500': key.includes('failed'),
          'border-gray-400': key.includes('reversed'),
        }"
      >
        <p class="text-sm text-gray-500 capitalize">{{ key.replaceAll('_', ' ') }}</p>
        <p class="text-xl font-semibold text-[#0a2342] mt-1">{{ formatNumber(value) }}</p>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="overflow-x-auto bg-white rounded-2xl mx-5 shadow border border-blue-100">
      <table class="min-w-full divide-y divide-blue-100">
        <thead class="bg-blue-100 text-[#0a2342]">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold">Txn ID</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Member</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Type</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Amount</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
            <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
            <th class="px-4 py-3 text-right text-sm font-semibold">Actions</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-blue-50">
          <tr
            v-for="txn in transactions.data"
            :key="txn.id"
            class="hover:bg-orange-50 transition"
          >
            <td class="px-4 py-3 text-sm text-gray-800">{{ txn.transaction_id }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">
              {{ txn.member?.first_name }} {{ txn.member?.last_name }}
            </td>
            <td class="px-4 py-3 text-sm capitalize text-gray-700">
              {{ txn.transaction_type.replace('_', ' ') }}
            </td>
            <td class="px-4 py-3 text-sm font-semibold text-[#0a2342]">
              {{ formatCurrency(txn.amount) }}
            </td>
            <td class="px-4 py-3">
              <span
                :class="statusClass(txn.status)"
                class="px-2 py-1 text-xs font-semibold rounded-full"
              >
                {{ txn.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">
              {{ formatDate(txn.created_at) }}
            </td>
            <td class="px-4 py-3 text-right">
              <button
                @click="viewTransaction(txn.id)"
                class="text-orange-500 hover:text-orange-700 text-sm font-medium"
              >
                View
              </button>
            </td>
          </tr>

          <tr v-if="transactions.data.length === 0">
            <td colspan="7" class="text-center py-6 text-gray-500">
              No transactions found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-between items-center mt-8 mb-10 text-sm text-gray-700 px-5">
      <p>
        Showing {{ transactions.from || 0 }} - {{ transactions.to || 0 }} of {{ transactions.total }}
      </p>

      <div class="flex gap-2">
        <button
          :disabled="!transactions.prev_page_url"
          @click="goToPage(transactions.current_page - 1)"
          class="px-4 py-1.5 rounded-full border border-blue-700 text-blue-800 hover:bg-blue-900 hover:text-white transition disabled:opacity-50"
        >
          Prev
        </button>
        <button
          :disabled="!transactions.next_page_url"
          @click="goToPage(transactions.current_page + 1)"
          class="px-4 py-1.5 rounded-full border border-blue-700 text-blue-800 hover:bg-blue-900 hover:text-white transition disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Make Transaction Modal -->
    <div
      v-if="showMakeTxnModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-2xl w-full max-w-md shadow-lg p-6 relative">
        <h2 class="text-xl font-semibold text-blue-900 mb-4">Make a Transaction</h2>

        <div class="space-y-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Transaction Type</label>
            <select
              v-model="newTxn.type"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-700"
            >
              <option disabled value="">Select Type</option>
              <option value="deposit">Deposit</option>
              <option value="withdraw">Withdraw</option>
            </select>
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Amount (KES)</label>
            <input
              v-model="newTxn.amount"
              type="number"
              placeholder="Enter amount"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-700"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-600 mb-1">Description</label>
            <textarea
              v-model="newTxn.description"
              rows="3"
              placeholder="Optional note..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-700"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="showMakeTxnModal = false"
            class="px-4 py-2 rounded-full border border-gray-400 text-gray-600 hover:bg-gray-100"
          >
            Cancel
          </button>
          <button
            @click="submitTransaction"
            class="px-5 py-2 rounded-full bg-blue-900 text-white font-semibold hover:bg-blue-800 transition"
          >
            Submit
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const page = usePage()
const transactions = ref(page.props.transactions)
const statistics = ref(page.props.statistics)
const transactionTypes = ref(page.props.transaction_types ?? {})

const filters = ref({
  search: page.props.filters?.search || '',
  transaction_type: page.props.filters?.transaction_type || '',
  status: page.props.filters?.status || '',
})

const showMakeTxnModal = ref(false)
const newTxn = ref({ type: '', amount: '', description: '' })

const applyFilters = () => {
  router.get(route('transactions.index'), filters.value, { preserveState: true })
}

const goToPage = (pageNumber: number) => {
  router.get(route('transactions.index'), { ...filters.value, page: pageNumber }, { preserveState: true })
}

const viewTransaction = (id: number) => {
  router.visit(route('transactions.show', id))
}

const submitTransaction = () => {
  if (!newTxn.value.type || !newTxn.value.amount) {
    alert('Please fill in transaction type and amount.')
    return
  }
  // Placeholder: integrate backend later
  console.log('Submitting transaction:', newTxn.value)
  showMakeTxnModal.value = false
  newTxn.value = { type: '', amount: '', description: '' }
}

const statusClass = (status: string) => ({
  'bg-yellow-100 text-yellow-800': status === 'pending',
  'bg-green-100 text-green-800': status === 'completed',
  'bg-red-100 text-red-800': status === 'failed',
  'bg-gray-200 text-gray-800': status === 'reversed',
})

const formatCurrency = (amount: number) =>
  new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount)

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString('en-GB', { year: 'numeric', month: 'short', day: 'numeric' })

const formatNumber = (n: number) => (n ? n.toLocaleString() : 0)
</script>
