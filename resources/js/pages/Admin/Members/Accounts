<template>
  <AppLayout :title="`${member.first_name} ${member.last_name} - Accounts`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <Link :href="route('members.show', member.id)" class="mr-4">
          <ArrowLeftIcon class="w-5 h-5" />
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              {{ member.first_name }} {{ member.last_name }} - Accounts
            </h2>
            <p class="text-sm text-gray-600">Member ID: {{ member.membership_id }}</p>
          </div>
        </div>
        <div class="flex space-x-2" v-if="canManageAccounts">
          <Link :href="route('accounts.create')"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
          <PlusIcon class="w-4 h-4 mr-2" />
          Add Account
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Member Info Card -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="px-6 py-4">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-12 w-12">
                <img v-if="member.profile_photo" :src="`/storage/${member.profile_photo}`" :alt="member.first_name"
                  class="h-12 w-12 rounded-full object-cover" />
                <div v-else class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
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
                <span :class="{
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                  'bg-green-100 text-green-800': member.membership_status === 'active',
                  'bg-red-100 text-red-800': member.membership_status === 'inactive',
                  'bg-yellow-100 text-yellow-800': member.membership_status === 'suspended'
                }">
                  {{ member.membership_status }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Account Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <BanknotesIcon class="h-8 w-8 text-green-500" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Share Deposits
                    </dt>
                    <dd class="text-2xl font-bold text-gray-900">
                      {{ formatCurrency(totalShareDeposits) }}
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
                  <ChartPieIcon class="h-8 w-8 text-blue-500" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Share Capital
                    </dt>
                    <dd class="text-2xl font-bold text-gray-900">
                      {{ formatCurrency(totalShareCapital) }}
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
                  <WalletIcon class="h-8 w-8 text-purple-500" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Balance
                    </dt>
                    <dd class="text-2xl font-bold text-gray-900">
                      {{ formatCurrency(totalBalance) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- Accounts List -->
        <div class="space-y-6">
          <div v-for="account in accounts" :key="account.id" class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Account Header -->
            <div class="px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div :class="{
                      'h-12 w-12 rounded-full flex items-center justify-center': true,
                      'bg-green-100': account.account_type === 'share_deposits',
                      'bg-blue-100': account.account_type === 'share_capital',

                    }">
                      <BanknotesIcon v-if="account.account_type === 'share_deposits'" class="h-6 w-6 text-green-600" />
                      <ChartPieIcon v-else-if="account.account_type === 'share_capital'"
                        class="h-6 w-6 text-blue-600" />

                      <CreditCardIcon v-else class="h-6 w-6 text-purple-600" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">
                      {{ formatAccountLabel(account.account_type) }} Account
                    </h3>
                    <p class="text-sm text-gray-500">{{ account.account_number }}</p>
                    <div class="flex items-center mt-1 space-x-4">
                      <span :class="{
                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                        'bg-green-100 text-green-800': account.is_active,
                        'bg-red-100 text-red-800': !account.is_active
                      }">
                        {{ account.is_active ? 'Active' : 'Inactive' }}
                      </span>
                      <span class="text-xs text-gray-500">
                        Opened {{ formatDate(account.created_at) }}
                      </span>
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-bold text-gray-900">
                    {{ formatCurrency(account.balance) }}
                  </div>
                  <div class="text-sm text-gray-500">
                    Available: {{ formatCurrency(account.available_balance) }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Account Actions -->
            <div class="px-6 py-4 bg-gray-50" v-if="canManageAccounts">
              <div class="flex flex-wrap gap-2">
                <Link :href="route('accounts.show', account.id)"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <EyeIcon class="h-4 w-4 mr-2" />
                View Details
                </Link>

                <button v-if="account.account_type === 'share_deposits'" @click="showDepositModal(account)"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                  <ArrowUpIcon class="h-4 w-4 mr-2" />
                  Deposit
                </button>

                <button v-if="account.account_type === 'share_deposits' && account.available_balance > 0"
                  @click="showWithdrawalModal(account)"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                  <ArrowDownIcon class="h-4 w-4 mr-2" />
                  Withdraw
                </button>

                <button v-if="account.account_type === 'share_capital'" @click="showSharesModal(account)"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                  <ChartPieIcon class="h-4 w-4 mr-2" />
                  Buy Shares
                </button>

                <Link :href="route('accounts.statement', account.id)"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <DocumentTextIcon class="h-4 w-4 mr-2" />
                Statement
                </Link>
              </div>
            </div>

            <!-- Recent Transactions -->
            <div class="px-6 py-4">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-medium text-gray-900">Recent Transactions</h4>
                <Link :href="route('accounts.transactions', account.id)"
                  class="text-sm text-indigo-600 hover:text-indigo-500">
                View All
                </Link>
              </div>

              <div v-if="account.transactions && account.transactions.length > 0" class="space-y-2">
                <div v-for="transaction in account.transactions" :key="transaction.id"
                  class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                  <div class="flex items-center">
                    <div :class="{
                      'h-8 w-8 rounded-full flex items-center justify-center': true,
                      'bg-green-100': ['deposit', 'credit'].includes(transaction.transaction_type),
                      'bg-red-100': ['withdrawal', 'debit'].includes(transaction.transaction_type),
                      'bg-blue-100': transaction.transaction_type === 'transfer'
                    }">
                      <ArrowUpIcon v-if="['deposit', 'credit'].includes(transaction.transaction_type)"
                        class="h-4 w-4 text-green-600" />
                      <ArrowDownIcon v-else-if="['withdrawal', 'debit'].includes(transaction.transaction_type)"
                        class="h-4 w-4 text-red-600" />
                      <ArrowsRightLeftIcon v-else class="h-4 w-4 text-blue-600" />
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">
                        {{ capitalize(transaction.transaction_type.replace('_', ' ')) }}
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ formatDate(transaction.transaction_date) }}
                      </p>
                    </div>
                  </div>
                  <div class="text-right">
                    <p :class="{
                      'text-sm font-medium': true,
                      'text-green-600': ['deposit', 'credit'].includes(transaction.transaction_type),
                      'text-red-600': ['withdrawal', 'debit'].includes(transaction.transaction_type),
                      'text-gray-900': transaction.transaction_type === 'transfer'
                    }">
                      {{ ['deposit', 'credit'].includes(transaction.transaction_type) ? '+' : '-' }}{{
                        formatCurrency(transaction.amount) }}
                    </p>
                    <p class="text-xs text-gray-500">
                      Balance: {{ formatCurrency(transaction.balance_after) }}
                    </p>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-4">
                <p class="text-sm text-gray-500">No recent transactions</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="accounts.length === 0" class="text-center py-12">
          <WalletIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No accounts</h3>
          <p class="mt-1 text-sm text-gray-500">This member doesn't have any accounts yet.</p>
          <div class="mt-6" v-if="canManageAccounts">
            <Link :href="route('accounts.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
            <PlusIcon class="h-4 w-4 mr-2" />
            Create Account
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Transaction Modals -->
    <DepositModal v-if="showDeposit" :account="selectedAccount" @close="closeModals"
      @success="handleTransactionSuccess" />

    <WithdrawalModal v-if="showWithdrawal" :account="selectedAccount" @close="closeModals"
      @success="handleTransactionSuccess" />

    <SharesModal v-if="showShares" :account="selectedAccount" @close="closeModals"
      @success="handleTransactionSuccess" />
  </AppLayout>
</template>

<script>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DepositModal from '@/components/DepositModal.vue'
import WithdrawalModal from '@/components/WithdrawalModal.vue'
import SharesModal from '@/components/SharesModal.vue'
import {
  ArrowLeftIcon,
  PlusIcon,
  UserIcon,
  BanknotesIcon,
  ChartPieIcon,
  WalletIcon,
  CreditCardIcon,
  EyeIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ArrowsRightLeftIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

export default {
  components: {
    AppLayout,
    Head,
    Link,
    DepositModal,
    WithdrawalModal,
    SharesModal,
    ArrowLeftIcon,
    PlusIcon,
    UserIcon,
    BanknotesIcon,
    ChartPieIcon,
    WalletIcon,
    CreditCardIcon,
    EyeIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ArrowsRightLeftIcon,
    DocumentTextIcon
  },
  props: {
    member: Object,
    accounts: Array
  },
  setup(props) {
    const showDeposit = ref(false)
    const showWithdrawal = ref(false)
    const showShares = ref(false)
    const selectedAccount = ref(null)

    const canManageAccounts = computed(() => {
      const userRole = window.Laravel?.user?.role
      return ['admin', 'management', 'accountant'].includes(userRole)
    })

    const totalShareDeposits = computed(() => {
      return props.accounts
        .filter(account => account.account_type === 'share_deposits')
        .reduce((sum, account) => sum + parseFloat(account.balance || 0), 0)
    })

    const totalShareCapital = computed(() => {
      return props.accounts
        .filter(account => account.account_type === 'share_capital')
        .reduce((sum, account) => sum + parseFloat(account.balance || 0), 0)
    })


    const totalBalance = computed(() => {
      return props.accounts
        .reduce((sum, account) => sum + parseFloat(account.balance || 0), 0)
    })

    const showDepositModal = (account) => {
      selectedAccount.value = account
      showDeposit.value = true
    }

    const showWithdrawalModal = (account) => {
      selectedAccount.value = account
      showWithdrawal.value = true
    }

    const showSharesModal = (account) => {
      selectedAccount.value = account
      showShares.value = true
    }

    const closeModals = () => {
      showDeposit.value = false
      showWithdrawal.value = false
      showShares.value = false
      selectedAccount.value = null
    }

    const handleTransactionSuccess = () => {
      closeModals()
      // Reload the page to get updated balances
      router.reload()
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString()
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

    const formatAccountLabel = (type) => {
      switch (type) {
        case 'share_capital':
          return 'Share Capital'
        case 'share_deposits':
          return 'Share Deposits'
        default:
          return type
      }
    }


    return {
      showDeposit,
      showWithdrawal,
      showShares,
      selectedAccount,
      canManageAccounts,
      totalShareDeposits,
      totalShareCapital,
      totalBalance,
      showDepositModal,
      showWithdrawalModal,
      showSharesModal,
      closeModals,
      handleTransactionSuccess,
      formatDate,
      formatCurrency,
      capitalize,
      formatAccountLabel
    }

  }
}
</script>