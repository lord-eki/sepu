<template>
  <AppLayout :title="`Edit Budget - ${budget.title}`">
    <template #header>
      <div class="flex items-center space-x-4">
        <Link :href="route('budgets.show', budget.id)" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </Link>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Edit Budget - {{ budget.title }}
        </h2>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Status Warning -->
        <div v-if="budget.status !== 'draft'" class="mb-6">
          <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Budget Cannot Be Edited
                </h3>
                <div class="mt-2 text-sm text-red-700">
                  <p>Only draft budgets can be edited. This budget is currently in "{{ getStatusLabel(budget.status) }}" status.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <form @submit.prevent="updateBudget" v-if="budget.status === 'draft'">
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
                    :href="route('budgets.show', budget.id)"
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
                    {{ processing ? 'Updating...' : 'Update Budget' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </form>

        <!-- View Only Mode for Non-Draft Budgets -->
        <div v-else class="bg-white shadow-sm sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Budget Information (Read Only)</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-600">Budget Year</label>
                <p class="mt-1 text-sm text-gray-900">{{ budget.budget_year }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600">Budget Title</label>
                <p class="mt-1 text-sm text-gray-900">{{ budget.title }}</p>
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600">Description</label>
                <p class="mt-1 text-sm text-gray-900">{{ budget.description || 'No description provided' }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600">Start Date</label>
                <p class="mt-1 text-sm text-gray-900">{{ formatDate(budget.start_date) }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600">End Date</label>
                <p class="mt-1 text-sm text-gray-900">{{ formatDate(budget.end_date) }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600">Total Budget</label>
                <p class="mt-1 text-lg font-semibold text-gray-900">{{ formatCurrency(budget.total_budget) }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600">Status</label>
                <span :class="getStatusClass(budget.status)" class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ getStatusLabel(budget.status) }}
                </span>
              </div>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-200">
              <Link
                :href="route('budgets.show', budget.id)"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
              >
                Back to Budget Details
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  budget: Object,
  budget_categories: Array,
  errors: Object
})

const form = useForm({
  budget_year: props.budget.budget_year,
  title: props.budget.title,
  description: props.budget.description,
  total_budget: props.budget.total_budget,
  start_date: props.budget.start_date,
  end_date: props.budget.end_date,
  budget_items: []
})

const processing = ref(false)

// Initialize budget items from existing data
onMounted(() => {
  form.budget_items = props.budget.budget_items.map(item => ({
    category: item.category,
    item_name: item.item_name,
    description: item.description,
    budgeted_amount: item.budgeted_amount
  }))
})

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

const updateBudget = () => {
  processing.value = true
  calculateTotalBudget()
  
  form.put(route('budgets.update', props.budget.id), {
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

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-KE', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-gray-100 text-gray-800',
    approved: 'bg-blue-100 text-blue-800',
    active: 'bg-green-100 text-green-800',
    closed: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    approved: 'Approved',
    active: 'Active',
    closed: 'Closed'
  }
  return labels[status] || status
}
</script>