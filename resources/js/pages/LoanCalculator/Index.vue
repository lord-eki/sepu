<template>
  <AppLayout :breadcrumbs="[{ title: 'Loan Calculator' }]">
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
            <span class="inline-flex items-center text-white text-xs font-semibold px-3 py-1 rounded">Viewing as Admin</span>
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
            <button
              v-if="isAdmin"
              :class="['px-3 py-2 rounded-md text-sm font-medium', activeTab === 'setup' ? 'bg-orange-50 text-orange-700' : 'text-gray-600 hover:bg-gray-50']"
              @click="activeTab = 'setup'">
              Setup
            </button>
          </div>

          <div class="p-6">
            <!-- SETUP TAB (admin only) -->
            <div v-if="activeTab === 'setup' && isAdmin">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                  <h4 class="text-sm font-semibold text-[#0a2342] mb-3">Edit Scope</h4>
                  <p class="text-sm text-gray-600 mb-4">Choose whether to edit global defaults or a specific product.</p>
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
                      <select v-model="adminForm.product_id" @change="onAdminProductChange" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">-- Select product to edit --</option>
                        <option v-for="p in loanProducts" :key="p.id" :value="p.id">{{ p.name }} ({{ p.code }})</option>
                      </select>
                    </div>
                    <div class="pt-4 border-t">
                      <button @click="resetAdminForm" type="button" class="text-sm text-gray-600 hover:underline">Reset form</button>
                    </div>
                  </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
                  <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-semibold text-[#0a2342]">Loan Setup & Parameters</h4>
                    <span v-if="setupSaved" class="text-green-600 text-sm font-medium">✔ Saved</span>
                  </div>
                  <form @submit.prevent="saveSetup" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Interest Rate (% / month)</label>
                      <input v-model.number="adminForm.interest_rate" type="number" step="0.1" class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Processing Fee (%)</label>
                      <input v-model.number="adminForm.processing_fee_rate" type="number" step="0.1" class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Insurance Fee (%)</label>
                      <input v-model.number="adminForm.insurance_rate" type="number" step="0.1" class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Processing Fee Flat (optional)</label>
                      <input v-model.number="adminForm.processing_fee_flat" type="number" step="1" class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Min Amount (KSh)</label>
                      <input v-model.number="adminForm.min_amount" type="number" step="100" class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Max Amount (KSh)</label>
                      <input v-model.number="adminForm.max_amount" type="number" step="100" class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Min Term (Months)</label>
                      <input v-model.number="adminForm.min_term_months" type="number" class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div>
                      <label class="block text-sm text-gray-700 mb-1">Max Term (Months)</label>
                      <input v-model.number="adminForm.max_term_months" type="number" class="w-full px-3 py-2 border rounded" />
                    </div>
                    <div class="sm:col-span-2 flex justify-end gap-2 mt-2">
                      <button type="button" @click="loadDefaults" class="px-4 py-2 border rounded text-sm">Load current defaults</button>
                      <button type="submit" :disabled="savingSetup" class="px-5 py-2 bg-orange-500 text-white rounded text-sm flex items-center">
                        <svg v-if="savingSetup" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        {{ savingSetup ? 'Saving...' : (setupScope === 'global' ? 'Save Global Defaults' : 'Update Product') }}
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
                      <label for="loan_product_id" class="block text-sm font-medium text-gray-700 mb-2">Loan Product *</label>
                      <select id="loan_product_id" v-model="form.loan_product_id" @change="onLoanProductChange"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-orange-500" required>
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
                        <div><span class="font-medium">Interest Rate:</span> {{ selectedProduct.interest_rate }}% p.m.</div>
                        <div><span class="font-medium">Processing Fee:</span> {{ selectedProduct.processing_fee_rate }}%</div>
                        <div>
                          <span class="font-medium">Amount Range:</span>
                          KSh {{ formatNumber(selectedProduct.min_amount) }} – KSh {{ formatNumber(selectedProduct.max_amount) }}
                        </div>
                        <div>
                          <span class="font-medium">Term Range:</span>
                          {{ selectedProduct.min_term_months }} – {{ selectedProduct.max_term_months }} months
                        </div>
                      </div>
                    </div>

                    <!-- Principal -->
                    <div>
                      <label for="principal_amount" class="block text-sm font-medium text-gray-700 mb-2">Loan Amount (KSh) *</label>
                      <input id="principal_amount" type="number" v-model.number="form.principal_amount"
                        :min="selectedProduct?.min_amount || 0" :max="selectedProduct?.max_amount || 999999999" step="100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-1 focus:ring-orange-500"
                        placeholder="Enter loan amount" required />
                      <p v-if="selectedProduct" class="mt-1 text-xs text-gray-500">
                        Range: KSh {{ formatNumber(selectedProduct.min_amount) }} – KSh {{ formatNumber(selectedProduct.max_amount) }}
                      </p>
                    </div>

                    <!-- Term -->
                    <div>
                      <label for="term_months" class="block text-sm font-medium text-gray-700 mb-2">Repayment Period (Months) *</label>
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
                  <div v-if="calculation" id="loan-summary" class="bg-white shadow-lg rounded-2xl border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-100 bg-blue-50 rounded-t-2xl">
                      <h3 class="text-lg font-semibold text-[#0a2342]">Loan Summary</h3>
                      <p class="text-xs text-gray-500 mt-1">
                        {{ calculation.loan_details.term_months }} months ·
                        {{ calculation.loan_details.monthly_rate ?? calculation.loan_product.interest_rate }}% p.m.
                        ({{ calculation.loan_details.annual_rate ?? (calculation.loan_product.interest_rate * 12).toFixed(1) }}% p.a.)
                      </p>
                    </div>

                    <div class="p-6">
                      <!-- Key figures -->
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <!-- Actual Installment (what member pays) -->
                        <div class="bg-green-50 rounded-lg p-4 sm:col-span-2">
                          <h4 class="text-xs font-medium text-green-800 uppercase tracking-wide mb-1">Actual Monthly Installment</h4>
                          <p class="text-2xl font-bold text-green-700">KSh {{ formatNumber(calculation.loan_details.monthly_payment) }}</p>
                          <p class="text-xs text-green-600 mt-1">
                            = Principal/mo (KSh {{ formatNumber(calculation.loan_details.principal_per_month) }})
                            + M. Interest (KSh {{ formatNumber(calculation.loan_details.m_interest) }})
                          </p>
                        </div>

                        <div class="bg-blue-50 rounded-lg p-4">
                          <h4 class="text-xs font-medium text-blue-800 uppercase tracking-wide mb-1">Total Interest</h4>
                          <p class="text-xl font-bold text-blue-700">KSh {{ formatNumber(calculation.loan_details.total_interest) }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4">
                          <h4 class="text-xs font-medium text-blue-800 uppercase tracking-wide mb-1">Total Repayment</h4>
                          <p class="text-xl font-bold text-blue-700">KSh {{ formatNumber(calculation.loan_details.total_repayment) }}</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4">
                          <h4 class="text-xs font-medium text-orange-800 uppercase tracking-wide mb-1">Total Cost of Loan</h4>
                          <p class="text-xl font-bold text-orange-700">KSh {{ formatNumber(calculation.loan_details.total_cost_of_loan) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                          <h4 class="text-xs font-medium text-gray-600 uppercase tracking-wide mb-1">Net Disbursement</h4>
                          <p class="text-xl font-bold text-gray-700">KSh {{ formatNumber(calculation.loan_details.net_disbursement) }}</p>
                        </div>
                      </div>

                      <!-- Detailed breakdown -->
                      <div class="border-t border-gray-200 pt-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Breakdown</h4>
                        <div class="space-y-2 text-sm">
                          <div class="flex justify-between">
                            <span class="text-gray-500">Principal Amount</span>
                            <span class="font-medium">KSh {{ formatNumber(calculation.loan_details.principal_amount) }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-500">Principal / Month</span>
                            <span class="font-medium">KSh {{ formatNumber(calculation.loan_details.principal_per_month) }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-500">M. Interest (fixed monthly)</span>
                            <span class="font-medium">KSh {{ formatNumber(calculation.loan_details.m_interest) }}</span>
                          </div>
                          <div class="flex justify-between border-t pt-2">
                            <span class="text-gray-500">Processing Fee</span>
                            <span class="font-medium">KSh {{ formatNumber(calculation.loan_details.processing_fee) }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-500">Insurance Fee</span>
                            <span class="font-medium">KSh {{ formatNumber(calculation.loan_details.insurance_fee) }}</span>
                          </div>
                          <div class="flex justify-between border-t pt-2">
                            <span class="text-gray-500">First Payment Date</span>
                            <span class="font-medium">{{ formatDate(calculation.summary.first_payment_date) }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-500">Last Payment Date</span>
                            <span class="font-medium">{{ formatDate(calculation.summary.last_payment_date) }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- View Schedule Button -->
                  <div v-if="calculation && !showSchedule" class="text-center">
                    <button @click="openSchedule"
                      class="bg-orange-500 hover:bg-orange-600 hover:cursor-pointer text-white font-semibold py-2 px-6 rounded-md transition duration-200">
                      View Repayment Schedule
                    </button>
                  </div>

                  <!-- Error -->
                  <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                      <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                      </svg>
                      <p class="ml-3 text-sm text-red-800">{{ error }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Amortization Schedule — mirrors all Excel columns -->
              <div v-if="calculation && showSchedule" ref="scheduleSection" class="mt-8 bg-white shadow-lg rounded-2xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-blue-50 rounded-t-2xl">
                  <div>
                    <h3 class="text-lg font-semibold text-[#0a2342]">Repayment Schedule</h3>
                  </div>
                  <button @click="closeSchedule" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>

                <div class="overflow-x-auto max-h-[550px] overflow-y-auto">
                  <table class="min-w-full text-sm">
                    <!-- ── Header ── -->
                    <thead class="bg-[#0a2342] text-white sticky top-0 z-10">
                      <tr>
                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap">Month</th>
                        <th class="px-4 py-3 text-right font-semibold whitespace-nowrap">Opening Balance</th>
                        <th class="px-4 py-3 text-right font-semibold whitespace-nowrap">Principal</th>
                        <th class="px-4 py-3 text-right font-semibold whitespace-nowrap">Interest</th>
                        <th class="px-4 py-3 text-right font-semibold whitespace-nowrap">Installment</th>
                        <th class="px-4 py-3 text-right font-semibold whitespace-nowrap bg-blue-700">M. Interest</th>
                        <th class="px-4 py-3 text-right font-semibold whitespace-nowrap bg-green-700">Actual Installment</th>
                      </tr>
                      <!-- Column explanations -->
                      <tr class="bg-[#0c2a52] text-blue-200 text-xs">
                        <th class="px-4 py-1"></th>
                        <th class="px-4 py-1 text-right font-normal">Balance b/f</th>
                        <th class="px-4 py-1 text-right font-normal">P / N</th>
                        <th class="px-4 py-1 text-right font-normal">Balance × rate</th>
                        <th class="px-4 py-1 text-right font-normal">P + Interest</th>
                        <th class="px-4 py-1 text-right font-normal bg-blue-800">Fixed avg interest</th>
                        <th class="px-4 py-1 text-right font-normal bg-green-800">P + M. Interest</th>
                      </tr>
                    </thead>

                    <!-- ── Body ── -->
                    <tbody class="divide-y divide-gray-100">
                      <tr v-for="row in calculation.amortization_schedule" :key="row.payment_number"
                          :class="row.payment_number % 2 === 0 ? 'bg-gray-50' : 'bg-white'" class="hover:bg-blue-50 transition-colors">
                        <td class="px-4 py-3 text-center text-gray-700 font-medium">{{ row.payment_number }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ formatNumber(row.opening_balance) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ formatNumber(row.principal_amount) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ formatNumber(row.interest_amount) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ formatNumber(row.installment) }}</td>
                        <td class="px-4 py-3 text-right text-blue-700 font-medium">{{ formatNumber(row.m_interest) }}</td>
                        <td class="px-4 py-3 text-right text-green-700 font-bold">{{ formatNumber(row.payment_amount) }}</td>
                      </tr>
                    </tbody>

                    <!-- ── Totals row ── -->
                    <tfoot class="bg-[#0a2342] text-white font-semibold sticky bottom-0">
                      <tr>
                        <td class="px-4 py-3 text-center">TOTALS</td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3 text-right">{{ formatNumber(calculation.summary.total_principal_paid) }}</td>
                        <td class="px-4 py-3 text-right">{{ formatNumber(calculation.summary.total_interest_paid) }}</td>
                        <td class="px-4 py-3 text-right">{{ formatNumber(calculation.loan_details.total_repayment) }}</td>
                        <td class="px-4 py-3 text-right bg-blue-800">{{ formatNumber(calculation.summary.total_interest_paid) }}</td>
                        <td class="px-4 py-3 text-right bg-green-800">{{ formatNumber(calculation.loan_details.total_repayment) }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
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

const props = defineProps({
  loanProducts: { type: Array, default: () => [] }
})

const page = usePage()
const isAdmin = computed(() => {
  const user = page.props.auth?.user || {}
  return user?.role === 'admin' || user?.is_admin === true || user?.isAdmin === true
})

const activeTab = ref('calculator')

const form = ref({
  loan_product_id: '',
  principal_amount: '',
  term_months: ''
})

const selectedProduct  = ref(null)
const calculation      = ref(null)
const loading          = ref(false)
const error            = ref('')
const showSchedule     = ref(false)
const scheduleSection  = ref(null)

// ── Admin setup ──────────────────────────────────────────────────────────────
const setupScope  = ref('global')
const adminForm   = ref({
  product_id: '', interest_rate: '', processing_fee_rate: '',
  processing_fee_flat: '', insurance_rate: '',
  min_amount: '', max_amount: '', min_term_months: '', max_term_months: ''
})
const savingSetup = ref(false)
const setupSaved  = ref(false)
const setupError  = ref('')

const onAdminProductChange = async () => {
  setupError.value = ''
  if (!adminForm.value.product_id) { resetAdminForm(); return }
  try {
    const res = await axios.get(`/api/loan-products/${adminForm.value.product_id}`)
    const p = res.data.loan_product
    adminForm.value = { ...adminForm.value, ...p }
  } catch { setupError.value = 'Failed to load product values' }
}

const loadDefaults = async () => {
  setupError.value = ''
  try {
    const res = await axios.get('/api/loan-setup/defaults')
    const d = res.data.defaults || {}
    adminForm.value = { ...adminForm.value, ...d }
  } catch { setupError.value = 'Failed to load defaults' }
}

const resetAdminForm = () => {
  adminForm.value = {
    product_id: '', interest_rate: '', processing_fee_rate: '',
    processing_fee_flat: '', insurance_rate: '',
    min_amount: '', max_amount: '', min_term_months: '', max_term_months: ''
  }
  setupError.value = ''
}

const saveSetup = async () => {
  savingSetup.value = true
  setupSaved.value  = false
  setupError.value  = ''
  try {
    if (setupScope.value === 'global') {
      await axios.post('/api/loan-setup/defaults', adminForm.value)
    } else {
      if (!adminForm.value.product_id) { setupError.value = 'Select product to update'; return }
      await axios.put(`/api/loan-products/${adminForm.value.product_id}`, adminForm.value)
    }
    setupSaved.value = true
    setTimeout(() => (setupSaved.value = false), 2500)
  } catch (err) {
    setupError.value = err.response?.data?.message || 'Failed to save setup'
  } finally {
    savingSetup.value = false
  }
}

// ── Calculator ───────────────────────────────────────────────────────────────
const isFormValid = computed(() =>
  form.value.loan_product_id && form.value.principal_amount && form.value.term_months
)

const onLoanProductChange = async () => {
  if (!form.value.loan_product_id) { selectedProduct.value = null; return }
  try {
    const response = await axios.get(`/api/loan-products/${form.value.loan_product_id}`)
    selectedProduct.value = response.data.loan_product
    calculation.value = null
    error.value = ''
    showSchedule.value = false
  } catch { error.value = 'Error loading loan product details' }
}

const calculateLoan = async () => {
  if (!isFormValid.value) return
  loading.value = true
  error.value   = ''
  try {
    const response = await axios.post('/loan-calculator/calculate', {
      loan_product_id:  form.value.loan_product_id,
      principal_amount: form.value.principal_amount,
      term_months:      form.value.term_months
    })
    calculation.value  = response.data.calculation
    showSchedule.value = false
  } catch (err) {
    error.value       = err.response?.data?.error || 'Error calculating loan'
    calculation.value = null
  } finally {
    loading.value = false
  }
}

const openSchedule = async () => {
  showSchedule.value = true
  await nextTick()
  scheduleSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  window.scrollBy(0, -80)
}

const closeSchedule = async () => {
  showSchedule.value = false
  await nextTick()
  document.getElementById('loan-summary')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  window.scrollBy(0, -80)
}

// ── Formatters ───────────────────────────────────────────────────────────────
const formatNumber = (n) => {
  if (n === null || n === undefined || isNaN(Number(n))) return '—'
  return new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n)
}

const formatDate = (d) =>
  new Date(d).toLocaleDateString('en-KE', { year: 'numeric', month: 'short', day: 'numeric' })

watch(() => adminForm.value.product_id, (v) => { if (v) onAdminProductChange() })
</script>

<style scoped>
.loan-calculator {
  min-height: 100vh;
  background-color: #f9fafb;
  overflow-y: auto;
}
</style>