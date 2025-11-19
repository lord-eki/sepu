<template>
  <AppLayout :breadcrumbs="[{ title: `Budget Items - ${budget.title}` }]">

    <!-- Flash Message -->
    <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
      <div v-if="flashMessage" :class="[
    flashType === 'success'
      ? 'bg-green-50 border border-green-200 text-green-800'
      : 'bg-red-50 border border-red-200 text-red-800',
    'max-w-3xl mx-auto px-6 py-3 rounded-xl flex items-center shadow-sm mb-8 backdrop-blur-sm',
  ]">
        <span class="flex-1 font-medium">{{ flashMessage }}</span>
        <button type="button" class="ml-3 text-gray-500 hover:text-gray-700" @click="flashMessage = null">
          ✕
        </button>
      </div>
    </transition>
    <!-- Header -->
    <div class="flex items-center space-x-4 mt-2 p-2 mb-4">
      <Link :href="route('budgets.show', budget.id)" class="text-slate-500 hover:text-[#f97316] transition">
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

        <!-- Search & Filters -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-6">
          <div
            class="bg-white shadow-sm border border-gray-100 rounded-2xl p-4 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">

            <!-- Search Input -->
            <div class="flex items-center w-full sm:w-1/2 gap-3">
              <div class="relative flex-1">
                <input v-model="searchQuery" type="text" placeholder="Search items..."
                  class="w-full rounded-xl border border-gray-300 pl-10 pr-4 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                </svg>
              </div>
            </div>

            <!-- Category Filter -->
            <div class="w-full sm:w-1/3">
              <select v-model="selectedCategory"
                class="w-full rounded-xl border border-gray-300 py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Categories</option>
                <option v-for="cat in availableCategories" :key="cat" :value="cat">
                  {{ cat }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Add New Item -->
        <div v-if="can_edit" class="bg-white shadow-md rounded-2xl border border-gray-100">
          <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-slate-800">Add New Budget Item</h3>
            <button @click="toggleAddForm"
              class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition">
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
                  <select v-model="newItemForm.category"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                    <option value="">Select Category</option>
                    <option v-for="category in availableCategories" :key="category" :value="category">
                      {{ category }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Item Name</label>
                  <input v-model="newItemForm.item_name" type="text"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required />
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700">Description</label>
                  <textarea v-model="newItemForm.description" rows="2"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Budgeted Amount (KES)</label>
                  <input v-model="newItemForm.budgeted_amount" type="number" step="0.01" min="0"
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required />
                </div>
              </div>

              <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="showAddForm = false"
                  class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-100 transition">
                  Cancel
                </button>
                <button type="submit" :disabled="addingItem"
                  class="px-4 py-2 bg-blue-900 text-white rounded-lg text-sm font-medium hover:bg-blue-900 transition disabled:opacity-50">
                  {{ addingItem ? 'Adding...' : 'Add Item' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Budget Items -->
        <div v-for="(items, category) in filteredCategories" :key="category"
          class="bg-white shadow-md rounded-2xl border border-gray-100 overflow-hidden">
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
                <div class="h-2 rounded-full transition-all duration-500" :class="getCategoryUtilization(items) > 90
    ? 'bg-red-500'
    : getCategoryUtilization(items) > 75
      ? 'bg-yellow-500'
      : 'bg-green-500'" :style="`width: ${Math.min(getCategoryUtilization(items), 100)}%`"></div>
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
                  <th v-if="can_edit" class="px-6 py-3 text-right font-semibold text-gray-600">
                    Actions
                  </th>
                </tr>
              </thead>

              <tbody class="divide-y divide-gray-100">
                <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4">
                    <div>
                      <p class="font-medium text-slate-800">{{ item.item_name }}</p>
                      <p v-if="item.description" class="text-gray-500 text-xs mt-1">
                        {{ item.description }}
                      </p>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right">{{ formatCurrency(item.budgeted_amount) }}</td>
                  <td class="px-6 py-4 text-right">{{ formatCurrency(item.spent_amount) }}</td>
                  <td class="px-6 py-4 text-right">
                    <span :class="item.remaining_amount < 0
    ? 'text-red-600 font-semibold'
    : 'text-green-600 font-medium'">
                      {{ formatCurrency(item.remaining_amount) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center">
                      <div class="w-16 bg-gray-200 rounded-full h-2 mr-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500" :class="getItemUtilization(item) > 100
    ? 'bg-red-500'
    : getItemUtilization(item) > 90
      ? 'bg-yellow-500'
      : 'bg-green-500'" :style="`width: ${Math.min(getItemUtilization(item), 100)}%`"></div>
                      </div>
                      <span class="text-xs text-gray-600">
                        {{ getItemUtilization(item).toFixed(0) }}%
                      </span>
                    </div>
                  </td>
                  <td v-if="can_edit" class="px-6 py-4 text-right space-x-3">
                    <button @click="editItem(item)" class="text-blue-900 hover:text-indigo-900 font-medium">
                      Edit
                    </button>
                    <button @click="openDeleteModal(item)" :disabled="item.spent_amount > 0"
                      class="text-red-600 hover:text-red-800 disabled:text-gray-400 disabled:cursor-not-allowed font-medium">
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="Object.keys(items_by_category).length === 0"
          class="bg-white rounded-2xl shadow-md border border-gray-100 p-12 text-center">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
              d="M9 5H7a2 2 0 00-2 2v11a2 2 0 002 2h5.586a1 1 0 00.707-.293l5.414-5.414a1 1 0 00.293-.707V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3 class="text-lg font-semibold text-slate-800 mb-2">No Budget Items</h3>
          <p class="text-gray-500 mb-4">This budget doesn't have any items yet.</p>
          <Link v-if="can_edit" :href="route('budgets.edit', budget.id)"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
          Add Budget Items
          </Link>
        </div>
      </div>
    </div>

    <!-- Edit Item Modal -->
    <div v-if="showEditModal"
      class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">

      <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 animate-fadeIn">
        <h2 class="text-xl font-semibold text-slate-800 mb-4">
          Edit Budget Item
        </h2>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Category</label>
            <select v-model="editItemForm.category" class="w-full border border-gray-300 rounded-lg p-2">
              <option v-for="cat in availableCategories" :key="cat" :value="cat">
                {{ cat }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Item Name</label>
            <input v-model="editItemForm.item_name" type="text" class="w-full border border-gray-300 rounded-lg p-2" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea v-model="editItemForm.description" rows="2"
              class="w-full border border-gray-300 rounded-lg p-2"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Budgeted Amount (KES)</label>
            <input v-model="editItemForm.budgeted_amount" type="number"
              class="w-full border border-gray-300 rounded-lg p-2" />
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 pt-6">
          <button @click="closeEditModal"
            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
            Cancel
          </button>

          <button @click="updateItem" :disabled="updatingItem"
            class="px-5 py-2 bg-blue-900 text-white rounded-lg hover:bg-blue-800 disabled:opacity-50">
            {{ updatingItem ? 'Updating...' : 'Update Item' }}
          </button>
        </div>
      </div>
    </div>


    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">

      <div class="bg-white rounded-2xl shadow-lg w-full max-w-md p-6 animate-fadeIn">
        <h2 class="text-xl font-semibold text-slate-800 mb-2">
          Delete Item
        </h2>

        <p class="text-gray-600 mb-6">
          Are you sure you want to delete <span class="font-semibold">{{ deleteItemTarget?.item_name }}</span>?
          This action cannot be undone.
        </p>

        <div class="flex justify-end gap-3">
          <button @click="closeDeleteModal" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
            Cancel
          </button>

          <button @click="confirmDelete"
            class="px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center justify-center"
            :disabled="deletingItem">
            <svg v-if="deletingItem" class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg"
              fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>{{ deletingItem ? 'Deleting...' : 'Delete' }}</span>
          </button>


        </div>
      </div>
    </div>

  </AppLayout>
</template>


<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
const searchQuery = ref("")
const selectedCategory = ref("")
const page = usePage()

import axios from 'axios'

const flashMessage = ref(page.props.flash.success || page.props.flash.error);
const flashType = ref(page.props.flash.success ? 'success' : 'error');

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      flashMessage.value = flash.success
      flashType.value = 'success'
    } else if (flash?.error) {
      flashMessage.value = flash.error
      flashType.value = 'error'
    }

    if (flashMessage.value) {
      // Scroll to top instantly
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      })

      setTimeout(() => (flashMessage.value = null), 5000)
    }
  },
  { immediate: true, deep: true }
)




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

const showDeleteModal = ref(false)
const deleteItemTarget = ref(null)
const deletingItem = ref(false)


const openDeleteModal = (item) => {
  if (item.spent_amount > 0) {
    alert("Cannot delete an item that has spent amount.")
    return
  }
  deleteItemTarget.value = item
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deleteItemTarget.value = null
}


const filteredCategories = computed(() => {
  const results = {}

  Object.entries(props.items_by_category).forEach(([category, items]) => {
    // Filter by selected category
    if (selectedCategory.value && selectedCategory.value !== category) return

    // Filter items by search
    const filteredItems = items.filter(item =>
      item.item_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (item.description && item.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
    )

    if (filteredItems.length > 0) {
      results[category] = filteredItems
    }
  })

  return results
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

const addNewItem = async () => {
  addingItem.value = true

  try {
    const response = await axios.post(
      route('budgets.store-item', props.budget.id),
      newItemForm.value
    )

    const category = newItemForm.value.category
    if (!props.items_by_category[category]) props.items_by_category[category] = []

    props.items_by_category[category].push({
      id: response.data.id || Date.now(),
      spent_amount: 0,
      remaining_amount: parseFloat(newItemForm.value.budgeted_amount),
      ...newItemForm.value
    })


    flashMessage.value = response.data.message || 'Item added successfully'
    flashType.value = 'success'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    setTimeout(() => (flashMessage.value = null), 5000)

    showAddForm.value = false
    resetNewItemForm()
  } catch (error) {
    flashMessage.value = error.response?.data?.message || 'Failed to add item'
    flashType.value = 'error'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    setTimeout(() => (flashMessage.value = null), 5000)
  } finally {
    addingItem.value = false
  }
}


const updateItem = async () => {
  if (!editingItem.value) return
  updatingItem.value = true

  try {
    const response = await axios.put(
      route('budgets.update-item', [props.budget.id, editingItem.value.id]),
      editItemForm.value
    )

    const category = editingItem.value.category
    const updatedCategory = editItemForm.value.category

    if (category !== updatedCategory) {
      props.items_by_category[category] = props.items_by_category[category].filter(
        i => i.id !== editingItem.value.id
      )
      if (!props.items_by_category[updatedCategory]) props.items_by_category[updatedCategory] = []
      props.items_by_category[updatedCategory].push({ ...editingItem.value, ...editItemForm.value })
    } else {
      const index = props.items_by_category[category].findIndex(i => i.id === editingItem.value.id)
      if (index !== -1) props.items_by_category[category][index] = { ...props.items_by_category[category][index], ...editItemForm.value }
    }

    closeEditModal()


    flashMessage.value = response.data.message || 'Item updated successfully'
    flashType.value = 'success'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    setTimeout(() => (flashMessage.value = null), 5000)

  } catch (error) {
    flashMessage.value = error.response?.data?.message || 'Failed to update item'
    flashType.value = 'error'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    setTimeout(() => (flashMessage.value = null), 5000)
  } finally {
    updatingItem.value = false
  }
}


const confirmDelete = async () => {
  if (!deleteItemTarget.value) return
  deletingItem.value = true

  try {
    const response = await axios.delete(route('budgets.destroy-item', [props.budget.id, deleteItemTarget.value.id]))

    const category = deleteItemTarget.value.category
    props.items_by_category[category] = props.items_by_category[category].filter(
      i => i.id !== deleteItemTarget.value.id
    )

    closeDeleteModal()

    flashMessage.value = response.data.message || 'Item deleted successfully'
    flashType.value = 'success'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    setTimeout(() => (flashMessage.value = null), 5000)

  } catch (error) {
    flashMessage.value = error.response?.data?.message || 'Failed to delete item'
    flashType.value = 'error'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    setTimeout(() => (flashMessage.value = null), 5000)
  } finally {
    deletingItem.value = false
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

<style scoped>
button:hover {
  cursor: pointer;
}
</style>
