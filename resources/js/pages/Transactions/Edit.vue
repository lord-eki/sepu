<template>
  <AppLayout :breadcrumbs="[{ title: `Edit Transaction #${transaction.id}` }]">
    <Head title="Edit Transaction" />

    <div class="max-w-3xl mx-auto py-10 space-y-6">

      <!-- Header -->
      <h1 class="text-2xl font-semibold text-gray-800">
        Edit Transaction #{{ transaction.id }}
      </h1>

      <div v-if="transaction.status !== 'pending'"
        class="bg-yellow-100 text-yellow-800 p-4 rounded-lg">
        Only pending transactions can be edited.
      </div>

      <!-- Edit Form -->
      <form v-if="transaction.status === 'pending'"
        @submit.prevent="submit"
        class="bg-white shadow-sm rounded-xl p-6 space-y-5">

        <!-- Transaction Type -->
        <div>
          <label class="font-medium text-gray-700">Transaction Type</label>
          <select v-model="form.transaction_type"
            class="mt-1 w-full border rounded-lg px-3 py-2">
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

        <!-- Description -->
        <div>
          <label class="font-medium text-gray-700">Description</label>
          <textarea rows="3" v-model="form.description"
            class="mt-1 w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        <!-- Submit -->
        <button :disabled="form.processing"
          class="w-full bg-primary text-white py-3 rounded-lg hover:bg-primary-dark disabled:opacity-50">
          {{ form.processing ? "Updating..." : "Update Transaction" }}
        </button>
      </form>

    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";

const props = defineProps<{
  transaction: any;
}>();

const form = useForm({
  transaction_type: props.transaction.transaction_type,
  amount: props.transaction.amount,
  payment_method: props.transaction.payment_method,
  description: props.transaction.description,
});

const submit = () => {
  form.put(`/transactions/${props.transaction.id}`);
};
</script>
