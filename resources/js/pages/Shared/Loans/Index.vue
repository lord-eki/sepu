<template>
 <AppLayout :breadcrumbs="[{ title: 'Loans', href: '/loans' }]">
 
    <Head title="Loan Management" />
    <div class="pt-4">
      <div class="max-w-7xl mx-auto py-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <h2 class="font-bold text-lg sm:text-xl text-gray-800">Loan Management</h2>
          <div class="flex gap-2">
              <Link :href="route('loans.create')"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-blue-700 transition">
              New Loan Application
              </Link>
              <Link :href="route('loan-calculator.index')"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-200 px-4 py-2 text-sm font-medium text-blue-800 shadow hover:bg-indigo-300 hover:text-gray-800 transition">
              Loan Calculator
              </Link>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div v-for="card in [
            {
              label: 'Total Applications',
              value: summary.total_loans,
              color: 'bg-blue-500',
              icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
            },
            {
              label: 'Pending',
              value: summary.pending_loans,
              color: 'bg-yellow-500',
              icon: 'M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
            },
            {
              label: 'Active Loans',
              value: summary.disbursed_loans,
              color: 'bg-green-500',
              icon: 'M5 13l4 4L19 7M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z'
            },
            {
              label: 'Overdue',
              value: summary.overdue_loans,
              color: 'bg-red-500',
              icon: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
            }
          ]" :key="card.label" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-5">
            <div class="flex items-center">
              <div
                :class="[card.color, 'w-7 sm:w-9 h-7 sm:h-9 rounded-xl flex items-center justify-center text-white']">
                <svg class="w-4 sm:w-5 h-4 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon" />
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">{{ card.label }}</p>
                <p class="text-base sm:text-lg font-bold text-gray-900">{{ card.value }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
          <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
              <select v-model="filters.status" id="status"
                class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="disbursed">Disbursed</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>

            <div>
              <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
              <input v-model="filters.search" type="text" id="search" placeholder="Loan number, member name..."
                class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2" />
            </div>

            <div>
              <label for="date_from" class="block text-sm font-medium text-gray-700">Date From</label>
              <input v-model="filters.date_from" type="date" id="date_from"
                class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2" />
            </div>

            <div>
              <label for="date_to" class="block text-sm font-medium text-gray-700">Date To</label>
              <input v-model="filters.date_to" type="date" id="date_to"
                class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2" />
            </div>

            <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap gap-2">
              <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow">
                Apply Filters
              </button>
              <button type="button" @click="clearFilters"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
                Clear
              </button>
            </div>
          </form>
        </div>

        <!-- Loans Table -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Loan Applications</h3>

          <div class="overflow-x-auto -mx-4 sm:-mx-0">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-blue-50">
                <tr>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Loan Number</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Member</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Product</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Amount</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Date</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="loan in loans.data" :key="loan.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 font-medium text-gray-900">{{ loan.loan_number }}</td>
                  <td class="px-4 py-3 text-gray-900">
                    {{ loan.member.first_name }} {{ loan.member.last_name }}<br />
                    <span class="text-xs text-gray-500">{{ loan.member.membership_id }}</span>
                  </td>
                  <td class="px-4 py-3">{{ loan.loan_product.name }}</td>
                  <td class="px-4 py-3">
                    KES {{ formatCurrency(loan.applied_amount) }}<br />
                    <span v-if="loan.approved_amount" class="text-xs text-green-600">
                      Approved: KES {{ formatCurrency(loan.approved_amount) }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <span :class="getStatusBadgeClass(loan.status)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ formatStatus(loan.status) }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-gray-500">{{ formatDate(loan.application_date) }}</td>
                  <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-2">
                      <Link :href="route('loans.show', loan.id)" class="text-indigo-600 hover:text-indigo-900">View
                      </Link>
                      <Link v-if="canEdit(loan)" :href="route('loans.edit', loan.id)"
                        class="text-blue-600 hover:text-blue-900">Edit</Link>
                      <button v-if="canApprove(loan)" @click="showApprovalModal(loan)"
                        class="text-green-600 hover:text-green-900">Approve</button>
                      <button v-if="canReject(loan)" @click="showRejectionModal(loan)"
                        class="text-red-600 hover:text-red-900">Reject</button>
                      <button v-if="canDisburse(loan)" @click="showDisbursementModal(loan)"
                        class="text-purple-600 hover:text-purple-900">Disburse</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="mt-6">
            <Pagination :data="loans" />
          </div>
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <div v-if="showApproval" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeApprovalModal">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <!-- Modal panel -->
        <div
          class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <form @submit.prevent="approveLoan">
            <div>
              <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-5">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                  Approve Loan Application
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Approve loan {{ selectedLoan?.loan_number }} for {{ selectedLoan?.member?.first_name }} {{
            selectedLoan?.member?.last_name }}
                  </p>
                </div>
              </div>
            </div>

            <div class="mt-4">
              <label for="approved_amount" class="block text-sm font-medium text-gray-700">Approved Amount</label>
              <input v-model="approvalForm.approved_amount" type="number" step="0.01" id="approved_amount" required
                :max="selectedLoan?.applied_amount"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
              <p class="text-xs text-gray-500 mt-1">Applied Amount: KES {{ formatCurrency(selectedLoan?.applied_amount)
                }}</p>
            </div>

            <div class="mt-4">
              <label for="approval_notes" class="block text-sm font-medium text-gray-700">Approval Notes</label>
              <textarea v-model="approvalForm.approval_notes" id="approval_notes" rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"></textarea>
            </div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
              <button type="submit"
                class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 sm:col-start-2">
                Approve Loan
              </button>
              <button type="button"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                @click="closeApprovalModal">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Rejection Modal -->
    <div v-if="showRejection" class="fixed inset-0 z-50 overflow-y-auto" @click.self="closeRejectionModal">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <!-- Modal panel -->
        <div
          class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <form @submit.prevent="rejectLoan">
            <div>
              <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-5">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                  Reject Loan Application
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Reject loan {{ selectedLoan?.loan_number }} for {{ selectedLoan?.member?.first_name }} {{
            selectedLoan?.member?.last_name }}
                  </p>
                </div>
              </div>
            </div>

            <div class="mt-4">
              <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Rejection Reason</label>
              <textarea v-model="rejectionForm.rejection_reason" id="rejection_reason" rows="4" required
                placeholder="Please provide a detailed reason for rejection..."
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"></textarea>
            </div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
              <button type="submit"
                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 sm:col-start-2">
                Reject Loan
              </button>
              <button type="button"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                @click="closeRejectionModal">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'

const props = defineProps({
  loans: Object,
  summary: Object,
  auth: Object
})

// Reactive data
const filters = reactive({
  status: '',
  search: '',
  member_id: '',
  loan_product_id: '',
  date_from: '',
  date_to: ''
})

const showApproval = ref(false)
const showRejection = ref(false)
const selectedLoan = ref(null)

const approvalForm = reactive({
  approved_amount: '',
  approval_notes: ''
})

const rejectionForm = reactive({
  rejection_reason: ''
})

// Methods
const applyFilters = () => {
  router.get(route('loans.index'), filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  applyFilters()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

const formatStatus = (status) => {
  return status.replace('_', ' ').toUpperCase()
}

const getStatusBadgeClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'approved': 'bg-green-100 text-green-800',
    'disbursed': 'bg-blue-100 text-blue-800',
    'completed': 'bg-gray-100 text-gray-800',
    'rejected': 'bg-red-100 text-red-800',
    'cancelled': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const canEdit = (loan) => {
  return loan.status === 'pending' && ['admin', 'loan_officer', 'management'].includes(props.auth.user.role)
}

const canApprove = (loan) => {
  return loan.status === 'pending' && ['admin', 'management'].includes(props.auth.user.role)
}

const canReject = (loan) => {
  return ['pending', 'approved'].includes(loan.status) && ['admin', 'management'].includes(props.auth.user.role)
}

const canDisburse = (loan) => {
  return loan.status === 'approved' && ['admin', 'accountant'].includes(props.auth.user.role)
}

const showApprovalModal = (loan) => {
  selectedLoan.value = loan
  approvalForm.approved_amount = loan.applied_amount
  approvalForm.approval_notes = ''
  showApproval.value = true
}

const showRejectionModal = (loan) => {
  selectedLoan.value = loan
  rejectionForm.rejection_reason = ''
  showRejection.value = true
}

const showDisbursementModal = (loan) => {
  router.visit(route('loans.show', loan.id) + '#disburse')
}

const closeApprovalModal = () => {
  showApproval.value = false
  selectedLoan.value = null
  Object.keys(approvalForm).forEach(key => {
    approvalForm[key] = ''
  })
}

const closeRejectionModal = () => {
  showRejection.value = false
  selectedLoan.value = null
  Object.keys(rejectionForm).forEach(key => {
    rejectionForm[key] = ''
  })
}

const approveLoan = () => {
  router.post(route('loans.approve', selectedLoan.value.id), approvalForm, {
    onSuccess: () => {
      closeApprovalModal()
    }
  })
}

const rejectLoan = () => {
  router.post(route('loans.reject', selectedLoan.value.id), rejectionForm, {
    onSuccess: () => {
      closeRejectionModal()
    }
  })
}
</script>