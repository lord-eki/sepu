<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

const activeTab = ref('all')

const props = defineProps({
  guarantees: Array,
  isAdmin: Boolean
})

const filtered = computed(() => {
  if (activeTab.value === 'all') return props.guarantees
  return props.guarantees.filter(g => g.status === activeTab.value)
})

const tabs = [
  { key: 'all', label: 'All' },
  { key: 'pending', label: 'Pending' },
  { key: 'accepted', label: 'Accepted' },
  { key: 'rejected', label: 'Rejected' }
]

const statusColor = (status) => ({
  accepted: 'bg-emerald-100 text-emerald-700',
  rejected: 'bg-rose-100 text-rose-700',
  pending: 'bg-yellow-100 text-yellow-700'
}[status] || 'bg-slate-100 text-slate-700')
</script>

<template>
<AppLayout :breadcrumbs="[
  { title: isAdmin ? 'Admin' : 'Loans', href: '/' },
  { title: 'Guarantees' }
]">

<div class="p-6 space-y-6">

  <!-- HEADER -->
  <div class="flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-bold">Guarantees</h1>
      <p class="text-sm text-gray-500">
        {{ isAdmin ? 'All system guarantees' : 'Loans you are guaranteeing' }}
      </p>
    </div>
  </div>

  <!-- TABS -->
  <div class="flex gap-2 border-b pb-2">
    <button
      v-for="t in tabs"
      :key="t.key"
      @click="activeTab = t.key"
      class="px-4 py-2 text-sm rounded-lg"
      :class="activeTab === t.key ? 'bg-blue-900 text-white' : 'bg-gray-100'"
    >
      {{ t.label }}
    </button>
  </div>

  <!-- EMPTY STATE -->
  <div v-if="!filtered.length" class="text-center py-10 text-gray-500">
    No guarantees found
  </div>

  <!-- LIST -->
  <div v-else class="grid gap-4">

    <div
      v-for="g in filtered"
      :key="g.id"
      class="bg-white border rounded-xl p-5 shadow-sm hover:shadow-md transition flex justify-between"
    >

      <!-- LEFT -->
      <div>
        <p class="font-semibold text-gray-900">
          {{ g.loan?.member?.first_name }} {{ g.loan?.member?.last_name }}
        </p>

        <p class="text-sm text-gray-500">
          Loan: {{ g.loan?.loan_number }}
        </p>

        <p class="text-sm text-gray-500">
          Amount: KES {{ g.guaranteed_amount }}
        </p>

        <!-- ADMIN EXTRA INFO -->
        <p v-if="isAdmin" class="text-xs text-gray-400 mt-1">
          Guarantor: {{ g.guarantor_member?.first_name }} {{ g.guarantor_member?.last_name }}
        </p>
      </div>

      <!-- RIGHT -->
      <div class="flex items-center gap-3">

        <span
          class="px-3 py-1 text-xs rounded-full font-semibold"
          :class="statusColor(g.status)"
        >
          {{ g.status }}
        </span>

        <button
          @click="router.visit(`/guarantor-requests/${g.loan_id}`)"
          class="bg-blue-950 hover:bg-blue-900 text-white text-sm px-4 py-2 rounded-lg"
        >
          View
        </button>

      </div>

    </div>
  </div>

</div>

</AppLayout>
</template>