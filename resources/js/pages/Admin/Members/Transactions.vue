<template>
  <AppLayout :title="`${member.first_name} ${member.last_name} - Transactions`">
      <div class="flex items-center">
        <Link :href="route('members.show', member.id)" class="mr-4">
          <ArrowLeftIcon class="w-5 h-5" />
        </Link>
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ member.first_name }} {{ member.last_name }} - Transactions
          </h2>
          <p class="text-sm text-gray-600">Member ID: {{ member.membership_id }}</p>
        </div>
      </div>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Member Info Card -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="px-6 py-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-12 w-12">
                <img
                  v-if="member.profile_photo"
                  :src="`/storage/${member.profile_photo}`"
                  :alt="member.first_name"
                  class="h-12 w-12 rounded-full object-cover"
                />
                <div
                  v-else
                  class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center"
                >
                  <UserIcon class="h-8 w-8 text-gray-600" />
                </div>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-medium text-gray-900">
                  {{ member.first_name }} {{ member.last_name }}
                </h3>
                <p class="text-sm text-gray-500">{{ member.membership_id }}</p>
              </div>
              <div class="ml-auto">
                <span
                  :class="{
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                    'bg-green-100 text-green-800': member.membership_status === 'active',
                    'bg-red-100 text-red-800': member.membership_status === 'inactive',
                    'bg-yellow-100 text-yellow-800': member.membership_status === 'suspended'
                  }"
                >
                  {{ member.membership_status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Transaction Type</label>
                <select
                  v-model="form.type"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  @change="search"
                >
                  <option value="">All Types</option>
                  <option value="deposit">Deposit</option>
                  <option value="withdrawal">Withdrawal</option>
                  <option value="transfer">Transfer</option>
                  <option value="loan_disbursement">Loan Disbursement</option>
                  <option value="loan_repayment">Loan Repayment</option>
                  <option value="dividend">Dividend Payment</option>
                  <option value="fee">Fee</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Account</label>
                <select
                  v-model="form.account_id"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  @change="search"
                >
                  <option value="">All Accounts</option>
                  <option v-for="account in accounts" :key="account.id" :value="account.id">
                    {{ account.account_number }} ({{ capitalize(account.account_type) }})
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Date From</label>
                <input
                  v-model="form.date_from"
                  type="date"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                  @change="search"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Date To</label>
                <input
                  v-model="form.date_to"
                  type="date"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                  @change="search"
                />
              </div>
            </div>

            <div class="mt-4 flex space-x-3">
              <button
                @click="clearFilters"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                Clear Filters
              </button>
              <button
                @click="exportTransactions"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
              >
                <DocumentArrowDownIcon class="w-4 h-4 mr-2" />
                Export
              </button>
            </div>
          </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date & Time
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Transaction ID
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Account
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Amount
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Balance After
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Description
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Processed By
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <div>{{ formatDate(transaction.transaction_date) }}</div>
                    <div class="text-xs text-gray-500">{{ formatTime(transaction.transaction_date) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    TXN-{{ transaction.id.toString().padStart(6, '0') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span
                      :class="{
                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                        'bg-green-100 text-green-800': transaction.transaction_type === 'deposit' || transaction.transaction_type === 'credit',
                        'bg-red-100 text-red-800': transaction.transaction_type === 'withdrawal' || transaction.transaction_type === 'debit',
                        'bg-blue-100 text-blue-800': transaction.transaction_type === 'transfer',
                        'bg-purple-100 text-purple-800': transaction.transaction_type === 'loan_disbursement',
                        'bg-orange-100 text-orange-800': transaction.transaction_type === 'loan_repayment',
                        'bg-indigo-100 text-indigo-800': transaction.transaction_type === 'dividend',
                        'bg-gray-100 text-gray-800': transaction.transaction_type === 'fee'
                      }"
                    >
                      {{ capitalize(transaction.transaction_type.replace('_', ' ')) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div>{{ transaction.account?.account_number }}</div>
                    <div class="text-xs text-gray-400">{{ capitalize(transaction.account?.account_type) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium"
                      :class="{
                        'text-green-600': transaction.transaction_type === 'credit' || transaction.transaction_type === 'deposit' || transaction.transaction_type === 'loan_disbursement' || transaction.transaction_type === 'dividend',
                        'text-red-600': transaction.transaction_type === 'debit' || transaction.transaction_type === 'withdrawal' || transaction.transaction_type === 'loan_repayment' || transaction.transaction_type === 'fee'
                      }">
                    <div>
                      {{ ['credit', 'deposit', 'loan_disbursement', 'dividend'].includes(transaction.transaction_type) ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                    {{ formatCurrency(transaction.balance_after) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span
                      :class="{
                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                        'bg-green-100 text-green-800': transaction.status === 'completed',
                        'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                        'bg-red-100 text-red-800': transaction.status === 'failed',
                        'bg-blue-100 text-blue-800': transaction.status === 'processing'
                      }"
                    >
                      {{ transaction.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                    {{ transaction.description || transaction.reference || '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ transaction.processed_by?.name || 'System' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      @click="viewTransaction(transaction)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      View Details
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="transactions.data.length === 0" class="text-center py-12">
            <DocumentIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions</h3>
            <p class="mt-1 text-sm text-gray-500">No transactions found for the selected filters.</p>
          </div>

          <!-- Pagination -->
          <Pagination v-if="transactions.links && transactions.data.length > 0" :links="transactions.links" />
        </div>

        <!-- Transaction Summary -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <ArrowUpIcon class="h-6 w-6 text-green-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Credits
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ formatCurrency(transactionSummary.total_credits) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <ArrowDownIcon class="h-6 w-6 text-red-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Debits
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ formatCurrency(transactionSummary.total_debits) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <DocumentTextIcon class="h-6 w-6 text-blue-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Transaction Count
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ transactions.total || 0 }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <ScaleIcon class="h-6 w-6 text-indigo-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Net Change
                    </dt>
                    <dd class="text-lg font-medium"
                        :class="{
                          'text-green-600': transactionSummary.net_change >= 0,
                          'text-red-600': transactionSummary.net_change < 0
                        }">
                      {{ formatCurrency(transactionSummary.net_change) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Transaction Detail Modal -->
    <TransactionModal
      v-if="selectedTransaction"
      :transaction="selectedTransaction"
      @close="selectedTransaction = null"
    />
  </AppLayout>
</template>

<script>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import TransactionModal from '@/Components/TransactionModal.vue'
import {
  ArrowLeftIcon,
  UserIcon,
  DocumentArrowDownIcon,
  DocumentIcon,
  DocumentTextIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ScaleIcon
} from '@heroicons/vue/24/outline'

export default {
  components: {
    AppLayout,
    Head,
    Link,
    Pagination,
    TransactionModal,
    ArrowLeftIcon,
    UserIcon,
    DocumentArrowDownIcon,
    DocumentIcon,
    DocumentTextIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ScaleIcon
  },
  props: {
    member: Object,
    transactions: Object,
    accounts: Array,
    filters: Object
  },
  setup(props) {
    const selectedTransaction = ref(null)

    const form = ref({
      type: props.filters.type || '',
      account_id: props.filters.account_id || '',
      date_from: props.filters.date_from || '',
      date_to: props.filters.date_to || ''
    })

    const transactionSummary = computed(() => {
      const credits = props.transactions.data
        .filter(t => ['credit', 'deposit', 'loan_disbursement', 'dividend'].includes(t.transaction_type))
        .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0)

      const debits = props.transactions.data
        .filter(t => ['debit', 'withdrawal', 'loan_repayment', 'fee'].includes(t.transaction_type))
        .reduce((sum, t) => sum + parseFloat(t.amount || 0), 0)

      return {
        total_credits: credits,
        total_debits: debits,
        net_change: credits - debits
      }
    })

    const search = debounce(() => {
      router.get(route('members.transactions', props.member.id), form.value, {
        preserveState: true,
        replace: true
      })
    }, 300)

    const clearFilters = () => {
      form.value = {
        type: '',
        account_id: '',
        date_from: '',
        date_to: ''
      }
      search()
    }

    const exportTransactions = () => {
      const params = new URLSearchParams(form.value)
      window.open(`${route('members.transactions', props.member.id)}?${params}&export=csv`)
    }

    const viewTransaction = (transaction) => {
      selectedTransaction.value = transaction
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString()
    }

    const formatTime = (date) => {
      return new Date(date).toLocaleTimeString()
    }

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
      }).format(amount || 0)
    }

    const capitalize = (str) => {
      if (!str) return ''
      return str.charAt(0).toUpperCase() + str.slice(1)
    }

    return {
      form,
      selectedTransaction,
      transactionSummary,
      search,
      clearFilters,
      exportTransactions,
      viewTransaction,
      formatDate,
      formatTime,
      formatCurrency,
      capitalize
    }
  }
}
</script>