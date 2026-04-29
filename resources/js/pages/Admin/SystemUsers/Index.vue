<template>
  <AppLayout :breadcrumbs="[{ title: 'System Users', href: route('system-users.index') }]">
    <Head title="System Users" />

    <!-- Toast -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-2 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2 scale-95"
    >
      <div
        v-if="toast.visible"
        class="fixed top-5 left-1/2 z-50 w-full max-w-md -translate-x-1/2 px-4"
      >
        <div
          :class="toast.type === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
            : 'border-rose-200 bg-rose-50 text-rose-700'"
          class="flex items-start gap-3 rounded-2xl border px-5 py-4 shadow-2xl backdrop-blur-xl"
        >
          <div class="mt-0.5 text-sm font-semibold uppercase tracking-wide">
            {{ toast.type }}
          </div>
          <p class="text-sm leading-relaxed">{{ toast.message }}</p>
        </div>
      </div>
    </transition>

    <div class="min-h-screen bg-[#F8FAFC] p-4 md:p-6">
      <div class="mx-auto max-w-7xl space-y-6">

        <!-- Header -->
        <div class="rounded-3xl bg-gradient-to-r from-blue-950 via-[#133263] to-blue-900 p-6 shadow-xl">
          <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <h1 class="text-2xl font-bold text-white md:text-3xl">System Users</h1>
              <p class="mt-1 text-sm text-blue-100">
                Manage system access, permissions, roles and account status
              </p>
            </div>

            <div class="flex flex-wrap gap-3">
              <Link
                :href="route('system-users.create')"
                class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-orange-600"
              >
                + Add User
              </Link>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
          <div
            v-for="(value, key) in statsSummary"
            :key="key"
            class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
          >
            <div class="absolute right-0 top-0 h-24 w-24 rounded-full bg-orange-100 blur-2xl opacity-40"></div>
            <div class="relative">
              <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                {{ value.label }}
              </p>
              <h3 class="mt-3 text-3xl font-bold text-[#0B1F3A]">
                {{ value.count }}
              </h3>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
          <form @submit.prevent="applyFilters" class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <div>
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                Search
              </label>
              <input
                v-model="filters.search"
                type="text"
                placeholder="Name, email or phone"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                Role
              </label>
              <select
                v-model="filters.role"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
              >
                <option value="all">All Roles</option>
                <option value="admin">Admin</option>
                <option value="loan_officer">Loan Officer</option>
                <option value="accountant">Accountant</option>
                <option value="management">Management</option>
              </select>
            </div>

            <div>
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                Status
              </label>
              <select
                v-model="filters.status"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-100"
              >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <div class="flex items-end gap-3">
              <button
                type="submit"
                class="flex-1 rounded-2xl bg-blue-950 px-4 py-3 text-sm font-semibold text-white shadow transition hover:bg-[#133263]"
              >
                Apply Filters
              </button>
            </div>
          </form>
        </div>

        <!-- Bulk Actions -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div class="flex flex-wrap items-center gap-3">
            <select
              v-model="bulkAction"
              class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm outline-none focus:border-orange-400 focus:ring-4 focus:ring-orange-100"
            >
              <option value="">Bulk Action</option>
              <option value="activate">Activate</option>
              <option value="deactivate">Deactivate</option>
              <option value="delete">Delete</option>
            </select>

            <button
              @click="applyBulkAction"
              class="rounded-2xl bg-orange-500 px-4 py-2.5 text-sm font-medium text-white shadow transition hover:bg-orange-600"
            >
              Apply
            </button>
          </div>

          <p class="text-sm text-slate-500">
            {{ users.data.length }} users displayed
          </p>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead class="bg-blue-950 text-white">
                <tr>
                  <th class="px-4 py-4 text-left">
                    <input
                      type="checkbox"
                      :checked="selectAll"
                      @change="toggleSelectAll($event.target.checked)"
                      class="rounded border-white/30"
                    />
                  </th>
                  <th class="px-4 py-4 text-left font-semibold">Name</th>
                  <th class="px-4 py-4 text-left font-semibold">Email</th>
                  <th class="px-4 py-4 text-left font-semibold">Phone</th>
                  <th class="px-4 py-4 text-left font-semibold">Role</th>
                  <th class="px-4 py-4 text-left font-semibold">Status</th>
                  <th class="px-4 py-4 text-right font-semibold">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100">
                <tr
                  v-for="user in sortedUsers"
                  :key="user.id"
                  class="transition hover:bg-slate-50"
                >
                  <td class="px-4 py-4">
                    <input
                      type="checkbox"
                      v-model="selected"
                      :value="user.id"
                      :disabled="auth.user.id === user.id"
                      class="rounded border-slate-300"
                    />
                  </td>

                  <td class="px-4 py-4">
                    <div class="font-semibold text-slate-900">{{ user.name }}</div>
                    <div v-if="auth.user.id === user.id" class="text-xs text-orange-500">
                      Current User
                    </div>
                  </td>

                  <td class="px-4 py-4 text-slate-600">{{ user.email }}</td>
                  <td class="px-4 py-4 text-slate-600">{{ user.phone }}</td>

                  <td class="px-4 py-4">
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium capitalize text-slate-700">
                      {{ user.role.replace('_', ' ') }}
                    </span>
                  </td>

                  <td class="px-4 py-4">
                    <span
                      :class="user.is_active
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-rose-100 text-rose-700'"
                      class="rounded-full px-3 py-1 text-xs font-semibold"
                    >
                      {{ user.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>

                  <td class="px-4 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <Link
                        :href="route('system-users.show', user.id)"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-100"
                      >
                        View
                      </Link>

                      <Link
                        v-if="auth.user.id !== user.id"
                        :href="route('system-users.edit', user.id)"
                        class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-blue-700"
                      >
                        Edit
                      </Link>
                    </div>
                  </td>
                </tr>

                <tr v-if="!users.data.length">
                  <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-400">
                    No users found
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <Pagination :data="users" />
      </div>
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

const toast = ref({
  visible: false,
  message: '',
  type: 'success',
})

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success || flash?.error) {
      toast.value = {
        visible: true,
        message: flash.success || flash.error,
        type: flash.success ? 'success' : 'error',
      }

      setTimeout(() => {
        toast.value.visible = false
      }, 3500)
    }
  },
  { immediate: true }
)

const applyFilters = () => {
  router.get(route('system-users.index'), filters.value, {
    preserveState: true,
    preserveScroll: true,
  })
}

const applyBulkAction = () => {
  if (!bulkAction.value || !selected.value.length) return

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

const sortedUsers = computed(() => {
  if (!props.users?.data) return []

  const me = props.users.data.find(u => u.id === auth.value.user.id)
  const others = props.users.data.filter(u => u.id !== auth.value.user.id)

  return me ? [me, ...others] : others
})

const statsSummary = computed(() => ({
  total: {
    label: 'Total Users',
    count: props.stats?.total ?? 0,
  },
  active: {
    label: 'Active Users',
    count: props.stats?.active ?? 0,
  },
  inactive: {
    label: 'Inactive Users',
    count: props.stats?.inactive ?? 0,
  },
  roles: {
    label: 'Roles',
    count: Object.keys(props.stats?.by_role ?? {}).length,
  },
}))
</script>