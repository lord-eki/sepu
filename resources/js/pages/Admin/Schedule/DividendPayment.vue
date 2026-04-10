<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const props = defineProps<{
  dividend: any
  memberDividends: any[]
  summary: any
  year: number
  alreadyRun: boolean
  filters: any
}>()

// -------------------------
// Selection State
// -------------------------
const selected = ref<number[]>(
  props.memberDividends
    .filter(m => m.status === 'pending' && m.eligible)
    .map(m => m.id)
)

// -------------------------
// Sync on reload
// -------------------------
watch(
  () => props.memberDividends,
  () => {
    selected.value = props.memberDividends
      .filter(m => m.status === 'pending' && m.eligible)
      .map(m => m.id)
  },
  { immediate: true }
)

// -------------------------
// Toggle
// -------------------------
const toggleAll = () => {
  const selectable = props.memberDividends.filter(
    m => m.status === 'pending' && m.eligible
  )

  if (selected.value.length === selectable.length) {
    selected.value = []
  } else {
    selected.value = selectable.map(m => m.id)
  }
}

const toggleOne = (id: number) => {
  if (selected.value.includes(id)) {
    selected.value = selected.value.filter(i => i !== id)
  } else {
    selected.value.push(id)
  }
}

// -------------------------
// Form
// -------------------------
const form = useForm({
  dividend_id: props.dividend?.id,
  year: props.year,
  entries: [] as any[]
})

// -------------------------
// Modal
// -------------------------
const showConfirm = ref(false)

const prepareRun = () => {
  form.entries = props.memberDividends
    .filter(m => selected.value.includes(m.id))
    .map(m => ({
      member_dividend_id: m.id,
      member_id: m.member_id,
      account_id: m.dividend_account_id,
      dividend_amount: m.dividend_amount
    }))

  showConfirm.value = true
}

const runSchedule = () => {
  form.post('/schedule/dividend-payment/run', {
    onFinish: () => (showConfirm.value = false)
  })
}

// -------------------------
// Helpers
// -------------------------
const selectableCount = computed(() =>
  props.memberDividends.filter(m => m.status === 'pending' && m.eligible).length
)
</script>

<template>
<AppLayout :breadcrumbs="[
  { title: 'Schedules', href: route('schedule.index') },
  { title: 'Dividend Payments' },
]">

<Head title="Dividend Payments" />

<div class="p-6 space-y-6">

  <!-- HEADER -->
  <div class="flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-bold">Dividend Payments</h1>
      <p class="text-gray-500 text-sm">
        Year {{ year }} dividend distribution
      </p>
    </div>

    <div class="text-sm bg-white px-4 py-2 rounded-xl shadow">
      Rate: {{ dividend?.dividend_rate ?? 0 }}%
    </div>
  </div>

  <!-- MESSAGE -->
  <div v-if="!dividend" class="bg-yellow-100 text-yellow-800 p-3 rounded-lg">
    No approved dividend found for this year.
  </div>

  <!-- SUMMARY -->
  <div v-else class="grid md:grid-cols-4 gap-4">

    <div class="card">
      Members<br>
      <strong>{{ summary.total_members }}</strong>
    </div>

    <div class="card">
      Pending<br>
      <strong>{{ summary.pending_count }}</strong>
    </div>

    <div class="card">
      Paid<br>
      <strong>{{ summary.paid_count }}</strong>
    </div>

    <div class="card">
      Pending Amount<br>
      <strong>KES {{ summary.pending_amount.toLocaleString() }}</strong>
    </div>

  </div>

  <!-- TABLE -->
  <div class="bg-white rounded-2xl shadow overflow-hidden">

    <table class="w-full text-sm">

      <thead class="bg-gray-50 text-gray-600">
        <tr>
          <th class="p-3">
            <input
              type="checkbox"
              :checked="selected.length === selectableCount"
              @change="toggleAll"
              :disabled="alreadyRun"
            />
          </th>

          <th>Member</th>
          <th>Shares</th>
          <th>Dividend</th>
          <th>Status</th>
        </tr>
      </thead>

      <tbody>

        <tr
          v-for="m in memberDividends"
          :key="m.id"
          class="border-t hover:bg-gray-50"
        >

          <td class="p-3">
            <input
              type="checkbox"
              :disabled="!m.eligible || m.status === 'paid' || alreadyRun"
              :checked="selected.includes(m.id)"
              @change="toggleOne(m.id)"
            />
          </td>

          <td>
            <div class="font-medium">{{ m.member_name }}</div>
            <div class="text-xs text-gray-500">{{ m.membership_id }}</div>
          </td>

          <td>
            {{ m.shares_balance.toLocaleString() }}
          </td>

          <td class="font-semibold text-green-600">
            KES {{ m.dividend_amount.toLocaleString() }}
          </td>

          <td>
            <span
              class="px-2 py-1 rounded-full text-xs"
              :class="m.status === 'paid'
                ? 'bg-green-100 text-green-700'
                : m.eligible
                  ? 'bg-yellow-100 text-yellow-700'
                  : 'bg-red-100 text-red-700'"
            >
              {{ m.status === 'paid'
                ? 'Paid'
                : m.eligible
                  ? 'Pending'
                  : 'Ineligible'
              }}
            </span>
          </td>

        </tr>

      </tbody>

    </table>

  </div>

  <!-- ACTION BAR -->
  <div class="flex justify-between items-center sticky bottom-0 bg-white p-4 shadow rounded-xl">

    <p class="text-sm text-gray-600">
      Selected: <strong>{{ selected.length }}</strong>
    </p>

    <button
      :disabled="selected.length === 0 || alreadyRun"
      @click="prepareRun"
      class="btn-primary"
    >
      Run Dividend Payment
    </button>

  </div>

  <!-- CONFIRM MODAL -->
  <div v-if="showConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center">

    <div class="bg-white rounded-xl p-6 w-full max-w-md space-y-4">

      <h2 class="text-lg font-bold">Confirm Dividend Payment</h2>

      <p class="text-sm text-gray-600">
        You are about to pay <strong>{{ selected.length }}</strong> members.
      </p>

      <div class="flex justify-end gap-2">

        <button @click="showConfirm = false" class="px-4 py-2">
          Cancel
        </button>

        <button
          @click="runSchedule"
          class="bg-purple-600 text-white px-4 py-2 rounded-lg"
        >
          Confirm & Pay
        </button>

      </div>

    </div>

  </div>

</div>
</AppLayout>
</template>

<style scoped>
.card {
  background: white;
  padding: 1rem;
  border-radius: 0.75rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  text-align: center;
}

.btn-primary {
  background: #7c3aed;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
}

.btn-primary:hover {
  background: #6d28d9;
}

.btn-primary:disabled {
  opacity: 0.5;
}
</style>