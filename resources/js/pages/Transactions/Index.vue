<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/transactions' }]">
    <Head title="Transactions" />

    <!-- Flash -->
    <div ref="flashBox" class="mx-auto mt-4 max-w-5xl px-4 sm:px-6 lg:px-8">
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="flashMessage"
          :class="[
            'flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm shadow-lg backdrop-blur-xl',
            flashType === 'success'
              ? 'border-emerald-200 bg-emerald-50/95 text-emerald-800'
              : 'border-rose-200 bg-rose-50/95 text-rose-800',
          ]"
        >
          <div
            class="flex h-9 w-9 items-center justify-center rounded-xl"
            :class="flashType === 'success' ? 'bg-emerald-100' : 'bg-rose-100'"
          >
            <CheckCircle2 v-if="flashType === 'success'" class="h-5 w-5" />
            <AlertCircle v-else class="h-5 w-5" />
          </div>

          <span class="font-medium">{{ flashMessage }}</span>

          <button
            class="ml-auto rounded-lg p-1 text-gray-400 transition hover:bg-white/60 hover:text-gray-700"
            @click="flashMessage = null"
          >
            <X class="h-4 w-4" />
          </button>
        </div>
      </transition>
    </div>

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-10 transition-colors">
      <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        <!-- Hero Header -->
        <section
          class="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-gradient-to-br from-[#071C3B] via-[#0B2C5F] to-[#123C7A] p-6 text-white shadow-2xl sm:p-8"
        >
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(249,115,22,0.18),transparent_28%)]"></div>
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.08),transparent_30%)]"></div>

          <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
            

              <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                Transactions Management
              </h1>
              <p class="mt-2 max-w-2xl text-sm text-blue-100 sm:text-base">
                Monitor, approve, reverse, and track all system transactions with full operational visibility.
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <button
                class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-md transition hover:bg-white/15"
              >
                <Download class="h-4 w-4 text-orange-300" />
                Export
              </button>

              <Link
                href="/transactions/create"
                class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/25 transition hover:-translate-y-0.5 hover:bg-orange-600"
              >
                <Plus class="h-4 w-4" />
                New Transaction
              </Link>
            </div>
          </div>
        </section>

        <!-- Stats -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div
            v-for="card in statCards"
            :key="card.label"
            class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ card.value }}</p>
              </div>

              <div
                class="flex h-10 w-10 items-center justify-center rounded-2xl text-white shadow-lg"
                :class="card.iconBg"
              >
                <component :is="card.icon" class="h-5 w-5" />
              </div>
            </div>
          </div>
        </section>

        <!-- Filters -->
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
          <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Filter Transactions</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">Search, refine and narrow transaction results.</p>
            </div>

            <button
              @click="resetFilters"
              class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
            >
              <RotateCcw class="h-4 w-4" />
              Reset
            </button>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
              <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Search</label>
              <div class="relative">
                <Search class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" />
                <input
                  v-model="filters.search"
                  @keyup.enter="applyFilters"
                  type="text"
                  placeholder="Search transaction..."
                  class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm outline-none transition focus:border-orange-300 focus:ring-4 focus:ring-orange-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                />
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Type</label>
              <select v-model="filters.transaction_type" class="input-modern">
                <option value="">All Types</option>
                <option v-for="(label, key) in transactionTypes" :key="key" :value="key">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
              <select v-model="filters.status" class="input-modern">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
                <option value="reversed">Reversed</option>
              </select>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Start Date</label>
              <input type="date" v-model="filters.start_date" class="input-modern" />
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">End Date</label>
              <input type="date" v-model="filters.end_date" class="input-modern" />
            </div>
          </div>

          <div class="mt-5 flex justify-end">
            <button
              @click="applyFilters"
              class="inline-flex items-center gap-2 rounded-2xl bg-[#0B2C5F] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/20 transition hover:-translate-y-0.5 hover:bg-[#123872]"
            >
              <Filter class="h-4 w-4" />
              Apply Filters
            </button>
          </div>
        </section>

        <!-- Desktop Table -->
        <section class="hidden overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:block">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-blue-950 text-left text-xs font-semibold uppercase tracking-wider text-white dark:bg-slate-800 dark:text-slate-300">
                <tr>
                  <th class="px-4 py-4">#</th>
                  <th class="px-4 py-4">Txn ID</th>
                  <th class="px-4 py-4">Member</th>
                  <th class="px-4 py-4">Account</th>
                  <th class="px-4 py-4">Type</th>
                  <th class="px-4 py-4 text-right">Amount</th>
                  <th class="px-4 py-4">Status</th>
                  <th class="px-4 py-4 text-right">Created</th>
                  <th class="px-4 py-4 text-center">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr
                  v-for="(t, idx) in pageData"
                  :key="t.id"
                  class="transition hover:bg-slate-50 dark:hover:bg-slate-800/60"
                >
                  <td class="px-5 py-4 text-slate-500">
                    {{ idx + 1 + ((meta?.current_page ?? 1) - 1) * (meta?.per_page ?? pageData.length) }}
                  </td>

                  <td class="px-5 py-4 font-semibold text-[#0B2C5F] dark:text-blue-300">
                    {{ t.transaction_id }}
                  </td>

                  <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                      <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-orange-100 font-semibold text-orange-600">
                        {{ getInitials(`${t.member?.first_name || ''} ${t.member?.last_name || ''}`) }}
                      </div>
                      <div>
                        <p class="font-medium text-slate-900 dark:text-white">
                          {{ t.member?.first_name }} {{ t.member?.last_name }}
                        </p>
                        <p class="text-xs text-slate-500">{{ t.member?.membership_id }}</p>
                      </div>
                    </div>
                  </td>

                  <td class="px-5 py-4 text-slate-700 dark:text-slate-300">
                    {{ t.account?.account_number ?? '-' }}
                  </td>

                  <td class="px-5 py-4">
                    <span :class="['rounded-xl px-2.5 py-1 text-xs font-semibold', typeBadge(t.transaction_type)]">
                      {{ transactionTypes[t.transaction_type] ?? t.transaction_type }}
                    </span>
                  </td>

                  <td class="px-5 py-4 text-right font-semibold text-slate-900 dark:text-white">
                    KSh {{ formattedNumber(t.amount) }}
                  </td>

                  <td class="px-5 py-4">
                    <span :class="['rounded-xl px-2.5 py-1 text-xs font-semibold', statusBadge(t.status)]">
                      {{ capitalize(t.status) }}
                    </span>
                  </td>

                  <td class="px-5 py-4 text-right text-slate-500">
                    {{ formatDate(t.created_at) }}
                  </td>

                  <td class="px-5 py-4">
                    <div class="flex flex-wrap items-center justify-center gap-2">
                      <button @click="viewTransaction(t.id)" class="action-btn text-blue-600">View</button>
                      <button v-if="t.status === 'pending'" @click="openApproveModal(t)" class="action-btn text-emerald-600">Approve</button>
                      <button v-if="t.status === 'pending'" @click="openRejectModal(t)" class="action-btn text-rose-600">Reject</button>
                      <button v-if="t.status === 'completed'" @click="openReverseModal(t)" class="action-btn text-orange-600">Reverse</button>
                    </div>
                  </td>
                </tr>

                <tr v-if="pageData.length === 0">
                  <td colspan="9" class="px-4 py-10 text-center text-slate-400">No transactions found</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="border-t border-slate-200 p-4 dark:border-slate-800">
            <Pagination :data="transactions" @page-changed="goToPage" />
          </div>
        </section>

        <!-- Mobile Cards -->
        <section class="space-y-4 lg:hidden">
          <div
            v-for="t in pageData"
            :key="t.id"
            class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-semibold text-[#0B2C5F] dark:text-blue-300">{{ t.transaction_id }}</p>
                <p class="text-xs text-slate-500">{{ formatDate(t.created_at) }}</p>
              </div>

              <span :class="['rounded-xl px-2.5 py-1 text-xs font-semibold', statusBadge(t.status)]">
                {{ capitalize(t.status) }}
              </span>
            </div>

            <div class="mt-4 space-y-2 text-sm">
              <div class="flex justify-between gap-3">
                <span class="text-slate-500">Member</span>
                <span class="font-medium text-slate-900 dark:text-white">
                  {{ t.member?.first_name }} {{ t.member?.last_name }}
                </span>
              </div>

              <div class="flex justify-between gap-3">
                <span class="text-slate-500">Account</span>
                <span class="font-medium text-slate-900 dark:text-white">{{ t.account?.account_number ?? '-' }}</span>
              </div>

              <div class="flex justify-between gap-3">
                <span class="text-slate-500">Type</span>
                <span :class="['rounded-lg px-2 py-1 text-xs font-semibold', typeBadge(t.transaction_type)]">
                  {{ transactionTypes[t.transaction_type] ?? t.transaction_type }}
                </span>
              </div>

              <div class="flex justify-between gap-3">
                <span class="text-slate-500">Amount</span>
                <span class="font-semibold text-slate-900 dark:text-white">KSh {{ formattedNumber(t.amount) }}</span>
              </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-3 text-sm font-medium">
              <button @click="viewTransaction(t.id)" class="text-blue-600">View</button>
              <button v-if="t.status === 'pending'" @click="openApproveModal(t)" class="text-emerald-600">Approve</button>
              <button v-if="t.status === 'pending'" @click="openRejectModal(t)" class="text-rose-600">Reject</button>
              <button v-if="t.status === 'completed'" @click="openReverseModal(t)" class="text-orange-600">Reverse</button>
            </div>
          </div>

          <Pagination :data="transactions" @page-changed="goToPage" />
        </section>
      </div>
    </div>

    <!-- Action Modal -->
    <div v-if="modals.approve || modals.reject || modals.reverse" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
      <div class="w-full max-w-lg rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-5 flex items-start justify-between gap-4">
          <div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">
              {{ modals.approve ? 'Approve Transaction' : modals.reject ? 'Reject Transaction' : 'Reverse Transaction' }}
            </h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
              Transaction: <span class="font-medium text-slate-800 dark:text-slate-200">{{ currentTxn.transaction_id }}</span>
            </p>
          </div>

          <button @click="closeModals" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800">
            <X class="h-4 w-4" />
          </button>
        </div>

        <p v-if="modals.approve" class="mb-4 text-sm text-slate-600 dark:text-slate-300">
          Confirm approval for this transaction.
        </p>

        <textarea
          v-if="modals.reject"
          v-model="actionPayload.rejection_reason"
          rows="4"
          placeholder="Enter rejection reason"
          class="input-modern mb-4"
        />

        <textarea
          v-if="modals.reverse"
          v-model="actionPayload.reversal_reason"
          rows="4"
          placeholder="Enter reversal reason"
          class="input-modern mb-4"
        />

        <div class="flex justify-end gap-3">
          <button
            @click="closeModals"
            class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
          >
            Cancel
          </button>

          <button
            @click="modals.approve ? approveTransaction() : modals.reject ? rejectTransaction() : reverseTransaction()"
            class="rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600"
            :disabled="loading"
          >
            {{ loading ? 'Processing...' : 'Confirm Action' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import Pagination from '@/components/Pagination.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { computed, nextTick, reactive, ref, watch } from 'vue'
import {
  AlertCircle,
  ArrowRightLeft,
  CheckCircle2,
  CreditCard,
  Download,
  Filter,
  Plus,
  RotateCcw,
  Search,
  Wallet,
  X,
  CircleDollarSign,
} from 'lucide-vue-next'

const page = usePage()
const props = page.props as any

const stats = computed(() => props.statistics ?? {})
const transactions = ref(props.transactions ?? {})
const transactionTypes = computed(() => props.transactionTypes ?? {})

const meta = computed(() => transactions.value.meta ?? {})
const pageData = computed(() => transactions.value.data ?? [])

const filters = reactive({
  search: props.filters?.search ?? '',
  transaction_type: props.filters?.transaction_type ?? '',
  status: props.filters?.status ?? '',
  start_date: props.filters?.start_date ?? '',
  end_date: props.filters?.end_date ?? '',
})

const flashMessage = ref<string | null>(null)
const flashType = ref('success')
const flashBox = ref<HTMLElement | null>(null)

watch(
  () => page.props,
  (props) => {
    if (props.flash?.success) {
      flashMessage.value = props.flash.success
      flashType.value = 'success'
    } else if (props.flash?.error || props.errors?.error) {
      flashMessage.value = props.flash?.error || props.errors?.error
      flashType.value = 'error'
    }

    if (flashMessage.value) {
      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true },
)

const statCards = computed(() => [
  {
    label: 'Total Transactions',
    value: stats.value.total_transactions ?? 0,
    icon: ArrowRightLeft,
    iconBg: 'bg-gradient-to-br from-[#0B2C5F] to-[#123C7A]',
  },
  {
    label: 'Total Amount',
    value: `KSh ${formattedNumber(stats.value.total_amount ?? 0)}`,
    icon: Wallet,
    iconBg: 'bg-gradient-to-br from-orange-500 to-orange-600',
  },
  {
    label: 'Pending',
    value: stats.value.pending_count ?? 0,
    icon: CreditCard,
    iconBg: 'bg-gradient-to-br from-amber-500 to-orange-500',
  },
  {
    label: 'Completed',
    value: stats.value.completed_count ?? 0,
    icon: CircleDollarSign,
    iconBg: 'bg-gradient-to-br from-emerald-500 to-emerald-600',
  },
])

function formattedNumber(n: number) {
  return Number(n ?? 0).toLocaleString()
}
function capitalize(s: string) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
}
function formatDate(d: string) {
  return d ? new Date(d).toLocaleString() : '-'
}
function getInitials(name: string) {
  return name
    ?.split(' ')
    .map((n) => n[0])
    .join('')
    .slice(0, 2)
    .toUpperCase() || 'NA'
}

function typeBadge(type: string) {
  switch (type) {
    case 'deposit': return 'bg-emerald-100 text-emerald-700'
    case 'withdrawal': return 'bg-rose-100 text-rose-700'
    case 'transfer': return 'bg-indigo-100 text-indigo-700'
    case 'loan_disbursement': return 'bg-amber-100 text-amber-700'
    default: return 'bg-slate-100 text-slate-700'
  }
}

function statusBadge(status: string) {
  switch (status) {
    case 'pending': return 'bg-amber-100 text-amber-700'
    case 'completed': return 'bg-emerald-100 text-emerald-700'
    case 'failed': return 'bg-rose-100 text-rose-700'
    case 'reversed': return 'bg-slate-100 text-slate-700'
    default: return 'bg-slate-100 text-slate-700'
  }
}

const loading = ref(false)
const modals = reactive({ approve: false, reject: false, reverse: false })
const currentTxn = reactive<any>({})
const actionPayload = reactive({ rejection_reason: '', reversal_reason: '' })

function showMessage(text: string, type: 'success' | 'error' = 'success') {
  flashMessage.value = text
  flashType.value = type

  nextTick(() => {
    flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })

  setTimeout(() => (flashMessage.value = null), 4000)
}

function viewTransaction(id: number) {
  router.visit(`/transactions/${id}`)
}

function goToPage(pageNum: number) {
  router.get('/transactions', { ...filters, page: pageNum }, { replace: true, preserveState: true })
}

function applyFilters() {
  router.get('/transactions', { ...filters, page: 1 }, { replace: true })
}

function resetFilters() {
  Object.assign(filters, {
    search: '',
    transaction_type: '',
    status: '',
    start_date: '',
    end_date: '',
  })
  applyFilters()
}

function openApproveModal(t: any) {
  Object.assign(currentTxn, t)
  modals.approve = true
}
function openRejectModal(t: any) {
  Object.assign(currentTxn, t)
  actionPayload.rejection_reason = ''
  modals.reject = true
}
function openReverseModal(t: any) {
  Object.assign(currentTxn, t)
  actionPayload.reversal_reason = ''
  modals.reverse = true
}
function closeModals() {
  modals.approve = modals.reject = modals.reverse = false
  Object.keys(currentTxn).forEach((k) => delete currentTxn[k])
  actionPayload.rejection_reason = ''
  actionPayload.reversal_reason = ''
}

function approveTransaction() {
  if (!currentTxn.id) return
  loading.value = true

  router.post(`/transactions/${currentTxn.id}/approve`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      loading.value = false
      modals.approve = false
      const txn = pageData.value.find((t) => t.id === currentTxn.id)
      if (txn) txn.status = 'completed'
      showMessage('Transaction approved', 'success')
      Object.keys(currentTxn).forEach((k) => delete currentTxn[k])
    },
    onError: () => (loading.value = false),
  })
}

function rejectTransaction() {
  if (!actionPayload.rejection_reason.trim()) return alert('Please provide a rejection reason')
  loading.value = true

  router.post(`/transactions/${currentTxn.id}/reject`, {
    rejection_reason: actionPayload.rejection_reason,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      loading.value = false
      modals.reject = false
      const txn = pageData.value.find((t) => t.id === currentTxn.id)
      if (txn) txn.status = 'failed'
      showMessage('Transaction rejected', 'success')
      closeModals()
    },
    onError: () => (loading.value = false),
  })
}

function reverseTransaction() {
  if (!actionPayload.reversal_reason.trim()) return alert('Please provide a reversal reason')
  loading.value = true

  router.post(`/transactions/${currentTxn.id}/reverse`, {
    reversal_reason: actionPayload.reversal_reason,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      loading.value = false
      modals.reverse = false
      const txn = pageData.value.find((t) => t.id === currentTxn.id)
      if (txn) txn.status = 'reversed'
      showMessage('Transaction reversed', 'success')
      closeModals()
    },
    onError: () => (loading.value = false),
  })
}

async function fetchTransactions(page = 1) {
  try {
    const { data } = await axios.get('/transactions', { params: { ...filters, page } })
    transactions.value = { data: data.data, meta: data.meta }
  } catch {
    showMessage('Failed to reload transactions', 'error')
  }
}
</script>

<style scoped>
.input-modern {
  width: 100%;
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
  background: white;
  padding: 12px 16px;
  font-size: 14px;
  outline: none;
  transition: all 0.3s ease;
}

.input-modern:focus {
  border-color: #fdba74;
  box-shadow: 0 0 0 4px #ffedd5;
}

.dark .input-modern {
  border-color: #334155;
  background: #1e293b;
  color: white;
}

.action-btn {
  border-radius: 0.5rem;
  padding: 4px 8px;
  font-size: 12px;
  font-weight: 500;
  transition: background 0.3s ease;
}

.action-btn:hover {
  background: #f1f5f9;
}

.dark .action-btn:hover {
  background: #1e293b;
}

button:hover {
  cursor: pointer;
}
</style>