<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { User2Icon } from 'lucide-vue-next'

const loading = ref(true)
const pendingMembers = ref<any[]>([])
const approvedMembers = ref<any[]>([])

const fetchMembers = async () => {
  loading.value = true
  try {
    const res = await fetch('/admin/pending-members/list')
    if (!res.ok) throw new Error('Failed to fetch members')

    const data = await res.json()
    pendingMembers.value = data.pending || []
    approvedMembers.value = data.approvedAwaitingActivation || []
  } catch (error) {
    console.error('Error fetching members:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchMembers)

const formatDate = (date: string) =>
  new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Pending Members', href: '/admin/pending-members' }]">
    <Head title="Pending Member Approvals" />

    <div class="p-6 bg-gray-50 min-h-screen space-y-8">
      <h1 class="text-2xl font-semibold text-blue-900">Member Approval Management</h1>

      <div v-if="loading" class="text-center py-10 text-gray-600">
        Loading members...
      </div>

      <div v-else>
        <!-- Pending Approvals Section -->
        <section>
          <h2 class="text-lg font-semibold text-blue-900 mb-4">
            Pending Approvals
          </h2>

          <div
            v-if="pendingMembers.length === 0"
            class="text-gray-500 text-center py-6 border rounded-lg bg-white"
          >
            No pending member applications.
          </div>

          <div
            v-else
            class="overflow-x-auto bg-white border rounded-lg shadow-sm"
          >
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-blue-100 text-blue-900">
                <tr>
                  <th class="px-6 py-3 text-left font-medium">Member</th>
                  <th class="px-6 py-3 text-left font-medium">Contact</th>
                  <th class="px-6 py-3 text-left font-medium">Status</th>
                  <th class="px-6 py-3 text-left font-medium">Date Joined</th>
                  <th class="px-6 py-3 text-right font-medium">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="member in pendingMembers"
                  :key="member.id"
                  class="hover:bg-blue-50/50 transition duration-150"
                >
                  <!-- Member Info -->
                  <td class="px-6 py-4 flex items-center gap-3">
                    <div
                      v-if="member.profile_photo"
                      class="h-10 w-10 rounded-full overflow-hidden"
                    >
                      <img
                        :src="`/storage/${member.profile_photo}`"
                        class="object-cover w-full h-full"
                      />
                    </div>
                    <div
                      v-else
                      class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center"
                    >
                      <User2Icon class="h-6 w-6 text-gray-600" />
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">
                        {{ member.first_name }} {{ member.last_name }}
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ member.membership_id || 'N/A' }}
                      </p>
                    </div>
                  </td>

                  <!-- Contact -->
                  <td class="px-6 py-4">
                    <p class="text-gray-900">{{ member.user?.email || '-' }}</p>
                    <p class="text-xs text-gray-500">{{ member.user?.phone || '-' }}</p>
                  </td>

                  <!-- Status -->
                  <td class="px-6 py-4">
                    <span
                      class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700"
                    >
                      Pending
                    </span>
                  </td>

                  <!-- Date Joined -->
                  <td class="px-6 py-4 text-gray-600">
                    {{ formatDate(member.membership_date || member.created_at) }}
                  </td>

                  <!-- Actions -->
                  <td class="px-6 py-4 text-right">
                    <Link
                      :href="route('members.show', member.id)"
                      class="text-indigo-600 hover:underline"
                    >
                      View
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Awaiting Activation Section -->
        <section>
          <h2 class="text-lg font-semibold text-orange-500 mt-10 mb-4">
            Pending Activation
          </h2>

          <div
            v-if="approvedMembers.length === 0"
            class="text-gray-500 text-center py-6 border rounded-lg bg-white"
          >
            No approved members awaiting activation.
          </div>

          <div
            v-else
            class="overflow-x-auto bg-white border rounded-lg shadow-sm"
          >
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-orange-100 text-blue-900">
                <tr>
                  <th class="px-6 py-3 text-left font-medium">Member</th>
                  <th class="px-6 py-3 text-left font-medium">Contact</th>
                  <th class="px-6 py-3 text-left font-medium">Status</th>
                  <th class="px-6 py-3 text-left font-medium">Date Approved</th>
                  <th class="px-6 py-3 text-right font-medium">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="member in approvedMembers"
                  :key="member.id"
                  class="hover:bg-orange-50/50 transition duration-150"
                >
                  <td class="px-6 py-4 flex items-center gap-3">
                    <div
                      v-if="member.profile_photo"
                      class="h-10 w-10 rounded-full overflow-hidden"
                    >
                      <img
                        :src="`/storage/${member.profile_photo}`"
                        class="object-cover w-full h-full"
                      />
                    </div>
                    <div
                      v-else
                      class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center"
                    >
                      <User2Icon class="h-6 w-6 text-gray-600" />
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">
                        {{ member.first_name }} {{ member.last_name }}
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ member.membership_id || 'N/A' }}
                      </p>
                    </div>
                  </td>

                  <td class="px-6 py-4">
                    <p class="text-gray-900">{{ member.user?.email || '-' }}</p>
                    <p class="text-xs text-gray-500">{{ member.user?.phone || '-' }}</p>
                  </td>

                  <td class="px-6 py-4">
                    <span
                      class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700"
                    >
                      Approved
                    </span>
                  </td>

                  <td class="px-6 py-4 text-gray-600">
                    {{ formatDate(member.updated_at) }}
                  </td>

                  <td class="px-6 py-4 text-right">
                    <Link
                      :href="route('members.show', member.id)"
                      class="text-indigo-600 hover:underline"
                    >
                      View
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
  </AppLayout>
</template>
