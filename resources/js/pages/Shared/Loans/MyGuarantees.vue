<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  guarantees: {
    type: Array,
    default: () => []
  }
})

const statusColor = (status) => {
  switch (status) {
    case 'accepted':
      return 'bg-emerald-100 text-emerald-700'
    case 'rejected':
      return 'bg-rose-100 text-rose-700'
    default:
      return 'bg-yellow-100 text-yellow-700'
  }
}

// Optional grouping
const pending = computed(() =>
  props.guarantees.filter(g => g.status === 'pending')
)
</script>

<template>
<AppLayout :breadcrumbs="[
    { title: 'Loans', href: route('my-loans') },
    { title: 'My Guarantees' }
]">

  <div class="p-6 space-y-6">

    <!-- Title -->
    <div>
      <h1 class="text-2xl font-bold text-gray-900">My Guarantees</h1>
      <p class="text-sm text-gray-500">All loan guarantee requests</p>
    </div>

    <!-- Empty state -->
    <div v-if="!guarantees.length" class="text-center py-12 text-gray-500">
      No guarantor requests found
    </div>

    <!-- List -->
    <div v-else class="grid gap-4">

      <div
        v-for="g in guarantees"
        :key="g.id"
        class="bg-white border rounded-xl p-5 shadow-sm hover:shadow-md transition flex justify-between items-center"
      >

        <!-- Left -->
        <div>
          <p class="font-semibold text-gray-900">
            {{ g.loan?.member?.first_name ?? '' }} {{ g.loan?.member?.last_name ?? '' }}
          </p>

          <p class="text-sm text-gray-500">
            Loan: {{ g.loan?.loan_number }}
          </p>

          <p class="text-sm text-gray-500">
            {{ g.loan?.purpose }}
          </p>

          <p class="text-sm mt-1 font-medium text-blue-800">
            KES {{ g.guaranteed_amount }}
          </p>
        </div>

        <!-- Right -->
        <div class="flex items-center gap-3">

          <!-- Status -->
          <span
            class="px-3 py-1 text-xs rounded-full font-semibold"
            :class="statusColor(g.status)"
          >
            {{ g.status }}
          </span>

          <!-- Action -->
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