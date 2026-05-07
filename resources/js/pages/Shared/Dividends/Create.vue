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
      setTimeout(() => {
        flashMessage.value = null
      }, 5000)
    }
  },
  { immediate: true, deep: true }
)

const props = defineProps({
  suggestedYear: Number,
  previousYear: Number,
  existingDividend: Object,
  totalShareCapital: Number,
  activeMembers: Number,
  settings: Object,
})

// FORM
const form = useForm({
  dividend_year: props.suggestedYear,
  notes: '',
})

const loading = ref(false)

const calculationSummary = ref(null)
const memberBreakdown = ref([])

const dividendRate = computed(() => {
  return props.settings?.share_dividend_rate || 0
})

const canPreview = computed(() => {
  return !!form.dividend_year
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount || 0)
}

const previewCalculation = async () => {
  if (!canPreview.value) return

  loading.value = true

  try {
    const response = await axios.post(route('dividends.preview'), {
      dividend_year: form.dividend_year,
    })

    calculationSummary.value = response.data.summary || {}
    memberBreakdown.value = response.data.preview || []

    flashMessage.value = 'Dividend preview generated successfully.'
    flashType.value = 'success'

  } catch (error) {
    console.error(error)

    flashMessage.value = 'Failed to preview dividend calculation.'
    flashType.value = 'error'

  } finally {
    loading.value = false
  }
}

const submitForm = () => {
  loading.value = true

  form.post(route('dividends.store'), {
    preserveScroll: true,

    onSuccess: () => {
      flashMessage.value = 'Dividend calculated successfully.'
      flashType.value = 'success'
    },

    onError: () => {
      flashMessage.value = 'Failed to calculate dividend.'
      flashType.value = 'error'
    },

    onFinish: () => {
      loading.value = false
    },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: 'Dividends', href: '/dividends' },
    { title: 'Calculate' }
  ]">

    <Head title="Calculate Dividend" />

    <!-- PROCESSING LOADER -->
    <div
      v-if="loading || form.processing"
      class="fixed inset-0 bg-black/40 dark:bg-black/60 flex items-center justify-center z-50"
    >
      <div
        class="loader border-4 border-white border-t-transparent rounded-full w-12 h-12 animate-spin"
      ></div>
    </div>

    <!-- FLASH -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="flashMessage"
          :class="[
            'mb-4 rounded-md p-4 shadow flex items-center gap-3 border',
            flashType === 'success'
              ? 'bg-green-50 border-green-200 text-green-700 dark:bg-green-900 dark:text-green-200 dark:border-green-700'
              : 'bg-red-50 border-red-200 text-red-700 dark:bg-red-900 dark:text-red-200 dark:border-red-700'
          ]"
        >
          <p class="ml-3 text-sm">{{ flashMessage }}</p>

          <button
            class="ml-auto text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200"
            @click="flashMessage = null"
          >
            ✕
          </button>
        </div>
      </transition>
    </div>

    <!-- HEADER -->
    <div class="flex justify-between items-center mx-8 mt-4">
      <h2 class="font-semibold flex items-center text-2xl sm:text-3xl">
        Calculate New Dividend
      </h2>

      <Link
        :href="route('dividends.index')"
        class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg shadow hover:bg-gray-900 transition"
      >
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
          />
        </svg>

        Back <span class="max-sm:hidden">&nbsp;to Dividend</span>
      </Link>
    </div>

    <div class="py-6 max-md:px-3">
      <div class="max-w-5xl mx-auto space-y-8">

        <!-- EXISTING DIVIDEND -->
        <div
          v-if="existingDividend"
          class="bg-orange-50 dark:bg-orange-900/30 border-l-4 border-orange-500 rounded-md py-4 shadow-sm"
        >
          <div class="flex items-center gap-3 px-4">

            <svg
              class="w-6 h-6 text-orange-500"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z"
              />
            </svg>

            <div>
              <h3 class="text-sm font-semibold text-[#0A1A2F] dark:text-white">
                Existing Dividend Found
              </h3>

              <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">
                A dividend for {{ existingDividend.dividend_year }} already exists.

                <Link
                  :href="route('dividends.show', existingDividend.id)"
                  class="font-medium text-orange-500 underline"
                >
                  View existing
                </Link>
              </p>
            </div>
          </div>
        </div>

        <!-- OVERVIEW -->
        <div class="bg-white dark:bg-[#0a0f1a] shadow-lg rounded-xl border border-gray-100 dark:border-gray-700">
          <div class="p-6">

            <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-white mb-6">
              Financial Overview
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

              <!-- TOTAL SHARES -->
              <div class="bg-[#0A1A2F] dark:bg-[#111f38] text-white p-5 rounded-xl shadow-sm">
                <div class="text-sm opacity-90">
                  Total Active Shares
                </div>

                <div class="text-xl font-bold mt-1">
                  KSh {{ formatCurrency(totalShareCapital) }}
                </div>

                <div class="text-sm opacity-80 mt-1">
                  {{ activeMembers }} active members
                </div>
              </div>

              <!-- SHARE DIVIDEND -->
              <div class="bg-blue-50 dark:bg-blue-900/40 p-5 rounded-xl border border-blue-200 dark:border-blue-700">
                <div class="text-sm font-medium text-[#0A1A2F] dark:text-white">
                  Share Dividend Rate
                </div>

                <div class="text-xl font-bold text-[#0A1A2F] dark:text-white mt-1">
                  {{ dividendRate }}%
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                  From SACCO settings
                </div>
              </div>

              <!-- NET PAYABLE -->
              <div class="bg-green-50 dark:bg-green-900/40 p-5 rounded-xl border border-green-200 dark:border-green-700">
                <div class="text-sm font-medium text-[#0A1A2F] dark:text-white">
                  Estimated Net Payable
                </div>

                <div class="text-xl font-bold text-green-600 mt-1">
                  KSh {{ formatCurrency(calculationSummary?.total_net_payable || 0) }}
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                  After deductions & tax
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- FORM -->
        <div class="bg-white dark:bg-[#0a0f1a] shadow-lg rounded-xl border border-gray-100 dark:border-gray-700">
          <div class="p-6">

            <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-white mb-6">
              Dividend Calculation
            </h3>

            <form @submit.prevent="submitForm" class="space-y-8">

              <!-- YEAR -->
              <div>
                <InputLabel
                  for="dividend_year"
                  value="Dividend Year *"
                  class="dark:text-gray-200"
                />

                <TextInput
                  id="dividend_year"
                  type="number"
                  class="mt-1 p-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  v-model="form.dividend_year"
                  required
                />

                <InputError :message="form.errors.dividend_year" />
              </div>

              <!-- SETTINGS -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
                  <div class="text-sm text-gray-500">
                    Share Dividend Rate
                  </div>

                  <div class="text-lg font-bold dark:text-white">
                    {{ settings.share_dividend_rate }}%
                  </div>
                </div>

                <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
                  <div class="text-sm text-gray-500">
                    Deposit Interest Rate
                  </div>

                  <div class="text-lg font-bold dark:text-white">
                    {{ settings.deposit_interest_rate }}%
                  </div>
                </div>

                <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
                  <div class="text-sm text-gray-500">
                    Tax Rate
                  </div>

                  <div class="text-lg font-bold dark:text-white">
                    {{ settings.tax_rate }}%
                  </div>
                </div>

              </div>

              <!-- NOTES -->
              <div>
                <InputLabel
                  for="notes"
                  value="Notes"
                  class="dark:text-gray-200"
                />

                <TextArea
                  id="notes"
                  class="p-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                  v-model="form.notes"
                />
              </div>

              <!-- SUMMARY -->
              <div
                v-if="calculationSummary"
                class="bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-700 rounded-xl p-5"
              >
                <h4 class="text-md font-semibold text-[#0A1A2F] dark:text-white mb-3">
                  Calculation Summary
                </h4>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

                  <div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                      Share Dividends
                    </div>

                    <div class="text-lg font-bold dark:text-white">
                      KSh {{ formatCurrency(calculationSummary.total_share_dividends) }}
                    </div>
                  </div>

                  <div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                      Deposit Interest
                    </div>

                    <div class="text-lg font-bold dark:text-white">
                      KSh {{ formatCurrency(calculationSummary.total_deposit_interest) }}
                    </div>
                  </div>

                  <div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                      Tax
                    </div>

                    <div class="text-lg font-bold dark:text-white">
                      KSh {{ formatCurrency(calculationSummary.total_tax) }}
                    </div>
                  </div>

                  <div>
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                      Net Payable
                    </div>

                    <div class="text-lg font-bold text-green-600">
                      KSh {{ formatCurrency(calculationSummary.total_net_payable) }}
                    </div>
                  </div>

                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 mt-4">
                  Processing fees and excise duties are automatically deducted during calculation.
                </div>
              </div>

              <!-- PREVIEW BUTTON -->
              <div class="flex justify-center">
                <button
                  type="button"
                  @click="previewCalculation"
                  :disabled="!canPreview || loading"
                  class="inline-flex items-center px-6 py-3 rounded-lg text-white font-semibold shadow-md
                  bg-[#0A1A2F] dark:bg-[#21395c] hover:bg-[#112C4F] transition
                  disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  Preview Calculation
                </button>
              </div>

              <!-- PREVIEW TABLE -->
              <div
                v-if="memberBreakdown.length"
                class="bg-white dark:bg-[#0a0f1a] border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden"
              >
                <div class="p-4 border-b bg-gray-50 dark:bg-gray-900 dark:border-gray-700">
                  <h4 class="text-md font-semibold text-[#0A1A2F] dark:text-white">
                    Member Dividend Breakdown (Preview)
                  </h4>
                </div>

                <div class="overflow-x-auto">

                  <table class="min-w-full text-sm">

                    <thead class="bg-[#0A1A2F] dark:bg-[#21395c] text-white">
                      <tr>
                        <th class="px-4 py-3 text-left">Member</th>
                        <th class="px-4 py-3 text-left">Share Dividend</th>
                        <th class="px-4 py-3 text-left">Deposit Interest</th>
                        <th class="px-4 py-3 text-left">Net Payable</th>
                      </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

                      <tr
                        v-for="member in memberBreakdown.slice(0, 10)"
                        :key="member.member_id"
                      >
                        <td class="px-4 py-3">
                          <div class="font-medium">
                            {{ member.member_name }}
                          </div>

                          <div class="text-gray-500">
                            {{ member.membership_id }}
                          </div>
                        </td>

                        <td class="px-4 py-3">
                          KSh {{ formatCurrency(member.share_dividend) }}
                        </td>

                        <td class="px-4 py-3">
                          KSh {{ formatCurrency(member.deposit_interest) }}
                        </td>

                        <td class="px-4 py-3 font-semibold text-green-600">
                          KSh {{ formatCurrency(member.net_payable) }}
                        </td>
                      </tr>

                    </tbody>
                  </table>
                </div>
              </div>

              <!-- ACTIONS -->
              <div class="flex items-center justify-between pt-4">

                <Link
                  :href="route('dividends.index')"
                  class="px-5 py-3 rounded-lg font-semibold bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 text-[#0A1A2F] dark:text-white transition"
                >
                  Cancel
                </Link>

                <button
                  type="submit"
                  :disabled="form.processing || loading"
                  class="px-6 py-3 bg-orange-500 dark:bg-orange-600 text-white hover:bg-orange-600 dark:hover:bg-orange-700 rounded-md shadow-md"
                >
                  <span v-if="loading">
                    Calculating...
                  </span>

                  <span v-else>
                    Calculate Dividend
                  </span>
                </button>

              </div>

            </form>
          </div>
        </div>

      </div>
    </div>

  </AppLayout>
</template>