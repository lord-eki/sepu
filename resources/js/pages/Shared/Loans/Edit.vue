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

        <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

            <!-- HEADER -->
            <div
                class="rounded-2xl overflow-hidden shadow-md border mb-8 bg-gradient-to-r from-[#06203a] to-[#0a2342] p-6">
                <h1 class="text-2xl font-bold text-white">Edit Loan Application</h1>
                <p class="text-orange-200 mt-1">
                    Modify loan details for: {{ loan.member.first_name }} {{ loan.member.last_name }}
                </p>
            </div>

            <!-- SUCCESS MESSAGE -->
            <div v-if="successMessage" class="p-4 mb-4 rounded-lg bg-green-100 text-green-800 border border-green-300 flex justify-between items-center">
                <span>{{ successMessage }}</span>
                <button @click="closeFlash" class="ml-4 font-bold">X</button>
            </div>


            <!-- FORM -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow p-6 space-y-6 border">

                <!-- APPLIED AMOUNT -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Applied Amount</label>
                    <input type="number" v-model="form.applied_amount"
                        class="w-full border rounded-lg p-2 focus:ring-blue-200 focus:ring" />
                    <p v-if="errors.applied_amount" class="text-red-500 text-sm">{{ errors.applied_amount[0] }}</p>
                </div>

                <!-- TERM MONTHS -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term (Months)</label>
                    <input type="number" v-model="form.term_months"
                        class="w-full border rounded-lg p-2 focus:ring-blue-200 focus:ring" />
                    <p v-if="errors.term_months" class="text-red-500 text-sm">{{ errors.term_months[0] }}</p>
                </div>

                <!-- PURPOSE / NOTES -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purpose / Notes</label>
                    <textarea v-model="form.purpose" rows="4"
                        class="w-full border rounded-lg p-2 focus:ring-blue-200 focus:ring"></textarea>
                    <p v-if="errors.purpose" class="text-red-500 text-sm">{{ errors.purpose[0] }}</p>
                </div>

                <!-- ACTIONS -->
                <div class="flex justify-end gap-3 pt-4">
                    <Link :href="route('loans.index')"
                        class="px-4 py-2 rounded-lg border bg-gray-100 hover:bg-gray-200 text-gray-700">
                    Cancel
                    </Link>

                    <button type="submit" :disabled="loading"
                        class="px-4 py-2 rounded-lg bg-orange-600 text-white font-medium shadow hover:bg-orange-700 disabled:opacity-60">
                        {{ loading ? 'Updating...' : 'Update Loan' }}
                    </button>
                </div>

            </form>
        </div>
    </AppLayout>
</template>
