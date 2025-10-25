<template>
  <AppLayout :breadcrumbs="[
    { title: isMemberRole ? 'My Accounts' : 'Accounts', href: isMemberRole ? route('my-accounts') : route('accounts.index') },
    { title: 'Deposit' }
  ]">
    <!-- Flash Messages -->
    <transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="successMessage || errorMessages"
        class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md shadow-lg rounded-xl overflow-hidden"
        :class="successMessage ? 'bg-green-600 text-white' : 'bg-red-600 text-white'"
      >
        <div class="flex items-center justify-between px-4 py-3">
          <div>
            <p v-if="successMessage" class="font-semibold">{{ successMessage }}</p>
            <ul v-else class="list-disc pl-5 text-sm space-y-1">
              <li v-for="(errs, field) in errorMessages" :key="field">
                <template v-if="field === 'general'">{{ errs.join(', ') }}</template>
                <template v-else><strong>{{ field }}:</strong> {{ errs.join(', ') }}</template>
              </li>
            </ul>
          </div>
          <button @click="() => { successMessage = null; errorMessages = null }" class="ml-3 text-white hover:text-gray-200">
            ✕
          </button>
        </div>
      </div>
    </transition>

    <!-- Header -->
    <div class="bg-[#0B2B40] py-6 px-[5%] flex justify-between items-center rounded-b-2xl shadow-md">
      <div>
        <h2 class="font-semibold text-xl text-white">Make Deposit</h2>
        <p class="text-sm text-gray-200 mt-1">
          {{ account.account_number }} - {{ account.member.first_name }} {{ account.member.last_name }}
        </p>
      </div>
      <Link
        :href="isMemberRole ? route('my-accounts') : route('accounts.index')"
        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold shadow transition"
      >
        Back to Account
      </Link>
    </div>

    <!-- Restriction Notice for Share Capital -->
    <div v-if="account.account_type === 'share_capital'" class="py-10 px-[5%] bg-[#F9FAFB]">
      <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-8">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-semibold text-yellow-800">Deposits Not Available</h3>
              <div class="mt-2 text-sm text-yellow-700">
                <p class="mb-3">
                  <strong>Share Capital</strong> accounts do not accept regular deposits. Share capital is paid:
                </p>
                <ul class="list-disc list-inside space-y-1 ml-2">
                  <li>Once during member registration</li>
                  <li>When purchasing shares from exiting members (share transfer)</li>
                </ul>
                <p class="mt-4">
                  For monthly share contributions, please use your <strong>Share Deposits</strong> account.
                </p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-6 flex justify-center">
          <Link
            :href="isMemberRole ? route('my-accounts') : route('accounts.index')"
            class="bg-[#0B2B40] hover:bg-[#0a2436] text-white font-semibold px-6 py-2 rounded-lg shadow"
          >
            Back to Accounts
          </Link>
        </div>
      </div>
    </div>

    <!-- Main Form (Only for Share Deposits) -->
    <div v-else class="py-10 px-[5%] bg-[#F9FAFB]">
      <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-8">
        <!-- Account Summary -->
        <div class="border-l-4 border-orange-500 bg-blue-50 rounded-md p-4 mb-6">
          <h3 class="text-lg font-semibold text-[#0B2B40] mb-3">Account Summary</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
              <span class="font-medium text-gray-700">Account Type:</span>
              <p class="mt-1 text-sm">{{ getAccountTypeLabel(account.account_type) }}</p>
            </div>
            <div>
              <span class="font-medium text-gray-700">Current Balance:</span>
              <p class="mt-1 text-lg font-semibold text-[#0B2B40]">
                {{ formatCurrency(account.balance) }}
              </p>
            </div>
            <div>
              <span class="font-medium text-gray-700">Available Balance:</span>
              <p class="mt-1 text-lg font-semibold text-green-700">
                {{ formatCurrency(account.available_balance) }}
              </p>
            </div>
            <div>
              <span class="font-medium text-gray-700">Status:</span>
              <span
                class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold"
                :class="account.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              >
                {{ account.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Info Note for Share Deposits -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">Share Deposits Information</h3>
              <div class="mt-2 text-sm text-blue-700">
                <p>This is your <strong>Share Deposits</strong> account for monthly contributions.</p>
                <ul class="list-disc list-inside mt-2 space-y-1 ml-2">
                  <li><strong>SEPU Staff:</strong> Contributions are automatically deducted from payroll</li>
                  <li><strong>Non-Staff Members:</strong> Use this form to make manual deposits</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Deposit Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Amount -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Deposit Amount (KES) *</label>
            <input
              v-model.number="form.amount"
              type="number"
              min="100"
              placeholder="Enter amount (min 100)"
              class="mt-1 w-full rounded-md border border-gray-300 p-2 focus:border-[#0B2B40] focus:ring-[#0B2B40]"
              required
            />
            <p class="mt-1 text-xs text-gray-500">Minimum deposit: KES 100</p>
          </div>

          <!-- Payment Method -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Payment Method *</label>
            <select
              v-model="form.payment_method"
              class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:border-[#0B2B40] focus:ring-[#0B2B40]"
              required
            >
              <option value="">Select payment method</option>
              <option v-for="(label, value) in paymentMethods" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>

          <!-- Conditional Payment Instructions -->
          <div
            v-if="['mobile_money', 'bank_transfer', 'cheque'].includes(form.payment_method)"
            class="bg-blue-50 border border-blue-200 rounded-lg p-5 space-y-4 mt-4"
          >
            <!-- Header Title -->
            <h3 class="text-lg font-semibold text-[#0B2B40]">
              {{
                form.payment_method === 'mobile_money'
                  ? 'Deposit via Paybill'
                  : form.payment_method === 'bank_transfer'
                  ? 'Deposit via Bank Transfer'
                  : 'Deposit via Cheque'
              }}
            </h3>

            <!-- M-Pesa Instructions -->
            <template v-if="form.payment_method === 'mobile_money'">
              <ul class="text-sm text-gray-700 space-y-1 mb-4">
                <li><strong>Paybill:</strong> 400200</li>
                <li><strong>Account No:</strong> 01120040146200</li>
                <li><strong>Account Name:</strong> SEPU SACCO SOCIETY LIMITED</li>
                <li><strong>Minimum Deposit:</strong> Kshs. 100</li>
              </ul>
            </template>

            <!-- Bank Transfer Instructions -->
            <template v-else-if="form.payment_method === 'bank_transfer'">
              <div class="space-y-2 text-sm text-gray-700 mb-4">
                <p><strong>Bank Name:</strong> Co-operative Bank of Kenya</p>
                <p><strong>Branch:</strong> Machakos Branch</p>
                <p><strong>Account Name:</strong> SEPU SACCO SOCIETY LIMITED</p>
                <p><strong>Account Number:</strong> 01120040146200</p>
                <p><strong>Swift Code:</strong> KCOOKENA</p>
              </div>
              <div class="bg-white rounded-lg border border-gray-200 p-4">
                <h4 class="text-sm font-semibold text-[#0B2B40] mb-2">Steps to Complete Bank Transfer:</h4>
                <ol class="list-decimal list-inside text-sm text-gray-700 space-y-1">
                  <li>Log in to your online or mobile banking app.</li>
                  <li>Select <strong>"Bank Transfer"</strong> or <strong>"Send Money to Account"</strong>.</li>
                  <li>Enter the above SACCO account details carefully.</li>
                  <li>Enter your desired deposit amount (minimum Ksh 100).</li>
                  <li>Confirm details and complete the transaction.</li>
                  <li>Copy or note down the <strong>Bank Transaction Reference Number</strong>.</li>
                </ol>
              </div>
            </template>

            <!-- Cheque Instructions -->
            <template v-else-if="form.payment_method === 'cheque'">
              <div class="space-y-2 text-sm text-gray-700 mb-4">
                <p><strong>Pay to:</strong> SEPU SACCO SOCIETY LIMITED</p>
                <p><strong>Deliver to:</strong> SACCO Office or nearest branch</p>
                <p>Ensure the cheque is properly signed and dated.</p>
              </div>
            </template>

            <!-- Payment Reference Input -->
            <div>
              <label for="payment_reference" class="block text-sm font-medium text-[#0B2B40]">
                {{
                  form.payment_method === 'mobile_money'
                    ? 'M-Pesa Transaction ID'
                    : form.payment_method === 'cheque'
                    ? 'Cheque Number'
                    : 'Bank Transaction Reference Number'
                }}
                *
              </label>
              <input
                id="payment_reference"
                v-model="form.payment_reference"
                type="text"
                :placeholder="
                  form.payment_method === 'mobile_money'
                    ? 'e.g., MP240315ABC123'
                    : form.payment_method === 'cheque'
                    ? 'e.g., CHQ-784562'
                    : 'e.g., BNK-202410-98765'
                "
                class="mt-1 w-full border border-gray-300 rounded-md p-2 focus:border-[#0B2B40] focus:ring-[#0B2B40]"
                required
              />
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Optional note for this deposit (e.g., Monthly share deposit for January 2025)"
              class="mt-1 w-full border border-gray-300 rounded-md p-2 focus:border-[#0B2B40] focus:ring-[#0B2B40]"
            ></textarea>
          </div>

          <!-- Submit -->
          <div class="pt-6 flex justify-between border-t border-gray-200">
            <Link
              :href="isMemberRole ? route('my-accounts') : route('accounts.index')"
              class="text-gray-600 hover:text-gray-800"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="
                form.processing ||
                !form.amount ||
                form.amount < 100 ||
                (
                  ['mobile_money', 'bank_transfer', 'cheque'].includes(form.payment_method) &&
                  !form.payment_reference
                )
              "
              class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-2 rounded-lg shadow disabled:opacity-50"
            >
              {{ form.processing ? 'Processing...' : 'Process Deposit' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link, usePage, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed } from 'vue'

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
})

const paymentMethods = {
  mobile_money: 'M-Pesa (Paybill)',
  bank_transfer: 'Bank Transfer',
  cheque: 'Cheque',
  cash: 'Cash Deposit'
}

const formatCurrency = (amount) =>
  new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES' }).format(amount || 0)

const getAccountTypeLabel = (type) => ({
  share_capital: 'Share Capital',
  share_deposits: 'Share Deposits',
}[type] || type)

const submit = () => {
  form.post(
    props.authUser?.role?.includes('member')
      ? route('members.accounts.deposit', {
          member: props.account.member.id,
          account: props.account.id,
        })
      : route('accounts.deposit', props.account.id),
    {
      onSuccess: () => showMessage('success', 'Deposit successful!'),
      onError: (errors) => showMessage('error', 'Deposit failed', errors),
    }
  )
}
</script>