<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Loans', href: route('loans.index') },
      { title: 'Loan Calculator' },
    ]"
  >
    <Head title="Calculator" />

    <div
      class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 p-4 sm:p-6"
    >
      <!-- HERO -->
      <section
        class="relative overflow-hidden rounded-[32px] bg-gradient-to-br from-[#0F172A] via-[#132F57] to-[#1E3A8A] p-6 sm:p-8 shadow-2xl"
      >
        <div
          class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-blue-400/20 blur-3xl"
        ></div>

        <div
          class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
        >
          <div>
            <div
              class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-orange-500/90 px-3 py-1 backdrop-blur"
            >
              <span
                class="h-2 w-2 rounded-full bg-emerald-400"
              ></span>

              <span class="text-xs text-white">
                SEPU SACCO
              </span>
            </div>

            <h1
              class="mt-4 text-3xl font-bold tracking-tight text-white"
            >
              Loan Calculator
            </h1>

            <p class="mt-2 text-sm text-slate-300">
              Calculate your loan repayments instantly before applying.
            </p>
          </div>

          <div
            v-if="isAdmin"
            class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm text-white backdrop-blur-xl"
          >
            Viewing as Admin
          </div>
        </div>
      </section>

      <!-- MAIN -->
      <section
        class="mt-8 overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-sm"
      >
        <!-- TABS -->
        <div
          class="border-b border-slate-100 p-4"
        >
          <div
            class="inline-flex rounded-2xl bg-slate-100 p-1"
          >
            <button
              :class="[
                'rounded-xl px-5 py-2 text-sm font-medium transition-all',
                activeTab === 'calculator'
                  ? 'bg-white text-[#0F172A] shadow-sm'
                  : 'text-slate-500 hover:text-slate-700',
              ]"
              @click="activeTab = 'calculator'"
            >
              Calculator
            </button>

            <button
              v-if="isAdmin"
              :class="[
                'rounded-xl px-5 py-2 text-sm font-medium transition-all',
                activeTab === 'setup'
                  ? 'bg-white text-orange-600 shadow-sm'
                  : 'text-slate-500 hover:text-slate-700',
              ]"
              @click="activeTab = 'setup'"
            >
              Setup
            </button>
          </div>
        </div>

        <div class="p-5 sm:p-7">
          <!-- CALCULATOR -->
        <div v-if="activeTab === 'calculator'">
          <div
            class="flex flex-col gap-6 xl:flex-row xl:items-start"
          >
            <!-- LEFT SIDE -->
            <div
              class="xl:sticky xl:top-6 xl:w-[420px] xl:flex-shrink-0"
            >
              <!-- FORM -->
              <div
                class="rounded-[28px] border border-slate-200 bg-white shadow-sm max-h-[calc(100vh-4rem)] overflow-y-auto"
              >
                <div
                  class="border-b border-slate-100 p-6"
                >
                  <h3
                    class="text-xl font-bold text-slate-900"
                  >
                    Loan Details
                  </h3>

                  <p
                    class="mt-1 text-sm text-slate-500"
                  >
                    Enter your loan requirements
                  </p>
                </div>

                <form
                  @submit.prevent="calculateLoan"
                  class="space-y-6 p-6"
                >
                  <!-- PRODUCT -->
                  <div>
                    <label
                      class="mb-2 block text-sm font-medium text-slate-700"
                    >
                      Loan Product
                    </label>

                    <select
                      v-model="form.loan_product_id"
                      @change="onLoanProductChange"
                      class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                    >
                      <option value="">
                        Select loan product
                      </option>

                      <option
                        v-for="product in loanProducts"
                        :key="product.id"
                        :value="product.id"
                      >
                        {{ product.name }}
                      </option>
                    </select>
                  </div>

                  <!-- PRODUCT INFO -->
                  <div
                    v-if="selectedProduct"
                    class="rounded-2xl border border-blue-100 bg-blue-50 p-5"
                  >
                    <h4
                      class="font-semibold text-[#0F172A]"
                    >
                      Product Details
                    </h4>

                    <div
                      class="mt-4 grid grid-cols-2 gap-4 text-sm"
                    >
                      <div>
                        <p class="text-slate-500">
                          Interest Rate
                        </p>

                        <p
                          class="font-semibold text-slate-800"
                        >
                          {{ selectedProduct.interest_rate }}%
                        </p>
                      </div>

                      <div>
                        <p class="text-slate-500">
                          Processing Fee
                        </p>

                        <p
                          class="font-semibold text-slate-800"
                        >
                          {{ selectedProduct.processing_fee_rate }}%
                        </p>
                      </div>

                      <div class="col-span-2">
                        <p class="text-slate-500">
                          Amount Range
                        </p>

                        <p
                          class="font-semibold text-slate-800"
                        >
                          KSh
                          {{
                            formatNumber(
                              selectedProduct.min_amount
                            )
                          }}
                          —
                          KSh
                          {{
                            formatNumber(
                              selectedProduct.max_amount
                            )
                          }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- AMOUNT -->
                  <div>
                    <label
                      class="mb-2 block text-sm font-medium text-slate-700"
                    >
                      Loan Amount
                    </label>

                    <input
                      type="number"
                      v-model.number="form.principal_amount"
                      class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                      placeholder="Enter amount"
                    />
                  </div>

                  <!-- TERM -->
                  <div>
                    <label
                      class="mb-2 block text-sm font-medium text-slate-700"
                    >
                      Repayment Period
                    </label>

                    <input
                      type="number"
                      v-model.number="form.term_months"
                      class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
                      placeholder="Months"
                    />
                  </div>

                  <!-- BUTTON -->
                  <button
                    type="submit"
                    :disabled="loading || !isFormValid"
                    class="flex h-12 w-full items-center justify-center rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 font-semibold text-white shadow-lg transition-all hover:scale-[1.01] hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <svg
                      v-if="loading"
                      class="mr-2 h-5 w-5 animate-spin"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      ></circle>

                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                      ></path>
                    </svg>

                    {{
                      loading
                        ? 'Calculating...'
                        : 'Calculate Loan'
                    }}
                  </button>
                </form>
              </div>
            </div>

            <!-- RIGHT SIDE -->
            <div
              class="min-w-0 flex-1 space-y-6"
            >
              <!-- SUMMARY -->
              <div
                v-if="calculation"
                id="loan-summary"
                class="overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-sm"
              >
                <!-- HEADER -->
                <div
                  class="border-b border-slate-100 p-6"
                >
                  <h3
                    class="text-2xl font-bold text-slate-900"
                  >
                    Loan Summary
                  </h3>

                  <p
                    class="mt-2 text-sm text-slate-500"
                  >
                    {{
                      calculation.loan_details.term_months
                    }}
                    months repayment plan
                  </p>
                </div>

                <div class="p-6">
                  <!-- BIG CARD -->
                  <div
                    class="rounded-[28px] bg-gradient-to-br from-blue-500 to-green-600 p-6 text-white shadow-xl"
                  >
                    <p class="text-sm text-white/80">
                      Monthly Installment
                    </p>

                    <h2
                      class="mt-2 text-2xl font-bold tracking-tight"
                    >
                      KSh
                      {{
                        formatNumber(
                          calculation.loan_details.monthly_payment
                        )
                      }}
                    </h2>

                    <p
                      class="mt-3 text-sm text-white/80"
                    >
                      Principal + Interest
                    </p>
                  </div>

                  <!-- STATS -->
                  <div
                    class="mt-6 grid gap-4 sm:grid-cols-2"
                  >
                    <div
                      class="rounded-2xl bg-slate-50 p-5"
                    >
                      <p class="text-sm text-slate-500">
                        Total Interest
                      </p>

                      <h3
                        class="mt-2 text-2xl font-bold text-slate-900"
                      >
                        KSh
                        {{
                          formatNumber(
                            calculation.loan_details.total_interest
                          )
                        }}
                      </h3>
                    </div>

                    <div
                      class="rounded-2xl bg-slate-50 p-5"
                    >
                      <p class="text-sm text-slate-500">
                        Total Repayment
                      </p>

                      <h3
                        class="mt-2 text-2xl font-bold text-slate-900"
                      >
                        KSh
                        {{
                          formatNumber(
                            calculation.loan_details.total_repayment
                          )
                        }}
                      </h3>
                    </div>
                  </div>

                  <!-- TIMELINE -->
                  <div
                    class="mt-6 rounded-2xl border border-slate-200 p-5"
                  >
                    <h4
                      class="font-semibold text-slate-800"
                    >
                      Repayment Timeline
                    </h4>

                    <div
                      class="mt-4 flex items-center justify-between text-sm"
                    >
                      <span class="text-slate-500">
                        First Payment
                      </span>

                      <span
                        class="font-medium text-slate-800"
                      >
                        {{
                          formatDate(
                            calculation.summary?.first_payment_date
                          )
                        }}
                      </span>
                    </div>

                    <div
                      class="mt-3 flex items-center justify-between text-sm"
                    >
                      <span class="text-slate-500">
                        Last Payment
                      </span>

                      <span
                        class="font-medium text-slate-800"
                      >
                        {{
                          formatDate(
                            calculation.summary?.last_payment_date
                          )
                        }}
                      </span>
                    </div>
                  </div>

                  <!-- BUTTON -->
                  <div
                    v-if="calculation && !showSchedule"
                    class="mt-6"
                  >
                    <button
                      @click="openSchedule"
                      class="flex h-12 w-full items-center justify-center rounded-2xl bg-blue-900 font-semibold text-white shadow-lg transition hover:bg-slate-800"
                    >
                      View Repayment Schedule
                    </button>
                  </div>
                </div>
              </div>

              <!-- EMPTY -->
              <div
                v-else
                class="flex min-h-[450px] flex-col items-center justify-center rounded-[30px] border border-dashed border-slate-300 bg-white p-10 text-center"
              >
                <div
                  class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100"
                >
                  💰
                </div>

                <h3
                  class="mt-5 text-xl font-bold text-slate-900"
                >
                  Loan Calculator
                </h3>

                <p
                  class="mt-2 max-w-sm text-sm text-slate-500"
                >
                  Fill in your loan details to see repayment estimates and schedules.
                </p>
              </div>
            </div>
          </div>
        </div>

          <!-- SCHEDULE -->
          <div
            v-if="calculation && showSchedule"
            ref="scheduleSection"
            class="mt-8 overflow-hidden rounded-[30px] border border-slate-200 bg-white shadow-sm"
          >
            <!-- HEADER -->
            <div
              class="flex items-center justify-between border-b border-slate-100 p-6"
            >
              <div>
                <h3
                  class="text-xl font-bold text-slate-900"
                >
                  Repayment Schedule
                </h3>

                <p
                  class="mt-1 text-sm text-slate-500"
                >
                  Monthly repayment breakdown
                </p>
              </div>

              <button
                @click="closeSchedule"
                class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200"
              >
                ✕
              </button>
            </div>

            <!-- TABLE -->
            <div
              class="max-h-[600px] overflow-auto"
            >
              <table class="min-w-full text-sm">
                <thead
                  class="sticky top-0 bg-blue-900 text-white"
                >
                  <tr>
                    <th
                      class="px-5 py-4 text-center"
                    >
                      #
                    </th>

                    <th
                      class="px-5 py-4 text-left"
                    >
                      Date
                    </th>

                    <th
                      class="px-5 py-4 text-right"
                    >
                      Opening
                    </th>

                    <th
                      class="px-5 py-4 text-right"
                    >
                      Principal
                    </th>

                    <th
                      class="px-5 py-4 text-right"
                    >
                      Interest
                    </th>

                    <th
                      class="px-5 py-4 text-right"
                    >
                      Installment
                    </th>

                    <th
                      class="px-5 py-4 text-right"
                    >
                      Balance
                    </th>
                  </tr>
                </thead>

                <tbody>
                  <tr
                    v-for="row in calculation.amortization_schedule"
                    :key="row.payment_number"
                    class="border-b transition hover:bg-slate-50"
                  >
                    <td
                      class="px-5 py-4 text-center"
                    >
                      {{ row.payment_number }}
                    </td>

                    <td class="px-5 py-4">
                      {{
                        formatDate(
                          row.payment_date
                        )
                      }}
                    </td>

                    <td
                      class="px-5 py-4 text-right"
                    >
                      {{
                        formatNumber(
                          row.opening_balance
                        )
                      }}
                    </td>

                    <td
                      class="px-5 py-4 text-right font-medium"
                    >
                      {{
                        formatNumber(
                          row.principal_amount
                        )
                      }}
                    </td>

                    <td
                      class="px-5 py-4 text-right text-blue-600"
                    >
                      {{
                        formatNumber(
                          row.interest_amount
                        )
                      }}
                    </td>

                    <td
                      class="px-5 py-4 text-right font-bold text-emerald-600"
                    >
                      {{
                        formatNumber(
                          row.payment_amount
                        )
                      }}
                    </td>

                    <td
                      class="px-5 py-4 text-right"
                    >
                      {{
                        formatNumber(
                          row.closing_balance
                        )
                      }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- SETUP -->
          <div
            v-if="activeTab === 'setup' && isAdmin"
            class="grid gap-6 lg:grid-cols-2"
          >
            <div
              class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm"
            >
              <h3
                class="text-lg font-bold text-slate-900"
              >
                Edit Scope
              </h3>

              <p
                class="mt-1 text-sm text-slate-500"
              >
                Configure loan calculator settings.
              </p>

              <div class="mt-6 space-y-5">
                <div>
                  <label
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Scope
                  </label>

                  <select
                    v-model="setupScope"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
                  >
                    <option value="global">
                      Global Defaults
                    </option>

                    <option value="product">
                      Specific Product
                    </option>
                  </select>
                </div>

                <div
                  v-if="
                    setupScope === 'product'
                  "
                >
                  <label
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Product
                  </label>

                  <select
                    v-model="
                      adminForm.product_id
                    "
                    @change="
                      onAdminProductChange
                    "
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
                  >
                    <option value="">
                      Select Product
                    </option>

                    <option
                      v-for="p in loanProducts"
                      :key="p.id"
                      :value="p.id"
                    >
                      {{ p.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- FORM -->
            <div
              class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm"
            >
              <div
                class="flex items-center justify-between"
              >
                <h3
                  class="text-lg font-bold text-slate-900"
                >
                  Loan Setup
                </h3>

                <span
                  v-if="setupSaved"
                  class="text-sm font-medium text-emerald-600"
                >
                  ✔ Saved
                </span>
              </div>

              <form
                @submit.prevent="saveSetup"
                class="mt-6 grid gap-5 sm:grid-cols-2"
              >
                <div>
                  <label
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Interest Rate
                  </label>

                  <input
                    v-model.number="
                      adminForm.interest_rate
                    "
                    type="number"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
                  />
                </div>

                <div>
                  <label
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Processing Fee
                  </label>

                  <input
                    v-model.number="
                      adminForm.processing_fee_rate
                    "
                    type="number"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
                  />
                </div>

                <div>
                  <label
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Insurance Fee
                  </label>

                  <input
                    v-model.number="
                      adminForm.insurance_rate
                    "
                    type="number"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
                  />
                </div>

                <div>
                  <label
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Processing Flat
                  </label>

                  <input
                    v-model.number="
                      adminForm.processing_fee_flat
                    "
                    type="number"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
                  />
                </div>

                <div>
                  <label
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Min Amount
                  </label>

                  <input
                    v-model.number="
                      adminForm.min_amount
                    "
                    type="number"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
                  />
                </div>

                <div>
                  <label
                    class="mb-2 block text-sm font-medium text-slate-700"
                  >
                    Max Amount
                  </label>

                  <input
                    v-model.number="
                      adminForm.max_amount
                    "
                    type="number"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4"
                  />
                </div>

                <div class="sm:col-span-2">
                  <button
                    type="submit"
                    :disabled="savingSetup"
                    class="flex h-12 w-full items-center justify-center rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 font-semibold text-white shadow-lg transition hover:shadow-xl"
                  >
                    {{
                      savingSetup
                        ? 'Saving...'
                        : 'Save Setup'
                    }}
                  </button>
                </div>
              </form>

              <p
                v-if="setupError"
                class="mt-4 text-sm text-red-600"
              >
                {{ setupError }}
              </p>
            </div>
          </div>
        </div>
      </section>
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
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

/* Desktop sticky calculator */
@media (min-width: 1280px) {
  .xl\:sticky {
    position: sticky;
  }
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Better scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.5);
  border-radius: 999px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .loan-calculator .grid {
    grid-template-columns: 1fr;
  }
}
</style>