<template>
  <AppLayout :title="`Edit Dividend ${dividend.dividend_year}`">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Edit Dividend {{ dividend.dividend_year }}
        </h2>
        <Link :href="route('dividends.show', dividend.id)" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"/>
          </svg>
          Back to Dividend
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Warning Notice -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-yellow-700">
                Editing will recalculate all member dividends. Only calculated dividends can be edited.
              </p>
            </div>
          </div>
        </div>

        <!-- Financial Overview -->
        <div class="bg-white shadow-sm rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Overview (Year {{ dividend.dividend_year }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-blue-800">Total Shares</div>
                <div class="text-2xl font-bold text-blue-900">KSh {{ formatCurrency(totalShares) }}</div>
                <div class="text-sm text-blue-600">Active member shares</div>
              </div>
              
              <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-green-800">Net Income</div>
                <div class="text-2xl font-bold text-green-900">KSh {{ formatCurrency(financialData.net_profit) }}</div>
                <div class="text-sm text-green-600">From financial records</div>
              </div>
              
              <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-purple-800">Current Total Dividends</div>
                <div class="text-2xl font-bold text-purple-900">KSh {{ formatCurrency(dividend.total_dividends) }}</div>
                <div class="text-sm text-purple-600">{{ dividend.dividend_rate }}% of shares</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white shadow-sm rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Edit Dividend Details</h3>
            
            <form @submit.prevent="submit" class="space-y-6">
              <!-- Total Profit -->
              <div>
                <InputLabel for="total_profit" value="Total Profit" class="required" />
                <div class="mt-1 relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">KSh</span>
                  </div>
                  <TextInput
                    id="total_profit"
                    v-model="form.total_profit"
                    type="number"
                    step="0.01"
                    min="0"
                    class="pl-12 block w-full"
                    placeholder="0.00"
                    required
                    @input="calculateDividends"
                  />
                </div>
                <InputError :message="form.errors.total_profit" class="mt-2" />
                <p class="mt-1 text-sm text-gray-500">Total profit for year {{ dividend.dividend_year }}</p>
              </div>

              <!-- Dividend Rate -->
              <div>
                <InputLabel for="dividend_rate" value="Dividend Rate (%)" class="required" />
                <div class="mt-1 relative">
                  <TextInput
                    id="dividend_rate"
                    v-model="form.dividend_rate"
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    class="block w-full pr-12"
                    placeholder="0.00"
                    required
                    @input="calculateDividends"
                  />
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">%</span>
                  </div>
                </div>
                <InputError :message="form.errors.dividend_rate" class="mt-2" />
                <p class="mt-1 text-sm text-gray-500">Percentage of shares to be paid as dividends</p>
              </div>

              <!-- Notes -->
              <div>
                <InputLabel for="notes" value="Notes (Optional)" />
                <TextArea
                  id="notes"
                  v-model="form.notes"
                  rows="4"
                  class="mt-1 block w-full"
                  placeholder="Add any additional notes about this dividend calculation..."
                />
                <InputError :message="form.errors.notes" class="mt-2" />
              </div>

              <!-- Calculation Preview -->
              <div v-if="calculationPreview" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Updated Calculation Preview</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div>
                    <div class="text-xs text-gray-500">Total Dividends</div>
                    <div class="text-sm font-semibold text-gray-900">KSh {{ formatCurrency(calculationPreview.total_dividends) }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-500">Members</div>
                    <div class="text-sm font-semibold text-gray-900">{{ calculationPreview.member_count }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-500">Average Dividend</div>
                    <div class="text-sm font-semibold text-gray-900">KSh {{ formatCurrency(calculationPreview.average_dividend) }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-500">Payout Ratio</div>
                    <div class="text-sm font-semibold text-gray-900">{{ calculationPreview.payout_ratio }}%</div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <Link :href="route('dividends.show', dividend.id)" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                  Cancel
                </Link>
                
                <button 
                  type="button" 
                  @click="previewChanges"
                  :disabled="form.processing || !hasChanges"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50"
                >
                  <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"/>
                  </svg>
                  Preview Changes
                </button>
                
                <PrimaryButton 
                  type="submit" 
                  :class="{ 'opacity-25': form.processing }" 
                  :disabled="form.processing || !hasChanges"
                >
                  <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                  </svg>
                  Update Dividend
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>

        <!-- Member Impact Preview -->
        <div v-if="memberImpactPreview" class="bg-white shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Member Impact Preview</h3>
              <span class="text-sm text-gray-500">Showing top 10 members by dividend amount</span>
            </div>
            
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shares</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Dividend</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Dividend</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="member in memberImpactPreview" :key="member.member_id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ member.member_name }}</div>
                      <div class="text-sm text-gray-500">{{ member.membership_id }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      KSh {{ formatCurrency(member.shares_balance) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      KSh {{ formatCurrency(member.current_dividend) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      KSh {{ formatCurrency(member.new_dividend) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getDifferenceClass(member.difference)" class="text-sm font-medium">
                        {{ member.difference >= 0 ? '+' : '' }}KSh {{ formatCurrency(Math.abs(member.difference)) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <ConfirmationModal :show="showConfirmModal" @close="showConfirmModal = false">
      <template #title>Confirm Dividend Update</template>
      <template #content>
        <div class="space-y-4">
          <p class="text-sm text-gray-600">
            Are you sure you want to update the dividend for {{ dividend.dividend_year }}?
          </p>
          
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-blue-900 mb-2">Changes Summary:</h4>
            <div class="text-sm text-blue-800 space-y-1">
              <div v-if="form.total_profit != dividend.total_profit">
                Total Profit: KSh {{ formatCurrency(dividend.total_profit) }} → KSh {{ formatCurrency(form.total_profit) }}
              </div>
              <div v-if="form.dividend_rate != dividend.dividend_rate">
                Dividend Rate: {{ dividend.dividend_rate }}% → {{ form.dividend_rate }}%
              </div>
              <div v-if="calculationPreview">
                Total Dividends: KSh {{ formatCurrency(dividend.total_dividends) }} → KSh {{ formatCurrency(calculationPreview.total_dividends) }}
              </div>
            </div>
          </div>
          
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-yellow-700">
                  This will recalculate all member dividends based on the new parameters.
                </p>
              </div>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <SecondaryButton @click="showConfirmModal = false">Cancel</SecondaryButton>
        <PrimaryButton class="ml-3" @click="confirmUpdate" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          Confirm Update
        </PrimaryButton>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
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

const previewChanges = async () => {
  if (!calculationPreview.value) {
    await calculateDividends()
  }
  
  // Simulate member impact preview (in real implementation, this would be an API call)
  memberImpactPreview.value = generateMemberImpactPreview()
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