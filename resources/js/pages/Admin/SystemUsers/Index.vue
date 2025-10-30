<template>
  <AppLayout :breadcrumbs="[{ title: 'System Users', href: route('system-users.index') }]">
    <Head title="System Users" />

    <!-- Flash Messages -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 -translate-y-3"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0 -translate-y-3"
    >
      <div v-if="$page.props.flash.success" class="mb-4 p-3 rounded-lg text-white bg-green-600">
        {{ $page.props.flash.success }}
      </div>
    </transition>

    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 -translate-y-3"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0 -translate-y-3"
    >
      <div v-if="$page.props.flash.error" class="mb-4 p-3 rounded-lg text-white bg-red-600">
        {{ $page.props.flash.error }}
      </div>
    </transition>


    <!-- Page Title -->
    <div class="flex items-center justify-between p-4 m-2 bg-gradient-to-r from-[#0B2B40] to-[#133263] rounded-2xl">
      <h1 class="text-2xl font-semibold text-white dark:text-white">System Users</h1>
      <Link
        :href="route('system-users.create')"
        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition"
      >
        + Add User
      </Link>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
      <div v-for="(value, key) in statsSummary" :key="key"
        class="rounded-2xl p-4 shadow-md bg-white dark:bg-[#0B1F3A] border border-gray-100 dark:border-gray-700">
        <p class="text-sm text-gray-500">{{ value.label }}</p>
        <h3 class="text-2xl font-semibold text-[#0B1F3A] dark:text-orange-400 mt-1">{{ value.count }}</h3>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#0B1F3A] p-4 mx-6 rounded-2xl shadow mb-6 border border-gray-100 dark:border-gray-700">
      <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search name, email, or phone..."
            class="w-full rounded-lg border p-2 border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500"
          />
        </div>

        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Role</label>
          <select
            v-model="filters.role"
            class="w-full rounded-lg p-2 border border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500"
          >
            <option selected value="all">All</option>
            <option value="admin">Admin</option>
            <option value="loan_officer">Loan Officer</option>
            <option value="accountant">Accountant</option>
            <option value="management">Management</option>
          </select>
        </div>

        <div>
          <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full rounded-lg p-2 border border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500"
          >
            <option selected value="">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div>
          <button
            type="submit"
            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg py-2 transition"
          >
            Apply
          </button>
        </div>
      </form>
    </div>

    <!-- Bulk Actions -->
    <div class="flex justify-between px-6 items-center mb-3">
      <div class="flex items-center gap-2">
        <select v-model="bulkAction" class="rounded-lg p-2 border border-gray-300 dark:border-gray-600 dark:bg-[#14294B] text-gray-900 dark:text-white">
          <option value="">Bulk Action</option>
          <option value="activate">Activate</option>
          <option value="deactivate">Deactivate</option>
          <option value="delete">Delete</option>
        </select>
        <button
          @click="applyBulkAction"
          class="px-3 py-2 bg-[#0B1F3A] text-white rounded-lg hover:bg-orange-700 transition"
        >
          Apply
        </button>
      </div>
      <p class="text-sm text-gray-600 dark:text-gray-300">{{ users.data.length }} users displayed others have a member role</p>
    </div>

    <!-- Users Table -->
    <div class="overflow-x-auto mx-6 my-2 bg-white dark:bg-[#0B1F3A] rounded-2xl shadow border border-gray-100 dark:border-gray-700">
      <table class="min-w-full text-sm text-left">
        <thead class="bg-[#0B1F3A] text-white">
          <tr>
            <th class="px-4 py-3"><input type="checkbox" @change="toggleSelectAll" v-model="selectAll" /></th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Phone</th>
            <th class="px-4 py-3">Role</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="user in users.data"
            :key="user.id"
            class="border-b border-gray-100 dark:border-gray-700 hover:bg-orange-50 dark:hover:bg-[#14294B]/50 transition"
          >
            <td class="px-4 py-3">
              <input type="checkbox" v-model="selected" :value="user.id" />
            </td>
            <td class="px-4 py-3 font-medium text-[#0B1F3A] dark:text-white">
              {{ user.name }}
            </td>
            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ user.email }}</td>
            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ user.phone }}</td>
            <td class="px-4 py-3 capitalize text-gray-700 dark:text-gray-300">{{ user.role.replace('_', ' ') }}</td>
            <td class="px-4 py-3">
              <span
                :class="user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                class="px-2 py-1 text-xs rounded-full font-semibold"
              >
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <Link :href="route('system-users.show', user.id)" class="text-orange-600 hover:text-orange-800 mr-2">View</Link>
              <Link :href="route('system-users.edit', user.id)" class="text-blue-600 hover:text-blue-800 mr-2">Edit</Link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="m-6">
      <Pagination :data="users" />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Pagination from '@/components/Pagination.vue'

const props = defineProps({
  users: Object,
  stats: Object,
  filters: Object,
})

const filters = ref({ ...props.filters })
const bulkAction = ref('')
const selected = ref([])
const selectAll = ref(false)

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

const toggleSelectAll = () => {
  selected.value = selectAll.value ? props.users.data.map(u => u.id) : []
}

const statsSummary = computed(() => ({
  total: { label: 'Total Users', count: props.stats.total },
  active: { label: 'Active Users', count: props.stats.active },
  inactive: { label: 'Inactive Users', count: props.stats.inactive },
  byRole: { label: 'Roles', count: Object.keys(props.stats.by_role).length },
}))
</script>
