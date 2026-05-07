<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  guarantees: Object, // paginator object
  isAdmin: Boolean
})

const activeTab = ref('all')
const search = ref('')

/**
 * Extract real data from Laravel paginator
 */
const baseList = computed(() => props.guarantees?.data || [])

/**
 * Filtered + searched list
 */
const filtered = computed(() => {
  let list = baseList.value

  // status filter
  if (activeTab.value !== 'all') {
    list = list.filter(g => g.status === activeTab.value)
  }

  // search filter
  if (search.value.trim()) {
    const s = search.value.toLowerCase()

    list = list.filter(g =>
      g.loan?.loan_number?.toLowerCase().includes(s) ||
      g.loan?.member?.first_name?.toLowerCase().includes(s) ||
      g.loan?.member?.last_name?.toLowerCase().includes(s) ||
      g.guarantorMember?.first_name?.toLowerCase().includes(s) ||
      g.guarantorMember?.last_name?.toLowerCase().includes(s)
    )
  }

  return list
})

const tabs = [
  { key: 'all', label: 'All', color: 'bg-slate-100 text-slate-700' },
  { key: 'pending', label: 'Pending', color: 'bg-amber-100 text-amber-700' },
  { key: 'accepted', label: 'Accepted', color: 'bg-emerald-100 text-emerald-700' },
  { key: 'rejected', label: 'Rejected', color: 'bg-rose-100 text-rose-700' }
]

const statusColor = (status) => ({
  accepted: 'bg-emerald-100 text-emerald-700 border-emerald-200',
  rejected: 'bg-rose-100 text-rose-700 border-rose-200',
  pending: 'bg-amber-100 text-amber-700 border-amber-200'
}[status] || 'bg-slate-100 text-slate-700')

</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: isAdmin ? 'Admin' : 'Loans', href: '/' },
    { title: 'Guarantors' }
  ]">

    <div
      class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 p-6">

      <!-- HEADER -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            Guarantor Requests
          </h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ isAdmin ? 'All system guarantee records' : 'Your active guarantees' }}
          </p>
        </div>

        <!-- SEARCH -->
        <div class="w-full md:w-80">
          <input v-model="search" type="text" placeholder="Search loan, member, guarantor..."
            class="w-full px-4 py-2 rounded-xl border bg-white dark:bg-gray-900 dark:border-gray-700 shadow-sm focus:ring-2 focus:ring-blue-900 outline-none" />
        </div>

      </div>

      <!-- TABS -->
      <div class="flex flex-wrap gap-2 mb-6">
        <button v-for="t in tabs" :key="t.key" @click="activeTab = t.key"
          class="px-4 py-2 text-sm rounded-xl font-medium transition-all duration-200 shadow-sm" :class="activeTab === t.key
    ? 'bg-blue-900 text-white shadow-md'
    : 'bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-orange-50'">
          {{ t.label }}
        </button>
      </div>

      <!-- EMPTY STATE -->
      <div v-if="!filtered.length" class="text-center py-16">
        <div class="text-gray-400 text-sm">
          No guarantor records found
        </div>
      </div>

      <!-- LIST -->
      <div v-else class="grid gap-4">

        <div v-for="g in filtered" :key="g.id"
          class="group bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:shadow-lg transition-all duration-300 flex justify-between items-start">

          <!-- LEFT CONTENT -->
          <div class="space-y-2">

            <!-- Borrower -->
            <p class="text-gray-900 dark:text-white font-semibold">
              {{ g.loan?.member?.first_name }} {{ g.loan?.member?.last_name }}
            </p>

            <!-- Loan -->
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Loan No:
              <span class="font-medium text-blue-900 dark:text-blue-400">
                {{ g.loan?.loan_number }}
              </span>
            </p>

            <!-- Amount -->
            <p class="text-sm text-gray-600 dark:text-gray-300">
              Guaranteed Amount:
              <span class="font-semibold text-orange-600">
                KES {{ Number(g.guaranteed_amount).toLocaleString() }}
              </span>
            </p>

            <!-- Guarantor (Admin only) -->
            <p v-if="isAdmin" class="text-xs text-gray-400 pt-1">
              Guarantor:
              {{ g.guarantorMember?.first_name }} {{ g.guarantorMember?.last_name }}
            </p>

          </div>

          <!-- RIGHT ACTIONS -->
          <div class="flex flex-col items-end gap-3">

            <!-- STATUS -->
            <span class="px-3 py-1 text-xs rounded-full font-semibold border" :class="statusColor(g.status)">
              {{ g.status }}
            </span>

            <!-- VIEW -->
            <button @click="router.visit(`/guarantor-requests/${g.loan_id}`)"
              class="px-4 py-2 text-sm rounded-xl bg-gradient-to-r from-blue-950 to-blue-800 text-white hover:from-orange-500 hover:to-orange-600 transition shadow-md">
              View Details
            </button>

          </div>

        </div>

      </div>

    </div>

  </AppLayout>
</template>