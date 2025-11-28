<template>
  <AppLayout :breadcrumbs="[
    { title: 'Dividends', href: '/dividends' },
    { title: 'Calculate' }
  ]">

    <Head title="Dividends" />
    <div v-if="form.processing" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
      <div class="loader border-4 border-white border-t-transparent rounded-full w-12 h-12 animate-spin"></div>
    </div>

    <!-- Flash Messages -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
      <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="flashMessage" class="flex gap-3" :class="[
    flashType === 'success'
      ? 'bg-green-50 border border-green-200 text-green-700'
      : 'bg-red-50 border border-red-200 text-red-700',
    'mb-4 rounded-md p-4 shadow flex items-center'
  ]">
          <component :is="flashType === 'success' ? CheckCircle : AlertCircle" class="h-5 w-5"
            :class="flashType === 'success' ? 'text-green-600' : 'text-red-600'" />
          <p class="ml-3 text-sm">{{ flashMessage }}</p>
          <button type="button" class="ml-auto text-gray-500 hover:text-gray-700" @click="flashMessage = null">
            ✕
          </button>
        </div>
      </transition>
    </div>


    <!-- PAGE TITLE -->
    <h2 class="font-semibold text-2xl px-4 sm:px-10 pt-5 text-[#0A1A2F]">
      Calculate New Dividend
    </h2>

    <div class="py-6 max-md:px-3">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

        <!-- Existing Dividend Warning -->
        <div v-if="existingDividend" class="bg-orange-50 border-l-4 border-[#F97316] rounded-md p-4 shadow-sm">
          <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-[#F97316]" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 6a1 1 0 112 0v4a1 1 0 11-2 0V6zm1 9a1 1 0 100-2 1 1 0 000 2z" />
            </svg>

            <div>
              <h3 class="text-sm font-semibold text-[#0A1A2F]">Existing Dividend Found</h3>
              <p class="text-sm text-gray-700 mt-1">
                A dividend for {{ existingDividend.dividend_year }} already exists ({{ existingDividend.status }}).
                <Link :href="route('dividends.show', existingDividend.id)" class="font-medium text-[#F97316] underline">
                View existing dividend
                </Link>
              </p>
            </div>
          </div>
        </div>

        <!-- Financial Overview -->
        <div class="bg-white shadow-lg rounded-xl border border-gray-100">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-[#0A1A2F] mb-6">Financial Overview</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Total Shares -->
              <div class="bg-[#0A1A2F] text-white p-5 rounded-xl shadow-sm">
                <div class="text-sm opacity-90">Total Active Shares</div>
                <div class="text-xl font-bold mt-1">KSh {{ formatCurrency(totalShares) }}</div>
                <div class="text-sm opacity-80 mt-1">{{ activeMembers }} active members</div>
              </div>

              <!-- Profit -->
              <div class="bg-orange-100 p-5 rounded-xl border border-orange-200">
                <div class="text-sm font-medium text-[#0A1A2F]">Available for Dividend</div>
                <div class="text-xl font-bold text-[#F97316] mt-1">
                  KSh {{ formatCurrency(form.total_profit || 0) }}
                </div>
                <div class="text-sm text-gray-600 mt-1">Based on entered profit</div>
              </div>

              <!-- Calculated Dividends -->
              <div class="bg-blue-50 p-5 rounded-xl border border-blue-100">
                <div class="text-sm font-medium text-[#0A1A2F]">Calculated Dividends</div>
                <div class="text-xl font-bold text-[#0A1A2F] mt-1">
                  KSh {{ formatCurrency(calculatedDividends) }}
                </div>
                <div class="text-sm text-gray-600 mt-1">
                  At {{ form.dividend_rate || 0 }}%
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- DIVIDEND FORM -->
        <div class="bg-white shadow-lg rounded-xl border border-gray-100">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-[#0A1A2F] mb-6">Dividend Calculation</h3>

            <form @submit.prevent="submitForm" class="space-y-8">

              <!-- YEAR -->
              <div>
                <InputLabel for="dividend_year" value="Dividend Year *" class="text-[#0A1A2F]" />
                <TextInput id="dividend_year" type="number"
                  class="mt-1 border border-gray-50 p-2 rounded-md block w-full" v-model="form.dividend_year"
                  :min="2000" :max="new Date().getFullYear() + 1" required />
                <InputError class="mt-2" :message="form.errors.dividend_year" />
                <p class="text-sm text-gray-500 mt-1">
                  Typically the current year for the previous year's profits.
                </p>
              </div>

              <!-- PROFIT -->
              <div>
                <InputLabel for="total_profit" value="Total Profit Available (KSh) *" class="text-[#0A1A2F]" />
                <TextInput id="total_profit" type="number" step="0.01" min="1"
                  class="mt-1 border border-gray-50 p-2 rounded-md block w-full" v-model="form.total_profit"
                  @input="calculateDividends" required />
                <InputError class="mt-2" :message="form.errors.total_profit" />
              </div>

              <!-- RATE -->
              <div>
                <InputLabel for="dividend_rate" value="Dividend Rate (%) *" class="text-[#0A1A2F]" />
                <TextInput id="dividend_rate" type="number" step="0.01" min="1" max="100"
                  class="mt-1 border border-gray-50 p-2 rounded-md block w-full" v-model="form.dividend_rate"
                  @input="calculateDividends" required />
                <InputError class="mt-2" :message="form.errors.dividend_rate" />
              </div>

              <!-- NOTES -->
              <div>
                <InputLabel for="notes" value="Notes" class="text-[#0A1A2F]" />
                <TextArea id="notes" class="mt-1 block w-full border border-gray-50 p-2 rounded-lg" :rows="4"
                  v-model="form.notes" placeholder="Additional notes..." />

              </div>

              <!-- SUMMARY -->
              <div v-if="calculationSummary" class="bg-blue-50 border border-blue-100 rounded-xl p-5">
                <h4 class="text-md font-semibold text-[#0A1A2F] mb-3">Calculation Summary</h4>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                  <div>
                    <div class="text-sm text-gray-700">Total Dividends</div>
                    <div class="text-lg font-bold">
                      KSh {{ formatCurrency(calculationSummary.total_dividends) }}
                    </div>
                  </div>

                  <div>
                    <div class="text-sm text-gray-700">Members Eligible</div>
                    <div class="text-lg font-bold">{{ calculationSummary.member_count }}</div>
                  </div>

                  <div>
                    <div class="text-sm text-gray-700">Average Dividend</div>
                    <div class="text-lg font-bold">
                      KSh {{ formatCurrency(calculationSummary.average_dividend) }}
                    </div>
                  </div>

                  <div>
                    <div class="text-sm text-gray-700">Profit Utilization</div>
                    <div class="text-lg font-bold">{{ profitUtilization }}%</div>
                  </div>
                </div>
              </div>

              <!-- PREVIEW BUTTON -->
              <div class="flex justify-center">
                <button type="button" @click="previewCalculation" :disabled="!canPreview" class="inline-flex items-center px-6 py-3 rounded-lg text-white font-semibold shadow-md
                    bg-[#0A1A2F] hover:bg-[#112C4F] transition disabled:opacity-30">
                  Preview Calculation
                </button>
              </div>

              <!-- MEMBER PREVIEW TABLE -->
              <div v-if="memberBreakdown && memberBreakdown.length > 0"
                class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b bg-gray-50">
                  <h4 class="text-md font-semibold text-[#0A1A2F]">
                    Member Dividend Breakdown (Preview)
                  </h4>
                  <p class="text-sm text-gray-500">
                    Showing first 10 of {{ memberBreakdown.length }} members
                  </p>
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full text-sm">
                    <thead class="bg-[#0A1A2F] text-white">
                      <tr>
                        <th class="px-4 py-3 text-left">Member</th>
                        <th class="px-4 py-3 text-left">Shares Balance</th>
                        <th class="px-4 py-3 text-left">Dividend Amount</th>
                      </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                      <tr v-for="member in memberBreakdown.slice(0, 10)" :key="member.member_id">
                        <td class="px-4 py-3">
                          <div class="font-medium text-[#0A1A2F]">
                            {{ member.member_name }}
                          </div>
                          <div class="text-gray-500">{{ member.membership_id }}</div>
                        </td>

                        <td class="px-4 py-3">
                          KSh {{ formatCurrency(member.shares_balance) }}
                        </td>

                        <td class="px-4 py-3">
                          KSh {{ formatCurrency(member.dividend_amount) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div v-if="memberBreakdown.length > 10" class="p-3 text-center text-gray-600 bg-gray-50 text-sm">
                  ... and {{ memberBreakdown.length - 10 }} more members
                </div>
              </div>

              <!-- ACTION BUTTONS -->
              <div class="flex items-center justify-between pt-4">
                <Link :href="route('dividends.index')"
                  class="px-5 py-3 rounded-lg font-semibold bg-gray-200 hover:bg-gray-300 text-[#0A1A2F] transition">
                Cancel
                </Link>

                <button type="submit" :disabled="form.processing || loading"
                  class="px-6 py-3 bg-orange-500 text-white hover:bg-orange-600 hover:cursor-pointer rounded-md shadow-md">
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
// import PrimaryButton from '@/components/PrimaryButton.vue'


// Flash handling
const page = usePage()
const flash = computed(() => page.props?.flash || {})

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
      window.scrollTo({ top: 0, behavior: 'smooth' })
      flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)

const props = defineProps({
  suggestedYear: Number,
  previousYear: Number,
  existingDividend: Object,
  financialData: Object,
  totalShares: [Number, String],
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

const numericTotalShares = computed(() => {
  const n = Number(props.totalShares)
  return isNaN(n) ? 0 : n
})


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

const loading = ref(false)

const submitForm = () => {
  loading.value = true
  form.post(route('dividends.store'), {
    onFinish: () => {
      loading.value = false
    },
    onError: () => {
      loading.value = false
    }
  })
}



// Watch for changes and auto-calculate
watch([() => form.total_profit, () => form.dividend_rate], calculateDividends)
</script>