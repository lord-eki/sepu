<template>
    <AppLayout>
        <Head title="Awaiting Payment" />

        <div class="mx-2 flex flex-col items-center space-y-6 py-16 text-center">
            <!-- Info icon -->
            <div class="rounded-full bg-yellow-100 p-4 sm:p-6 dark:bg-yellow-900">
                <svg
                    class="h-10 w-10 text-yellow-500 sm:h-12 sm:w-12 dark:text-yellow-300"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- Heading -->
            <h2 class="text-xl font-bold text-gray-800 sm:text-2xl dark:text-gray-100">Complete Your Membership Payment</h2>

            <p class="max-w-xl text-gray-600 dark:text-gray-400">
                Your membership has been approved. To fully activate your account, please make the minimum payments below.
            </p>

            <div class="mt-6 w-full max-w-md">
                <!-- Flash Messages -->
                <div v-if="showError" class="mb-4 rounded-lg bg-red-100 p-4 text-red-800 transition">
                    {{ flash.error }}
                </div>

                <div v-if="showSuccess" class="mb-4 rounded-lg bg-green-100 p-4 text-green-800 transition">
                    {{ flash.success }}
                </div>

                <!-- Card -->
                <div class="rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="mb-2 text-lg font-semibold text-[#081642]">Membership Payment Details</h3>

                    <ul class="mb-4 space-y-1 text-sm">
                        <li>• Registration Fee: <strong>Kshs. 2,500</strong></li>
                        <li>• Minimum Share Capital: <strong>Kshs. 5,000</strong></li>
                        <li>• Minimum Share Deposits: <strong>Kshs. 5,000</strong></li>
                    </ul>

                    <!-- Payment Method -->
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700"> Payment Method </label>
                        <select
                            v-model="selectedPaymentMethod"
                            class="w-full rounded-lg border border-gray-300 p-2 text-sm focus:border-orange-500 focus:ring-orange-500"
                        >
                            <option value="">Select method</option>
                            <option value="mpesa">Paybill (M-Pesa)</option>
                            <option value="bank">Bank Deposit</option>
                        </select>
                    </div>

                    <!-- MPESA -->
                    <div v-if="selectedPaymentMethod === 'mpesa'" class="mt-4 rounded-md bg-orange-50 p-3 text-sm">
                        <p><strong>Paybill Number:</strong> 400200</p>
                        <p><strong>Account Number:</strong> 01***********00</p>
                    </div>

                    <!-- BANK -->
                    <div v-if="selectedPaymentMethod === 'bank'" class="mt-4 rounded-md bg-blue-50 p-3 text-sm">
                        <p><strong>Bank Name:</strong> Co-operative Bank</p>
                        <p><strong>Account No:</strong> 01***********000</p>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-3">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                        >
                            Logout
                        </Link>

                        <!-- WAITING FOR ACTIVATION -->
                        <button
                            v-if="waitingForActivation"
                            @click="finish"
                            :disabled="finishLoading"
                            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-white"
                            :class="finishLoading ? 'cursor-not-allowed bg-blue-400' : 'bg-blue-600 hover:bg-blue-700'"
                        >
                            <svg
                                v-if="finishLoading"
                                class="h-4 w-4 animate-spin"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" />
                                <path class="opacity-75" d="M4 12a8 8 0 018-8" />
                            </svg>

                            <span>{{ finishLoading ? 'Checking...' : 'Finish' }}</span>
                        </button>

                        <!-- NORMAL PAY BUTTON -->
                        <button
                            v-else
                            @click="confirmPayment"
                            :disabled="loading"
                            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-white"
                            :class="loading ? 'cursor-not-allowed bg-orange-400' : 'bg-orange-600 hover:bg-orange-700'"
                        >
                            <svg v-if="loading" class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const selectedPaymentMethod = ref('');
const loading = ref(false);
const finishLoading = ref(false);
const waitingForActivation = ref(false);

const page = usePage();
const flash = computed(() => page.props.flash || {});

const showSuccess = ref(false);
const showError = ref(false);

// Watch flash messages and auto-hide after 5s
watch(
    () => flash.value,
    (val) => {
        showError.value = false;
        showSuccess.value = false;

        if (val?.error) {
            showError.value = true;
            setTimeout(() => (showError.value = false), 5000);
        }

        if (val?.success) {
            showSuccess.value = true;

            // Only set waiting if activated=false
            if (val?.activated === false) {
                waitingForActivation.value = true;
            } else if (val?.activated === true) {
                waitingForActivation.value = false;
            }

            setTimeout(() => (showSuccess.value = false), 5000);
        }
    },
    { immediate: true },
);

// Confirm payment
const confirmPayment = () => {
    loading.value = true;
    router.post(
        route('members.confirm-payment'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                // Force flash update manually
                const flashProps = page.props.flash || {};

                if (flashProps.success) showSuccess.value = true;
                if (flashProps.error) showError.value = true;

                // If flash message contains "Click Finish" → mark waiting
                if (flashProps.success?.includes('Click Finish')) {
                    waitingForActivation.value = true;
                }
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

// Check activation
const finish = () => {
    finishLoading.value = true;

    router.post(
        route('members.check-activation'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                const flashProps = page.props.flash || {};

                // Show flash messages
                if (flashProps.success) showSuccess.value = true;
                if (flashProps.error) showError.value = true;

                // Check if account is active
                if (flashProps.activated) {
                    waitingForActivation.value = false;
                    // Optionally redirect or show success
                    // router.visit(route('dashboard'))
                } else {
                    waitingForActivation.value = true;
                }
            },
            onFinish: () => {
                finishLoading.value = false;
            },
        },
    );
};
</script>
