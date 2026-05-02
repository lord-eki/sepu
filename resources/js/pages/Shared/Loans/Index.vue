<template>
  <AppLayout :breadcrumbs="[{ title: 'Loans', href: '/loans' }]">

    <Head title="Loan Management" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50/40">
      <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div
          class="flex flex-col gap-4 rounded-3xl py-8 border-white/20 bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-900 p-5 shadow-lg backdrop-blur-xl md:flex-row md:items-center md:justify-between">
          <div class="flex gap-4">
            <div
              class="flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/10 backdrop-blur">
              <Banknote class="h-6 w-6 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold tracking-tight text-white md:text-3xl">
                Loan Management
              </h1>
              <p class="mt-1 text-sm text-slate-200">
                Manage applications, approvals, disbursements and loan reviews
              </p>
            </div>
          </div>

          <div class="flex flex-wrap gap-2">
            <Link :href="route('loans.create')"
              class="inline-flex items-center rounded-2xl bg-white px-4 py-2.5 text-sm font-medium text-slate-900 shadow-sm transition hover:bg-slate-100">
            + New Loan
            </Link>

            <button @click="openModal = true"
              class="inline-flex items-center rounded-2xl bg-white/10 px-4 py-2.5 text-sm font-medium text-white ring-1 ring-white/20 transition hover:bg-white/20">
              Check Eligibility
            </button>

            <Link :href="route('loan-calculator.index')"
              class="inline-flex items-center rounded-2xl bg-orange-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-orange-600">
            Loan Calculator
            </Link>
          </div>
        </div>


        <!-- Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div v-for="card in cards" :key="card.label"
            class="group relative overflow-hidden rounded-3xl border-2 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
            :class="card.border">
            <div class="relative z-10">
              <p class="text-sm font-medium text-slate-500">{{ card.label }}</p>
              <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                {{ card.value }}
              </p>
            </div>
          </div>
        </div>

        <!-- Toolbar -->
        <div class="rounded-3xl border border-white/70 bg-white/85 p-4 shadow-sm backdrop-blur-xl">
          <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
            <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
              <input v-model="filters.search" placeholder="Search loans, member, loan no..."
                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" />

              <select v-model="filters.status"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="disbursed">Disbursed</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
              </select>

              <input type="date" v-model="filters.date_from"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" />

              <input type="date" v-model="filters.date_to"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100" />
            </div>

            <div class="flex flex-wrap gap-2 xl:ml-auto">
              <button @click="applyFilters"
                class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-800">
                Apply Filters
              </button>

              <button @click="clearFilters"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                <tr>
                  <th class="px-5 py-4">Loan</th>
                  <th class="px-5 py-4">Member</th>
                  <th class="px-5 py-4">Amount</th>
                  <th class="px-5 py-4">Status</th>
                  <th class="px-5 py-4">Date</th>
                  <th class="px-5 py-4 text-right">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100">
                <tr v-for="loan in loans.data" :key="loan.id" class="transition hover:bg-slate-50/70">
                  <td class="px-5 py-4 font-semibold text-slate-900">
                    {{ loan.loan_number }}
                  </td>

                  <td class="px-5 py-4">
                    <div class="font-medium text-slate-900">{{ loan.member.first_name }} {{ loan.member.last_name }}
                    </div>
                    <div class="text-xs text-slate-400">{{ loan.member.membership_id }}</div>
                  </td>

                  <td class="px-5 py-4">
                    <div class="font-medium text-slate-900">KES {{ formatCurrency(loan.applied_amount) }}</div>
                    <div v-if="loan.approved_amount" class="mt-1 text-xs font-medium text-emerald-600">
                      Approved: KES {{ formatCurrency(loan.approved_amount) }}
                    </div>
                  </td>

                  <td class="px-5 py-4">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1"
                      :class="getStatusBadgeClass(loan.status)">
                      {{ loan.status }}
                    </span>
                  </td>

                  <td class="px-5 py-4 text-slate-500">
                    {{ formatDate(loan.application_date) }}
                  </td>

                  <td class="px-5 py-4">
                    <div class="flex flex-wrap justify-end gap-2">
                      <Link :href="route('loans.show', loan.id)"
                        class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                      View
                      </Link>

                      <Link v-if="canEdit(loan)" :href="route('loans.edit', loan.id)"
                        class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                      Edit
                      </Link>

                      <button v-if="canApprove(loan)" @click="openApprovalModal(loan)"
                        class="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-emerald-700">
                        Approve
                      </button>

                      <button v-if="canReject(loan)" @click="openRejectionModal(loan)"
                        class="rounded-xl bg-red-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-700">
                        Reject
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="!loans.data?.length">
                  <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">No loan records found</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="border-t border-slate-100 p-4">
            <Pagination :data="loans" />
          </div>
        </div>
      </div>
    </div>

    <!-- Eligibility Modal -->
    <div v-if="openModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm">
      <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-slate-900">Check Eligibility</h2>
            <p class="text-sm text-slate-500">Select member to continue</p>
          </div>
          <button @click="openModal = false" class="rounded-full p-2 text-slate-500 hover:bg-slate-100">✕</button>
        </div>

        <div v-if="loadingMembers" class="mb-3 text-sm text-slate-500">Loading members...</div>

        <!-- Search Input -->
          <div class="relative" ref="dropdownRef">
            <label class="block text-sm font-medium text-slate-700 mb-1">
              Select Member
            </label>

            <input
                v-model="memberSearch"
                @focus="filterMembers(); showMemberDropdown = true"
                @input="filterMembers"
                placeholder="Search by name or membership ID..."
                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm
                      outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
              />

            <!-- Dropdown -->
            <div
              v-if="showMemberDropdown && filteredMembers.length"
              class="absolute z-50 mt-2 w-full max-h-60 overflow-y-auto rounded-2xl border bg-white shadow-lg"
            >
              <div
                v-for="m in filteredMembers"
                :key="m.id"
                @mousedown.prevent="
                  selectedMember = m.id;
                  memberSearch = m.name;
                  showMemberDropdown = false;
                "
                class="cursor-pointer px-4 py-2 text-sm hover:bg-blue-50"
              >
                <div class="font-medium text-slate-900">{{ m.name }}</div>
                <div class="text-xs text-slate-500">{{ m.membership_id }}</div>
              </div>
            </div>
          </div>

        <div class="mt-6 flex justify-end gap-2">
          <button @click="openModal = false"
            class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Cancel
          </button>
          <button @click="checkEligibility"
            class="rounded-2xl bg-blue-900 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
            Continue
          </button>
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <div v-if="showApprovalModal && selectedLoan"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm">
      <div class="w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="text-xl font-bold text-slate-900">Approve Loan</h2>
          <button @click="closeApprovalModal" class="rounded-full p-2 text-slate-500 hover:bg-slate-100">✕</button>
        </div>

        <div class="mb-4 rounded-2xl bg-slate-50 p-4">
          <p class="text-sm text-slate-500">Applied Amount</p>
          <p class="mt-1 text-2xl font-bold text-slate-900">KES {{ formatCurrency(selectedLoan.applied_amount || 0) }}
          </p>
        </div>

        <label class="text-sm font-semibold text-slate-700">Amount To Approve</label>
        <input v-model="approvalForm.approved_amount" type="number"
          class="mt-2 w-full rounded-2xl border border-slate-200 p-3 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
          placeholder="Enter approved amount" />

        <div class="mt-4 grid gap-3 md:grid-cols-3">
          <div class="rounded-2xl bg-blue-50 p-4">
            <p class="text-sm text-blue-600">Monthly Repayment</p>
            <p class="mt-1 text-xl font-bold text-blue-800">KES {{ formatCurrency(monthlyPreview) }}</p>
          </div>

          <div class="rounded-2xl bg-orange-50 p-4">
            <p class="text-sm text-orange-600">Total Interest</p>
            <p class="mt-1 text-xl font-bold text-orange-700">KES {{ formatCurrency(totalInterestPreview) }}</p>
          </div>

          <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-sm text-slate-600">Net Disbursement</p>
            <p class="mt-1 text-xl font-bold text-slate-900">KES {{ formatCurrency(netDisbursementPreview) }}</p>
          </div>
        </div>

        <textarea v-model="approvalForm.approval_notes" rows="4"
          class="mt-4 w-full rounded-2xl border border-slate-200 p-3 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
          placeholder="Approval Notes" />

        <div class="mt-5 flex justify-end gap-3">
          <button @click="closeApprovalModal"
            class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium hover:bg-slate-50">
            Cancel
          </button>

          <button @click="approveLoan" :disabled="approvingLoan"
            class="flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
            <svg v-if="approvingLoan" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25" />
              <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" class="opacity-90" />
            </svg>
            {{ approvingLoan ? 'Approving...' : 'Approve' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Rejection Modal -->
    <div v-if="showRejectionModal && selectedLoan"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm">
      <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="text-xl font-bold text-red-600">Reject Loan</h2>
          <button @click="closeRejectionModal" class="rounded-full p-2 text-slate-500 hover:bg-slate-100">✕</button>
        </div>

        <textarea v-model="rejectionForm.rejection_reason" rows="5"
          class="w-full rounded-2xl border border-slate-200 p-3 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100"
          placeholder="Reason for rejection" />

        <div class="mt-5 flex justify-end gap-3">
          <button @click="closeRejectionModal"
            class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-medium hover:bg-slate-50">
            Cancel
          </button>

          <button @click="rejectLoan" :disabled="rejectingLoan"
            class="flex items-center gap-2 rounded-2xl bg-red-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60">
            <svg v-if="rejectingLoan" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25" />
              <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" class="opacity-90" />
            </svg>
            {{ rejectingLoan ? 'Rejecting...' : 'Reject' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'
import axios from 'axios'
import {
  Banknote
} from 'lucide-vue-next'

const loadingMembers = ref(false)
const approvingLoan = ref(false)
const rejectingLoan = ref(false)

const props = defineProps({
  loans: Object,
  summary: Object,
  auth: Object
})

const filters = reactive({
  status: '',
  search: '',
  member_id: '',
  loan_product_id: '',
  date_from: '',
  date_to: ''
})

const showApprovalModal = ref(false)
const showRejectionModal = ref(false)
const selectedLoan = ref(null)


const memberSearch = ref('')
const showMemberDropdown = ref(false)
const filteredMembers = ref([])
const members = ref([]) // make sure this exists

// FILTER FUNCTION
const filterMembers = () => {
  const search = memberSearch.value.toLowerCase().trim()

  if (!search) {
    filteredMembers.value = []
    showMemberDropdown.value = false
    return
  }

  filteredMembers.value = members.value.filter(m =>
    (m.name || '').toLowerCase().includes(search) ||
    (m.membership_id || '').toLowerCase().includes(search)
  )

  showMemberDropdown.value = filteredMembers.value.length > 0
}


const approvalForm = reactive({
  approved_amount: '',
  approval_notes: ''
})

const rejectionForm = reactive({
  rejection_reason: ''
})

const applyFilters = () => {
  const cleanedFilters = {}

  Object.keys(filters).forEach((key) => {
    if (filters[key] !== '' && filters[key] !== null) cleanedFilters[key] = filters[key]
  })

  router.get(route('loans.index'), cleanedFilters, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  Object.keys(filters).forEach((key) => (filters[key] = ''))
  applyFilters()
}

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)

const formatDate = (date) =>
  new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })

const previewMeta = computed(() => {
  if (!selectedLoan.value || !approvalForm.approved_amount) {
    return {
      monthlyRepayment: 0,
      totalInterest: 0,
      netDisbursement: 0,
      processingFee: 0,
      insuranceFee: 0
    }
  }

  const principal = Number(approvalForm.approved_amount)
  const termMonths = Number(selectedLoan.value.term_months || 1)
  const monthlyRate = Number(selectedLoan.value.loan_product?.interest_rate || selectedLoan.value.interest_rate || 0) / 100
  const processingRate = Number(selectedLoan.value.loan_product?.processing_fee_rate || 0) / 100
  const insuranceRate = Number(selectedLoan.value.loan_product?.insurance_rate || 0) / 100

  const processingFee = principal * processingRate
  const insuranceFee = principal * insuranceRate
  const principalPerMonth = principal / termMonths
  const totalInterest = (principal * monthlyRate * (termMonths + 1)) / 2
  const monthlyRepayment = principalPerMonth + totalInterest / termMonths
  const netDisbursement = principal - (processingFee + insuranceFee)

  return { monthlyRepayment, totalInterest, netDisbursement, processingFee, insuranceFee }
})

const monthlyPreview = computed(() => previewMeta.value.monthlyRepayment)
const totalInterestPreview = computed(() => previewMeta.value.totalInterest)
const netDisbursementPreview = computed(() => previewMeta.value.netDisbursement)

const getStatusBadgeClass = (status) =>
({
  pending: 'bg-yellow-50 text-yellow-700 ring-yellow-200',
  approved: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
  disbursed: 'bg-blue-50 text-blue-700 ring-blue-200',
  active: 'bg-indigo-50 text-indigo-700 ring-indigo-200',
  completed: 'bg-slate-100 text-slate-700 ring-slate-200',
  rejected: 'bg-red-50 text-red-700 ring-red-200',
  cancelled: 'bg-red-50 text-red-700 ring-red-200'
}[status] || 'bg-slate-100 text-slate-700 ring-slate-200')

const canEdit = (loan) => loan.status === 'pending' && ['admin', 'loan_officer', 'management'].includes(props.auth.user.role)
const canApprove = (loan) => loan.status === 'pending' && ['admin', 'management'].includes(props.auth.user.role)
const canReject = (loan) => ['pending', 'approved'].includes(loan.status) && ['admin', 'management'].includes(props.auth.user.role)

const openApprovalModal = (loan) => {
  selectedLoan.value = loan
  approvalForm.approved_amount = loan.applied_amount || ''
  approvalForm.approval_notes = ''
  showApprovalModal.value = true
}

const openRejectionModal = (loan) => {
  selectedLoan.value = loan
  rejectionForm.rejection_reason = ''
  showRejectionModal.value = true
}

const closeApprovalModal = () => {
  showApprovalModal.value = false
  selectedLoan.value = null
  approvalForm.approved_amount = ''
  approvalForm.approval_notes = ''
}

const closeRejectionModal = () => {
  showRejectionModal.value = false
  selectedLoan.value = null
  rejectionForm.rejection_reason = ''
}

const approveLoan = () => {
  if (!selectedLoan.value || approvingLoan.value) return

  approvingLoan.value = true

  router.post(route('loans.approve', selectedLoan.value.id), approvalForm, {
    preserveScroll: true,
    onSuccess: () => closeApprovalModal(),
    onFinish: () => (approvingLoan.value = false)
  })
}

const rejectLoan = () => {
  if (!selectedLoan.value || rejectingLoan.value) return

  rejectingLoan.value = true

  router.post(route('loans.reject', selectedLoan.value.id), rejectionForm, {
    preserveScroll: true,
    onSuccess: () => closeRejectionModal(),
    onFinish: () => (rejectingLoan.value = false)
  })
}

const cards = computed(() => ([
  {
    label: 'Total Applications',
    value: props.summary?.total_loans ?? 0,
    border: 'border-blue-200'
  },
  {
    label: 'Pending',
    value: props.summary?.pending_loans ?? 0,
    border: 'border-amber-200'
  },
  {
    label: 'Active Loans',
    value: props.summary?.disbursed_loans ?? 0,
    border: 'border-indigo-200'
  },
  {
    label: 'Overdue',
    value: props.summary?.overdue_loans ?? 0,
    border: 'border-rose-200'
  }
]))

const openModal = ref(false)
const selectedMember = ref('')

onMounted(async () => {
  loadingMembers.value = true
  try {
    const response = await axios.get('/api/search/members')
    members.value = response.data
  } catch (error) {
    console.error('Failed to load members:', error)
  } finally {
    loadingMembers.value = false
  }
})

const checkEligibility = () => {
  if (!selectedMember.value) return
  openModal.value = false
  router.visit(route('members.loan-eligibility', selectedMember.value))
}

watch(memberSearch, (val) => {
  if (!val) {
    filteredMembers.value = []
    showMemberDropdown.value = false
    selectedMember.value = ''
  }
})

const dropdownRef = ref(null)

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    showMemberDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

</script>