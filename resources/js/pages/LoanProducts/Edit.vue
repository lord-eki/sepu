<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Loan Products', href: route('loan-products.index') },
      { title: 'Edit Product' },
    ]"
  >
    <!-- Flash Messages -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="showFlash"
        class="relative mb-4 p-4 mx-auto rounded-xl flex justify-between items-center"
        :class="flash.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
        >
        <div class="pr-8">
            {{ flash.success || flash.error }}
        </div>

        <button
            @click="showFlash = false"
            class="absolute top-2 right-3 text-lg text-current hover:opacity-70"
            aria-label="Close"
        >
            X
        </button>
        </div>

    </transition>

    <!-- Header -->
    <div
      class="flex justify-between m-4 items-center bg-[#0B2B40] text-white p-5 rounded-xl mb-6"
    >
      <div>
        <h1 class="text-xl font-semibold">Edit Loan Product</h1>
        <p class="text-sm opacity-90">
          Update details for <strong>{{ form.name }}</strong>
        </p>
      </div>
      <Link
        :href="route('loan-products.index')"
        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition"
      >
        <span class="max-sm:hidden">←</span> Back
      </Link>
    </div>

    <!-- Status Banner -->
    <div
      class="flex justify-between mx-4 items-center bg-blue-50 border border-blue-100 text-[#0B2B40] p-4 rounded-lg mb-6"
    >
      <p class="text-sm">
        Status:
        <span
          class="px-3 py-1 rounded-full text-xs font-semibold"
          :class="form.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
        >
          {{ form.is_active ? 'Active' : 'Inactive' }}
        </span>
      </p>
    </div>

    <!-- Form -->
    <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-xl shadow-sm">
      <!-- GENERAL INFO -->
      <details class="border rounded-lg overflow-hidden" open>
        <summary
          class="bg-blue-50 px-4 py-3 cursor-pointer font-semibold text-[#0B2B40]"
        >
          General Information
        </summary>
        <div class="p-4 grid sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Product Name</label>
            <input
              v-model="form.name"
              type="text"
              class="input"
              :class="{ 'border-red-500 bg-red-50': form.errors.name }"
            />
            <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>
          </div>

          <div>
            <label class="label">Code</label>
            <input
              v-model="form.code"
              type="text"
              class="input"
              :class="{ 'border-red-500 bg-red-50': form.errors.code }"
            />
            <p v-if="form.errors.code" class="error">{{ form.errors.code }}</p>
          </div>

          <div class="sm:col-span-2">
            <label class="label">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="input"
              :class="{ 'border-red-500 bg-red-50': form.errors.description }"
            ></textarea>
            <p v-if="form.errors.description" class="error">{{ form.errors.description }}</p>
          </div>
        </div>
      </details>

      <!-- LIMITS & RATES -->
      <details class="border rounded-lg overflow-hidden">
        <summary
          class="bg-blue-50 px-4 py-3 cursor-pointer font-semibold text-[#0B2B40]"
        >
          Loan Limits & Rates
        </summary>
        <div class="p-4 grid sm:grid-cols-2 gap-4">
          <div v-for="field in limitsAndRates" :key="field.key">
            <label class="label">{{ field.label }}</label>
            <input
              v-model.number="form[field.key]"
              type="number"
              class="input"
              :class="{ 'border-red-500 bg-red-50': form.errors[field.key] }"
              step="any"
            />
            <p v-if="form.errors[field.key]" class="error">{{ form.errors[field.key] }}</p>
          </div>


          <div>
            <label class="label">Interest Method</label>
            <select
              v-model="form.interest_method"
              class="input"
              :class="{ 'border-red-500 bg-red-50': form.errors.interest_method }"
            >
              <option value="">Select</option>
              <option value="reducing_balance">Reducing Balance</option>
              <option value="flat_rate">Flat Rate</option>
            </select>
            <p v-if="form.errors.interest_method" class="error">
              {{ form.errors.interest_method }}
            </p>
          </div>
        </div>
      </details>

      <!-- ELIGIBILITY -->
      <details class="border rounded-lg overflow-hidden">
        <summary
          class="bg-blue-50 px-4 py-3 cursor-pointer font-semibold text-[#0B2B40]"
        >
          Eligibility Criteria
        </summary>
        <div class="p-4 grid sm:grid-cols-2 gap-4">
          <div v-for="field in eligibilityFields" :key="field.key">
            <label class="label">{{ field.label }}</label>
            <input
              v-model.number="form.eligibility_criteria[field.key]"
              type="number"
              step="any"
              class="input"
            />
          </div>


          <div>
            <label class="label">Clean Credit History</label>
            <select v-model="form.eligibility_criteria.clean_credit_history" class="input">
              <option :value="true">Yes</option>
              <option :value="false">No</option>
            </select>
          </div>

          <div>
            <label class="label">Regular Deposits Required</label>
            <select v-model="form.eligibility_criteria.regular_deposits_required" class="input">
              <option :value="true">Yes</option>
              <option :value="false">No</option>
            </select>
          </div>
        </div>
      </details>

      <!-- REQUIRED DOCUMENTS -->
      <details class="border rounded-lg overflow-hidden">
        <summary
          class="bg-blue-50 px-4 py-3 cursor-pointer font-semibold text-[#0B2B40]"
        >
          Required Documents
        </summary>
        <div class="p-4">
          <textarea v-model="form.required_documents" rows="3" class="input"></textarea>
        </div>
      </details>

      <!-- OTHER SETTINGS -->
      <details class="border rounded-lg overflow-hidden">
        <summary
          class="bg-blue-50 px-4 py-3 cursor-pointer font-semibold text-[#0B2B40]"
        >
          Other Settings
        </summary>
        <div class="p-4 grid sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Requires Guarantor?</label>
            <select v-model="form.requires_guarantor" class="input">
              <option :value="true">Yes</option>
              <option :value="false">No</option>
            </select>
          </div>

          <div>
            <label class="label">Min Guarantors</label>
            <input
              v-model="form.min_guarantors"
              type="number"
              class="input"
            />
          </div>

          <div>
            <label class="label">Active Status</label>
            <select v-model="form.is_active" class="input">
              <option :value="true">Active</option>
              <option :value="false">Inactive</option>
            </select>
          </div>
        </div>
      </details>

      <!-- Submit Button -->
      <div class="pt-4 flex justify-end">
        <button
          type="submit"
          class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-medium transition"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { useForm, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";

const props = defineProps({
  loanProduct: Object,
});

import { reactive, ref } from 'vue';
const flash = reactive({ success: '', error: '' });
const showFlash = ref(false);


const form = useForm({
  ...props.loanProduct,
  eligibility_criteria: {
    minimum_membership_months:
      props.loanProduct?.eligibility_criteria?.minimum_membership_months ?? 0,
    minimum_shares_balance:
      props.loanProduct?.eligibility_criteria?.minimum_shares_balance ?? 0,
    maximum_loan_to_shares_ratio:
      props.loanProduct?.eligibility_criteria?.maximum_loan_to_shares_ratio ?? 0,
    maximum_salary_deduction_ratio:
      props.loanProduct?.eligibility_criteria?.maximum_salary_deduction_ratio ?? 0,
    clean_credit_history:
      !!props.loanProduct?.eligibility_criteria?.clean_credit_history,
    regular_deposits_required:
      !!props.loanProduct?.eligibility_criteria?.regular_deposits_required,
  },
  required_documents: Array.isArray(props.loanProduct.required_documents)
    ? props.loanProduct.required_documents
    : [],
});

const limitsAndRates = [
  { label: "Minimum Amount", key: "min_amount" },
  { label: "Maximum Amount", key: "max_amount" },
  { label: "Interest Rate (%)", key: "interest_rate" },
  { label: "Min Term (Months)", key: "min_term_months" },
  { label: "Max Term (Months)", key: "max_term_months" },
  { label: "Processing Fee Rate (%)", key: "processing_fee_rate" },
  { label: "Insurance Rate (%)", key: "insurance_rate" },
  { label: "Grace Period (Days)", key: "grace_period_days" },
  { label: "Penalty Rate (%)", key: "penalty_rate" },
];

const eligibilityFields = [
  { label: "Min Membership (Months)", key: "minimum_membership_months" },
  { label: "Min Shares Balance (Ksh)", key: "minimum_shares_balance" },
  { label: "Max Loan:Shares Ratio", key: "maximum_loan_to_shares_ratio" },
  { label: "Max Salary Deduction Ratio", key: "maximum_salary_deduction_ratio" },
];





const limitsAndRates1 = [
  {
    label: "Minimum Amount",
    model: form.min_amount,
    error: form.errors.min_amount,
  },
  {
    label: "Maximum Amount",
    model: form.max_amount,
    error: form.errors.max_amount,
  },
  {
    label: "Interest Rate (%)",
    model: form.interest_rate,
    error: form.errors.interest_rate,
  },
  {
    label: "Min Term (Months)",
    model: form.min_term_months,
    error: form.errors.min_term_months,
  },
  {
    label: "Max Term (Months)",
    model: form.max_term_months,
    error: form.errors.max_term_months,
  },
  {
    label: "Processing Fee Rate (%)",
    model: form.processing_fee_rate,
    error: form.errors.processing_fee_rate,
  },
  {
    label: "Insurance Rate (%)",
    model: form.insurance_rate,
    error: form.errors.insurance_rate,
  },
  {
    label: "Grace Period (Days)",
    model: form.grace_period_days,
    error: form.errors.grace_period_days,
  },
  {
    label: "Penalty Rate (%)",
    model: form.penalty_rate,
    error: form.errors.penalty_rate,
  },
];

const eligibilityFields1 = [
  {
    label: "Min Membership (Months)",
    model: form.eligibility_criteria.minimum_membership_months,
  },
  {
    label: "Min Shares Balance (Ksh)",
    model: form.eligibility_criteria.minimum_shares_balance,
  },
  {
    label: "Max Loan:Shares Ratio",
    model: form.eligibility_criteria.maximum_loan_to_shares_ratio,
  },
  {
    label: "Max Salary Deduction Ratio",
    model: form.eligibility_criteria.maximum_salary_deduction_ratio,
  },
];

function submit() {
  form.put(route('loan-products.update', props.loanProduct.id), {
    onError: () => {
      flash.error = 'Please correct the highlighted errors and try again.';
      flash.success = '';
      showFlash.value = true;
      setTimeout(() => (showFlash.value = false), 5000);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    onSuccess: () => {
      flash.success = 'Changes saved successfully.';
      flash.error = '';
      showFlash.value = true;
      setTimeout(() => (showFlash.value = false), 4000);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
  });
}

</script>

<style scoped>
.input {
  width: 100%;
  margin-top: 0.25rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  outline: none;
  transition: all 0.2s ease;
}

.input:focus {
  border-color: #fb923c;
  box-shadow: 0 0 0 2px rgba(251, 146, 60, 0.4);
}

.label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.error {
  color: #dc2626;
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.border-red-500 {
  border-color: #dc2626 !important;
}
.bg-red-50 {
  background-color: #fef2f2 !important;
}
</style>
