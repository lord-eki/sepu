<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/transactions' }, { title: 'New Transaction' }]">

    <Head title="New Transaction" />

    <!-- Global message -->
    <div v-if="message.visible"
      :class="message.type === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
      class="fixed top-5 left-1/2 transform -translate-x-1/2 py-3 px-6 rounded shadow-lg z-50 flex items-center justify-between gap-3 min-w-[250px] max-w-sm transition-opacity duration-300">
      <span>{{ message.text }}</span>
      <button @click="message.visible = false" class="text-gray-500 hover:text-gray-800 font-bold">&times;</button>
    </div>

    <div class="max-w-3xl mx-auto p-4 bg-[#F4F6F8] min-h-screen space-y-6">

      <!-- Page header -->
      <h1 class="text-2xl font-semibold text-[#0A2342]">Create New Transaction</h1>

      <!-- Form -->
      <form @submit.prevent="submitTransaction"
        class="bg-white p-6 rounded-2xl shadow space-y-4 border border-gray-100">

        <!-- Member -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Member</label>
          <select v-model="form.member_id" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]">
            <option value="">Select member</option>
            <option v-for="m in members" :key="m.id" :value="m.id">
              {{ m.first_name }} {{ m.last_name }} ({{ m.membership_id }})
            </option>
          </select>
          <p v-if="errors.member_id" class="text-rose-500 text-sm mt-1">{{ errors.member_id[0] }}</p>
        </div>

        <!-- Account -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Account</label>
          <select v-model="form.account_id"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]">
            <option value="">Select account</option>
            <option v-for="a in accounts" :key="a.id" :value="a.id">
              {{ a.account_number }} - {{ a.member ? `${a.member.first_name} ${a.member.last_name}` : 'No Member' }}
            </option>
          </select>
          <p v-if="errors.account_id" class="text-rose-500 text-sm mt-1">{{ errors.account_id[0] }}</p>
        </div>

        <!-- Transaction Type -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Transaction Type</label>
          <select v-model="form.transaction_type"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]">
            <option value="">Select type</option>
            <option v-for="(label, key) in transactionTypes" :key="key" :value="key">{{ label }}</option>
          </select>
          <p v-if="errors.transaction_type" class="text-rose-500 text-sm mt-1">{{ errors.transaction_type[0] }}</p>
        </div>

        <!-- Amount -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Amount</label>
          <input type="number" min="0.01" step="0.01" v-model="form.amount"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]" />
          <p v-if="errors.amount" class="text-rose-500 text-sm mt-1">{{ errors.amount[0] }}</p>
        </div>

        <!-- Payment Method -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Payment Method</label>
          <select v-model="form.payment_method"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]">
            <option value="">Select method</option>
            <option v-for="(label, key) in paymentMethods" :key="key" :value="key">{{ label }}</option>
          </select>
          <p v-if="errors.payment_method" class="text-rose-500 text-sm mt-1">{{ errors.payment_method[0] }}</p>
        </div>

        <!-- Payment Reference -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Payment Reference</label>
          <input type="text" v-model="form.payment_reference"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]" />
          <p v-if="errors.payment_reference" class="text-rose-500 text-sm mt-1">{{ errors.payment_reference[0] }}</p>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Description</label>
          <textarea v-model="form.description" rows="3"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]"></textarea>
          <p v-if="errors.description" class="text-rose-500 text-sm mt-1">{{ errors.description[0] }}</p>
        </div>

        <!-- Destination Account for Transfers -->
        <div v-if="form.transaction_type === 'transfer'">
          <label class="block text-sm font-medium text-gray-700">Destination Account</label>
          <select v-model="form.destination_account_id"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]">
            <option value="">Select destination</option>
            <option v-for="a in accounts" :key="a.id" :value="a.id">
              {{ a.account_number }} - {{ a.member ? `${a.member.first_name} ${a.member.last_name}` : 'No Member' }}
            </option>
          </select>
          <p v-if="errors.destination_account_id" class="text-rose-500 text-sm mt-1">{{ errors.destination_account_id[0]
            }}</p>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-2 pt-2">
          <Link href="/transactions" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</Link>
          <button type="submit"
            class="px-4 py-2 bg-[#F97316] text-white rounded hover:bg-orange-500 flex items-center gap-2"
            :disabled="loading">
            <span v-if="loading" class="animate-spin">⏳</span>
            <span>{{ loading ? 'Submitting...' : 'Submit' }}</span>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
  accounts: any[];
  members: any[];
  transactionTypes: Record<string, string>;
  paymentMethods: Record<string, string>;
}>();

const accounts = props.accounts;
const members = props.members;
const transactionTypes = props.transactionTypes;
const paymentMethods = props.paymentMethods;

// Form state
const form = reactive({
  member_id: '',
  account_id: '',
  transaction_type: '',
  amount: '',
  description: '',
  payment_method: '',
  payment_reference: '',
  destination_account_id: '',
});

// UI state
const errors = reactive<any>({});
const loading = ref(false);
const message = reactive({ text: '', type: 'success', visible: false });

function showMessage(text: string, type: 'success' | 'error' = 'success') {
  message.text = text;
  message.type = type;
  message.visible = true;
  setTimeout(() => (message.visible = false), 4000);
}

// Submit form
async function submitTransaction() {
  errors.value = {};
  loading.value = true;

  try {
    await router.post('/transactions', form, {
      preserveState: true,
      onSuccess: (page) => {
        showMessage('Transaction created successfully!', 'success');
        router.visit('/transactions');
      },
      onError: (err) => {
        Object.assign(errors, err);
        showMessage('Please fix the errors', 'error');
      },
      onFinish: () => { loading.value = false; },
    });
  } catch (e) {
    showMessage('Transaction failed', 'error');
    loading.value = false;
  }
}
</script>

<style scoped>
button:hover {
  cursor: pointer;
}
</style>
