<template>
   <AppLayout :breadcrumbs="[{ title: `Edit Budget - ${budget.title}` }]">
    <!-- Page header -->
    <div class="pb-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
          <Link :href="route('budgets.show', budget.id)" class="text-slate-500 hover:text-[#f97316] transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </Link>

          <div>
            <h1 class="text-xl sm:text-2xl mt-5 font-semibold text-[#0a2342] leading-tight">
              Edit Budget — <span class="text-[#0a2342]/90">{{ budget.title }}</span>
            </h1>
            <div class="mt-1 flex items-center gap-3">
              <p class="text-sm text-gray-600">{{ budget.budget_year }} Budget</p>
              <span class="w-10 h-0.5 bg-[#f97316] rounded-full"></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Page body -->
    <div class="bg-[#f8fafc] min-h-screen py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Status warning (non-editable) -->
        <div v-if="budget.status !== 'draft'">
          <div class="rounded-lg bg-white shadow-sm border border-gray-100 p-4">
            <div class="flex items-start gap-4">
              <div class="p-2 rounded-full bg-[#fff3e0]">
                <svg class="h-5 w-5 text-[#f97316]" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8.257 3.099c.366-.89 1.603-.89 1.969 0l6.518 15.857A1 1 0 0115.8 20H4.2a1 1 0 01-.944-1.044L8.257 3.1zM11 14a1 1 0 10-2 0 1 1 0 002 0zm-1-9a1 1 0 00-.993.883L9 6v5a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
              <div>
                <h3 class="text-sm font-semibold text-[#0a2342]">Budget Cannot Be Edited</h3>
                <p class="mt-1 text-sm text-gray-600">
                  Only draft budgets can be edited. This budget is currently
                  <strong class="text-[#0a2342]"> "{{ getStatusLabel(budget.status) }}"</strong>.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit form (draft only) -->
        <form @submit.prevent="updateBudget" v-if="budget.status === 'draft'" class="space-y-6">

          <!-- Basic Information card -->
          <section class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
              <h2 class="text-lg font-semibold text-[#0a2342]">Basic Information</h2>
              <div class="text-sm text-gray-500">Edit the budget meta information</div>
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
                    class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f97316]/40 focus:border-[#f97316]"
                    required
                  />
                  <div v-if="errors.budget_year" class="mt-1 text-sm text-red-600">{{ errors.budget_year }}</div>
                </div>

                <div>
                  <label for="title" class="block text-sm font-medium text-gray-700">Budget Title</label>
                  <input
                    id="title"
                    v-model="form.title"
                    type="text"
                    class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f97316]/40 focus:border-[#f97316]"
                    required
                  />
                  <div v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</div>
                </div>

                <div class="md:col-span-2">
                  <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f97316]/40 focus:border-[#f97316]"
                  ></textarea>
                  <div v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</div>
                </div>

                <div>
                  <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                  <input
                    id="start_date"
                    v-model="form.start_date"
                    type="date"
                    class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f97316]/40 focus:border-[#f97316]"
                    required
                  />
                  <div v-if="errors.start_date" class="mt-1 text-sm text-red-600">{{ errors.start_date }}</div>
                </div>

                <div>
                  <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                  <input
                    id="end_date"
                    v-model="form.end_date"
                    type="date"
                    class="mt-1 block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f97316]/40 focus:border-[#f97316]"
                    required
                  />
                  <div v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</div>
                </div>
              </div>
            </div>
          </section>

          <!-- Budget Items card -->
          <section class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
              <div>
                <h2 class="text-lg font-semibold text-[#0a2342]">Budget Items</h2>
                <div class="text-sm text-gray-500">Add or remove items for this budget</div>
              </div>

              <button
                type="button"
                @click="addBudgetItem"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-[#f97316] text-white text-sm font-medium hover:bg-[#e86b11] transition-shadow shadow-sm"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Item
              </button>
            </div>

            <div class="p-6">
              <!-- Empty state -->
              <div v-if="form.budget_items.length === 0" class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h5.586a1 1 0 00.707-.293l5.414-5.414a1 1 0 00.293-.707V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"></path>
                </svg>
                <p class="text-gray-600">No budget items added yet. Click <span class="font-semibold text-[#f97316]">Add Item</span> to get started.</p>
              </div>

              <!-- Item list -->
              <div v-else class="space-y-4">
                <div
                  v-for="(item, index) in form.budget_items"
                  :key="index"
                  class="border border-gray-100 rounded-lg p-4 bg-white shadow-sm"
                >
                  <div class="flex justify-between items-start mb-4">
                    <h4 class="text-sm font-medium text-[#0a2342]">Budget Item #{{ index + 1 }}</h4>
                    <div class="flex items-center gap-2">
                      <button
                        type="button"
                        @click="removeBudgetItem(index)"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition"
                        :title="'Remove item ' + (index + 1)"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Category</label>
                      <select
                        v-model="item.category"
                        class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f97316]/40 focus:border-[#f97316]"
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
                        class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f97316]/40 focus:border-[#f97316]"
                        required
                      />
                      <div v-if="errors[`budget_items.${index}.item_name`]" class="mt-1 text-sm text-red-600">
                        {{ errors[`budget_items.${index}.item_name`] }}
                      </div>
                    </div>

                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700">Description</label>
                      <textarea
                        v-model="item.description"
                        rows="2"
                        class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f97316]/40 focus:border-[#f97316]"
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
                        @input="calculateTotalBudget"
                        class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#f97316]/40 focus:border-[#f97316]"
                        required
                      />
                      <div v-if="errors[`budget_items.${index}.budgeted_amount`]" class="mt-1 text-sm text-red-600">
                        {{ errors[`budget_items.${index}.budgeted_amount`] }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Totals -->
              <div v-if="form.budget_items.length > 0" class="mt-6 border-t border-gray-100 pt-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                  <div class="w-full md:w-1/2">
                    <label class="block text-sm font-medium text-gray-700">Total Budget Amount</label>
                    <input
                      id="total_budget"
                      v-model="form.total_budget"
                      type="number"
                      step="0.01"
                      min="0"
                      readonly
                      class="mt-1 block w-full rounded-lg border border-gray-200 px-3 py-2 bg-gray-50 shadow-sm"
                    />
                    <div v-if="errors.total_budget" class="mt-1 text-sm text-red-600">{{ errors.total_budget }}</div>
                  </div>

                  <div class="text-right">
                    <div class="text-xl font-bold text-[#0a2342]">{{ formatCurrency(form.total_budget) }}</div>
                    <div class="text-sm text-gray-500">Total Budget Amount</div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Actions (sticky on small screens) -->
          <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4">
            <div class="flex justify-end md:items-center gap-3">
              <Link
                :href="route('budgets.show', budget.id)"
                class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-[#0a2342] hover:shadow-sm transition"
              >
                Cancel
              </Link>

              <button
                type="submit"
                :disabled="processing"
                class="inline-flex items-center gap-3 px-4 py-2 rounded-lg bg-[#f97316] text-white text-sm font-semibold hover:bg-[#e86b11] transition disabled:opacity-60"
              >
                <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.3726 0 0 5.3726 0 12h4z"></path>
                </svg>
                {{ processing ? 'Updating...' : 'Update Budget' }}
              </button>
            </div>
          </div>
        </form>

        <!-- Read-only view for non-draft -->
        <div v-else class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-[#0a2342]">Budget Information (Read Only)</h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-600">Budget Year</label>
                <p class="mt-1 text-sm text-[#0a2342]">{{ budget.budget_year }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-600">Budget Title</label>
                <p class="mt-1 text-sm text-[#0a2342]">{{ budget.title }}</p>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600">Description</label>
                <p class="mt-1 text-sm text-[#0a2342]">{{ budget.description || 'No description provided' }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-600">Start Date</label>
                <p class="mt-1 text-sm text-[#0a2342]">{{ formatDate(budget.start_date) }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-600">End Date</label>
                <p class="mt-1 text-sm text-[#0a2342]">{{ formatDate(budget.end_date) }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-600">Total Budget</label>
                <p class="mt-1 text-lg font-semibold text-[#0a2342]">{{ formatCurrency(budget.total_budget) }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-600">Status</label>
                <div class="mt-1">
                  <span :class="getStatusClass(budget.status) + ' inline-flex px-3 py-1 text-xs font-semibold rounded-full'">
                    {{ getStatusLabel(budget.status) }}
                  </span>
                </div>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100">
              <Link
                :href="route('budgets.show', budget.id)"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-[#f97316] text-white text-sm font-medium hover:bg-[#e86b11] transition"
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
import AppLayout from '@/layouts/AppLayout.vue'

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
  form.budget_items = (props.budget.budget_items || []).map(item => ({
    category: item.category,
    item_name: item.item_name,
    description: item.description,
    budgeted_amount: item.budgeted_amount
  }))

  // calculate total on mount
  calculateTotalBudget()
})

// Watch for budget year changes to update title and default dates
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
  // allow user to see new total immediately
  calculateTotalBudget()
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
    preserveScroll: true,
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

<style scoped>
button:hover {
  cursor:pointer;
}
</style>
