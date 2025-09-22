<template>
  <AppLayout title="Members">
    <Head title="Members Management" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="flex justify-between items-center pb-6">
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Members Management
          </h2>
          <Link :href="route('members.create')"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            v-if="$page.props.auth.user.role !== 'member'">
          <PlusCircle class="w-4 h-4 mr-2" />
          Add Member
          </Link>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Users class="h-6 w-6 text-gray-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Members
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ stats.total }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CheckCircle class="h-6 w-6 text-green-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Active
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ stats.active }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CircleX cass="h-6 w-6 text-red-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Inactive
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ stats.inactive }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <TriangleAlert class="h-6 w-6 text-yellow-400" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Suspended
                    </dt>
                    <dd class="text-lg font-medium text-gray-900">
                      {{ stats.suspended }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg mb-6">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input v-model="form.search" type="text" placeholder="Search members..."
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                  @input="search" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select v-model="form.status"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
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
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
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
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2"
                  @change="search">
                  <option value="asc">Ascending</option>
                  <option value="desc">Descending</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Members Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Member
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contact
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Accounts
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date Joined
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="member in members.data" :key="member.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <img v-if="member.profile_photo" :src="`/storage/${member.profile_photo}`"
                          :alt="member.first_name" class="h-10 w-10 rounded-full object-cover" />
                        <div v-else class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                          <User2Icon class="h-6 w-6 text-gray-600" />
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">
                          {{ member.first_name }} {{ member.last_name }}
                        </div>
                        <div class="text-sm text-gray-500">
                          {{ member.membership_id }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ member.user.email }}</div>
                    <div class="text-sm text-gray-500">{{ member.user.phone }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="{
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full': true,
                      'bg-green-100 text-green-800': member.membership_status === 'active',
                      'bg-red-100 text-red-800': member.membership_status === 'inactive',
                      'bg-yellow-100 text-yellow-800': member.membership_status === 'suspended'
                    }">
                      {{ member.membership_status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ member.accounts.length }} accounts
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(member.membership_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                      <Link :href="route('members.show', member.id)" class="text-indigo-600 hover:text-indigo-900">
                      View
                      </Link>
                      <Link v-if="$page.props.auth.user.role !== 'member'" :href="route('members.edit', member.id)"
                        class="text-yellow-600 hover:text-yellow-900">
                      Edit
                      </Link>

                      <!-- Status Actions -->
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
                    </div>
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
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AppLayout from '@/layouts/AppLayout.vue'
import { CheckCircle, CircleX, PlusCircle, TriangleAlert, User2Icon, Users } from 'lucide-vue-next'
import Pagination from '@/components/Pagination.vue'

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
