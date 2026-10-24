<template>
    <AppLayout
        :breadcrumbs="[
            { title: isMemberRole ? 'My Accounts' : 'Accounts', href: isMemberRole ? route('my-accounts') : route('accounts.index') },
            { title: 'Withdraw' },
        ]"
    >
        <div class="pb-5">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-6">
                            <div>
                                <h2 class="text-lg leading-tight font-semibold text-gray-800 sm:text-xl">Make Withdrawal</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ account.account_number }} - {{ account.member.first_name }} {{ account.member.last_name }}
                                </p>
                            </div>
                            <Link
                                :href="isMemberRole ? route('my-accounts') : route('accounts.index')"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-900"
                            >
                                Back<span class="max-sm:hidden">&nbsp;to account</span>
                            </Link>
                        </div>

                        <!-- Account Summary -->
                        <div class="mb-6 rounded-md bg-gray-50 p-4">
                            <h3 class="mb-4 text-lg font-medium text-gray-900">Account Summary</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                                <div>
                                    <span class="font-medium">Account Type:</span>
                                    <div class="mt-1">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="getAccountTypeBadgeClass(account.account_type)"
                                        >
                                            {{ getAccountTypeLabel(account.account_type) }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <span class="font-medium">Current Balance:</span>
                                    <div class="mt-1 text-base font-semibold text-green-900">
                                        {{ formatCurrency(account.balance) }}
                                    </div>
                                </div>
                                <div>
                                    <span class="font-medium">Available Balance:</span>
                                    <div class="mt-1 text-base font-semibold text-blue-900">
                                        {{ formatCurrency(account.available_balance) }}
                                    </div>
                                </div>
                                <div>
                                    <span class="font-medium">Status:</span>
                                    <div class="mt-1">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        >
                                            {{ account.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Balance Warning -->
                        <div v-if="account.balance <= 0" class="mb-6 rounded-md border border-red-200 bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Insufficient Balance</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>This account has insufficient balance for withdrawals. Please make a deposit first.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Amount -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700"> Withdrawal Amount (KES) * </label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">KES</span>
                                    </div>
                                    <input
                                        id="amount"
                                        v-model.number="form.amount"
                                        type="number"
                                        step="0.01"
                                        min="1"
                                        :max="account.available_balance"
                                        class="block w-full rounded-md border border-gray-300 p-2 pr-12 pl-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.amount > account.available_balance }"
                                        placeholder="0.00"
                                        required
                                        :disabled="account.balance <= 0"
                                    />
                                </div>
                                <div v-if="form.errors.amount" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.amount }}
                                </div>
                                <div v-if="form.amount > account.available_balance" class="mt-2 text-sm text-red-600">
                                    Insufficient balance. Maximum withdrawal: {{ formatCurrency(account.available_balance) }}
                                </div>
                                <div v-if="form.amount > 0 && form.amount <= account.available_balance" class="mt-2 text-sm text-gray-600">
                                    New balance will be:
                                    <span class="font-medium text-blue-600">{{ formatCurrency(account.balance - form.amount) }}</span>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">Maximum withdrawal: {{ formatCurrency(account.available_balance) }}</div>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700"> Payment Method * </label>
                                <select
                                    id="payment_method"
                                    v-model="form.payment_method"
                                    class="mt-1 block w-full rounded-md border border-gray-300 p-2 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                                    required
                                    :disabled="account.balance <= 0"
                                >
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
                                    <span v-if="form.payment_method === 'mobile_money'" class="text-xs text-gray-500">(Phone Number)</span>
                                    <span v-if="form.payment_method === 'bank_transfer'" class="text-xs text-gray-500">(Account Number)</span>
                                    <span v-if="form.payment_method === 'cheque'" class="text-xs text-gray-500">(Payee Name)</span>
                                </label>
                                <input
                                    id="payment_reference"
                                    v-model="form.payment_reference"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :placeholder="getPaymentReferencePlaceholder(form.payment_method)"
                                />
                                <div v-if="form.errors.payment_reference" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.payment_reference }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700"> Description </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Optional description for this withdrawal"
                                    :disabled="account.balance <= 0"
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Transaction Summary -->
                            <div
                                v-if="form.amount > 0 && form.amount <= account.available_balance"
                                class="rounded-md border border-red-200 bg-red-50 p-4"
                            >
                                <h3 class="mb-3 text-sm font-medium text-red-800">Transaction Summary</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Account:</span>
                                        <span class="font-medium">{{ account.account_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Current Balance:</span>
                                        <span class="font-medium">{{ formatCurrency(account.balance) }}</span>
                                    </div>
                                    <div class="flex justify-between text-red-700">
                                        <span>Withdrawal Amount:</span>
                                        <span class="font-medium">-{{ formatCurrency(form.amount) }}</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2 text-base font-semibold text-red-900">
                                        <span>New Balance:</span>
                                        <span>{{ formatCurrency(account.balance - form.amount) }}</span>
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
                            <div v-if="account.account_type === 'shares'" class="rounded-md border border-yellow-200 bg-yellow-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Share Sale Warning</h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>
                                                This withdrawal will be recorded as a share sale. Selling shares reduces your ownership in the
                                                cooperative and may affect your dividend entitlements. Please ensure this action is intentional.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Minimum Balance Warning -->
                            <div
                                v-if="form.amount > 0 && account.balance - form.amount < 100"
                                class="rounded-md border border-orange-200 bg-orange-50 p-4"
                            >
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-orange-800">Low Balance Warning</h3>
                                        <div class="mt-2 text-sm text-orange-700">
                                            <p>
                                                This withdrawal will leave your account with a low balance. Consider maintaining a minimum balance for
                                                optimal account management.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-between border-t pt-6">
                                <Link
                                    :href="route('accounts.show', account.id)"
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="
                                        form.processing ||
                                        !form.amount ||
                                        form.amount <= 0 ||
                                        form.amount > account.available_balance ||
                                        account.balance <= 0
                                    "
                                    class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <svg
                                        v-if="form.processing"
                                        class="mr-3 -ml-1 h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    {{ form.processing ? 'Processing...' : 'Process Withdrawal' }}
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
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    account: Object,
    paymentMethods: Object,
});

const form = useForm({
    amount: '',
    payment_method: '',
    payment_reference: '',
    description: '',
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(amount || 0);
};

const getAccountTypeLabel = (type) => {
    const labels = {
        savings: 'Savings Account',
        shares: 'Shares Account',
        deposits: 'Deposits Account',
    };
    return labels[type] || type;
};

const getAccountTypeBadgeClass = (type) => {
    const classes = {
        savings: 'bg-blue-100 text-blue-800',
        shares: 'bg-purple-100 text-purple-800',
        deposits: 'bg-green-100 text-green-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};

const getPaymentReferencePlaceholder = (method) => {
    const placeholders = {
        mobile_money: 'e.g., 0712345678',
        bank_transfer: 'e.g., 1234567890',
        cheque: 'e.g., John Doe',
    };
    return placeholders[method] || 'Enter reference';
};

const submit = () => {
    form.post(route('accounts.withdrawal', props.account.id));
};
</script>
