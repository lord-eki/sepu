<script setup>
import axios from 'axios'
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({ loan: Object })

const form = ref({
  applied_amount: props.loan.applied_amount,
  term_months: props.loan.term_months || 12,
  purpose: props.loan.purpose || '',
})

const errors = ref({})
const loading = ref(false)
const successMessage = ref('')
let flashTimeout = null

const loanProduct = props.loan.loan_product

// ========================
// 📊 LOAN PROJECTIONS
// ========================

const monthlyPreview = computed(() => {
  const P = form.value.applied_amount || 0
  const n = form.value.term_months || 1
  const r = (loanProduct?.interest_rate || 0) / 100

  if (!P || !n) return 0

  const principalPerMonth = P / n
  const totalInterest = P * r * ((n + 1) / 2)
  const mInterest = totalInterest / n

  return principalPerMonth + mInterest
})

const totalInterestPreview = computed(() => {
  const P = form.value.applied_amount || 0
  const n = form.value.term_months || 1
  const r = (loanProduct?.interest_rate || 0) / 100

  return P * r * ((n + 1) / 2)
})

const processingFeePreview = computed(() => {
  const P = form.value.applied_amount || 0
  return (P * (loanProduct?.processing_fee_rate || 0)) / 100
})

const insuranceFeePreview = computed(() => {
  const P = form.value.applied_amount || 0
  return (P * (loanProduct?.insurance_rate || 0)) / 100
})

const netDisbursementPreview = computed(() => {
  return (
    (form.value.applied_amount || 0)
    - processingFeePreview.value
    - insuranceFeePreview.value
  )
})

// ========================
// 💰 SUBMIT
// ========================
const submit = async () => {
  loading.value = true
  errors.value = {}
  successMessage.value = ''

  try {
    const payload = {
      applied_amount: form.value.applied_amount,
      term_months: form.value.term_months,
      purpose: form.value.purpose || '',
    }

    const res = await axios.put(`/loans/${props.loan.id}`, payload)

    successMessage.value = res.data.message

    Object.assign(props.loan, res.data.data)

    flashTimeout = setTimeout(() => {
      router.visit(route('loans.index'))
    }, 3000)

  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors
    } else {
      alert('Unexpected error: ' + error.response?.data?.message)
    }
  } finally {
    loading.value = false
  }
}

const closeFlash = () => {
  successMessage.value = ''
  if (flashTimeout) clearTimeout(flashTimeout)
  router.visit(route('loans.index'))
}

// ========================
// FORMATTER
// ========================
const formatCurrency = (value) =>
  new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value || 0)

</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: 'Loans', href: '/loans' },
    { title: `Edit ${loan.loan_number}` }
  ]">

    <Head :title="`Edit Loan ${loan.loan_number}`" />

    <div class="max-w-7xl mx-auto py-10 px-4 space-y-8">

      <!-- HEADER -->
      <div class="rounded-2xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-blue-950 via-blue-800 to-blue-900 p-6 text-white">
          <h1 class="text-2xl font-bold">Edit Loan Application</h1>
          <p class="text-orange-300 text-sm mt-1">
            {{ loan.member.first_name }} {{ loan.member.last_name }} • {{ loan.loan_number }}
          </p>
        </div>
      </div>

      <!-- SUCCESS -->
      <div v-if="successMessage" class="p-4 bg-green-50 border border-green-200 rounded-xl flex justify-between">
        <span class="text-green-700 text-sm">{{ successMessage }}</span>
        <button @click="closeFlash" class="text-green-700 font-bold">×</button>
      </div>

      <!-- FORM -->
      <form @submit.prevent="submit" class="bg-white rounded-2xl shadow p-6 space-y-6">

        <div class="grid md:grid-cols-2 gap-6">

          <!-- AMOUNT -->
          <div>
            <label class="text-sm font-medium">Applied Amount</label>
            <input
              v-model="form.applied_amount"
              type="number"
              class="w-full border rounded-xl p-3 mt-1 focus:ring-2 focus:ring-orange-500"
            />
            <p v-if="errors.applied_amount" class="text-red-500 text-sm">
              {{ errors.applied_amount[0] }}
            </p>
          </div>

          <!-- TERM -->
          <div>
            <label class="text-sm font-medium">Term (Months)</label>
            <input
              v-model="form.term_months"
              type="number"
              class="w-full border rounded-xl p-3 mt-1 focus:ring-2 focus:ring-orange-500"
            />
            <p v-if="errors.term_months" class="text-red-500 text-sm">
              {{ errors.term_months[0] }}
            </p>
          </div>
        </div>

        <!-- PURPOSE -->
        <div>
          <label class="text-sm font-medium">Purpose</label>
          <textarea
            v-model="form.purpose"
            rows="4"
            class="w-full border rounded-xl p-3 mt-1"
          ></textarea>
        </div>

        <!-- ================= PREVIEW SECTION ================= -->
        <div class="grid md:grid-cols-2 gap-4 pt-4">

          <div class="bg-blue-50 p-4 rounded-xl">
            <p class="text-sm text-blue-600">Monthly Repayment</p>
            <p class="text-xl font-bold text-blue-800">
              KES {{ formatCurrency(monthlyPreview) }}
            </p>
          </div>

          <div class="bg-orange-50 p-4 rounded-xl">
            <p class="text-sm text-orange-600">Total Interest</p>
            <p class="text-xl font-bold text-orange-700">
              KES {{ formatCurrency(totalInterestPreview) }}
            </p>
          </div>

          <div class="bg-slate-50 p-4 rounded-xl">
            <p class="text-sm text-slate-600">Processing Fee</p>
            <p class="text-xl font-bold">
              KES {{ formatCurrency(processingFeePreview) }}
            </p>
          </div>

          <div class="bg-emerald-50 p-4 rounded-xl">
            <p class="text-sm text-emerald-600">Net Disbursement</p>
            <p class="text-xl font-bold text-emerald-700">
              KES {{ formatCurrency(netDisbursementPreview) }}
            </p>
          </div>

        </div>

        <!-- ACTIONS -->
        <div class="flex justify-between pt-4 border-t">

          <Link :href="route('loans.index')" class="text-gray-600">
            ← Back
          </Link>

          <button
            type="submit"
            :disabled="loading"
            class="bg-orange-600 text-white px-6 py-2 rounded-xl"
          >
            {{ loading ? 'Updating...' : 'Update Loan' }}
          </button>

        </div>

      </form>
    </div>
  </AppLayout>
</template>