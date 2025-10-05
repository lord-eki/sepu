<template>
  <AppLayout :breadcrumbs="[{ title: 'Members', href: '/members' }]">

    <Head title="Members Management" />

    <!-- Flash Messages -->
    <div ref="flashBox" class="max-w-3xl mx-auto mt-4 px-4">
      <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <div v-if="flashMessage" class="flex gap-3" :class="[
    flashType === 'success'
      ? 'bg-green-50 border border-green-200 text-green-700'
      : 'bg-red-50 border border-red-200 text-red-700',
    'mb-4 rounded-md p-4 shadow flex items-center'
  ]">
          <component :is="flashType === 'success' ? CheckCircle : AlertCircle" class="h-5 w-5"
            :class="flashType === 'success' ? 'text-green-600' : 'text-red-600'" />
          <p class="ml-3 text-sm">{{ flashMessage }}</p>
          <button type="button" class="ml-auto text-gray-500 hover:text-gray-700" @click="flashMessage = null">
            ✕
          </button>
        </div>
      </transition>
    </div>


    <div class="pb-8">
      <div class="max-w-7xl mx-auto px-3 sm:px-5 lg:px-6 space-y-8">
        <!-- Header -->
        <div class="flex justify-between items-center">
          <h2 class="text-lg sm:text-xl font-bold text-[#0a2342]">Members Management</h2>
          <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.create')"
            class="inline-flex items-center gap-2 rounded-xl bg-[#0f2e55] px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium text-white shadow-sm hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
          <PlusCircle class="w-4 h-4" />
          <div><span class="max-sm:hidden">Add</span> Member</div>
          </Link>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <div v-for="card in [
    { label: 'Total Members', value: stats.total, icon: Users, color: 'text-blue-600 bg-blue-50' },
    { label: 'Active', value: stats.active, icon: CheckCircle, color: 'text-green-600 bg-green-50' },
    { label: 'Inactive', value: stats.inactive, icon: CircleX, color: 'text-red-600 bg-red-50' },
    { label: 'Suspended', value: stats.suspended, icon: TriangleAlert, color: 'text-yellow-600 bg-yellow-50' }
  ]" :key="card.label" class="rounded-2xl bg-white shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-4">
              <div :class="['rounded-xl p-3', card.color]">
                <component :is="card.icon" class="h-5 sm:h-6 w-5 sm:w-6" />
              </div>
              <div>
                <p class="text-sm text-gray-500">{{ card.label }}</p>
                <p class="text-lg sm:text-xl font-semibold text-gray-900">{{ card.value }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="rounded-2xl bg-white shadow-sm border border-gray-100 p-6">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Search</label>
              <div class="relative">
                <input v-model="form.search" type="text" placeholder="Search members..."
                  class="pl-9 pr-3 py-2 w-full rounded-lg border shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  @input="search" />
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                  stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
              </div>

            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <select v-model="form.status"
                class="mt-1 w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="search">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Sort By</label>
              <select v-model="form.sortBy"
                class="mt-1 w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="search">
                <option value="created_at">Date Joined</option>
                <option value="first_name">First Name</option>
                <option value="last_name">Last Name</option>
                <option value="membership_id">Member ID</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Direction</label>
              <select v-model="form.sortDirection"
                class="mt-1 w-full rounded-lg border border-gray-300 shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                @change="search">
                <option value="asc">Ascending</option>
                <option value="desc">Descending</option>
              </select>
            </div>
          </div>
        </div>
        <div class="sm:hidden">
          <div v-for="member in members.data" :key="member.id" class="p-4 rounded-xl border mb-3 bg-white shadow-sm">
            <!-- Top section: name + status -->
            <div class="flex justify-between items-center">
              <div>
                <p class="font-semibold">{{ member.first_name }} {{ member.last_name }}</p>
                <p class="text-xs text-gray-500">{{ member.membership_id }}</p>
              </div>
              <span class="text-xs rounded-full px-2 py-1" :class="{
    'bg-green-100 text-green-700': member.membership_status === 'active',
    'bg-red-100 text-red-700': member.membership_status === 'inactive',
    'bg-yellow-100 text-yellow-700': member.membership_status === 'suspended'
  }">
                {{ member.membership_status }}
              </span>
            </div>

            <!-- Contact -->
            <p class="text-sm text-gray-600 mt-2">{{ member.user.email }}</p>
            <p class="text-xs text-gray-500">{{ member.user.phone }}</p>

            <!-- Accounts + Date Joined -->
            <div class="mt-3 text-sm text-gray-600">
              <p><span class="font-medium">Accounts:</span> {{ member.accounts.length }}</p>
              <p><span class="font-medium">Date Joined:</span> {{ formatDate(member.membership_date) }}</p>
            </div>

            <!-- Actions -->
            <div class="mt-3 flex gap-3 text-sm">
              <Link :href="route('members.show', member.id)" class="text-indigo-600 hover:underline">View</Link>
              <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.edit', member.id)"
                class="text-yellow-600 hover:underline">
              Edit
              </Link>
            </div>
          </div>
        </div>

        <!-- Members Table -->
        <div class="rounded-2xl bg-white shadow-sm border hidden sm:block border-gray-100 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-blue-50 sticky top-0 z-10">
                <tr>
                  <th class="px-6 py-3 text-left font-semibold text-gray-600">Member</th>
                  <th class="px-6 py-3 text-left font-semibold text-gray-600">Contact</th>
                  <th class="px-6 py-3 text-left font-semibold text-gray-600">Status</th>
                  <th class="px-6 py-3 text-left font-semibold text-gray-600">Accounts</th>
                  <th class="px-6 py-3 text-left font-semibold text-gray-600">Date Joined</th>
                  <th class="px-6 py-3 text-right font-semibold text-gray-600">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="member in members.data" :key="member.id" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="h-10 w-10 flex-shrink-0">
                        <img v-if="member.profile_photo" :src="`/storage/${member.profile_photo}`"
                          :alt="member.first_name" class="h-10 w-10 rounded-full object-cover" />
                        <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                          <User2Icon class="h-6 w-6 text-gray-600" />
                        </div>
                      </div>
                      <div>
                        <p class="font-medium text-gray-900">
                          {{ member.first_name }} {{ member.last_name }}
                        </p>
                        <p class="text-xs text-gray-500">
                          {{ member.membership_id }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <p class="text-gray-900">{{ member.user.email }}</p>
                    <p class="text-xs text-gray-500">{{ member.user.phone }}</p>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="{
    'inline-flex px-2 py-1 rounded-full text-xs font-medium': true,
    'bg-green-100 text-green-700': member.membership_status === 'active',
    'bg-red-100 text-red-700': member.membership_status === 'inactive',
    'bg-yellow-100 text-yellow-700': member.membership_status === 'suspended'
  }">
                      {{ member.membership_status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-600">
                    {{ member.accounts.length }} accounts
                  </td>
                  <td class="px-6 py-4 text-gray-600">
                    {{ formatDate(member.membership_date) }}
                  </td>
                  <td class="px-6 py-4 text-right space-x-3">
                    <Link :href="route('members.show', member.id)" class="text-indigo-600 hover:text-indigo-900">
                    View
                    </Link>
                    <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.edit', member.id)"
                      class="text-yellow-600 hover:text-yellow-900">
                    Edit
                    </Link>
                    <button v-if="member.membership_status !== 'active' && canManageStatus"
                      @click="updateStatus(member, 'activate')" class="text-green-600 hover:text-green-900">
                      Activate
                    </button>
                    <button v-if="member.membership_status === 'active' && canManageStatus"
                      @click="updateStatus(member, 'deactivate')" class="text-red-600 hover:text-red-900">
                      Deactivate
                    </button>
                    <button v-if="member.membership_status !== 'suspended' && canManageStatus"
                      @click="updateStatus(member, 'suspend')" class="text-yellow-600 hover:text-yellow-900">
                      Suspend
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <Pagination :data="members" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AppLayout from '@/layouts/AppLayout.vue'
import { CheckCircle, CircleX, PlusCircle, TriangleAlert, User2Icon, Users } from 'lucide-vue-next'
import Pagination from '@/components/Pagination.vue'

// Flash handling
const page = usePage();
const flash = computed(() => page.props.flash || {});
const flashMessage = ref(null);
const flashType = ref("success");
const flashBox = ref(null);

watch(
  flash,
  (val) => {
    if (val.success) {
      flashMessage.value = val.success;
      flashType.value = "success";
    } else if (val.error) {
      flashMessage.value = val.error;
      flashType.value = "error";
    }

    if (flashMessage.value) {
      // Scroll to top of page to ensure flash is visible
      window.scrollTo({ top: 0, behavior: "smooth" });

      // Optional: also ensure the flash container itself is in view
      flashBox.value?.scrollIntoView({ behavior: "smooth", block: "start" });

      // Auto-hide after 3s
      setTimeout(() => (flashMessage.value = null), 5000);
    }
  },
  { immediate: true, deep: true }
);



// Props
const props = defineProps({
  members: Object,
  filters: Object,
  stats: Object
})

// Form state
const form = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
  sortBy: props.filters.sortBy || 'created_at',
  sortDirection: props.filters.sortDirection || 'desc'
})

const canManageStatus = computed(() => {
  const userRole = window.Laravel?.user?.role
  return ['admin', 'management'].includes(userRole)
})

const search = debounce(() => {
  router.get(route('members.index'), form.value, {
    preserveState: true,
    replace: true
  })
}, 300)

// Update member status
const updateStatus = (member, action) => {
  if (confirm(`Are you sure you want to ${action} this member?`)) {
    router.post(route(`members.${action}`, member.id), {}, {
      preserveScroll: true
    })
  }
}

// Date formatter
const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}
</script>
