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

// Selection state
const selected = ref<number[]>(
  props.rows
    .filter(r => !r.already_deposited_this_month)
    .map(r => r.member_id)
)

const allSelectableIds = computed(() =>
  props.rows
    .filter(r => !r.already_deposited_this_month)
    .map(r => r.member_id)
)

const toggleAll = () => {
  if (selected.value.length === allSelectableIds.value.length) {
    selected.value = []
  } else {
    selected.value = [...allSelectableIds.value]
  }
}

const toggleOne = (id: number) => {
  if (selected.value.includes(id)) {
    selected.value = selected.value.filter(i => i !== id)
  } else {
    selected.value.push(id)
  }
}

watch(
  () => props.rows,
  () => {
    selected.value = props.rows
      .filter(r => !r.already_deposited_this_month)
      .map(r => r.member_id)
  },
  { immediate: true }
)

// Form
const form = useForm({
  month: props.month,
  entries: [] as any[]
})

// Modal
const showConfirm = ref(false)

const prepareRun = () => {
  form.entries = props.rows
    .filter(r => selected.value.includes(r.member_id))
    .map(r => ({
      member_id: r.member_id,
      account_id: r.account_id,
      amount: r.amount
    }))

  showConfirm.value = true
}

const runSchedule = () => {
  form.post('/schedule/monthly-deposit/run', {
    onFinish: () => (showConfirm.value = false)
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: 'Schedules', href: route('schedule.index') },
    { title: 'Monthly Deposit' },
  ]">

    <Head title="Monthly Deposits" />

    <div class="p-6 space-y-6">
      <!-- HEADER -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Monthly Deposits</h1>
          <p class="text-gray-500 text-sm">Automated member contributions</p>
        </div>

        <div class="text-sm bg-white px-4 py-2 rounded-xl shadow">
          {{ month }}
        </div>
      </div>

      <!-- SUMMARY CARDS -->
      <div class="grid md:grid-cols-4 gap-4">
        <div class="card">Eligible<br><strong>{{ summary.total_eligible }}</strong></div>
        <div class="card">Pending<br><strong>{{ summary.pending }}</strong></div>
        <div class="card">Completed<br><strong>{{ summary.already_done }}</strong></div>
        <div class="card">Amount<br><strong>KES {{ summary.total_amount.toLocaleString() }}</strong></div>
      </div>

      <!-- TABLE -->
      <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="p-3">
                <input type="checkbox" @change="toggleAll" :checked="selected.length === allSelectableIds.length" />
              </th>
              <th class="text-left">Member</th>
              <th class="text-left">Account</th>
              <th class="text-left">Balance</th>
              <th class="text-left">Amount</th>
              <th>Status</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in rows" :key="row.member_id" class="border-t hover:bg-gray-50">
              <td class="p-3">
                <input type="checkbox" :disabled="row.already_deposited_this_month"
                  :checked="selected.includes(row.member_id)" @change="toggleOne(row.member_id)" />
              </td>

              <td class="font-medium">{{ row.member_name }}</td>
              <td>{{ row.account_number }}</td>
              <td>KES {{ row.account_balance.toLocaleString() }}</td>
              <td class="font-semibold">KES {{ row.amount.toLocaleString() }}</td>

              <td>
                <span class="px-2 py-1 rounded-full text-xs" :class="row.already_deposited_this_month
    ? 'bg-green-100 text-green-700'
    : 'bg-yellow-100 text-yellow-700'">
                  {{ row.already_deposited_this_month ? 'Completed' : 'Pending' }}
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

        <button :disabled="alreadyRun || selected.length === 0" @click="prepareRun" class="btn-primary">
          Run Schedule
        </button>
      </div>

      <!-- CONFIRM MODAL -->
      <div v-if="showConfirm" class="fixed inset-0 bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 w-full max-w-md space-y-4">
          <h2 class="text-lg font-bold">Confirm Schedule</h2>

          <p class="text-sm text-gray-600">
            You are about to process <strong>{{ selected.length }}</strong> deposits.
          </p>

          <div class="flex justify-end gap-2">
            <button @click="showConfirm = false" class="px-4 py-2">Cancel</button>

            <button @click="runSchedule" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
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
  background-color: #ffffff;
  padding: 1rem;
  /* p-4 */
  border-radius: 0.75rem;
  /* rounded-xl */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  /* shadow */
  text-align: center;
}

.btn-primary {
  background-color: #2563eb;
  /* bg-blue-600 */
  color: #ffffff;
  padding: 0.5rem 1rem;
  /* py-2 px-4 */
  border-radius: 0.5rem;
  /* rounded-lg */
  border: none;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.btn-primary:hover {
  background-color: #1d4ed8;
  /* bg-blue-700 */
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>