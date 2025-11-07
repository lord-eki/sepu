<script setup lang="ts">
import { reactive } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const form = useForm({
  fiscal_year_start: '2025-01-01',
  tax_rate: '',
  default_account: '',
  currency_format: 'KES',
  rounding: 'nearest',
})

function saveFinancialSettings() {
  // route('settings.financial.update') 
  
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'Financial' }]">
    <Head title="Financial Settings" />

    <div class="bg-white rounded-2xl shadow-md p-6">
      <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">
        Financial Settings
      </h1>

      <form @submit.prevent="saveFinancialSettings" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700">Fiscal Year Start Date</label>
          <input
            v-model="form.fiscal_year_start"
            type="date"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 outline-none"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Default Currency</label>
          <select
            v-model="form.currency_format"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 outline-none"
          >
            <option value="KES">KES (Kenyan Shilling)</option>
            <option value="USD">USD (US Dollar)</option>
            <option value="EUR">EUR (Euro)</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
          <input
            v-model="form.tax_rate"
            type="number"
            step="0.01"
            placeholder="e.g., 5"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 outline-none"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Default Account</label>
          <input
            v-model="form.default_account"
            type="text"
            placeholder="e.g., Main SACCO Account"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 outline-none"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Rounding Method</label>
          <select
            v-model="form.rounding"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 outline-none"
          >
            <option value="nearest">Nearest Whole Number</option>
            <option value="up">Round Up</option>
            <option value="down">Round Down</option>
          </select>
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
