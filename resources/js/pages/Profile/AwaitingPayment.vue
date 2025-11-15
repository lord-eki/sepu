<template>
    <AppLayout>
      <Head title="Awaiting Payment" />
  
      <div class="py-16 mx-2 flex flex-col items-center text-center space-y-6">
        <div class="bg-yellow-100 dark:bg-yellow-900 p-4 sm:p-6 rounded-full">
          <svg
            class="w-10 sm:w-12 h-10 sm:h-12 text-yellow-500 dark:text-yellow-300"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
  
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">
          Complete Your Membership Payment
        </h2>
  
        <p class="max-w-xl text-gray-600 dark:text-gray-400">
          Your membership has been approved. To fully activate your account, please make the minimum payments below.
        </p>
  
        <!-- Payment Modal Section -->
        <div class="mt-6 w-full max-w-md">
          <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-[#081642] mb-2">Membership Payment Details</h3>
  
            <ul class="text-sm space-y-1 mb-4">
              <li>• Registration Fee: <strong>Kshs. 2,500</strong></li>
              <li>• Minimum Share Capital: <strong>Kshs. 5,000</strong></li>
              <li>• Minimum Share Deposits: <strong>Kshs. 5,000</strong></li>
            </ul>
  
            <div class="space-y-3">
              <label class="block text-sm font-medium text-gray-700">Payment Method</label>
              <select
                v-model="selectedPaymentMethod"
                class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-orange-500 focus:border-orange-500"
              >
                <option value="">Select method</option>
                <option value="mpesa">Paybill (M-Pesa)</option>
                <option value="bank">Bank Deposit</option>
              </select>
            </div>
  
            <div v-if="selectedPaymentMethod === 'mpesa'" class="mt-4 bg-orange-50 p-3 rounded-md text-sm">
              <p><strong>Paybill Number:</strong> 400200</p>
              <p><strong>Account Number:</strong> 01120040146200</p>
            </div>
  
            <div v-if="selectedPaymentMethod === 'bank'" class="mt-4 bg-blue-50 p-3 rounded-md text-sm">
              <p><strong>Bank Name:</strong> Co-operative Bank</p>
              <p><strong>Account No:</strong> 01120040146200</p>
            </div>
  
            <div class="mt-6 flex justify-end gap-3">
              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
              >
                Logout
              </Link>
              <button
                @click="confirmPayment"
                class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg"
              >
                I’ve Paid
              </button>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { Head, Link } from '@inertiajs/vue3'
  
  const selectedPaymentMethod = ref('')
  
  const confirmPayment = () => {
    alert('Payment confirmation submitted!') 
    // Implement API call to update payment status
  }
  </script>
  