<template>
  <AppLayout :breadcrumbs="[{ title: 'System Users', href: route('system-users.index') }]">
    <Head title="System Users" />

    <!-- Toast Notifications -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="toast.visible"
        class="fixed top-6 left-1/2 -translate-x-1/2 z-50 w-full max-w-md"
      >
        <div
          :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'"
          class="text-white px-5 py-4 rounded-2xl shadow-xl flex items-start gap-3"
        >
          <span class="font-semibold capitalize">{{ toast.type }}</span>
          <p class="text-sm leading-relaxed">{{ toast.message }}</p>
        </div>
      </div>
    </transition>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6 m-4 rounded-3xl bg-gradient-to-r from-[#0B2B40] to-[#133263] shadow-lg">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-white">System Users</h1>
        <p class="text-sm text-blue-100 mt-1">Manage system access, roles and status</p>
      </div>
      <Link
        :href="route('system-users.create')"
        class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition shadow"
      >
        + Add User
      </Link>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 px-6">
      <div
        v-for="(value, key) in statsSummary"
        :key="key"
        class="rounded-3xl p-5 bg-white dark:bg-[#0B1F3A] shadow border border-gray-100 dark:border-gray-700"
      >
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ value.label }}</p>
        <h3 class="text-3xl font-bold text-[#0B1F3A] dark:text-orange-400 mt-2">{{ value.count }}</h3>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#0B1F3A] p-6 mx-6 mt-8 rounded-3xl shadow border border-gray-100 dark:border-gray-700">
      <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div>
          <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Search</label>
          <input v-model="filters.search" type="text" placeholder="Name, email or phone"
            class="w-full rounded-xl border px-3 py-2.5 dark:bg-[#14294B] dark:text-white focus:ring-2 focus:ring-orange-500" />
        </div>

        <div>
          <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Role</label>
          <select v-model="filters.role"
            class="w-full rounded-xl border px-3 py-2.5 dark:bg-[#14294B] dark:text-white focus:ring-2 focus:ring-orange-500">
            <option value="all">All</option>
            <option value="admin">Admin</option>
            <option value="loan_officer">Loan Officer</option>
            <option value="accountant">Accountant</option>
            <option value="management">Management</option>
          </select>
        </div>

        <div>
          <label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Status</label>
          <select v-model="filters.status"
            class="w-full rounded-xl border px-3 py-2.5 dark:bg-[#14294B] dark:text-white focus:ring-2 focus:ring-orange-500">
            <option value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div class="flex items-end">
          <button type="submit"
            class="w-full rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2.5 transition shadow">
            Apply Filters
          </button>
        </div>
      </form>
    </div>

    <!-- Bulk Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-6 mt-6">
      <div class="flex items-center gap-3">
        <select v-model="bulkAction"
          class="rounded-xl px-3 py-2 border dark:bg-[#14294B] dark:text-white">
          <option value="">Bulk Action</option>
          <option value="activate">Activate</option>
          <option value="deactivate">Deactivate</option>
          <option value="delete">Delete</option>
        </select>
        <button @click="applyBulkAction"
          class="px-4 py-2 rounded-xl bg-[#0B1F3A] hover:bg-orange-700 text-white transition shadow">
          Apply
        </button>
      </div>
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ users.data.length }} users displayed</p>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto mx-6 mt-4 bg-white dark:bg-[#0B1F3A] rounded-3xl shadow border border-gray-100 dark:border-gray-700">
      <table class="min-w-full text-sm">
        <thead class="bg-[#0B1F3A] text-white">
          <tr>
            <th class="px-4 py-3"><input type="checkbox" :checked="selectAll" @change="toggleSelectAll($event.target.checked)" /></th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Phone</th>
            <th class="px-4 py-3">Role</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users.data" :key="user.id"
            class="border-b dark:border-gray-700 hover:bg-orange-50 dark:hover:bg-[#14294B]/50 transition">
            <td class="px-4 py-3">
              <input type="checkbox" v-model="selected" :value="user.id" :disabled="auth.user.id === user.id" />
            </td>
            <td class="px-4 py-3 font-medium dark:text-white">{{ user.name }}</td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ user.email }}</td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ user.phone }}</td>
            <td class="px-4 py-3 capitalize">{{ user.role.replace('_',' ') }}</td>
            <td class="px-4 py-3">
              <span
                :class="user.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                class="px-3 py-1 rounded-full text-xs font-semibold">
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right space-x-3">
              <Link :href="route('system-users.show', user.id)" class="text-orange-600 hover:underline">View</Link>
              <Link v-if="auth.user.id !== user.id" :href="route('system-users.edit', user.id)"
                class="text-blue-600 hover:underline">Edit</Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="m-6">
      <Pagination :data="users" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, Head, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'

const page = usePage()
const auth = computed(() => page.props.auth)

const props = defineProps({
  users: Object,
  stats: Object,
  filters: Object,
})

const filters = ref({ ...props.filters })
const bulkAction = ref('')
const selected = ref([])
const selectAll = ref(false)

// Toast handling
const toast = ref({ visible: false, message: '', type: 'success' })

watch(() => page.props.flash, (flash) => {
  if (flash?.success || flash?.error) {
    toast.value = {
      visible: true,
      message: flash.success || flash.error,
      type: flash.success ? 'success' : 'error',
    }
    setTimeout(() => (toast.value.visible = false), 3500)
  }
}, { immediate: true })

const applyFilters = () => {
  router.get(route('system-users.index'), filters.value, { preserveState: true })
}

const applyBulkAction = () => {
  if (!bulkAction.value || selected.value.length === 0) return
  router.post(route('system-users.bulk-action'), {
    action: bulkAction.value,
    user_ids: selected.value,
  })
}

const toggleSelectAll = (checked) => {
  selectAll.value = checked
  selected.value = checked
    ? props.users.data.filter(u => u.id !== auth.value.user.id).map(u => u.id)
    : []
}

const statsSummary = computed(() => ({
  total: { label: 'Total Users', count: props.stats.total },
  active: { label: 'Active Users', count: props.stats.active },
  inactive: { label: 'Inactive Users', count: props.stats.inactive },
  byRole: { label: 'Roles', count: Object.keys(props.stats.by_role).length },
}))
</script>
