<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Loan Products', href: route('loan-products.index') },
      { title: 'Create Product' },
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

        <!-- Close Button -->
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
      class="flex justify-between items-center bg-[#0B2B40] text-white m-4 p-5 rounded-xl mb-6"
    >
      <div>
        <h1 class="text-xl font-semibold">Create Loan Product</h1>
        <p class="text-sm opacity-90">
          Add a new loan product to your SACCO system
        </p>
      </div>
      <Link
        :href="route('loan-products.index')"
        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition"
      >
        <span class="max-sm:hidden">←</span> Back
      </Link>
    </div>

    <!-- Form -->
    <form @submit.prevent="submit" class="space-y-6 bg-white p-6 rounded-xl shadow-sm">
      <!-- GENERAL INFO -->
      <details class="border rounded-lg overflow-hidden" open>
        <summary class="bg-blue-50 px-4 py-3 cursor-pointer font-semibold text-[#0B2B40]">
          General Information
        </summary>
        <div class="p-4 grid sm:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-gray-700">Product Name *</label>
            <input
              v-model="form.name"
              type="text"
              class="input"
              required
              placeholder="e.g. Personal Loan"
            />
            <p v-if="form.errors.name" class="error">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Code *</label>
            <input
              v-model="form.code"
              type="text"
              class="input"
              required
              placeholder="e.g. PL001"
            />
            <p v-if="form.errors.code" class="error">{{ form.errors.code }}</p>
          </div>
          <div class="sm:col-span-2">
            <label class="text-sm font-medium text-gray-700">Description *</label>
            <textarea
              v-model="form.description"
              rows="3"
              required
              class="input"
              placeholder="Short product description"
            ></textarea>
            <p v-if="form.errors.description" class="error">{{ form.errors.description }}</p>
          </div>
        </div>
      </details>

      <!-- LIMITS & RATES -->
      <details class="border rounded-lg overflow-hidden">
        <summary class="bg-blue-50 px-4 py-3 cursor-pointer font-semibold text-[#0B2B40]">
          Loan Limits & Rates
        </summary>
        <div class="p-4 grid sm:grid-cols-2 gap-4">
          <div v-for="field in requiredNumericFields" :key="field.key">
            <label class="text-sm font-medium text-gray-700">{{ field.label }} *</label>
            <input
              v-model.number="form[field.key]"
              type="number"
              required
              class="input"
              :placeholder="field.placeholder"
            />
            <p v-if="form.errors[field.key]" class="error">{{ form.errors[field.key] }}</p>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">Interest Method *</label>
            <select v-model="form.interest_method" class="input" required>
              <option value="">Select</option>
              <option value="reducing_balance">Reducing Balance</option>
              <option value="flat_rate">Flat Rate</option>
            </select>
            <p v-if="form.errors.interest_method" class="error">{{ form.errors.interest_method }}</p>
          </div>
        </div>
      </details>

      <!-- ELIGIBILITY -->
    <details class="border rounded-lg overflow-hidden">
    <summary class="bg-blue-50 px-4 py-3 cursor-pointer font-semibold text-[#0B2B40]">
        Eligibility Criteria
    </summary>
    <div class="p-4 grid sm:grid-cols-2 gap-4">
        <div>
        <label class="text-sm font-medium text-gray-700">Min Membership (Months)</label>
        <input
            v-model.number="form.eligibility_criteria.minimum_membership_months"
            type="number"
            class="input"
            required
            min="0"
        />
        <p v-if="form.errors['eligibility_criteria.minimum_membership_months']" class="error">
            {{ form.errors['eligibility_criteria.minimum_membership_months'] }}
        </p>
        </div>

        <div>
        <label class="text-sm font-medium text-gray-700">Min Shares Balance (Ksh)</label>
        <input
            v-model.number="form.eligibility_criteria.minimum_shares_balance"
            type="number"
            class="input"
            required
            min="0"
        />
        <p v-if="form.errors['eligibility_criteria.minimum_shares_balance']" class="error">
            {{ form.errors['eligibility_criteria.minimum_shares_balance'] }}
        </p>
        </div>

        <div>
        <label class="text-sm font-medium text-gray-700">Max Loan:Shares Ratio</label>
        <input
            v-model.number="form.eligibility_criteria.maximum_loan_to_shares_ratio"
            type="number"
            class="input"
            required
            min="0"
            step="0.01"
        />
        <p v-if="form.errors['eligibility_criteria.maximum_loan_to_shares_ratio']" class="error">
            {{ form.errors['eligibility_criteria.maximum_loan_to_shares_ratio'] }}
        </p>
        </div>

        <div>
        <label class="text-sm font-medium text-gray-700">Max Salary Deduction Ratio</label>
        <input
            v-model.number="form.eligibility_criteria.maximum_salary_deduction_ratio"
            type="number"
            class="input"
            required
            min="0"
            step="0.01"
        />
        <p v-if="form.errors['eligibility_criteria.maximum_salary_deduction_ratio']" class="error">
            {{ form.errors['eligibility_criteria.maximum_salary_deduction_ratio'] }}
        </p>
        </div>

        <div>
        <label class="text-sm font-medium text-gray-700">Clean Credit History</label>
        <select v-model="form.eligibility_criteria.clean_credit_history" class="input" required>
            <option value="">Select</option>
            <option value="true">Yes</option>
            <option value="false">No</option>
        </select>
        <p v-if="form.errors['eligibility_criteria.clean_credit_history']" class="error">
            {{ form.errors['eligibility_criteria.clean_credit_history'] }}
        </p>
        </div>

        <div>
        <label class="text-sm font-medium text-gray-700">Regular Deposits Required</label>
        <select v-model="form.eligibility_criteria.regular_deposits_required" class="input" required>
            <option value="">Select</option>
            <option value="true">Yes</option>
            <option value="false">No</option>
        </select>
        <p v-if="form.errors['eligibility_criteria.regular_deposits_required']" class="error">
            {{ form.errors['eligibility_criteria.regular_deposits_required'] }}
        </p>
        </div>
    </div>
    </details>


      <!-- REQUIRED DOCUMENTS -->
      <details class="border rounded-lg overflow-hidden">
        <summary class="bg-blue-50 px-4 py-3 cursor-pointer font-semibold text-[#0B2B40]">
          Required Documents *
        </summary>
        <div class="p-4">
          <textarea
            v-model="form.required_documents"
            rows="3"
            required
            class="input"
            placeholder='e.g. Payslip, National ID, Bank Statement (separated by comma)'
          ></textarea>
          <p v-if="form.errors.required_documents" class="error">
            {{ form.errors.required_documents }}
          </p>
        </div>
      </details>

      <!-- OTHER SETTINGS -->
      <details class="border rounded-lg overflow-hidden">
        <summary class="bg-blue-50 px-4 py-3 cursor-pointer font-semibold text-[#0B2B40]">
          Other Settings
        </summary>
        <div class="p-4 grid sm:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-gray-700">Requires Guarantor?</label>
            <select v-model="form.requires_guarantor" class="input">
              <option value="true">Yes</option>
              <option value="false">No</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Min Guarantors</label>
            <input v-model.number="form.min_guarantors" type="number" class="input" />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Active Status</label>
            <select v-model="form.is_active" class="input">
              <option value="true">Active</option>
              <option value="false">Inactive</option>
            </select>
          </div>
        </div>
      </details>

      <!-- Submit Button -->
      <div class="pt-4 flex justify-end">
        <button
          type="submit"
          class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-medium transition disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="form.processing || !isFormValid"
        >
          {{ form.processing ? "Saving..." : "Create Product" }}
        </button>
      </div>
    </form>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm, Link, usePage } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import AppLayout from "@/layouts/AppLayout.vue";

const { flash } = usePage().props;

const form = useForm({
  name: "",
  code: "",
  description: "",
  min_amount: "",
  max_amount: "",
  interest_rate: "",
  interest_method: "",
  min_term_months: "",
  max_term_months: "",
  processing_fee_rate: "",
  insurance_rate: "",
  grace_period_days: "",
  penalty_rate: "",
  eligibility_criteria: {
    minimum_membership_months: 0,
    minimum_shares_balance: 0,
    maximum_loan_to_shares_ratio: 0,
    maximum_salary_deduction_ratio: 0,
    clean_credit_history: false,
    regular_deposits_required: false,
  },
  required_documents: [],
  requires_guarantor: false,
  min_guarantors: 0,
  is_active: true,
});

const showFlash = ref(!!(flash.success || flash.error));

// Flash auto-hide
watch(
  () => [flash.success, flash.error],
  () => {
    if (flash.success || flash.error) {
      showFlash.value = true;
      setTimeout(() => (showFlash.value = false), 5000);
    }
  },
  { immediate: true }
);

// Numeric fields list for loop rendering
const requiredNumericFields = [
  { key: "min_amount", label: "Minimum Amount", placeholder: "e.g. 1000" },
  { key: "max_amount", label: "Maximum Amount", placeholder: "e.g. 50000" },
  { key: "interest_rate", label: "Interest Rate (%)", placeholder: "e.g. 12" },
  { key: "min_term_months", label: "Min Term (Months)", placeholder: "e.g. 6" },
  { key: "max_term_months", label: "Max Term (Months)", placeholder: "e.g. 24" },
  { key: "processing_fee_rate", label: "Processing Fee Rate (%)", placeholder: "e.g. 1" },
  { key: "insurance_rate", label: "Insurance Rate (%)", placeholder: "e.g. 0.5" },
  { key: "grace_period_days", label: "Grace Period (Days)", placeholder: "e.g. 30" },
  { key: "penalty_rate", label: "Penalty Rate (%)", placeholder: "e.g. 2" },
];

const isFormValid = computed(() => {
  return (
    form.name &&
    form.code &&
    form.description &&
    form.min_amount &&
    form.max_amount &&
    form.interest_rate &&
    form.interest_method &&
    form.min_term_months &&
    form.max_term_months &&
    form.processing_fee_rate &&
    form.insurance_rate &&
    form.grace_period_days &&
    form.penalty_rate &&
    form.eligibility_criteria.minimum_membership_months >= 0 &&
    form.eligibility_criteria.minimum_shares_balance >= 0 &&
    form.eligibility_criteria.maximum_loan_to_shares_ratio >= 0 &&
    form.eligibility_criteria.maximum_salary_deduction_ratio >= 0 &&
    form.eligibility_criteria.clean_credit_history !== "" &&
    form.eligibility_criteria.regular_deposits_required !== "" &&
    form.requires_guarantor !== "" &&
    form.min_guarantors !== "" &&
    form.required_documents &&
    form.required_documents.length > 0 &&
    form.is_active !== ""
  );
});


// Submit handler
function submit() {
  // Convert documents text to array
  if (typeof form.required_documents === "string") {
    try {
      const parsed = JSON.parse(form.required_documents);
      form.required_documents = Array.isArray(parsed) ? parsed : [];
    } catch {
      form.required_documents = form.required_documents
        .split(",")
        .map((d) => d.trim())
        .filter(Boolean);
    }
  }

  form.post(route("loan-products.store"), {
    onError: (errors) => {
      flash.error = "Please correct the highlighted errors and try again.";
      showFlash.value = true;
      setTimeout(() => (showFlash.value = false), 8000);
    },
    onSuccess: () => {
      flash.success = "Loan product created successfully!";
      showFlash.value = true;
      setTimeout(() => (showFlash.value = false), 8000);
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
  transition: border-color 0.2s, box-shadow 0.2s;
}
.input:focus {
  border-color: #fb923c;
  box-shadow: 0 0 0 2px rgba(251, 146, 60, 0.4);
}
.error {
  color: #dc2626;
  font-size: 0.75rem;
  margin-top: 0.25rem;
}
details[open] summary {
  background-color: #dbeafe;
}
</style>
