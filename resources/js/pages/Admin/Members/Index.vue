<template>
  <AppLayout :breadcrumbs="[{ title: 'Members', href: '/members' }]">
    <Head title="Members Management" />

    <!-- Page Wrapper -->
    <div class="min-h-screen bg-[#f9fafb] pb-16">

      <!-- Header Section -->
      <div class="bg-gradient-to-r from-[#0a2342] via-[#0c2e55] to-[#103a66] rounded-2xl mt-5 px-2 mx-2 shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col sm:flex-row sm:items-center sm:justify-between text-white">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Members Management</h1>
            <p class="text-blue-100 text-xs sm:text-sm mt-1">Manage, view, and organize SACCO members efficiently.</p>
          </div>
          <Link
            v-if="$page.props.auth.user.role !== 'member'"
            :href="route('members.create')"
            class="mt-4 sm:mt-0 inline-flex items-center gap-2 rounded-xl bg-[#f97316] w-fit px-4 py-2 text-sm font-medium text-white shadow-md hover:bg-orange-600 transition-all duration-200"
          >
            <PlusCircle class="w-4 h-4" />
            Add Member
          </Link>
        </div>
      </div>

      <!-- Flash Messages -->
      <div ref="flashBox" class="max-w-3xl mx-auto mt-6 px-4">
        <transition
          enter-active-class="transition ease-out duration-300"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition ease-in duration-200"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-2"
        >
          <div
            v-if="flashMessage"
            class="flex gap-3 mb-4 rounded-lg border shadow-sm items-center p-4"
            :class="[
              flashType === 'success'
                ? 'bg-green-50 border-green-200 text-green-700'
                : 'bg-red-50 border-red-200 text-red-700'
            ]"
          >
            <component
              :is="flashType === 'success' ? CheckCircle : AlertCircle"
              class="h-5 w-5"
              :class="flashType === 'success' ? 'text-green-600' : 'text-red-600'"
            />
            <p class="ml-2 text-sm">{{ flashMessage }}</p>
            <button @click="flashMessage = null" class="ml-auto text-gray-400 hover:text-gray-600">✕</button>
          </div>
        </transition>
      </div>

      <!-- Content Section -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 mt-8 animate-fadeIn">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div
            v-for="card in [
              { label: 'Total Members', value: stats.total, icon: Users, color: 'text-blue-600 bg-blue-50' },
              { label: 'Active', value: stats.active, icon: CheckCircle, color: 'text-green-600 bg-green-50' },
              { label: 'Inactive', value: stats.inactive, icon: CircleX, color: 'text-red-600 bg-red-50' },
              { label: 'Suspended', value: stats.suspended, icon: TriangleAlert, color: 'text-yellow-600 bg-yellow-50' }
            ]"
            :key="card.label"
            class="p-5 rounded-2xl bg-white/90 shadow-sm border border-gray-100 backdrop-blur-sm hover:shadow-md transition"
          >
            <div class="flex items-center gap-4">
              <div :class="['rounded-xl p-3', card.color]">
                <component :is="card.icon" class="h-6 w-6" />
              </div>
              <div>
                <p class="text-sm text-gray-500">{{ card.label }}</p>
                <p class="text-xl font-semibold text-gray-900">{{ card.value }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="rounded-2xl bg-white shadow-sm border border-gray-100 p-6">
          <h3 class="font-semibold text-gray-800 mb-4">Filter & Sort</h3>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Search</label>
              <div class="relative">
                <input
                  v-model="form.search"
                  type="text"
                  placeholder="Search members..."
                  class="pl-9 pr-3 py-2 w-full rounded-lg border shadow-sm focus:ring-[#0a2342] focus:border-[#0a2342] sm:text-sm"
                  @input="search"
                />
                <svg
                  class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                </svg>
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <select
                v-model="form.status"
                class="mt-1 w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:ring-[#0a2342] focus:border-[#0a2342] sm:text-sm"
                @change="search"
              >
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>

            <!-- Sort By -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Sort By</label>
              <select
                v-model="form.sortBy"
                class="mt-1 w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:ring-[#0a2342] focus:border-[#0a2342] sm:text-sm"
                @change="search"
              >
                <option value="created_at">Date Joined</option>
                <option value="first_name">First Name</option>
                <option value="last_name">Last Name</option>
                <option value="membership_id">Member ID</option>
              </select>
            </div>

            <!-- Sort Direction -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Direction</label>
              <select
                v-model="form.sortDirection"
                class="mt-1 w-full rounded-lg border border-gray-300 p-2 shadow-sm focus:ring-[#0a2342] focus:border-[#0a2342] sm:text-sm"
                @change="search"
              >
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
        <div class="rounded-2xl bg-white shadow-sm border border-gray-100 overflow-hidden hidden sm:block">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-[#0a2342]/90 text-white">
                <tr>
                  <th class="px-6 py-3 text-left font-medium">Member</th>
                  <th class="px-6 py-3 text-left font-medium">Contact</th>
                  <th class="px-6 py-3 text-left font-medium">Status</th>
                  <th class="px-6 py-3 text-left font-medium">Accounts</th>
                  <th class="px-6 py-3 text-left font-medium">Date Joined</th>
                  <th class="px-6 py-3 text-right font-medium">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="member in members.data"
                  :key="member.id"
                  class="hover:bg-blue-50 transition duration-150"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <img
                        v-if="member.profile_photo"
                        :src="`/storage/${member.profile_photo}`"
                        class="h-10 w-10 rounded-full object-cover"
                      />
                      <div
                        v-else
                        class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center"
                      >
                        <User2Icon class="h-6 w-6 text-gray-600" />
                      </div>
                      <div>
                        <p class="font-medium text-gray-900">{{ member.first_name }} {{ member.last_name }}</p>
                        <p class="text-xs text-gray-500">{{ member.membership_id }}</p>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4">
                    <p class="text-gray-900">{{ member.user.email }}</p>
                    <p class="text-xs text-gray-500">{{ member.user.phone }}</p>
                  </td>

                  <td class="px-6 py-4">
                    <span
                      :class="{
                        'px-2 py-1 rounded-full text-xs font-medium': true,
                        'bg-green-100 text-green-700': member.membership_status === 'active',
                        'bg-red-100 text-red-700': member.membership_status === 'inactive',
                        'bg-yellow-100 text-yellow-700': member.membership_status === 'suspended'
                      }"
                    >
                      {{ member.membership_status }}
                    </span>
                  </td>

                  <td class="px-6 py-4 text-gray-600">{{ member.accounts.length }} accounts</td>
                  <td class="px-6 py-4 text-gray-600">{{ formatDate(member.membership_date) }}</td>
                  <td class="px-6 py-4 text-right space-x-3">
                    <Link :href="route('members.show', member.id)" class="text-indigo-600 hover:text-indigo-900">View</Link>
                    <Link
                      v-if="$page.props.auth.user.role !== 'member'"
                      :href="route('members.edit', member.id)"
                      class="text-orange-600 hover:text-orange-800"
                    >Edit</Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <Pagination :data="members" />
        </div>
      </div>

      <!-- Floating Add Member Button for Mobile -->
      <Link
        v-if="$page.props.auth.user.role !== 'member'"
        :href="route('members.create')"
        class="fixed bottom-6 right-6 bg-[#0a2342] text-white p-4 rounded-full shadow-lg hover:bg-orange-600 transition sm:hidden"
      >
        <PlusCircle class="w-5 h-5" />
      </Link>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AppLayout from '@/layouts/AppLayout.vue'
import { CheckCircle, CircleX, PlusCircle, TriangleAlert, User2Icon, Users, AlertCircle } from 'lucide-vue-next'
import Pagination from '@/components/Pagination.vue'

// Flash Message Handling
const page = usePage()
const flash = computed(() => page.props.flash || {})
const flashMessage = ref(null)
const flashType = ref('success')
const flashBox = ref(null)

watch(flash, (val) => {
  if (val.success) {
    flashMessage.value = val.success
    flashType.value = 'success'
  } else if (val.error) {
    flashMessage.value = val.error
    flashType.value = 'error'
  }
  if (flashMessage.value) {
    window.scrollTo({ top: 0, behavior: 'smooth' })
    flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    setTimeout(() => (flashMessage.value = null), 5000)
  }
}, { immediate: true, deep: true })

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

const search = debounce(() => {
  router.get(route('members.index'), form.value, {
    preserveState: true,
    replace: true
  })
}, 300)

const formatDate = (date) => new Date(date).toLocaleDateString()
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fadeIn {
  animation: fadeIn 0.4s ease-in-out;
}
</style>
