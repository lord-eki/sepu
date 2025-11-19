<template>
  <AppLayout :breadcrumbs="[{ title: 'Create Transaction', href: '/transactions' }]">
    <Head title="Create Transaction" />

    <div class="max-w-3xl mx-auto py-10 space-y-6">

      <!-- Page Title -->
      <h1 class="text-2xl font-semibold text-gray-800">New Transaction</h1>

      <!-- Form Card -->
      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl p-6 space-y-5">

        <!-- Account -->
        <div>
          <label class="font-medium text-gray-700">Account</label>
          <select v-model="form.account_id"
            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-primary">
            <option disabled value="">Select Account</option>
            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
              {{ acc.account_number }} — {{ acc.account_type }}
            </option>
          </select>
          <p v-if="form.errors.account_id" class="text-red-600 text-sm">
            {{ form.errors.account_id }}
          </p>
        </div>

        <!-- Transaction Type -->
        <div>
          <label class="font-medium text-gray-700">Transaction Type</label>
          <select v-model="form.transaction_type"
            class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-primary">
            <option value="deposit">Deposit</option>
            <option value="withdrawal">Withdrawal</option>
            <option value="transfer">Transfer</option>
          </select>
        </div>

        <!-- Amount -->
        <div>
          <label class="font-medium text-gray-700">Amount</label>
          <input type="number" v-model="form.amount"
            class="mt-1 w-full border rounded-lg px-3 py-2" />
          <p v-if="form.errors.amount" class="text-red-600 text-sm">{{ form.errors.amount }}</p>
        </div>

        <!-- Payment Method -->
        <div>
          <label class="font-medium text-gray-700">Payment Method</label>
          <select v-model="form.payment_method"
            class="mt-1 w-full border rounded-lg px-3 py-2">
            <option value="mpesa">M-Pesa</option>
            <option value="bank">Bank</option>
            <option value="cash">Cash</option>
          </select>
        </div>

        <!-- Destination Account (Transfers ONLY) -->
        <div v-if="form.transaction_type === 'transfer'">
          <label class="font-medium text-gray-700">Destination Account</label>
          <select v-model="form.destination_account_id"
            class="mt-1 w-full border rounded-lg px-3 py-2">
            <option disabled value="">Select Destination Account</option>
            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
              {{ acc.account_number }} — {{ acc.account_type }}
            </option>
          </select>
        </div>

        <!-- Description -->
        <div>
          <label class="font-medium text-gray-700">Description</label>
          <textarea v-model="form.description"
            rows="3" class="mt-1 w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        <!-- Submit -->
        <button :disabled="form.processing"
          class="w-full bg-primary text-white py-3 rounded-lg hover:bg-primary-dark disabled:opacity-50">
          {{ form.processing ? 'Submitting...' : 'Create Transaction' }}
        </button>

      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm, usePage } from "@inertiajs/vue3";

const props = defineProps<{
  accounts: any[];
}>();

const form = useForm({
  account_id: "",
  transaction_type: "deposit",
  amount: "",
  payment_method: "mpesa",
  description: "",
  destination_account_id: "",
});

const submit = () => {
  form.post("/transactions");
};
</script>
