<template>
  <AppLayout :breadcrumbs="[
    { title: 'Dividends', href: '/dividends' },
    { title: 'Calculate' }
  ]">

    <Head title="Calculate Dividend" />

    <!-- Processing Loader -->
    <div v-if="form.processing"
      class="fixed inset-0 bg-black/40 dark:bg-black/60 flex items-center justify-center z-50">
      <div class="loader border-4 border-white border-t-transparent rounded-full w-12 h-12 animate-spin"></div>
    </div>

    <!-- FLASH BOX -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
      <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="flashMessage" :class="[
    'mb-4 rounded-md p-4 shadow flex items-center gap-3 border',
    flashType === 'success'
      ? 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-700'
      : 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-700'
  ]">
          <p class="ml-3 text-sm">{{ flashMessage }}</p>

          <button class="ml-auto text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200"
            @click="flashMessage = null">
            ✕
          </button>
        </div>
      </transition>
    </div>

    <!-- HEADER -->
    <div class="flex justify-between items-center mx-[10%] mt-4">
      <h2 class="font-semibold flex items-center text-2xl sm:text-3xl">
        Calculate New Dividend
      </h2>
      <Link :href="route('dividends.index')"
        class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg shadow hover:bg-gray-900 transition">
      <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd"
          d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" />
      </svg>
      Back <span class="max-sm:hidden">&nbsp;to Dividend</span>
      </Link>
    </div>

    <div class="py-6 max-md:px-3">
      <div class="max-w-5xl mx-auto space-y-8">

        <!-- EXISTING DIVIDEND WARNING -->
        <div v-if="existingDividend"
          class="bg-orange-50 dark:bg-orange-900/30 border-l-4 border-orange-500 rounded-md p-4 shadow-sm">
          <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8..." />
            </svg>

            <div>
              <h3 class="text-sm font-semibold text-[#0A1A2F] dark:text-white">
                Existing Dividend Found
              </h3>
              <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">
                A dividend for {{ existingDividend.dividend_year }} already exists.
                <Link :href="route('dividends.show', existingDividend.id)"
                  class="font-medium text-orange-500 underline">
                View existing
                </Link>
              </p>
            </div>
          </div>
        </div>

        <!-- FINANCIAL OVERVIEW -->
        <div class="bg-white dark:bg-[#0a0f1a] shadow-lg rounded-xl border border-gray-100 dark:border-gray-700">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-white mb-6">
              Financial Overview
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

              <!-- TOTAL ACTIVE SHARES -->
              <div class="bg-[#0A1A2F] dark:bg-[#111f38] text-white p-5 rounded-xl shadow-sm">
                <div class="text-sm opacity-90">Total Active Shares</div>
                <div class="text-xl font-bold mt-1">KSh {{ formatCurrency(totalShares) }}</div>
                <div class="text-sm opacity-80 mt-1">{{ activeMembers }} active members</div>
              </div>

              <!-- PROFIT -->
              <div
                class="bg-orange-100 dark:bg-orange-900/40 p-5 rounded-xl border border-orange-200 dark:border-orange-700">
                <div class="text-sm font-medium text-[#0A1A2F] dark:text-white">
                  Available for Dividend
                </div>
                <div class="text-xl font-bold text-orange-600 mt-1">
                  KSh {{ formatCurrency(form.total_profit || 0) }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                  Based on entered profit
                </div>
              </div>

              <!-- CALCULATED DIVIDENDS -->
              <div class="bg-blue-50 dark:bg-blue-900/40 p-5 rounded-xl border border-blue-200 dark:border-blue-700">
                <div class="text-sm font-medium text-[#0A1A2F] dark:text-white">Calculated Dividends</div>
                <div class="text-xl font-bold text-[#0A1A2F] dark:text-white mt-1">
                  KSh {{ formatCurrency(calculatedDividends) }}
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                  At {{ form.dividend_rate || 0 }}%
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- DIVIDEND FORM WRAPPER -->
        <div class="bg-white dark:bg-[#0a0f1a] shadow-lg rounded-xl border border-gray-100 dark:border-gray-700">
          <div class="p-6">

            <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-white mb-6">
              Dividend Calculation
            </h3>

            <form @submit.prevent="submitForm" class="space-y-8">

              <!-- YEAR -->
              <div>
                <InputLabel for="dividend_year" value="Dividend Year *" class="dark:text-gray-200" />
                <TextInput id="dividend_year" type="number"
                  class="mt-1 p-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  v-model="form.dividend_year" required />
                <InputError :message="form.errors.dividend_year" />
              </div>

              <!-- PROFIT -->
              <div>
                <InputLabel for="total_profit" value="Total Profit (KSh) *" class="dark:text-gray-200" />
                <TextInput id="total_profit" type="number"
                  class="mt-1 p-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  v-model="form.total_profit" @input="calculateDividends" />
                <InputError :message="form.errors.total_profit" />
              </div>

              <!-- RATE -->
              <div>
                <InputLabel for="dividend_rate" value="Dividend Rate (%) *" class="dark:text-gray-200" />
                <TextInput id="dividend_rate" type="number" min="0.01" max="9" step="0.01"
                  class="mt-1 p-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  v-model="form.dividend_rate" @input="calculateDividends" />
                <InputError :message="form.errors.dividend_rate" />
                <p class="text-sm text-gray-500 mt-1">Enter a value greater than 0 and maximum 9%</p>
              </div>


              <!-- NOTES -->
              <div>
                <InputLabel for="notes" value="Notes" class="dark:text-gray-200" />
                <TextArea id="notes"
                  class="p-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  v-model="form.notes" />
              </div>

              <!-- SUMMARY -->
              <div v-if="calculationSummary"
                class="bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-700 rounded-xl p-5">
                <h4 class="text-md font-semibold text-[#0A1A2F] dark:text-white mb-3">
                  Calculation Summary
                </h4>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

                  <div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">Total Dividends</div>
                    <div class="text-lg font-bold dark:text-white">
                      KSh {{ formatCurrency(calculationSummary.total_dividends) }}
                    </div>
                  </div>

                  <div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">Members Eligible</div>
                    <div class="text-lg font-bold dark:text-white">
                      {{ calculationSummary.member_count }}
                    </div>
                  </div>

                  <div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">Average Dividend</div>
                    <div class="text-lg font-bold dark:text-white">
                      KSh {{ formatCurrency(calculationSummary.average_dividend) }}
                    </div>
                  </div>

                  <div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">Profit Utilization</div>
                    <div class="text-lg font-bold dark:text-white">
                      {{ profitUtilization }}%
                    </div>
                  </div>

                </div>
              </div>

              <!-- PREVIEW BUTTON -->
              <div class="flex justify-center">
                <button type="button" @click="previewCalculation" :disabled="!canPreview" class="inline-flex items-center px-6 py-3 rounded-lg text-white font-semibold shadow-md
                    bg-[#0A1A2F] dark:bg-[#21395c] hover:bg-[#112C4F] transition
                    disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-[#0A1A2F] disabled:bg-gray-400">
                  Preview Calculation
                </button>
              </div>

              <!-- MEMBER PREVIEW TABLE -->
              <div v-if="memberBreakdown.length"
                class="bg-white dark:bg-[#0a0f1a] border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b bg-gray-50 dark:bg-gray-900 dark:border-gray-700">
                  <h4 class="text-md font-semibold text-[#0A1A2F] dark:text-white">
                    Member Dividend Breakdown (Preview)
                  </h4>
                  <p class="text-sm text-gray-600 dark:text-gray-300">
                    Showing first 10 of {{ memberBreakdown.length }} members
                  </p>
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full text-sm">
                    <thead class="bg-[#0A1A2F] dark:bg-[#21395c] text-white">
                      <tr>
                        <th class="px-4 py-3 text-left">Member</th>
                        <th class="px-4 py-3 text-left">Shares Balance</th>
                        <th class="px-4 py-3 text-left">Dividend Amount</th>
                      </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="member in memberBreakdown.slice(0, 10)" :key="member.member_id"
                        class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                        <td class="px-4 py-3 text-[#0A1A2F] dark:text-white">
                          <div class="font-medium">{{ member.member_name }}</div>
                          <div class="text-gray-500 dark:text-gray-300">
                            {{ member.membership_id }}
                          </div>
                        </td>

                        <td class="px-4 py-3 dark:text-white">
                          KSh {{ formatCurrency(member.shares_balance) }}
                        </td>

                        <td class="px-4 py-3 dark:text-white">
                          KSh {{ formatCurrency(member.dividend_amount) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div v-if="memberBreakdown.length > 10"
                  class="p-3 text-center bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-300 text-sm">
                  ... and {{ memberBreakdown.length - 10 }} more members
                </div>
              </div>

              <!-- ACTION BUTTONS -->
              <div class="flex items-center justify-between pt-4">
                <Link :href="route('dividends.index')"
                  class="px-5 py-3 rounded-lg font-semibold bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-[#0A1A2F] dark:text-white transition">
                Cancel
                </Link>

                <button type="submit" :disabled="form.processing || loading"
                  class="px-6 py-3 bg-orange-500 dark:bg-orange-600 text-white hover:bg-orange-600 dark:hover:bg-orange-700 rounded-md shadow-md">
                  <span v-if="loading">Calculating...</span>
                  <span v-else>Calculate Dividend</span>
                </button>
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
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import axios from 'axios'

import InputLabel from '@/components/InputLabel.vue'
import TextInput from '@/components/TextInput.vue'
import TextArea from '@/components/TextArea.vue'
import InputError from '@/components/InputError.vue'

// FLASH HANDLING
const page = usePage()
const flashMessage = ref(null)
const flashType = ref('success')
const flashBox = ref(null)

watch(
  () => page.props,
  (props) => {
    if (props.flash?.success) {
      flashMessage.value = props.flash.success
      flashType.value = 'success'
    } else if (props.flash?.error) {
      flashMessage.value = props.flash.error
      flashType.value = 'error'
    } else if (props.errors?.error) {
      flashMessage.value = props.errors.error
      flashType.value = 'error'
    }

    if (flashMessage.value) {
      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)

const props = defineProps({
  suggestedYear: Number,
  previousYear: Number,
  existingDividend: Object,
  totalShares: Number,
  activeMembers: Number,
})

// FORM
const form = useForm({
  dividend_year: props.suggestedYear,
  total_profit: 0,
  dividend_rate: 0,
  notes: ''
})

const calculationSummary = ref(null)
const memberBreakdown = ref([])
const loading = ref(false)

const numericTotalShares = computed(() => Number(props.totalShares) || 0)

const calculatedDividends = computed(() => {
  if (!form.dividend_rate || numericTotalShares.value <= 0) return 0
  return (numericTotalShares.value * form.dividend_rate) / 100
})

const profitUtilization = computed(() => {
  if (!form.total_profit || !calculatedDividends.value) return 0
  return ((calculatedDividends.value / form.total_profit) * 100).toFixed(1)
})

const canPreview = computed(() => {
  return form.dividend_year && form.total_profit > 0 && form.dividend_rate > 0
})

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2 }).format(amount || 0)

const calculateDividends = () => {
  if (canPreview.value) {
    calculationSummary.value = {
      total_dividends: calculatedDividends.value,
      member_count: props.activeMembers,
      average_dividend:
        props.activeMembers > 0 ? calculatedDividends.value / props.activeMembers : 0
    }
  }
}

const previewCalculation = async () => {
  if (!canPreview.value) return
  try {
    const res = await axios.post(route('dividends.calculate', form.dividend_year), {
      total_profit: form.total_profit,
      dividend_rate: form.dividend_rate,
    })
    calculationSummary.value = res.data
    memberBreakdown.value = res.data.member_breakdown || []
  } catch (e) {
    console.error(e)
  }
}

const submitForm = () => {
  loading.value = true
  form.post(route('dividends.store'), {
    onFinish: () => (loading.value = false),
    onError: () => (loading.value = false),
  })
}

watch([() => form.total_profit, () => form.dividend_rate], calculateDividends)
</script>

<style scoped>
button:hover {
  cursor: pointer;
}
</style>
