<template>
  <AppLayout :breadcrumbs="[{ title: 'Accounts', href: '/my-accounts' }, { title: 'Deposit' }]">

    <div v-if="flashMessage" class="w-fit px-6  p-3 rounded mb-4 mx-auto text-white"
      :class="flashType === 'success' ? 'bg-green-700' : 'bg-red-600'">
      {{ flashMessage }}
    </div>


    <div class="flex mx-[5%] mt-5 justify-between items-center">
      <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Make Deposit
        </h2>
        <p class="text-sm text-gray-600 mt-1">
          {{ account.account_number }} - {{ account.member.first_name }} {{ account.member.last_name }}
        </p>
      </div>
      <Link :href="route('accounts.show', account.id)"
        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
      Back to Account
      </Link>
    </div>


    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Account Summary -->
            <div class="bg-gray-50 p-4 rounded-md mb-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Account Summary</h3>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                  <span class="font-medium">Account Type:</span>
                  <div class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getAccountTypeBadgeClass(account.account_type)">
                      {{ getAccountTypeLabel(account.account_type) }}
                    </span>
                  </div>
                </div>
                <div>
                  <span class="font-medium">Current Balance:</span>
                  <div class="mt-1 text-lg font-semibold text-green-600">
                    {{ formatCurrency(account.balance) }}
                  </div>
                </div>
                <div>
                  <span class="font-medium">Available Balance:</span>
                  <div class="mt-1 text-lg font-semibold text-blue-600">
                    {{ formatCurrency(account.available_balance) }}
                  </div>
                </div>
                <div>
                  <span class="font-medium">Status:</span>
                  <div class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                      {{ account.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
              <!-- Amount -->
              <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">
                  Deposit Amount (KES) *
                </label>
                <div class="mt-1 relative rounded-md shadow-sm">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">KES</span>
                  </div>
                  <input id="amount" v-model.number="form.amount" type="number" step="0.01" min="1"
                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-12 pr-12 sm:text-sm border-gray-300 rounded-md p-2"
                    placeholder="0.00" required>
                </div>
                <div v-if="form.errors.amount" class="mt-2 text-sm text-red-600">
                  {{ form.errors.amount }}
                </div>
                <div v-if="form.amount > 0" class="mt-2 text-sm text-gray-600">
                  New balance will be: <span class="font-medium text-green-600">{{
    formatCurrency(Number(account.balance)
      + Number(form.amount)) }}</span>
                </div>
              </div>

              <!-- Payment Method -->
              <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700">
                  Payment Method *
                </label>
                <select id="payment_method" v-model="form.payment_method"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                  required>
                  <option value="">Select payment method</option>
                  <option v-for="(label, value) in paymentMethods" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <div v-if="form.errors.payment_method" class="mt-2 text-sm text-red-600">
                  {{ form.errors.payment_method }}
                </div>
              </div>

              <!-- Payment Reference -->
              <div v-if="form.payment_method && form.payment_method !== 'cash'">
                <label for="payment_reference" class="block text-sm font-medium text-gray-700">
                  Payment Reference
                  <span v-if="form.payment_method === 'mobile_money'" class="text-xs text-gray-500">(Transaction
                    ID)</span>
                  <span v-if="form.payment_method === 'bank_transfer'" class="text-xs text-gray-500">(Transfer
                    Reference)</span>
                  <span v-if="form.payment_method === 'cheque'" class="text-xs text-gray-500">(Cheque Number)</span>
                </label>
                <input id="payment_reference" v-model="form.payment_reference" type="text"
                  class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2"
                  :placeholder="getPaymentReferencePlaceholder(form.payment_method)">
                <div v-if="form.errors.payment_reference" class="mt-2 text-sm text-red-600">
                  {{ form.errors.payment_reference }}
                </div>
              </div>

              <!-- Description -->
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                  Description
                </label>
                <textarea id="description" v-model="form.description" rows="3"
                  class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2"
                  placeholder="Optional description for this deposit"></textarea>
                <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                  {{ form.errors.description }}
                </div>
              </div>

              <!-- Transaction Summary -->
              <div v-if="form.amount > 0" class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <h3 class="text-sm font-medium text-blue-800 mb-3">Transaction Summary</h3>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span>Account:</span>
                    <span class="font-medium">{{ account.account_number }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Current Balance:</span>
                    <span class="font-medium">{{ formatCurrency(account.balance) }}</span>
                  </div>
                  <div class="flex justify-between text-green-700">
                    <span>Deposit Amount:</span>
                    <span class="font-medium">+{{ formatCurrency(form.amount) }}</span>
                  </div>
                  <div class="flex justify-between border-t pt-2 text-base font-semibold text-blue-900">
                    <span>New Balance:</span>
                    <span>{{ formatCurrency(account.balance + form.amount) }}</span>
                  </div>
                  <div v-if="form.payment_method" class="flex justify-between">
                    <span>Payment Method:</span>
                    <span class="font-medium">{{ paymentMethods[form.payment_method] }}</span>
                  </div>
                  <div v-if="form.payment_reference" class="flex justify-between">
                    <span>Reference:</span>
                    <span class="font-medium">{{ form.payment_reference }}</span>
                  </div>
                </div>
              </div>

              <!-- Account Type Specific Info -->
              <div v-if="account.account_type === 'shares'"
                class="bg-purple-50 border border-purple-200 rounded-md p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-purple-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-purple-800">
                      Share Purchase
                    </h3>
                    <div class="mt-2 text-sm text-purple-700">
                      <p>
                        This deposit will be recorded as a share purchase. Shares represent ownership
                        in the cooperative and entitle you to dividends based on the cooperative's performance.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="flex items-center justify-between pt-6 border-t">
                <Link :href="route('accounts.show', account.id)"
                  class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
                </Link>
                <button type="submit" :disabled="form.processing || !form.amount || form.amount <= 0"
                  class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  {{ form.processing ? 'Processing...' : 'Process Deposit' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, usePage, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, watch } from "vue"

const props = defineProps({
  account: Object,
  paymentMethods: Object,
  authUser: Object,
})

const page = usePage()

const flashMessage = ref(null)
const flashType = ref('success')

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      flashMessage.value = flash.success
      flashType.value = 'success'
      form.reset()
    } else if (flash?.error) {
      flashMessage.value = flash.error
      flashType.value = 'error'
      form.reset()
    }

    // Auto hide after 3 seconds
    if (flashMessage.value) {
      setTimeout(() => {
        flashMessage.value = null
      }, 3000)
    }
  },
  { immediate: true, deep: true }
)

const form = useForm({
  amount: '',
  payment_method: '',
  payment_reference: '',
  description: '',
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount || 0)
}

const getAccountTypeLabel = (type) => {
  const labels = {
    'savings': 'Savings Account',
    'shares': 'Shares Account',
    'deposits': 'Deposits Account'
  }
  return labels[type] || type
}

const getAccountTypeBadgeClass = (type) => {
  const classes = {
    'savings': 'bg-blue-100 text-blue-800',
    'shares': 'bg-purple-100 text-purple-800',
    'deposits': 'bg-green-100 text-green-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getPaymentReferencePlaceholder = (method) => {
  const placeholders = {
    'mobile_money': 'e.g., MP240315ABC123',
    'bank_transfer': 'e.g., TXN123456789',
    'cheque': 'e.g., 001234'
  }
  return placeholders[method] || 'Enter reference'
}

const submit = () => {
  if (props.authUser?.role?.includes('member')) {
    // Member deposit route
    form.post(route('members.accounts.deposit', {
      member: props.account.member.id,
      account: props.account.id,
    }))
  } else {
    // Staff/admin deposit route
    form.post(route('accounts.deposit', props.account.id))
  }
}

</script>