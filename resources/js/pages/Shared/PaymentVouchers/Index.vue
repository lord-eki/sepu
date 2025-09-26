<template>
  <Head title="Payment Vouchers" />

   <AppLayout :breadcrumbs="[{ title: 'Vouchers', href: '/vouchers' }]">
    <!-- Header -->
      <div class="flex justify-between px-4 sm:px-6 pt-4 items-center">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800 tracking-tight">
          Payment Vouchers
        </h2>
        <Link
          :href="route('vouchers.create')"
          class="inline-flex items-center px-3 sm:px-4 py-2 rounded-lg bg-blue-600 text-white text-sm shadow-sm hover:bg-blue-700 transition"
        >
          <PlusCircleIcon class="h-5 w-5 mr-2" />
          Create Voucher
        </Link>
      </div>

    <div class="py-10 px-3 sm:px-1">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div
            v-for="card in [
              { label: 'Total Vouchers', value: stats.total_vouchers, icon: FileText, color: 'bg-blue-100 text-blue-600' },
              { label: 'Pending', value: stats.pending_vouchers, extra: formatCurrency(stats.total_pending_amount), icon: Clock, color: 'bg-yellow-100 text-yellow-600' },
              { label: 'Approved', value: stats.approved_vouchers, extra: formatCurrency(stats.total_approved_amount), icon: CheckCircle2Icon, color: 'bg-green-100 text-green-600' },
              { label: 'Paid', value: stats.paid_vouchers, extra: formatCurrency(stats.total_paid_amount), icon: CurrencyIcon, color: 'bg-indigo-100 text-indigo-600' }
            ]"
            :key="card.label"
            class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-4 sm:p-5"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0 p-3 rounded-xl" :class="card.color">
                <component :is="card.icon" class="h-4 sm:h-5 w-4 sm:w-5" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">{{ card.label }}</p>
                <p class="text-lg sm:text-xl font-bold text-gray-900">{{ card.value }}</p>
                <p v-if="card.extra" class="text-sm text-gray-500">{{ card.extra }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-sm p-5">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="form.status"
                class="w-full rounded-lg border border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                @change="applyFilters"
              >
                <option value="">All Statuses</option>
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
                class="w-full rounded-lg border border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                @change="applyFilters"
              >
                <option value="">All Types</option>
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
                class="w-full rounded-lg border border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                @change="applyFilters"
              />
            </div>

            <!-- Date To -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
              <input
                v-model="form.date_to"
                type="date"
                class="w-full rounded-lg text-sm border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                @change="applyFilters"
              />
            </div>

            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <div class="relative">
                <input
                  v-model="form.search"
                  type="text"
                  placeholder="Search vouchers..."
                  class="w-full pl-10 rounded-lg border border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                  @input="debouncedSearch"
                />
                <Search class="h-5 w-5 text-gray-400 absolute left-3 top-3" />
              </div>
            </div>
          </div>

          <div class="flex justify-end mt-2 lg:mt-0">
            <button
              @click="clearFilters"
              class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-200 rounded-lg"
            >
              Clear Filters
            </button>
          </div>
        </div>

        <!-- Vouchers Table -->
        <div class="bg-white rounded-2xl shadow-sm">
          <div class="overflow-x-auto">
            <table class="w-lg text-sm">
              <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                  <th class="px-4 py-3 text-left">Voucher Number</th>
                  <th class="px-4 py-3 text-left">Type</th>
                  <th class="px-4 py-3 text-left">Payee</th>
                  <th class="px-4 py-3 text-left">Amount</th>
                  <th class="px-4 py-3 text-left">Purpose</th>
                  <th class="px-4 py-3 text-left">Status</th>
                  <th class="px-4 py-3 text-left">Created</th>
                  <th class="px-4 py-3 text-left">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr
                  v-for="voucher in vouchers.data"
                  :key="voucher.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-4 py-4 font-medium text-blue-600">
                    <Link :href="route('vouchers.show', voucher.id)" class="hover:underline">
                      {{ voucher.voucher_number }}
                    </Link>
                  </td>
                  <td class="px-4 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700">
                      {{ formatVoucherType(voucher.voucher_type) }}
                    </span>
                  </td>
                  <td class="px-4 py-4">{{ voucher.payee_name }}</td>
                  <td class="px-4 py-4">{{ formatCurrency(voucher.amount) }}</td>
                  <td class="px-4 py-4 truncate max-w-xs">{{ voucher.purpose }}</td>
                  <td class="px-4 py-4">
                    <span
                      :class="getStatusBadgeClass(voucher.status)"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    >
                      {{ formatStatus(voucher.status) }}
                    </span>
                  </td>
                  <td class="px-4 py-4 text-gray-500">
                    {{ formatDate(voucher.created_at) }}
                  </td>
                  <td class="px-4 py-4 flex space-x-2">
                    <Link
                      :href="route('vouchers.show', voucher.id)"
                      class="text-blue-600 hover:text-blue-800"
                      title="View"
                    >
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
          </div>

          <!-- Empty State -->
          <div v-if="vouchers.data.length === 0" class="text-center py-12">
            <FileText class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No vouchers found</h3>
            <p class="mt-1 text-sm text-gray-500">
              Get started by creating a new payment voucher.
            </p>
            <div class="mt-6">
              <Link
                :href="route('vouchers.create')"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white shadow-sm hover:bg-blue-700"
              >
                <PlusCircleIcon class="h-5 w-5 mr-2" />
                Create Voucher
              </Link>
            </div>
        
        </div>

        <!-- Pagination -->
        <div
          v-if="vouchers.data.length > 0"
          class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 rounded-2xl shadow-sm"
        >
          <div class="hidden sm:block">
            <p class="text-sm text-gray-600">
              Showing
              <span class="font-medium">{{ vouchers.from }}</span>
              to
              <span class="font-medium">{{ vouchers.to }}</span>
              of
              <span class="font-medium">{{ vouchers.total }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="inline-flex -space-x-px rounded-lg shadow-sm">
              <Link
                v-if="vouchers.prev_page_url"
                :href="vouchers.prev_page_url"
                class="px-3 py-2 rounded-l-lg border bg-white text-sm text-gray-500 hover:bg-gray-50"
              >
                <ChevronLeft class="h-4 w-4" />
              </Link>
              <template v-for="link in vouchers.links" :key="link.label">
                <Link
                  v-if="link.url && link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                  :href="link.url"
                  :class="link.active
                    ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                  class="px-3 py-2 border text-sm"
                  v-html="link.label"
                />
              </template>
              <Link
                v-if="vouchers.next_page_url"
                :href="vouchers.next_page_url"
                class="px-3 py-2 rounded-r-lg border bg-white text-sm text-gray-500 hover:bg-gray-50"
              >
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
import { ref, reactive, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue';
import { CheckCircle2Icon, ChevronLeft, ChevronRight, Clock, CurrencyIcon, Eye, FileText, Pencil, PlusCircleIcon, Search } from 'lucide-vue-next'

const props = defineProps({
  vouchers: Object,
  stats: Object,
  filters: Object
})

const form = reactive({
  status: props.filters.status || '',
  voucher_type: props.filters.voucher_type || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  search: props.filters.search || ''
})

let searchTimeout = null

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  router.get(route('vouchers.index'), form, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  form.status = ''
  form.voucher_type = ''
  form.date_from = ''
  form.date_to = ''
  form.search = ''
  applyFilters()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-KE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatVoucherType = (type) => {
  const types = {
    loan_disbursement: 'Loan Disbursement',
    operational_expense: 'Operational Expense',
    dividend_payment: 'Dividend Payment',
    refund: 'Refund',
    other: 'Other'
  }
  return types[type] || type
}

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    paid: 'bg-blue-100 text-blue-800',
    rejected: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>