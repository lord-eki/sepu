<script setup lang="ts">
import AppLayout from "@/Layouts/AppLayout.vue"
import { Head, Link } from "@inertiajs/vue3"
import { computed } from "vue"
import { ArrowLeft } from "lucide-vue-next"

// ---------------- PROPS ----------------
const props = defineProps({
  loan: Object,
  repayments: Array,
})

// ---------------- COMPUTED ----------------
const totals = computed(() => {
  const principal = props.repayments.reduce((sum, r) => sum + Number(r.principal_amount), 0)
  const interest = props.repayments.reduce((sum, r) => sum + Number(r.interest_amount), 0)
  const total = props.repayments.reduce((sum, r) => sum + Number(r.payment_amount), 0)

  return {
    principal,
    interest,
    total,
  }
})
</script>

<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Loans', href: route('loans.index') },
      { title: 'Repayment Schedule' }
    ]"
  >
    <Head title="Loan Repayment Schedule" />

    <div class="p-4 md:p-6 space-y-6">

      <!-- HEADER -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">
            Repayment Schedule
          </h1>
          <p class="text-sm text-gray-500">
            Loan #: {{ loan.loan_number }}
          </p>
        </div>

        <Link
          :href="route('loans.show', loan.id)"
          class="flex items-center gap-2 bg-slate-800 text-white px-4 py-2 rounded-xl hover:bg-slate-700 transition"
        >
          <ArrowLeft class="w-4 h-4" />
          Back
        </Link>
      </div>

      <!-- LOAN SUMMARY -->
      <div class="grid md:grid-cols-4 gap-4">

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-blue-900">
          <p class="text-sm text-gray-500">Approved Amount</p>
          <h2 class="text-lg font-semibold">
            KES {{ Number(loan.approved_amount).toLocaleString() }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-orange-500">
          <p class="text-sm text-gray-500">Total Repayable</p>
          <h2 class="text-lg font-semibold">
            KES {{ Number(loan.total_repayable).toLocaleString() }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-green-600">
          <p class="text-sm text-gray-500">Monthly Payment</p>
          <h2 class="text-lg font-semibold">
            KES {{ Number(loan.monthly_repayment).toLocaleString() }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-purple-600">
          <p class="text-sm text-gray-500">Term</p>
          <h2 class="text-lg font-semibold">
            {{ loan.term_months }} Months
          </h2>
        </div>

      </div>

      <!-- TABLE -->
      <div class="bg-white shadow rounded-2xl overflow-hidden">

        <div class="px-4 py-3 border-b bg-gradient-to-r from-blue-900 to-orange-500 text-white font-semibold">
          Repayment Breakdown
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">

            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
              <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Opening Balance</th>
                <th class="px-4 py-3">Principal</th>
                <th class="px-4 py-3">Interest</th>
                <th class="px-4 py-3">Installment</th>
                <th class="px-4 py-3">Closing Balance</th>
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
                  {{ row.payment_date }}
                </td>

                <td class="px-4 py-3">
                  {{ Number(row.opening_balance).toLocaleString() }}
                </td>

                <td class="px-4 py-3 text-blue-700 font-medium">
                  {{ Number(row.principal_amount).toLocaleString() }}
                </td>

                <td class="px-4 py-3 text-orange-600 font-medium">
                  {{ Number(row.interest_amount).toLocaleString() }}
                </td>

                <td class="px-4 py-3 font-semibold">
                  {{ Number(row.payment_amount).toLocaleString() }}
                </td>

                <td class="px-4 py-3">
                  {{ Number(row.closing_balance).toLocaleString() }}
                </td>
              </tr>

              <tr v-if="repayments.length === 0">
                <td colspan="7" class="text-center py-6 text-gray-500">
                  No repayment schedule available
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
            KES {{ totals.principal.toLocaleString() }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-orange-500">
          <p class="text-sm text-gray-500">Total Interest</p>
          <h2 class="text-lg font-semibold">
            KES {{ totals.interest.toLocaleString() }}
          </h2>
        </div>

        <div class="bg-white shadow rounded-2xl p-4 border-l-4 border-green-600">
          <p class="text-sm text-gray-500">Total Paid</p>
          <h2 class="text-lg font-semibold">
            KES {{ totals.total.toLocaleString() }}
          </h2>
        </div>

      </div>

    </div>
  </AppLayout>
</template>