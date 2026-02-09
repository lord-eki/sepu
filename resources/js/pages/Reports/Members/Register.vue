<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { format } from 'date-fns'

const props = defineProps<{
  members: any
  summary: {
    total_members: number
    active_members: number
    inactive_members: number
    suspended_members: number
    new_members_this_month: number
  }
  filters: {
    status?: string
    county?: string
    start_date?: string
    end_date?: string
  }
}>()

const status = ref(props.filters.status || '')
const county = ref(props.filters.county || '')
const startDate = ref(props.filters.start_date || '')
const endDate = ref(props.filters.end_date || '')

const breadcrumbs = [
  { title: 'Member Reports', href: route('reports.membersReport.index') },
  { title: 'Member Register' },
]

function applyFilters() {
  router.get(
    route('reports.members.register'),
    {
      status: status.value || undefined,
      county: county.value || undefined,
      start_date: startDate.value || undefined,
      end_date: endDate.value || undefined,
    },
    { preserveState: true, replace: true }
  )
}

// Apply filters on Enter key in any input
function onEnterKey(event: KeyboardEvent) {
  if (event.key === 'Enter') applyFilters()
}

// Format date nicely
function formatDate(date: string | null) {
  if (!date) return '-'
  try {
    return format(new Date(date), 'dd MMM yyyy')
  } catch {
    return date
  }
}
</script>

<template>

  <Head title="Member Register" />

  <AppLayout title="Member Register" :breadcrumbs="breadcrumbs">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mx-5 mt-5 mb-8">
      <div class="rounded-2xl bg-blue-50 dark:bg-[#0a2342] p-4 border border-blue-200 dark:border-blue-900">
        <p class="text-sm text-blue-900 dark:text-blue-300">Total Members</p>
        <p class="text-xl font-bold text-[#0a2342] dark:text-white">{{ summary.total_members }}</p>
      </div>
      <div class="rounded-2xl bg-green-50 dark:bg-green-900 p-4 border border-green-200 dark:border-green-800">
        <p class="text-sm text-green-800 dark:text-green-300">Active</p>
        <p class="text-xl font-bold text-green-600">{{ summary.active_members }}</p>
      </div>
      <div class="rounded-2xl bg-gray-50 dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-800 dark:text-gray-300">Inactive</p>
        <p class="text-xl font-bold text-gray-600">{{ summary.inactive_members }}</p>
      </div>
      <div class="rounded-2xl bg-red-50 dark:bg-red-900 p-4 border border-red-200 dark:border-red-800">
        <p class="text-sm text-red-800 dark:text-red-300">Suspended</p>
        <p class="text-xl font-bold text-red-600">{{ summary.suspended_members }}</p>
      </div>
      <div class="rounded-2xl bg-orange-50 dark:bg-orange-900 p-4 border border-orange-200 dark:border-orange-800">
        <p class="text-sm text-orange-800 dark:text-orange-300">New This Month</p>
        <p class="text-xl font-bold text-orange-600">{{ summary.new_members_this_month }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4 mx-5 mt-5 mb-6">
      <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <select v-model="status" @keydown.enter="onEnterKey"
          class="rounded-lg border border-blue-300 dark:border-blue-900 px-3 py-2 bg-white dark:bg-blue-950 text-[#0a2342] dark:text-white">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="suspended">Suspended</option>
        </select>

        <input type="text" v-model="county" placeholder="County" @keydown.enter="onEnterKey"
          class="rounded-lg border border-blue-300 dark:border-blue-900 px-3 py-2 bg-white dark:bg-blue-950 text-[#0a2342] dark:text-white" />

        <div class="flex items-center gap-2">
          <input type="date" v-model="startDate" @keydown.enter="onEnterKey"
            class="rounded-xl border border-blue-300 dark:border-blue-900 px-3 py-2 bg-white dark:bg-blue-950 text-[#0a2342] dark:text-white" />
          <span class="text-gray-500 dark:text-gray-300">-</span>
          <input type="date" v-model="endDate" @keydown.enter="onEnterKey"
            class="rounded-xl border border-blue-300 dark:border-blue-900 px-3 py-2 bg-white dark:bg-blue-950 text-[#0a2342] dark:text-white" />
        </div>
      </div>

      <button @click="applyFilters"
        class="mt-2 sm:mt-0 rounded-xl hover:cursor-pointer bg-blue-900 hover:bg-blue-900 text-white px-5 py-2 transition">
        Apply Filters
      </button>
    </div>

    <!-- Table -->
    <div
      class="overflow-x-auto rounded-2xl mx-5 border border-blue-200 dark:border-blue-900 bg-white dark:bg-[#0a2342] shadow-sm">
      <table class="min-w-full text-sm">
        <thead class="bg-blue-50 dark:bg-blue-900 text-[#0a2342] dark:text-white sticky top-0">
          <tr>
            <th class="px-4 py-3 text-left">Member</th>
            <th class="px-4 py-3 text-left">Membership ID</th>
            <th class="px-4 py-3 text-left">Phone</th>
            <th class="px-4 py-3 text-left">County</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Joined</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-blue-200 dark:divide-blue-900">
          <tr v-for="member in members.data" :key="member.id"
            class="hover:bg-blue-50 dark:hover:bg-blue-900 transition-colors">
            <td class="px-4 py-3 font-medium">{{ member.first_name }} {{ member.last_name }}</td>
            <td class="px-4 py-3">{{ member.membership_id }}</td>
            <td class="px-4 py-3">{{ member.user?.phone ?? '-' }}</td>
            <td class="px-4 py-3">{{ member.county }}</td>
            <td class="px-4 py-3 capitalize">{{ member.membership_status }}</td>
            <td class="px-4 py-3">{{ formatDate(member.membership_date) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
