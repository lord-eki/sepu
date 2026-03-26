<template>
  <AppLayout :breadcrumbs="[{ title: 'Loans', href: '/loans' }]">
    <Head title="Loan Management" />

    <div class="bg-slate-100 min-h-screen">

      <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">

        <!-- HEADER (clean, not decorative) -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-semibold text-slate-900">Loan Management</h1>
            <p class="text-sm text-slate-500">Manage applications, approvals and disbursements</p>
          </div>

          <div class="flex gap-2 flex-wrap">
  <Link
    :href="route('loans.create')"
    class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:bg-slate-800 transition"
  >
    New Loan
  </Link>

  <button
    @click="openModal = true"
    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:bg-blue-700 transition"
  >
    Eligibility
  </button>

  <Link
    :href="route('loan-calculator.index')"
    class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:bg-orange-600 transition"
  >
    Calculator
  </Link>
</div>
        </div>

        <!-- STATS (tight + minimal) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="card in cards" :key="card.label"
  class="p-4 rounded-xl border border-slate-200 shadow-sm
         bg-gradient-to-br from-white via-orange-50 to-orange-100
         hover:shadow-md transition"
>
            <p class="text-xs text-slate-500">{{ card.label }}</p>
            <p class="text-lg font-semibold text-slate-900 mt-1">{{ card.value }}</p>
          </div>
        </div>

        <!-- TOOLBAR -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-wrap gap-3 items-center">

          <input
            v-model="filters.search"
            placeholder="Search loans..."
            class="border border-slate-200 px-3 py-2 rounded-lg text-sm w-64"
          />

          <select v-model="filters.status" class="border border-slate-200 px-3 py-2 rounded-lg text-sm">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="disbursed">Disbursed</option>
          </select>

          <input type="date" v-model="filters.date_from" class="border border-slate-200 px-3 py-2 rounded-lg text-sm" />
          <input type="date" v-model="filters.date_to" class="border border-slate-200 px-3 py-2 rounded-lg text-sm" />

          <div class="ml-auto flex gap-2">
            <button
              @click="applyFilters"
              class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm"
            >
              Apply
            </button>

            <button
              @click="clearFilters"
              class="border border-slate-300 px-4 py-2 rounded-lg text-sm"
            >
              Reset
            </button>
          </div>
        </div>

        <!-- TABLE -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">

          <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500">
              <tr>
                <th class="text-left px-4 py-3 font-medium">Loan</th>
                <th class="text-left px-4 py-3 font-medium">Member</th>
                <th class="text-left px-4 py-3 font-medium">Amount</th>
                <th class="text-left px-4 py-3 font-medium">Status</th>
                <th class="text-left px-4 py-3 font-medium">Date</th>
                <th class="text-right px-4 py-3 font-medium">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="loan in loans.data"
                :key="loan.id"
                class="border-t hover:bg-slate-50"
              >
                <td class="px-4 py-3 font-medium text-slate-900">
                  {{ loan.loan_number }}
                </td>

                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">
                    {{ loan.member.first_name }} {{ loan.member.last_name }}
                  </div>
                  <div class="text-xs text-slate-500">
                    {{ loan.member.membership_id }}
                  </div>
                </td>

                <td class="px-4 py-3">
                  <div>KES {{ formatCurrency(loan.applied_amount) }}</div>
                  <div v-if="loan.approved_amount" class="text-xs text-green-600">
                    Approved: {{ formatCurrency(loan.approved_amount) }}
                  </div>
                </td>

                <td class="px-4 py-3">
                 <span
  class="text-xs px-2.5 py-1 rounded-full font-medium capitalize"
  :class="{
    'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200': loan.status === 'pending',

    'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': loan.status === 'approved',

    'bg-blue-50 text-blue-700 ring-1 ring-blue-200': loan.status === 'disbursed',

    'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200': loan.status === 'active',

    'bg-gray-100 text-gray-700 ring-1 ring-gray-200': loan.status === 'completed',

    'bg-red-50 text-red-700 ring-1 ring-red-200': loan.status === 'rejected'
  }"
>
  {{ loan.status }}
</span>
                </td>

                <td class="px-4 py-3 text-slate-500">
                  {{ formatDate(loan.application_date) }}
                </td>

                <td class="px-4 py-3 text-right">
                  <div class="flex justify-end gap-2">

                    <Link
                      :href="route('loans.show', loan.id)"
                      class="px-3 py-1.5 border border-slate-300 rounded-md text-xs hover:bg-slate-100"
                    >
                      View
                    </Link>

                    <Link
                      v-if="canEdit(loan)"
                      :href="route('loans.edit', loan.id)"
                      class="px-3 py-1.5 border border-slate-300 rounded-md text-xs hover:bg-slate-100"
                    >
                      Edit
                    </Link>

                    <button
                      v-if="canApprove(loan)"
                      @click="showApprovalModal(loan)"
                      class="px-3 py-1.5 bg-green-600 text-white rounded-md text-xs hover:bg-green-700"
                    >
                      Approve
                    </button>

                    <button
                      v-if="canReject(loan)"
                      @click="showRejectionModal(loan)"
                      class="px-3 py-1.5 bg-red-600 text-white rounded-md text-xs hover:bg-red-700"
                    >
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

    <!-- ELIGIBILITY MODAL -->
<div
  v-if="openModal"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black/70"
>   
  <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6">

    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-semibold text-slate-900">Check Eligibility</h2>
      <button
        @click="openModal = false"
        class="text-slate-400 hover:text-slate-600"
      >
        ✕
      </button>
    </div>

    <!-- Body -->
    <div class="space-y-4">
      <select
        v-model="selectedMember"
        class="w-full border border-slate-200 rounded-lg p-2 text-sm"
      >
        <option disabled value="">Select a member</option>
        <option
          v-for="member in members"
          :key="member.id"
          :value="member.id"
        >
          {{ member.name }}
        </option>
      </select>
    </div>

    <!-- Footer -->
    <div class="flex justify-end gap-2 mt-6">
      <button
        @click="openModal = false"
        class="px-4 py-2 border border-slate-300 rounded-lg text-sm"
      >
        Cancel
      </button>

      <button
        @click="checkEligibility"
        :disabled="!selectedMember"
        class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm disabled:opacity-50"
      >
        Continue
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
  animation: scaleIn 0.18s cubic-bezier(.2,.8,.2,1) both;
}

@keyframes scaleIn {
  from { transform: translateY(6px) scale(.98); opacity: 0; }
  to { transform: translateY(0) scale(1); opacity: 1; }
}

/* Fade transition for cards */
.fade-enter-active, .fade-leave-active { transition: all 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(6px); }
.fade-enter-to, .fade-leave-from { opacity: 1; transform: translateY(0); }
</style>
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
    router.visit(route('members.loan-eligibility', selectedMember.value))
  }
}

</script>

