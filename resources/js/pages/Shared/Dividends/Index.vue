

<template>
  <AppLayout title="Dividends Management">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Dividends Management
      </h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"/>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Dividends</p>
                  <p class="text-lg font-semibold text-gray-900">{{ stats.total_dividends }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Total Distributed</p>
                  <p class="text-lg font-semibold text-gray-900">KSh {{ formatCurrency(stats.total_distributed) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Pending Approval</p>
                  <p class="text-lg font-semibold text-gray-900">{{ stats.pending_approval }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-500">Ready to Distribute</p>
                  <p class="text-lg font-semibold text-gray-900">{{ stats.approved_pending_distribution }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters and Actions -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
          <div class="p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
              <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                <div>
                  <select v-model="filters.status" @change="applyFilters" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="all">All Status</option>
                    <option value="calculated">Calculated</option>
                    <option value="approved">Approved</option>
                    <option value="distributed">Distributed</option>
                  </select>
                </div>
                
                <div>
                  <select v-model="filters.year" @change="applyFilters" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="">All Years</option>
                    <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                  </select>
                </div>
              </div>

              <div class="flex space-x-3">
                <Link :href="route('dividends.analytics.history')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                  <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Analytics
                </Link>
                
                <Link :href="route('dividends.create')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                  <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                  </svg>
                  Calculate New Dividend
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Dividends Table -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Profit</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dividend Rate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Dividends</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="dividend in dividends.data" :key="dividend.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ dividend.dividend_year }}</div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">KSh {{ formatCurrency(dividend.total_profit) }}</div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ dividend.dividend_rate }}%</div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">KSh {{ formatCurrency(dividend.total_dividends) }}</div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusClass(dividend.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ formatStatus(dividend.status) }}
                    </span>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div>Calculated: {{ formatDate(dividend.calculation_date) }}</div>
                    <div v-if="dividend.approval_date">Approved: {{ formatDate(dividend.approval_date) }}</div>
                    <div v-if="dividend.distribution_date">Distributed: {{ formatDate(dividend.distribution_date) }}</div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                      <Link :href="route('dividends.show', dividend.id)" class="text-indigo-600 hover:text-indigo-900">
                        View
                      </Link>
                      
                      <Link v-if="dividend.status === 'calculated'" :href="route('dividends.edit', dividend.id)" class="text-blue-600 hover:text-blue-900">
                        Edit
                      </Link>
                      
                      <button v-if="dividend.status === 'calculated'" @click="approveDividend(dividend)" class="text-green-600 hover:text-green-900">
                        Approve
                      </button>
                      
                      <button v-if="dividend.status === 'approved'" @click="distributeDividend(dividend)" class="text-purple-600 hover:text-purple-900">
                        Distribute
                      </button>
                      
                      <button v-if="dividend.status === 'calculated'" @click="confirmDelete(dividend)" class="text-red-600 hover:text-red-900">
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="dividends.links" class="px-6 py-3 border-t border-gray-200">
            <Pagination :links="dividends.links" />
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modals -->
    <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
      <template #title>Delete Dividend</template>
      <template #content>
        Are you sure you want to delete the dividend for {{ selectedDividend?.dividend_year }}? This action cannot be undone.
      </template>
      <template #footer>
        <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
        <DangerButton class="ml-3" @click="deleteDividend" :class="{ 'opacity-25': processing }" :disabled="processing">
          Delete
        </DangerButton>
      </template>
    </ConfirmationModal>

    <ConfirmationModal :show="showApproveModal" @close="showApproveModal = false">
      <template #title>Approve Dividend</template>
      <template #content>
        <div class="mb-4">
          Are you sure you want to approve the dividend for {{ selectedDividend?.dividend_year }}?
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

    <ConfirmationModal :show="showDistributeModal" @close="showDistributeModal = false">
      <template #title>Distribute Dividend</template>
      <template #content>
        Are you sure you want to distribute the dividend for {{ selectedDividend?.dividend_year }}? This will transfer dividend amounts to all eligible members' accounts.
      </template>
      <template #footer>
        <SecondaryButton @click="showDistributeModal = false">Cancel</SecondaryButton>
        <PrimaryButton class="ml-3" @click="submitDistribution" :class="{ 'opacity-25': processing }" :disabled="processing">
          Distribute
        </PrimaryButton>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  dividends: Object,
  availableYears: Array,
  filters: Object,
  stats: Object
})

const showDeleteModal = ref(false)
const showApproveModal = ref(false)
const showDistributeModal = ref(false)
const selectedDividend = ref(null)
const processing = ref(false)

const filters = reactive({
  status: props.filters.status || 'all',
  year: props.filters.year || ''
})

const approvalForm = useForm({
  approval_notes: ''
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

const applyFilters = () => {
  router.get(route('dividends.index'), filters, {
    preserveState: true,
    replace: true
  })
}

const confirmDelete = (dividend) => {
  selectedDividend.value = dividend
  showDeleteModal.value = true
}

const deleteDividend = () => {
  processing.value = true
  router.delete(route('dividends.destroy', selectedDividend.value.id), {
    onFinish: () => {
      processing.value = false
      showDeleteModal.value = false
      selectedDividend.value = null
    }
  })
}

const approveDividend = (dividend) => {
  selectedDividend.value = dividend
  approvalForm.reset()
  showApproveModal.value = true
}

const submitApproval = () => {
  processing.value = true
  approvalForm.post(route('dividends.approve', selectedDividend.value.id), {
    onFinish: () => {
      processing.value = false
      showApproveModal.value = false
      selectedDividend.value = null
    }
  })
}

const distributeDividend = (dividend) => {
  selectedDividend.value = dividend
  showDistributeModal.value = true
}

const submitDistribution = () => {
  processing.value = true
  router.post(route('dividends.distribute', selectedDividend.value.id), {}, {
    onFinish: () => {
      processing.value = false
      showDistributeModal.value = false
      selectedDividend.value = null
    }
  })
}
</script>