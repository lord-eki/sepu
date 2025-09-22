<template>
  <Head title="Payment Vouchers" />
  
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Payment Vouchers
        </h2>
        <Link
          :href="route('vouchers.create')"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-flex items-center"
        >
          <PlusCircleIcon class="h-5 w-5 mr-2" />
          Create Voucher
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <FileText class="h-8 w-8 text-blue-500" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Vouchers</p>
                  <p class="text-2xl font-bold text-gray-900">{{ stats.total_vouchers }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Clock class="h-8 w-8 text-yellow-500" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Pending</p>
                  <p class="text-2xl font-bold text-gray-900">{{ stats.pending_vouchers }}</p>
                  <p class="text-sm text-gray-500">{{ formatCurrency(stats.total_pending_amount) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CheckCircle2Icon class="h-8 w-8 text-green-500" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Approved</p>
                  <p class="text-2xl font-bold text-gray-900">{{ stats.approved_vouchers }}</p>
                  <p class="text-sm text-gray-500">{{ formatCurrency(stats.total_approved_amount) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CurrencyIcon class="h-8 w-8 text-blue-500" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Paid</p>
                  <p class="text-2xl font-bold text-gray-900">{{ stats.paid_vouchers }}</p>
                  <p class="text-sm text-gray-500">{{ formatCurrency(stats.total_paid_amount) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select
                  v-model="form.status"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select
                  v-model="form.voucher_type"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                <input
                  v-model="form.date_from"
                  type="date"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                  @change="applyFilters"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                <input
                  v-model="form.date_to"
                  type="date"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                  @change="applyFilters"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <div class="relative">
                  <input
                    v-model="form.search"
                    type="text"
                    placeholder="Search vouchers..."
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-10 p-2"
                    @input="debouncedSearch"
                  />
                  <Search class="h-5 w-5 text-gray-400 absolute left-3 top-3" />
                </div>
              </div>
            </div>

            <div class="flex justify-end mt-4 space-x-2">
              <button
                @click="clearFilters"
                class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md"
              >
                Clear Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Vouchers Table -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Voucher Number
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Payee
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Amount
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Purpose
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Created
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="voucher in vouchers.data" :key="voucher.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                    <Link :href="route('vouchers.show', voucher.id)" class="hover:text-blue-800">
                      {{ voucher.voucher_number }}
                    </Link>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                      {{ formatVoucherType(voucher.voucher_type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ voucher.payee_name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(voucher.amount) }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                    {{ voucher.purpose }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span :class="getStatusBadgeClass(voucher.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                      {{ formatStatus(voucher.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(voucher.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="flex space-x-2">
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
                    </div>
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
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
              >
                <PlusCircleIcon class="h-5 w-5 mr-2" />
                Create Voucher
              </Link>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="vouchers.data.length > 0" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow-sm">
          <div class="flex-1 flex justify-between sm:hidden">
            <Link
              v-if="vouchers.prev_page_url"
              :href="vouchers.prev_page_url"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Previous
            </Link>
            <Link
              v-if="vouchers.next_page_url"
              :href="vouchers.next_page_url"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Next
            </Link>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
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
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <Link
                  v-if="vouchers.prev_page_url"
                  :href="vouchers.prev_page_url"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                >
                  <ChevronLeft class="h-5 w-5" />
                </Link>
                
                <template v-for="link in vouchers.links" :key="link.label">
                  <Link
                    v-if="link.url && link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                    :href="link.url"
                    :class="link.active 
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                    v-html="link.label"
                  />
                  <span
                    v-else-if="!link.url && link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500"
                    v-html="link.label"
                  />
                </template>

                <Link
                  v-if="vouchers.next_page_url"
                  :href="vouchers.next_page_url"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                >
                  <ChevronRight class="h-5 w-5" />
                </Link>
              </nav>
            </div>
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