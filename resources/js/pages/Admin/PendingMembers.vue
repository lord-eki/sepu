<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { User2Icon } from 'lucide-vue-next'

const loading = ref(true)
const pendingMembers = ref<any[]>([])
const awaitingActivation = ref<any[]>([])
const paymentFilter = ref<'all' | 'paid' | 'unpaid'>('all')

// Search/filter text
const pendingSearch = ref('')
const approvedSearch = ref('')

const fetchMembers = async () => {
  loading.value = true
  try {
    const res = await fetch('/admin/pending-members/list')
    if (!res.ok) throw new Error('Failed to fetch members')

    const data = await res.json()
    pendingMembers.value = data.pending || []

    // Merge both approved groups
    awaitingActivation.value = [
      ...(data.approvedAwaitingActivation || []),
      ...(data.approvedButUnpaid || []),
    ]
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

// Computed filters
const filteredPending = computed(() =>
  pendingMembers.value.filter(member => {
    const term = pendingSearch.value.toLowerCase()
    return (
      member.first_name.toLowerCase().includes(term) ||
      member.last_name.toLowerCase().includes(term) ||
      (member.membership_id || '').toLowerCase().includes(term) ||
      (member.user?.email || '').toLowerCase().includes(term) ||
      (member.user?.phone || '').toLowerCase().includes(term)
    )
  })
)

const filteredApproved = computed(() =>
  awaitingActivation.value
    .filter(member => {
      const term = approvedSearch.value.toLowerCase()
      return (
        member.first_name.toLowerCase().includes(term) ||
        member.last_name.toLowerCase().includes(term) ||
        (member.membership_id || '').toLowerCase().includes(term) ||
        (member.user?.email || '').toLowerCase().includes(term) ||
        (member.user?.phone || '').toLowerCase().includes(term)
      )
    })
    .filter(member => {
      if (paymentFilter.value === 'all') return true

      const isPaid =
        member.accounts.some(a => a.account_type === 'share_deposits' && a.available_balance >= 7500) &&
        member.accounts.some(a => a.account_type === 'share_capital' && a.available_balance >= 5000)

      if (paymentFilter.value === 'paid') return isPaid
      if (paymentFilter.value === 'unpaid') return !isPaid
      return true
    })
)
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Members Approvals', href: '/admin/pending-members' }]">
    <Head title="Member Approvals" />

    <div class="p-6 bg-gray-50 min-h-screen space-y-8">
      <h1 class="text-2xl font-semibold text-[#0a2342]">Member Approval</h1>

      <div v-if="loading" class="text-center py-10 text-gray-600">
        Loading members...
      </div>

      <div v-else>
        <!--  Pending Approvals -->
        <section>
          <h2 class="text-lg font-semibold text-blue-900 mb-2">Pending Approvals</h2>

          <!-- Search box -->
          <input
            type="text"
            v-model="pendingSearch"
            placeholder="Search by name, email, phone, or ID"
            class="mb-4 w-full max-w-md px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring focus:border-blue-300"
          />

          <div v-if="filteredPending.length === 0" class="text-gray-500 text-center py-6 border rounded-lg bg-white">
            No pending member applications.
          </div>

          <div v-else class="overflow-x-auto bg-white border rounded-lg shadow-sm">
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
                  v-for="member in filteredPending"
                  :key="member.id"
                  class="hover:bg-blue-50/50 transition duration-150"
                >
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

                  <td class="px-6 py-4">
                    <p class="text-gray-900">{{ member.user?.email || '-' }}</p>
                    <p class="text-xs text-gray-500">{{ member.user?.phone || '-' }}</p>
                  </td>

                  <td class="px-6 py-4">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Pending</span>
                  </td>

                  <td class="px-6 py-4 text-gray-600">
                    {{ formatDate(member.membership_date || member.created_at) }}
                  </td>

                  <td class="px-6 py-4 text-right">
                    <Link :href="route('members.show', member.id)" class="text-indigo-600 hover:underline">View</Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!--  Awaiting Activation -->
        <section>
          <h2 class="text-lg font-semibold text-orange-500 mt-10 mb-2">Approved Members (Awaiting Activation)</h2>

          <!-- Search box -->
          <div class="flex items-center gap-4 mb-4">
          <input
            type="text"
            v-model="approvedSearch"
            placeholder="Search by name, email, phone, or ID"
            class="w-full max-w-md px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring focus:border-orange-300"
          />

          <select v-model="paymentFilter" class="px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring focus:border-orange-300">
            <option value="all">All</option>
            <option value="paid">Paid (Ready)</option>
            <option value="unpaid">Awaiting Payment</option>
          </select>
        </div>


          <div
            v-if="filteredApproved.length === 0"
            class="text-gray-500 text-center py-6 border rounded-lg bg-white"
          >
            No approved members awaiting activation.
          </div>

          <div v-else class="overflow-x-auto bg-white border rounded-lg shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead class="bg-orange-100 text-blue-900">
                <tr>
                  <th class="px-6 py-3 text-left font-medium">Member</th>
                  <th class="px-6 py-3 text-left font-medium">Contact</th>
                  <th class="px-6 py-3 text-left font-medium">Payment Status</th>
                  <th class="px-6 py-3 text-left font-medium">Date Approved</th>
                  <th class="px-6 py-3 text-right font-medium">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="member in filteredApproved"
                  :key="member.id"
                  class="hover:bg-orange-50/50 transition duration-150"
                >
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

                  <td class="px-6 py-4">
                    <p class="text-gray-900">{{ member.user?.email || '-' }}</p>
                    <p class="text-xs text-gray-500">{{ member.user?.phone || '-' }}</p>
                  </td>

                  <td class="px-6 py-4">
                    <span
                      v-if="member.accounts.some(a => a.account_type === 'share_deposits' && a.available_balance >= 7500) &&
                              member.accounts.some(a => a.account_type === 'share_capital' && a.available_balance >= 5000)"
                      class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700"
                    >
                      Paid (Ready)
                    </span>
                    <span
                      v-else
                      class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700"
                    >
                      Awaiting Payment
                    </span>
                  </td>

                  <td class="px-6 py-4 text-gray-600">
                    {{ formatDate(member.updated_at) }}
                  </td>

                  <td class="px-6 py-4 text-right">
                    <Link :href="route('members.show', member.id)" class="text-indigo-600 hover:underline">View</Link>
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
