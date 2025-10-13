<template>
  <AppLayout :breadcrumbs="[{ title: `Budget Items - ${budget.title}` }]">
    <!-- Header -->
    <div class="flex items-center space-x-4 mt-2 p-2 mb-4">
      <Link
        :href="route('budgets.show', budget.id)"
        class="text-slate-500 hover:text-[#f97316] transition"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
      </Link>
      <div>
        <h2 class="text-xl sm:text-2xl font-semibold text-[#102a54] dark:text-white">
          Budget Items - {{ budget.title }}
        </h2>
        <p class="text-sm text-slate-500">{{ budget.budget_year }} Budget</p>
      </div>
    </div>

    <div class="sm:py-4 px-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Budget Summary -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 shadow-md rounded-2xl p-6">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
              <p class="text-2xl font-semibold text-blue-900">{{ Object.keys(items_by_category).length }}</p>
              <p class="text-sm text-gray-600">Categories</p>
            </div>
            <div>
              <p class="text-2xl font-semibold text-blue-900">{{ totalItems }}</p>
              <p class="text-sm text-gray-600">Total Items</p>
            </div>
            <div>
              <p class="text-lg sm:text-xl font-semibold text-blue-900">{{ formatCurrency(totalBudgeted) }}</p>
              <p class="text-sm text-gray-600">Total Budgeted</p>
            </div>
            <div>
              <p class="text-lg sm:text-xl font-semibold text-blue-900">{{ formatCurrency(totalRemaining) }}</p>
              <p class="text-sm text-gray-600">Total Remaining</p>
            </div>
          </div>
        </div>

        <!-- Add New Item -->
        <div
          v-if="can_edit"
          class="bg-white shadow-md rounded-2xl border border-gray-100"
        >
          <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-slate-800">Add New Budget Item</h3>
            <button
              @click="toggleAddForm"
              class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  :d="showAddForm ? 'M6 18L18 6M6 6l12 12' : 'M12 4v16m8-8H4'" />
              </svg>
              {{ showAddForm ? 'Cancel' : 'Add Item' }}
            </button>
          </div>

          <div v-if="showAddForm" class="p-6 border-t border-gray-100 bg-gray-50/50">
            <form @submit.prevent="addNewItem" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Category</label>
                  <select
                    v-model="newItemForm.category"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required
                  >
                    <option value="">Select Category</option>
                    <option
                      v-for="category in availableCategories"
                      :key="category"
                      :value="category"
                    >
                      {{ category }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Item Name</label>
                  <input
                    v-model="newItemForm.item_name"
                    type="text"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required
                  />
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700">Description</label>
                  <textarea
                    v-model="newItemForm.description"
                    rows="2"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Budgeted Amount (KES)</label>
                  <input
                    v-model="newItemForm.budgeted_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required
                  />
                </div>
              </div>

              <div class="flex justify-end gap-3 pt-4">
                <button
                  type="button"
                  @click="showAddForm = false"
                  class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-100 transition"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="addingItem"
                  class="px-4 py-2 bg-blue-900 text-white rounded-lg text-sm font-medium hover:bg-blue-900 transition disabled:opacity-50"
                >
                  {{ addingItem ? 'Adding...' : 'Add Item' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Budget Items -->
        <div
          v-for="(items, category) in items_by_category"
          :key="category"
          class="bg-white shadow-md rounded-2xl border border-gray-100 overflow-hidden"
        >
          <div class="px-6 py-4 flex justify-between items-center bg-gray-50">
            <div>
              <h3 class="text-lg font-semibold text-slate-800">{{ category }}</h3>
              <p class="text-sm text-gray-500">
                {{ items.length }} item{{ items.length !== 1 ? 's' : '' }} •
                {{ formatCurrency(getCategoryTotal(items).budgeted) }} budgeted •
                {{ formatCurrency(getCategoryTotal(items).remaining) }} remaining
              </p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-slate-800">
                {{ getCategoryUtilization(items).toFixed(1) }}% utilized
              </p>
              <div class="w-32 bg-gray-200 rounded-full h-2 mt-1 overflow-hidden">
                <div
                  class="h-2 rounded-full transition-all duration-500"
                  :class="getCategoryUtilization(items) > 90
                    ? 'bg-red-500'
                    : getCategoryUtilization(items) > 75
                    ? 'bg-yellow-500'
                    : 'bg-green-500'"
                  :style="`width: ${Math.min(getCategoryUtilization(items), 100)}%`"
                ></div>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-indigo-50">
                <tr>
                  <th class="px-6 py-3 text-left font-semibold text-gray-600">Item</th>
                  <th class="px-6 py-3 text-right font-semibold text-gray-600">Budgeted</th>
                  <th class="px-6 py-3 text-right font-semibold text-gray-600">Spent</th>
                  <th class="px-6 py-3 text-right font-semibold text-gray-600">Remaining</th>
                  <th class="px-6 py-3 text-center font-semibold text-gray-600">Utilization</th>
                  <th
                    v-if="can_edit"
                    class="px-6 py-3 text-right font-semibold text-gray-600"
                  >
                    Actions
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-100">
                <tr
                  v-for="item in items"
                  :key="item.id"
                  class="hover:bg-gray-50 transition"
                >
                  <td class="px-6 py-4">
                    <div>
                      <p class="font-medium text-slate-800">{{ item.item_name }}</p>
                      <p
                        v-if="item.description"
                        class="text-gray-500 text-xs mt-1"
                      >
                        {{ item.description }}
                      </p>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right">{{ formatCurrency(item.budgeted_amount) }}</td>
                  <td class="px-6 py-4 text-right">{{ formatCurrency(item.spent_amount) }}</td>
                  <td class="px-6 py-4 text-right">
                    <span
                      :class="item.remaining_amount < 0
                        ? 'text-red-600 font-semibold'
                        : 'text-green-600 font-medium'"
                    >
                      {{ formatCurrency(item.remaining_amount) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center">
                      <div class="w-16 bg-gray-200 rounded-full h-2 mr-2 overflow-hidden">
                        <div
                          class="h-2 rounded-full transition-all duration-500"
                          :class="getItemUtilization(item) > 100
                            ? 'bg-red-500'
                            : getItemUtilization(item) > 90
                            ? 'bg-yellow-500'
                            : 'bg-green-500'"
                          :style="`width: ${Math.min(getItemUtilization(item), 100)}%`"
                        ></div>
                      </div>
                      <span class="text-xs text-gray-600">
                        {{ getItemUtilization(item).toFixed(0) }}%
                      </span>
                    </div>
                  </td>
                  <td
                    v-if="can_edit"
                    class="px-6 py-4 text-right space-x-3"
                  >
                    <button
                      @click="editItem(item)"
                      class="text-blue-900 hover:text-indigo-900 font-medium"
                    >
                      Edit
                    </button>
                    <button
                      @click="deleteItem(item)"
                      :disabled="item.spent_amount > 0"
                      class="text-red-600 hover:text-red-800 disabled:text-gray-400 disabled:cursor-not-allowed font-medium"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-if="Object.keys(items_by_category).length === 0"
          class="bg-white rounded-2xl shadow-md border border-gray-100 p-12 text-center"
        >
          <svg
            class="w-16 h-16 text-gray-400 mx-auto mb-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1"
              d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h5.586a1 1 0 00.707-.293l5.414-5.414a1 1 0 00.293-.707V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
            />
          </svg>
          <h3 class="text-lg font-semibold text-slate-800 mb-2">No Budget Items</h3>
          <p class="text-gray-500 mb-4">This budget doesn't have any items yet.</p>
          <Link
            v-if="can_edit"
            :href="route('budgets.edit', budget.id)"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition"
          >
            Add Budget Items
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>


<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  budget: Object,
  items_by_category: Object,
  can_edit: Boolean
})

// Available budget categories (from controller)
const availableCategories = [
  'Administrative Expenses',
  'Staff Costs',
  'Marketing & Communication',
  'Technology & Equipment',
  'Office Supplies',
  'Training & Development',
  'Legal & Professional',
  'Insurance',
  'Utilities',
  'Travel & Transport',
  'Member Services',
  'Loan Provisions',
  'Other Expenses',
]

const showAddForm = ref(false)
const showEditModal = ref(false)
const addingItem = ref(false)
const updatingItem = ref(false)
const editingItem = ref(null)

const newItemForm = ref({
  category: '',
  item_name: '',
  description: '',
  budgeted_amount: 0
})

const editItemForm = ref({
  category: '',
  item_name: '',
  description: '',
  budgeted_amount: 0
})

// Computed properties
const totalItems = computed(() => {
  return Object.values(props.items_by_category).reduce((total, items) => total + items.length, 0)
})

const totalBudgeted = computed(() => {
  return Object.values(props.items_by_category).reduce((total, items) => {
    return total + items.reduce((categoryTotal, item) => categoryTotal + parseFloat(item.budgeted_amount), 0)
  }, 0)
})

const totalRemaining = computed(() => {
  return Object.values(props.items_by_category).reduce((total, items) => {
    return total + items.reduce((categoryTotal, item) => categoryTotal + parseFloat(item.remaining_amount), 0)
  }, 0)
})

// Methods
const toggleAddForm = () => {
  showAddForm.value = !showAddForm.value
  if (!showAddForm.value) {
    resetNewItemForm()
  }
}

const resetNewItemForm = () => {
  newItemForm.value = {
    category: '',
    item_name: '',
    description: '',
    budgeted_amount: 0
  }
}

const addNewItem = async () => {
  addingItem.value = true
  
  try {
    await router.post(route('budgets.store-item', props.budget.id), newItemForm.value, {
      onSuccess: () => {
        showAddForm.value = false
        resetNewItemForm()
      }
    })
  } finally {
    addingItem.value = false
  }
}

const editItem = (item) => {
  editingItem.value = item
  editItemForm.value = {
    category: item.category,
    item_name: item.item_name,
    description: item.description,
    budgeted_amount: item.budgeted_amount
  }
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingItem.value = null
}

const updateItem = async () => {
  updatingItem.value = true
  
  try {
    await router.put(route('budgets.update-item', [props.budget.id, editingItem.value.id]), editItemForm.value, {
      onSuccess: () => {
        closeEditModal()
      }
    })
  } finally {
    updatingItem.value = false
  }
}

const deleteItem = (item) => {
  if (item.spent_amount > 0) {
    alert('Cannot delete budget item that has been spent against.')
    return
  }
  
  if (confirm(`Are you sure you want to delete "${item.item_name}"?`)) {
    router.delete(route('budgets.destroy-item', [props.budget.id, item.id]))
  }
}

const getCategoryTotal = (items) => {
  const budgeted = items.reduce((total, item) => total + parseFloat(item.budgeted_amount), 0)
  const spent = items.reduce((total, item) => total + parseFloat(item.spent_amount), 0)
  const remaining = items.reduce((total, item) => total + parseFloat(item.remaining_amount), 0)
  
  return { budgeted, spent, remaining }
}

const getCategoryUtilization = (items) => {
  const totals = getCategoryTotal(items)
  return totals.budgeted > 0 ? (totals.spent / totals.budgeted) * 100 : 0
}

const getItemUtilization = (item) => {
  return item.budgeted_amount > 0 ? (item.spent_amount / item.budgeted_amount) * 100 : 0
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount || 0)
}
</script>