<template>
  <AppLayout :breadcrumbs="[
    { title: 'Budgets', href: route('budgets.index') },
    { title: `Budget Items - ${budget.title}` }
  ]">
    <Head :title="`Budget Items - ${budget.title}`" />

    <div class="min-h-screen bg-slate-50 dark:bg-[#020817]">
      <!-- Flash -->
      <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="flashMessage"
          class="fixed top-5 left-1/2 z-50 w-full max-w-xl -translate-x-1/2 px-4"
        >
          <div
            :class="flashType === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/80 dark:text-emerald-300'
              : 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/80 dark:text-rose-300'"
            class="flex items-start gap-3 rounded-2xl border px-5 py-4 shadow-2xl backdrop-blur-xl"
          >
            <div class="mt-0.5">
              <div
                class="flex h-8 w-8 items-center justify-center rounded-xl"
                :class="flashType === 'success'
                  ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'
                  : 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300'"
              >
                <svg v-if="flashType === 'success'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.93 19h14.14c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.2 16c-.77 1.33.19 3 1.73 3z" />
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <p class="font-semibold">{{ flashType === 'success' ? 'Success' : 'Error' }}</p>
              <p class="mt-0.5 text-sm">{{ flashMessage }}</p>
            </div>
            <button @click="flashMessage = null" class="text-current/70 transition hover:text-current">✕</button>
          </div>
        </div>
      </transition>

      <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <!-- Hero -->
        <section class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 p-6 shadow-2xl shadow-blue-950/20">
          <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(249,115,22,0.18),transparent_28%),radial-gradient(circle_at_left,rgba(59,130,246,0.18),transparent_35%)]"></div>

          <div class="relative z-10 flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div class="flex items-start gap-4">
              <Link
                :href="route('budgets.show', budget.id)"
                class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-white transition hover:bg-white/10"
              >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </Link>

              <div>
                <h1 class="mt-3 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                  Budget Items - {{ budget.title }}
                </h1>
                <p class="mt-1 text-sm text-slate-300">
                  Manage categories, allocations, utilization and item-level budget control for {{ budget.budget_year }}.
                </p>
              </div>
            </div>

            <button
              v-if="can_edit"
              @click="toggleAddForm"
              class="inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-950/30 transition hover:bg-orange-600"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  :d="showAddForm ? 'M6 18L18 6M6 6l12 12' : 'M12 4v16m8-8H4'"
                />
              </svg>
              {{ showAddForm ? 'Close Form' : 'Add Budget Item' }}
            </button>
          </div>
        </section>

        <!-- Summary -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div
            v-for="card in summaryCards"
            :key="card.label"
            class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900"
          >
            <div class="flex items-start justify-between">
              <div>
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">{{ card.label }}</p>
                <p class="mt-3 text-2xl font-bold text-slate-900 dark:text-white">{{ card.value }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ card.meta }}</p>
              </div>
              <div :class="card.iconWrap" class="flex h-11 w-11 items-center justify-center rounded-2xl text-white shadow-lg">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon" />
                </svg>
              </div>
            </div>
          </div>
        </section>

        <!-- Filters -->
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="grid flex-1 grid-cols-1 gap-3 md:grid-cols-2">
              <div class="relative">
                <svg class="pointer-events-none absolute left-4 top-3.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                </svg>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search item name or description..."
                  class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm outline-none ring-0 transition placeholder:text-slate-400 focus:border-orange-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                />
              </div>

              <select
                v-model="selectedCategory"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-white"
              >
                <option value="">All Categories</option>
                <option v-for="cat in availableCategories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
            </div>

            <div class="text-sm text-slate-500 dark:text-slate-400">
              {{ totalItems }} item{{ totalItems !== 1 ? 's' : '' }} across {{ Object.keys(filteredCategories).length }} visible categories
            </div>
          </div>
        </section>

        <!-- Add Form -->
        <section
          v-if="can_edit && showAddForm"
          class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
          <div class="border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Add New Budget Item</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create and assign a new item to a budget category.</p>
          </div>

          <form @submit.prevent="addNewItem" class="space-y-5 p-6">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Category</label>
                <select v-model="newItemForm.category" required class="input-modern">
                  <option value="">Select category</option>
                  <option v-for="category in availableCategories" :key="category" :value="category">{{ category }}</option>
                </select>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Item Name</label>
                <input v-model="newItemForm.item_name" type="text" required class="input-modern" />
              </div>

              <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Description</label>
                <textarea v-model="newItemForm.description" rows="3" class="input-modern resize-none"></textarea>
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Budgeted Amount (KES)</label>
                <input v-model="newItemForm.budgeted_amount" type="number" min="0" step="0.01" required class="input-modern" />
              </div>
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="showAddForm = false" class="btn-secondary">Cancel</button>
              <button type="submit" :disabled="addingItem" class="btn-primary">
                {{ addingItem ? 'Adding...' : 'Add Item' }}
              </button>
            </div>
          </form>
        </section>

        <!-- Items -->
        <section
          v-for="(items, category) in filteredCategories"
          :key="category"
          class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
          <div class="border-b border-slate-200 bg-slate-50 px-6 py-5 dark:border-slate-800 dark:bg-slate-800/60">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ category }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                  {{ items.length }} item{{ items.length !== 1 ? 's' : '' }} •
                  {{ formatCurrency(getCategoryTotal(items).budgeted) }} budgeted •
                  {{ formatCurrency(getCategoryTotal(items).remaining) }} remaining
                </p>
              </div>

              <div class="w-full max-w-xs">
                <div class="mb-2 flex items-center justify-between text-xs font-medium text-slate-500 dark:text-slate-400">
                  <span>Utilization</span>
                  <span>{{ getCategoryUtilization(items).toFixed(1) }}%</span>
                </div>
                <div class="h-2.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                  <div
                    class="h-2.5 rounded-full transition-all duration-500"
                    :class="getCategoryUtilization(items) > 90 ? 'bg-rose-500' : getCategoryUtilization(items) > 75 ? 'bg-amber-500' : 'bg-emerald-500'"
                    :style="`width:${Math.min(getCategoryUtilization(items), 100)}%`"
                  />
                </div>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <tr class="text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                  <th class="px-6 py-4">Item</th>
                  <th class="px-6 py-4 text-right">Budgeted</th>
                  <th class="px-6 py-4 text-right">Spent</th>
                  <th class="px-6 py-4 text-right">Remaining</th>
                  <th class="px-6 py-4 text-center">Utilization</th>
                  <th v-if="can_edit" class="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <tr
                  v-for="item in items"
                  :key="item.id"
                  class="transition hover:bg-orange-50/60 dark:hover:bg-slate-800/60"
                >
                  <td class="px-6 py-4">
                    <p class="font-medium text-slate-900 dark:text-white">{{ item.item_name }}</p>
                    <p v-if="item.description" class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ item.description }}</p>
                  </td>

                  <td class="px-6 py-4 text-right font-medium text-slate-700 dark:text-slate-200">
                    {{ formatCurrency(item.budgeted_amount) }}
                  </td>

                  <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-300">
                    {{ formatCurrency(item.spent_amount) }}
                  </td>

                  <td class="px-6 py-4 text-right">
                    <span :class="item.remaining_amount < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'" class="font-semibold">
                      {{ formatCurrency(item.remaining_amount) }}
                    </span>
                  </td>

                  <td class="px-6 py-4">
                    <div class="mx-auto flex max-w-[120px] items-center gap-2">
                      <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                        <div
                          class="h-2 rounded-full transition-all duration-500"
                          :class="getItemUtilization(item) > 100 ? 'bg-rose-500' : getItemUtilization(item) > 90 ? 'bg-amber-500' : 'bg-emerald-500'"
                          :style="`width:${Math.min(getItemUtilization(item), 100)}%`"
                        />
                      </div>
                      <span class="w-10 text-right text-xs text-slate-500 dark:text-slate-400">
                        {{ getItemUtilization(item).toFixed(0) }}%
                      </span>
                    </div>
                  </td>

                  <td v-if="can_edit" class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-4">
                      <button @click="editItem(item)" class="font-medium text-blue-700 hover:text-blue-900 dark:text-blue-400">Edit</button>
                      <button
                        @click="openDeleteModal(item)"
                        :disabled="item.spent_amount > 0"
                        class="font-medium text-rose-600 hover:text-rose-800 disabled:cursor-not-allowed disabled:text-slate-400"
                      >
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Empty -->
        <section
          v-if="Object.keys(items_by_category).length === 0"
          class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900"
        >
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
            <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
            </svg>
          </div>
          <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">No Budget Items Yet</h3>
          <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Start by creating your first budget item for this allocation plan.</p>
        </section>
      </div>
    </div>
  </AppLayout>
</template>


<script setup>
import { ref, computed, watch, reactive, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
const searchQuery = ref("")
const selectedCategory = ref("")
const page = usePage()

import axios from 'axios'


const props = defineProps({
  budget: Object,
  items_by_category: Object,
  can_edit: Boolean
})

const localItems = ref({})

onMounted(() => {
  localItems.value = JSON.parse(
    JSON.stringify(props.items_by_category || {})
  )
})

const flashMessage = ref(page.props.flash?.success || page.props.flash?.error)
const flashType = ref(page.props.flash?.success ? 'success' : 'error')

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


const showFlash = (message, type = 'success') => {
  flashMessage.value = message
  flashType.value = type

  window.scrollTo({ top: 0, behavior: 'smooth' })

  setTimeout(() => {
    flashMessage.value = null
  }, 5000)
}


const filteredCategories = computed(() => {
  const results = {}

  Object.entries(localItems.value || {}).forEach(([category, items]) => {
    if (!Array.isArray(items)) return

    // Filter by selected category
    if (selectedCategory.value && selectedCategory.value !== category) return

    const filteredItems = items.filter(item =>
      item.item_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.description?.toLowerCase().includes(searchQuery.value.toLowerCase())
    )

    if (filteredItems.length > 0) {
      results[category] = filteredItems
    }
  })

  return results
})

// Computed properties
const totalItems = computed(() => {
  return Object.entries(localItems.value || {}).reduce((total, items) => total + items.length, 0)
})

const totalBudgeted = computed(() => {
  return Object.entries(localItems.value || {}).reduce((total, items) => {
    return total + items.reduce((categoryTotal, item) => categoryTotal + parseFloat(item.budgeted_amount), 0)
  }, 0)
})

const totalRemaining = computed(() => {
  return Object.entries(localItems.value || {}).reduce((total, items) => {
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
    if (!localItems.value[category]) localItems.value[category] = []

    localItems.value[category].push({
      id: response.data.id || Date.now(),
      spent_amount: 0,
      remaining_amount: parseFloat(newItemForm.value.budgeted_amount),
      ...newItemForm.value
    })


    flashMessage.value = response.data.message
    flashType.value = 'success'
    window.scrollTo({ top: 0, behavior: 'smooth' })
    setTimeout(() => (flashMessage.value = null), 5000)

    showAddForm.value = false
    resetNewItemForm()
  } catch (error) {
    flashMessage.value = error.response?.data?.message
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
      localItems.value[category] = localItems.value[category].filter(
        i => i.id !== editingItem.value.id
      )
      if (!localItems[updatedCategory]) localItems[updatedCategory] = []
      localItems[updatedCategory].push({ ...editingItem.value, ...editItemForm.value })
    } else {
      const index = localItems.value[category].findIndex(i => i.id === editingItem.value.id)
      if (index !== -1) localItems.value[category][index] = { ...localItems.value[category][index], ...editItemForm.value }
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
    localItems.value[category] = localItems.value[category].filter(
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
  }).format(Number(amount) || 0)
}
</script>

<style scoped>
button:hover {
  cursor: pointer;
}
</style>
