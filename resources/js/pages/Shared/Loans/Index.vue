<template>
  <AppLayout :breadcrumbs="[{ title: 'Loans', href: '/loans' }]">

    <Head title="Loan Management" />

    <div class="loan-page pt-4 pb-8">
      <!-- Gradient header -->
      <header class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div
          class="rounded-2xl overflow-hidden shadow-md border border-transparent transform-gpu transition-all duration-300">
          <div class="bg-gradient-to-r from-[#06203a] to-[#0a2342] px-6 py-6 sm:py-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div class="flex items-center gap-4">
                <div class="rounded-lg p-3 bg-white/6 shadow md:shadow-lg">
                  <!-- icon -->
                  <svg class="w-6 sm:w-7 h-6 sm:h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>

                <div>
                  <h1 class="text-white font-semibold text-xl sm:text-2xl tracking-tight">Loan Management</h1>
                  <p class="text-orange-200 max-sm:text-xs text-sm mt-0.5">Manage loan applications, approvals and
                    disbursements</p>
                </div>
              </div>

              <div class="flex max-sm:flex-col w-fit gap-2">
                <!-- New Loan Application -->
                <Link :href="route('loans.create')"
                  class="inline-flex items-center gap-2 rounded-lg bg-white text-[#0a2342] px-4 py-2 text-sm font-medium shadow hover:scale-[1.01] transition">
                New Loan Application
                </Link>

                <!-- Check Eligibility -->
                <button @click="openModal = true"
                  class="inline-flex items-center gap-2 rounded-lg bg-blue-600 text-white hover:cursor-pointer px-4 py-2 text-sm font-medium shadow hover:bg-blue-700 transition">
                  Check Eligibility
                </button>

                <!-- Loan Calculator -->
                <Link :href="route('loan-calculator.index')"
                  class="inline-flex items-center gap-2 rounded-lg bg-orange-500 text-white px-4 py-2 text-sm font-medium shadow hover:opacity-95 transition">
                Loan Calculator
                </Link>
              </div>
            </div>
          </div>
        </div>
      </header>


      <!-- Member Selection Modal -->
      <div v-if="openModal" class="fixed inset-0 bg-black/50 flex justify-center items-center z-9999">
        <div class="bg-white p-6 rounded-lg shadow-lg max-sm:w-[90%] w-96">
          <h2 class="text-lg font-semibold mb-4 text-gray-700">Select Member</h2>

          <select v-model="selectedMember"
            class="w-full border border-gray-300 rounded-md p-2 mb-4 focus:ring focus:ring-blue-200">
            <option disabled value="">-- Choose a member --</option>
            <option v-for="member in members" :key="member.id" :value="member.id">
              {{ member.name }}
            </option>
          </select>

          <div class="flex justify-end gap-2">
            <button @click="openModal = false" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
              Cancel
            </button>
            <button @click="checkEligibility" :disabled="!selectedMember"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
              Continue
            </button>
          </div>
        </div>
      </div>


      <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Summary cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <transition-group name="fade" tag="div" class="contents">
            <div v-for="card in cards" :key="card.label" class="card-glass p-4 rounded-2xl shadow-lg border">
              <div class="flex items-center gap-4">
                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center', card.bg]">
                  <svg class="w-5  h-5  text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" :d="card.icon" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500">{{ card.label }}</p>
                  <p class="text-xl font-bold text-gray-900">{{ card.value }}</p>
                </div>
              </div>
            </div>
          </transition-group>
        </section>

        <!-- Filters -->
        <section class="bg-white rounded-2xl p-6 shadow-sm border">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
            <div class="text-sm text-gray-500">Refine loan list</div>
          </div>

          <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select v-model="filters.status" id="status"
                class="mt-1 block w-full rounded-lg border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-orange-200">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="disbursed">Disbursed</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>

            <div>
              <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <input v-model="filters.search" id="search" type="text" placeholder="Loan number, member name..."
                class="mt-1 block w-full rounded-lg border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-blue-50" />
            </div>

            <div>
              <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
              <input v-model="filters.date_from" id="date_from" type="date"
                class="mt-1 block w-full rounded-lg border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-blue-50" />
            </div>

            <div>
              <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
              <input v-model="filters.date_to" id="date_to" type="date"
                class="mt-1 block w-full rounded-lg border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-blue-50" />
            </div>

            <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap gap-2 justify-start mt-2">
              <button type="submit"
                class="inline-flex items-center hover:cursor-pointer gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:scale-[1.01] transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>Apply&nbsp;<span class="max-sm:hidden">Filters</span></span>
              </button>

              <button type="button" @click="clearFilters"
                class="inline-flex items-center hover:cursor-pointer gap-2 bg-gray-100 text-gray-800 px-4 py-2 rounded-lg border hover:bg-gray-200 transition">
                Clear
              </button>

              <button type="button" @click="openQuickExport"
                class="ml-auto inline-flex items-center hover:cursor-pointer gap-2 bg-orange-100 text-[#0a2342] px-4 py-2 rounded-lg hover:bg-orange-200 transition">
                <svg class="w-4 h-4 max-sm:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" />
                </svg>
                Export
              </button>
            </div>
          </form>
        </section>

        <!-- Loans table -->
        <section class="bg-white rounded-2xl p-6 shadow-sm border">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Loan Applications</h3>
            <div class="text-sm text-gray-500">Showing results</div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm rounded-lg">
              <thead class="bg-blue-50 sticky top-0">
                <tr>
                  <th class="px-4 py-3 text-left font-medium text-blue-900 tracking-wider">Loan Number</th>
                  <th class="px-4 py-3 text-left font-medium text-blue-900 tracking-wider">Member</th>
                  <th class="px-4 py-3 text-left font-medium text-blue-900 tracking-wider">Product</th>
                  <th class="px-4 py-3 text-left font-medium text-blue-900 tracking-wider">Amount</th>
                  <th class="px-4 py-3 text-left font-medium text-blue-900 tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left font-medium text-blue-900 tracking-wider">Date</th>
                  <th class="px-4 py-3 text-left font-medium text-blue-900 tracking-wider">Actions</th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y divide-gray-100">
                <tr v-for="loan in loans.data" :key="loan.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-4 py-3 font-medium text-gray-900">{{ loan.loan_number }}</td>
                  <td class="px-4 py-3 text-gray-900">
                    {{ loan.member.first_name }} {{ loan.member.last_name }}
                    <div class="text-xs text-gray-500 mt-0.5">{{ loan.member.membership_id }}</div>
                  </td>
                  <td class="px-4 py-3">{{ loan.loan_product.name }}</td>
                  <td class="px-4 py-3">
                    <div class="font-medium">KES {{ formatCurrency(loan.applied_amount) }}</div>
                    <div v-if="loan.approved_amount" class="text-xs text-green-600">Approved: KES {{
    formatCurrency(loan.approved_amount) }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <span
                      :class="getStatusBadgeClass(loan.status) + ' inline-flex px-2 py-1 text-xs font-semibold rounded-full'">
                      {{ formatStatus(loan.status) }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-gray-500">{{ formatDate(loan.application_date) }}</td>
                  <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-2">
                      <Link :href="route('loans.show', loan.id)" class="text-indigo-600 hover:text-indigo-900">View
                      </Link>
                      <Link v-if="canEdit(loan)" :href="route('loans.edit', loan.id)"
                        class="text-[#0a2342] hover:text-[#0a2342]">Edit</Link>
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

          <div class="mt-6">
            <Pagination :data="loans" />
          </div>
        </section>
      </main>
    </div>

    <!-- Approval Modal (animated, blurred backdrop) -->
    <transition name="modal">
      <div v-if="showApproval" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click.self="closeApprovalModal"></div>
        <div class="relative bg-white rounded-lg shadow-2xl max-w-lg w-full p-6 transform-gpu scale-in">
          <form @submit.prevent="approveLoan">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Approve Loan Application</h3>
                <p class="text-sm text-gray-500">Approve loan {{ selectedLoan?.loan_number }} for {{
    selectedLoan?.member?.first_name }} {{ selectedLoan?.member?.last_name }}</p>
              </div>
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700">Approved Amount</label>
              <input v-model="approvalForm.approved_amount" type="number" step="0.01" required
                :max="selectedLoan?.applied_amount"
                class="mt-1 block w-full rounded-md border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-green-100" />
              <p class="text-xs text-gray-500 mt-1">Applied Amount: KES {{ formatCurrency(selectedLoan?.applied_amount)
                }}</p>
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700">Approval Notes</label>
              <textarea v-model="approvalForm.approval_notes" rows="3"
                class="mt-1 block w-full rounded-md border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-green-100"></textarea>
            </div>

            <div class="mt-6 flex gap-3">
              <button type="submit"
                class="ml-auto inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">
                Approve Loan
              </button>
              <button type="button" @click="closeApprovalModal"
                class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold border">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- Rejection Modal -->
    <transition name="modal">
      <div v-if="showRejection" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click.self="closeRejectionModal"></div>
        <div class="relative bg-white rounded-lg shadow-2xl max-w-lg w-full p-6 transform-gpu scale-in">
          <form @submit.prevent="rejectLoan">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Reject Loan Application</h3>
                <p class="text-sm text-gray-500">Reject loan {{ selectedLoan?.loan_number }} for {{
    selectedLoan?.member?.first_name }} {{ selectedLoan?.member?.last_name }}</p>
              </div>
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700">Rejection Reason</label>
              <textarea v-model="rejectionForm.rejection_reason" rows="4" required
                placeholder="Please provide a detailed reason for rejection..."
                class="mt-1 block w-full rounded-md border border-gray-200 p-2 focus:outline-none focus:ring-2 focus:ring-red-100"></textarea>
            </div>

            <div class="mt-6 flex gap-3">
              <button type="submit"
                class="ml-auto inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">
                Reject Loan
              </button>
              <button type="button" @click="closeRejectionModal"
                class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold border">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'

const props = defineProps({
  loans: Object,
  summary: Object,
  auth: Object
})

// filters + forms (unchanged logic)
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
    'pending': 'bg-yellow-100 text-yellow-800 border border-yellow-200',
    'approved': 'bg-green-100 text-green-800 border border-green-200',
    'disbursed': 'bg-blue-100 text-blue-800 border border-blue-200',
    'completed': 'bg-gray-100 text-gray-800 border border-gray-200',
    'rejected': 'bg-red-100 text-red-800 border border-red-200',
    'cancelled': 'bg-red-100 text-red-800 border border-red-200'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 border border-gray-200'
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

// small helpers for UI
const openQuickExport = () => {
  // placeholder - hook your export logic here
  // e.g. router.visit(route('loans.export'), { data: filters })
  alert('Export feature: implement server export endpoint or client CSV here.')
}

// cards computed from summary prop
const cards = computed(() => ([
  { label: 'Total Applications', value: props.summary?.total_loans ?? 0, bg: 'bg-gradient-to-tr from-[#0a2342] to-[#0f3b5a]', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
  { label: 'Pending', value: props.summary?.pending_loans ?? 0, bg: 'bg-yellow-400', icon: 'M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
  { label: 'Active Loans', value: props.summary?.disbursed_loans ?? 0, bg: 'bg-green-500', icon: 'M5 13l4 4L19 7M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z' },
  { label: 'Overdue', value: props.summary?.overdue_loans ?? 0, bg: 'bg-red-500', icon: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
]))


const openModal = ref(false)
const selectedMember = ref('')


import axios from 'axios'
import { onMounted } from 'vue'

const members = ref([])

onMounted(async () => {
  const response = await axios.get('/api/search/members')
  members.value = response.data
})


const checkEligibility = () => {
  if (selectedMember.value) {
    openModal.value = false
    router.visit(route('members.members.loan-eligibility', selectedMember.value))
  }
}

</script>

<style scoped>
/* card glass effect */
.card-glass {
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.82));
  backdrop-filter: blur(6px);
  border: 1px solid rgba(10, 35, 66, 0.06);
  transition: transform .18s ease, box-shadow .18s ease;
}

.card-glass:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(10, 35, 66, 0.08);
}

/* small modal scale animation */
.scale-in {
  animation: scaleIn .18s cubic-bezier(.2, .8, .2, 1) both;
}

@keyframes scaleIn {
  from {
    transform: translateY(6px) scale(.98);
    opacity: 0;
  }

  to {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
}

/* fade group */
.fade-enter-active,
.fade-leave-active {
  transition: all .25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(6px);
}

.fade-enter-to,
.fade-leave-from {
  opacity: 1;
  transform: translateY(0);
}

/* modal transition */
.modal-enter-active,
.modal-leave-active {
  transition: opacity .18s ease, transform .18s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: translateY(8px) scale(.99);
}

.modal-enter-to,
.modal-leave-from {
  opacity: 1;
  transform: translateY(0) scale(1);
}

/* page bg */
.loan-page {
  background-color: #f6f8fb;
  /* subtle light background for depth */
  min-height: 100vh;
  padding-bottom: 3rem;
}
</style>
