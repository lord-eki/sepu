<template>
  <AppLayout :breadcrumbs="[{ title: 'Vouchers', href: '/vouchers' }]">
    <Head title="Payment Vouchers" />

    <div class="min-h-screen bg-slate-50 dark:bg-[#081122] p-4 sm:p-6 space-y-6">

      <!-- Flash -->
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="flashMessage"
          class="max-w-3xl mx-auto rounded-2xl border px-5 py-4 shadow-lg backdrop-blur-xl flex items-start gap-3"
          :class="flashType === 'success'
            ? 'bg-emerald-50/90 border-emerald-200 text-emerald-700'
            : 'bg-rose-50/90 border-rose-200 text-rose-700'"
        >
          <component
            :is="flashType === 'success' ? CheckCircle2Icon : AlertCircle"
            class="w-5 h-5 mt-0.5 shrink-0"
          />
          <div class="flex-1 text-sm font-medium">{{ flashMessage }}</div>
          <button @click="flashMessage = null" class="text-current/70 hover:text-current">✕</button>
        </div>
      </transition>

      <!-- Hero -->
      <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 p-6 sm:p-8 shadow-2xl shadow-blue-950/20">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(249,115,22,0.18),_transparent_30%)]" />
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
              Payment Vouchers
            </h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">
              Manage, approve, and track all outgoing payment vouchers across operations.
            </p>
          </div>

          <Link
            :href="route('vouchers.create')"
            class="inline-flex items-center gap-2 rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600"
          >
            <PlusCircleIcon class="h-5 w-5" />
            Create Voucher
          </Link>
        </div>
      </section>

      <!-- Stats -->
      <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div
          v-for="card in statCards"
          :key="card.label"
          class="rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white/90 dark:bg-slate-900/80 p-5 shadow-sm hover:shadow-xl transition-all duration-300"
        >
          <div class="flex items-start justify-between">
            <div>
              <p class="text-sm text-slate-500 dark:text-slate-400">{{ card.label }}</p>
              <h3 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ card.value }}</h3>
              <p v-if="card.extra" class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ card.extra }}</p>
            </div>
            <div :class="[card.color, 'flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-lg']">
              <component :is="card.icon" class="h-5 w-5" />
            </div>
          </div>
        </div>
      </section>

      <!-- Filters -->
      <section class="rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white/90 dark:bg-slate-900/80 p-5 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
          <select v-model="form.status" @change="applyFilters" class="filter-input">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="paid">Paid</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
          </select>

          <select v-model="form.voucher_type" @change="applyFilters" class="filter-input">
            <option value="">All Types</option>
            <option value="loan_disbursement">Loan Disbursement</option>
            <option value="operational_expense">Operational Expense</option>
            <option value="dividend_payment">Dividend Payment</option>
            <option value="refund">Refund</option>
            <option value="other">Other</option>
          </select>

          <input v-model="form.date_from" @change="applyFilters" type="date" class="filter-input" />
          <input v-model="form.date_to" @change="applyFilters" type="date" class="filter-input" />

          <div class="relative">
            <Search class="absolute left-3 top-3.5 h-4 w-4 text-slate-400" />
            <input
              v-model="form.search"
              @input="debouncedSearch"
              type="text"
              placeholder="Search vouchers..."
              class="filter-input pl-10"
            />
          </div>
        </div>

        <div class="mt-4 flex justify-end">
          <button
            @click="clearFilters"
            class="rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
          >
            Clear Filters
          </button>
        </div>
      </section>

      <!-- Table -->
      <section class="overflow-hidden rounded-3xl border border-slate-200/70 dark:border-slate-800 bg-white/90 dark:bg-slate-900/80 shadow-sm">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300">
              <tr>
                <th v-for="head in tableHeaders" :key="head" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">
                  {{ head }}
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="voucher in vouchers.data"
                :key="voucher.id"
                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition"
              >
                <td class="px-6 py-4 font-semibold text-blue-700 dark:text-blue-400">
                  <Link :href="route('vouchers.show', voucher.id)" class="hover:underline">
                    {{ voucher.voucher_number }}
                  </Link>
                </td>

                <td class="px-6 py-4">
                  <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-medium text-slate-700 dark:text-slate-300">
                    {{ formatVoucherType(voucher.voucher_type) }}
                  </span>
                </td>

                <td class="px-6 py-4 text-slate-700 dark:text-slate-300">{{ voucher.payee_name }}</td>
                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">KSh {{ formatCurrency(voucher.amount) }}</td>
                <td class="px-6 py-4 max-w-xs truncate text-slate-500 dark:text-slate-400">{{ voucher.purpose }}</td>

                <td class="px-6 py-4">
                  <span :class="getStatusBadgeClass(voucher.status)" class="rounded-full px-3 py-1 text-xs font-semibold">
                    {{ formatStatus(voucher.status) }}
                  </span>
                </td>

                <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ formatDate(voucher.created_at) }}</td>

                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <Link :href="route('vouchers.show', voucher.id)" class="text-blue-600 hover:text-blue-800">
                      <Eye class="h-4 w-4" />
                    </Link>
                    <Link
                      v-if="voucher.status === 'pending'"
                      :href="route('vouchers.edit', voucher.id)"
                      class="text-orange-500 hover:text-orange-600"
                    >
                      <Pencil class="h-4 w-4" />
                    </Link>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="vouchers.data.length === 0" class="py-16 text-center">
          <FileText class="mx-auto h-12 w-12 text-slate-400" />
          <h3 class="mt-4 text-base font-semibold text-slate-900 dark:text-white">No vouchers found</h3>
          <p class="mt-1 text-sm text-slate-500">Create your first payment voucher to get started.</p>
        </div>

        <div
          v-if="vouchers.data.length"
          class="flex items-center justify-between border-t border-slate-200 dark:border-slate-800 px-6 py-4"
        >
          <p class="hidden sm:block text-sm text-slate-500">
            Showing {{ vouchers.from }}–{{ vouchers.to }} of {{ vouchers.total }} results
          </p>
          <Pagination :data="vouchers" />
        </div>
      </section>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, reactive, computed } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'
import {
  CheckCircle2Icon,
  AlertCircle,
  Clock,
  Eye,
  FileText,
  Pencil,
  PlusCircleIcon,
  Search,
  Wallet
} from 'lucide-vue-next'

const props = defineProps({
  vouchers: Object,
  stats: Object,
  filters: Object,
})



// Flash handling
const page = usePage()
const flash = computed(() => page.props?.flash || {})

const flashMessage = ref(null)
const flashType = ref('success')
const flashBox = ref(null)

const statCards = computed(() => [
  {
    label: 'Total Vouchers',
    value: props.stats.total_vouchers,
    icon: FileText,
    color: 'bg-slate-900'
  },
  {
    label: 'Pending',
    value: props.stats.pending_vouchers,
    extra: formatCurrency(props.stats.total_pending_amount),
    icon: Clock,
    color: 'bg-yellow-500'
  },
  {
    label: 'Approved',
    value: props.stats.approved_vouchers,
    extra: formatCurrency(props.stats.total_approved_amount),
    icon: CheckCircle2Icon,
    color: 'bg-emerald-600'
  },
  {
    label: 'Paid',
    value: props.stats.paid_vouchers,
    extra: formatCurrency(props.stats.total_paid_amount),
    icon: Wallet,
    color: 'bg-orange-500'
  }
])

const tableHeaders = [
  'Voucher Number',
  'Type',
  'Payee',
  'Amount',
  'Purpose',
  'Status',
  'Created',
  'Actions'
]

watch(
  () => page.props,
  (props) => {
    if (props.flash?.success) {
      flashMessage.value = props.flash.success
      flashType.value = 'success'
    } else if (props.flash?.error) {
      flashMessage.value = props.flash.error
      flashType.value = 'error'
    } else if (props.errors?.error) {   
      flashMessage.value = props.errors.error
      flashType.value = 'error'
    }

    if (flashMessage.value) {
      window.scrollTo({ top: 0, behavior: 'smooth' })
      flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)

const form = reactive({
  status: props.filters.status || '',
  voucher_type: props.filters.voucher_type || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  search: props.filters.search || '',
})

let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => applyFilters(), 500)
}

const applyFilters = () =>
  router.get(route('vouchers.index'), form, { preserveState: true, preserveScroll: true })

const clearFilters = () => {
  Object.assign(form, { status: '', voucher_type: '', date_from: '', date_to: '', search: '' })
  applyFilters()
}

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2 }).format(amount || 0)

const formatDate = (date) =>
  new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })

const formatVoucherType = (type) =>
({
  loan_disbursement: 'Loan Disbursement',
  operational_expense: 'Operational Expense',
  dividend_payment: 'Dividend Payment',
  refund: 'Refund',
  other: 'Other',
}[type] || type)

const formatStatus = (status) => status.charAt(0).toUpperCase() + status.slice(1)

const getStatusBadgeClass = (status) =>
({
  pending: 'bg-yellow-100 text-yellow-800',
  approved: 'bg-green-100 text-green-800',
  paid: 'bg-blue-100 text-blue-800',
  rejected: 'bg-red-100 text-red-800',
  cancelled: 'bg-gray-100 text-gray-800',
}[status] || 'bg-gray-100 text-gray-800')
</script>


<style scoped>
.bg-header {
  background: linear-gradient(135deg, #0a2342 0%, #f97316 100%);
}

.filter-input {
  width: 100%;
  border-radius: 1rem; /* rounded-2xl */
  border: 1px solid #e2e8f0; /* slate-200 */
  background-color: #ffffff;
  padding: 0.75rem 1rem; /* py-3 px-4 */
  font-size: 0.875rem; /* text-sm */
  color: #334155; /* slate-700 */
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); /* shadow-sm */
  outline: none;
  transition: all 0.2s ease;
}

/* Dark mode */
.dark .filter-input {
  border-color: #334155; /* slate-700 */
  background-color: #020617; /* slate-950 */
  color: #e2e8f0; /* slate-200 */
}

/* Focus state */
.filter-input:focus {
  border-color: #f97316; /* orange-500 */
  box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
}
</style>
