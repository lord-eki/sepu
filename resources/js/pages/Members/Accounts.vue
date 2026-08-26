<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed, onMounted, reactive } from 'vue'

import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

import {
  ArrowDownCircle,
  Download,
  Eye,
  EyeOff,
  Wallet,
  CreditCard,
  Activity,
} from 'lucide-vue-next'

const page = usePage()

const props = defineProps<{
  member: any
  accounts: any[]
}>()

onMounted(() => {
  const flash = page.props.flash

  if (flash?.success) {
    showMessage('success', flash.success)
  } else if (flash?.error) {
    showMessage('error', flash.error)
  }
})

const totalBalanceVisible = reactive({
  value: true,
})

const balanceVisibility = reactive<{ [key: number]: boolean }>({})

const totalBalance = computed(() =>
  props.accounts
    .filter((acc) => acc.account_type === 'share_deposits')
    .reduce((sum, acc) => {
    return sum + Number(acc.balance || 0)
    }, 0)
)

const formattedTotalBalance = computed(() =>
  Number(totalBalance.value || 0).toLocaleString()
)

const memberName = computed(() => {
  if (!props.member) return 'N/A'

  const { first_name, middle_name, last_name } = props.member

  return [first_name, middle_name, last_name]
    .filter((name) => name && name.trim() !== '')
    .join(' ')
})

const shareDeposits = computed(() => {
  return Number(
    props.accounts.find(
      (acc) => acc.account_type === 'share_deposits'
    )?.balance || 0
  ).toLocaleString()
})

const totalTransactions = computed(() => {
  return props.accounts.reduce((sum, acc) => {
    return sum + (acc.transactions?.length || 0)
  }, 0)
})

const formatAccountLabel = (type: string) => {
  switch (type) {
    case 'share_capital':
      return 'Share Capital'

    case 'share_deposits':
      return 'Share Deposits'

    default:
      return type
  }
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'completed':
      return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'

    case 'pending':
      return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'

    case 'failed':
      return 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'

    default:
      return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/my-accounts' }]">
    <Head title="Accounts" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors">
      <div class="mx-auto max-w-7xl space-y-8 p-4 sm:p-6">
        <!-- Hero -->
        <div
          class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-blue-900 to-indigo-700 p-6 sm:p-8 text-white shadow-2xl"
        >
          <div
            class="absolute right-0 top-0 h-40 w-40 rounded-full bg-white/10 blur-3xl"
          />
          <div
            class="absolute bottom-0 left-0 h-32 w-32 rounded-full bg-blue-400/20 blur-2xl"
          />

          <div
            class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
          >
            <div class="space-y-3">
              <div
                class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-orange-500/90 px-4 py-1 text-sm backdrop-blur"
              >
                <Wallet class="h-4 w-4" />
                My Accounts
              </div>

              <div>
                <h1
                  class="text-lg font-bold tracking-tight sm:text-xl lg:text-3xl"
                >
                  Welcome,
                  {{ memberName }}
                </h1>

                <p class="mt-2 max-w-xl text-sm text-blue-100 sm:text-base">
                  Track your balances, deposits, and account activity.
                </p>
              </div>
            </div>

            <!-- Balance Card -->
            <div
              class="w-full max-w-sm rounded-3xl border border-white/15 bg-white/10 p-5 backdrop-blur-xl"
            >
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm text-blue-100">
                    Total Balance
                  </p>

                  <h2
                    class="mt-2 text-lg font-bold tracking-tight sm:text-xl"
                  >
                    <span
                      :class="
                        totalBalanceVisible.value
                          ? ''
                          : 'blur-md select-none'
                      "
                    >
                      KES {{ formattedTotalBalance }}
                    </span>
                  </h2>
                </div>

                <button
                  @click="totalBalanceVisible.value = !totalBalanceVisible.value"
                  class="rounded-full bg-white/10 p-2 transition hover:bg-white/20"
                >
                  <component
                    :is="totalBalanceVisible.value ? EyeOff : Eye"
                    class="h-5 w-5"
                  />
                </button>
              </div>

              <div
                class="mt-5 flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3"
              >
                <div>
                  <p class="text-xs text-blue-100">
                    Share Deposits
                  </p>

                  <p class="text-base font-semibold">
                    KES {{ shareDeposits }}
                  </p>
                </div>

                <CreditCard class="h-8 w-8 text-blue-100" />
              </div>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
          <Card class="rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <CardContent class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Total Accounts
                  </p>

                  <h3 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                    {{ props.accounts.length }}
                  </h3>
                </div>

                <div
                  class="rounded-2xl bg-blue-100 p-3 text-blue-700"
                >
                  <Wallet class="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
           
            <CardContent class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Active Accounts
                  </p>

                  <h3 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                    {{ props.accounts.length }}
                  </h3>
                </div>

                <div
                  class="rounded-2xl bg-emerald-100 p-3 text-emerald-700"
                >
                  <Activity class="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
           
            <CardContent class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Transactions
                  </p>

                  <h3 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                    {{ totalTransactions }}
                  </h3>
                </div>

                <div
                  class="rounded-2xl bg-orange-100 p-3 text-orange-700"
                >
                  <ArrowDownCircle class="h-6 w-6" />
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Accounts -->
        <div
          v-if="props.accounts.length"
          class="grid gap-6"
        >
          <div
            v-for="account in props.accounts"
            :key="account.id"
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
          >
            <!-- Top -->
            <div
              class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white p-6 dark:border-slate-800 dark:from-slate-900 dark:to-slate-950"
            >
              <div
                class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
              >
                <div class="space-y-2">
                  <div
                    class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"
                  >
                    {{ formatAccountLabel(account.account_type) }}
                  </div>

                  <div>
                    <h2
                      class="text-lg font-semibold text-slate-900 dark:text-white sm:text-xl"
                    >
                      {{ account.account_number }}
                    </h2>

                    <div
                      class="mt-2 flex items-center gap-3 text-sm text-slate-500 dark:text-slate-400"
                    >
                      <span>Available Balance</span>

                      <span
                        class="text-base font-semibold text-slate-900 dark:text-white"
                      >
                        <span
                          :class="
                            balanceVisibility[account.id]
                              ? ''
                              : 'blur-md select-none'
                          "
                        >
                          KES
                          {{ Number(account.balance || 0).toLocaleString() }}
                        </span>
                      </span>

                      <button
                        @click="
                          balanceVisibility[account.id] =
                            !balanceVisibility[account.id]
                        "
                        class="text-slate-400 transition hover:text-slate-700 dark:hover:text-white dark:text-slate-300"
                      >
                        <component
                          :is="
                            balanceVisibility[account.id]
                              ? EyeOff
                              : Eye
                          "
                          class="h-4 w-4"
                        />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-3">
                  <Link
                    v-if="account.account_type === 'share_deposits'"
                    :href="
                      route('members.accounts.deposit.show', {
                        member: account.member_id,
                        account: account.id,
                      })
                    "
                  >
                    <Button
                      class="h-11 rounded-2xl bg-blue-900 px-5 text-white hover:bg-blue-800"
                    >
                      <ArrowDownCircle class="mr-2 h-4 w-4" />
                      Deposit
                    </Button>
                  </Link>

                  <Link
                    v-if="account.transactions.length"
                    :href="
                      route('my-accounts.statement', {
                        member: account.member_id,
                        account: account.id,
                      })
                    "
                  >
                    <Button
                      variant="outline"
                      class="h-11 rounded-2xl border-orange-300 bg-orange-50 px-5 text-orange-700 hover:bg-orange-100 dark:border-orange-700 dark:bg-orange-900/20 dark:text-orange-300 dark:hover:bg-orange-900/40"
                    >
                      <Download class="mr-2 h-4 w-4" />
                      Statement
                    </Button>
                  </Link>
                </div>
              </div>
            </div>

            <!-- Transactions -->
            <div class="p-6">
              <div
                class="mb-4 flex items-center justify-between"
              >
                <div>
                  <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                    Recent Transactions
                  </h3>

                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Latest activity on this account
                  </p>
                </div>
              </div>

              <div
                v-if="account.transactions.length"
                class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700"
              >
                <div class="overflow-x-auto">
                  <table class="min-w-full">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                      <tr>
                        <th
                          class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >
                          Date
                        </th>

                        <th
                          class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >
                          Type
                        </th>

                        <th
                          class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >
                          Amount
                        </th>

                        <th
                          class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                        >
                          Status
                        </th>
                      </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                      <tr
                        v-for="tx in account.transactions"
                        :key="tx.id"
                        class="transition hover:bg-slate-50 dark:hover:bg-slate-800/60"
                      >
                        <td class="px-5 py-4 text-sm text-slate-700 dark:text-slate-300">
                          {{
                            new Date(
                              tx.created_at
                            ).toLocaleDateString()
                          }}
                        </td>

                        <td
                          class="px-5 py-4 text-sm capitalize text-slate-700 dark:text-slate-300"
                        >
                          {{ tx.transaction_type }}
                        </td>

                        <td
                          class="px-5 py-4 text-sm font-semibold text-slate-900 dark:text-white"
                        >
                          KES
                          {{ Number(tx.amount).toLocaleString() }}
                        </td>

                        <td class="px-5 py-4">
                          <span
                            class="rounded-full px-3 py-1 text-xs font-medium"
                            :class="getStatusClass(tx.status)"
                          >
                            {{ tx.status }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <div
                v-else
                class="rounded-2xl border border-dashed border-slate-300 py-10 text-center"
              >
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  No recent transactions found.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty -->
        <div
          v-else
          class="rounded-3xl border border-dashed border-slate-300 bg-white py-20 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900"
        >
          <div
            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"
          >
            <Wallet class="h-8 w-8 text-slate-500 dark:text-slate-400" />
          </div>

          <h2 class="text-xl font-semibold text-slate-900 dark:text-white">
            No Accounts Found
          </h2>

          <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            Open your first SACCO account to get started.
          </p>

          <Link
            :href="route('accounts.create', { member: props.member.id })"
          >
            <Button
              class="mt-6 h-11 rounded-2xl bg-blue-900 px-6 hover:bg-blue-800"
            >
              Open Account
            </Button>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>