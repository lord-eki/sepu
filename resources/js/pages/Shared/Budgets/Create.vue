<template>
  <AppLayout title="Create Budget">
    <template #header>
      <div class="flex items-center space-x-4">
        <Link :href="route('budgets.index')" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Create New Budget
        </h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Existing Budget Warning -->
        <div v-if="existing_budget" class="mb-6">
          <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">
                  Budget Already Exists
                </h3>
                <div class="mt-2 text-sm text-yellow-700">
                  <p>A budget for {{ suggested_year }} already exists. Consider creating a budget for a different year.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <form @submit.prevent="submitBudget">
          <div class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
              </div>
              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label for="budget_year" class="block text-sm font-medium text-gray-700">Budget Year</label>
                    <input
                      id="budget_year"
                      v-model="form.budget_year"
                      type="number"
                      :min="new Date().getFullYear()"
                      :max="new Date().getFullYear() + 5"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      required
                    >
                    <div v-if="errors.budget_year" class="mt-1 text-sm text-red-600">{{ errors.budget_year }}</div>
                  </div>

                  <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Budget Title</label>
                    <input
                      id="title"
                      v-model="form.title"
                      type="text"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      required
                    >
                    <div v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</div>
                  </div>

                  <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                      id="description"
                      v-model="form.description"
                      rows="3"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    ></textarea>
                    <div v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</div>
                  </div>

                  <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input
                      id="start_date"
                      v-model="form.start_date"
                      type="date"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      required
                    >
                    <div v-if="errors.start_date" class="mt-1 text-sm text-red-600">{{ errors.start_date }}</div>
                  </div>

                  <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input
                      id="end_date"
                      v-model="form.end_date"
                      type="date"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                      required
                    >
                    <div v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Budget Items -->
            <div class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                  <h3 class="text-lg font-medium text-gray-900">Budget Items</h3>
                  <button
                    type="button"
                    @click="addBudgetItem"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Item
                  </button>
                </div>
              </div>
              <div class="p-6">
                <div v-if="form.budget_items.length === 0" class="text-center py-8">
                  <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h5.586a1 1 0 00.707-.293l5.414-5.414a1 1 0 00.293-.707V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                  <p class="text-gray-500">No budget items added yet. Click "Add Item" to get started.</p>
                </div>

                <div v-else class="space-y-4">
                  <div
                    v-for="(item, index) in form.budget_items"
                    :key="index"
                    class="border border-gray-200 rounded-lg p-4"
                  >
                    <div class="flex justify-between items-start mb-4">
                      <h4 class="text-sm font-medium text-gray-900">Budget Item #{{ index + 1 }}</h4>
                      <button
                        type="button"
                        @click="removeBudgetItem(index)"
                        class="text-red-600 hover:text-red-900"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select
                          v-model="item.category"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                          required
                        >
                          <option value="">Select Category</option>
                          <option v-for="category in budget_categories" :key="category" :value="category">
                            {{ category }}
                          </option>
                        </select>
                        <div v-if="errors[`budget_items.${index}.category`]" class="mt-1 text-sm text-red-600">
                          {{ errors[`budget_items.${index}.category`] }}
                        </div>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-gray-700">Item Name</label>
                        <input
                          v-model="item.item_name"
                          type="text"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                          required
                        >
                        <div v-if="errors[`budget_items.${index}.item_name`]" class="mt-1 text-sm text-red-600">
                          {{ errors[`budget_items.${index}.item_name`] }}
                        </div>
                      </div>

                      <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                          v-model="item.description"
                          rows="2"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        ></textarea>
                        <div v-if="errors[`budget_items.${index}.description`]" class="mt-1 text-sm text-red-600">
                          {{ errors[`budget_items.${index}.description`] }}
                        </div>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-gray-700">Budgeted Amount (KES)</label>
                        <input
                          v-model="item.budgeted_amount"
                          type="number"
                          step="0.01"
                          min="0"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                          required
                          @input="calculateTotalBudget"
                        >
                        <div v-if="errors[`budget_items.${index}.budgeted_amount`]" class="mt-1 text-sm text-red-600">
                          {{ errors[`budget_items.${index}.budgeted_amount`] }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Total Budget Display -->
                <div v-if="form.budget_items.length > 0" class="mt-6 border-t border-gray-200 pt-4">
                  <div class="flex justify-between items-center">
                    <div>
                      <label for="total_budget" class="block text-sm font-medium text-gray-700">Total Budget Amount</label>
                      <input
                        id="total_budget"
                        v-model="form.total_budget"
                        type="number"
                        step="0.01"
                        min="0"
                        readonly
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500"
                      >
                      <div v-if="errors.total_budget" class="mt-1 text-sm text-red-600">{{ errors.total_budget }}</div>
                    </div>
                    <div class="text-right">
                      <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(form.total_budget) }}</div>
                      <div class="text-sm text-gray-500">Total Budget Amount</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="bg-white shadow-sm sm:rounded-lg">
              <div class="px-6 py-4">
                <div class="flex justify-end space-x-3">
                  <Link
                    :href="route('budgets.index')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                  >
                    Cancel
                  </Link>
                  <button
                    type="submit"
                    :disabled="processing"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150 disabled:opacity-50"
                  >
                    <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ processing ? 'Creating...' : 'Create Budget' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  suggested_year: Number,
  existing_budget: Boolean,
  budget_categories: Array,
  errors: Object
})

const form = useForm({
  budget_year: props.suggested_year,
  title: `${props.suggested_year} Annual Budget`,
  description: '',
  total_budget: 0,
  start_date: `${props.suggested_year}-01-01`,
  end_date: `${props.suggested_year}-12-31`,
  budget_items: []
})

const processing = ref(false)

// Watch for budget year changes to update title and dates
watch(() => form.budget_year, (newYear) => {
  if (newYear) {
    form.title = `${newYear} Annual Budget`
    form.start_date = `${newYear}-01-01`
    form.end_date = `${newYear}-12-31`
  }
})

const addBudgetItem = () => {
  form.budget_items.push({
    category: '',
    item_name: '',
    description: '',
    budgeted_amount: 0
  })
}

const removeBudgetItem = (index) => {
  form.budget_items.splice(index, 1)
  calculateTotalBudget()
}

const calculateTotalBudget = () => {
  const total = form.budget_items.reduce((sum, item) => {
    return sum + (parseFloat(item.budgeted_amount) || 0)
  }, 0)
  form.total_budget = total
}

const submitBudget = () => {
  processing.value = true
  calculateTotalBudget()
  
  form.post(route('budgets.store'), {
    onFinish: () => {
      processing.value = false
    }
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount || 0)
}

</script>