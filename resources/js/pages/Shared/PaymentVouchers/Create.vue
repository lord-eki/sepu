<template>
  <Head title="Create Payment Voucher" />
  
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create Payment Voucher
          </h2>
          <p class="text-sm text-gray-600 mt-1">Create a new payment voucher for approval</p>
        </div>
        <Link
          :href="route('vouchers.index')"
          class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center"
        >
          <ArrowBigLeft class="h-5 w-5 mr-2" />
          Back to Vouchers
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Basic Information -->
          <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
              <p class="text-sm text-gray-600">Enter the basic details of the payment voucher</p>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Voucher Type <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.voucher_type"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                    :class="{ 'border-red-300': $page.props.errors.voucher_type }"
                    @change="handleVoucherTypeChange"
                  >
                    <option value="">Select Voucher Type</option>
                    <option v-for="(label, value) in voucherTypes" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </select>
                  <p v-if="$page.props.errors.voucher_type" class="mt-2 text-sm text-red-600">{{ $page.props.errors.voucher_type }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Amount <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input
                      v-model="form.amount"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="0.00"
                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 pl-12 p-2"
                      :class="{ 'border-red-300': $page.props.errors.amount }"
                    />
                  </div>
                  <p v-if="$page.props.errors.amount" class="mt-2 text-sm text-red-600">{{ $page.props.errors.amount }}</p>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Purpose <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.purpose"
                  type="text"
                  placeholder="Enter the purpose of this payment"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                  :class="{ 'border-red-300': $page.props.errors.purpose }"
                />
                <p v-if="$page.props.errors.purpose" class="mt-2 text-sm text-red-600">{{ $page.props.errors.purpose }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Description
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  placeholder="Additional details or notes about this payment"
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                  :class="{ 'border-red-300': $page.props.errors.description }"
                ></textarea>
                <p v-if="$page.props.errors.description" class="mt-2 text-sm text-red-600">{{ $page.props.errors.description }}</p>
              </div>
            </div>
          </div>

          <!-- Payee Information -->
          <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Payee Information</h3>
              <p class="text-sm text-gray-600">Details of the person or organization to be paid</p>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payee Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.payee_name"
                    type="text"
                    placeholder="Enter payee name"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                    :class="{ 'border-red-300': $page.props.errors.payee_name }"
                  />
                  <p v-if="$page.props.errors.payee_name" class="mt-2 text-sm text-red-600">{{ $page.props.errors.payee_name }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number
                  </label>
                  <input
                    v-model="form.payee_phone"
                    type="text"
                    placeholder="e.g., +254712345678"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                    :class="{ 'border-red-300': $page.props.errors.payee_phone }"
                  />
                  <p v-if="$page.props.errors.payee_phone" class="mt-2 text-sm text-red-600">{{ $page.props.errors.payee_phone }}</p>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Account Details
                </label>
                <input
                  v-model="form.payee_account"
                  type="text"
                  placeholder="Bank account number, mobile money number, etc."
                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                  :class="{ 'border-red-300': $page.props.errors.payee_account }"
                />
                <p v-if="$page.props.errors.payee_account" class="mt-2 text-sm text-red-600">{{ $page.props.errors.payee_account }}</p>
              </div>
            </div>
          </div>

          <!-- Budget & Loan Information -->
          <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Budget & Loan Information</h3>
              <p class="text-sm text-gray-600">Link this voucher to a budget item or loan</p>
            </div>
            <div class="p-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Budget Item
                  </label>
                  <select
                    v-model="form.budget_item_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                    :class="{ 'border-red-300': $page.props.errors.budget_item_id }"
                    :disabled="form.voucher_type === 'loan_disbursement'"
                  >
                    <option value="">Select Budget Item (Optional)</option>
                    <option v-for="item in budgetItems" :key="item.id" :value="item.id">
                      {{ item.item_name }} - Available: {{ formatCurrency(item.remaining_amount) }}
                    </option>
                  </select>
                  <p v-if="$page.props.errors.budget_item_id" class="mt-2 text-sm text-red-600">{{ $page.props.errors.budget_item_id }}</p>
                  <p v-if="selectedBudgetItem" class="mt-2 text-sm text-gray-600">
                    Budget: {{ selectedBudgetItem.budget?.title }} | 
                    Available: {{ formatCurrency(selectedBudgetItem.remaining_amount) }}
                  </p>
                </div>

                <div v-if="form.voucher_type === 'loan_disbursement'">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Loan <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.loan_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2"
                    :class="{ 'border-red-300': $page.props.errors.loan_id }"
                  >
                    <option value="">Select Loan</option>
                    <option v-for="loan in pendingLoans" :key="loan.id" :value="loan.id">
                      {{ loan.member?.first_name }} {{ loan.member?.last_name }} - {{ loan.loan_number }} 
                      ({{ formatCurrency(loan.approved_amount) }})
                    </option>
                  </select>
                  <p v-if="$page.props.errors.loan_id" class="mt-2 text-sm text-red-600">{{ $page.props.errors.loan_id }}</p>
                  <p v-if="selectedLoan" class="mt-2 text-sm text-gray-600">
                    Loan Amount: {{ formatCurrency(selectedLoan.approved_amount) }} | 
                    Member: {{ selectedLoan.member?.first_name }} {{ selectedLoan.member?.last_name }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Supporting Documents -->
          <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Supporting Documents</h3>
              <p class="text-sm text-gray-600">Upload any supporting documents for this voucher</p>
            </div>
            <div class="p-6">
              <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                <div class="text-center">
                  <File class="mx-auto h-12 w-12 text-gray-400" />
                  <div class="mt-4">
                    <label class="cursor-pointer">
                      <span class="mt-2 block text-sm font-medium text-gray-900">
                        Upload supporting documents
                      </span>
                      <span class="mt-1 block text-sm text-gray-600">
                        PDF, DOC, DOCX, JPG, JPEG, PNG up to 5MB each
                      </span>
                      <input
                        ref="fileInput"
                        type="file"
                        multiple
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        class="hidden"
                        @change="handleFileUpload"
                      />
                      <span class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Choose Files
                      </span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- File List -->
              <div v-if="form.supporting_documents && form.supporting_documents.length > 0" class="mt-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Selected Files</h4>
                <div class="space-y-2">
                  <div
                    v-for="(file, index) in form.supporting_documents"
                    :key="index"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                  >
                    <div class="flex items-center">
                      <File class="h-5 w-5 text-gray-400 mr-3" />
                      <div>
                        <p class="text-sm font-medium text-gray-900">{{ file.name }}</p>
                        <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                      </div>
                    </div>
                    <button
                      type="button"
                      @click="removeFile(index)"
                      class="text-red-500 hover:text-red-700"
                    >
                      <Cross class="h-5 w-5" />
                    </button>
                  </div>
                </div>
              </div>

              <p v-if="$page.props.errors.supporting_documents" class="mt-2 text-sm text-red-600">{{ $page.props.errors.supporting_documents }}</p>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4">
              <div class="flex justify-end space-x-4">
                <Link
                  :href="route('vouchers.index')"
                  class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Cancel
                </Link>
                <button
                  type="submit"
                  :disabled="processing"
                  class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="processing" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Creating...
                  </span>
                  <span v-else>Create Voucher</span>
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>


<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ArrowBigLeft, Cross, File } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  budgetItems: Array,
  pendingLoans: Array,
  voucherTypes: Object
})

const fileInput = ref(null)

// Initialize the form with useForm
const form = useForm({
  voucher_type: '',
  payee_name: '',
  payee_phone: '',
  payee_account: '',
  amount: '',
  purpose: '',
  description: '',
  budget_item_id: '',
  loan_id: '',
  supporting_documents: []
})

const selectedBudgetItem = computed(() => {
  if (!form.budget_item_id) return null
  return props.budgetItems.find(item => item.id === parseInt(form.budget_item_id))
})

const selectedLoan = computed(() => {
  if (!form.loan_id) return null
  return props.pendingLoans.find(loan => loan.id === parseInt(form.loan_id))
})

const handleVoucherTypeChange = () => {
  // Clear related fields when voucher type changes
  if (form.voucher_type !== 'loan_disbursement') {
    form.loan_id = ''
  }
  
  // Auto-populate amount and payee for loan disbursement
  if (form.voucher_type === 'loan_disbursement' && form.loan_id) {
    const loan = selectedLoan.value
    if (loan) {
      form.amount = loan.approved_amount
      form.payee_name = `${loan.member?.first_name} ${loan.member?.last_name}`
      form.payee_phone = loan.member?.phone
    }
  }
}

// Watch for loan selection changes
watch(() => form.loan_id, (newLoanId) => {
  if (newLoanId && form.voucher_type === 'loan_disbursement') {
    const loan = selectedLoan.value
    if (loan) {
      form.amount = loan.approved_amount
      form.payee_name = `${loan.member?.first_name} ${loan.member?.last_name}`
      form.payee_phone = loan.member?.phone
      form.purpose = `Loan disbursement for ${loan.loan_number}`
    }
  }
})

const handleFileUpload = (event) => {
  const files = Array.from(event.target.files)
  const validFiles = []
  
  files.forEach(file => {
    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
      alert(`File ${file.name} is too large. Maximum size is 5MB.`)
      return
    }
    
    // Validate file type
    const allowedTypes = [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'image/jpeg',
      'image/jpg',
      'image/png'
    ]
    
    if (!allowedTypes.includes(file.type)) {
      alert(`File ${file.name} has an invalid type. Only PDF, DOC, DOCX, JPG, JPEG, PNG are allowed.`)
      return
    }
    
    validFiles.push(file)
  })
  
  if (validFiles.length > 0) {
    form.supporting_documents = [...(form.supporting_documents || []), ...validFiles]
  }
  
  // Clear the input
  event.target.value = ''
}

const removeFile = (index) => {
  form.supporting_documents.splice(index, 1)
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount)
}

const submit = () => {
  form.post(route('vouchers.store'), {
    forceFormData: true,
    onSuccess: () => {
      // Form will redirect on success
      console.log('Voucher created successfully')
    },
    onError: (errors) => {
      console.log('Validation errors:', errors)
    }
  })
}
</script>