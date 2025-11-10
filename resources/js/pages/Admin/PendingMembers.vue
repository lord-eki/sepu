<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { User2Icon } from 'lucide-vue-next'

const members = ref<any[]>([])
const loading = ref(true)

// Fetch pending members
async function fetchPendingMembers() {
  loading.value = true
  try {
    const res = await fetch('/admin/pending-members/list')
    const data = await res.json()
    members.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(fetchPendingMembers)

const formatDate = (date: string) => new Date(date).toLocaleDateString()
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Pending Members', href: '/admin/pending-members' }]">
    <Head title="Pending Member Approvals" />

    <div class="p-6 bg-gray-50 min-h-screen space-y-6">
      <h1 class="text-2xl font-semibold text-blue-900">Pending Member Approvals</h1>

      <div v-if="loading" class="text-center py-10">Loading pending members...</div>

      <div v-else-if="members.length === 0" class="text-center py-10 text-gray-500">
        No pending member applications.
      </div>

      <div v-else class="overflow-x-auto rounded-lg shadow-sm bg-white border">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-blue-900 text-white">
            <tr>
              <th class="px-6 py-3 text-left font-medium">Member</th>
              <th class="px-6 py-3 text-left font-medium">Contact</th>
              <th class="px-6 py-3 text-left font-medium">Status</th>
              <th class="px-6 py-3 text-left font-medium">Date Joined</th>
              <th class="px-6 py-3 text-right font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-for="member in members" :key="member.id" class="hover:bg-blue-50 transition duration-150">
              <!-- Member Info -->
              <td class="px-6 py-4 flex items-center gap-3">
                <div v-if="member.profile_photo" class="h-10 w-10 rounded-full overflow-hidden">
                  <img :src="`/storage/${member.profile_photo}`" class="object-cover w-full h-full" />
                </div>
                <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                  <User2Icon class="h-6 w-6 text-gray-600" />
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ member.first_name }} {{ member.last_name }}</p>
                  <p class="text-xs text-gray-500">{{ member.membership_id || 'N/A' }}</p>
                </div>
              </td>

              <!-- Contact -->
              <td class="px-6 py-4">
                <p class="text-gray-900">{{ member.user?.email || '-' }}</p>
                <p class="text-xs text-gray-500">{{ member.user?.phone || '-' }}</p>
              </td>

              <!-- Status -->
              <td class="px-6 py-4">
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                  Pending
                </span>
              </td>

              <!-- Date Joined -->
              <td class="px-6 py-4 text-gray-600">{{ formatDate(member.membership_date || member.created_at) }}</td>

              <!-- Actions -->
              <td class="px-6 py-4 text-right">
                <Link :href="`/admin/members/${member.id}`" class="text-indigo-600 hover:text-indigo-900">View</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
