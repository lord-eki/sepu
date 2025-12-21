<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-6">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl sm:text-2xl font-bold text-slate-900">
            Edit Payment Voucher
          </h1>
          <p class="text-sm text-slate-500">
            Update voucher details and save changes
          </p>
        </div>

        <Link :href="route('vouchers.show', props.voucher.id)"
          class="text-sm font-medium bg-[#0a2342] px-4 py-2 text-white rounded-lg hover:text-orange-600 transition">
        Back
        </Link>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
        <form @submit.prevent="submit" class="p-6 space-y-6">

          <!-- Voucher Type -->
          <div>
            <label class="label">Voucher Type</label>
            <select v-model="form.voucher_type" class="input">
              <option disabled value="">Select voucher type</option>
              <option v-for="(label, value) in voucherTypes" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
            <InputError :message="form.errors.voucher_type" />
          </div>

          <!-- Payee -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="label">Payee Name</label>
              <input v-model="form.payee_name" class="input" />
              <InputError :message="form.errors.payee_name" />
            </div>

            <div>
              <label class="label">Payee Phone</label>
              <input v-model="form.payee_phone" class="input" />
              <InputError :message="form.errors.payee_phone" />
            </div>
          </div>

          <div>
            <label class="label">Payee Account</label>
            <input v-model="form.payee_account" class="input" />
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
            <input v-model="form.purpose" class="input" />
            <InputError :message="form.errors.purpose" />
          </div>

          <!-- Description -->
          <div>
            <label class="label">Description</label>
            <textarea v-model="form.description" rows="3" class="input resize-none" />
          </div>

          <!-- Budget -->
          <div>
            <label class="label">Budget Item</label>
            <select v-model="form.budget_item_id" class="input">
              <option :value="null">— None —</option>
              <option v-for="item in budgetItems" :key="item.id" :value="item.id">
                {{ item.budget.name }} – {{ item.name }}
                ({{ item.remaining_amount }})
              </option>
            </select>
          </div>

          <!-- Loan -->
          <div v-if="form.voucher_type === 'loan_disbursement'"
            class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <label class="label">Loan</label>
            <select v-model="form.loan_id" class="input">
              <option :value="null">Select Loan</option>
              <option v-for="loan in pendingLoans" :key="loan.id" :value="loan.id">
                {{ loan.member.first_name }}
                {{ loan.member.last_name }} – {{ loan.amount }}
              </option>
            </select>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-5 border-t border-slate-200">
            <Link :href="route('vouchers.show', props.voucher.id)" class="btn-secondary">
            Cancel
            </Link>

            <button type="submit"
              class="bg-[#0a2342] text-white py-1 px-2 rounded-lg hover:cursor-pointer hover:bg-blue-900"
              :disabled="form.processing">
              Update
            </button>
          </div>

        </form>
      </div>
    </div>
  </AppLayout>
</template>



<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'

interface Voucher {
  id: number
  voucher_number: string
  voucher_type: string
  payee_name: string
  payee_phone: string
  payee_account: string
  amount: number
  purpose: string
  description?: string
  budget_item_id?: number | null
  loan_id?: number | null
}

const props = defineProps<{
  voucher: Voucher
  budgetItems: any[]
  pendingLoans: any[]
  voucherTypes: { label: string; value: string }[]
}>()


const breadcrumbs = [
  { title: 'Vouchers', href: route('vouchers.index') },
  { title: `${props.voucher.voucher_number}` },
]

const form = useForm({
  voucher_type: props.voucher.voucher_type,
  payee_name: props.voucher.payee_name,
  payee_phone: props.voucher.payee_phone,
  payee_account: props.voucher.payee_account,
  amount: props.voucher.amount,
  purpose: props.voucher.purpose,
  description: props.voucher.description ?? '',
  budget_item_id: props.voucher.budget_item_id ?? null,
  loan_id: props.voucher.loan_id ?? null,
})

const submit = () => {
  form.put(route('vouchers.update', props.voucher.id))
}
</script>


<style scoped>
.label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
  /* slate-700 */
  margin-bottom: 0.375rem;
}

.input {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid #cbd5e1;
  /* slate-300 */
  padding: 0.6rem 0.8rem;
  font-size: 0.875rem;
  background-color: #ffffff;
  transition: border-color 0.15s, box-shadow 0.15s;
}

.input:focus {
  border-color: #2563eb;
  /* blue-600 */
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
  outline: none;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  border-radius: 0.75rem;
  background-color: #2563eb;
  /* blue-600 */
  padding: 0.6rem 1.2rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #ffffff;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s;
}

.btn-primary:hover {
  background-color: #1d4ed8;
  /* blue-700 */
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  border-radius: 0.75rem;
  background-color: #f1f5f9;
  /* slate-100 */
  padding: 0.6rem 1.2rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s;
}

.btn-secondary:hover {
  background-color: #e2e8f0;
  /* slate-200 */
}
</style>
