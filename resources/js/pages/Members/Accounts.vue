<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Download, ArrowDownCircle, Eye, EyeOff } from 'lucide-vue-next'
import { Button } from "@/components/ui/button"
import { Link } from "@inertiajs/vue3"
import { computed, reactive } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'


const props = defineProps<{
  member: any
  accounts: any[]
}>()

const totalBalanceVisible = reactive({ value: false })

// Total balance across all accounts
const totalBalance = computed(() =>
  props.accounts.reduce((sum, acc) => sum + Number(acc.balance || 0), 0)
)

const formattedTotalBalance = computed(() =>
  Number(totalBalance.value).toLocaleString()
)

// Track visibility per account
const balanceVisibility = reactive<{ [key: number]: boolean }>({})

const memberName = computed(() => {
  if (!props.member) return 'N/A'
  const { first_name, middle_name, last_name } = props.member
  return [first_name, middle_name, last_name]
    .filter(name => name && name.trim() !== '') // skip null/empty
    .join(' ')
})


</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/my-accounts' }]">
    <div class="space-y-8 p-5 sm:p-6">

      <Head title="Accounts" />

      <!-- Header -->
      <div class="rounded-xl bg-gradient-to-r from-blue-900 to-orange-500 p-6 shadow-md text-white">
        <div class="flex items-center justify-between">
          <!-- Accounts -->
          <div>
            <h1 class="text-lg md:text-xl font-semibold">
              Accounts
            </h1>
            <p class="text-sm max-sm:w-[90%] opacity-90 mt-1">
              Overview of your balances and transactions
            </p>
          </div>

          <!-- Account Holder -->
          <div class="text-right self-start">
            <p class="text-sm opacity-75">Account Name</p>
            <h2 class="text-base sm:text-lg font-medium text-white">
              {{ memberName }}
            </h2>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- My Accounts -->
        <Card
          class="rounded-2xl bg-white shadow-md hover:shadow-xl border-l-4 border-blue-900 transition-all duration-300">
          <CardHeader>
            <CardTitle class="text-blue-900">My Accounts</CardTitle>
          </CardHeader>
          <CardContent class="flex flex-col sm:flex-row justify-between gap-4">
            <div>
              <p class="text-sm text-gray-500">Total Accounts</p>
              <p class="text-lg font-medium sm:text-xl text-blue-900">
                {{ props.accounts.length }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Status</p>
              <p class="text-lg font-medium sm:text-xl text-orange-600">
                {{ props.accounts.length > 0 ? `${props.accounts.length} active` : "No accounts" }}
              </p>
            </div>
          </CardContent>
        </Card>

        <!-- Total Balance -->
        <Card
          class="rounded-2xl bg-white shadow-md hover:shadow-xl border-l-4 border-orange-500 transition-all duration-300">
          <CardHeader>
            <CardTitle class="text-blue-900 flex items-center justify-between">
              Total Balance
            </CardTitle>
          </CardHeader>
          <CardContent class="flex justify-between mr-5 items-center">
            <p class="text-lg font-medium sm:text-xl text-blue-900">
              <span :class="totalBalanceVisible.value ? '' : 'blur-sm select-none'">
                KES {{ formattedTotalBalance }}
                <p class="text-sm font-normal text-gray-800">
                  savings:
                  {{
    props.accounts.find(acc => acc.account_type === 'savings')?.balance?.toLocaleString() || 0
  }}
                </p>
              </span>
            </p>
            <button @click="totalBalanceVisible.value = !totalBalanceVisible.value"
              class="text-gray-500 hover:text-blue-700 transition">
              <component :is="totalBalanceVisible.value ? EyeOff : Eye" class="w-5 h-5" />
            </button>
          </CardContent>
        </Card>
      </div>


      <!-- Accounts List -->
      <div v-for="account in props.accounts" :key="account.id"
        class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-lg transition-all duration-300 p-6 space-y-4">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 w-full">
          <div>
            <h2 class="text-base sm:text-lg font-medium text-gray-800">
              <span class="font-normal">Acc. No:</span> {{ account.account_number }} - {{ account.account_type }}
            </h2>
            <p class="text-sm sm:text-base text-gray-500 flex items-center gap-3">
              Balance:
              <span class="font-medium text-blue-900">
                <span :class="balanceVisibility[account.id] ? '' : 'blur-sm select-none'" class="text-base sm:text-lg">
                  KES {{ Number(account.balance).toLocaleString() }}
                </span>
              </span>
              <button @click="balanceVisibility[account.id] = !balanceVisibility[account.id]"
                class="text-gray-500 hover:text-gray-700 transition">
                <component :is="balanceVisibility[account.id] ? EyeOff : Eye" class="w-4 h-4" />
              </button>
            </p>
          </div>

          <div class="flex gap-2 sm:gap-3 flex-wrap">
            <!-- Deposit -->
            <Link v-if="account.account_type === 'savings'"
              :href="route('members.accounts.deposit.show', { member: account.member_id, account: account.id })">
            <Button
              class="bg-blue-800 hover:bg-blue-900 hover:cursor-pointer font-normal text-white max-sm:text-xs shadow px-3 sm:px-4 py-1 sm:py-2 rounded-lg flex items-center gap-1 sm:gap-2 transition">
              <ArrowDownCircle class="w-3 sm:w-4 h-3 sm:h-4" />
              Deposit
            </Button>
            </Link>

            <!-- Statement -->
            <Link v-if="account.account_type === 'savings' && account.transactions.length"
              :href="route('my-accounts.statement', { member: account.member_id, account: account.id })">
            <Button
              class="border border-orange-500 bg-white hover:bg-orange-500 font-normal hover:cursor-pointer text-orange-500 hover:text-white max-sm:text-xs text-sm shadow px-3 sm:px-4 py-1 sm:py-2 rounded-lg flex items-center gap-1 sm:gap-2 transition">
              <Download class="w-3 sm:w-4 h-3 sm:h-4" />
              Download Statement
            </Button>
            </Link>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div>
          <h3 class="text-sm font-semibold text-gray-500 mb-2">Recent Transactions</h3>
          <div v-if="account.transactions.length" class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm">
            <table class="min-w-full text-sm text-left text-gray-600">
              <thead class="bg-gradient-to-r from-blue-50 to-blue-100 text-xs uppercase font-medium text-gray-600">
                <tr>
                  <th class="px-4 py-3">Date</th>
                  <th class="px-4 py-3">Type</th>
                  <th class="px-4 py-3">Amount</th>
                  <th class="px-4 py-3">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="tx in account.transactions" :key="tx.id" class="hover:bg-gray-50 transition">
                  <td class="px-4 py-3">{{ new Date(tx.created_at).toLocaleDateString() }}</td>
                  <td class="px-4 py-3 capitalize">{{ tx.transaction_type }}</td>
                  <td class="px-4 py-3 font-medium">KES {{ Number(tx.amount).toLocaleString() }}</td>
                  <td class="px-4 py-3">
                    <span class="px-2 py-1 text-xs rounded-full" :class="{
    'text-green-700 bg-green-100': tx.status === 'completed',
    'text-yellow-700 bg-yellow-100 ': tx.status === 'pending',
    'text-red-700 bg-red-100 ': tx.status === 'failed'
  }">
                      {{ tx.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-else class="text-gray-500 text-sm">No recent transactions.</p>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="!props.accounts.length" class="text-center text-gray-500 py-12">
        <p class="text-lg">No accounts found for this member.</p>
        <Link :href="route('accounts.create', { member: props.member.id })">
        <Button class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl shadow-md transition">
          Open Your First Account
        </Button>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
