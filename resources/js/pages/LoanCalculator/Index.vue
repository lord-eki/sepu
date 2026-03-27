<template>
   <AppLayout :breadcrumbs="[
    { title: 'Loans', href: route('loans.index') },
    { title: 'Loan Calculator' }
  ]">

    <Head title="Calculator" />
    <div class="loan-calculator max-sm:px-3">
      <!-- Page Header -->
      <div class="bg-gradient-to-r from-orange-500 to-blue-900 shadow-md mt-2 sm:mx-6 rounded-xl px-6 py-5 sm:px-6">
        <div class="md:flex md:items-center md:justify-between">
          <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-white">Loan Calculator</h2>
            <p class="mt-1 text-sm text-gray-100">Calculate your loan repayment breakdown before applying</p>
          </div>
          <div v-if="isAdmin" class="mt-3 md:mt-0 self-end">
            <span class="inline-flex items-center text-white text-xs font-semibold px-3 py-1 rounded">Viewing as
              Admin</span>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Tabs -->
        <div class="bg-white shadow rounded-2xl border border-gray-200 mb-6">
          <div class="px-4 py-3 border-b border-gray-100 flex gap-3 items-center">
            <button
              :class="['px-3 py-2 rounded-md text-sm font-medium', activeTab === 'calculator' ? 'bg-blue-50 text-[#0a2342]' : 'text-gray-600 hover:bg-gray-50']"
              @click="activeTab = 'calculator'">
              Calculator
            </button>
            <button v-if="isAdmin"
              :class="['px-3 py-2 rounded-md text-sm font-medium', activeTab === 'setup' ? 'bg-orange-50 text-orange-700' : 'text-gray-600 hover:bg-gray-50']"
              @click="activeTab = 'setup'">
              Setup
            </button>
          </div>

          <div class="p-6">
            <!-- SETUP TAB (admin only) -->
            <div v-if="activeTab === 'setup' && isAdmin">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Scope Selection -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                  <h4 class="text-sm font-semibold text-[#0a2342] mb-3">Edit Scope</h4>
                  <p class="text-sm text-gray-600 mb-4">Choose whether to edit global defaults or a specific product.
                  </p>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm text-gray-700 mb-2">Scope</label>
                      <select v-model="setupScope" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="global">Global Defaults</option>
                        <option value="product">Specific Loan Product</option>
                      </select>
                    </div>
                    <div v-if="setupScope === 'product'">
                      <label class="block text-sm text-gray-700 mb-2">Select Product</label>
                      <select v-model="adminForm.product_id" @change="onAdminProductChange"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">-- Select product to edit --</option>
                        <option v-for="p in loanProducts" :key="p.id" :value="p.id">{{ p.name }} ({{ p.code }})</option>
                      </select>
                    </div>
                    <div class="pt-4 border-t">
                      <button @click="resetAdminForm" type="button" class="text-sm text-gray-600 hover:underline">Reset
                        form</button>
                    </div>
                  </div>
                </div>

                <!-- Setup Form -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                  <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-semibold text-[#0a2342]">Loan Setup & Parameters</h4>
                    <span v-if="setupSaved" class="text-green-600 text-sm font-medium">✔ Saved</span>
                  </div>
                  <form @submit.prevent="saveSetup" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Interest Rate (% / month)</label>
                      <input v-model.number="adminForm.interest_rate" type="number" step="0.1"
                        class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Processing Fee (%)</label>
                      <input v-model.number="adminForm.processing_fee_rate" type="number" step="0.1"
                        class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Insurance Fee (%)</label>
                      <input v-model.number="adminForm.insurance_rate" type="number" step="0.1"
                        class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Processing Fee Flat (optional)</label>
                      <input v-model.number="adminForm.processing_fee_flat" type="number" step="1"
                        class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Min Amount (KSh)</label>
                      <input v-model.number="adminForm.min_amount" type="number" step="100"
                        class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Max Amount (KSh)</label>
                      <input v-model.number="adminForm.max_amount" type="number" step="100"
                        class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Min Term (Months)</label>
                      <input v-model.number="adminForm.min_term_months" type="number"
                        class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Max Term (Months)</label>
                      <input v-model.number="adminForm.max_term_months" type="number"
                        class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div class="sm:col-span-2 flex justify-end gap-2 mt-2">
                      <button type="button" @click="loadDefaults" class="px-4 py-2 border rounded text-sm">Load current
                        defaults</button>
                      <button type="submit" :disabled="savingSetup"
                        class="px-5 py-2 bg-orange-500 text-white rounded text-sm flex items-center">
                        <svg v-if="savingSetup" class="animate-spin -ml-1 mr-2 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                          </circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                          </path>
                        </svg>
                        {{ savingSetup ? 'Saving...' : (setupScope === 'global' ? 'Save Global Defaults' :
    'UpdateProduct') }}
                      </button>
                    </div>
                  </form>
                  <p v-if="setupError" class="mt-3 text-sm text-red-600">{{ setupError }}</p>
                </div>
              </div>
            </div>

            <!-- CALCULATOR TAB -->
            <div v-if="activeTab === 'calculator'">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Calculator Form -->
                <div class="bg-white shadow-lg rounded-2xl border border-gray-200">
                  <div class="px-6 py-4 border-b border-gray-100 bg-blue-50 rounded-t-2xl">
                    <h3 class="text-lg font-semibold text-[#0a2342]">Loan Details</h3>
                    <p class="text-sm text-gray-600">Enter your loan requirements</p>
                  </div>

                  <form @submit.prevent="calculateLoan" class="p-6 space-y-6">
                    <!-- Loan Product -->
                    <div>
                      <label for="loan_product_id" class="block text-sm font-medium text-gray-700 mb-2">Loan Product
                        *</label>
                      <select id="loan_product_id" v-model="form.loan_product_id" @change="onLoanProductChange"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-orange-500"
                        required>
                        <option value="">Select a loan product</option>
                        <option v-for="product in loanProducts" :key="product.id" :value="product.id">
                          {{ product.name }} ({{ product.code }})
                        </option>
                      </select>
                    </div>

                    <!-- Product Info -->
                    <div v-if="selectedProduct" class="bg-blue-50 rounded-lg p-4 space-y-2">
                      <h4 class="text-sm font-semibold text-[#0a2342]">Product Details</h4>
                      <div class="grid grid-cols-2 gap-4 text-sm text-blue-700">
                        <div><span class="font-medium">Interest Rate:</span> {{ selectedProduct.interest_rate }}% p.m.
                        </div>
                        <div><span class="font-medium">Processing Fee:</span> {{ selectedProduct.processing_fee_rate }}%
                        </div>
                        <div>
                          <span class="font-medium">Amount Range:</span>
                          KSh {{ formatNumber(selectedProduct.min_amount) }} – KSh {{
    formatNumber(selectedProduct.max_amount) }}
                        </div>
                        <div>
                          <span class="font-medium">Term Range:</span>
                          {{ selectedProduct.min_term_months }} – {{ selectedProduct.max_term_months }} months
                        </div>
                        <div v-if="selectedProduct.grace_period_days">
                          <span class="font-medium">Grace Period:</span> {{ selectedProduct.grace_period_days }} days
                        </div>
                      </div>
                    </div>

                    <!-- Principal -->
                    <div>
                      <label for="principal_amount" class="block text-sm font-medium text-gray-700 mb-2">Loan Amount
                        (KSh) *</label>
                      <input id="principal_amount" type="number" v-model.number="form.principal_amount"
                        :min="selectedProduct?.min_amount || 0" :max="selectedProduct?.max_amount || 999999999"
                        step="100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-orange-500"
                        placeholder="Enter loan amount" required />
                      <p v-if="selectedProduct" class="mt-1 text-xs text-gray-500">
                        Range: KSh {{ formatNumber(selectedProduct.min_amount) }} – KSh {{
    formatNumber(selectedProduct.max_amount) }}
                      </p>
                    </div>

                    <!-- Term -->
                    <div>
                      <label for="term_months" class="block text-sm font-medium text-gray-700 mb-2">Repayment Period
                        (Months) *</label>
                      <input id="term_months" type="number" v-model.number="form.term_months"
                        :min="selectedProduct?.min_term_months || 1" :max="selectedProduct?.max_term_months || 60"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-orange-500"
                        placeholder="Enter repayment period" required />
                      <p v-if="selectedProduct" class="mt-1 text-xs text-gray-500">
                        Range: {{ selectedProduct.min_term_months }} – {{ selectedProduct.max_term_months }} months
                      </p>
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="loading || !isFormValid"
                      class="w-full bg-[#0a2342] hover:bg-blue-800 hover:cursor-pointer disabled:bg-gray-400 text-white font-semibold py-3 px-4 rounded-md transition duration-200 flex items-center justify-center">
                      <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                      </svg>
                      {{ loading ? 'Calculating...' : 'Calculate Loan' }}
                    </button>
                  </form>
                </div>

                <!-- Results Panel -->
                <div class="space-y-6">
                  <div v-if="calculation" id="loan-summary"
                    class="bg-white shadow-lg rounded-2xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 bg-blue-50 rounded-t-2xl">
                      <h3 class="text-lg font-semibold text-[#0a2342]">Loan Summary</h3>
                      <p class="text-xs text-gray-500 mt-1">
                        {{ calculation.loan_details.term_months }} months ·
                        {{ calculation.loan_details.monthly_rate ?? calculation.loan_product.interest_rate }}% p.m.
                        ({{ calculation.loan_details.annual_rate ?? (calculation.loan_product.interest_rate *
    12).toFixed(1) }}% p.a.)
                      </p>
                    </div>

                    <div class="p-6">
                      <!-- Key figures -->
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div class="bg-green-50 rounded-lg p-4 sm:col-span-2">
                          <h4 class="text-xs font-medium text-green-800 uppercase tracking-wide mb-1">Actual Monthly
                            Installment</h4>
                          <p class="text-2xl font-bold text-green-700">KSh {{
    formatNumber(calculation.loan_details.monthly_payment) }}</p>
                          <p class="text-xs text-green-600 mt-1">
                            = Principal/mo (KSh {{ formatNumber(calculation.loan_details.principal_per_month) }})
                            + M. Interest (KSh {{ formatNumber(calculation.loan_details.m_interest) }})
                          </p>
                        </div>

                        <div class="bg-blue-50 rounded-lg p-4">
                          <h4 class="text-xs font-medium text-blue-800 uppercase tracking-wide mb-1">Total Interest</h4>
                          <p class="text-xl font-bold text-blue-700">KSh {{
    formatNumber(calculation.loan_details.total_interest) }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4">
                          <h4 class="text-xs font-medium text-blue-800 uppercase tracking-wide mb-1">Total Repayment
                          </h4>
                          <p class="text-xl font-bold text-blue-700">KSh {{
    formatNumber(calculation.loan_details.total_repayment) }}</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4">
                          <h4 class="text-xs font-medium text-orange-800 uppercase tracking-wide mb-1">Total Cost of
                            Loan</h4>
                          <p class="text-xl font-bold text-orange-700">KSh {{
    formatNumber(calculation.loan_details.total_cost_of_loan) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                          <h4 class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">Net Disbursement
                          </h4>
                          <p class="text-xl font-bold text-gray-700">KSh {{
    formatNumber(calculation.loan_details.net_disbursement) }}</p>
                        </div>
                      </div>
                      <!-- Breakdown -->
                      <div class="mt-6">
                        <h4 class="text-sm font-semibold text-[#0a2342] mb-3">Breakdown</h4>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                          <div>
                            <span class="font-medium">Processing Fee:</span>
                            KSh {{ formatNumber(calculation.loan_details.processing_fee) }}
                          </div>

                          <div>
                            <span class="font-medium">Insurance Fee:</span>
                            KSh {{ formatNumber(calculation.loan_details.insurance_fee) }}
                          </div>

                          <div>
                            <span class="font-medium">Total Fees:</span>
                            KSh {{ formatNumber(calculation.loan_details.total_fees) }}
                          </div>

                          <div>
                            <span class="font-medium">Net Disbursement:</span>
                            KSh {{ formatNumber(calculation.loan_details.net_disbursement) }}
                          </div>
                        </div>
                      </div>

                      <!-- ADD THIS inside SUMMARY -->
                      <div class="border-t border-gray-200 pt-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Repayment Timeline</h4>

                        <div class="flex justify-between text-sm">
                          <span class="text-gray-500">First Payment Date</span>
                          <span class="font-medium">
                            {{ formatDate(calculation.summary?.first_payment_date) }}
                          </span>
                        </div>

                        <div class="flex justify-between text-sm mt-2">
                          <span class="text-gray-500">Last Payment Date</span>
                          <span class="font-medium">
                            {{ formatDate(calculation.summary?.last_payment_date) }}
                          </span>
                        </div>
                      </div>


                      <!-- VIEW SCHEDULE BUTTON -->
                      <div v-if="calculation && !showSchedule" class="text-center mt-4">
                        <button @click="openSchedule"
                          class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-6 rounded-md transition">
                          View Repayment Schedule
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <!-- SCHEDULE  -->
            <div v-if="calculation && showSchedule" ref="scheduleSection"
              class="mt-8 bg-white shadow-lg rounded-2xl border border-gray-200">

              <!-- Header -->
              <div
                class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-blue-50 rounded-t-2xl">
                <h3 class="text-lg font-semibold text-[#0a2342]">Repayment Schedule</h3>

                <button @click="closeSchedule" class="text-gray-500 hover:text-gray-700">
                  ✕
                </button>
              </div>

              <!-- Table -->
              <div class="overflow-x-auto max-h-[500px] overflow-y-auto">

                <table class="min-w-full text-sm">

                  <!-- Header -->
                  <thead class="bg-[#0a2342] text-white sticky top-0 z-10">
                    <tr>
                      <th class="px-4 py-3 text-center">#</th>
                      <th class="px-4 py-3 text-left">Date</th>
                      <th class="px-4 py-3 text-right">Opening</th>
                      <th class="px-4 py-3 text-right">Principal</th>
                      <th class="px-4 py-3 text-right">Interest</th>
                      <th class="px-4 py-3 text-right">Installment</th>
                      <th class="px-4 py-3 text-right">Balance</th>
                    </tr>
                  </thead>

                  <!-- Body -->
                  <tbody class="divide-y divide-gray-100">
                    <tr v-for="row in calculation.amortization_schedule" :key="row.payment_number"
                      :class="row.payment_number % 2 === 0 ? 'bg-gray-50' : 'bg-white'"
                      class="hover:bg-blue-50 transition">

                      <td class="px-4 py-3 text-center font-medium">
                        {{ row.payment_number }}
                      </td>

                      <td class="px-4 py-3">
                        {{ formatDate(row.payment_date) }}
                      </td>

                      <td class="px-4 py-3 text-right">
                        {{ formatNumber(row.opening_balance) }}
                      </td>

                      <td class="px-4 py-3 text-right">
                        {{ formatNumber(row.principal_amount) }}
                      </td>

                      <td class="px-4 py-3 text-right text-blue-700">
                        {{ formatNumber(row.interest_amount) }}
                      </td>

                      <td class="px-4 py-3 text-right font-semibold text-green-700">
                        {{ formatNumber(row.payment_amount) }}
                      </td>

                      <td class="px-4 py-3 text-right">
                        {{ formatNumber(row.closing_balance) }}
                      </td>
                    </tr>
                  </tbody>

                  <!-- Footer -->
                  <tfoot class="bg-[#0a2342] text-white font-semibold sticky bottom-0">
                    <tr>
                      <td colspan="2" class="px-4 py-3 text-center">TOTAL</td>

                      <td></td>

                      <td class="px-4 py-3 text-right">
                        {{ formatNumber(calculation.summary?.total_principal_paid) }}
                      </td>

                      <td class="px-4 py-3 text-right">
                        {{ formatNumber(calculation.summary?.total_interest_paid) }}
                      </td>

                      <td class="px-4 py-3 text-right">
                        {{ formatNumber(calculation.loan_details.total_repayment) }}
                      </td>

                      <td></td>
                    </tr>
                  </tfoot>

                </table>
              </div>
            </div>
            <!-- end calculator -->
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { usePage, Head } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'

const showSchedule = ref(false)

const props = defineProps({ loanProducts: { type: Array, default: () => [] } })
const page = usePage()
const isAdmin = computed(() => {
  const user = page.props.auth?.user || {}
  return user?.role === 'admin' || user?.is_admin === true || user?.isAdmin === true
})
const activeTab = ref('calculator')
const form = ref({ loan_product_id: '', principal_amount: '', term_months: '' })
const selectedProduct = ref(null)
const calculation = ref(null)
const loading = ref(false)
const error = ref('')

const adminForm = ref({
  product_id: '',
  interest_rate: 0,
  processing_fee_rate: 0,
  insurance_rate: 0,
  processing_fee_flat: 0,
  min_amount: 0,
  max_amount: 0,
  min_term_months: 0,
  max_term_months: 0
})
const setupScope = ref('global')
const savingSetup = ref(false)
const setupSaved = ref(false)
const setupError = ref('')

watch(() => form.value.loan_product_id, (v) => onLoanProductChange())
watch(setupScope, () => resetAdminForm())


const scheduleSection = ref(null)

const openSchedule = async () => {
  showSchedule.value = true
  await nextTick()
  scheduleSection.value?.scrollIntoView({ behavior: 'smooth' })
}

const closeSchedule = async () => {
  showSchedule.value = false
  await nextTick()
  document.getElementById('loan-summary')?.scrollIntoView({ behavior: 'smooth' })
}

const onLoanProductChange = () => {
  calculation.value = null
  error.value = ''
  selectedProduct.value = props.loanProducts.find(p => p.id === form.value.loan_product_id) || null
  form.value.principal_amount = ''
  form.value.term_months = ''
}

const calculateLoan = async () => {
  if (!isFormValid.value) return

  loading.value = true
  calculation.value = null
  error.value = ''
  showSchedule.value = false

  try {
    const res = await axios.post('/loan-calculator/calculate', form.value)
    calculation.value = res.data.calculation || null
    await nextTick()
    document.getElementById('loan-summary')?.scrollIntoView({ behavior: 'smooth' })
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.error || 'An error occurred.'
    calculation.value = null
  } finally {
    loading.value = false
  }
}

const formatDate = (d) => {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-KE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Admin Setup
const onAdminProductChange = () => {
  const product = props.loanProducts.find(p => p.id === adminForm.value.product_id)
  if (!product) return
  Object.assign(adminForm.value, {
    interest_rate: product.interest_rate,
    processing_fee_rate: product.processing_fee_rate,
    insurance_rate: product.insurance_rate,
    processing_fee_flat: product.processing_fee_flat,
    min_amount: product.min_amount,
    max_amount: product.max_amount,
    min_term_months: product.min_term_months,
    max_term_months: product.max_term_months
  })
}

const saveSetup = async () => {
  savingSetup.value = true
  setupSaved.value = false
  setupError.value = ''
  if (setupScope.value === 'global') adminForm.value.product_id = ''
  try {
    const url = setupScope.value === 'global' ? '/api/loan-setup/defaults' : '/api/loan-setup/product'
    await axios.post(url, adminForm.value)
    setupSaved.value = true
  } catch (err) {
    setupError.value = err.response?.data?.message || 'Failed to save.'
  } finally { savingSetup.value = false }
}

const resetAdminForm = () => {
  Object.assign(adminForm.value, {
    product_id: '',
    interest_rate: 0,
    processing_fee_rate: 0,
    insurance_rate: 0,
    processing_fee_flat: 0,
    min_amount: 0,
    max_amount: 0,
    min_term_months: 0,
    max_term_months: 0
  })
  setupSaved.value = false
  setupError.value = ''
}

const loadDefaults = async () => {
  try {
    const res = await axios.get('/api/loan-setup/defaults')
    Object.assign(adminForm.value, res.data)
  } catch (err) { setupError.value = 'Failed to load defaults.' }
}

const isFormValid = computed(() => {
  return form.value.loan_product_id && form.value.principal_amount > 0 && form.value.term_months > 0
})

const formatNumber = (n) => new Intl.NumberFormat().format(n || 0)
</script>

<style scoped>
.loan-calculator .shadow-lg {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

.loan-calculator select,
.loan-calculator input {
  transition: border-color 0.2s, box-shadow 0.2s;
}

@media (max-width: 640px) {
  .loan-calculator .grid {
    grid-template-columns: 1fr;
  }
}
</style>