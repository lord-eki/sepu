<template>
  <AppLayout :breadcrumbs="[{ title: 'Vouchers', href: '/vouchers' }]">
    <Head title="Payment Vouchers" />

    <!-- Header -->
    <div class="bg-header text-white py-5 mt-5 mx-5 px-6 sm:px-10 rounded-3xl shadow-lg">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-xl sm:text-2xl font-bold tracking-tight">
            Payment Vouchers
          </h2>
          <p class="mt-1 text-sm sm:text-base opacity-90">
            Manage, approve, and track all outgoing payment vouchers.
          </p>
        </div>

        <Link
          :href="route('vouchers.create')"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white text-[#0a2342] font-semibold text-sm shadow-md hover:bg-orange-100 transition"
        >
          <PlusCircleIcon class="h-5 w-5 text-orange-600" />
          Create Voucher
        </Link>
      </div>
    </div>

    <div class="py-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="card in [
              { label: 'Total Vouchers', value: stats.total_vouchers, icon: FileText, color: 'bg-[#0a2342]' },
              { label: 'Pending', value: stats.pending_vouchers, extra: formatCurrency(stats.total_pending_amount), icon: Clock, color: 'bg-yellow-500' },
              { label: 'Approved', value: stats.approved_vouchers, extra: formatCurrency(stats.total_approved_amount), icon: CheckCircle2Icon, color: 'bg-green-600' },
              { label: 'Paid', value: stats.paid_vouchers, extra: formatCurrency(stats.total_paid_amount), icon: CurrencyIcon, color: 'bg-orange-500' }
            ]"
            :key="card.label"
            class="bg-white/90 backdrop-blur-sm rounded-2xl border border-gray-100 shadow-md hover:shadow-lg transition-all duration-300 p-6"
          >
            <div class="flex items-center space-x-4">
              <div
                :class="[card.color, 'w-12 h-12 rounded-xl flex items-center justify-center shadow-md']"
              >
                <component :is="card.icon" class="w-6 h-6 text-white" />
              </div>
              <div>
                <p class="text-sm text-gray-500">{{ card.label }}</p>
                <p class="text-lg font-semibold text-[#0a2342]">{{ card.value }}</p>
                <p v-if="card.extra" class="text-sm text-gray-400">{{ card.extra }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6">
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="form.status"
                @change="applyFilters"
                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#0a2342] focus:ring-blue-100 p-2"
              >
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="paid">Paid</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <!-- Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
              <select
                v-model="form.voucher_type"
                @change="applyFilters"
                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-100 p-2"
              >
                <option value="">All</option>
                <option value="loan_disbursement">Loan Disbursement</option>
                <option value="operational_expense">Operational Expense</option>
                <option value="dividend_payment">Dividend Payment</option>
                <option value="refund">Refund</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Date From -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
              <input
                v-model="form.date_from"
                type="date"
                @change="applyFilters"
                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-100 p-2"
              />
            </div>

            <!-- Date To -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
              <input
                v-model="form.date_to"
                type="date"
                @change="applyFilters"
                class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-100 p-2"
              />
            </div>

            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <div class="relative">
                <input
                  v-model="form.search"
                  @input="debouncedSearch"
                  type="text"
                  placeholder="Search vouchers..."
                  class="w-full pl-10 rounded-lg border border-gray-300 text-sm shadow-sm focus:border-[#0a2342] focus:ring-blue-100 p-2"
                />
                <Search class="h-5 w-5 text-gray-400 absolute left-3 top-3" />
              </div>
            </div>
          </div>

          <!-- Clear Filters -->
          <div class="flex justify-end mt-4">
            <button
              @click="clearFilters"
              class="px-4 py-2 rounded-lg text-gray-700 bg-gray-100 hover:bg-orange-50 border border-gray-200 transition"
            >
              Clear Filters
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-blue-50 text-[#0a2342] uppercase text-xs font-semibold tracking-wide">
                <tr>
                  <th v-for="head in ['Voucher Number', 'Type', 'Payee', 'Amount', 'Purpose', 'Status', 'Created', 'Actions']" :key="head" class="px-6 py-3 text-left">
                    {{ head }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="voucher in vouchers.data" :key="voucher.id" class="hover:bg-orange-50 transition duration-200">
                  <td class="px-6 py-4 font-medium text-blue-800">
                    <Link :href="route('vouchers.show', voucher.id)" class="hover:underline">
                      {{ voucher.voucher_number }}
                    </Link>
                  </td>
                  <td class="px-6 py-4">
                    <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">
                      {{ formatVoucherType(voucher.voucher_type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4">{{ voucher.payee_name }}</td>
                  <td class="px-6 py-4">KSh {{ formatCurrency(voucher.amount) }}</td>
                  <td class="px-6 py-4 truncate max-w-xs">{{ voucher.purpose }}</td>
                  <td class="px-6 py-4">
                    <span
                      :class="getStatusBadgeClass(voucher.status)"
                      class="px-2 py-1 rounded-full text-xs font-semibold"
                    >
                      {{ formatStatus(voucher.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-500">{{ formatDate(voucher.created_at) }}</td>
                  <td class="px-6 py-4 flex space-x-3">
                    <Link :href="route('vouchers.show', voucher.id)" class="text-blue-600 hover:text-blue-800" title="View">
                      <Eye class="h-4 w-4" />
                    </Link>
                    <Link
                      v-if="voucher.status === 'pending'"
                      :href="route('vouchers.edit', voucher.id)"
                      class="text-green-600 hover:text-green-800"
                      title="Edit"
                    >
                      <Pencil class="h-4 w-4" />
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="vouchers.data.length === 0" class="text-center py-12">
            <FileText class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No vouchers found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new payment voucher.</p>
            <div class="mt-6">
              <Link
                :href="route('vouchers.create')"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-[#0a2342] text-white font-semibold shadow-sm hover:bg-blue-900 transition"
              >
                <PlusCircleIcon class="h-5 w-5 mr-2" />
                Create Voucher
              </Link>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="vouchers.data.length > 0" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-600 hidden sm:block">
              Showing <span class="font-medium">{{ vouchers.from }}</span>–<span class="font-medium">{{ vouchers.to }}</span> of
              <span class="font-medium">{{ vouchers.total }}</span> results
            </p>

            <nav class="inline-flex -space-x-px rounded-lg shadow-sm">
              <Link v-if="vouchers.prev_page_url" :href="vouchers.prev_page_url" class="px-3 py-2 rounded-l-lg border bg-white text-sm text-gray-500 hover:bg-gray-50">
                <ChevronLeft class="h-4 w-4" />
              </Link>

              <template v-for="link in vouchers.links" :key="link.label">
                <Link
                  v-if="link.url && link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                  :href="link.url"
                  v-html="link.label"
                  :class="link.active
                    ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                  class="px-3 py-2 border text-sm"
                />
              </template>

              <Link v-if="vouchers.next_page_url" :href="vouchers.next_page_url" class="px-3 py-2 rounded-r-lg border bg-white text-sm text-gray-500 hover:bg-gray-50">
                <ChevronRight class="h-4 w-4" />
              </Link>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { CheckCircle2Icon, ChevronLeft, ChevronRight, Clock, CurrencyIcon, Eye, FileText, Pencil, PlusCircleIcon, Search } from 'lucide-vue-next'

const props = defineProps({
  vouchers: Object,
  stats: Object,
  filters: Object,
})

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
</style>
