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
        :class="successMessage ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900'"
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
          <button @click="() => { successMessage = null; errorMessages = null }" class="ml-3 text-gray-400 hover:text-gray-300">
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

    <!-- Main Form -->
    <div class="py-10 px-[5%] bg-[#F9FAFB]">
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
              <p class="mt-1 text-lg font-semibold text-blue-900">
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

        <!-- Deposit Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Amount -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Deposit Amount (KES) *</label>
            <input
              v-model.number="form.amount"
              type="number"
              min="5000"
              placeholder="Enter amount (min 5000)"
              class="mt-1 w-full rounded-md border border-gray-300 p-2 focus:border-[#0B2B40] focus:ring-[#0B2B40]"
              required
            />
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
                <li><strong>Minimum Deposit:</strong> Kshs. 5,000</li>
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
                  <li>Select <strong>“Bank Transfer”</strong> or <strong>“Send Money to Account”</strong>.</li>
                  <li>Enter the above SACCO account details carefully.</li>
                  <li>Enter your desired deposit amount (minimum Ksh 5,000).</li>
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
              <label for="payment_reference" class="block                                                                              text-sm font-medium text-[#0B2B40]">
                Enter {{
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
                class="mt-1 w-full bg-white border border-gray-300 rounded-md p-2 focus:border-[#0B2B40] focus:ring-[#0B2B40]"
                required
              />
            </div>
          </div>



          <!-- STK Push Section -->
          <!-- <div v-if="form.payment_method === 'mobile_money'">
            <h3 class="text-base font-semibold text-blue-800">Deposit via M-Pesa Express (STK Push)</h3>
            <input
              v-model="form.stk_phone"
              type="tel"
              placeholder="2547XXXXXXXX"
              class="mt-2 w-full border p-2 rounded-md"
            />
            <button
              type="button"
              class="mt-3 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
              @click="initiateStkPush"
            >
              Initiate Payment
            </button>
          </div>
          -->

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              placeholder="Optional note for this deposit"
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
                form.amount < 5000 ||
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
  stk_phone: '',
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
  savings: 'Savings Account',
  shares: 'Shares Account',
  deposits: 'Deposits Account',
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
      onSuccess: () => {
          const flash = page.props.flash
          if (flash?.success) showMessage('success', flash.success)
        },

      onError: (errors) => showMessage('error', 'Deposit failed', errors),
    }
  )
}
</script>
