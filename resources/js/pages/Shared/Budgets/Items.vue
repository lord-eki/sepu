<template>
  <AppLayout :title="`Budget Items - ${budget.title}`">
    <template #header>
      <div class="flex items-center space-x-4">
        <Link :href="route('budgets.show', budget.id)" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </Link>
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Budget Items - {{ budget.title }}
          </h2>
          <p class="text-sm text-gray-600">{{ budget.budget_year }} Budget</p>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Budget Summary -->
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ Object.keys(items_by_category).length }}</div>
                <div class="text-sm text-gray-500">Categories</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-900">{{ totalItems }}</div>
                <div class="text-sm text-gray-500">Total Items</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ formatCurrency(totalBudgeted) }}</div>
                <div class="text-sm text-gray-500">Total Budgeted</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ formatCurrency(totalRemaining) }}</div>
                <div class="text-sm text-gray-500">Total Remaining</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Add New Item (if budget is draft) -->
        <div v-if="can_edit" class="bg-white shadow-sm sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-medium text-gray-900">Add New Budget Item</h3>
              <button
                @click="toggleAddForm"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="showAddForm ? 'M6 18L18 6M6 6l12 12' : 'M12 4v16m8-8H4'"></path>
                </svg>
                {{ showAddForm ? 'Cancel' : 'Add Item' }}
              </button>
            </div>
          </div>
          
          <div v-if="showAddForm" class="p-6 border-t border-gray-200">
            <form @submit.prevent="addNewItem" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Category</label>
                  <select
                    v-model="newItemForm.category"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required
                  >
                    <option value="">Select Category</option>
                    <option v-for="category in availableCategories" :key="category" :value="category">
                      {{ category }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Item Name</label>
                  <input
                    v-model="newItemForm.item_name"
                    type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required
                  >
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700">Description</label>
                  <textarea
                    v-model="newItemForm.description"
                    rows="2"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  ></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Budgeted Amount (KES)</label>
                  <input
                    v-model="newItemForm.budgeted_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required
                  >
                </div>
              </div>
              <div class="flex justify-end space-x-3">
                <button
                  type="button"
                  @click="showAddForm = false"
                  class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="addingItem"
                  class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                >
                  {{ addingItem ? 'Adding...' : 'Add Item' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Budget Items by Category -->
        <div v-for="(items, category) in items_by_category" :key="category" class="bg-white shadow-sm sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-lg font-medium text-gray-900">{{ category }}</h3>
                <p class="text-sm text-gray-500">
                  {{ items.length }} item{{ items.length !== 1 ? 's' : '' }} • 
                  {{ formatCurrency(getCategoryTotal(items).budgeted) }} budgeted • 
                  {{ formatCurrency(getCategoryTotal(items).remaining) }} remaining
                </p>
              </div>
              <div class="text-right">
                <div class="text-sm text-gray-900">
                  {{ getCategoryUtilization(items).toFixed(1) }}% utilized
                </div>
                <div class="w-32 bg-gray-200 rounded-full h-2 mt-1">
                  <div 
                    class="h-2 rounded-full transition-all duration-300"
                    :class="getCategoryUtilization(items) > 90 ? 'bg-red-500' : getCategoryUtilization(items) > 75 ? 'bg-yellow-500' : 'bg-green-500'"
                    :style="`width: ${Math.min(getCategoryUtilization(items), 100)}%`"
                  ></div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Budgeted</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Spent</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                  <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization</th>
                  <th v-if="can_edit" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ item.item_name }}</div>
                      <div class="text-sm text-gray-500" v-if="item.description">{{ item.description }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                    {{ formatCurrency(item.budgeted_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                    {{ formatCurrency(item.spent_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <span :class="item.remaining_amount < 0 ? 'text-red-600 font-medium' : 'text-green-600'">
                      {{ formatCurrency(item.remaining_amount) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center">
                      <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                        <div 
                          class="h-2 rounded-full transition-all duration-300"
                          :class="getItemUtilization(item) > 100 ? 'bg-red-500' : getItemUtilization(item) > 90 ? 'bg-yellow-500' : 'bg-green-500'"
                          :style="`width: ${Math.min(getItemUtilization(item), 100)}%`"
                        ></div>
                      </div>
                      <span class="text-xs text-gray-600">{{ getItemUtilization(item).toFixed(0) }}%</span>
                    </div>
                  </td>
                  <td v-if="can_edit" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                      <button
                        @click="editItem(item)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteItem(item)"
                        :disabled="item.spent_amount > 0"
                        class="text-red-600 hover:text-red-900 disabled:text-gray-400 disabled:cursor-not-allowed"
                        :title="item.spent_amount > 0 ? 'Cannot delete item with expenses' : 'Delete item'"
                      >
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- No Items Message -->
        <div v-if="Object.keys(items_by_category).length === 0" class="bg-white shadow-sm sm:rounded-lg">
          <div class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h5.586a1 1 0 00.707-.293l5.414-5.414a1 1 0 00.293-.707V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Budget Items</h3>
            <p class="text-gray-500 mb-4">This budget doesn't have any items yet.</p>
            <Link
              v-if="can_edit"
              :href="route('budgets.edit', budget.id)"
              class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
            >
              Add Budget Items
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Item Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="updateItem">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    Edit Budget Item
                  </h3>
                  <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select
                          v-model="editItemForm.category"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                          required
                        >
                          <option value="">Select Category</option>
                          <option v-for="category in availableCategories" :key="category" :value="category">
                            {{ category }}
                          </option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Item Name</label>
                        <input
                          v-model="editItemForm.item_name"
                          type="text"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                          required
                        >
                      </div>
                      <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                          v-model="editItemForm.description"
                          rows="2"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        ></textarea>
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700">Budgeted Amount (KES)</label>
                        <input
                          v-model="editItemForm.budgeted_amount"
                          type="number"
                          step="0.01"
                          min="0"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                          required
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="submit"
                :disabled="updatingItem"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                {{ updatingItem ? 'Updating...' : 'Update' }}
              </button>
              <button
                @click="closeEditModal"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </form>
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