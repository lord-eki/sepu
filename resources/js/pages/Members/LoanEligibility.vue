<script setup lang="ts">
import { ref } from "vue"
import { Head, Link } from "@inertiajs/vue3"
import AppLayout from "@/layouts/AppLayout.vue"
import axios from "axios"
import { Button } from "@/components/ui/button"
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card"

const props = defineProps<{
  member: any
  loanProducts: any[]
}>()

const form = ref({
  loan_product_id: "",
  requested_amount: "",
})

const checking = ref(false)
const result = ref<any | null>(null)
const errors = ref<string[]>([])

const checkEligibility = async () => {
  errors.value = []
  result.value = null

  if (!form.value.loan_product_id || !form.value.requested_amount) {
    errors.value.push("Please select a loan product and enter amount.")
    return
  }

  checking.value = true
  try {
    const response = await axios.post(
      route("members.loans.check-eligibility", props.member.id),
      {
        member_id: props.member.id,
        loan_product_id: form.value.loan_product_id,
        requested_amount: form.value.requested_amount,
      }
    )

    result.value = response.data.data
  } catch (error: any) {
    console.error(error)
    errors.value.push("Unable to check eligibility at this time.")
  } finally {
    checking.value = false
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: 'Loans', href: route('loans.index') },
    { title: 'Loan Eligibility' }
  ]">
    <Head title="Loan Eligibility Check" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-orange-50 py-10 px-4">
      <div class="max-w-3xl mx-auto space-y-8">

        <!-- Header -->
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-extrabold text-[rgb(7,40,75)]">
              Loan Eligibility Check
            </h1>
            <p class="text-sm text-gray-500 mt-1">
              Verify if this member qualifies for a loan product
            </p>
          </div>

          <Link :href="route('loans.index')"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-xl shadow-md text-sm transition">
            ← Back
          </Link>
        </div>

        <!-- Member Card -->
        <div class="bg-white border border-blue-100 rounded-2xl shadow-lg overflow-hidden">
          <div class="bg-blue-900/90 px-6 py-3">
            <h3 class="text-white font-semibold">Member Information</h3>
          </div>

          <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700">
            <div>
              <p class="text-gray-500">Full Name</p>
              <p class="font-semibold text-gray-900">
                {{ props.member.first_name }} {{ props.member.last_name }}
              </p>
            </div>

            <div>
              <p class="text-gray-500">Member ID</p>
              <p class="font-semibold text-gray-900">
                {{ props.member.member_number ?? props.member.membership_id }}
              </p>
            </div>

            <div v-if="props.member.phone">
              <p class="text-gray-500">Phone</p>
              <p class="font-semibold text-gray-900">
                {{ props.member.phone }}
              </p>
            </div>
          </div>
        </div>

        <!-- Form -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-xl p-6 space-y-6">

          <!-- Loan Product -->
          <div>
            <label class="text-sm font-medium text-gray-700">Loan Product</label>
            <select
              v-model="form.loan_product_id"
              class="mt-2 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none transition"
            >
              <option value="">-- Select Loan Product --</option>
              <option v-for="product in props.loanProducts" :key="product.id" :value="product.id">
                {{ product.name }}
              </option>
            </select>
          </div>

          <!-- Amount -->
          <div>
            <label class="text-sm font-medium text-gray-700">Requested Amount (KES)</label>
            <input
              v-model="form.requested_amount"
              type="number"
              min="1"
              placeholder="Enter amount"
              class="mt-2 w-full rounded-xl border border-gray-200 p-3 focus:ring-2 focus:ring-orange-400 focus:outline-none transition"
            />
          </div>

          <!-- Button -->
          <div class="flex justify-end">
            <button
              :disabled="checking"
              @click="checkEligibility"
              class="flex items-center gap-2 bg-blue-900 hover:bg-blue-800 text-white px-6 py-3 rounded-xl shadow-lg transition disabled:bg-gray-400"
            >
              <svg v-if="checking" class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="white" stroke-width="4" fill="none"/>
              </svg>
              {{ checking ? "Checking..." : "Check Eligibility" }}
            </button>
          </div>

          <!-- Errors -->
          <div v-if="errors.length"
            class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
            <ul class="list-disc pl-5">
              <li v-for="err in errors" :key="err">{{ err }}</li>
            </ul>
          </div>
        </div>

        <!-- Results -->
        <div v-if="result" class="space-y-4">

          <div
            class="rounded-2xl shadow-xl border p-6 transition"
            :class="result.eligible
              ? 'bg-gradient-to-r from-green-50 to-green-100 border-green-300'
              : 'bg-gradient-to-r from-red-50 to-red-100 border-red-300'"
          >
            <div class="flex items-center justify-between mb-3">
              <h3
                class="text-lg font-bold"
                :class="result.eligible ? 'text-green-700' : 'text-red-700'"
              >
                {{ result.eligible ? "Eligible for Loan" : "Not Eligible" }}
              </h3>

              <span
                class="text-xs px-3 py-1 rounded-full font-medium"
                :class="result.eligible
                  ? 'bg-green-200 text-green-800'
                  : 'bg-red-200 text-red-800'"
              >
                {{ result.eligible ? "Approved" : "Declined" }}
              </span>
            </div>

            <div class="text-sm text-gray-800 space-y-2">
              <p>
                <strong>Maximum Loan:</strong>
                KES {{ Number(result.max_loan_amount).toLocaleString() }}
              </p>

              <div v-if="result.messages?.length">
                <p class="font-semibold mt-2">Reason(s):</p>
                <ul class="list-disc pl-5 mt-1">
                  <li v-for="msg in result.messages" :key="msg">{{ msg }}</li>
                </ul>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
button:hover {
    cursor: pointer;
}
</style>
