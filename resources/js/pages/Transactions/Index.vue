<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: route('transactions.index') }]">
    <Head title="Transactions" />

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center p-5 md:justify-between mb-6">
      <h1 class="text-2xl font-bold text-blue-900">Transactions</h1>

      <!-- Search & Filters -->
      <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
        <input
          v-model="filters.search"
          type="text"
          placeholder="Search transaction..."
          class="border border-blue-300 focus:ring-2 focus:ring-orange-500 rounded-lg px-3 py-2 w-60 text-sm text-gray-700"
        />

        <select
          v-model="filters.transaction_type"
          class="border border-blue-300 focus:ring-2 focus:ring-orange-500 rounded-lg px-3 py-2 text-sm text-gray-700"
        >
          <option value="">All Types</option>
          <option v-for="(label, key) in transactionTypes" :key="key" :value="key">
            {{ label }}
          </option>
        </select>

        <select
          v-model="filters.status"
          class="border border-blue-300 focus:ring-2 focus:ring-orange-500 rounded-lg px-3 py-2 text-sm text-gray-700"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="completed">Completed</option>
          <option value="failed">Failed</option>
          <option value="reversed">Reversed</option>
        </select>

        <button
          @click="applyFilters"
          class="bg-orange-500 hover:bg-orange-600 transition-colors text-white text-sm rounded-lg px-4 py-2 font-medium shadow"
        >
          Apply
        </button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid md:grid-cols-5 sm:grid-cols-2 gap-4 mb-8 px-5">
      <div
        v-for="(value, key) in statistics"
        :key="key"
        class="bg-white shadow-md rounded-2xl p-4 border-t-2"
        :class="{
          'border-orange-500': key.includes('pending'),
          'border-green-500': key.includes('completed'),
          'border-blue-700': key.includes('total'),
          'border-red-500': key.includes('failed'),
          'border-gray-400': key.includes('reversed'),
        }"
      >
        <p class="text-sm text-gray-500 capitalize">{{ key.replaceAll('_', ' ') }}</p>
        <p class="text-xl font-semibold text-blue-900">
          {{ formatNumber(value) }}
        </p>
      </div>
    </div>

    <!-- Transactions Table -->
    <div
      class="overflow-x-auto bg-white rounded-2xl mx-5 shadow border border-blue-100"
    >
      <table class="min-w-full divide-y divide-blue-100">
        <thead class="bg-blue-900 text-white">
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
            class="hover:bg-orange-50 transition-colors"
          >
            <td class="px-4 py-3 text-sm text-gray-800">{{ txn.transaction_id }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">
              {{ txn.member?.first_name }} {{ txn.member?.last_name }}
            </td>
            <td class="px-4 py-3 text-sm capitalize text-gray-700">
              {{ txn.transaction_type.replace('_', ' ') }}
            </td>
            <td class="px-4 py-3 text-sm font-semibold text-blue-900">
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
            <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(txn.created_at) }}</td>
            <td class="px-4 py-3 text-right">
              <button
                @click="viewTransaction(txn.id)"
                class="text-orange-500 hover:text-orange-700 text-sm font-medium transition"
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
    <div class="flex justify-between items-center mt-6 text-sm text-gray-700">
      <p>Showing {{ transactions.from || 0 }} - {{ transactions.to || 0 }} of {{ transactions.total }}</p>

      <div class="flex gap-2">
        <button
          :disabled="!transactions.prev_page_url"
          @click="goToPage(transactions.current_page - 1)"
          class="px-3 py-1 rounded-md border border-blue-700 text-blue-800 hover:bg-blue-900 hover:text-white transition disabled:opacity-50"
        >
          Prev
        </button>
        <button
          :disabled="!transactions.next_page_url"
          @click="goToPage(transactions.current_page + 1)"
          class="px-3 py-1 rounded-md border border-blue-700 text-blue-800 hover:bg-blue-900 hover:text-white transition disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

// Use props from Laravel Inertia response
const page = usePage()
const transactions = ref(page.props.transactions)
const statistics = ref(page.props.statistics)
const transactionTypes = ref(page.props.transaction_types ?? {})

const filters = ref({
  search: page.props.filters?.search || '',
  transaction_type: page.props.filters?.transaction_type || '',
  status: page.props.filters?.status || '',
})

// Apply filters (reloads Inertia page)
const applyFilters = () => {
  router.get(route('transactions.index'), filters.value, { preserveState: true })
}

const goToPage = (pageNumber: number) => {
  router.get(route('transactions.index'), { ...filters.value, page: pageNumber }, { preserveState: true })
}

const viewTransaction = (id: number) => {
  router.visit(route('transactions.show', id))
}

// Helpers
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
