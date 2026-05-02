<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue"
import { Head, Link } from "@inertiajs/vue3"
import { computed } from "vue"
import { ArrowLeft, Printer, Download } from "lucide-vue-next"

// ---------------- PROPS ----------------
const props = defineProps({
  loan: Object,
  repayments: Array,
  totals: Object,
  message: String,
})

// ---------------- HELPERS ----------------
const formatMoney = (val: number) => {
  return Number(val || 0).toLocaleString()
}

const statusClass = (status: string) => {
  switch (status) {
    case "paid":
      return "bg-green-100 text-green-700"
    case "pending":
      return "bg-yellow-100 text-yellow-700"
    case "overdue":
      return "bg-red-100 text-red-700"
    default:
      return "bg-gray-100 text-gray-600"
  }
}

const formatDate = (date: string) => {
  if (!date) return ""

  return new Date(date).toLocaleDateString("en-GB", {
    year: "numeric",
    month: "short",
    day: "2-digit",
  })
}

// ---------------- COMPUTED ----------------
const hasData = computed(() => props.repayments && props.repayments.length > 0)
</script>

<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Loans', href: route('loans.index') },
      { title: 'Schedule' }
    ]"
  >
    <Head title="Loan Schedule" />

    <div class="p-4 md:p-6 space-y-6">

      <!-- HEADER -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
          <h1 class="text-2xl font-bold text-slate-800">
            Loan Schedule
          </h1>
          <p class="text-sm text-gray-500">
            Loan #: {{ loan.loan_number }}
          </p>
        </div>

        <div class="flex gap-2">

          <button
            onclick="window.print()"
            class="flex items-center gap-2 bg-white border px-4 py-2 rounded-xl shadow hover:bg-gray-50"
          >
            <Printer class="w-4 h-4" />
            Print
          </button>

          <Link
            :href="route('loans.show', loan.id)"
            class="flex items-center gap-2 bg-slate-800 text-white px-4 py-2 rounded-xl hover:bg-slate-700 transition"
          >
            <ArrowLeft class="w-4 h-4" />
            Back
          </Link>

        </div>
      </div>

      <!-- LOAN SUMMARY -->
      <div class="grid md:grid-cols-4 gap-4">

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-blue-900">
          <p class="text-sm text-gray-500">Principal</p>
          <h2 class="text-lg font-semibold">
            KES {{ formatMoney(loan.approved_amount) }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-orange-500">
          <p class="text-sm text-gray-500">Total Repayable</p>
          <h2 class="text-lg font-semibold">
            KES {{ formatMoney(loan.total_repayable) }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-green-600">
          <p class="text-sm text-gray-500">Monthly Installment</p>
          <h2 class="text-lg font-semibold">
            KES {{ formatMoney(loan.monthly_repayment) }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-purple-600">
          <p class="text-sm text-gray-500">Term</p>
          <h2 class="text-lg font-semibold">
            {{ loan.term_months }} Months
          </h2>
        </div>

      </div>

      <!-- MESSAGE -->
      <div
        v-if="message"
        class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl"
      >
        {{ message }}
      </div>

      <!-- TABLE -->
      <div class="bg-white shadow rounded-2xl overflow-hidden">

        <div class="px-4 py-3 border-b bg-gradient-to-r from-blue-900 to-orange-500 text-white font-semibold">
          Full Loan Schedule
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">

            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
              <tr>
                <th class="px-4 py-3 text-left">#</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-right">Opening</th>
                <th class="px-4 py-3 text-right">Principal</th>
                <th class="px-4 py-3 text-right">Interest</th>
                <th class="px-4 py-3 text-right">Installment</th>
                <th class="px-4 py-3 text-right">Closing</th>
                <th class="px-4 py-3 text-center">Status</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="row in repayments"
                :key="row.payment_number"
                class="border-b hover:bg-gray-50 transition"
              >
                <td class="px-4 py-3 font-medium">
                  {{ row.payment_number }}
                </td>

                <td class="px-4 py-3">
                  {{ formatDate(row.payment_date) }}
                </td>

                <td class="px-4 py-3 text-right">
                  {{ formatMoney(row.opening_balance) }}
                </td>

                <td class="px-4 py-3 text-right text-blue-700 font-medium">
                  {{ formatMoney(row.principal_amount) }}
                </td>

                <td class="px-4 py-3 text-right text-orange-600 font-medium">
                  {{ formatMoney(row.interest_amount) }}
                </td>

                <td class="px-4 py-3 text-right font-semibold">
                  {{ formatMoney(row.payment_amount) }}
                </td>

                <td class="px-4 py-3 text-right">
                  {{ formatMoney(row.closing_balance) }}
                </td>

                <td class="px-4 py-3 text-center">
                  <span
                    class="px-2 py-1 rounded-full text-xs font-semibold"
                    :class="statusClass(row.status)"
                  >
                    {{ row.status ?? 'pending' }}
                  </span>
                </td>
              </tr>

              <tr v-if="!hasData">
                <td colspan="8" class="text-center py-6 text-gray-500">
                  No schedule data available
                </td>
              </tr>
            </tbody>

          </table>
        </div>

      </div>

      <!-- TOTALS -->
      <div class="grid md:grid-cols-3 gap-4">

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-blue-900">
          <p class="text-sm text-gray-500">Total Principal</p>
          <h2 class="text-lg font-semibold">
            KES {{ formatMoney(totals?.principal) }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-orange-500">
          <p class="text-sm text-gray-500">Total Interest</p>
          <h2 class="text-lg font-semibold">
            KES {{ formatMoney(totals?.interest) }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-green-600">
          <p class="text-sm text-gray-500">Total Payment</p>
          <h2 class="text-lg font-semibold">
            KES {{ formatMoney(totals?.total) }}
          </h2>
        </div>

      </div>

    </div>
  </AppLayout>
</template>