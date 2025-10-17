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

    <div class="p-6 max-w-3xl lg:mx-[25%] space-y-8">
      <h1 class="text-xl sm:text-2xl font-bold text-blue-900">Loan Eligibility Check</h1>

      <!-- Form Card -->
      <Card class="border border-gray-200 shadow-sm">
        <CardHeader>
          <CardTitle>Select Loan Product and Amount</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Loan Product</label>
            <select
              v-model="form.loan_product_id"
              class="w-full border rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">-- Select Loan Product --</option>
              <option v-for="product in props.loanProducts" :key="product.id" :value="product.id">
                {{ product.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Requested Amount (KES)</label>
            <input
              v-model="form.requested_amount"
              type="number"
              min="1"
              class="w-full border rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter amount"
            />
          </div>

          <div class="flex justify-end">
            <Button
              :disabled="checking"
              @click="checkEligibility"
              class="bg-blue-800 text-white px-5 py-2 rounded-lg"
            >
              {{ checking ? "Checking..." : "Check Eligibility" }}
            </Button>
          </div>

          <!-- Errors -->
          <div v-if="errors.length" class="bg-red-50 border-l-4 border-red-500 p-3 rounded-md">
            <ul class="list-disc list-inside text-red-700 text-sm">
              <li v-for="err in errors" :key="err">{{ err }}</li>
            </ul>
          </div>
        </CardContent>
      </Card>

      <!-- Results -->
      <div v-if="result" class="space-y-4">
        <Card
          :class="result.eligible ? 'border-green-400 bg-green-50' : 'border-red-400 bg-red-50'"
        >
          <CardHeader>
            <CardTitle
              :class="result.eligible ? 'text-green-700' : 'text-red-700'"
            >
              {{ result.eligible ? "Eligible for Loan" : "Not Eligible" }}
            </CardTitle>
          </CardHeader>
          <CardContent class="text-sm text-gray-800 space-y-2">
            <p><strong>Maximum Loan Amount:</strong> KES {{ result.max_loan_amount.toLocaleString() }}</p>

            <div v-if="result.messages && result.messages.length">
              <p class="font-semibold mb-1">Reason(s):</p>
              <ul class="list-disc list-inside">
                <li v-for="msg in result.messages" :key="msg">{{ msg }}</li>
              </ul>
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="flex justify-start">
        <Link :href="route('loans.index', props.member.id)">
          <Button variant="outline">Back to Loans</Button>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
button:hover {
    cursor: pointer;
}
</style>
