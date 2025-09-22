<template>
  <AppLayout>

    <Head title="New Loan Application" />
    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

        <div class="flex justify-between items-center pb-6">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            New Loan Application
          </h2>
          <Link :href="isMemberRole ? route('my-loans') : route('loans.index')"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
          Back to Loans
          </Link>

        </div>

        <!-- Success message -->
        <div v-if="successMessage" class="relative mb-4 p-3 rounded bg-green-100 text-green-800">
          {{ successMessage }}
          <button @click="successMessage = null"
            class="absolute top-2 right-2 text-green-700 hover:text-green-900 font-bold">
            ×
          </button>
        </div>

        <!-- Error messages -->
        <div v-if="errorMessages" class="relative mb-4 p-3 rounded bg-red-100 text-red-800">
          <ul>
            <li v-for="(errs, field) in errorMessages" :key="field">
              <template v-if="field === 'general'">
                {{ errs.join(', ') }}
              </template>
              <template v-else>
                <strong>{{ field }}:</strong> {{ errs.join(', ') }}
              </template>
            </li>
          </ul>
          <button @click="errorMessages = null"
            class="absolute top-2 right-2 text-red-700 hover:text-red-900 font-bold">
            ×
          </button>
        </div>


        <form @submit.prevent="submitApplication" class="space-y-6">
          <!-- Member Selection -->
          <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Member Information</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-if="!isMemberRole">
                  <label for="member_id" class="block text-sm font-medium text-gray-700">Select Member *</label>
                  <select v-model="form.member_id" @change="onMemberChange" id="member_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                    <option value="">Choose a member...</option>
                    <option v-for="member in members" :key="member.id" :value="member.id">
                      {{ member.first_name }} {{ member.last_name }} - {{ member.membership_id }}
                    </option>
                  </select>
                </div>

                <div v-if="selectedMember || isMemberRole">
                  <label class="block text-sm font-medium text-gray-700">Member Details</label>
                  <div class="mt-1 p-3 bg-gray-50 rounded-md">
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

          <!-- Loan Product Selection -->
          <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Loan Product</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 gap-6">
                <div>
                  <label for="loan_product_id" class="block text-sm font-medium text-gray-700">Select Loan Product
                    *</label>
                  <select v-model="form.loan_product_id" @change="onProductChange" id="loan_product_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                    <option value="">Choose a loan product...</option>
                    <option v-for="product in loanProducts" :key="product.id" :value="product.id">
                      {{ product.name }} - {{ product.interest_rate }}% p.a.
                    </option>
                  </select>
                </div>

                <div v-if="selectedProduct" class="bg-blue-50 p-4 rounded-md">
                  <h4 class="font-medium text-blue-900 mb-2">Product Details</h4>
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                      <span class="text-blue-600">Interest Rate:</span>
                      <p class="font-medium">{{ selectedProduct.interest_rate }}% p.a.</p>
                    </div>
                    <div>
                      <span class="text-blue-600">Amount Range:</span>
                      <p class="font-medium">KES {{ formatCurrency(selectedProduct.min_amount) }} - {{
            formatCurrency(selectedProduct.max_amount) }}</p>
                    </div>
                    <div>
                      <span class="text-blue-600">Term Range:</span>
                      <p class="font-medium">{{ selectedProduct.min_term_months }} - {{ selectedProduct.max_term_months
                        }} months</p>
                    </div>
                    <div>
                      <span class="text-blue-600">Processing Fee:</span>
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
          </div>

          <!-- Loan Details -->
          <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Loan Details</h3>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="applied_amount" class="block text-sm font-medium text-gray-700">Applied Amount (KES)
                    *</label>
                  <input v-model="form.applied_amount" @input="calculateRepayment" type="number" step="0.01"
                    id="applied_amount" required :min="selectedProduct?.min_amount" :max="selectedProduct?.max_amount"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                  <p v-if="selectedProduct" class="text-xs text-gray-500 mt-1">
                    Range: KES {{ formatCurrency(selectedProduct.min_amount) }} - {{
            formatCurrency(selectedProduct.max_amount) }}
                  </p>
                </div>

                <div>
                  <label for="term_months" class="block text-sm font-medium text-gray-700">Loan Term (Months) *</label>
                  <input v-model="form.term_months" @input="calculateRepayment" type="number" id="term_months" required
                    :min="selectedProduct?.min_term_months" :max="selectedProduct?.max_term_months"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                  <p v-if="selectedProduct" class="text-xs text-gray-500 mt-1">
                    Range: {{ selectedProduct.min_term_months }} - {{ selectedProduct.max_term_months }} months
                  </p>
                </div>

                <div class="md:col-span-2">
                  <label for="purpose" class="block text-sm font-medium text-gray-700">Loan Purpose *</label>
                  <textarea v-model="form.purpose" id="purpose" rows="3" required
                    placeholder="Please describe the purpose of this loan..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"></textarea>
                </div>
              </div>

              <!-- Loan Calculation Summary -->
              <div v-if="loanCalculation && form.applied_amount && form.term_months"
                class="mt-6 bg-green-50 p-4 rounded-md">
                <h4 class="font-medium text-green-900 mb-3">Loan Calculation Summary</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                  <div>
                    <span class="text-green-600">Monthly Repayment:</span>
                    <p class="font-bold text-lg">KES {{ formatCurrency(loanCalculation.monthlyRepayment) }}</p>
                  </div>
                  <div>
                    <span class="text-green-600">Total Repayable:</span>
                    <p class="font-medium">KES {{ formatCurrency(loanCalculation.totalRepayable) }}</p>
                  </div>
                  <div>
                    <span class="text-green-600">Processing Fee:</span>
                    <p class="font-medium">KES {{ formatCurrency(loanCalculation.processingFee) }}</p>
                  </div>
                  <div>
                    <span class="text-green-600">Insurance Fee:</span>
                    <p class="font-medium">KES {{ formatCurrency(loanCalculation.insuranceFee) }}</p>
                  </div>
                </div>
                <div class="mt-2 pt-2 border-t border-green-200">
                  <span class="text-green-600">Net Amount to Receive:</span>
                  <p class="font-bold text-lg">KES {{ formatCurrency(loanCalculation.netAmount) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Guarantors (Optional) -->
          <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Guarantors</h3>
              <p class="text-sm text-gray-500 mt-1">Add guarantors if required by the loan product</p>
            </div>
            <div class="p-6">
              <div v-for="(guarantor, index) in form.guarantors" :key="index"
                class="mb-4 p-4 border border-gray-200 rounded-md">
                <div class="flex justify-between items-center mb-3">
                  <h4 class="font-medium text-gray-900">Guarantor {{ index + 1 }}</h4>
                  <button @click="removeGuarantor(index)" type="button" class="text-red-600 hover:text-red-800 text-sm">
                    Remove
                  </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Select Member</label>
                    <select v-model="guarantor.member_id"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                      <option value="">Choose a member...</option>
                      <option v-for="member in availableGuarantors" :key="member.id" :value="member.id"
                        :disabled="isGuarantorSelected(member.id)">
                        {{ member.first_name }} {{ member.last_name }} - {{ member.membership_id }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Guaranteed Amount (KES)</label>
                    <input v-model="guarantor.guaranteed_amount" type="number" step="0.01" min="0"
                      :max="form.applied_amount"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                  </div>
                </div>
              </div>

              <button @click="addGuarantor" type="button"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Guarantor
              </button>
            </div>
          </div>

          <!-- Documents Upload -->
          <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Supporting Documents</h3>
            </div>
            <div class="p-6">
              <div
                class="border-2 border-dashed border-gray-300 rounded-md p-6 text-center cursor-pointer hover:border-blue-500"
                @click="$refs.fileInput.click()">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <div class="mt-4">
                  <span class="mt-2 block text-sm font-medium text-gray-900">
                    Upload supporting documents
                  </span>
                  <input ref="fileInput" id="documents" @change="handleFileUpload" type="file" multiple
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="hidden">
                  <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX, JPG, JPEG, PNG up to 10MB each</p>
                </div>
              </div>


              <div v-if="uploadedFiles.length > 0" class="mt-4">
                <h4 class="font-medium text-gray-900 mb-2">Uploaded Files</h4>
                <ul class="divide-y divide-gray-200">
                  <li v-for="(file, index) in uploadedFiles" :key="index"
                    class="py-2 flex items-center justify-between">
                    <div class="flex items-center">
                      <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      <span class="text-sm text-gray-900">{{ file.name }}</span>
                    </div>
                    <button @click="removeFile(index)" type="button" class="text-red-600 hover:text-red-800 text-sm">
                      Remove
                    </button>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex justify-end space-x-3">
            <Link :href="isMemberRole ? route('my-loans') : route('loans.index')"
              class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-md text-sm font-medium">
            Cancel
            </Link>
            <button type="submit" :disabled="!canSubmit || processing"
              class="bg-blue-600 hover:bg-blue-700 hover:cursor-pointer disabled:bg-gray-400 text-white px-6 py-3 rounded-md text-sm font-medium flex items-center">
              <span v-if="processing">Submitting...</span>
              <span v-else>Submit Application</span>
            </button>

          </div>
        </form>
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
  documents: []
})

const uploadedFiles = ref([])
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
  return form.member_id &&
    form.loan_product_id &&
    form.applied_amount &&
    form.term_months &&
    form.purpose.trim()
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
    if (file.size <= 10 * 1024 * 1024) { // 10MB limit
      uploadedFiles.value.push(file)
    }
  })
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
  }, 3000)
}

const submitApplication = async () => {
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

  uploadedFiles.value.forEach((file, index) => {
    formData.append(`documents[${index}]`, file)
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
