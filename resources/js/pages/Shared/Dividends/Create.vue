<template>
  <AppLayout :breadcrumbs="[
    { title: 'Dividends', href: '/dividends' },
    { title: 'Calculate' }
  ]">
    <h2 class="font-semibold text-xl px-4 sm:px-10 pt-5 text-gray-800 leading-tight">
      Calculate New Dividend
    </h2>

    <div class="py-6 max-md:px-3">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Existing Dividend Warning -->
        <div v-if="existingDividend" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-yellow-800">Existing Dividend Found</h3>
              <div class="mt-2 text-sm text-yellow-700">
                A dividend for {{ existingDividend.dividend_year }} already exists with status "{{
    existingDividend.status
  }}".
                <Link :href="route('dividends.show', existingDividend.id)" class="font-medium underline">
                View existing dividend
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Financial Overview -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-blue-800">Total Active Shares</div>
                <div class="text-lg sm:text-xl font-bold text-blue-900">KSh {{ formatCurrency(totalShares) }}</div>
                <div class="text-sm text-blue-600">{{ activeMembers }} active members</div>
              </div>

              <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-green-800">Available for Dividend</div>
                <div class="text-lg sm:text-xl font-bold text-green-900">KSh {{ formatCurrency(form.total_profit || 0)
                  }}</div>
                <div class="text-sm text-green-600">Based on entered profit</div>
              </div>

              <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-purple-800">Calculated Dividends</div>
                <div class="text-lg sm:text-xl font-bold text-purple-900">KSh {{ formatCurrency(calculatedDividends) }}
                </div>
                <div class="text-sm text-purple-600">At {{ form.dividend_rate || 0 }}% rate</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dividend Calculation Form -->
        <div class="bg-white shadow-sm rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Dividend Calculation</h3>

            <form @submit.prevent="submitForm">
              <div class="space-y-6">
                <!-- Dividend Year -->
                <div>
                  <InputLabel for="dividend_year" value="Dividend Year *" />
                  <TextInput id="dividend_year" type="number" class="mt-1 block w-full" v-model="form.dividend_year"
                    :min="2000" :max="new Date().getFullYear() + 1" required />
                  <InputError class="mt-2" :message="form.errors.dividend_year" />
                  <div class="mt-1 text-sm text-gray-500">
                    Typically the current year for the previous year's profits
                  </div>
                </div>

                <!-- Total Profit -->
                <div>
                  <InputLabel for="total_profit" value="Total Profit Available for Distribution (KSh) *" />
                  <TextInput id="total_profit" type="number" step="0.01" min="0" class="mt-1 block w-full"
                    v-model="form.total_profit" @input="calculateDividends" required />
                  <InputError class="mt-2" :message="form.errors.total_profit" />
                  <div class="mt-1 text-sm text-gray-500">
                    Net profit available for dividend distribution after reserves and expenses
                  </div>
                </div>

                <!-- Dividend Rate -->
                <div>
                  <InputLabel for="dividend_rate" value="Dividend Rate (%) *" />
                  <TextInput id="dividend_rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full"
                    v-model="form.dividend_rate" @input="calculateDividends" required />
                  <InputError class="mt-2" :message="form.errors.dividend_rate" />
                  <div class="mt-1 text-sm text-gray-500">
                    Percentage of share balance to be paid as dividend (e.g., 12% means 12% of each member's shares)
                  </div>
                </div>

                <!-- Notes -->
                <div>
                  <InputLabel for="notes" value="Notes" />
                  <TextArea id="notes" class="mt-1 block w-full" rows="4" v-model="form.notes"
                    placeholder="Additional notes about this dividend calculation..." />
                  <InputError class="mt-2" :message="form.errors.notes" />
                </div>

                <!-- Calculation Summary -->
                <div v-if="calculationSummary" class="bg-gray-50 rounded-lg p-4">
                  <h4 class="text-md font-medium text-gray-900 mb-3">Calculation Summary</h4>
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                      <div class="text-sm font-medium text-gray-700">Total Dividends</div>
                      <div class="text-lg font-bold text-gray-900">KSh {{
    formatCurrency(calculationSummary.total_dividends)
  }}</div>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-700">Members Eligible</div>
                      <div class="text-lg font-bold text-gray-900">{{ calculationSummary.member_count }}</div>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-700">Average Dividend</div>
                      <div class="text-lg font-bold text-gray-900">KSh {{
    formatCurrency(calculationSummary.average_dividend) }}</div>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-700">Profit Utilization</div>
                      <div class="text-lg font-bold text-gray-900">{{ profitUtilization }}%</div>
                    </div>
                  </div>
                </div>

                <!-- Preview Button -->
                <div class="flex justify-center">
                  <button type="button" @click="previewCalculation" :disabled="!canPreview"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-25">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" />
                    </svg>
                    Preview Calculation
                  </button>
                </div>

                <!-- Member Breakdown Preview -->
                <div v-if="memberBreakdown && memberBreakdown.length > 0"
                  class="bg-white border border-gray-200 rounded-lg">
                  <div class="p-4 border-b border-gray-200">
                    <h4 class="text-md font-medium text-gray-900">Member Dividend Breakdown (Preview)</h4>
                    <p class="text-sm text-gray-600">Showing first 10 members - all {{ memberBreakdown.length }} members
                      will receive dividends</p>
                  </div>
                  <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shares Balance
                          </th>
                          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dividend Amount
                          </th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="member in memberBreakdown.slice(0, 10)" :key="member.member_id">
                          <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ member.member_name }}</div>
                            <div class="text-sm text-gray-500">{{ member.membership_id }}</div>
                          </td>
                          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            KSh {{ formatCurrency(member.shares_balance) }}
                          </td>
                          <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            KSh {{ formatCurrency(member.dividend_amount) }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div v-if="memberBreakdown.length > 10" class="p-4 bg-gray-50 text-sm text-gray-600 text-center">
                    ... and {{ memberBreakdown.length - 10 }} more members
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between">
                  <Link :href="route('dividends.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                  Cancel
                  </Link>

                  <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Calculate Dividend
                  </PrimaryButton>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'


const props = defineProps({
  suggestedYear: Number,
  previousYear: Number,
  existingDividend: Object,
  financialData: Object,
  totalShares: Number,
  activeMembers: Number
})

const calculationSummary = ref(null)
const memberBreakdown = ref([])

const form = useForm({
  dividend_year: props.suggestedYear,
  total_profit: 0,
  dividend_rate: 0,
  notes: ''
})

const calculatedDividends = computed(() => {
  if (!form.dividend_rate || !props.totalShares) return 0
  return (props.totalShares * form.dividend_rate) / 100
})

const profitUtilization = computed(() => {
  if (!form.total_profit || !calculatedDividends.value) return 0
  return ((calculatedDividends.value / form.total_profit) * 100).toFixed(1)
})

const canPreview = computed(() => {
  return form.dividend_year && form.total_profit > 0 && form.dividend_rate > 0
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const calculateDividends = () => {
  if (canPreview.value) {
    calculationSummary.value = {
      total_dividends: calculatedDividends.value,
      member_count: props.activeMembers,
      average_dividend: props.activeMembers > 0 ? calculatedDividends.value / props.activeMembers : 0
    }
  }
}

const previewCalculation = async () => {
  if (!canPreview.value) return

  try {
    const response = await axios.post(route('dividends.calculate', form.dividend_year), {
      total_profit: form.total_profit,
      dividend_rate: form.dividend_rate
    })

    calculationSummary.value = response.data
    memberBreakdown.value = response.data.member_breakdown || []
  } catch (error) {
    console.error('Failed to calculate dividend preview:', error)
  }
}

const submitForm = () => {
  form.post(route('dividends.store'))
}

// Watch for changes and auto-calculate
watch([() => form.total_profit, () => form.dividend_rate], calculateDividends)
</script>