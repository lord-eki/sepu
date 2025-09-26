<template>
  <AppLayout :breadcrumbs="[{ title: 'Loan Calculator' }]">
  <div class="loan-calculator">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 px-4 py-5 sm:px-6">
      <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
          <h2 class="text-lg font-bold leading-7 text-gray-900 sm:truncate sm:text-xl">
            Loan Calculator
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Calculate your loan repayment breakdown before applying
          </p>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Calculator Form -->
        <div class="bg-white shadow-lg rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Loan Details</h3>
            <p class="text-sm text-gray-500">Enter your loan requirements</p>
          </div>
          
          <form @submit.prevent="calculateLoan" class="p-6 space-y-6">
            <!-- Loan Product Selection -->
            <div>
              <label for="loan_product_id" class="block text-sm font-medium text-gray-700 mb-2">
                Loan Product *
              </label>
              <select
                id="loan_product_id"
                v-model="form.loan_product_id"
                @change="onLoanProductChange"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option value="">Select a loan product</option>
                <option 
                  v-for="product in loanProducts" 
                  :key="product.id" 
                  :value="product.id"
                >
                  {{ product.name }} ({{ product.code }})
                </option>
              </select>
            </div>

            <!-- Loan Product Info -->
            <div v-if="selectedProduct" class="bg-blue-50 rounded-lg p-4 space-y-2">
              <h4 class="text-sm font-medium text-blue-900">Product Details</h4>
              <div class="grid grid-cols-2 gap-4 text-sm text-blue-700">
                <div>
                  <span class="font-medium">Interest Rate:</span>
                  {{ selectedProduct.interest_rate }}% p.a.
                </div>
                <div>
                  <span class="font-medium">Processing Fee:</span>
                  {{ selectedProduct.processing_fee_rate }}%
                </div>
                <div>
                  <span class="font-medium">Amount Range:</span>
                  KSh {{ formatNumber(selectedProduct.min_amount) }} - KSh {{ formatNumber(selectedProduct.max_amount) }}
                </div>
                <div>
                  <span class="font-medium">Term Range:</span>
                  {{ selectedProduct.min_term_months }} - {{ selectedProduct.max_term_months }} months
                </div>
              </div>
            </div>

            <!-- Principal Amount -->
            <div>
              <label for="principal_amount" class="block text-sm font-medium text-gray-700 mb-2">
                Loan Amount (KSh) *
              </label>
              <input
                id="principal_amount"
                type="number"
                v-model.number="form.principal_amount"
                :min="selectedProduct?.min_amount || 0"
                :max="selectedProduct?.max_amount || 999999999"
                step="100"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter loan amount"
                required
              />
              <p v-if="selectedProduct" class="mt-1 text-xs text-gray-500">
                Range: KSh {{ formatNumber(selectedProduct.min_amount) }} - KSh {{ formatNumber(selectedProduct.max_amount) }}
              </p>
            </div>

            <!-- Term in Months -->
            <div>
              <label for="term_months" class="block text-sm font-medium text-gray-700 mb-2">
                Repayment Period (Months) *
              </label>
              <input
                id="term_months"
                type="number"
                v-model.number="form.term_months"
                :min="selectedProduct?.min_term_months || 1"
                :max="selectedProduct?.max_term_months || 60"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter repayment period"
                required
              />
              <p v-if="selectedProduct" class="mt-1 text-xs text-gray-500">
                Range: {{ selectedProduct.min_term_months }} - {{ selectedProduct.max_term_months }} months
              </p>
            </div>

            <!-- Calculate Button -->
            <button
              type="submit"
              :disabled="loading || !isFormValid"
              class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium py-3 px-4 rounded-md transition duration-200 flex items-center justify-center"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'Calculating...' : 'Calculate Loan' }}
            </button>
          </form>
        </div>

        <!-- Results Panel -->
        <div class="space-y-6">
          <!-- Loan Summary -->
          <div v-if="calculation" class="bg-white shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Loan Summary</h3>
            </div>
            
            <div class="p-6">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-green-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-green-900 mb-2">Monthly Payment</h4>
                  <p class="text-2xl font-bold text-green-800">
                    KSh {{ formatNumber(calculation.loan_details.monthly_payment) }}
                  </p>
                </div>
                
                <div class="bg-blue-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-blue-900 mb-2">Total Interest</h4>
                  <p class="text-2xl font-bold text-blue-800">
                    KSh {{ formatNumber(calculation.loan_details.total_interest) }}
                  </p>
                </div>

                <div class="bg-purple-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-purple-900 mb-2">Total Repayment</h4>
                  <p class="text-xl font-bold text-purple-800">
                    KSh {{ formatNumber(calculation.loan_details.total_repayment) }}
                  </p>
                </div>

                <div class="bg-orange-50 rounded-lg p-4">
                  <h4 class="text-sm font-medium text-orange-900 mb-2">Total Cost</h4>
                  <p class="text-xl font-bold text-orange-800">
                    KSh {{ formatNumber(calculation.loan_details.total_cost_of_loan) }}
                  </p>
                </div>
              </div>

              <!-- Additional Details -->
              <div class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Breakdown</h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Principal Amount:</span>
                    <span class="font-medium">KSh {{ formatNumber(calculation.loan_details.principal_amount) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Processing Fee:</span>
                    <span class="font-medium">KSh {{ formatNumber(calculation.loan_details.processing_fee) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Insurance Fee:</span>
                    <span class="font-medium">KSh {{ formatNumber(calculation.loan_details.insurance_fee) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Effective Annual Rate:</span>
                    <span class="font-medium">{{ calculation.loan_details.effective_annual_rate }}%</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">First Payment Date:</span>
                    <span class="font-medium">{{ formatDate(calculation.summary.first_payment_date) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Last Payment Date:</span>
                    <span class="font-medium">{{ formatDate(calculation.summary.last_payment_date) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Error Display -->
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-800">{{ error }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Amortization Schedule -->
      <div v-if="calculation && showSchedule" class="mt-8 bg-white shadow-lg rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <div>
            <h3 class="text-lg font-medium text-gray-900">Repayment Schedule</h3>
            <p class="text-sm text-gray-500">Monthly payment breakdown</p>
          </div>
          <button 
            @click="showSchedule = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Principal</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr 
                v-for="payment in calculation.amortization_schedule" 
                :key="payment.payment_number"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ payment.payment_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(payment.payment_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  KSh {{ formatNumber(payment.payment_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  KSh {{ formatNumber(payment.principal_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  KSh {{ formatNumber(payment.interest_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  KSh {{ formatNumber(payment.remaining_balance) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Show Schedule Button -->
      <div v-if="calculation && !showSchedule" class="mt-6 text-center">
        <button
          @click="showSchedule = true"
          class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-200"
        >
          View Repayment Schedule
        </button>
      </div>
    </div>
  </div>
  </AppLayout>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'

export default {
  name: 'LoanCalculator',
  components: { AppLayout },
  props: {
    loanProducts: {
      type: Array,
      default: () => []
    }
  },
  
  setup(props) {
    // Reactive data
    const form = ref({
      loan_product_id: '',
      principal_amount: '',
      term_months: ''
    })

    const selectedProduct = ref(null)
    const calculation = ref(null)
    const loading = ref(false)
    const error = ref('')
    const showSchedule = ref(false)

    // Computed properties
    const isFormValid = computed(() => {
      return form.value.loan_product_id && 
             form.value.principal_amount && 
             form.value.term_months
    })

    // Methods
    const onLoanProductChange = async () => {
      if (!form.value.loan_product_id) {
        selectedProduct.value = null
        return
      }

      try {
        const response = await axios.get(`/api/loan-products/${form.value.loan_product_id}`)
        selectedProduct.value = response.data.loan_product
        
        // Clear previous calculation and errors
        calculation.value = null
        error.value = ''
        showSchedule.value = false
      } catch (err) {
        console.error(err);
        error.value = 'Error loading loan product details'
      }
    }

    const calculateLoan = async () => {
      if (!isFormValid.value) return

      loading.value = true
      error.value = ''

      try {
        const response = await axios.post('/loan-calculator/calculate', {
          loan_product_id: form.value.loan_product_id,
          principal_amount: form.value.principal_amount,
          term_months: form.value.term_months
        })

        calculation.value = response.data.calculation
        showSchedule.value = false
      } catch (err) {
        error.value = err.response?.data?.error || 'Error calculating loan'
        calculation.value = null
      } finally {
        loading.value = false
      }
    }

    const formatNumber = (number) => {
      return new Intl.NumberFormat('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(number)
    }

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-KE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    return {
      form,
      selectedProduct,
      calculation,
      loading,
      error,
      showSchedule,
      isFormValid,
      onLoanProductChange,
      calculateLoan,
      formatNumber,
      formatDate
    }
  }
}
</script>

<style scoped>
.loan-calculator {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>