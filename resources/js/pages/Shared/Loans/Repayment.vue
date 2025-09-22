<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Loan Repayments - {{ loan.loan_number }}
        </h2>
        <div class="flex space-x-3">
          <button v-if="canRecordPayment" @click="showPaymentModal = true"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Record Payment
          </button>
          <Link :href="route('loans.show', loan.id)"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
          Back to Loan
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Loan Summary -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-500">Member</label>
                <p class="mt-1 text-sm font-semibold text-gray-900">
                  {{ loan.member.first_name }} {{ loan.member.last_name }}
                </p>
                <p class="text-xs text-gray-500">{{ loan.member.membership_id }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Loan Amount</label>
                <p class="mt-1 text-sm font-semibold text-gray-900">
                  KES {{ formatCurrency(loan.disbursed_amount) }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Monthly Repayment</label>
                <p class="mt-1 text-sm font-semibold text-gray-900">
                  KES {{ formatCurrency(loan.monthly_repayment) }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Outstanding Balance</label>
                <p class="mt-1 text-sm font-semibold text-red-600">
                  KES {{ formatCurrency(loan.outstanding_balance) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Repayment Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Paid Installments</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ paidCount }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Pending</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ pendingCount }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Overdue</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ overdueCount }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Paid</p>
                  <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(totalPaid) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Repayment Schedule Table -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Repayment Schedule</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Installment
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Due Date
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Expected Amount
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Principal
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Interest
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Penalty
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Paid Amount
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Outstanding
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(repayment, index) in repayments" :key="repayment.id" :class="getRowClass(repayment)">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ index + 1 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(repayment.due_date) }}
                    <span v-if="repayment.days_late > 0" class="block text-xs text-red-500">
                      {{ repayment.days_late }} days late
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                    KES {{ formatCurrency(repayment.expected_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    KES {{ formatCurrency(repayment.principal_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    KES {{ formatCurrency(repayment.interest_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                    KES {{ formatCurrency(repayment.penalty_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                    KES {{ formatCurrency(repayment.paid_amount) }}
                    <span v-if="repayment.payment_date" class="block text-xs text-gray-500">
                      {{ formatDate(repayment.payment_date) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">
                    KES {{ formatCurrency(repayment.outstanding_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusBadgeClass(repayment.status)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ formatStatus(repayment.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <button v-if="canRecordPayment && repayment.status !== 'paid'"
                        @click="selectRepaymentForPayment(repayment)" class="text-green-600 hover:text-green-900">
                        Pay
                      </button>
                      <button v-if="repayment.transaction_id" @click="viewTransaction(repayment.transaction_id)"
                        class="text-blue-600 hover:text-blue-900">
                        View
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Recording Modal -->
    <TransitionRoot as="template" :show="showPaymentModal">
      <Dialog as="div" class="relative z-10" @close="showPaymentModal = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300"
              enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
              leave-from="opacity-100 translate-y-0 sm:scale-100"
              leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel
                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <form @submit.prevent="recordPayment">
                  <div>
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                      <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                      </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                      <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                        Record Loan Payment
                      </DialogTitle>
                      <div class="mt-2" v-if="selectedRepayment">
                        <p class="text-sm text-gray-500">
                          Installment {{ getRepaymentNumber(selectedRepayment) }} - Due: {{
            formatDate(selectedRepayment.due_date) }}
                        </p>
                        <p class="text-sm font-semibold text-gray-900">
                          Outstanding: KES {{ formatCurrency(selectedRepayment.outstanding_amount) }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="mt-4 space-y-4">
                    <div>
                      <label for="amount" class="block text-sm font-medium text-gray-700">Payment Amount *</label>
                      <input v-model="paymentForm.amount" type="number" step="0.01" id="amount" required min="0.01"
                        :max="selectedRepayment?.outstanding_amount"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                      <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method
                        *</label>
                      <select v-model="paymentForm.payment_method" id="payment_method" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select method...</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                      </select>
                    </div>

                    <div>
                      <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference
                        Number</label>
                      <input v-model="paymentForm.reference_number" type="text" id="reference_number"
                        placeholder="Receipt number, transaction ID, etc."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                      <label for="payment_date" class="block text-sm font-medium text-gray-700">Payment Date *</label>
                      <input v-model="paymentForm.payment_date" type="date" id="payment_date" required :max="today"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                      <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                      <textarea v-model="paymentForm.notes" id="notes" rows="2"
                        placeholder="Additional notes about this payment..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                  </div>

                  <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                    <button type="submit"
                      class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:col-start-2">
                      Record Payment
                    </button>
                    <button type="button"
                      class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
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
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  loan: Object,
  repayments: Array,
  auth: Object
})

// Reactive data
const showPaymentModal = ref(false)
const selectedRepayment = ref(null)

const paymentForm = reactive({
  amount: '',
  payment_method: '',
  reference_number: '',
  payment_date: new Date().toISOString().split('T')[0],
  notes: ''
})

// Computed properties
const canRecordPayment = computed(() => {
  return ['accountant', 'admin', 'management'].includes(props.auth.user.role)
})

const paidCount = computed(() => {
  return props.repayments.filter(r => r.status === 'paid').length
})

const pendingCount = computed(() => {
  return props.repayments.filter(r => r.status === 'pending').length
})

const overdueCount = computed(() => {
  return props.repayments.filter(r => r.status === 'overdue').length
})

const totalPaid = computed(() => {
  return props.repayments.reduce((sum, r) => sum + (r.paid_amount || 0), 0)
})

const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
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
    'partial': 'bg-orange-100 text-orange-800',
    'paid': 'bg-green-100 text-green-800',
    'overdue': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getRowClass = (repayment) => {
  if (repayment.status === 'paid') {
    return 'bg-green-50'
  } else if (repayment.status === 'overdue') {
    return 'bg-red-50'
  } else if (repayment.status === 'partial') {
    return 'bg-orange-50'
  }
  return ''
}

const getRepaymentNumber = (repayment) => {
  return props.repayments.findIndex(r => r.id === repayment.id) + 1
}

const selectRepaymentForPayment = (repayment) => {
  selectedRepayment.value = repayment
  paymentForm.amount = repayment.outstanding_amount
  showPaymentModal.value = true
}

const recordPayment = () => {
  const data = {
    ...paymentForm,
    repayment_id: selectedRepayment.value.id
  }

  router.post(route('loans.record-repayment', props.loan.id), data, {
    onSuccess: () => {
      showPaymentModal.value = false
      // Reset form
      paymentForm.amount = ''
      paymentForm.payment_method = ''
      paymentForm.reference_number = ''
      paymentForm.payment_date = today.value
      paymentForm.notes = ''
      selectedRepayment.value = null
    }
  })
}

const viewTransaction = (transactionId) => {
  router.visit(route('transactions.show', transactionId))
}
</script>