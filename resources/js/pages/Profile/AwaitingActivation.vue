<template>
  <AppLayout>
    <Head title="Complete Profile" />

   <!-- Flash messages -->
   <div class="max-w-2xl mx-auto mt-2 sm:mt-6 px-4">
      <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-if="flashMessage"
          :class="[
            flashType === 'success'
              ? 'bg-green-100 text-green-800 border border-green-300'
              : 'bg-red-100 text-red-800 border border-red-300',
            'relative w-full px-6 py-3 rounded-lg mb-4 flex items-center shadow-sm'
          ]"
        >
          <span class="flex-1">{{ flashMessage }}</span>
          <button
            type="button"
            class="ml-3 text-gray-500 hover:text-gray-700"
            @click="flashMessage = null"
          >
            ✕
          </button>
        </div>
      </transition>
    </div>

    <div class="py-16 mx-2 flex flex-col items-center text-center space-y-6">
      <!-- Icon -->
      <div class="bg-yellow-100 dark:bg-yellow-900 p-4 sm:p-6 rounded-full">
        <svg
          class="w-12 sm:w-16 h-12 sm:h-16 text-yellow-500 dark:text-yellow-300"
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

      <!-- Title -->
      <h2
        class="max-sm:px-6 text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100"
      >
        Your Profile is Awaiting Approval
      </h2>

      <!-- Message -->
      <p
        class="max-sm:text-sm max-w-xl text-gray-600 dark:text-gray-400"
      >
        Thank you for completing your registration.  
        Admin is reviewing your details. Once your account is approved, you will be able to access all features.
      </p>

      <!-- Estimated time -->
      <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
        This process usually takes
        <span class="font-medium">24–48 hours</span>.
      </p>

      <!-- Action button -->
      <div class="pt-6">
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="px-5 py-2 hover:cursor-pointer rounded-xl bg-blue-800 text-white hover:bg-blue-700 
                dark:bg-gray-200 dark:text-gray-900 dark:hover:bg-gray-300 transition"
        >
          Logout
        </Link>
      </div>
    </div>

    <div>
      <!-- Payment Modal -->
        <transition name="fade" class="max-sm:px-2">
          <div
            v-if="showPaymentModal"
            class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"
          >
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
              <h3 class="text-lg font-semibold text-[#081642] mb-2">Complete Membership Payment</h3>
              <p class="text-sm text-gray-600 mb-4">
                To proceed with SEPU Sacco membership, kindly make the following minimum payments:
              </p>

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
                <button
                  @click="showPaymentModal = false"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                >
                  Cancel
                </button>
                <button
                  @click="confirmPayment"
                  class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg"
                >
                  I’ve Paid
                </button>
              </div>
            </div>
          </div>
        </transition>

    
    </div>
    
  </AppLayout>
</template>

<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'

const page = usePage()

// local flash state
const flash = ref({ ...page.props.flash })
const flashMessage = computed(() => flash.value.success || flash.value.error)
const flashType = computed(() => (flash.value.success ? 'success' : 'error'))

// watch for changes to page.props.flash
watch(
  () => page.props.flash,
  (newFlash) => {
    flash.value = { ...newFlash }

    if (flash.value.success || flash.value.error) {
      setTimeout(() => {
        flash.value = {}
      }, 5000) 
    }
  },
  { immediate: true }
)



// const showPaymentModal = ref(false)

// const submit = () => {
//   // Example eligibility check logic
//   const registrationFeePaid = false
//   const shareCapitalPaid = false
//   const shareDepositsPaid = false

//   // If all minimums met, complete profile
//   if (registrationFeePaid && shareCapitalPaid && shareDepositsPaid) {
//     form.post(route('profile.complete.store'), {
//       forceFormData: true,
//     })
//   } else {
//     // Otherwise show payment modal
//     showPaymentModal.value = true
//   }
// }
</script>
