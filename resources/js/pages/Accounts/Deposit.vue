<template>
  <AppLayout :breadcrumbs="[
    { title: isMemberRole ? 'My Accounts' : 'Accounts', href: isMemberRole ? route('my-accounts') : route('accounts.index') },
    { title: 'Deposit' }
  ]">


    <!-- Flash Messages -->
        <div class="max-w-2xl mx-auto mt-4 sm:mt-6 px-4">
          <transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
            <div v-if="successMessage || errorMessages" :class="[
    successMessage
      ? 'bg-green-100 text-green-900 border border-green-300'
      : 'bg-red-100 text-red-900 border border-red-300',
    'relative w-full px-6 py-3 rounded-lg mb-4 flex items-center shadow-md'
  ]">
              <!-- Success -->
              <span v-if="successMessage" class="flex-1">{{ successMessage }}</span>
              <!-- Errors -->
              <ul v-else class="flex-1 list-disc pl-4 space-y-1">
                <li v-for="(errs, field) in errorMessages" :key="field">
                  <template v-if="field === 'general'">{{ errs.join(', ') }}</template>
                  <template v-else>
                    <strong>{{ field }}:</strong> {{ errs.join(', ') }}
                  </template>
                </li>
              </ul>
              <!-- Close -->
              <button type="button" class="ml-3 text-gray-500 hover:text-gray-700"
                @click="() => { successMessage = null; errorMessages = null }">
                ✕
              </button>
            </div>
          </transition>
        </div>



    <div class="mt-2 flex mx-[5%] justify-between items-center">
      <div>
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
          Make Deposit
        </h2>
        <p class="text-sm text-gray-600 mt-1">
          {{ account.account_number }} - {{ account.member.first_name }} {{ account.member.last_name }}
        </p>
      </div>

      <!-- Back to Accounts -->
      <Link :href="isMemberRole ? route('my-accounts') : route('accounts.index')"
        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
      Back<span class="max-sm:hidden">&nbsp;to accounts</span>
      </Link>

    </div>

    <div class="py-5">
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
                  <div class="mt-1 text-lg font-medium text-blue-900">
                    {{ formatCurrency(account.balance) }}
                  </div>
                </div>
                <div>
                  <span class="font-medium">Available Balance:</span>
                  <div class="mt-1 text-lg font-medium text-gray-700">
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
                  <input id="amount" v-model.number="form.amount" type="number" step="0.01" min="5000"
                    class="focus:ring-indigo-500 border focus:border-indigo-500 block w-full pl-12 pr-12 sm:text-sm border-gray-300 rounded-md p-2"
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

               <!-- Deposit Options -->
                <div v-if="form.payment_method === 'mobile_money'"
                    class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                  <h3 class="text-base font-medium text-blue-900 mb-2">Deposit Options (Mobile Money)</h3>
                  <p class="text-xs text-gray-600 mb-5">
                    Minimum deposit: <span class="font-semibold text-green-900">KES 5,000</span>
                  </p>

                  <!-- STK Push Option -->
                  <div class="p-4 border rounded-lg bg-white mb-4">
                    <p class="text-base font-medium text-green-700 mb-2">
                      Deposit via M-Pesa Express
                    </p>
                    <p class="text-xs text-gray-500 mb-3">
                      Fast and secure – you’ll receive an M-Pesa prompt
                    </p>

                    <div class="space-y-3 flex flex-col sm:flex-row items-center gap-3 justify-between">
                      <!-- Phone Input -->
                      <div class="w-full sm:w-[50%]">
                        <label for="stk_phone" class="block text-sm font-medium text-green-700">Phone Number *</label>
                        <input id="stk_phone" v-model="form.stk_phone" type="tel"
                          placeholder="e.g., 254712345678"
                          class="mt-1 block w-full rounded-md p-2 border border-gray-300 shadow-sm
                                focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                        <p class="text-xs text-gray-500 mt-1">Format: 2547XXXXXXXX</p>
                      </div>

                      <!-- STK Push Button -->
                      <button type="button"
                        class="w-fit px-4 py-2 rounded-md bg-blue-500 text-white text-sm font-medium
                              hover:cursor-pointer hover:bg-blue-600"
                        
                        @click="initiateStkPush">
                        Initiate Payment
                      </button>
                    </div>
                  </div>

                <!-- OR Divider -->
                <div class="flex items-center justify-center my-3">
                  <span class="text-xs text-gray-500">OR</span>
                </div>

                <!-- Paybill Option -->
                <div class="p-3 border rounded-lg bg-white">
                  <p class="text-base font-medium text-blue-800">Deposit via Paybill</p>
                  <ul class="mt-2 text-base text-gray-700 space-y-1">
                    <li><span class="font-semibold">Paybill:</span> 400200</li>
                    <li><span class="font-semibold">Account No:</span> 01120040146200</li>
                    <li><span class="font-semibold">Account Name:</span> SEPU SACCO SOCIETY LIMITED</li>
                    <li><span class="font-semibold">Minimum Deposit:</span> Kshs. 5,000</li>
                  </ul>

                  <!-- Reference ID Input -->
                  <div class="mt-10">
                    <label for="payment_reference" class="block text-sm font-medium text-blue-800">
                      Enter M-Pesa Transaction ID
                    </label>
                    <input id="payment_reference" v-model="form.payment_reference" type="text"
                      class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm
                            focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2"
                      placeholder="e.g., MP240315ABC123" />
                  </div>
                </div>
              </div>

            <!-- Payment Reference -->
              <div v-if="form.payment_method && form.payment_method !== 'cash' && form.payment_method !== 'mobile_money'">
                <label for="payment_reference" class="block text-sm font-medium text-gray-700">
                  Payment Reference
                  <span v-if="form.payment_method === 'mobile_money'" class="text-xs text-gray-500">(Transaction
                    ID)</span>
                  <span v-if="form.payment_method === 'bank_transfer'" class="text-xs text-gray-500">(Transfer
                    Reference)</span>
                  <span v-if="form.payment_method === 'cheque'" class="text-xs text-gray-500">(Cheque Number)</span>
                </label>
                <input id="payment_reference" v-model="form.payment_reference" type="text"
                  class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 border block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2"
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
                  class="mt-1 shadow-sm border focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2"
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
                <Link :href="isMemberRole
    ? route('my-accounts')
    : route('accounts.index')"
                  class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
                </Link>
                <button type="submit" :disabled="form.processing || !form.amount || form.amount < 5000 || (form.payment_method === 'mobile_money' && !form.payment_reference)"
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
            <!-- Transaction Status Reflection -->
            <div v-if="transaction" class="mt-8 bg-gray-50 border rounded-lg p-6 shadow-sm">
              <h3 class="text-md font-semibold text-gray-800 mb-4">Deposit Status</h3>

              <div class="flex items-center space-x-3">
                <div :class="[
                'h-3 w-3 rounded-full',
                transaction.status === 'success' ? 'bg-green-500' :
                  transaction.status === 'pending' ? 'bg-yellow-500 animate-pulse' :
                    'bg-red-500'
              ]">
                            </div>
                            <span class="text-sm">
                              <template v-if="transaction.status === 'pending'">
                                Deposit of <span class="font-medium text-blue-600">{{ formatCurrency(transaction.amount) }}</span>
                                is
                                <span class="font-semibold">pending</span>. Waiting for M-Pesa confirmation…
                              </template>
                              <template v-else-if="transaction.status === 'success'">
                                ✅ Deposit of <span class="font-medium text-green-600">{{ formatCurrency(transaction.amount)
                                  }}</span> was
                                <span class="font-semibold">successful</span>.
                                New Balance: <span class="font-semibold text-green-700">{{ formatCurrency(account.balance) }}</span>
                              </template>
                              <template v-else>
                                ❌ Deposit failed. Please try again or check your M-Pesa messages.
                              </template>
                            </span>
                          </div>

              <!-- Reference -->
              <div v-if="transaction.status === 'success'" class="mt-4 text-sm text-gray-700">
                <p><span class="font-medium">Receipt:</span> {{ transaction.payment_reference }}</p>
                <p><span class="font-medium">Transaction ID:</span> {{ transaction.id }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- STK Push Modal -->
    <div v-if="stkModal"
      class="fixed inset-0 flex items-center justify-center bg-[rgba(0,0,0,0.8)] z-50">
      <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-6 relative">
        <h3 class="text-lg font-semibold text-blue-900 mb-3">M-Pesa Prompt Sent</h3>
        <p class="text-sm text-gray-700 mb-4">
          An M-Pesa prompt has been sent to your phone number
          <span class="font-medium text-blue-600">{{ form.stk_phone }}</span>.
          Please enter your M-Pesa PIN on your phone to approve the payment.
          Once you’ve completed the prompt, click
          <span class="font-semibold">Complete</span> below.
        </p>

        <div class="flex justify-end gap-3">
          <button @click="closeStkModal"
            class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button @click="closeStkModal"
            class="px-4 py-2 rounded-md bg-green-600 text-white font-medium hover:bg-green-700">
            Complete
          </button>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import { Head, Link, usePage, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, watch, computed } from "vue"

const props = defineProps({
  account: Object,
  paymentMethods: Object,
  authUser: Object,
})

const page = usePage()
const isMemberRole = computed(() => props.authUser?.role?.includes('member'))


const successMessage = ref(null)
const errorMessages = ref(null)


const showMessage = (type, message, errors = null, callback = null) => {
  window.scrollTo({ top: 0, behavior: 'smooth' })

  if (type === 'success') {
    successMessage.value = message
    errorMessages.value = null
  } else {
    errorMessages.value = errors || { general: [message] }
    successMessage.value = null
  }

  setTimeout(() => {
    successMessage.value = null
    errorMessages.value = null

    if (callback) callback()
  }, 5000)
}




const form = useForm({
  amount: '',
  payment_method: '',
  payment_reference: '',
  description: '',
  stk_phone: ''
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

const stkModal = ref(false)

const initiateStkPush = () => {

  if (!form.amount || form.amount < 5000) {
    showMessage("error", "Minimum deposit is KES 5,000")
    return
  }
  if (!form.stk_phone) {
    showMessage("error", "Please enter your phone number")
    return
  }


  //for testing
  stkModal.value = true;

  // Call backend for STK push
  form.post(route('payments.stkpush'), {
    preserveScroll: true,
    onSuccess: () => {
      stkModal.value = true
    },
    onError: () => {
      flashMessage.value = "Failed to initiate STK Push, try again"
      flashType.value = "error"
    }
  })
}

const closeStkModal = () => {
  stkModal.value = false
}


</script>