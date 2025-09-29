<template>
  <AppLayout :breadcrumbs="[
    { title: 'Loans', href: isMemberRole ? route('my-loans') : route('loans.index') },
    { title: 'Apply' }
  ]">

    <Head title="New Loan Application" />

    <div class="py-8 sm:py-12 bg-white max-sm:px-4">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex justify-between items-center pb-6 border-b border-gray-200">
          <h2 class="font-bold text-lg sm:text-xl text-gray-800 tracking-tight">
            New Loan Application
          </h2>
          <Link :href="isMemberRole ? route('my-loans') : route('loans.index')"
            class="bg-blue-800 hover:bg-blue-900 text-white px-5 py-2 rounded-lg text-sm font-medium shadow transition">
          Back <span class="max-sm:hidden">to Loans</span>
          </Link>
        </div>

        <!-- Flash Messages -->
        <div class="max-w-2xl mx-auto mt-4 sm:mt-6 px-4">
          <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
            <div v-if="successMessage || errorMessages" :class="[
    successMessage
      ? 'bg-green-100 text-green-900 border border-green-300'
      : 'bg-red-100 text-red-900 border border-red-300',
    'relative w-full px-6 py-3 rounded-lg mb-4 flex items-center shadow-md'
  ]">
              <!-- Success -->
              <span v-if="successMessage" class="flex-1">{{ successMessage }}</span>
              <!-- Errors -->
              <ul v-else class="flex-1 list-disc pl-4 space-y-1">
                <li v-for="(errs, field) in errorMessages" :key="field">
                  <template v-if="field === 'general'">{{ errs.join(', ') }}</template>
                  <template v-else>
                    <strong>{{ field }}:</strong> {{ errs.join(', ') }}
                  </template>
                </li>
              </ul>
              <!-- Close -->
              <button type="button" class="ml-3 text-gray-500 hover:text-gray-700"
                @click="() => { successMessage = null; errorMessages = null }">
                ✕
              </button>
            </div>
          </transition>
        </div>

        <!-- Loan Application Form -->
        <form @submit.prevent="submitApplication" class="space-y-8 mt-6">

          <!-- Sections Wrapper -->
          <div class="space-y-8">

            <!-- Member Information -->
            <div class="bg-white shadow-md sm:rounded-lg border border-gray-100">
              <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h3 class="text-lg font-semibold text-blue-900">Member Information</h3>
              </div>
              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div v-if="!isMemberRole">
                    <label for="member_id" class="block text-sm font-medium text-gray-700">Select Member *</label>
                    <select v-model="form.member_id" @change="onMemberChange" id="member_id" required
                      class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 p-2">
                      <option value="">Choose a member...</option>
                      <option v-for="member in members" :key="member.id" :value="member.id">
                        {{ member.first_name }} {{ member.last_name }} - {{ member.membership_id }}
                      </option>
                    </select>
                  </div>

                  <div v-if="selectedMember || isMemberRole">
                    <label class="block text-sm font-medium text-gray-700">Member Details</label>
                    <div class="mt-1 p-3 bg-gray-50 rounded-lg border border-gray-200">
                      <p class="text-sm font-medium text-gray-900">
                        {{ memberInfo.first_name }} {{ memberInfo.last_name }}
                      </p>
                      <p class="text-xs text-gray-500">{{ memberInfo.membership_id }}</p>
                      <p class="text-xs text-gray-500">Phone: {{ auth.user.phone }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Loan Product -->
            <div class="bg-white shadow-md sm:rounded-lg border border-gray-100">
              <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h3 class="text-lg font-semibold text-blue-900">Loan Product</h3>
              </div>
              <div class="p-6">
                <label for="loan_product_id" class="block text-sm font-medium text-gray-700">Select Loan Product
                  *</label>
                <select v-model="form.loan_product_id" @change="onProductChange" id="loan_product_id" required
                  class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 p-2">
                  <option value="">Choose a loan product...</option>
                  <option v-for="product in loanProducts" :key="product.id" :value="product.id">
                    {{ product.name }} - {{ product.interest_rate }}% p.a.
                  </option>
                </select>

                <div v-if="selectedProduct" class="mt-4 bg-blue-50 border border-blue-200 p-4 rounded-lg">
                  <h4 class="font-semibold text-blue-900 mb-2">Product Details</h4>
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div><span class="text-blue-600">Interest Rate:</span>
                      <p class="font-medium">{{ selectedProduct.interest_rate }}% p.a.</p>
                    </div>
                    <div><span class="text-blue-600">Amount Range:</span>
                      <p class="font-medium">KES {{ formatCurrency(selectedProduct.min_amount) }} - {{
    formatCurrency(selectedProduct.max_amount) }}</p>
                    </div>
                    <div><span class="text-blue-600">Term Range:</span>
                      <p class="font-medium">{{ selectedProduct.min_term_months }} - {{ selectedProduct.max_term_months
                        }}
                        months</p>
                    </div>
                    <div><span class="text-blue-600">Processing Fee:</span>
                      <p class="font-medium">{{ selectedProduct.processing_fee_rate }}%</p>
                    </div>
                  </div>
                  <div class="mt-3">
                    <span class="text-blue-600">Description:</span>
                    <p class="text-sm text-gray-700">{{ selectedProduct.description }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Loan Details -->
            <div class="bg-white shadow-md sm:rounded-lg border border-gray-100">
              <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h3 class="text-lg font-semibold text-blue-900">Loan Details</h3>
              </div>
              <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="amount" class="block text-sm font-medium text-gray-700">Loan Amount *</label>
                  <input v-model="form.applied_amount" type="number" id="amount" required
                    class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 p-2" />
                </div>
                <div>
                  <label for="term" class="block text-sm font-medium text-gray-700">Repayment Term (months) *</label>
                  <input v-model="form.term_months" type="number" id="term" required
                    class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 p-2" />
                </div>
                <div class="md:col-span-2">
                  <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose *</label>
                  <textarea v-model="form.purpose" id="purpose" rows="3" required
                    class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 p-2"></textarea>
                </div>
              </div>
            </div>

            <!-- Disbursement Section -->
            <div class="bg-white shadow-md sm:rounded-lg border border-gray-100">
              <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h3 class="text-lg font-semibold text-blue-900">Disbursement Method</h3>
              </div>
              <div class="p-6 space-y-4">
                <!-- Method Select -->
                <div>
                  <label for="disbursement" class="block text-sm font-medium text-gray-700">
                    Choose Method *
                  </label>
                  <select id="disbursement" v-model="disbursementMethod"
                    class="mt-1 block w-full rounded-md p-2 border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required>
                    <option value="">-- Select Method --</option>
                    <option value="mpesa">M-Pesa</option>
                    <option value="bank">Bank</option>
                    <option value="cheque">Cheque</option>
                  </select>
                </div>

                <!-- Conditional Inputs -->
                <div v-if="disbursementMethod === 'mpesa'" class="space-y-3">
                  <label for="mpesaNumber" class="block text-sm font-medium text-gray-700">
                    M-Pesa Number *
                  </label>
                  <input id="mpesaNumber" type="text" v-model="disbursementDetails.mpesaNumber"
                    placeholder="e.g. 0712345678"
                    class="mt-1 block w-full rounded-md p-2 border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required />
                </div>

                <div v-if="disbursementMethod === 'bank'" class="space-y-3">
                  <label for="bankName" class="block text-sm font-medium text-gray-700">
                    Bank Name *
                  </label>
                  <input id="bankName" type="text" v-model="disbursementDetails.bankName" placeholder="e.g. Equity Bank"
                    class="mt-1 block w-full rounded-md p-2 border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required />

                  <label for="branch" class="block text-sm font-medium text-gray-700">
                    Branch *
                  </label>
                  <input id="branch" type="text" v-model="disbursementDetails.branch" placeholder="e.g. Nairobi CBD"
                    class="mt-1 block w-full rounded-md p-2 border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required />

                  <label for="accountNumber" class="block text-sm font-medium text-gray-700">
                    Account Number *
                  </label>
                  <input id="accountNumber" type="text" v-model="disbursementDetails.accountNumber"
                    placeholder="e.g. 123456789"
                    class="mt-1 block w-full rounded-md p-2 border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required />
                </div>

                <div v-if="disbursementMethod === 'cheque'" class="space-y-3">
                  <label for="payee" class="block text-sm font-medium text-gray-700">
                    Payee Name *
                  </label>
                  <input id="payee" type="text" v-model="disbursementDetails.payee" placeholder="Enter Payee Name"
                    class="mt-1 block w-full rounded-md p-2 border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    required />
                </div>
              </div>
            </div>


            <!-- Guarantors -->
            <div class="bg-white shadow-md sm:rounded-lg border border-gray-100">
              <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h3 class="text-lg font-semibold text-blue-900">
                  Guarantors
                  <span class="font-normal text-base text-gray-700">(Optional)</span>
                </h3>
              </div>
              <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600 mb-4">
                  Select guarantors from members.
                  <span class="text-blue-900 font-medium">
                    The selected guarantors will receive a notification about this loan application.
                  </span>
                </p>

                <div v-for="(guarantor, index) in form.guarantors" :key="index" class="p-4 border rounded-lg space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Guarantor Select -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Select Member *</label>
                      <select v-model="guarantor.member_id" class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm 
                              focus:border-orange-500 focus:ring-orange-500 p-2">
                        <option value="">Choose a member...</option>
                        <option v-for="member in members" :key="member.id" :value="member.id">
                          {{ member.first_name }} {{ member.last_name }} - {{ member.membership_id }}
                        </option>
                      </select>
                    </div>

                    <!-- Guaranteed Amount -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        Guaranteed Amount (KES) *
                      </label>
                      <input v-model="guarantor.guaranteed_amount" type="number" step="0.01" min="0"
                        :max="form.applied_amount" class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm 
                                    focus:border-orange-500 focus:ring-orange-500 p-2" />
                    </div>
                  </div>

                  <!-- Remove Button -->
                  <div class="flex justify-end">
                    <button type="button" @click="removeGuarantor(index)"
                      class="text-red-600 hover:text-red-800 text-sm font-medium">
                      ✕ Remove Guarantor
                    </button>
                  </div>
                </div>

                <!-- Add Guarantor -->
                <button type="button" @click="addGuarantor" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg 
                        text-sm font-medium shadow">
                  + Add Guarantor
                </button>
              </div>
            </div>


            <!-- Support Documents -->
            <div class="bg-white shadow-md sm:rounded-lg border border-gray-100">
              <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <h3 class="text-lg font-semibold text-blue-900">Support Documents</h3>
              </div>
              <div class="p-6 space-y-5">
                <div v-for="doc in requiredDocuments" :key="doc.key"
                  class="flex flex-col sm:flex-row sm:items-center sm:space-x-4">
                  <label :for="doc.key" class="block text-sm font-medium text-gray-700 w-40">
                    {{ doc.label }} *
                  </label>

                  <div class="flex items-center space-x-3">

                    <label :for="doc.key"
                      class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 max-sm:text-sm rounded-lg shadow cursor-pointer transition">
                      Choose File
                    </label>
                    <input type="file" :id="doc.key" :name="doc.key" class="hidden"
                      @change="handleFileChange($event, doc.key)" required />

                    <!-- Filename -->
                    <span v-if="uploadedFiles[doc.key]" class="max-sm:text-xs sm:text-sm text-gray-700">
                      {{ uploadedFiles[doc.key].name }}
                    </span>

                    <span v-else class="text-sm text-gray-400 italic">
                      No file chosen
                    </span>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- Submit -->
          <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
            <Link :href="isMemberRole ? route('my-loans') : route('loans.index')"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg text-sm font-medium shadow">
            Cancel
            </Link>
            <button type="submit" :disabled="!canSubmit || processing"
              class="bg-blue-900 hover:bg-blue-800 hover:cursor-pointer disabled:bg-gray-400 text-white px-6 py-3 rounded-lg text-sm font-medium flex items-center shadow transition">
              <span v-if="processing">Submitting...</span>
              <span v-else>Submit Application</span>
            </button>
          </div>

        </form>

        <!-- Confirmation Modal -->
        <div v-if="showConfirm"
          class="fixed inset-0 bg-[rgba(0,0,0,0.7)] max-sm:px-5 flex items-center justify-center z-50">
          <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full p-6 overflow-y-auto max-h-[90vh]">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">Review Your Loan Application</h3>

            <!-- Summary Section -->
            <div class="space-y-4 text-sm text-gray-700">
              <div>
                <span class="font-medium text-gray-900">Member:</span>
                {{ memberInfo.first_name }} {{ memberInfo.last_name }}
                <span class="text-xs text-gray-500">({{ memberInfo.membership_id }})</span>
              </div>


              <div>
                <span class="font-medium text-gray-900">Loan Product:</span>
                {{ loanProducts.find(p => p.id === form.loan_product_id)?.name }}
              </div>

              <div>
                <span class="font-medium text-gray-900">Applied Amount:</span>
                KES {{ form.applied_amount }}
              </div>

              <div>
                <span class="font-medium text-gray-900">Term:</span>
                {{ form.term_months }} months
              </div>

              <div>
                <span class="font-medium text-gray-900">Purpose:</span>
                {{ form.purpose }}
              </div>

              <!-- Guarantors -->
              <div v-if="form.guarantors.length">
                <span class="font-medium text-gray-900">Guarantors:</span>
                <ul class="list-disc ml-6 mt-1">
                  <li v-for="(g, index) in form.guarantors" :key="index">
                    {{ members.find(m => m.id === g.member_id)?.first_name }}
                    {{ members.find(m => m.id === g.member_id)?.last_name }}
                    — Guaranteed: KES {{ g.guaranteed_amount }}
                  </li>
                </ul>
              </div>

              <!-- Disbursement -->
              <div>
                <span class="font-medium text-gray-900">Disbursement Method:</span>
                {{ disbursementMethod }}
                <div class="ml-4 mt-1">
                  <div v-if="disbursementMethod === 'mpesa'">
                    M-Pesa Number: {{ disbursementDetails.mpesaNumber }}
                  </div>
                  <div v-if="disbursementMethod === 'bank'">
                    Bank: {{ disbursementDetails.bankName }},
                    Branch: {{ disbursementDetails.branch }},
                    Account: {{ disbursementDetails.accountNumber }}
                  </div>
                  <div v-if="disbursementMethod === 'cheque'">
                    Payee: {{ disbursementDetails.payee }}
                  </div>
                </div>
              </div>

              <!-- Documents -->
              <div>
                <span class="font-medium text-gray-900">Attached Documents:</span>
                <ul class="list-disc ml-6 mt-1 max-sm:text-xs">
                  <li v-for="doc in requiredDocuments" :key="doc.key">
                    {{ doc.label }} —
                    <span v-if="uploadedFiles[doc.key]">{{ uploadedFiles[doc.key].name }}</span>
                    <span v-else class="italic text-gray-400">Not uploaded</span>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="showConfirm = false"
                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button type="button" @click="confirmSubmit"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow">
                Yes, Submit
              </button>
            </div>
          </div>
        </div>

        <!-- Fullscreen Loader -->
        <div v-if="processing" class="fixed inset-0 bg-[rgba(0,0,0,0.8)] flex items-center justify-center z-50">
          <div class="bg-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3">
            <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span class="text-gray-800 font-medium">Submitting your loan application...</span>
          </div>
        </div>


      </div>
    </div>
  </AppLayout>
</template>



<script setup>
import { ref, reactive, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import axios from 'axios'

const successMessage = ref(null)
const errorMessages = ref(null)
const processing = ref(false)

const props = defineProps({
  loanProducts: Array,
  members: Array,
  auth: Object
})



// Reactive data
const form = reactive({
  member_id: '',
  loan_product_id: '',
  applied_amount: '',
  term_months: '',
  purpose: '',
  guarantors: [],
  documents: [],
  disbursement_method: '',
  bank_account: '',
  bank_name: '',
  mpesa_number: '',
})

const disbursementMethod = ref('')
const disbursementDetails = reactive({
  mpesaNumber: '',
  bankName: '',
  branch: '',
  accountNumber: '',
  payee: ''
})


const selectedMember = ref(null)
const selectedProduct = ref(null)
const loanCalculation = ref(null)

// Computed properties
const isMemberRole = computed(() => props.auth.user.role === 'member')

const memberInfo = computed(() => {
  if (isMemberRole.value) {
    return props.auth.user.member
  }
  return selectedMember.value || {}
})

const availableGuarantors = computed(() => {
  return props.members.filter(member =>
    member.id !== form.member_id &&
    member.membership_status === 'active'
  )
})

const canSubmit = computed(() => {
  const hasBasics = form.member_id &&
    form.loan_product_id &&
    form.applied_amount &&
    form.term_months &&
    form.purpose.trim()

  // ensure disbursement details are filled
  let hasDisbursement = false
  if (disbursementMethod.value === 'mpesa') {
    hasDisbursement = !!disbursementDetails.mpesaNumber
  } else if (disbursementMethod.value === 'bank') {
    hasDisbursement =
      !!disbursementDetails.bankName &&
      !!disbursementDetails.branch &&
      !!disbursementDetails.accountNumber
  } else if (disbursementMethod.value === 'cheque') {
    hasDisbursement = !!disbursementDetails.payee
  }

  // ensure all required docs are uploaded
  const hasDocs = requiredDocuments.every(doc => uploadedFiles[doc.key])

  return hasBasics && hasDisbursement && hasDocs
})


// Initialize member for member role
if (isMemberRole.value && props.auth.user.member) {
  form.member_id = props.auth.user.member.id
  selectedMember.value = props.auth.user.member
}

// Reset form after success
const resetForm = () => {
  form.member_id = isMemberRole.value ? props.auth.user.member.id : ''
  form.loan_product_id = ''
  form.applied_amount = ''
  form.term_months = ''
  form.purpose = ''
  form.guarantors = []
  form.documents = []
  uploadedFiles.value = []
  selectedMember.value = isMemberRole.value ? props.auth.user.member : null
  selectedProduct.value = null
  loanCalculation.value = null
  form.disbursement_method = ''
  form.bank_account = ''
  form.bank_name = ''
  form.mpesa_number = ''

}

// Helpers
const onMemberChange = () => {
  selectedMember.value = props.members.find(m => m.id == form.member_id)
}

const onProductChange = () => {
  selectedProduct.value = props.loanProducts.find(p => p.id == form.loan_product_id)
  calculateRepayment()
}

const calculateRepayment = () => {
  if (!selectedProduct.value || !form.applied_amount || !form.term_months) {
    loanCalculation.value = null
    return
  }

  const principal = parseFloat(form.applied_amount)
  const monthlyRate = selectedProduct.value.interest_rate / 100 / 12
  const months = parseInt(form.term_months)

  let monthlyRepayment
  if (monthlyRate === 0) {
    monthlyRepayment = principal / months
  } else {
    monthlyRepayment = principal * (monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1)
  }

  const totalRepayable = monthlyRepayment * months
  const processingFee = principal * (selectedProduct.value.processing_fee_rate / 100)
  const insuranceFee = principal * (selectedProduct.value.insurance_rate / 100)
  const netAmount = principal - processingFee - insuranceFee

  loanCalculation.value = {
    monthlyRepayment,
    totalRepayable,
    processingFee,
    insuranceFee,
    netAmount
  }
}

const addGuarantor = () => {
  form.guarantors.push({
    member_id: '',
    guaranteed_amount: ''
  })
}

const removeGuarantor = (index) => {
  form.guarantors.splice(index, 1)
}

const isGuarantorSelected = (memberId) => {
  return form.guarantors.some(g => g.member_id == memberId)
}

const handleFileUpload = (event) => {
  const files = Array.from(event.target.files)
  files.forEach(file => {
    if (file.size <= 10 * 1024 * 1024) {
      uploadedFiles.value.push(file)
    }
  })
}


// list of required docs
const requiredDocuments = [
  { key: 'payslip', label: 'Payslip (latest)' },
  { key: 'id_copy', label: 'ID/Passport Copy' },
  { key: 'bank_statement', label: 'Bank Statement (6 months)' },
  { key: 'employment_letter', label: 'Employment Letter' },
  { key: 'guarantor_form', label: 'Guarantor Form' }
]

// store filenames per doc
const uploadedFiles = reactive({})

function handleFileChange(e, key) {
  const input = e.target
  if (input.files && input.files.length > 0) {
    uploadedFiles[key] = input.files[0]
  } else {
    uploadedFiles[key] = null
  }
}

const removeFile = (index) => {
  uploadedFiles.value.splice(index, 1)
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const showMessage = (type, message, errors = null, callback = null) => {
  window.scrollTo({ top: 0, behavior: 'smooth' })

  if (type === 'success') {
    successMessage.value = message
    errorMessages.value = null
  } else {
    errorMessages.value = errors || { general: [message] }
    successMessage.value = null
  }

  setTimeout(() => {
    successMessage.value = null
    errorMessages.value = null

    if (callback) callback()
  }, 5000)
}

const showConfirm = ref(false)

const submitApplication = () => {
  showConfirm.value = true
}

const confirmSubmit = async () => {
  showConfirm.value = false
  processing.value = true
  const formData = new FormData()

  Object.keys(form).forEach(key => {
    if (key !== 'guarantors' && key !== 'documents') {
      formData.append(key, form[key] ?? "")
    }
  })

  form.guarantors.forEach((guarantor, index) => {
    Object.keys(guarantor).forEach(key => {
      formData.append(`guarantors[${index}][${key}]`, guarantor[key] ?? "")
    })
  })

  Object.entries(uploadedFiles).forEach(([key, file]) => {
    if (file) {
      formData.append(`documents[${key}]`, file)
    }
  })

  try {
    const response = await axios.post(route('loans.store'), formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (response.data?.success) {
      resetForm()
      showMessage('success', response.data.message, null, () => {

        if (isMemberRole.value) {
          router.visit(route('my-loans'))
        } else {
          router.visit(route('loans.index'))
        }
      })
    }
  } catch (error) {
    if (error.response?.status === 422) {
      const data = error.response.data
      if (data.errors) {
        showMessage('error', null, data.errors)
      } else if (data.message) {
        showMessage('error', data.message)
      }
    } else {
      console.error("Unexpected error:", error)
      showMessage('error', "Something went wrong. Please try again.")
    }
  } finally {
    processing.value = false
  }
}
</script>
