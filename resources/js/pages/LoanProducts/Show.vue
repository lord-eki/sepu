<template>
  <AppLayout
    :breadcrumbs="[
      { title: 'Loan Products', href: route('loan-products.index') },
      { title: loanProduct.name }
    ]"
  >
    <!-- Flash Message -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
    >
      <div
        v-if="showFlash"
        class="p-4 mb-4 rounded-xl w-fit mx-auto text-center font-medium"
        :class="flash.error ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
      >
        {{ flash.error || flash.success }}
      </div>
    </transition>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row m-4 justify-between items-start sm:items-center">
      <h1 class="text-2xl font-bold text-[#0B2B40]">{{ loanProduct.name }}</h1>
      <div class="flex space-x-2 mt-4 sm:mt-0">
        <Link
            :href="route('loan-products.edit', loanProduct.id)"
            class="px-4 flex items-center text-center border text-sm border-gray-300 bg-blue-900 rounded-lg text-white hover:bg-blue-800 transition"
            >
            Edit
        </Link>
        <Button @click="confirmDelete" class="hover:cursor-pointer" variant="destructive">Delete</Button>
        <Button
            @click="toggleStatus(loanProduct)"
            class="text-white bg-orange-500 hover:bg-orange-600 hover:cursor-pointer"
            :variant="loanProduct.is_active ? 'secondary' : 'default'"
            >
            {{ loanProduct.is_active ? 'Deactivate' : 'Activate' }}
         </Button>

      </div>
    </div>

    <!-- Details Card -->
    <Card class="shadow-sm m-4 border border-gray-200">
      <CardHeader class="bg-blue-100 p-4 rounded-t-2xl">
        <CardTitle class="text-lg font-semibold text-[#0B2B40]">
          Product Information
        </CardTitle>
      </CardHeader>

      <CardContent class="px-6 pt-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-4 text-sm">
          <div v-for="(value, label) in displayData" :key="label" class="space-y-1">
            <dt class="font-medium text-gray-500">{{ label }}</dt>
            <dd class="text-gray-800 font-semibold">{{ value }}</dd>
          </div>
        </dl>

        <!-- Eligibility Section -->
        <div class="mt-10">
          <h2 class="text-lg font-semibold text-[#0B2B40] mb-3">Eligibility Criteria</h2>
          <ul class="list-disc list-inside space-y-1 text-gray-700">
            <li>Minimum Membership Months: {{ loanProduct.eligibility_criteria.minimum_membership_months }}</li>
            <li>Minimum Shares Balance: {{ loanProduct.eligibility_criteria.minimum_shares_balance }}</li>
            <li>Max Loan to Shares Ratio: {{ loanProduct.eligibility_criteria.maximum_loan_to_shares_ratio }}</li>
            <li>Max Salary Deduction Ratio: {{ loanProduct.eligibility_criteria.maximum_salary_deduction_ratio }}</li>
            <li>
              Clean Credit History:
              <span :class="loanProduct.eligibility_criteria.clean_credit_history ? 'text-green-600' : 'text-red-600'">
                {{ loanProduct.eligibility_criteria.clean_credit_history ? 'Yes' : 'No' }}
              </span>
            </li>
            <li>
              Regular Deposits Required:
              <span :class="loanProduct.eligibility_criteria.regular_deposits_required ? 'text-green-600' : 'text-red-600'">
                {{ loanProduct.eligibility_criteria.regular_deposits_required ? 'Yes' : 'No' }}
              </span>
            </li>
          </ul>
        </div>

        <!-- Documents -->
        <div class="mt-10">
          <h2 class="text-lg font-semibold text-[#0B2B40] mb-3">Required Documents</h2>
          <ul class="list-disc list-inside space-y-1 text-gray-700">
            <li v-for="doc in loanProduct.required_documents" :key="doc">{{ doc }}</li>
          </ul>
        </div>

        <!-- Guarantor -->
        <div class="mt-10">
          <h2 class="text-lg font-semibold text-[#0B2B40] mb-3">Guarantor Requirements</h2>
          <p>
            Requires Guarantor:
            <span :class="loanProduct.requires_guarantor ? 'text-green-600' : 'text-red-600'">
              {{ loanProduct.requires_guarantor ? 'Yes' : 'No' }}
            </span>
          </p>
          <p v-if="loanProduct.requires_guarantor" class="text-gray-700">
            Minimum Guarantors: {{ loanProduct.min_guarantors }}
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- Inline Delete Modal -->
    <transition name="fade">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      >
        <div class="bg-white w-full max-w-md mx-4 rounded-2xl shadow-xl p-6 relative">
          <h3 class="text-lg font-semibold text-[#0B2B40] mb-3">Confirm Deletion</h3>
          <p class="text-gray-600 mb-6">
            Are you sure you want to delete this loan product?
            This action <span class="font-semibold text-red-600">cannot be undone.</span>
          </p>
          <div class="flex justify-end gap-3">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 rounded-xl bg-gray-200 hover:cursor-pointer text-gray-700 hover:bg-gray-300 transition"
            >
              Cancel
            </button>
            <button
            @click="deleteProduct"
            class="px-4 py-2 rounded-xl bg-red-600 hover:cursor-pointer text-white hover:bg-red-700 transition flex items-center justify-center gap-2"
            :disabled="deleting"
            >
            <span v-if="deleting" class="loader-border animate-spin w-5 h-5 rounded-full border-2 border-white border-t-transparent"></span>
            <span>{{ deleting ? 'Deleting...' : 'Yes, Delete' }}</span>
            </button>
          </div>

          <!-- Close Icon -->
          <button
            @click="showDeleteModal = false"
            class="absolute top-2 right-3 text-gray-400 hover:text-gray-600"
          >
            ✕
          </button>
        </div>
      </div>
    </transition>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
const deleting = ref(false)


const props = defineProps<{ loanProduct: any }>()

// local flash messages
const flash = reactive({ success: '', error: '' })
const showFlash = ref(false)

const showDeleteModal = ref(false)

const displayData = computed(() => ({
  'Code': props.loanProduct.code,
  'Description': props.loanProduct.description,
  'Min Amount': `Ksh ${props.loanProduct.min_amount.toLocaleString()}`,
  'Max Amount': `Ksh ${props.loanProduct.max_amount.toLocaleString()}`,
  'Interest Rate': `${props.loanProduct.interest_rate}%`,
  'Interest Method': props.loanProduct.interest_method === 'flat_rate' ? 'Flat Rate' : 'Reducing Balance',
  'Min Term (Months)': props.loanProduct.min_term_months,
  'Max Term (Months)': props.loanProduct.max_term_months,
  'Processing Fee Rate': `${props.loanProduct.processing_fee_rate}%`,
  'Insurance Rate': `${props.loanProduct.insurance_rate}%`,
  'Grace Period (Days)': props.loanProduct.grace_period_days,
  'Penalty Rate': `${props.loanProduct.penalty_rate}%`,
  'Status': props.loanProduct.is_active ? 'Active' : 'Inactive',
  'Created At': new Date(props.loanProduct.created_at).toLocaleString(),
}))

// modal handlers
const confirmDelete = () => (showDeleteModal.value = true)
const deleteProduct = () => {
  deleting.value = true // start loader

  router.delete(route('loan-products.destroy', props.loanProduct.id), {
    preserveScroll: true,
    onSuccess: (page) => {
      const message = page.props.flash.success || page.props.flash.error
      const isError = !!page.props.flash.error
      showFlashMsg(message, isError)
      showDeleteModal.value = false

      setTimeout(() => {
        router.visit(route('loan-products.index'))
      }, 500)
    },
    onError: () => {
      showFlashMsg('Failed to delete product. Please try again.', true)
      showDeleteModal.value = false
    },
    onFinish: () => {
      deleting.value = false // stop loader
    }
  })
}


function toggleStatus(product: any) {
  router.post(
    route('loan-products.toggle-status', { loanProduct: product.id }),
    {},
    {
      preserveState: true,
      onSuccess: (page) => {
        // get flash from backend response
        const message = page.props.flash.success || page.props.flash.error || 'Action completed';
        const isError = !!page.props.flash.error;
        showFlashMsg(message, isError);

        // update UI immediately
        product.is_active = !product.is_active;
      },
      onError: () => showFlashMsg('Error updating status.', true),
    }
  );
}


function showFlashMsg(message: string, isError = false) {
  flash.success = isError ? '' : message
  flash.error = isError ? message : ''
  showFlash.value = true
  window.scrollTo({ top: 0, behavior: 'smooth' })
  setTimeout(() => (showFlash.value = false), 4000)
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

dl div {
  background: #f9fafb;
  border-radius: 12px;
  padding: 10px 14px;
}
</style>
