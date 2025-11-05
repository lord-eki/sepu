<script setup lang="ts">
import { ref, computed } from 'vue'
import { usePage, Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'



const props = defineProps({
  notifications: Object,
  stats: Object,
  filters: Object
})

// Local filter state
const filterType = ref(props.filters?.type || 'all')
const filterStatus = ref(props.filters?.status || 'all')
const filterChannel = ref(props.filters?.channel || 'all')

// Build query URL dynamically for Inertia navigation
const buildUrl = (type: string, status: string, channel: string) => {
  let query = []
  if (type !== 'all') query.push(`type=${type}`)
  if (status !== 'all') query.push(`status=${status}`)
  if (channel !== 'all') query.push(`channel=${channel}`)
  return `/notifications?${query.join('&')}`
}

// Format time difference
const timeAgo = (dateStr: string) => {
  const date = new Date(dateStr)
  const diff = Math.floor((Date.now() - date.getTime()) / 60000)
  if (diff < 60) return `${diff} min ago`
  if (diff < 1440) return `${Math.floor(diff / 60)} hrs ago`
  return date.toLocaleDateString()
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Notifications' }]">
    <Head title="Notifications" />

    <div class="px-6 py-4">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800">Notifications</h1>
          <p class="text-sm text-gray-500">View and manage your latest updates</p>
        </div>
        <Link
            :href="route('notifications.settings')"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition mt-3 sm:mt-0"
            >
            Notification Settings
            </Link>

      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl">
          <h3 class="text-sm font-medium text-gray-700">Total Notifications</h3>
          <p class="text-2xl font-semibold text-blue-700">{{ props.stats.total }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 p-4 rounded-xl">
          <h3 class="text-sm font-medium text-gray-700">Unread Notifications</h3>
          <p class="text-2xl font-semibold text-orange-700">{{ props.stats.unread }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 p-4 rounded-xl">
          <h3 class="text-sm font-medium text-gray-700">Types</h3>
          <p class="text-2xl font-semibold text-green-700">{{ Object.keys(props.stats.by_type || {}).length }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <select v-model="filterType" class="border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
          <option value="all">All Types</option>
          <option value="transaction">Transaction</option>
          <option value="loan">Loan</option>
          <option value="dividend">Dividend</option>
          <option value="general">General</option>
          <option value="system">System</option>
        </select>

        <select v-model="filterStatus" class="border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
          <option value="all">All Status</option>
          <option value="unread">Unread</option>
          <option value="read">Read</option>
        </select>

        <select v-model="filterChannel" class="border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
          <option value="all">All Channels</option>
          <option value="sms">SMS</option>
          <option value="email">Email</option>
          <option value="system">System</option>
        </select>

        <Link
          :href="buildUrl(filterType, filterStatus, filterChannel)"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg transition"
        >
          Apply Filters
        </Link>
      </div>

      <!-- Notifications Table -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
            <tr>
              <th class="px-6 py-3 text-left">Title</th>
              <th class="px-6 py-3 text-left">Type</th>
              <th class="px-6 py-3 text-left">Channel</th>
              <th class="px-6 py-3 text-left">Status</th>
              <th class="px-6 py-3 text-left">Date</th>
              <th class="px-6 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in props.notifications.data"
              :key="item.id"
              :class="item.is_read ? 'bg-white' : 'bg-orange-50'"
              class="border-b border-gray-100 hover:bg-gray-50 transition"
            >
              <td class="px-6 py-3">
                <Link
                  :href="`/notifications/${item.id}`"
                  class="font-medium text-blue-700 hover:underline"
                >
                  {{ item.title }}
                </Link>
                <p class="text-gray-500 text-xs">{{ item.message.slice(0, 60) }}...</p>
              </td>
              <td class="px-6 py-3 capitalize">{{ item.type }}</td>
              <td class="px-6 py-3 uppercase">{{ item.channel }}</td>
              <td class="px-6 py-3">
                <span
                  :class="item.is_read ? 'text-green-700' : 'text-orange-600'"
                  class="font-medium"
                >
                  {{ item.is_read ? 'Read' : 'Unread' }}
                </span>
              </td>
              <td class="px-6 py-3 text-gray-600">{{ timeAgo(item.created_at) }}</td>
              <td class="px-6 py-3 text-right">
                <Link
                  :href="`/notifications/${item.id}`"
                  class="text-blue-600 hover:underline text-sm font-medium"
                >
                  View
                </Link>
              </td>
            </tr>

            <tr v-if="!props.notifications.data.length">
              <td colspan="6" class="text-center py-6 text-gray-500">No notifications found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
