<script setup>
import { Head, router } from "@inertiajs/vue3"
import { ref } from "vue"

const props = defineProps({
  rows: Array,
  summary: Object,
  month: String,
  alreadyRun: Boolean
})

const loading = ref(false)

const runSchedule = () => {
  if (!confirm("Run monthly deposits?")) return

  loading.value = true

  router.post("/schedule/monthly-deposit/run", {
    month: props.month,
    entries: props.rows.filter(r => !r.already_deposited_this_month)
  })
}
</script>

<template>
  <Head title="Monthly Deposits" />

  <div class="p-6 space-y-6 bg-slate-50 min-h-screen">

    <!-- HEADER -->
    <div class="flex justify-between items-center">
      <h1 class="text-xl font-bold text-gray-800">Monthly Deposits</h1>

      <button
        @click="runSchedule"
        :disabled="alreadyRun || loading"
        class="px-5 py-2 rounded-xl text-white font-medium shadow transition"
        :class="alreadyRun 
          ? 'bg-gray-400 cursor-not-allowed' 
          : 'bg-blue-600 hover:bg-blue-700'"
      >
        Run Schedule
      </button>
    </div>

    <!-- ALERT -->
    <div v-if="alreadyRun"
      class="bg-yellow-100 text-yellow-800 p-4 rounded-xl border border-yellow-300">
      ⚠ This schedule has already been executed for this month
    </div>

    <!-- SUMMARY -->
    <div class="grid md:grid-cols-4 gap-4">

      <div class="bg-white p-4 rounded-xl shadow border">
        <p class="text-gray-500 text-sm">Eligible</p>
        <h2 class="text-xl font-bold">{{ summary.total_eligible }}</h2>
      </div>

      <div class="bg-white p-4 rounded-xl shadow border">
        <p class="text-gray-500 text-sm">Pending</p>
        <h2 class="text-xl font-bold text-yellow-600">{{ summary.pending }}</h2>
      </div>

      <div class="bg-white p-4 rounded-xl shadow border">
        <p class="text-gray-500 text-sm">Completed</p>
        <h2 class="text-xl font-bold text-green-600">{{ summary.already_done }}</h2>
      </div>

      <div class="bg-white p-4 rounded-xl shadow border">
        <p class="text-gray-500 text-sm">Total Amount</p>
        <h2 class="text-xl font-bold text-blue-600">
          KES {{ summary.total_amount }}
        </h2>
      </div>

    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-xl shadow border overflow-hidden">
      <div class="overflow-x-auto">

        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600">
            <tr>
              <th class="p-3 text-left">Member</th>
              <th class="p-3 text-left">Account</th>
              <th class="p-3 text-left">Type</th>
              <th class="p-3 text-left">Amount</th>
              <th class="p-3 text-left">Status</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in rows" :key="row.commitment_id"
              class="border-b hover:bg-gray-50 transition">

              <td class="p-3">{{ row.member_name }}</td>
              <td class="p-3">{{ row.account_number }}</td>
              <td class="p-3 capitalize">{{ row.account_type }}</td>

              <td class="p-3 font-medium">
                KES {{ row.amount }}
              </td>

              <td class="p-3">
                <span v-if="row.already_deposited_this_month"
                  class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                  Completed
                </span>

                <span v-else
                  class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">
                  Pending
                </span>
              </td>

            </tr>
          </tbody>
        </table>

      </div>
    </div>

  </div>
</template>