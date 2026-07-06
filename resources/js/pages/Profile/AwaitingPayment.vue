<template>
  <AppLayout>

    <Head title="Awaiting Payment" />

    <div class="py-16 mx-2 flex flex-col items-center text-center space-y-6">

      <!-- Info icon -->
      <div class="bg-yellow-100 dark:bg-yellow-900 p-4 sm:p-6 rounded-full">
        <svg class="w-10 sm:w-12 h-10 sm:h-12 text-yellow-500 dark:text-yellow-300" fill="none" stroke="currentColor"
          stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>

      <!-- Heading -->
      <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">
        Complete Your Membership Payment
      </h2>

      <p class="max-w-xl text-gray-600 dark:text-gray-400">
        Your membership has been approved. To fully activate your account, please make the minimum payments below.
      </p>

      <div class="mt-6 w-full max-w-md">

        <!-- Flash Messages -->
        <div v-if="showError" class="mb-4 bg-red-100 text-red-800 p-4 rounded-lg transition">
          {{ flash.error }}
        </div>

        <div v-if="showSuccess" class="mb-4 bg-green-100 text-green-800 p-4 rounded-lg transition">
          {{ flash.success }}
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
          <h3 class="text-lg font-semibold text-[#081642] mb-2">
            Membership Payment Details
          </h3>

          <ul class="text-sm space-y-1 mb-4">
            <li>• Registration Fee: <strong>Kshs. 2,500</strong></li>
            <li>• Minimum Share Capital: <strong>Kshs. 5,000</strong></li>
            <li>• Minimum Share Deposits: <strong>Kshs. 5,000</strong></li>
          </ul>

          <!-- Payment Method -->
          <div class="space-y-3">
            <label class="block text-sm font-medium text-gray-700">
              Payment Method
            </label>
            <select v-model="selectedPaymentMethod"
              class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-orange-500 focus:border-orange-500">
              <option value="">Select method</option>
              <option value="mpesa">Paybill (M-Pesa)</option>
              <option value="bank">Bank Deposit</option>
            </select>
          </div>

          <!-- MPESA -->
          <div v-if="selectedPaymentMethod === 'mpesa'" class="mt-4 bg-orange-50 p-3 rounded-md text-sm">
            <p><strong>Paybill Number:</strong> 400200</p>
            <p><strong>Account Number:</strong> 01***********00</p>
          </div>

          <!-- BANK -->
          <div v-if="selectedPaymentMethod === 'bank'" class="mt-4 bg-blue-50 p-3 rounded-md text-sm">
            <p><strong>Bank Name:</strong> Co-operative Bank</p>
            <p><strong>Account No:</strong> 01***********000</p>
          </div>

          <!-- Actions -->
          <div class="mt-6 flex justify-end gap-3">
            <Link :href="route('logout')" method="post" as="button"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
            Logout
            </Link>

            <!-- WAITING FOR ACTIVATION -->
            <button v-if="waitingForActivation" @click="finish" :disabled="finishLoading"
              class="px-4 py-2 text-sm font-medium text-white rounded-lg flex items-center gap-2" :class="finishLoading
          ? 'bg-blue-400 cursor-not-allowed'
          : 'bg-blue-600 hover:bg-blue-700'">
              <svg v-if="finishLoading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" />
                <path class="opacity-75" d="M4 12a8 8 0 018-8" />
              </svg>

              <span>{{ finishLoading ? 'Checking...' : 'Finish' }}</span>
            </button>

            <!-- NORMAL PAY BUTTON -->
            <button v-else @click="confirmPayment" :disabled="loading"
              class="px-4 py-2 text-sm font-medium text-white rounded-lg flex items-center gap-2" :class="loading
          ? 'bg-orange-400 cursor-not-allowed'
          : 'bg-orange-600 hover:bg-orange-700'">
              <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" />
                <path class="opacity-75" d="M4 12a8 8 0 018-8" />
              </svg>

              <span>{{ loading ? 'Checking...' : 'I’ve Paid' }}</span>
            </button>
          </div>

          <!-- Waiting Message -->
          <p v-if="waitingForActivation" class="mt-4 text-sm text-blue-600">
            ⏳ Payment verified. Waiting for account activation by SACCO admin.
          </p>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

const selectedPaymentMethod = ref('')
const loading = ref(false)
const finishLoading = ref(false)
const waitingForActivation = ref(false)

const page = usePage()
const flash = computed(() => page.props.flash || {})

const showSuccess = ref(false)
const showError = ref(false)

// Watch flash messages and auto-hide after 5s
watch(
  () => flash.value,
  (val) => {
    showError.value = false
    showSuccess.value = false

    if (val?.error) {
      showError.value = true
      setTimeout(() => (showError.value = false), 5000)
    }

    if (val?.success) {
      showSuccess.value = true

      // Only set waiting if activated=false
      if (val?.activated === false) {
        waitingForActivation.value = true
      } else if (val?.activated === true) {
        waitingForActivation.value = false
      }

      setTimeout(() => (showSuccess.value = false), 5000)
    }
  },
  { immediate: true }
)

// Confirm payment
const confirmPayment = () => {
  loading.value = true
  router.post(route('members.confirm-payment'), {}, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      // Force flash update manually
      const flashProps = page.props.flash || {}

      if (flashProps.success) showSuccess.value = true
      if (flashProps.error) showError.value = true

      // If flash message contains "Click Finish" → mark waiting
      if (flashProps.success?.includes('Click Finish')) {
        waitingForActivation.value = true
      }
    },
    onFinish: () => {
      loading.value = false
    }
  })
}


// Check activation
const finish = () => {
  finishLoading.value = true

  router.post(route('members.check-activation'), {}, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      const flashProps = page.props.flash || {}

      // Show flash messages
      if (flashProps.success) showSuccess.value = true
      if (flashProps.error) showError.value = true

      // Check if account is active
      if (flashProps.activated) {
        waitingForActivation.value = false
        // Optionally redirect or show success
        // router.visit(route('dashboard'))
      } else {
        waitingForActivation.value = true
      }
    },
    onFinish: () => {
      finishLoading.value = false
    }
  })
}

</script>
