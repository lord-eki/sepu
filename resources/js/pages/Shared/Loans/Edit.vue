<script setup>
import axios from 'axios'
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
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

    // Update local loan data
    Object.assign(props.loan, res.data.data)

    // Auto redirect after 5s
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

// Optional: Close flash manually and redirect immediately
const closeFlash = () => {
  successMessage.value = ''
  if (flashTimeout) clearTimeout(flashTimeout)
  router.visit(route('loans.index'))
}

</script>
<template>
  <AppLayout :breadcrumbs="[
    { title: 'Loans', href: '/loans' },
    { title: `Edit ${loan.loan_number}` }
  ]">

    <Head :title="`Edit Loan ${loan.loan_number}`" />

    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">

      <!-- 🔷 HEADER -->
      <div class="relative overflow-hidden rounded-2xl shadow-lg border border-blue-900/20">
        <div class="absolute inset-0 bg-gradient-to-r from-[#041c32] via-blue-800 to-blue-900"></div>

        <div class="relative p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white">
              Edit Loan Application
            </h1>

            <p class="text-orange-300 mt-1 text-sm">
              {{ loan.member.first_name }} {{ loan.member.last_name }} • {{ loan.loan_number }}
            </p>
          </div>

          <div class="text-white/70 text-sm">
            Update Loan Details
          </div>
        </div>
      </div>

      <!-- ✅ SUCCESS MESSAGE -->
      <div 
        v-if="successMessage" 
        class="flex justify-between items-center gap-4 p-4 rounded-xl border border-green-300 bg-green-50 shadow-sm"
      >
        <span class="text-green-800 text-sm font-medium">
          {{ successMessage }}
        </span>

        <button 
          @click="closeFlash" 
          class="text-green-700 hover:text-green-900 font-bold text-lg"
        >
          ×
        </button>
      </div>

      <!-- 🧾 FORM -->
      <form 
        @submit.prevent="submit" 
        class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 space-y-8"
      >

        <!-- SECTION -->
        <div>
          <h2 class="text-lg font-semibold text-gray-800">
            Loan Details
          </h2>
          <p class="text-sm text-gray-500">
            Modify loan values below
          </p>
        </div>

        <!-- 🔲 GRID (KEY IMPROVEMENT) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- 💰 AMOUNT -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Applied Amount (KES)
            </label>

            <input 
              type="number" 
              v-model="form.applied_amount"
              class="w-full rounded-xl border border-gray-300 px-4 py-3 
                     focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
              placeholder="Enter amount"
            />

            <p v-if="errors.applied_amount" class="text-red-500 text-sm mt-1">
              {{ errors.applied_amount[0] }}
            </p>
          </div>

          <!-- 📅 TERM -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Loan Term (Months)
            </label>

            <input 
              type="number" 
              v-model="form.term_months"
              class="w-full rounded-xl border border-gray-300 px-4 py-3 
                     focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
              placeholder="e.g. 12"
            />

            <p v-if="errors.term_months" class="text-red-500 text-sm mt-1">
              {{ errors.term_months[0] }}
            </p>
          </div>

        </div>

        <!-- 📝 PURPOSE (FULL WIDTH) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Purpose / Notes
          </label>

          <textarea 
            v-model="form.purpose" 
            rows="5"
            class="w-full rounded-xl border border-gray-300 px-4 py-3 
                   focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition resize-none"
            placeholder="Optional description..."
          ></textarea>

          <p v-if="errors.purpose" class="text-red-500 text-sm mt-1">
            {{ errors.purpose[0] }}
          </p>
        </div>

        <!-- ⚡ ACTIONS -->
        <div class="flex justify-between items-center pt-4 border-t">

          <Link 
            :href="route('loans.index')" 
            class="text-gray-600 hover:text-gray-900 text-sm"
          >
            ← Back to Loans
          </Link>

          <div class="flex gap-3">
            <Link 
              :href="route('loans.index')" 
              class="px-5 py-2.5 rounded-xl border border-gray-300 bg-gray-100 
                     hover:bg-gray-200 text-gray-700 transition"
            >
              Cancel
            </Link>

            <button 
              type="submit" 
              :disabled="loading"
              class="px-6 py-2.5 rounded-xl text-white font-semibold 
                     bg-gradient-to-r from-orange-500 to-orange-600
                     hover:from-orange-600 hover:to-orange-700
                     shadow-md hover:shadow-lg
                     transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ loading ? 'Updating...' : 'Update Loan' }}
            </button>
          </div>

        </div>

      </form>
    </div>
  </AppLayout>
</template>