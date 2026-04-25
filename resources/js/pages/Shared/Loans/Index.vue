<template>
  <AppLayout :breadcrumbs="[{ title: 'Loans', href: '/loans' }]">

    <Head title="Loan Management" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">

      <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-slate-900">Loan Management</h1>
            <p class="text-sm text-slate-500 mt-1">
              Manage applications, approvals and disbursements
            </p>
          </div>

          <div class="flex gap-2 flex-wrap bg-blue-900 px-2 py-1 rounded-xl">
            <Link :href="route('loans.create')"
              class=" text-white px-4 py-3 rounded-xl text-base shadow hover:bg-blue-500 transition">
            + New Loan
            </Link>

            <button @click="openModal = true"
              class=" text-white px-4 py-3 rounded-xl text-base shadow hover:bg-indigo-500 transition">
              Check Eligibility
            </button>

            <Link :href="route('loan-calculator.index')"
              class="text-white px-4 py-3 rounded-xl text-base shadow hover:bg-orange-500 transition">
            Loan Calculator
            </Link>
          </div>
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="(card, index) in cards" :key="card.label"
            class="relative p-5 rounded-2xl overflow-hidden text-white shadow-sm hover:shadow-lg transition group"
            :class="card.bg">

            <!-- RADIAL GLOW -->
            <div
              class="absolute -top-10 -right-10 w-40 h-40 bg-white/20 rounded-full blur-2xl opacity-60 group-hover:scale-110 transition">
            </div>

            <!-- SHINE EFFECT -->
            <div class="absolute inset-0 bg-gradient-to-tr from-white/10 via-transparent to-white/20 opacity-40"></div>

            <!-- CONTENT -->
            <div class="relative z-10">
              <p class="text-lg text-gray-900">{{ card.label }}</p>
              <p class="text-blue-950 text-2xl font-bold mt-2">{{ card.value }}</p>
            </div>

          </div>
        </div>

        <!-- TOOLBAR -->
        <div
          class="bg-white/80 backdrop-blur border border-slate-200 rounded-2xl p-4 flex flex-wrap gap-3 items-center shadow-sm">

          <input v-model="filters.search" placeholder="Search loans..."
            class="border border-slate-300 px-3 py-2 rounded-xl text-sm w-64 focus:ring-2 focus:ring-slate-300 outline-none" />

          <select v-model="filters.status" class="border border-slate-200 px-3 py-2 rounded-xl text-sm">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="disbursed">Disbursed</option>
          </select>

          <input type="date" v-model="filters.date_from" class="border border-slate-200 px-3 py-2 rounded-xl text-sm" />
          <input type="date" v-model="filters.date_to" class="border border-slate-200 px-3 py-2 rounded-xl text-sm" />

          <div class="ml-auto flex gap-2">
            <button @click="applyFilters"
              class="bg-slate-900 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-slate-800">
              Apply
            </button>

            <button @click="clearFilters"
              class="border border-slate-300 px-3 py-1.5 rounded-lg text-sm hover:bg-slate-100">
              Reset
            </button>
          </div>
        </div>

        <!-- TABLE -->
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">

          <table class="w-full text-sm">
            <thead class="bg-blue-100 text-blue-950">
              <tr>
                <th class="text-left px-4 py-3 font-medium">LOAN</th>
                <th class="text-left px-4 py-3 font-medium">MEMBER</th>
                <th class="text-left px-4 py-3 font-medium">AMOUNT</th>
                <th class="text-left px-4 py-3 font-medium">STATUS</th>
                <th class="text-left px-4 py-3 font-medium">DATE</th>
                <th class="text-right px-4 py-3 font-medium">ACTIONS</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="loan in loans.data" :key="loan.id" class="border-t hover:bg-slate-50/70 transition">
                <td class="px-4 py-3 font-semibold text-slate-900">
                  {{ loan.loan_number }}
                </td>

                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">
                    {{ loan.member.first_name }} {{ loan.member.last_name }}
                  </div>
                  <div class="text-xs text-slate-400">
                    {{ loan.member.membership_id }}
                  </div>
                </td>

                <td class="px-4 py-3">
                  <div>KES {{ formatCurrency(loan.applied_amount) }}</div>
                  <div v-if="loan.approved_amount" class="text-xs text-emerald-600">
                    Approved: {{ formatCurrency(loan.approved_amount) }}
                  </div>
                </td>

                <!-- STATUS -->
                <td class="px-4 py-3">
                  <span class="text-xs px-2.5 py-1 rounded-full font-medium capitalize" :class="{
    'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200': loan.status === 'pending',
    'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': loan.status === 'approved',
    'bg-blue-50 text-blue-700 ring-1 ring-blue-200': loan.status === 'disbursed',
    'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200': loan.status === 'active',
    'bg-gray-100 text-gray-700 ring-1 ring-gray-200': loan.status === 'completed',
    'bg-red-50 text-red-700 ring-1 ring-red-200': loan.status === 'rejected'
  }">
                    {{ loan.status }}
                  </span>
                </td>

                <td class="px-4 py-3 text-slate-500">
                  {{ formatDate(loan.application_date) }}
                </td>

                <td class="px-4 py-3 text-right">
                  <div class="flex justify-end gap-2">

                    <Link :href="route('loans.show', loan.id)"
                      class="border border-slate-300 px-3 py-1.5 rounded-md text-xs hover:bg-slate-100">
                    View
                    </Link>

                    <Link v-if="canEdit(loan)" :href="route('loans.edit', loan.id)"
                      class="border border-slate-300 px-3 py-1.5 rounded-md text-xs hover:bg-slate-100">
                    Edit
                    </Link>

                    <button v-if="canApprove(loan)" @click="openApprovalModal(loan)"
                      class="bg-emerald-600 text-white px-3 py-1.5 rounded-md text-xs hover:bg-emerald-700">
                      Approve
                    </button>

                    <button v-if="canReject(loan)" @click="openRejectionModal(loan)"
                      class="bg-red-600 text-white px-3 py-1.5 rounded-md text-xs hover:bg-red-700">
                      Reject
                    </button>

                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div class="p-4 border-t">
            <Pagination :data="loans" />
          </div>
        </div>

      </div>
    </div>

    <!-- MODAL -->
    <div v-if="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">

        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold">Check Eligibility</h2>
          <button @click="openModal = false">✕</button>
        </div>

        <div v-if="loadingMembers" class="text-sm text-slate-500 mb-3">
          Loading members...
        </div>
        <select
            v-model="selectedMember"
            :disabled="loadingMembers"
            class="w-full border border-slate-200 rounded-xl p-2 text-sm disabled:bg-slate-100 disabled:cursor-not-allowed"
          >
          <option disabled value="">Select a member</option>
          <option v-for="m in members" :key="m.id" :value="m.id">
            {{ m.name }}
          </option>
        </select>

        <div class="flex justify-end gap-2 mt-6">
          <button @click="openModal = false" class="border border-slate-300 px-4 py-2 rounded-lg text-sm">
            Cancel
          </button>

          <button @click="checkEligibility" class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm">
            Continue
          </button>
        </div>

      </div>
    </div>

    <!-- APPROVAL MODAL -->
    <div
      v-if="showApprovalModal && selectedLoan"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
    >
      <div class="w-full max-w-xl rounded-3xl bg-white shadow-2xl border border-slate-200 p-6">

        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-xl font-bold text-slate-900">Approve Loan</h2>
          <button
            @click="closeApprovalModal"
            class="w-9 h-9 rounded-full hover:bg-slate-100 text-slate-500"
          >
            ✕
          </button>
        </div>

        <!-- Applied -->
        <div class="mb-4 rounded-2xl bg-slate-50 p-4">
          <p class="text-sm text-slate-500">Applied Amount</p>
          <p class="text-2xl font-bold text-slate-900">
            KES {{ formatCurrency(selectedLoan.applied_amount || 0) }}
          </p>
        </div>

        <!-- Input -->
        <label class="text-sm font-semibold text-slate-700">
          Amount To Approve
        </label>

        <input
          v-model="approvalForm.approved_amount"
          type="number"
          class="w-full mt-2 border border-slate-200 rounded-2xl p-3 focus:ring-2 focus:ring-blue-400 outline-none"
          placeholder="Enter approved amount"
        />

        <!-- Preview -->
        <div class="mt-4 space-y-3">
          <div class="rounded-2xl bg-blue-50 p-4">
            <p class="text-sm text-blue-600">Monthly Repayment</p>
            <p class="text-xl font-bold text-blue-800">
              KES {{ formatCurrency(monthlyPreview) }}
            </p>
          </div>

          <div class="rounded-2xl bg-orange-50 p-4">
            <p class="text-sm text-orange-600">Total Interest</p>
            <p class="text-xl font-bold text-orange-700">
              KES {{ formatCurrency(totalInterestPreview) }}
            </p>
          </div>

          <div class="rounded-2xl bg-slate-50 p-4">
            <p class="text-sm text-slate-600">Net Disbursement</p>
            <p class="text-xl font-bold text-slate-900">
              KES {{ formatCurrency(netDisbursementPreview) }}
            </p>
          </div>
        </div>

        <textarea
          v-model="approvalForm.approval_notes"
          class="w-full border border-slate-200 rounded-2xl p-3 mt-4 focus:ring-2 focus:ring-blue-400 outline-none"
          placeholder="Approval Notes"
          rows="4"
        ></textarea>

        <!-- Actions -->
        <div class="flex justify-end gap-3 mt-5">
          <button
            @click="closeApprovalModal"
            class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50"
          >
            Cancel
          </button>

          <button
            @click="approveLoan"
            :disabled="approvingLoan"
            class="px-5 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <svg
              v-if="approvingLoan"
              class="w-4 h-4 animate-spin"
              viewBox="0 0 24 24"
              fill="none"
            >
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
              <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="4" class="opacity-90"/>
            </svg>

            {{ approvingLoan ? 'Approving...' : 'Approve' }}
          </button>
        </div>
      </div>
    </div>

<!-- REJECTION MODAL -->
    <div
      v-if="showRejectionModal && selectedLoan"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
    >
      <div class="w-full max-w-lg rounded-3xl bg-white shadow-2xl border border-slate-200 p-6">

        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-xl font-bold text-red-600">Reject Loan</h2>
          <button
            @click="closeRejectionModal"
            class="w-9 h-9 rounded-full hover:bg-slate-100 text-slate-500"
          >
            ✕
          </button>
        </div>

        <textarea
          v-model="rejectionForm.rejection_reason"
          class="w-full border border-slate-200 rounded-2xl p-3 focus:ring-2 focus:ring-red-400 outline-none"
          placeholder="Reason for rejection"
          rows="5"
        ></textarea>

        <div class="flex justify-end gap-3 mt-5">
          <button
            @click="closeRejectionModal"
            class="px-4 py-2 border border-slate-300 rounded-xl hover:bg-slate-50"
          >
            Cancel
          </button>

          <button
            @click="rejectLoan"
            :disabled="rejectingLoan"
            class="px-5 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <svg
              v-if="rejectingLoan"
              class="w-4 h-4 animate-spin"
              viewBox="0 0 24 24"
              fill="none"
            >
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
              <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="4" class="opacity-90"/>
            </svg>

            {{ rejectingLoan ? 'Rejecting...' : 'Reject' }}
          </button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>
<style scoped>
/* Glassy card effect */
.card-glass {
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.82));
  backdrop-filter: blur(6px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.card-glass:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
}

/* Modal scale-in animation */
.scale-in {
  animation: scaleIn 0.18s cubic-bezier(.2, .8, .2, 1) both;
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

/* Fade transition for cards */
.fade-enter-active,
.fade-leave-active {
  transition: all 0.25s ease;
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
</style>
<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'
import axios from 'axios'

const loadingMembers = ref(false)
const approvingLoan = ref(false)
const rejectingLoan = ref(false)


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

const showApprovalModal = ref(false)
const showRejectionModal = ref(false)
const selectedLoan = ref(null)

const approvalForm = reactive({
  approved_amount: '',
  approval_notes: ''
})

const rejectionForm = reactive({
  rejection_reason: ''
})

const applyFilters = () => {
  const cleanedFilters = {}

  Object.keys(filters).forEach(key => {
    if (filters[key] !== '' && filters[key] !== null) {
      cleanedFilters[key] = filters[key]
    }
  })

  router.get(route('loans.index'), cleanedFilters, {
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

  // backend uses monthly rate already
  const monthlyRate = Number(selectedLoan.value.loan_product?.interest_rate || selectedLoan.value.interest_rate || 0) / 100

  const processingRate = Number(selectedLoan.value.loan_product?.processing_fee_rate || 0) / 100
  const insuranceRate = Number(selectedLoan.value.loan_product?.insurance_rate || 0) / 100

  const processingFee = principal * processingRate
  const insuranceFee = principal * insuranceRate

  const principalPerMonth = principal / termMonths
  const totalInterest = principal * monthlyRate * (termMonths + 1) / 2
  const monthlyRepayment = principalPerMonth + (totalInterest / termMonths)
  const netDisbursement = principal - (processingFee + insuranceFee)

  return {
    monthlyRepayment,
    totalInterest,
    netDisbursement,
    processingFee,
    insuranceFee
  }
})

const monthlyPreview = computed(() => previewMeta.value.monthlyRepayment)
const totalInterestPreview = computed(() => previewMeta.value.totalInterest)
const netDisbursementPreview = computed(() => previewMeta.value.netDisbursement)
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

const showDisbursementModal = (loan) => {
  router.visit(route('loans.show', loan.id) + '#disburse')
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
    onFinish: () => {
      approvingLoan.value = false
    }
  })
}
const rejectLoan = () => {
  if (!selectedLoan.value || rejectingLoan.value) return

  rejectingLoan.value = true

  router.post(route('loans.reject', selectedLoan.value.id), rejectionForm, {
    preserveScroll: true,
    onSuccess: () => closeRejectionModal(),
    onFinish: () => {
      rejectingLoan.value = false
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
  {
    label: 'Total Applications',
    value: props.summary?.total_loans ?? 0,
    bg: 'bg-gradient-to-br from-blue-600 via-blue-400 to-blue-700'
  },
  {
    label: 'Pending',
    value: props.summary?.pending_loans ?? 0,
    bg: 'bg-gradient-to-br from-yellow-300 via-orange-400 to-yellow-200'
  },
  {
    label: 'Active Loans',
    value: props.summary?.disbursed_loans ?? 0,
    bg: 'bg-gradient-to-br from-indigo-400 via-indigo-200 to-blue-700'
  },
  {
    label: 'Overdue',
    value: props.summary?.overdue_loans ?? 0,
    bg: 'bg-gradient-to-br from-rose-400 via-rose-200 to-rose-500'
  }
]))


const openModal = ref(false)
const selectedMember = ref('')


const members = ref([])

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
  if (selectedMember.value) {
    openModal.value = false
    router.visit(route('members.loan-eligibility', selectedMember.value))
  }
}

</script>
