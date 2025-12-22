<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-5xl mx-auto px-4 py-6 space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Edit Payment Voucher</h1>
        <Link :href="route('vouchers.show', voucher.id)" class="text-sm text-gray-500 hover:underline">
          Back to Voucher
        </Link>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">

        <!-- Voucher Type -->
        <div>
          <label class="label">Voucher Type</label>
          <select v-model="form.voucher_type" class="input">
            <option v-for="type in voucherTypes" :key="type.value" :value="type.value">
              {{ type.label }}
            </option>
          </select>
          <InputError :message="form.errors.voucher_type" />
        </div>

        <!-- Payee Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="label">Payee Name</label>
            <input v-model="form.payee_name" type="text" class="input" />
            <InputError :message="form.errors.payee_name" />
          </div>

          <div>
            <label class="label">Payee Phone</label>
            <input v-model="form.payee_phone" type="text" class="input" />
            <InputError :message="form.errors.payee_phone" />
          </div>
        </div>

        <div>
          <label class="label">Payee Account</label>
          <input v-model="form.payee_account" type="text" class="input" />
          <InputError :message="form.errors.payee_account" />
        </div>

        <!-- Amount -->
        <div>
          <label class="label">Amount</label>
          <input v-model="form.amount" type="number" step="0.01" class="input" />
          <InputError :message="form.errors.amount" />
        </div>

        <!-- Purpose -->
        <div>
          <label class="label">Purpose</label>
          <input v-model="form.purpose" type="text" class="input" />
          <InputError :message="form.errors.purpose" />
        </div>

        <!-- Description -->
        <div>
          <label class="label">Description</label>
          <textarea v-model="form.description" rows="3" class="input"></textarea>
        </div>

        <!-- Budget Item -->
        <div>
          <label class="label">Budget Item</label>
          <select v-model="form.budget_item_id" class="input">
            <option :value="null">-- None --</option>
            <option v-for="item in budgetItems" :key="item.id" :value="item.id">
              {{ item.budget.name }} - {{ item.name }} ({{ item.remaining_amount }})
            </option>
          </select>
          <InputError :message="form.errors.budget_item_id" />
        </div>

        <!-- Loan -->
        <div v-if="form.voucher_type === 'loan_disbursement'">
          <label class="label">Loan</label>
          <select v-model="form.loan_id" class="input">
            <option :value="null">-- Select Loan --</option>
            <option v-for="loan in pendingLoans" :key="loan.id" :value="loan.id">
              {{ loan.member.first_name }} {{ loan.member.last_name }} - {{ loan.amount }}
            </option>
          </select>
          <InputError :message="form.errors.loan_id" />
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
          <Link :href="route('vouchers.show', voucher.id)" class="btn-secondary">Cancel</Link>
          <button type="submit" class="btn-primary" :disabled="form.processing">
            Update Voucher
          </button>
        </div>

      </form>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'

const props = defineProps({
  voucher: Object,
  budgetItems: Array,
  pendingLoans: Array,
  voucherTypes: Array,
})

const breadcrumbs = [
  { title: 'Payment Vouchers', href: route('vouchers.index') },
  { title: `Edit ${props.voucher.voucher_number}` },
]

const form = useForm({
  voucher_type: props.voucher.voucher_type,
  payee_name: props.voucher.payee_name,
  payee_phone: props.voucher.payee_phone,
  payee_account: props.voucher.payee_account,
  amount: props.voucher.amount,
  purpose: props.voucher.purpose,
  description: props.voucher.description,
  budget_item_id: props.voucher.budget_item_id,
  loan_id: props.voucher.loan_id,
})

function submit() {
  form.put(route('vouchers.update', props.voucher.id))
}
</script>

<style scoped>
.label {
  display: block;
  font-size: 0.875rem; /* text-sm */
  font-weight: 500;
  color: #374151; /* gray-700 */
  margin-bottom: 0.25rem;
}

.input {
  width: 100%;
  border-radius: 0.5rem;
  border: 1px solid #d1d5db; /* gray-300 */
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  outline: none;
}

.input:focus {
  border-color: #3b82f6; /* blue-500 */
  box-shadow: 0 0 0 1px #3b82f6;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  border-radius: 0.5rem;
  background-color: #2563eb; /* blue-600 */
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #ffffff;
  border: none;
  cursor: pointer;
}

.btn-primary:hover {
  background-color: #1d4ed8; /* blue-700 */
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  border-radius: 0.5rem;
  background-color: #f3f4f6; /* gray-100 */
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151; /* gray-700 */
  border: none;
  cursor: pointer;
}

.btn-secondary:hover {
  background-color: #e5e7eb; /* gray-200 */
}
</style>
