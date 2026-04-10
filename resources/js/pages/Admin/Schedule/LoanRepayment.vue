<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const props = defineProps<{
  rows: any[]
  summary: any
  month: string
  alreadyRun: boolean
}>()

// ---------------------------
// Selection State
// ---------------------------
const selected = ref<number[]>(
  props.rows
    .filter(r => r.status !== 'paid') // only unpaid/partial
    .map(r => r.repayment_id)
)

const selectableIds = computed(() =>
  props.rows
    .filter(r => r.status !== 'paid')
    .map(r => r.repayment_id)
)

// ---------------------------
// Watch props refresh safety
// ---------------------------
watch(
  () => props.rows,
  () => {
    selected.value = props.rows
      .filter(r => r.status !== 'paid')
      .map(r => r.repayment_id)
  },
  { immediate: true }
)

// ---------------------------
// Toggle
// ---------------------------
const toggleAll = () => {
  if (selected.value.length === selectableIds.value.length) {
    selected.value = []
  } else {
    selected.value = [...selectableIds.value]
  }
}

const toggleOne = (id: number) => {
  if (props.alreadyRun) return

  if (selected.value.includes(id)) {
    selected.value = selected.value.filter(i => i !== id)
  } else {
    selected.value.push(id)
  }
}

// ---------------------------
// Form
// ---------------------------
const form = useForm({
  month: props.month,
  entries: [] as any[]
})

// ---------------------------
// Modal
// ---------------------------
const showConfirm = ref(false)

const prepareRun = () => {
  form.entries = props.rows
    .filter(r => selected.value.includes(r.repayment_id))
    .map(r => ({
      repayment_id: r.repayment_id,
      loan_id: r.loan_id,
      member_id: r.member_id,
      deduct_amount: r.deduct_amount
    }))

  showConfirm.value = true
}

const runSchedule = () => {
  form.post('/schedule/loan-repayment/run', {
    onFinish: () => (showConfirm.value = false)
  })
}

// ---------------------------
// Helpers
// ---------------------------
const allSelected = computed(() =>
  selectableIds.value.length > 0 &&
  selectableIds.value.every(id => selected.value.includes(id))
)
</script>

<template>
<AppLayout :breadcrumbs="[
  { title: 'Schedules', href: route('schedule.index') },
  { title: 'Loan Repayments' },
]">

<Head title="Loan Repayments" />

<div class="p-6 space-y-6">

  <!-- HEADER -->
  <div class="flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-bold">Loan Repayment Schedule</h1>
      <p class="text-gray-500 text-sm">Automated loan deductions</p>
    </div>

    <div class="text-sm bg-white px-4 py-2 rounded-xl shadow">
      {{ month }}
    </div>
  </div>

  <!-- FLASH -->
  <div v-if="$page.props.flash?.success" class="bg-green-100 text-green-700 p-3 rounded-lg">
    {{ $page.props.flash.success }}
  </div>

  <div v-if="$page.props.flash?.error" class="bg-red-100 text-red-700 p-3 rounded-lg">
    {{ $page.props.flash.error }}
  </div>

  <!-- SUMMARY -->
  <div class="grid md:grid-cols-4 gap-4">
    <div class="card">Total<br><strong>{{ summary.total_repayments }}</strong></div>
    <div class="card">Expected<br><strong>KES {{ summary.total_expected.toLocaleString() }}</strong></div>
    <div class="card">Deductable<br><strong>KES {{ summary.total_deductable.toLocaleString() }}</strong></div>
    <div class="card">Outstanding<br><strong>KES {{ summary.total_outstanding.toLocaleString() }}</strong></div>
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
          <th class="text-left">Member</th>
          <th class="text-left">Loan</th>
          <th class="text-left">Outstanding</th>
          <th class="text-left">Deduct</th>
          <th>Status</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="row in rows" :key="row.repayment_id" class="border-t hover:bg-gray-50">

          <td class="p-3">
            <input
              type="checkbox"
              :checked="selected.includes(row.repayment_id)"
              :disabled="row.status === 'paid' || alreadyRun"
              @change="toggleOne(row.repayment_id)"
            />
          </td>

          <td>
            <div class="font-medium">{{ row.member_name }}</div>
            <div class="text-xs text-gray-500">{{ row.membership_id }}</div>
          </td>

          <td>
            <div class="font-medium">{{ row.loan_number }}</div>
            <div class="text-xs text-gray-500">{{ row.loan_product }}</div>
          </td>

          <td>
            KES {{ row.outstanding_amount.toLocaleString() }}
          </td>

          <td class="font-semibold text-blue-600">
            KES {{ row.deduct_amount.toLocaleString() }}
          </td>

          <td>
            <span class="px-2 py-1 rounded-full text-xs"
              :class="row.status === 'paid'
                ? 'bg-green-100 text-green-700'
                : row.status === 'partial'
                ? 'bg-yellow-100 text-yellow-700'
                : 'bg-red-100 text-red-700'"
            >
              {{ row.status }}
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
      :disabled="form.processing || alreadyRun || selected.length === 0"
      @click="prepareRun"
      class="btn-primary"
    >
      Run Loan Repayment
    </button>

  </div>

  <!-- CONFIRM MODAL -->
  <div v-if="showConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-md space-y-4">

      <h2 class="text-lg font-bold">Confirm Loan Repayments</h2>

      <p class="text-sm text-gray-600">
        You are about to process <strong>{{ selected.length }}</strong> repayments.
      </p>

      <div class="flex justify-end gap-2">
        <button @click="showConfirm = false" class="px-4 py-2">
          Cancel
        </button>

        <button
          @click="runSchedule"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg"
        >
          Confirm & Run
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
  background: #2563eb;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
}
.btn-primary:hover {
  background: #1d4ed8;
}
.btn-primary:disabled {
  opacity: 0.5;
}
</style>