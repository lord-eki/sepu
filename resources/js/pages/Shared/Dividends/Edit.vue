<template>
  <AppLayout :breadcrumbs="[
    { title: 'Dividends', href: '/dividends' },
    { title: `Edit Dividend ${dividend.dividend_year}` }
  ]">
 <Head title="Edit Dividend" />
    <!-- HEADER -->
    <div class="flex justify-between items-center mx-6 mt-4">
      <h2 class="font-semibold flex items-center text-2xl sm:text-3xl text-[#0A1A2F] dark:text-gray-100">
        <span>Edit Dividend</span>&nbsp;
        <span class="text-xl text-orange-500 text-center">({{ dividend.dividend_year }})</span>
      </h2>

      <Link
        :href="route('dividends.show', dividend.id)"
        class="inline-flex items-center px-4 py-2 bg-[#0A1A2F] dark:bg-gray-800 text-white rounded-lg shadow hover:bg-[#112C4F] dark:hover:bg-gray-700 transition"
      >
        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd"
            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" />
        </svg>
        Back <span class="max-sm:hidden">&nbsp;to Dividend</span>
      </Link>
    </div>

    <div class="space-y-6 m-4 mt-6 sm:m-8">

      <!-- WARNING -->
      <div class="bg-orange-50 dark:bg-orange-900/20 border-l-4 border-[#F97316] rounded-lg p-3 sm:p-6 shadow-sm flex items-start gap-3">
        <svg class="sm:w-5 sm:h-5 w-1/8 h-1/8 text-[#F97316]" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd"
            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
        </svg>
        <p class="text-xs sm:text-sm text-[#0A1A2F] dark:text-gray-300">
          Editing will recalculate all member dividends. Only calculated dividends can be edited.
        </p>
      </div>

      <!-- FINANCIAL OVERVIEW -->
      <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-gray-100 mb-4">
          Financial Overview (Year {{ dividend.dividend_year }})
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <div class="bg-[#0A1A2F] dark:bg-gray-800 text-white p-5 rounded-lg shadow">
            <div class="text-sm opacity-90">Total Shares</div>
            <div class="text-xl sm:text-2xl font-bold mt-1">KSh {{ formatCurrency(totalShares) }}</div>
            <div class="text-sm opacity-80 mt-1">Active member shares</div>
          </div>

          <div class="bg-orange-100 dark:bg-orange-900/30 p-5 rounded-lg border border-orange-200 dark:border-orange-700">
            <div class="text-sm font-medium text-[#0A1A2F] dark:text-gray-100">Net Income</div>
            <div class="text-xl sm:text-2xl font-bold text-[#F97316] mt-1">
              KSh {{ formatCurrency(financialData.net_profit) }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">From financial records</div>
          </div>

          <div class="bg-blue-50 dark:bg-blue-900/30 p-5 rounded-lg border border-blue-100 dark:border-blue-700">
            <div class="text-sm font-medium text-[#0A1A2F] dark:text-gray-100">Current Total Dividends</div>
            <div class="text-xl sm:text-2xl font-bold text-[#0A1A2F] dark:text-gray-100 mt-1">
              KSh {{ formatCurrency(dividend.total_dividends) }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
              {{ dividend.dividend_rate }}% of shares
            </div>
          </div>

        </div>
      </div>

      <!-- EDIT FORM -->
      <div class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-100 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-gray-100 mb-6">Edit Dividend Details</h3>

        <form @submit.prevent="submit" class="space-y-6">

          <!-- TOTAL PROFIT -->
          <div>
            <InputLabel for="total_profit" value="Total Profit" class="text-[#0A1A2F] dark:text-gray-100" />
            <div class="mt-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 dark:text-gray-400">KSh</div>

              <TextInput
                id="total_profit"
                v-model="form.total_profit"
                type="number"
                step="0.01"
                min="1"
                class="pl-12 block p-2 w-full rounded-lg border border-gray-300 dark:border-gray-600
                       bg-white dark:bg-gray-800 text-[#0A1A2F] dark:text-gray-100
                       focus:ring-[#0A1A2F] dark:focus:ring-gray-300
                       focus:border-[#0A1A2F] dark:focus:border-gray-300"
                placeholder="0.00"
                required
                @input="calculateDividends"
              />
            </div>
            <InputError :message="form.errors.total_profit" class="mt-2" />
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total profit for year {{ dividend.dividend_year }}</p>
          </div>

          <!-- DIVIDEND RATE -->
          <div>
            <InputLabel for="dividend_rate" value="Dividend Rate (%)" class="text-[#0A1A2F] dark:text-gray-100" />
            <div class="mt-1 relative">

              <TextInput
                id="dividend_rate"
                v-model="form.dividend_rate"
                type="number"
                step="0.01"
                min="1"
                max="9"
                class="block w-full p-2 rounded-lg border border-gray-300 dark:border-gray-600
                       bg-white dark:bg-gray-800 text-[#0A1A2F] dark:text-gray-100
                       focus:ring-[#0A1A2F] dark:focus:ring-gray-300
                       focus:border-[#0A1A2F] dark:focus:border-gray-300"
                placeholder="0.00"
                required
                @input="calculateDividends"
              />

              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500 dark:text-gray-400">%</div>
            </div>

            <InputError :message="form.errors.dividend_rate" class="mt-2" />
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Percentage of shares to be paid as dividends (max 9%)</p>
          </div>

          <!-- NOTES -->
          <div>
            <InputLabel for="notes" value="Notes (Optional)" class="text-[#0A1A2F] dark:text-gray-100" />
            <TextArea
              id="notes"
              v-model="form.notes"
              rows="4"
              class="mt-1 block w-full rounded-lg p-2 border border-gray-300 dark:border-gray-600
                     bg-white dark:bg-gray-800 text-[#0A1A2F] dark:text-gray-100
                     focus:ring-[#0A1A2F] dark:focus:ring-gray-300
                     focus:border-[#0A1A2F] dark:focus:border-gray-300"
              placeholder="Add any additional notes..."
            />
            <InputError :message="form.errors.notes" class="mt-2" />
          </div>

          <!-- CALCULATION PREVIEW -->
          <div v-if="calculationPreview" class="bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-700 rounded-lg p-4">
            <h4 class="text-sm font-medium text-[#0A1A2F] dark:text-gray-100 mb-3">Updated Calculation Preview</h4>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-[#0A1A2F] dark:text-gray-100">
              <div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Total Dividends</div>
                <div class="text-sm font-semibold">KSh {{ formatCurrency(calculationPreview.total_dividends) }}</div>
              </div>
              <div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Members</div>
                <div class="text-sm font-semibold">{{ calculationPreview.member_count }}</div>
              </div>
              <div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Average Dividend</div>
                <div class="text-sm font-semibold">KSh {{ formatCurrency(calculationPreview.average_dividend) }}</div>
              </div>
              <div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Payout Ratio</div>
                <div class="text-sm font-semibold">{{ calculationPreview.payout_ratio }}%</div>
              </div>
            </div>
          </div>

          <!-- ACTION BUTTONS -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
            <Link
              :href="route('dividends.show', dividend.id)"
              class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-[#0A1A2F] dark:text-gray-100 rounded-lg shadow hover:bg-gray-300 dark:hover:bg-gray-600 transition"
            >
              Cancel
            </Link>

            <button
              type="button"
              @click="previewChanges"
              :disabled="form.processing || !hasChanges || previewLoading"
              class="inline-flex items-center px-4 py-2 bg-[#0A1A2F] dark:bg-gray-800 text-white rounded-lg shadow 
                     hover:bg-[#112C4F] dark:hover:bg-gray-700 transition disabled:opacity-50"
            >
              <span v-if="previewLoading" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                Loading...
              </span>
              <span v-else>Preview Changes</span>
            </button>

            <button
              type="submit"
              :disabled="form.processing || !hasChanges"
              class="bg-[#F97316] hover:bg-[#ea6a0f] text-white rounded-lg px-4 py-2 shadow transition
                     disabled:opacity-50"
            >
              Update Dividend
            </button>
          </div>

        </form>
      </div>

      <!-- MEMBER IMPACT PREVIEW -->
      <div
        v-if="memberImpactPreview"
        ref="memberImpactRef"
        class="bg-white dark:bg-gray-900 shadow-lg rounded-xl border border-gray-100 dark:border-gray-700 p-6 mt-6"
      >
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2 md:gap-0">
          <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-gray-100">Member Impact Preview</h3>
          <span class="text-sm text-gray-500 dark:text-gray-400">Showing top 10 members by dividend amount</span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-[#0A1A2F] text-white dark:bg-gray-800">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Member</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Shares</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Current Dividend</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">New Dividend</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Change</th>
              </tr>
            </thead>

            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr
                v-for="member in memberImpactPreview"
                :key="member.member_id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800 transition"
              >
                <td class="px-4 py-2 whitespace-nowrap">
                  <div class="text-sm font-medium text-[#0A1A2F] dark:text-gray-100">{{ member.member_name }}</div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">{{ member.membership_id }}</div>
                </td>

                <td class="px-4 py-2 whitespace-nowrap text-sm text-[#0A1A2F] dark:text-gray-100">
                  KSh {{ formatCurrency(member.shares_balance) }}
                </td>

                <td class="px-4 py-2 whitespace-nowrap text-sm text-[#0A1A2F] dark:text-gray-100">
                  KSh {{ formatCurrency(member.current_dividend) }}
                </td>

                <td class="px-4 py-2 whitespace-nowrap text-sm text-[#0A1A2F] dark:text-gray-100">
                  KSh {{ formatCurrency(member.new_dividend) }}
                </td>

                <td class="px-4 py-2 whitespace-nowrap">
                  <span :class="getDifferenceClass(member.difference) + ' text-sm font-medium'">
                    {{ member.difference >= 0 ? '+' : '' }}KSh {{ formatCurrency(Math.abs(member.difference)) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- CONFIRMATION MODAL -->
      <div v-if="showConfirmModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 w-full max-w-lg rounded-xl shadow-lg p-6 animate-fadeIn">
          <h3 class="text-lg font-semibold text-[#0A1A2F] dark:text-gray-100 mb-4">Confirm Dividend Update</h3>

          <div class="space-y-4 text-sm text-[#0A1A2F] dark:text-gray-200">

            <p>
              Are you sure you want to update the dividend for
              <strong>{{ dividend.dividend_year }}</strong>?
            </p>

            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
              <h4 class="text-sm font-medium text-[#0A1A2F] dark:text-gray-100 mb-2">Changes Summary:</h4>

              <div class="space-y-1 text-sm">
                <div v-if="form.total_profit != dividend.total_profit">
                  Total Profit:
                  KSh {{ formatCurrency(dividend.total_profit) }}
                  →
                  <strong>KSh {{ formatCurrency(form.total_profit) }}</strong>
                </div>

                <div v-if="form.dividend_rate != dividend.dividend_rate">
                  Dividend Rate:
                  {{ dividend.dividend_rate }}%
                  →
                  <strong>{{ form.dividend_rate }}%</strong>
                </div>

                <div v-if="calculationPreview">
                  Total Dividends:
                  KSh {{ formatCurrency(dividend.total_dividends) }}
                  →
                  <strong>KSh {{ formatCurrency(calculationPreview.total_dividends) }}</strong>
                </div>
              </div>
            </div>

            <div class="bg-orange-50 dark:bg-orange-900/20 border-l-4 border-[#F97316] rounded-lg p-4 flex gap-3">
              <svg class="w-5 h-5 text-[#F97316]" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
              </svg>
              <p class="dark:text-gray-200">
                This will recalculate all member dividends based on the new parameters.
              </p>
            </div>

          </div>

          <div class="mt-6 flex justify-end gap-3">
            <button
              @click="showConfirmModal = false"
              class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-sm"
            >
              Cancel
            </button>

            <button
              @click="confirmUpdate"
              :disabled="form.processing"
              class="px-4 py-2 rounded-lg bg-[#0A1A2F] dark:bg-gray-800 text-white text-sm font-semibold disabled:opacity-50"
            >
              <span v-if="form.processing">Processing...</span>
              <span v-else>Confirm Update</span>
            </button>
          </div>

        </div>
      </div>

    </div>
  </AppLayout>
</template>


<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { Link, useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputLabel from '@/components/InputLabel.vue'
import TextInput from '@/components/TextInput.vue'
import TextArea from '@/components/TextArea.vue'
import InputError from '@/components/InputError.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'

const props = defineProps({
  dividend: Object,
  financialData: Object,
  totalShares: Number
})

const showConfirmModal = ref(false)
const calculationPreview = ref(null)
const memberImpactPreview = ref(null)
const previewLoading = ref(false)


const form = useForm({
  total_profit: props.dividend.total_profit,
  dividend_rate: props.dividend.dividend_rate,
  notes: props.dividend.notes || ''
})

const hasChanges = computed(() => {
  return form.total_profit != props.dividend.total_profit ||
    form.dividend_rate != props.dividend.dividend_rate ||
    form.notes != (props.dividend.notes || '')
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const memberImpactRef = ref(null)

const previewChanges = async () => {
  previewLoading.value = true   // START LOADING

  if (!calculationPreview.value) {
    await calculateDividends()
  }

  // Simulate or fetch real preview
  memberImpactPreview.value = generateMemberImpactPreview()

  // scroll into view
  nextTick(() => {
    if (memberImpactRef.value) {
      memberImpactRef.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }
  })

  previewLoading.value = false   // STOP LOADING
}


const getDifferenceClass = (difference) => {
  if (difference > 0) return 'text-green-600'
  if (difference < 0) return 'text-red-600'
  return 'text-gray-600'
}

const calculateDividends = async () => {
  if (!form.total_profit || !form.dividend_rate || !props.totalShares) {
    calculationPreview.value = null
    return
  }

  try {
    const response = await fetch(route('dividends.calculate', props.dividend.dividend_year), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        total_profit: parseFloat(form.total_profit),
        dividend_rate: parseFloat(form.dividend_rate)
      })
    })

    if (response.ok) {
      const data = await response.json()
      calculationPreview.value = {
        total_dividends: data.total_dividends,
        member_count: data.member_count,
        average_dividend: data.average_dividend,
        payout_ratio: form.total_profit > 0 ? Math.round((data.total_dividends / form.total_profit) * 100) : 0
      }
    }
  } catch (error) {
    console.error('Error calculating dividends:', error)
  }
}

const generateMemberImpactPreview = () => {
  // This would typically come from the server
  // For now, we'll simulate some sample data
  const sampleMembers = [
    { member_id: 1, member_name: 'John Doe', membership_id: 'MEM001', shares_balance: 50000 },
    { member_id: 2, member_name: 'Jane Smith', membership_id: 'MEM002', shares_balance: 35000 },
    { member_id: 3, member_name: 'Bob Johnson', membership_id: 'MEM003', shares_balance: 75000 },
    { member_id: 4, member_name: 'Alice Brown', membership_id: 'MEM004', shares_balance: 45000 },
    { member_id: 5, member_name: 'Charlie Wilson', membership_id: 'MEM005', shares_balance: 60000 }
  ]

  return sampleMembers.map(member => {
    const currentDividend = (member.shares_balance * props.dividend.dividend_rate) / 100
    const newDividend = (member.shares_balance * form.dividend_rate) / 100
    const difference = newDividend - currentDividend

    return {
      ...member,
      current_dividend: currentDividend,
      new_dividend: newDividend,
      difference: difference
    }
  })
}

const submit = () => {
  if (!hasChanges.value) return
  showConfirmModal.value = true
}

const confirmUpdate = () => {
  form.put(route('dividends.update', props.dividend.id), {
    onSuccess: () => {
      showConfirmModal.value = false
    }
  })
}

// Watch for changes and recalculate
watch([() => form.total_profit, () => form.dividend_rate], () => {
  memberImpactPreview.value = null
  calculateDividends()
}, { debounce: 500 })

</script>

<style scoped>
button:hover {
  cursor: pointer;
}
</style>