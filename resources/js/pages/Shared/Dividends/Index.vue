<template>
  <AppLayout :breadcrumbs="[{ title: 'Dividends', href: '/dividends' }]">
    <Head title="Dividends Management" />

    <!-- Header -->
    <div class="bg-header text-white py-5 mt-5 mx-5 px-6 sm:px-10 rounded-3xl shadow-lg">
      <h2 class="text-xl sm:text-2xl font-bold tracking-tight">
        Dividends Management
      </h2>
      <p class="mt-2 text-sm sm:text-base opacity-90">
        Track, approve, and distribute member dividends with ease.
      </p>
    </div>

    <div class="py-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="card in [
              { label: 'Total Dividends', value: stats.total_dividends, color: 'bg-[#0a2342]', icon: 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z' },
              { label: 'Total Distributed', value: 'KSh ' + formatCurrency(stats.total_distributed), color: 'bg-orange-500', icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-10.707l-4 4a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L9 9.586l3.293-3.293a1 1 0 111.414 1.414z' },
              { label: 'Pending Approval', value: stats.pending_approval, color: 'bg-yellow-500', icon: 'M10 2a8 8 0 100 16 8 8 0 000-16zm1 9H9V5a1 1 0 112 0v6z' },
              { label: 'Ready to Distribute', value: stats.approved_pending_distribution, color: 'bg-blue-500', icon: 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-10.707l-4 4a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L9 9.586l3.293-3.293a1 1 0 111.414 1.414z' },
            ]"
            :key="card.label"
            class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-md border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-6"
          >
            <div class="flex items-center space-x-4">
              <div
                :class="[card.color, 'w-12 h-12 rounded-xl flex items-center justify-center shadow-md']"
              >
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path :d="card.icon" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500">{{ card.label }}</p>
                <p class="text-lg font-semibold text-[#0a2342]">{{ card.value }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters + Actions -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-md p-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
              <select
                v-model="filters.status"
                @change="applyFilters"
                class="rounded-lg border-gray-300 text-sm px-3 py-2 shadow-sm focus:border-[#0a2342] focus:ring focus:ring-blue-100"
              >
                <option value="all">All Status</option>
                <option value="calculated">Calculated</option>
                <option value="approved">Approved</option>
                <option value="distributed">Distributed</option>
              </select>

              <select
                v-model="filters.year"
                @change="applyFilters"
                class="rounded-lg border-gray-300 text-sm px-3 py-2 shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-100"
              >
                <option value="">All Years</option>
                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-3">
              <Link
                :href="route('dividends.analytics.history')"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-orange-100 text-orange-700 text-sm font-medium hover:bg-orange-200 transition"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M4 13l4-4 3 3 5-6a1 1 0 111.6 1.2l-6 7a1 1 0 01-1.4.1L8 11l-3.3 3.3a1 1 0 11-1.4-1.4z"/>
                </svg>
                Analytics
              </Link>

              <Link
                :href="route('dividends.create')"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#0a2342] text-white text-sm font-medium hover:bg-blue-900 transition"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                </svg>
                Calculate New Dividend
              </Link>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-md overflow-hidden hover:shadow-lg transition-all duration-300">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-blue-50">
                <tr>
                  <th
                    v-for="head in ['Year', 'Total Profit', 'Dividend Rate', 'Total Dividends', 'Status', 'Dates', 'Actions']"
                    :key="head"
                    class="px-6 py-3 text-left font-semibold text-[#0a2342] uppercase tracking-wide text-xs"
                  >
                    {{ head }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="dividend in dividends.data"
                  :key="dividend.id"
                  class="hover:bg-orange-50 transition duration-200"
                >
                  <td class="px-6 py-4 font-medium text-blue-900">{{ dividend.dividend_year }}</td>
                  <td class="px-6 py-4 text-gray-700">KSh {{ formatCurrency(dividend.total_profit) }}</td>
                  <td class="px-6 py-4 text-gray-700">{{ dividend.dividend_rate }}%</td>
                  <td class="px-6 py-4 text-gray-700">KSh {{ formatCurrency(dividend.total_dividends) }}</td>
                  <td class="px-6 py-4">
                    <span
                      :class="getStatusClass(dividend.status)"
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ formatStatus(dividend.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-500">
                    <div>Calculated: {{ formatDate(dividend.calculation_date) }}</div>
                    <div v-if="dividend.approval_date">Approved: {{ formatDate(dividend.approval_date) }}</div>
                    <div v-if="dividend.distribution_date">Distributed: {{ formatDate(dividend.distribution_date) }}</div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-3">
                      <Link :href="route('dividends.show', dividend.id)" class="text-blue-700 hover:underline">View</Link>
                      <Link v-if="dividend.status === 'calculated'" :href="route('dividends.edit', dividend.id)" class="text-orange-600 hover:underline">Edit</Link>
                      <button v-if="dividend.status === 'calculated'" @click="approveDividend(dividend)" class="text-green-600 hover:underline">Approve</button>
                      <button v-if="dividend.status === 'approved'" @click="distributeDividend(dividend)" class="text-purple-600 hover:underline">Distribute</button>
                      <button v-if="dividend.status === 'calculated'" @click="confirmDelete(dividend)" class="text-red-600 hover:underline">Delete</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="dividends.links" class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <Pagination :links="dividends.links" />
          </div>
        </div>
      </div>
    </div>

    <!-- Modals (unchanged) -->
    <!-- ... keep your existing modals code here ... -->
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  dividends: Object,
  availableYears: Array,
  filters: Object,
  stats: Object,
})

const showDeleteModal = ref(false)
const showApproveModal = ref(false)
const showDistributeModal = ref(false)
const selectedDividend = ref(null)
const processing = ref(false)

const filters = reactive({
  status: props.filters.status || 'all',
  year: props.filters.year || '',
})

const approvalForm = useForm({
  approval_notes: '',
})

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0)

const formatDate = (date) =>
  date ? new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A'

const formatStatus = (status) => status.charAt(0).toUpperCase() + status.slice(1)

const getStatusClass = (status) => ({
  calculated: 'bg-yellow-100 text-yellow-800',
  approved: 'bg-blue-100 text-blue-800',
  distributed: 'bg-green-100 text-green-800',
}[status] || 'bg-gray-100 text-gray-800')

const applyFilters = () => router.get(route('dividends.index'), filters, { preserveState: true, replace: true })

const confirmDelete = (dividend) => {
  selectedDividend.value = dividend
  showDeleteModal.value = true
}

const deleteDividend = () => {
  processing.value = true
  router.delete(route('dividends.destroy', selectedDividend.value.id), {
    onFinish: () => {
      processing.value = false
      showDeleteModal.value = false
      selectedDividend.value = null
    },
  })
}

const approveDividend = (dividend) => {
  selectedDividend.value = dividend
  approvalForm.reset()
  showApproveModal.value = true
}

const distributeDividend = (dividend) => {
  selectedDividend.value = dividend
  showDistributeModal.value = true
}
</script>

<style scoped>
.bg-header {
  background: linear-gradient(135deg, #0a2342 0%, #f97316 100%);
}
</style>
