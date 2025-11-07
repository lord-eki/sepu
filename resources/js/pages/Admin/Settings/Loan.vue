<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const form = useForm({
  default_interest_rate: '',
  max_loan_amount: '',
  min_loan_amount: '',
  repayment_period: '',
  penalty_rate: '',
  loan_processing_fee: '',
})

function saveLoanSettings() {
  form.post(route('settings.loan.update'))
  
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'Loan' }]">
    <Head title="Loan Settings" />

    <div class="bg-white rounded-2xl shadow-md p-6">
      <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">
        Loan Settings
      </h1>

      <form @submit.prevent="saveLoanSettings" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700">Default Interest Rate (%)</label>
          <input
            v-model="form.default_interest_rate"
            type="number"
            step="0.1"
            placeholder="e.g., 12.5"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
          />
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-gray-700">Minimum Loan Amount</label>
            <input
              v-model="form.min_loan_amount"
              type="number"
              placeholder="e.g., 5000"
              class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Maximum Loan Amount</label>
            <input
              v-model="form.max_loan_amount"
              type="number"
              placeholder="e.g., 500000"
              class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
            />
          </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-gray-700">Repayment Period (Months)</label>
            <input
              v-model="form.repayment_period"
              type="number"
              placeholder="e.g., 12"
              class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Penalty Rate (% per Month)</label>
            <input
              v-model="form.penalty_rate"
              type="number"
              step="0.1"
              placeholder="e.g., 2"
              class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Loan Processing Fee (KES)</label>
          <input
            v-model="form.loan_processing_fee"
            type="number"
            placeholder="e.g., 500"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
          />
        </div>

        <div class="flex justify-end pt-4">
          <button
            type="submit"
            class="px-6 py-2 bg-gradient-to-r from-sky-600 to-orange-500 text-white rounded-lg shadow-md hover:opacity-90 transition"
          >
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
