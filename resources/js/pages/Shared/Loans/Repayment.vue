<template>
  <AppLayout :breadcrumbs="[
    { title: 'Loans', href: isMemberRole ? '/my-loans' : route('loans.index') },
    { title: 'Loan Repayments' }
  ]">
    <div class="overflow-x-auto">

      <!-- Header Section -->
      <div
        class="flex flex-col sm:flex-row bg-[#0B2B40] rounded-lg p-4 justify-between items-start sm:items-center mb-6 mx-6 mt-5">
        <div class="flex items-start gap-4">
          <div class="rounded-2xl p-3 shadow-md bg-gradient-to-tr from-[#B40] to-[#133a56] text-white">
            <!-- compact icon -->
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
            </svg>
          </div>
          <div>
            <h2 class="text-lg sm:text-xl font-semibold text-white dark:text-white flex items-center gap-2">
              Loan Repayments -
              <span class="text-white dark:text-[#9cc6ff] font-medium">{{ loan.loan_number }}</span>
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Loan ID: <span class="font-medium">{{
    loan.loan_number }}</span></p>
          </div>
        </div>

        <div class="flex flex-wrap gap-3 mt-3 sm:mt-0 items-center">
          <button v-if="canRecordPayment" @click="showPaymentModal = true"
            class="inline-flex items-center gap-2 bg-[#F97316] hover:cursor-pointer hover:bg-[#fb7c26] text-white px-4 py-2 rounded-lg shadow transition-colors duration-200 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Record Payment
          </button>

          <button v-if="canSelfRepay" @click="showSelfRepaymentModal = true"
            class="bg-green-600 border border-[#0B2B40] text-white px-4 py-2 rounded-lg shadow transition-all text-sm font-medium hover:cursor-pointer hover:bg-green-500">
            Repay Loan
          </button>

          <Link :href="route('loans.show', loan.id)"
            class="inline-flex items-center gap-2 bg-white hover:bg-gray-200 text-[#0B2B40] px-4 py-2 rounded-lg shadow transition-colors duration-200 text-sm font-medium">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Loan
          </Link>
        </div>
      </div>

      <!-- Loan Summary -->
      <div
        class="bg-white dark:bg-gray-800 shadow rounded-2xl p-6 mb-6 mx-5 border border-gray-100 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Member</p>
            <p class="mt-1 text-lg font-semibold text-[#0B2B40] dark:text-gray-100">
              {{ loan.member.first_name }} {{ loan.member.last_name }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ loan.member.membership_id }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Loan Amount</p>
            <p class="mt-1 text-lg font-semibold text-[#0B2B40] dark:text-gray-100">
              KES {{ formatCurrency(loan.disbursed_amount) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Monthly Repayment</p>
            <p class="mt-1 text-lg font-semibold text-[#0B2B40] dark:text-gray-100">
              KES {{ formatCurrency(loan.monthly_repayment) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Outstanding Balance</p>
            <p class="mt-1 text-lg font-semibold text-[#F97316]">
              KES {{ formatCurrency(loan.outstanding_balance) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Repayment Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 mx-5">
        <div v-for="stat in stats" :key="stat.label"
          class="bg-white dark:bg-gray-800 shadow border border-gray-200 rounded-2xl p-6 flex items-center hover:shadow-lg transition">
          <div :class="`w-10 h-10 rounded-lg flex items-center justify-center ${stat.bg}`">
            <component :is="stat.icon" class="w-5 h-5 text-white" />
          </div>
          <div class="ml-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.label }}</p>
            <p class="text-xl font-semibold text-[#0B2B40] dark:text-gray-100">{{ stat.value }}</p>
          </div>
        </div>
      </div>

      <!-- Repayment Schedule Table -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-2xl overflow-hidden">
        <!-- Header -->
        <div
          class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-white to-white/95 dark:from-[#0B2B40] dark:to-[#0B2B40]/95">
          <h3 class="text-lg font-semibold text-[#0B2B40] dark:text-white">
            Repayment Schedule
          </h3>
        </div>

        <!-- Table Container -->
        <div
          class="relative w-full overflow-x-auto border border-gray-100 dark:border-gray-700 bg-white dark:bg-[#0B2B40]">
          <table class="w-full text-xs text-left border-collapse">
            <!-- Table Header -->
            <thead class="bg-gradient-to-r from-blue-100 to-blue-200 dark:from-[#123C5B] dark:to-[#0B2B40]">
              <tr>
                <th v-for="header in tableHeaders" :key="header"
                  class="px-4 py-3 font-semibold text-[#0B2B40] dark:text-gray-100 text-xs uppercase tracking-wide text-nowrap">
                  {{ header }}
                </th>
              </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="(repayment, index) in repayments" :key="repayment.id"
                :class="[getRowClass(repayment), 'hover:bg-blue-50 dark:hover:bg-[#13324D] transition-colors duration-150']">
                <td class="px-4 py-3 text-gray-800 dark:text-gray-200 font-medium text-nowrap">{{ index + 1 }}</td>

                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-nowrap">
                  {{ formatDate(repayment.due_date) }}
                  <span v-if="repayment.days_late > 0" class="block text-xs text-[#F97316] mt-1">
                    {{ repayment.days_late }} days late
                  </span>
                </td>

                <td class="px-4 py-3 font-semibold text-[#0B2B40] dark:text-white text-nowrap">
                  KES {{ formatCurrency(repayment.expected_amount) }}
                </td>

                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-nowrap">
                  KES {{ formatCurrency(repayment.principal_amount) }}
                </td>

                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-nowrap">
                  KES {{ formatCurrency(repayment.interest_amount) }}
                </td>

                <td class="px-4 py-3 text-[#F97316] text-nowrap">
                  KES {{ formatCurrency(repayment.penalty_amount) }}
                </td>

                <td class="px-4 py-3 font-semibold text-emerald-600 text-nowrap">
                  KES {{ formatCurrency(repayment.paid_amount) }}
                  <span v-if="repayment.payment_date" class="block text-xs text-gray-500 dark:text-gray-300">
                    {{ formatDate(repayment.payment_date) }}
                  </span>
                </td>

                <td class="px-4 py-3 font-semibold text-[#F97316] text-nowrap">
                  KES {{ formatCurrency(repayment.outstanding_amount) }}
                </td>

                <td class="px-4 py-3 text-nowrap">
                  <span :class="getStatusBadgeClass(repayment.status)"
                    class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full">
                    {{ formatStatus(repayment.status) }}
                  </span>
                </td>

                <td class="px-4 py-3 text-sm font-medium flex space-x-3 text-nowrap">
                  <button v-if="canRecordPayment && repayment.status !== 'paid'"
                    @click="selectRepaymentForPayment(repayment)"
                    class="text-[#F97316] hover:text-[#fb7c26] transition">
                    Pay
                  </button>
                  <button v-if="repayment.transaction_id" @click="viewTransaction(repayment.transaction_id)"
                    class="text-[#0B2B40] dark:text-gray-100 hover:text-[#102f4a] transition">
                    View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>


      <!-- Self Repayment Modal -->
      <TransitionRoot as="template" :show="showSelfRepaymentModal">
        <Dialog as="div" class="relative z-50" @close="showSelfRepaymentModal = false">
          <TransitionChild enter="ease-out duration-300" enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
            <div class="fixed inset-0 flex items-center justify-center p-4">
              <DialogPanel
                class="bg-white dark:bg-gray-800 w-full max-w-md rounded-2xl shadow-2xl p-6 space-y-4 border border-gray-100 dark:border-gray-700">
                <DialogTitle class="text-xl font-semibold text-[#0B2B40] dark:text-gray-100">
                  Self Loan Repayment
                </DialogTitle>

                <form @submit.prevent="submitSelfRepayment">
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                      <input v-model="selfRepayment.amount" type="number" required
                        class="w-full mt-1 rounded-lg shadow-sm border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-[#F97316] focus:border-[#F97316] dark:bg-gray-900 dark:text-gray-100" />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                      <select v-model="selfRepayment.method" required
                        class="w-full mt-1 rounded-lg shadow-sm border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-[#F97316] focus:border-[#F97316] dark:bg-gray-900 dark:text-gray-100">
                        <option disabled value="">Select method</option>
                        <option value="mpesa">M-Pesa</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cash">Cash Deposit</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number
                        (optional)</label>
                      <input v-model="selfRepayment.reference" type="text"
                        class="w-full mt-1 rounded-lg shadow-sm border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-[#F97316] focus:border-[#F97316] dark:bg-gray-900 dark:text-gray-100" />
                    </div>
                  </div>

                  <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" @click="showSelfRepaymentModal = false"
                      class="px-4 py-2 bg-white border border-gray-200 text-[#0B2B40] rounded-lg hover:bg-gray-50">
                      Cancel
                    </button>
                    <button type="submit"
                      class="px-4 py-2 bg-[#F97316] text-white rounded-lg hover:bg-[#fb7c26] shadow">
                      Submit Payment
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </div>
          </TransitionChild>
        </Dialog>
      </TransitionRoot>

      <!-- Payment Modal -->
      <TransitionRoot as="template" :show="showPaymentModal">
        <Dialog as="div" class="relative z-10" @close="showPaymentModal = false">
          <TransitionChild as="template" enter="ease-out duration-300" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave="ease-in duration-200" leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div class="fixed inset-0 bg-gray-800 bg-opacity-60 transition-opacity" />
          </TransitionChild>

          <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
              <TransitionChild as="template" enter="ease-out duration-300"
                enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                leave-from="opacity-100 translate-y-0 sm:scale-100"
                leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <DialogPanel
                  class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 px-6 pb-6 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700">
                  <form @submit.prevent="recordPayment">
                    <div class="flex flex-col items-center">
                      <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#E6F7FF]">
                        <svg class="h-6 w-6 text-[#0B2B40]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 8v8m0 0v1m0-1c1.11 0 2.08-.402 2.599-1M12 8V7m0 1c-1.11 0-2.08.402-2.599 1" />
                        </svg>
                      </div>
                      <DialogTitle as="h3" class="mt-3 text-lg font-semibold text-[#0B2B40] dark:text-white">
                        Record Loan Payment
                      </DialogTitle>

                      <div class="mt-2 text-center" v-if="selectedRepayment">
                        <p class="text-sm text-gray-500 dark:text-gray-300">
                          Installment {{ getRepaymentNumber(selectedRepayment) }} - Due: {{
    formatDate(selectedRepayment.due_date) }}
                        </p>
                        <p class="text-sm font-semibold text-[#0B2B40] dark:text-gray-100">
                          Outstanding: KES {{ formatCurrency(selectedRepayment.outstanding_amount) }}
                        </p>
                      </div>
                    </div>

                    <div class="mt-5 space-y-4">
                      <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment
                          Amount *</label>
                        <input v-model="paymentForm.amount" type="number" step="0.01" id="amount" required min="0.01"
                          :max="selectedRepayment?.outstanding_amount"
                          class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-[#F97316] focus:border-[#F97316]" />
                      </div>

                      <div>
                        <label for="payment_method"
                          class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method *</label>
                        <select v-model="paymentForm.payment_method" id="payment_method" required
                          class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-[#F97316] focus:border-[#F97316]">
                          <option value="">Select method...</option>
                          <option value="cash">Cash</option>
                          <option value="mobile_money">Mobile Money</option>
                          <option value="bank_transfer">Bank Transfer</option>
                          <option value="cheque">Cheque</option>
                        </select>
                      </div>

                      <div>
                        <label for="reference_number"
                          class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference Number</label>
                        <input v-model="paymentForm.reference_number" type="text" id="reference_number"
                          placeholder="Receipt number, transaction ID, etc."
                          class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-[#F97316] focus:border-[#F97316]" />
                      </div>

                      <div>
                        <label for="payment_date"
                          class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date *</label>
                        <input v-model="paymentForm.payment_date" type="date" id="payment_date" required :max="today"
                          class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-[#F97316] focus:border-[#F97316]" />
                      </div>

                      <div>
                        <label for="notes"
                          class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                        <textarea v-model="paymentForm.notes" id="notes" rows="2"
                          placeholder="Additional notes about this payment..."
                          class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:ring-2 focus:ring-[#F97316] focus:border-[#F97316]"></textarea>
                      </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row-reverse gap-3">
                      <button type="submit"
                        class="inline-flex justify-center rounded-md bg-[#F97316] hover:bg-[#fb7c26] px-4 py-2 text-sm font-semibold text-white shadow transition">
                        Record Payment
                      </button>
                      <button type="button"
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-[#0B2B40] dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-600 shadow"
                        @click="showPaymentModal = false">
                        Cancel
                      </button>
                    </div>
                  </form>
                </DialogPanel>
              </TransitionChild>
            </div>
          </div>
        </Dialog>
      </TransitionRoot>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Check, Clock, AlertTriangle, DollarSign } from 'lucide-vue-next'

const props = defineProps({
  loan: Object,
  repayments: Array,
  auth: Object
})

/* ----------- Local UI state & forms (kept exactly) ----------- */
const showPaymentModal = ref(false)
const selectedRepayment = ref(null)

const paymentForm = reactive({
  amount: '',
  payment_method: '',
  reference_number: '',
  payment_date: new Date().toISOString().split('T')[0],
  notes: ''
})

const showSelfRepaymentModal = ref(false)

const selfRepayment = ref({
  amount: '',
  method: '',
  reference: ''
})

/* ----------- Permissions & computed values (kept exactly) ----------- */
const canRecordPayment = computed(() => ['accountant', 'admin', 'management'].includes(props.auth.user.role))
const paidCount = computed(() => props.repayments.filter(r => r.status === 'paid').length)
const pendingCount = computed(() => props.repayments.filter(r => r.status === 'pending').length)
const overdueCount = computed(() => props.repayments.filter(r => r.status === 'overdue').length)
const totalPaid = computed(() => props.repayments.reduce((sum, r) => sum + (r.paid_amount || 0), 0))
const today = computed(() => new Date().toISOString().split('T')[0])
const canSelfRepay = computed(() =>
  props.auth.user.role === 'member' &&
  ['active', 'disbursed'].includes(props.loan.status?.toLowerCase())
)

const tableHeaders = [
  'Installment', 'Due Date', 'Expected Amount', 'Principal', 'Interest',
  'Penalty', 'Paid Amount', 'Outstanding', 'Status', 'Actions'
]

const stats = computed(() => [
  { label: 'Paid Installments', value: paidCount.value, bg: 'bg-[#0B2B40]', icon: Check },
  { label: 'Pending', value: pendingCount.value, bg: 'bg-[#F97316]', icon: Clock },
  { label: 'Overdue', value: overdueCount.value, bg: 'bg-red-500', icon: AlertTriangle },
  { label: 'Total Paid', value: `KES ${formatCurrency(totalPaid.value)}`, bg: 'bg-green-600', icon: DollarSign },
])

/* ----------- Helpers & formatting (kept exactly) ----------- */
const formatCurrency = (amount) => new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0)
const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' })
const formatStatus = (status) => status.replace('_', ' ').toUpperCase()
const getStatusBadgeClass = (status) => ({
  pending: 'bg-yellow-100 text-yellow-800',
  partial: 'bg-orange-100 text-orange-800',
  paid: 'bg-emerald-100 text-green-800',
  overdue: 'bg-red-100 text-red-800'
}[status] || 'bg-gray-100 text-gray-800')
const getRowClass = (r) => ({
  paid: 'bg-emerald-50',
  overdue: 'bg-red-50',
  partial: 'bg-orange-50'
}[r.status] || '')

const getRepaymentNumber = (r) => props.repayments.findIndex(x => x.id === r.id) + 1
const selectRepaymentForPayment = (r) => {
  selectedRepayment.value = r
  paymentForm.amount = r.outstanding_amount
  showPaymentModal.value = true
}

/* ----------- Actions & routing (kept exactly) ----------- */
const recordPayment = () => {
  const data = { ...paymentForm, repayment_id: selectedRepayment.value.id }
  router.post(route('loans.record-repayment', props.loan.id), data, {
    onSuccess: () => {
      showPaymentModal.value = false
      Object.assign(paymentForm, {
        amount: '',
        payment_method: '',
        reference_number: '',
        payment_date: today.value,
        notes: ''
      })
    }
  })
}

const submitSelfRepayment = () => {
  // Validate or send data to backend
  console.log('Self repayment submitted:', selfRepayment.value)
  showSelfRepaymentModal.value = false

  // Optional: flash success message or reload payments
}

const viewTransaction = (transactionId) => {
  router.visit(route('transactions.show', transactionId))
}

/* ----------- Header helper (kept simple) ----------- */
const goBack = () => {
  // If you prefer inertia route to loans.show, use:
  router.visit(route('loans.show', props.loan.id))
}
</script>
