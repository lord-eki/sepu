<template>
  <AppLayout :breadcrumbs="[
      { title: 'Accounts', href: route('accounts.index') },
      { title: 'Transactions' }
  ]">

    <Head title="Account Transactions" />

    <div class="py-8 bg-slate-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- HEADER -->
        <div
          class="rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between
          bg-gradient-to-r from-blue-900 to-blue-800 text-white shadow-xl">

          <div>
            <h2 class="text-xl font-bold tracking-tight">
              Account Transactions
            </h2>

            <p class="text-blue-200 text-sm mt-1">
              {{ account.account_number }} —
              {{ account.member.first_name }} {{ account.member.last_name }}
            </p>
          </div>

          <div class="flex gap-3 mt-4 sm:mt-0">

            <Link
              :href="route('accounts.show', account.id)"
              class="px-4 py-2 rounded-xl text-sm font-semibold
              bg-white text-blue-900 hover:bg-slate-100 transition shadow-sm">
              Back to Account
            </Link>

            <button
              @click="exportTransactions"
              class="px-4 py-2 rounded-xl text-sm font-semibold
              bg-orange-500 hover:bg-orange-600 text-white
              shadow-lg shadow-orange-500/20 transition">
              Export
            </button>

          </div>
        </div>


        <!-- FILTER CARD -->
        <div
          class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

          <h3 class="text-lg font-semibold text-slate-800 mb-6">
            Filter Transactions
          </h3>

          <form @submit.prevent="applyFilters" class="space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

              <div>
                <label class="text-sm font-medium text-slate-600">
                  Transaction Type
                </label>

                <select
                  v-model="filters.transaction_type"
                  class="mt-2 w-full rounded-xl border border-slate-300
                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                  px-3 py-2 text-sm">

                  <option value="">All Types</option>
                  <option
                    v-for="(label, value) in transactionTypes"
                    :key="value"
                    :value="value">

                    {{ label }}

                  </option>
                </select>
              </div>


              <div>
                <label class="text-sm font-medium text-slate-600">
                  Status
                </label>

                <select
                  v-model="filters.status"
                  class="mt-2 w-full rounded-xl border border-slate-300
                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                  px-3 py-2 text-sm">

                  <option value="">All Statuses</option>

                  <option
                    v-for="(label, value) in statuses"
                    :key="value"
                    :value="value">

                    {{ label }}

                  </option>

                </select>
              </div>


              <div>
                <label class="text-sm font-medium text-slate-600">
                  Date From
                </label>

                <input
                  v-model="filters.date_from"
                  type="date"
                  class="mt-2 w-full rounded-xl border border-slate-300
                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                  px-3 py-2 text-sm" />
              </div>


              <div>
                <label class="text-sm font-medium text-slate-600">
                  Date To
                </label>

                <input
                  v-model="filters.date_to"
                  type="date"
                  class="mt-2 w-full rounded-xl border border-slate-300
                  focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                  px-3 py-2 text-sm" />
              </div>

            </div>


            <div class="flex justify-between items-center">

              <button
                @click="clearFilters"
                type="button"
                class="px-4 py-2 rounded-xl border border-slate-300
                text-sm font-medium text-slate-600 hover:bg-slate-100">

                Clear Filters

              </button>

              <button
                type="submit"
                class="px-5 py-2 rounded-xl text-sm font-semibold
                bg-blue-900 text-white hover:bg-blue-800
                shadow-md transition">

                Apply Filters

              </button>

            </div>

          </form>
        </div>


        <!-- TRANSACTION TABLE -->
        <div
          class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

          <div
            class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">

            <h3 class="font-semibold text-slate-800">
              Transactions ({{ transactions.total }})
            </h3>

            <span class="text-sm text-slate-500">
              Showing {{ transactions.from || 0 }} –
              {{ transactions.to || 0 }}
            </span>

          </div>


          <div class="overflow-x-auto">

            <table class="w-full text-sm">

              <thead
                class="bg-slate-100 text-slate-600 uppercase text-xs">

                <tr>
                  <th class="px-6 py-3 text-left">Transaction</th>
                  <th class="px-6 py-3 text-left">Type</th>
                  <th class="px-6 py-3 text-left">Amount</th>
                  <th class="px-6 py-3 text-left">Balance</th>
                  <th class="px-6 py-3 text-left">Status</th>
                  <th class="px-6 py-3 text-left">Date</th>
                  <th class="px-6 py-3 text-left">Processed By</th>
                </tr>

              </thead>


              <tbody class="divide-y divide-slate-100">

                <tr
                  v-if="transactions.data.length === 0">

                  <td
                    colspan="7"
                    class="text-center py-8 text-slate-400">

                    No transactions found

                  </td>

                </tr>


                <tr
                  v-for="transaction in transactions.data"
                  :key="transaction.id"
                  class="hover:bg-slate-50 transition">

                  <!-- Transaction -->

                  <td class="px-6 py-4">

                    <div class="flex items-center">

                      <div
                        class="h-9 w-9 flex items-center justify-center
                        rounded-full"
                        :class="getTransactionIconClass(transaction.transaction_type)">

                        <svg
                          class="h-4 w-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24">

                          <path
                            v-if="isDebitTransaction(transaction.transaction_type)"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />

                          <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 16l-4-4m0 0l4-4m-4 4h18" />

                        </svg>

                      </div>

                      <div class="ml-4">

                        <p class="font-semibold text-slate-800">
                          {{ transaction.transaction_id }}
                        </p>

                        <p class="text-slate-500 text-sm">
                          {{ transaction.description }}
                        </p>

                      </div>

                    </div>

                  </td>


                  <!-- TYPE -->

                  <td class="px-6 py-4">

                    <span
                      class="px-3 py-1 text-xs rounded-full font-medium"
                      :class="getTransactionTypeBadgeClass(transaction.transaction_type)">

                      {{ transactionTypes[transaction.transaction_type] }}

                    </span>

                  </td>


                  <!-- AMOUNT -->

                  <td
                    class="px-6 py-4 font-semibold"
                    :class="isDebitTransaction(transaction.transaction_type) ? 'text-red-600' : 'text-emerald-600'">

                    {{ isDebitTransaction(transaction.transaction_type) ? '-' : '+' }}
                    {{ formatCurrency(transaction.amount) }}

                  </td>


                  <td class="px-6 py-4 text-slate-700">
                    {{ formatCurrency(transaction.balance_after) }}
                  </td>


                  <td class="px-6 py-4">

                    <span
                      class="px-3 py-1 text-xs rounded-full font-medium"
                      :class="getTransactionStatusClass(transaction.status)">

                      {{ statuses[transaction.status] }}

                    </span>

                  </td>


                  <td class="px-6 py-4 text-slate-500">
                    {{ formatDate(transaction.created_at) }}
                  </td>


                  <td class="px-6 py-4 text-slate-500">
                    {{ transaction.processed_by?.name || 'System' }}
                  </td>

                </tr>

              </tbody>

            </table>

          </div>


          <!-- PAGINATION -->

          <div
            v-if="transactions.links.length > 3"
            class="px-6 py-4 border-t border-slate-200">

            <nav class="flex justify-center gap-2">

              <template
                v-for="(link, index) in transactions.links"
                :key="index">

                <Link
                  v-if="link.url"
                  :href="link.url"
                  v-html="link.label"
                  class="px-3 py-1 rounded-lg border text-sm font-medium"
                  :class="[
                    link.active
                      ? 'bg-blue-900 text-white border-blue-900'
                      : 'bg-white border-slate-300 text-slate-600 hover:bg-slate-100'
                  ]" />

                <span
                  v-else
                  v-html="link.label"
                  class="px-3 py-1 rounded-lg border border-slate-200 text-slate-400" />

              </template>

            </nav>

          </div>

        </div>

      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  account: Object,
  transactions: Object,
  filters: Object,
  transactionTypes: Object,
  statuses: Object,
})

const filters = reactive({
  transaction_type: props.filters.transaction_type || '',
  status: props.filters.status || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const isDebitTransaction = (type) => {
  return ['withdrawal', 'share_sale', 'share_transfer_out', 'loan_repayment'].includes(type)
}

const getTransactionIconClass = (type) => isDebitTransaction(type) ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'

const getTransactionTypeBadgeClass = (type) => {
  const classes = {
    'deposit': 'bg-green-100 text-green-800',
    'withdrawal': 'bg-red-100 text-red-800',
    'share_purchase': 'bg-purple-100 text-purple-800',
    'share_sale': 'bg-orange-100 text-orange-800',
    'share_transfer_in': 'bg-blue-100 text-blue-800',
    'share_transfer_out': 'bg-indigo-100 text-indigo-800',
    'loan_disbursement': 'bg-yellow-100 text-yellow-800',
    'loan_repayment': 'bg-pink-100 text-pink-800',
    'dividend_payment': 'bg-cyan-100 text-cyan-800',
    'account_opening': 'bg-gray-100 text-gray-800',
    'account_closure': 'bg-gray-100 text-gray-800',
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getTransactionStatusClass = (status) => {
  const classes = {
    'completed': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'failed': 'bg-red-100 text-red-800',
    'reversed': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const applyFilters = () => {
  router.get(route('accounts.transactions', props.account.id), filters, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  filters.transaction_type = ''
  filters.status = ''
  filters.date_from = ''
  filters.date_to = ''
  applyFilters()
}

const exportTransactions = () => {
  const params = new URLSearchParams({
    ...filters,
    export: 'true'
  })
  window.open(`${route('accounts.transactions', props.account.id)}?${params}`, '_blank')
}
</script>