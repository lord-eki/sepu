<template>
  <AppLayout :breadcrumbs="[
    { title: 'Loans', href: isMemberRole ? '/my-loans' : route('loans.index') },
    { title: 'Loan Details' }
  ]">

    <Head title="Loan Details" />

    <!-- ================= TOAST ================= -->
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-3"
      enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-3">
      <div v-if="visible" class="fixed top-6 left-1/2 -translate-x-1/2 z-50 w-[92%] max-w-lg
               bg-white/90 backdrop-blur-xl border border-slate-200
               shadow-xl rounded-2xl px-5 py-3 flex gap-3 items-start">
        <div class="h-8 w-8 rounded-full flex items-center justify-center text-white font-bold"
          :class="type === 'success' ? 'bg-emerald-500' : type === 'error' ? 'bg-red-500' : 'bg-slate-500'">
          {{ type === 'success' ? '✓' : '!' }}
        </div>

        <div class="flex-1">
          <p class="text-sm font-medium text-slate-800">{{ message }}</p>
        </div>

        <button @click="close" class="text-slate-500 hover:text-slate-900">✕</button>
      </div>
    </transition>

    <!-- ================= PAGE WRAPPER ================= -->
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">

      <!-- ================= HEADER (STRIPE STYLE) ================= -->
      <div class="border-b bg-white/70 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col lg:flex-row lg:items-center justify-between gap-5">

          <div class="flex items-center gap-4">
            <div class="p-3 rounded-2xl bg-gradient-to-br from-blue-900 to-slate-700 shadow-lg">
              <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3" />
              </svg>
            </div>

            <div>
              <h1 class="text-2xl font-bold text-slate-900">
                Loan No: <span class="text-orange-600">{{ loan.loan_number }}</span>
              </h1>
              <p class="text-sm text-slate-500">Complete loan lifecycle & management</p>
            </div>
          </div>

          <div class="flex gap-3 flex-wrap">
            <Link v-if="canEdit" :href="route('loans.edit', loan.id)"
              class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm hover:bg-slate-800 shadow">
            Edit Loan
            </Link>

            <Link :href="isMemberRole ? route('my-loans') : route('loans.index')"
              class="px-4 py-2 rounded-xl bg-white border text-slate-700 hover:bg-slate-50 text-sm">
            Back
            </Link>
          </div>

        </div>
      </div>

      <!-- ================= STATUS STRIP ================= -->
      <div class="max-w-7xl mx-auto px-6 pt-6">
        <div class="rounded-2xl shadow-xl bg-white p-5 flex items-center gap-4">

          <div class="h-10 w-10 rounded-xl flex items-center justify-center font-bold"
            :class="getStatusBannerClass(loan.status)">
            !
          </div>

          <div>
            <p class="font-semibold text-slate-900">
              Status: {{ formatStatus(loan.status) }}
            </p>
            <p class="text-sm text-slate-500">
              {{ getStatusDescription(loan.status) }}
            </p>
          </div>

        </div>
      </div>

      <!-- ================= QUICK ACTIONS (RESTORED) ================= -->
      <div v-if="hasQuickActions" class="max-w-7xl mx-auto px-6 mt-6">
        <div class="bg-white border rounded-2xl shadow-sm p-5">

          <h3 class="text-sm font-semibold text-slate-900 mb-4">Quick Actions</h3>

          <div class="flex flex-wrap gap-3">

            <button v-if="canApprove" @click="openApprovalModal"
              class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm hover:bg-emerald-700">
              Approve
            </button>

            <button v-if="canReject" @click="openRejectModal"
              class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm hover:bg-red-700">
              Reject
            </button>

            <button v-if="canDisburse" @click="openDisbursementModal"
              class="px-4 py-2 rounded-xl bg-blue-900 text-white text-sm hover:bg-blue-950">
              Disburse
            </button>

            <Link v-if="canViewSchedule" :href="route('loans.schedule', loan.id)"
              class="px-4 py-2 rounded-xl bg-white border text-sm hover:bg-slate-50">
            Schedule
            </Link>

            <Link v-if="canViewRepayments" :href="route('loans.repayments', loan.id)"
              class="px-4 py-2 rounded-xl bg-orange-500 text-white text-sm hover:bg-orange-600">
            Repayments
            </Link>

          </div>

        </div>
      </div>

      <!-- ================= MAIN GRID (STRIPE STYLE) ================= -->
      <div class="max-w-7xl mx-auto px-6 py-8 grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT -->
        <div class="lg:col-span-2 space-y-6">

          <!-- LOAN DETAILS -->
          <div class="bg-white border rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-5">Loan Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

              <div class="p-4 bg-slate-100 rounded-xl">
                <p class="text-slate-500">Applied</p>
                <p class="font-semibold">KES {{ formatCurrency(loan.applied_amount) }}</p>
              </div>

              <div class="p-4 bg-slate-100 rounded-xl">
                <p class="text-slate-500">Approved</p>
                <p class="font-semibold text-blue-700">
                  KES {{ formatCurrency(loan.approved_amount) }}
                </p>
              </div>

              <div class="p-4 bg-slate-100 rounded-xl">
                <p class="text-slate-500">Interest</p>
                <p class="font-semibold">{{ loan.interest_rate }}%</p>
              </div>

              <div class="p-4 bg-slate-100 rounded-xl">
                <p class="text-slate-500">Term</p>
                <p class="font-semibold">{{ loan.term_months }} months</p>
              </div>

            </div>
          </div>

          <!-- GUARANTORS-->
          <div v-if="loan.guarantors?.length" class="bg-white border rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Guarantors</h2>

            <div class="space-y-3">
              <div v-for="g in loan.guarantors" :key="g.id" class="p-4 border rounded-xl flex justify-between">
                <div>
                  <p class="font-medium">
                    {{ g.guarantor_member.first_name }} {{ g.guarantor_member.last_name }}
                  </p>
                  <p class="text-sm text-slate-500">{{ g.guarantor_member.membership_id }}</p>
                </div>

                <div class="text-right">
                  <p class="font-semibold">KES {{ formatCurrency(g.guaranteed_amount) }}</p>
                  <span class="text-xs px-2 py-1 rounded-full bg-slate-100">
                    {{ g.status }}
                  </span>
                </div>
              </div>
            </div>

          </div>

        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="space-y-6">

          <!-- BALANCE -->
          <div class="bg-white border rounded-2xl shadow-sm p-6">
            <h2 class="font-semibold mb-4">Balance</h2>

            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-slate-500">Outstanding</span>
                <span class="text-red-600 font-semibold">
                  KES {{ formatCurrency(loan.outstanding_balance) }}
                </span>
              </div>

              <div class="flex justify-between">
                <span class="text-slate-500">Principal</span>
                <span>{{ formatCurrency(loan.principal_balance) }}</span>
              </div>

              <div class="flex justify-between">
                <span class="text-slate-500">Interest</span>
                <span>{{ formatCurrency(loan.interest_balance) }}</span>
              </div>
            </div>
          </div>

          <!-- FEES -->
          <div class="bg-white border rounded-2xl shadow-sm p-6">
            <h2 class="font-semibold mb-4">Fees</h2>

            <div class="flex justify-between text-sm">
              <span>Processing</span>
              <span>{{ formatCurrency(loan.processing_fee) }}</span>
            </div>

            <div class="flex justify-between text-sm mt-2">
              <span>Insurance</span>
              <span>{{ formatCurrency(loan.insurance_fee) }}</span>
            </div>
          </div>

        </div>
      </div>

    </div>

    <!-- APPROVAL MODAL -->
    <TransitionRoot as="template" :show="showApprovalModal">
      <Dialog as="div" class="relative z-40" @close="closeApprovalModal">

        <TransitionChild enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 z-40 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">

            <TransitionChild enter="ease-out duration-300" enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95">

              <DialogPanel v-if="loan"
                class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl border border-slate-200 overflow-hidden">

                <!-- HEADER -->
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 text-white">
                  <DialogTitle class="text-xl font-semibold">
                    Approve Loan Application
                  </DialogTitle>
                  <p class="text-sm text-orange-100">Review and set approval terms</p>
                </div>

                <!-- CONTENT -->
                <div class="p-6 space-y-5">

                  <!-- SUMMARY -->
                  <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 rounded-xl p-4 border">
                      <p class="text-xs text-slate-500">Applied Amount</p>
                      <p class="font-semibold">
                        KES {{ formatCurrency(loan?.applied_amount ?? 0) }}
                      </p>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4 border">
                      <p class="text-xs text-slate-500">Term</p>
                      <p class="font-semibold">{{ loan?.term_months ?? 0 }} Months</p>
                    </div>
                  </div>

                  <!-- INPUT -->
                  <div>
                    <label class="text-sm font-medium text-slate-600">Approved Amount</label>
                    <input v-model.number="approvalForm.approved_amount" type="number"
                      class="mt-1 w-full rounded-xl border p-3 focus:ring-2 focus:ring-orange-500"
                      :class="{ 'border-red-300': validationErrors.approved_amount }" />

                    <p v-if="validationErrors.approved_amount" class="text-sm text-red-600 mt-1">
                      {{ firstError(validationErrors.approved_amount) }}
                    </p>
                  </div>

                  <!-- BREAKDOWN -->
                  <div v-if="approvalForm.approved_amount" class="rounded-2xl border bg-slate-50 p-4 space-y-2">

                    <p class="font-semibold text-sm">Breakdown</p>

                    <div class="flex justify-between text-sm">
                      <span>Processing Fee</span>
                      <span class="text-red-600">- {{ formatCurrency(processingFeePreview) }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                      <span>Insurance Fee</span>
                      <span class="text-red-600">- {{ formatCurrency(insuranceFeePreview) }}</span>
                    </div>

                    <div class="border-t pt-2 flex justify-between font-semibold">
                      <span>Net Disbursement</span>
                      <span class="text-green-600">
                        {{ formatCurrency(netDisbursementPreview) }}
                      </span>
                    </div>

                    <div class="flex justify-between font-semibold text-sm">
                      <span>Monthly Repayment</span>
                      <span class="text-orange-600">
                        {{ formatCurrency(monthlyPreview) }}
                      </span>
                    </div>
                  </div>

                  <!-- NOTES -->
                  <div>
                    <label class="text-sm font-medium text-slate-600">Notes</label>
                    <textarea v-model="approvalForm.approval_notes" rows="3"
                      class="mt-1 w-full rounded-xl border p-3 focus:ring-2 focus:ring-orange-500"
                      :class="{ 'border-red-300': validationErrors.approval_notes }" />
                  </div>

                </div>

                <!-- ACTIONS -->
                <div class="px-6 py-4 bg-slate-50 border-t flex justify-end gap-3">

                  <button @click="closeApprovalModal" class="px-4 py-2 rounded-xl bg-white border hover:bg-slate-100">
                    Cancel
                  </button>

                  <button @click="approveLoan" :disabled="loading.approve"
                    class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white disabled:opacity-50">
                    <span v-if="!loading.approve">Approve Loan</span>
                    <span v-else>Processing…</span>
                  </button>

                </div>

              </DialogPanel>

            </TransitionChild>
          </div>
        </div>

      </Dialog>
    </TransitionRoot>
    <!-- REJECTION MODAL -->
    <TransitionRoot as="template" :show="showRejectionModal">
      <Dialog as="div" class="relative z-40" @close="closeRejectModal">

        <TransitionChild enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 z-40 flex items-center justify-center p-4">

          <TransitionChild enter="ease-out duration-300" enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100">

            <DialogPanel class="w-full max-w-xl rounded-3xl bg-white shadow-2xl overflow-hidden">

              <div class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-5">
                <DialogTitle class="text-xl font-semibold">Reject Loan</DialogTitle>
                <p class="text-sm text-red-100">Provide rejection reason</p>
              </div>

              <div class="p-6">
                <label class="text-sm font-medium text-slate-600">Reason</label>

                <textarea v-model="rejectionForm.rejection_reason" rows="4"
                  class="mt-1 w-full rounded-xl border p-3 focus:ring-2 focus:ring-red-500"
                  :class="{ 'border-red-300': validationErrors.rejection_reason }" />

                <p v-if="validationErrors.rejection_reason" class="text-sm text-red-600 mt-1">
                  {{ firstError(validationErrors.rejection_reason) }}
                </p>
              </div>

              <div class="px-6 py-4 bg-slate-50 border-t flex justify-end gap-3">

                <button @click="closeRejectModal" class="px-4 py-2 rounded-xl bg-white border hover:bg-slate-100">
                  Cancel
                </button>

                <button @click="rejectLoan" :disabled="loading.reject"
                  class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white disabled:opacity-50">
                  <span v-if="!loading.reject">Reject</span>
                  <span v-else>Processing…</span>
                </button>

              </div>

            </DialogPanel>

          </TransitionChild>
        </div>

      </Dialog>
    </TransitionRoot>
    <!-- DISBURSEMENT MODAL -->
    <TransitionRoot as="template" :show="showDisbursementModal">
      <Dialog as="div" class="relative z-40" @close="closeDisbursementModal">

        <TransitionChild enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 z-40 flex items-center justify-center p-4">

          <TransitionChild enter="ease-out duration-300" enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100">

            <DialogPanel class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden">

              <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-5">
                <DialogTitle class="text-xl font-semibold">Disburse Loan</DialogTitle>
                <p class="text-sm text-blue-100">Confirm payout details</p>
              </div>

              <div class="p-6 space-y-5">

                <!-- SUMMARY -->
                <div class="bg-slate-50 rounded-xl p-4 border space-y-2 text-sm">

                  <div class="flex justify-between">
                    <span>Approved</span>
                    <span class="font-semibold">{{ formatCurrency(loan.approved_amount) }}</span>
                  </div>

                  <div class="flex justify-between text-red-600">
                    <span>Fees</span>
                    <span>
                      - {{ formatCurrency(loan.processing_fee + loan.insurance_fee) }}
                    </span>
                  </div>

                  <div class="border-t pt-2 flex justify-between font-semibold">
                    <span>Net Disbursement</span>
                    <span class="text-green-600">
                      {{ formatCurrency(
    loan.approved_amount -
    loan.processing_fee -
    loan.insurance_fee
  ) }}
                    </span>
                  </div>

                </div>

                <!-- METHOD -->
                <div>
                  <label class="text-sm font-medium">Method</label>
                  <select v-model="disbursementForm.disbursement_method"
                    class="mt-1 w-full rounded-xl border p-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">Select</option>
                    <option value="cash">Cash</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank_transfer">Bank Transfer</option>
                  </select>
                </div>

                <!-- REF -->
                <input v-model="disbursementForm.disbursement_reference" class="w-full rounded-xl border p-2"
                  placeholder="Reference (optional)" />

              </div>

              <div class="px-6 py-4 bg-slate-50 border-t flex justify-end gap-3">

                <button @click="closeDisbursementModal" class="px-4 py-2 rounded-xl bg-white border hover:bg-slate-100">
                  Cancel
                </button>

                <button @click="disburseLoan" :disabled="loading.disburse"
                  class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white disabled:opacity-50">
                  <span v-if="!loading.disburse">Confirm</span>
                  <span v-else>Processing…</span>
                </button>

              </div>

            </DialogPanel>

          </TransitionChild>
        </div>

      </Dialog>
    </TransitionRoot>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import AppLayout from '@/layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
  loan: Object,
  auth: Object,
  message: String,
  type: { type: String, default: 'success' },
  duration: { type: Number, default: 5000 }
})

const loan = computed(() => props.loan)
const isMemberRole = computed(() => props.auth.user.role === 'member')

// modal controls
const showApprovalModal = ref(false)
const showRejectionModal = ref(false)
const showDisbursementModal = ref(false)

function openApprovalModal() { resetValidation(); approvalForm.approved_amount = props.loan.applied_amount || 0; showApprovalModal.value = true }
function openRejectModal() { resetValidation(); showRejectionModal.value = true }
function openDisbursementModal() { resetValidation(); disbursementForm.disbursed_amount = props.loan.approved_amount || 0; showDisbursementModal.value = true }

function closeApprovalModal() { showApprovalModal.value = false }
function closeRejectModal() { showRejectionModal.value = false }
function closeDisbursementModal() { showDisbursementModal.value = false }

// forms & validation
const approvalForm = reactive({
  approved_amount: '',
  interest_rate: '',
  months: ''
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

// loading states
const loading = reactive({ approve: false, reject: false, disburse: false })

function resetValidation() {
  for (const k in validationErrors) delete validationErrors[k]
}



// computed previews for approval modal
const processingFeePreview = computed(() => {
  if (!approvalForm.approved_amount || !loan.value?.loanProduct) return 0
  return (approvalForm.approved_amount * loan.value?.loanProduct?.processing_fee_rate) / 100
})

const insuranceFeePreview = computed(() => {
  if (!approvalForm.approved_amount || !loan.value?.loanProduct) return 0
  return (approvalForm.approved_amount * loan.value?.loanProduct.insurance_rate) / 100
})

const netDisbursementPreview = computed(() => {
  if (!approvalForm.approved_amount) return 0
  return approvalForm.approved_amount - processingFeePreview.value - insuranceFeePreview.value
})

const monthlyPreview = computed(() => {
  if (!approvalForm.approved_amount || !loan.value?.loanProduct) return 0
  const P = approvalForm.approved_amount
  const r = loan.value?.loanProduct.interest_rate / 100 / 12
  const n = loan.value?.term_months
  if (r === 0) return P / n
  return (P * r) / (1 - Math.pow(1 + r, -n))
})


// permissions computed
const canEdit = computed(() => {
  return props.loan.status === 'pending' && ['admin', 'loan_officer', 'management'].includes(props.auth.user.role)
})

const canApprove = computed(() => {
  return props.loan.status === 'pending' && ['admin', 'management'].includes(props.auth.user.role)
})

const canReject = computed(() => {
  return ['pending', 'approved'].includes(props.loan.status) && ['admin', 'management'].includes(props.auth.user.role)
})

const canDisburse = computed(() => {
  return props.loan.status === 'approved' && ['admin', 'accountant'].includes(props.auth.user.role)
})

const canViewSchedule = computed(() => {
  return ['disbursed', 'active', 'completed'].includes(props.loan.status)
})

const canViewRepayments = computed(() => {
  return ['disbursed', 'active', 'completed'].includes(props.loan.status)
})

const hasQuickActions = computed(() => {
  return canApprove.value || canReject.value || canDisburse.value || canViewSchedule.value || canViewRepayments.value
})

// helpers
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0)
}

const formatDate = (date) => {
  if (!date) return '-'
  try { return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: '2-digit' }) }
  catch { return date }
}

const formatStatus = (status = '') => status.replace('_', ' ').toUpperCase()

const getStatusBannerClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-50 text-yellow-800 border border-yellow-200',
    'approved': 'bg-green-50 text-green-800 border border-green-200',
    'disbursed': 'bg-blue-50 text-blue-800 border border-blue-200',
    'active': 'bg-blue-100 text-blue-800 border border-blue-200',
    'completed': 'bg-green-50 text-slate-800 border border-green-200',
    'rejected': 'bg-red-50 text-red-800 border border-red-200',
    'defaulted': 'bg-gray-50 text-gray-800 border border-gray-200'
  }
  return classes[status] || 'bg-slate-50 text-slate-800 border border-slate-200'
}

const getStatusIcon = (status) => 'svg'

const getStatusDescription = (status) => {
  const descriptions = {
    'pending': 'This loan application is awaiting review and approval.',
    'approved': 'This loan has been approved and is ready for disbursement.',
    'disbursed': 'This loan has been disbursed to the member.',
    'active': 'This loan is active with ongoing repayments.',
    'completed': 'This loan has been fully repaid.',
    'rejected': 'This loan application has been rejected.',
    'cancelled': 'This loan application has been cancelled.'
  }
  return descriptions[status] || 'Unknown status'
}

const getGuarantorStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'accepted': 'bg-emerald-100 text-emerald-800',
    'rejected': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-slate-100 text-slate-800'
}

// Toast state
const visible = ref(Boolean(props.message))
const message = ref(props.message || '')
const type = ref(props.type || 'success')
let toastTimer = null

const toastAppearance = computed(() => {
  return type.value === 'success'
    ? 'bg-white/95 border border-slate-200'
    : 'bg-white/95 border border-red-200'
})

const close = () => {
  visible.value = false
  if (toastTimer) { clearTimeout(toastTimer); toastTimer = null }
}

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

// returns first message from array or string
function firstError(err) {
  if (!err) return ''
  if (Array.isArray(err)) return err[0]
  if (typeof err === 'object') return Object.values(err)[0]
  return String(err)
}

// Actions (with validation)
const approveLoan = async () => {

  resetValidation()

  loading.approve = true

  try {

    const { data } = await axios.post(
      route('loans.approve', props.loan.id),
      approvalForm
    )

    alert(data.message)

    showApprovalModal.value = false

    router.reload({ only: ['loan'] })

  } catch (error) {

    const res = error.response

    if (res?.data?.errors) {
      Object.assign(validationErrors, res.data.errors)
    }

    if (res?.data?.message) {
      alert(res.data.message)
    }

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


watch(showApprovalModal, (val) => {
  if (!val) {
    approvalForm.approved_amount = null
    approvalForm.approval_notes = ''
  }
})

const disburseLoan = async () => {
  resetValidation()

  if (!disbursementForm.disbursement_method) {
    validationErrors.disbursement_method = ['Select a disbursement method.']
    return
  }

  loading.disburse = true

  try {
    const response = await axios.post(
      `/loans/${props.loan.id}/disburse`,
      {
        disbursement_method: disbursementForm.disbursement_method,
        disbursement_reference: disbursementForm.disbursement_reference
      }
    )

    showToast(response.data.message || 'Loan disbursed successfully', 'success')

    showDisbursementModal.value = false
    router.reload()

  } catch (err) {
    const res = err.response

    if (res?.data?.errors) {
      Object.assign(validationErrors, res.data.errors)
    }

    showToast(res?.data?.message || 'Failed to disburse loan', 'error')
  } finally {
    loading.disburse = false
  }
}
</script>
