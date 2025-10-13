<template>
  <AppLayout :title="`Dividend ${dividend.dividend_year}`">
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dividend {{ dividend.dividend_year }}
          </h2>
          <p class="mt-1 text-sm text-gray-600">
            Status: <span :class="getStatusClass(dividend.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
              {{ formatStatus(dividend.status) }}
            </span>
          </p>
        </div>
        
        <div class="flex space-x-3">
          <Link :href="route('dividends.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"/>
            </svg>
            Back to Dividends
          </Link>
          
          <Link v-if="dividend.status === 'calculated'" :href="route('dividends.edit', dividend.id)" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
            </svg>
            Edit
          </Link>
          
          <button v-if="canApprove" @click="showApproveModal = true" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
            </svg>
            Approve
          </button>
          
          <button v-if="canDistribute" @click="showDistributeModal = true" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM3 4a1 1 0 011-1h1a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM8 4a1 1 0 011-1h6a1 1 0 011 1v3a1 1 0 01-1 1H9a1 1 0 01-1-1V4z"/>
            </svg>
            Distribute
          </button>

          <button v-if="dividend.status === 'distributed'" @click="showReverseModal = true" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
            </svg>
            Reverse Distribution
          </button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Dividend Overview -->
        <div class="bg-white shadow-sm rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dividend Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-blue-800">Total Profit</div>
                <div class="text-2xl font-bold text-blue-900">KSh {{ formatCurrency(dividend.total_profit) }}</div>
              </div>
              
              <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-green-800">Dividend Rate</div>
                <div class="text-2xl font-bold text-green-900">{{ dividend.dividend_rate }}%</div>
              </div>
              
              <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-purple-800">Total Dividends</div>
                <div class="text-2xl font-bold text-purple-900">KSh {{ formatCurrency(dividend.total_dividends) }}</div>
              </div>
              
              <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-yellow-800">Profit Utilization</div>
                <div class="text-2xl font-bold text-yellow-900">{{ profitUtilization }}%</div>
                <div class="text-sm text-yellow-600">of total profit</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="bg-white shadow-sm rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Member Statistics</h3>
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Total Members</span>
                  <span class="font-semibold">{{ stats.total_members }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Members Paid</span>
                  <span class="font-semibold text-green-600">{{ stats.members_paid }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Members Pending</span>
                  <span class="font-semibold text-yellow-600">{{ stats.members_pending }}</span>
                </div>
                <div class="border-t pt-2">
                  <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-900">Average Dividend</span>
                    <span class="font-bold">KSh {{ formatCurrency(averageDividend) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white shadow-sm rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Statistics</h3>
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Total Distributed</span>
                  <span class="font-semibold text-green-600">KSh {{ formatCurrency(stats.total_paid) }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Pending Distribution</span>
                  <span class="font-semibold text-yellow-600">KSh {{ formatCurrency(stats.total_pending) }}</span>
                </div>
                <div class="border-t pt-2">
                  <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-900">Distribution Progress</span>
                    <span class="font-bold">{{ distributionProgress }}%</span>
                  </div>
                  <div class="mt-2">
                    <div class="bg-gray-200 rounded-full h-2">
                      <div 
                        class="bg-green-500 h-2 rounded-full transition-all duration-300"
                        :style="{ width: distributionProgress + '%' }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white shadow-sm rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dividend Timeline</h3>
            <div class="flow-root">
              <ul class="-mb-8">
                <li>
                  <div class="relative pb-8">
                    <div v-if="dividend.approval_date" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                    <div class="relative flex space-x-3">
                      <div>
                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
                          </svg>
                        </span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5">
                        <div>
                          <p class="text-sm text-gray-500">
                            Calculated on {{ formatDate(dividend.calculation_date) }}
                            <span v-if="dividend.calculated_by" class="font-medium text-gray-900">
                              by {{ dividend.calculated_by.name }}
                            </span>
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>

                <li v-if="dividend.approval_date">
                  <div class="relative pb-8">
                    <div v-if="dividend.distribution_date" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                    <div class="relative flex space-x-3">
                      <div>
                        <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                          </svg>
                        </span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5">
                        <div>
                          <p class="text-sm text-gray-500">
                            Approved on {{ formatDate(dividend.approval_date) }}
                            <span v-if="dividend.approved_by" class="font-medium text-gray-900">
                              by {{ dividend.approved_by.name }}
                            </span>
                          </p>
                          <p v-if="dividend.approval_notes" class="mt-1 text-sm text-gray-600">
                            {{ dividend.approval_notes }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>

                <li v-if="dividend.distribution_date">
                  <div class="relative pb-8">
                    <div v-if="dividend.reversed_at" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></div>
                    <div class="relative flex space-x-3">
                      <div>
                        <span class="h-8 w-8 rounded-full bg-purple-500 flex items-center justify-center ring-8 ring-white">
                          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM3 4a1 1 0 011-1h1a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V4z"/>
                          </svg>
                        </span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5">
                        <div>
                          <p class="text-sm text-gray-500">
                            Distributed on {{ formatDate(dividend.distribution_date) }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>

                <li v-if="dividend.reversed_at">
                  <div class="relative">
                    <div class="relative flex space-x-3">
                      <div>
                        <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                          </svg>
                        </span>
                      </div>
                      <div class="min-w-0 flex-1 pt-1.5">
                        <div>
                          <p class="text-sm text-gray-500">
                            Distribution reversed on {{ formatDate(dividend.reversed_at) }}
                          </p>
                          <p v-if="dividend.reversal_reason" class="mt-1 text-sm text-gray-600">
                            Reason: {{ dividend.reversal_reason }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="dividend.notes" class="bg-white shadow-sm rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
            <p class="text-gray-600">{{ dividend.notes }}</p>
          </div>
        </div>

        <!-- Member Dividends -->
        <div class="bg-white shadow-sm rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-medium text-gray-900">Member Dividends</h3>
              <div class="flex space-x-2">
                <Link :href="route('dividends.members', dividend.id)" class="text-sm text-indigo-600 hover:text-indigo-900">
                  View All Members
                </Link>
                <span class="text-gray-300">|</span>
                <Link :href="route('dividends.report', dividend.id)" class="text-sm text-indigo-600 hover:text-indigo-900">
                  Generate Report
                </Link>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shares Balance</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dividend Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Date</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="memberDividend in memberDividends.data" :key="memberDividend.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div>
                        <div class="text-sm font-medium text-gray-900">
                          {{ memberDividend.member.first_name }} {{ memberDividend.member.last_name }}
                        </div>
                        <div class="text-sm text-gray-500">{{ memberDividend.member.membership_id }}</div>
                      </div>
                    </div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    KSh {{ formatCurrency(memberDividend.shares_balance) }}
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    KSh {{ formatCurrency(memberDividend.dividend_amount) }}
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getPaymentStatusClass(memberDividend.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ formatStatus(memberDividend.status) }}
                    </span>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ memberDividend.payment_date ? formatDate(memberDividend.payment_date) : 'Not paid' }}
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                      <Link :href="route('dividends.member-details', [dividend.id, memberDividend.member.id])" class="text-indigo-600 hover:text-indigo-900">
                        View
                      </Link>
                      
                      <button 
                        v-if="memberDividend.status === 'pending' && dividend.status === 'approved'" 
                        @click="payMember(memberDividend)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Pay
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="memberDividends.links" class="px-6 py-3 border-t border-gray-200">
            <Pagination :links="memberDividends.links" />
          </div>
        </div>
      </div>
    </div>

    <!-- Approval Modal -->
    <ConfirmationModal :show="showApproveModal" @close="showApproveModal = false">
      <template #title>Approve Dividend</template>
      <template #content>
        <div class="mb-4">
          Are you sure you want to approve the dividend for {{ dividend.dividend_year }}?
        </div>
        <div class="mb-4">
          <InputLabel for="approval_notes" value="Approval Notes (Optional)" />
          <TextArea
            id="approval_notes"
            v-model="approvalForm.approval_notes"
            class="mt-1 block w-full"
            rows="3"
            placeholder="Enter any approval notes..."
          />
        </div>
      </template>
      <template #footer>
        <SecondaryButton @click="showApproveModal = false">Cancel</SecondaryButton>
        <PrimaryButton class="ml-3" @click="submitApproval" :class="{ 'opacity-25': processing }" :disabled="processing">
          Approve
        </PrimaryButton>
      </template>
    </ConfirmationModal>

    <!-- Distribution Modal -->
    <ConfirmationModal :show="showDistributeModal" @close="showDistributeModal = false">
      <template #title>Distribute Dividend</template>
      <template #content>
        <div class="mb-4">
          Are you sure you want to distribute the dividend for {{ dividend.dividend_year }}?
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
                This will transfer KSh {{ formatCurrency(dividend.total_dividends) }} to {{ stats.total_members }} member accounts. This action cannot be easily undone.
              </p>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <SecondaryButton @click="showDistributeModal = false">Cancel</SecondaryButton>
        <PrimaryButton class="ml-3" @click="submitDistribution" :class="{ 'opacity-25': processing }" :disabled="processing">
          Distribute Dividends
        </PrimaryButton>
      </template>
    </ConfirmationModal>

    <!-- Reverse Distribution Modal -->
    <ConfirmationModal :show="showReverseModal" @close="showReverseModal = false">
      <template #title>Reverse Distribution</template>
      <template #content>
        <div class="mb-4">
          Are you sure you want to reverse the dividend distribution for {{ dividend.dividend_year }}?
        </div>
        <div class="mb-4">
          <InputLabel for="reversal_reason" value="Reason for Reversal" class="required" />
          <TextArea
            id="reversal_reason"
            v-model="reversalForm.reason"
            class="mt-1 block w-full"
            rows="3"
            placeholder="Please provide a reason for reversing the distribution..."
            required
          />
          <InputError :message="reversalForm.errors.reason" class="mt-2" />
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700">
                This will reverse all dividend payments and return funds to the system. This action should only be performed in exceptional circumstances.
              </p>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <SecondaryButton @click="showReverseModal = false">Cancel</SecondaryButton>
        <DangerButton class="ml-3" @click="submitReversal" :class="{ 'opacity-25': processing }" :disabled="processing">
          Reverse Distribution
        </DangerButton>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import PrimaryButton from '@/components/PrimaryButton.vue'
import SecondaryButton from '@/components/SecondaryButton.vue'
import DangerButton from '@/components/DangerButton.vue'
import InputLabel from '@/components/InputLabel.vue'
import TextArea from '@/components/TextArea.vue'
import InputError from '@/components/InputError.vue'

const props = defineProps({
  dividend: Object,
  memberDividends: Object,
  stats: Object,
  canApprove: Boolean,
  canDistribute: Boolean
})

const showApproveModal = ref(false)
const showDistributeModal = ref(false)
const showReverseModal = ref(false)
const processing = ref(false)

const approvalForm = useForm({
  approval_notes: ''
})

const reversalForm = useForm({
  reason: ''
})

const profitUtilization = computed(() => {
  if (!props.dividend.total_profit || props.dividend.total_profit === 0) return 0
  return Math.round((props.dividend.total_dividends / props.dividend.total_profit) * 100)
})

const averageDividend = computed(() => {
  if (!props.stats.total_members || props.stats.total_members === 0) return 0
  return props.dividend.total_dividends / props.stats.total_members
})

const distributionProgress = computed(() => {
  if (!props.dividend.total_dividends || props.dividend.total_dividends === 0) return 0
  return Math.round((props.stats.total_paid / props.dividend.total_dividends) * 100)
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

const getStatusClass = (status) => {
  const classes = {
    calculated: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    distributed: 'bg-green-100 text-green-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const submitApproval = () => {
  processing.value = true
  approvalForm.post(route('dividends.approve', props.dividend.id), {
    onFinish: () => {
      processing.value = false
      showApproveModal.value = false
    }
  })
}

const submitDistribution = () => {
  processing.value = true
  router.post(route('dividends.distribute', props.dividend.id), {}, {
    onFinish: () => {
      processing.value = false
      showDistributeModal.value = false
    }
  })
}

const submitReversal = () => {
  processing.value = true
  reversalForm.post(route('dividends.reverse', props.dividend.id), {
    onFinish: () => {
      processing.value = false
      showReverseModal.value = false
    }
  })
}

const payMember = (memberDividend) => {
  processing.value = true
  router.post(route('dividends.pay-member', [props.dividend.id, memberDividend.member.id]), {}, {
    onFinish: () => {
      processing.value = false
    }
  })
}

</script>