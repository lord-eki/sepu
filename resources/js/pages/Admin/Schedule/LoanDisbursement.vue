<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const props = defineProps<{
  loans: any[]
  summary: any
  filters: any
}>()

// -------------------------
// Selection State
// -------------------------
const selected = ref<number[]>(
  props.loans.map(l => l.id)
)

const selectableIds = computed(() =>
  props.loans.map(l => l.id)
)

// -------------------------
// Sync when filters change
// -------------------------
watch(
  () => props.loans,
  () => {
    selected.value = props.loans.map(l => l.id)
  },
  { immediate: true }
)

// -------------------------
// Toggle
// -------------------------
const toggleAll = () => {
  if (selected.value.length === selectableIds.value.length) {
    selected.value = []
  } else {
    selected.value = [...selectableIds.value]
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
  loan_ids: [] as number[],
  year: new Date().getFullYear()
})

// -------------------------
// Modal
// -------------------------
const showConfirm = ref(false)

const prepareRun = () => {
  form.loan_ids = props.loans
    .filter(l => selected.value.includes(l.id))
    .map(l => l.id)

  showConfirm.value = true
}

const runSchedule = () => {
  form.post('/schedule/loan-disbursement/run', {
    onFinish: () => (showConfirm.value = false)
  })
}

// -------------------------
// Helpers
// -------------------------
const allSelected = computed(() =>
  selectableIds.value.length > 0 &&
  selectableIds.value.every(id => selected.value.includes(id))
)
</script>

<template>
<AppLayout :breadcrumbs="[
  { title: 'Schedules', href: route('schedule.index') },
  { title: 'Loan Disbursements' },
]">

<Head title="Loan Disbursements" />

<div class="p-6 space-y-6">

  <!-- HEADER -->
  <div class="flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-bold">Loan Disbursement Schedule</h1>
      <p class="text-gray-500 text-sm">Approved loans ready for payout</p>
    </div>

    <div class="text-sm bg-white px-4 py-2 rounded-xl shadow">
      {{ new Date().getFullYear() }}
    </div>
  </div>

  <!-- SUMMARY -->
  <div class="grid md:grid-cols-4 gap-4">

    <div class="card">
      Loans<br>
      <strong>{{ summary.total_loans }}</strong>
    </div>

    <div class="card">
      Approved<br>
      <strong>KES {{ summary.total_approved.toLocaleString() }}</strong>
    </div>

    <div class="card">
      Net Disbursement<br>
      <strong>KES {{ summary.total_net.toLocaleString() }}</strong>
    </div>

    <div class="card">
      Fees<br>
      <strong>KES {{ summary.total_fees.toLocaleString() }}</strong>
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
              :checked="allSelected"
              @change="toggleAll"
            />
          </th>

          <th class="text-left">Loan</th>
          <th class="text-left">Member</th>
          <th class="text-left">Approved</th>
          <th class="text-left">Fees</th>
          <th class="text-left">Net</th>
        </tr>
      </thead>

      <tbody>

        <tr v-for="loan in loans" :key="loan.id" class="border-t hover:bg-gray-50">

          <td class="p-3">
            <input
              type="checkbox"
              :checked="selected.includes(loan.id)"
              @change="toggleOne(loan.id)"
            />
          </td>

          <td>
            <div class="font-medium">{{ loan.loan_number }}</div>
            <div class="text-xs text-gray-500">{{ loan.loan_product }}</div>
          </td>

          <td>
            <div class="font-medium">{{ loan.member_name }}</div>
            <div class="text-xs text-gray-500">{{ loan.membership_id }}</div>
          </td>

          <td>
            KES {{ loan.approved_amount.toLocaleString() }}
          </td>

          <td>
            <div class="text-xs text-red-500">
              P: {{ loan.processing_fee.toLocaleString() }}
            </div>
            <div class="text-xs text-red-500">
              I: {{ loan.insurance_fee.toLocaleString() }}
            </div>
          </td>

          <td class="font-semibold text-green-600">
            KES {{ loan.net_disbursement.toLocaleString() }}
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
      :disabled="selected.length === 0"
      @click="prepareRun"
      class="btn-primary"
    >
      Run Disbursement
    </button>

  </div>

  <!-- CONFIRM MODAL -->
  <div v-if="showConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center">

    <div class="bg-white rounded-xl p-6 w-full max-w-md space-y-4">

      <h2 class="text-lg font-bold">Confirm Loan Disbursement</h2>

      <p class="text-sm text-gray-600">
        You are about to disburse <strong>{{ selected.length }}</strong> loans.
      </p>

      <div class="flex justify-end gap-2">

        <button @click="showConfirm = false" class="px-4 py-2">
          Cancel
        </button>

        <button
          @click="runSchedule"
          class="bg-green-600 text-white px-4 py-2 rounded-lg"
        >
          Confirm & Disburse
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
  background: #16a34a;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
}

.btn-primary:hover {
  background: #15803d;
}

.btn-primary:disabled {
  opacity: 0.5;
}
</style>