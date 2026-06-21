<template>
  <AppLayout :breadcrumbs="[
    { title: 'Loans', href: isMemberRole ? '/my-loans' : route('loans.index') },
    { title: 'Loan Details' }
  ]">

    <Head title="Loan Details" />

    <!-- ================= TOAST ================= -->
    <transition enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-3 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-3">
      <div v-if="visible" class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] w-[92%] max-w-md
        rounded-2xl border border-white/60 bg-white/90 backdrop-blur-xl
        shadow-2xl px-5 py-4 flex items-start gap-3">
        <div class="h-9 w-9 rounded-xl flex items-center justify-center text-white font-bold shadow" :class="type === 'success'
    ? 'bg-emerald-500'
    : type === 'error'
      ? 'bg-rose-500'
      : 'bg-slate-500'">
          {{ type === 'success' ? '✓' : '!' }}
        </div>

        <div class="flex-1">
          <p class="text-sm font-semibold text-slate-800">{{ message }}</p>
        </div>

        <button @click="close" class="text-slate-400 hover:text-slate-800">
          ✕
        </button>
      </div>
    </transition>

    <!-- ================= PAGE ================= -->
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">

      <!-- ================= HERO HEADER ) ================= -->
      <div class="border-b border-white/50 bg-white/70 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-6 py-5 sm:py-7">

          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

            <!-- LEFT -->
            <div class="flex items-start gap-3 sm:gap-4">

              <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-2xl sm:rounded-3xl
                bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900
                flex items-center justify-center shadow-xl flex-shrink-0">
                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3" />
                </svg>
              </div>

              <div class="min-w-0">
                <p class="text-[10px] sm:text-xs uppercase tracking-[0.2em] text-slate-400 mb-1">
                  Loan Account
                </p>

                <h1 class="text-lg sm:text-2xl font-black text-slate-900 break-words">
                  {{ loan.loan_number }}
                </h1>

                <div class="mt-2 sm:mt-3 flex flex-wrap gap-2">
                  <span
                    class="px-2 sm:px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-[10px] sm:text-xs font-semibold">
                    {{ loan.loan_product?.name || 'Loan Product' }}
                  </span>

                  <span
                    class="px-2 sm:px-3 py-1 rounded-full bg-slate-200 text-slate-700 text-[10px] sm:text-xs font-semibold">
                    Applied {{ formatDate(loan.application_date || loan.created_at) }}
                  </span>
                </div>
              </div>

            </div>

            <!-- RIGHT -->
            <div class="flex items-center justify-between w-full lg:flex-col lg:items-end gap-3">

              <!-- Member info (left on mobile) -->
              <div class="min-w-0">
                <p class="text-[10px] sm:text-xs uppercase tracking-[0.2em] text-slate-400">
                  Member
                </p>

                <h2 class="text-base sm:text-xl font-bold text-slate-900 mt-1 truncate">
                  {{ loan.member?.first_name }} {{ loan.member?.last_name }}
                </h2>
              </div>

              <!-- Back button (right on mobile) -->
              <div class="flex-shrink-0">
                <Link :href="isMemberRole ? route('my-loans') : route('loans.index')" class="px-3 sm:px-4 py-2 rounded-xl sm:rounded-2xl bg-white border border-slate-200
                      text-slate-700 text-xs sm:text-sm font-medium hover:bg-slate-50">
                Back
                </Link>
              </div>

            </div>

          </div>

        </div>
      </div>

      <!-- ================= STATUS BAR ================= -->
      <div class="max-w-7xl mx-auto px-6 pt-2">
        <div class="rounded-3xl bg-white/90 backdrop-blur-xl border border-white shadow-xl p-5
          flex flex-col md:flex-row md:items-center gap-4 justify-between">

          <div class="flex items-center gap-4">

            <div class="h-12 w-12 rounded-2xl flex items-center justify-center font-bold text-lg"
              :class="getStatusBannerClass(loan.status)">
              !
            </div>

            <div>
              <p class="text-sm text-slate-500">Current Status</p>
              <p class="font-bold text-slate-900 text-sm">
                {{ formatStatus(loan.status) }}
              </p>
            </div>

          </div>

          <p class="text-sm text-slate-500 max-w-xl">
            {{ getStatusDescription(loan.status) }}
          </p>

        </div>
      </div>

      <!-- ================= QUICK ACTIONS ================= -->
      <div v-if="hasQuickActions" class="max-w-7xl mx-auto px-6 mt-6">
        <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-5">

          <h3 class="font-semibold text-slate-900 mb-4">
            Quick Actions
          </h3>

          <div class="flex flex-wrap gap-3">

            <button v-if="canApprove" @click="openApprovalModal"
              class="px-4 py-2.5 rounded-2xl bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">
              Approve
            </button>

            <button v-if="canReject" @click="openRejectModal"
              class="px-4 py-2.5 rounded-2xl bg-rose-600 text-white text-sm font-medium hover:bg-rose-700">
              Reject
            </button>

            <button v-if="canDisburse" @click="openDisbursementModal"
              class="px-4 py-2.5 rounded-2xl bg-blue-700 text-white text-sm font-medium hover:bg-blue-800">
              Disburse
            </button>

            <Link v-if="canViewSchedule" :href="route('loans.schedule', loan.id)"
              class="px-4 py-2.5 rounded-2xl bg-slate-100 text-slate-700 text-sm font-medium hover:bg-slate-200">
            Schedule
            </Link>

            <Link v-if="canViewRepayments" :href="route('loans.repayments', loan.id)"
              class="px-4 py-2.5 rounded-2xl bg-orange-500 text-white text-sm font-medium hover:bg-orange-600">
            Repayments
            </Link>

          </div>

        </div>
      </div>

      <!-- ================= BODY ================= -->
      <div class="max-w-7xl mx-auto px-6 py-8 grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- LEFT -->
        <div class="xl:col-span-2 space-y-6">

          <!-- LOAN INFO -->
          <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6">

            <div class="flex items-center justify-between mb-5">
              <h2 class="text-lg font-bold text-slate-900">
                Loan Information
              </h2>

              <span class="text-xs text-slate-400 uppercase tracking-widest">
                Overview
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

              <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Applied Amount</p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                  KES {{ formatCurrency(loan.applied_amount) }}
                </p>
              </div>

              <div class="rounded-2xl bg-blue-50 p-4">
                <p class="text-xs text-blue-600">Approved Amount</p>
                <p class="mt-1 text-xl font-bold text-blue-800">
                  KES {{ formatCurrency(loan.approved_amount) }}
                </p>
              </div>

              <div class="rounded-2xl bg-emerald-50 p-4">
                <p class="text-xs text-emerald-600">Disbursed Amount</p>
                <p class="mt-1 text-xl font-bold text-emerald-700">
                  KES {{ formatCurrency(loan.disbursed_amount) }}
                </p>
              </div>

              <div class="rounded-2xl bg-orange-50 p-4">
                <p class="text-xs text-orange-600">Interest Rate</p>
                <p class="mt-1 text-xl font-bold text-orange-700">
                  {{ loan.interest_rate }}%
                </p>
              </div>

              <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Repayment Term</p>
                <p class="mt-1 text-xl font-bold text-slate-900">
                  {{ loan.term_months }} Months
                </p>
              </div>

              <div class="rounded-2xl bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Purpose</p>
                <p class="mt-1 font-semibold text-slate-900">
                  {{ loan.purpose || 'N/A' }}
                </p>
              </div>

            </div>
          </div>

          <!-- GUARANTORS -->
          <div v-if="loan.guarantors?.length" class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-5">
              Guarantors
            </h2>

            <div class="space-y-3">

              <div v-for="g in loan.guarantors" :key="g.id"
                class="rounded-2xl border border-slate-100 p-4 flex items-center justify-between">
                <div>
                  <p class="font-semibold text-slate-900">
                    {{ g.guarantor_member.first_name }}
                    {{ g.guarantor_member.last_name }}
                  </p>

                  <p class="text-sm text-slate-500">
                    {{ g.guarantor_member.membership_id }}
                  </p>
                </div>

                <div class="text-right">
                  <p class="font-bold text-slate-900">
                    KES {{ formatCurrency(g.guaranteed_amount) }}
                  </p>

                  <span class="text-xs text-slate-500">
                    {{ g.status }}
                  </span>
                </div>
              </div>

            </div>
          </div>

        </div>

        <!-- SUPPORTING DOCUMENTS -->
        <div v-if="loan.documents?.length" class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6">

          <!-- HEADER -->
          <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-slate-900">
              Supporting Documents
            </h2>

            <span class="text-xs text-slate-400 uppercase tracking-widest">
              {{ loan.documents.length }} File(s)
            </span>
          </div>

          <div class="space-y-3">

            <!-- PREVIEW (first 3) -->
            <div
              v-for="(doc, index) in loan.documents.slice(0, 3)"
              :key="index"
              class="flex items-center justify-between p-4 border border-slate-200 rounded-2xl hover:bg-slate-50 transition"
            >
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                  📄
                </div>

                <div>
                  <p class="font-semibold text-slate-900">
                    {{ doc.name || `Document ${index + 1}` }}
                  </p>
                  <p class="text-sm text-slate-500">Supporting attachment</p>
                </div>
              </div>

              <div class="flex gap-2">
                <a :href="doc.url || doc" target="_blank"
                  class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm hover:bg-blue-700">
                  View
                </a>

                <a :href="doc.url || doc" download
                  class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-sm hover:bg-slate-100">
                  Download
                </a>
              </div>
            </div>

            <!-- VIEW ALL BUTTON -->
            <div v-if="loan.documents.length > 3" class="flex justify-center pt-2">
              <button
                @click="showDocsModal = true"
                class="px-5 py-2 rounded-xl bg-slate-900 text-white text-sm hover:bg-slate-800"
              >
                View All Documents ({{ loan.documents.length }})
              </button>
            </div>

          </div>
        </div>

        <!-- ===================== -->
        <!-- DOCUMENTS MODAL -->
        <!-- ===================== -->
        <div v-if="showDocsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

          <div class="bg-white w-full max-w-2xl rounded-2xl shadow-lg p-6 relative">

            <!-- CLOSE -->
            <button
              @click="showDocsModal = false"
              class="absolute top-3 right-3 text-slate-500 hover:text-slate-800"
            >
              ✕
            </button>

            <h2 class="text-lg font-bold text-slate-900 mb-4">
              All Supporting Documents
            </h2>

            <!-- LIST -->
            <div class="space-y-3 max-h-[70vh] overflow-y-auto">

              <div
                v-for="(doc, index) in loan.documents"
                :key="index"
                class="flex items-center justify-between p-4 border rounded-2xl hover:bg-slate-50 transition"
              >
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                    📄
                  </div>

                  <div>
                    <p class="font-semibold text-slate-900">
                      {{ doc.name || `Document ${index + 1}` }}
                    </p>
                    <p class="text-sm text-slate-500">Supporting attachment</p>
                  </div>
                </div>

                <div class="flex gap-2">
                  <a :href="doc.url || doc" target="_blank"
                    class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm hover:bg-blue-700">
                    View
                  </a>

                  <a :href="doc.url || doc" download
                    class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-sm hover:bg-slate-100">
                    Download
                  </a>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- RIGHT -->
        <div class="space-y-6">

          <!-- BALANCE -->
          <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6">

            <h2 class="text-lg font-bold text-slate-900 mb-5">
              Balance Summary
            </h2>

            <div class="space-y-4">

              <div class="flex justify-between">
                <span class="text-slate-500">Outstanding</span>
                <span class="font-bold text-rose-600">
                  KES {{ formatCurrency(loan.outstanding_balance) }}
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-slate-500">Principal</span>
                <span class="font-semibold text-slate-900">
                  KES {{ formatCurrency(loan.principal_balance) }}
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-slate-500">Interest</span>
                <span class="font-semibold text-slate-900">
                  KES {{ formatCurrency(loan.interest_balance) }}
                </span>
              </div>

            </div>
          </div>

          <!-- FEES -->
          <div class="rounded-3xl bg-white border border-slate-100 shadow-sm p-6">

            <h2 class="text-lg font-bold text-slate-900 mb-5">
              Charges & Fees
            </h2>

            <div class="space-y-4">

              <div class="flex justify-between">
                <span class="text-slate-500">Processing Fee</span>
                <span class="font-semibold text-slate-900">
                  KES {{ formatCurrency(loan.processing_fee) }}
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-slate-500">Insurance Fee</span>
                <span class="font-semibold text-slate-900">
                  KES {{ formatCurrency(loan.insurance_fee) }}
                </span>
              </div>

            </div>
          </div>

        </div>

      </div>

    </div>

    <!-- ================= APPROVAL MODAL ================= -->
    <div v-if="showApprovalModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
      <div class="bg-white w-full max-w-xl rounded-3xl shadow-2xl p-6">

        <h2 class="text-xl font-bold text-slate-900 mb-5">
          Approve Loan
        </h2>

        <!-- Applied Amount -->
        <div class="mb-4 rounded-2xl bg-slate-50 p-4">
          <p class="text-sm text-slate-500">Applied Amount</p>
          <p class="text-2xl font-bold text-slate-900">
            KES {{ formatCurrency(loan.applied_amount) }}
          </p>
        </div>

        <!-- Amount To Approve -->
        <div class="mb-4">
          <label class="text-sm font-semibold text-slate-700">
            Amount To Approve
          </label>

          <input v-model="approvalForm.approved_amount" type="number" class="w-full mt-2 border rounded-2xl p-3"
            placeholder="Enter approved amount" />
        </div>

        <!-- Preview -->
        <div class="mb-4 rounded-2xl bg-blue-50 p-4">
          <p class="text-sm text-blue-600">Monthly Repayment</p>
          <p class="text-xl font-bold text-blue-800">
            KES {{ formatCurrency(monthlyPreview) }}
          </p>
        </div>

        <div class="mb-4 rounded-2xl bg-orange-50 p-4">
          <p class="text-sm text-orange-600">Total Interest</p>
          <p class="text-xl font-bold text-orange-700">
            KES {{ formatCurrency(totalInterestPreview) }}
          </p>
        </div>

        <div class="mb-4 rounded-2xl bg-slate-50 p-4">
          <p class="text-sm text-slate-600">Net Disbursement</p>
          <p class="text-xl font-bold text-slate-900">
            KES {{ formatCurrency(netDisbursementPreview) }}
          </p>
        </div>

        <textarea v-model="approvalForm.approval_notes" class="w-full border rounded-2xl p-3 mb-5"
          placeholder="Approval Notes (optional)"></textarea>

        <div class="flex justify-end gap-3">
          <button @click="closeApprovalModal" class="px-4 py-2 border rounded-xl">
            Cancel
          </button>

          <button @click="approveLoan" :disabled="loading.approve"
            class="px-5 py-2 bg-emerald-600 text-white rounded-xl flex items-center gap-2">
            <span v-if="loading.approve"
              class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            <span>{{ loading.approve ? 'Processing...' : 'Approve Loan' }}</span>
          </button>
        </div>

      </div>
    </div>

    <div v-if="showRejectionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
      <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl p-6">
        <h2 class="text-xl font-bold text-red-600 mb-4">Reject Loan</h2>

        <textarea v-model="rejectionForm.rejection_reason" class="w-full border rounded-2xl p-3 mb-4"
          placeholder="Reason for rejection"></textarea>

        <div class="flex justify-end gap-3">
          <button @click="closeRejectModal" class="px-4 py-2 border rounded-xl">
            Cancel
          </button>

          <button @click="rejectLoan" :disabled="loading.reject"
            class="px-5 py-2 bg-red-600 text-white rounded-xl flex items-center gap-2">
            <span v-if="loading.reject"
              class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            <span>{{ loading.reject ? 'Processing...' : 'Reject' }}</span>
          </button>
        </div>
      </div>
    </div>


    <!-- ================= DISBURSEMENT MODAL ================= -->
    <div v-if="showDisbursementModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
      <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl p-6">

        <h2 class="text-xl font-bold text-slate-900 mb-5">
          Disburse Loan
        </h2>

        <!-- Approved Amount -->
        <div class="mb-4 rounded-2xl bg-blue-50 p-4">
          <p class="text-sm text-blue-600">Approved Amount</p>
          <p class="text-2xl font-bold text-blue-700">
            KES {{ formatCurrency(loan.approved_amount) }}
          </p>
        </div>

        <!-- Net Disbursement -->
        <div class="mb-6 rounded-2xl bg-emerald-50 p-4">
          <p class="text-sm text-emerald-600">Net Disbursement</p>
          <p class="text-2xl font-bold text-emerald-700">
            KES {{
    formatCurrency(loan.approved_amount - (
      (loan.approved_amount * (loan.loan_product?.processing_fee_rate || 0) / 100) +
      (loan.approved_amount * (loan.loan_product?.insurance_rate || 0) / 100)
    ))
  }}
          </p>
        </div>

        <!-- Disbursement Method -->
        <select v-model="disbursementForm.disbursement_method"
          class="w-full border rounded-2xl p-3 mb-4 focus:ring-2 focus:ring-blue-500 outline-none">
          <option value="">Select Method</option>
          <option value="cash">Cash</option>
          <option value="mobile_money">Mobile Money</option>
          <option value="bank_transfer">Bank Transfer</option>
        </select>

        <!-- Reference -->
        <input v-model="disbursementForm.disbursement_reference"
          class="w-full border rounded-2xl p-3 mb-5 focus:ring-2 focus:ring-blue-500 outline-none"
          placeholder="Reference Number" />

        <!-- Actions -->
        <div class="flex justify-end gap-3">

          <button @click="closeDisbursementModal" class="px-4 py-2 border rounded-xl hover:bg-slate-50">
            Cancel
          </button>

          <button @click="disburseLoan" :disabled="loading.disburse"
            class="px-5 py-2 bg-blue-700 text-white rounded-xl flex items-center gap-2 hover:bg-blue-800 disabled:opacity-60">
            <span v-if="loading.disburse"
              class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>

            <span>
              {{ loading.disburse ? 'Processing...' : 'Confirm Disbursement' }}
            </span>
          </button>

        </div>

      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import AppLayout from '@/layouts/AppLayout.vue'
import axios from 'axios'

// Props
const props = defineProps({
  loan: Object,
  auth: Object,
  message: String,
  type: { type: String, default: 'success' },
  duration: { type: Number, default: 5000 }
})

// Computed
const loan = computed(() => props.loan)
const isMemberRole = computed(() => props.auth.user.role === 'member')

// Modal controls
const showApprovalModal = ref(false)
const showRejectionModal = ref(false)
const showDisbursementModal = ref(false)

function openApprovalModal() {
  resetValidation()

  const applied = props.loan.applied_amount || 0

  // ✅ Approved amount should start as FULL amount
  approvalForm.approved_amount = applied
  approvalForm.approval_notes = ''

  showApprovalModal.value = true
}

function openRejectModal() {
  resetValidation()
  showRejectionModal.value = true
}

function openDisbursementModal() {
  resetValidation()
  disbursementForm.disbursed_amount = props.loan.approved_amount || 0
  showDisbursementModal.value = true
}

function closeApprovalModal() { showApprovalModal.value = false }
function closeRejectModal() { showRejectionModal.value = false }
function closeDisbursementModal() { showDisbursementModal.value = false }

// Forms & validation
const approvalForm = reactive({
  approved_amount: 0,
  net_amount: 0,
  approval_notes: ''
})

const rejectionForm = reactive({
  rejection_reason: ''
})

const disbursementForm = reactive({
  disbursed_amount: props.loan.approved_amount || 0,
  disbursement_method: '',
  disbursement_reference: ''
})

const validationErrors = reactive({})
const loading = reactive({ approve: false, reject: false, disburse: false })

function resetValidation() {
  for (const k in validationErrors) delete validationErrors[k]
}

// Computed previews for approval modal
const totalFeeRate = computed(() => ((loan.value.loan_product.processing_fee_rate || 0) + (loan.value.loan_product.insurance_rate || 0)) / 100)
const processingFeePreview = computed(() => approvalForm.approved_amount ? (approvalForm.approved_amount * (loan.value.loan_product.processing_fee_rate ?? 0)) / 100 : 0)
const insuranceFeePreview = computed(() => approvalForm.approved_amount ? (approvalForm.approved_amount * (loan.value.loan_product.insurance_rate ?? 0)) / 100 : 0)
const netDisbursementPreview = computed(() => approvalForm.approved_amount ? approvalForm.approved_amount - processingFeePreview.value - insuranceFeePreview.value : 0)
const monthlyPreview = computed(() => {
  const P = approvalForm.approved_amount || 0
  const n = loan.value.term_months || 1
  const r = loan.value.loan_product?.interest_rate / 100 || 0 // monthly rate as decimal

  if (!P || n === 0) return 0

  // ── Principal slice
  const principalPerMonth = P / n

  // ── Total reducing-balance interest
  const totalInterest = P * r * ((n + 1) / 2)

  // ── Fixed monthly interest
  const mInterest = totalInterest / n

  // ── Actual installment (principal + fixed monthly interest)
  return principalPerMonth + mInterest
})

const totalInterestPreview = computed(() => {
  const P = approvalForm.approved_amount || 0
  const n = loan.value.term_months || 1
  const r = loan.value.loan_product?.interest_rate / 100 || 0

  return P * r * ((n + 1) / 2)
})


const totalRepaymentPreview = computed(() => {
  const n = loan.value.term_months || 1
  return monthlyPreview.value * n
})


// Permissions
const canEdit = computed(() => props.loan.status === 'pending' && ['admin', 'loan_officer', 'management'].includes(props.auth.user.role))
const canApprove = computed(() => props.loan.status === 'pending' && ['admin', 'management'].includes(props.auth.user.role))
const canReject = computed(() => ['pending', 'approved'].includes(props.loan.status) && ['admin', 'management'].includes(props.auth.user.role))
const canDisburse = computed(() => props.loan.status === 'approved' && ['admin', 'accountant'].includes(props.auth.user.role))
const canViewSchedule = computed(() => ['disbursed', 'active', 'completed'].includes(props.loan.status))
const canViewRepayments = computed(() => ['disbursed', 'active', 'completed'].includes(props.loan.status))
const hasQuickActions = computed(() => canApprove.value || canReject.value || canDisburse.value || canViewSchedule.value || canViewRepayments.value)

// Helpers
const formatCurrency = (amount) => new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0)
const formatDate = (date) => { if (!date) return '-'; try { return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: '2-digit' }) } catch { return date } }
const formatStatus = (status = '') => status.replace('_', ' ').toUpperCase()

const getStatusBannerClass = (status) => ({
  'pending': 'bg-yellow-50 text-yellow-800 border border-yellow-200',
  'pending_guarantor_approval': 'bg-orange-50 text-orange-800 border border-orange-200',
  'approved': 'bg-green-50 text-green-800 border border-green-200',
  'disbursed': 'bg-blue-50 text-blue-800 border border-blue-200',
  'active': 'bg-blue-100 text-blue-800 border border-blue-200',
  'completed': 'bg-green-50 text-slate-800 border border-green-200',
  'rejected': 'bg-red-50 text-red-800 border border-red-200',
  'defaulted': 'bg-gray-50 text-gray-800 border border-gray-200'
}[status] || 'bg-slate-50 text-slate-800 border border-slate-200')

const getStatusDescription = (status) => ({
  'pending': 'This loan application is awaiting review and approval.',
  'pending_guarantor_approval': 'This loan application is awaiting guarantor acceptance.',
  'approved': 'This loan has been approved and is ready for disbursement.',
  'disbursed': 'This loan has been disbursed to the member.',
  'active': 'This loan is active with ongoing repayments.',
  'completed': 'This loan has been fully repaid.',
  'rejected': 'This loan application has been rejected.',
  'cancelled': 'This loan application has been cancelled.'
}[status] || 'Unknown status')

const getGuarantorStatusClass = (status) => ({
  'pending': 'bg-yellow-100 text-yellow-800',
  'accepted': 'bg-emerald-100 text-emerald-800',
  'rejected': 'bg-red-100 text-red-800'
}[status] || 'bg-slate-100 text-slate-800')

// Toast
const visible = ref(Boolean(props.message))
const message = ref(props.message || '')
const type = ref(props.type || 'success')
let toastTimer = null
const toastAppearance = computed(() => type.value === 'success' ? 'bg-white/95 border border-slate-200' : 'bg-white/95 border border-red-200')
const close = () => { visible.value = false; if (toastTimer) { clearTimeout(toastTimer); toastTimer = null } }

if (visible.value && props.duration > 0) {
  toastTimer = setTimeout(() => { visible.value = false; toastTimer = null }, props.duration)
}

function showToast(txt = '', t = 'success', duration = props.duration) {
  message.value = txt
  type.value = t
  visible.value = true
  if (toastTimer) clearTimeout(toastTimer)
  if (duration > 0) toastTimer = setTimeout(() => { visible.value = false; toastTimer = null }, duration)
}

// Error helper
function firstError(err) {
  if (!err) return ''
  if (Array.isArray(err)) return err[0]
  if (typeof err === 'object') return Object.values(err)[0]
  return String(err)
}

// Actions
const approveLoan = async () => {
  resetValidation()
  if (approvalForm.approved_amount > props.loan.applied_amount) {
    validationErrors.approved_amount = ['Cannot approve more than applied amount']
    return
  }

  if (!approvalForm.approved_amount || approvalForm.approved_amount <= 0) {
    validationErrors.approved_amount = ['Enter a valid amount']
    return
  }

  if (approvalForm.approved_amount > props.loan.applied_amount) {
    validationErrors.approved_amount = ['Cannot approve more than applied amount']
    return
  }
  loading.approve = true
  try {
    const { data } = await axios.post(route('loans.approve', props.loan.id), approvalForm)
    showToast(data.message || 'Loan approved', 'success')
    showApprovalModal.value = false
    router.visit(route('loans.show', props.loan.id), {
      preserveScroll: true,
      preserveState: false
    })
  } catch (error) {
    const res = error.response
    if (res?.data?.errors) Object.assign(validationErrors, res.data.errors)
    showToast(res?.data?.message || 'Approval failed', 'error')
  } finally {
    loading.approve = false
  }
}

const rejectLoan = async () => {
  resetValidation()
  if (!rejectionForm.rejection_reason || rejectionForm.rejection_reason.trim().length < 3) {
    validationErrors.rejection_reason = ['Please provide a reason for rejection (min 3 characters).']
    return
  }
  loading.reject = true
  try {
    const response = await axios.post(`/loans/${props.loan.id}/reject`, rejectionForm)
    showToast(response.data.message || 'Loan rejected', 'success')
    showRejectionModal.value = false
    router.reload()
  } catch (err) {
    const res = err.response
    if (res?.data?.errors) Object.assign(validationErrors, res.data.errors)
    showToast(res?.data?.message || 'Failed to reject loan', 'error')
  } finally {
    loading.reject = false
  }
}

const disburseLoan = async () => {
  resetValidation()
  if (!disbursementForm.disbursement_method) {
    validationErrors.disbursement_method = ['Select a disbursement method.']
    return
  }
  loading.disburse = true
  try {
    const response = await axios.post(`/loans/${props.loan.id}/disburse`, {
      disbursement_method: disbursementForm.disbursement_method,
      disbursement_reference: disbursementForm.disbursement_reference
    })
    showToast(response.data.message || 'Loan disbursed successfully', 'success')
    showDisbursementModal.value = false
    router.reload()
  } catch (err) {
    const res = err.response
    if (res?.data?.errors) Object.assign(validationErrors, res.data.errors)
    showToast(res?.data?.message || 'Failed to disburse loan', 'error')
  } finally {
    loading.disburse = false
  }
}



watch(showApprovalModal, (val) => {
  if (!val) {
    approvalForm.approved_amount = null
    approvalForm.approval_notes = ''
  }
})

const showDocsModal = ref(false)
</script>