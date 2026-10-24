<template>
    <AppLayout :breadcrumbs="[{ title: 'Accounts', href: route('accounts.index') }, { title: 'Share Transfer ' + `${account.account_number}` }]">
        <div class="py-2 sm:py-5">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between pb-6">
                            <div>
                                <h2 class="text-lg leading-tight font-semibold text-gray-800 sm:text-xl">Transfer Shares</h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ account.account_number }} - {{ account.member.first_name }} {{ account.member.last_name }}
                                </p>
                            </div>
                            <Link
                                :href="route('accounts.show', account.id)"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-900"
                            >
                                Back<span class="max-sm:hidden">&nbsp;to Account</span>
                            </Link>
                        </div>

                        <!-- Account Summary -->
                        <div class="mb-6 rounded-md bg-purple-50 p-4">
                            <h3 class="mb-4 text-lg font-medium text-purple-900">Shares Account Summary</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                                <div>
                                    <span class="font-medium">Account Number:</span>
                                    <div class="mt-1 text-base font-semibold text-purple-700">
                                        {{ account.account_number }}
                                    </div>
                                </div>
                                <div>
                                    <span class="font-medium">Current Balance:</span>
                                    <div class="mt-1 text-base font-semibold text-green-600">
                                        {{ formatCurrency(account.balance) }}
                                    </div>
                                </div>
                                <div>
                                    <span class="font-medium">Available for Transfer:</span>
                                    <div class="mt-1 text-base font-semibold text-blue-600">
                                        {{ formatCurrency(account.available_balance) }}
                                    </div>
                                </div>
                                <div>
                                    <span class="font-medium">Share Value:</span>
                                    <div class="mt-1 text-base font-semibold text-purple-600">KES 100 per share</div>
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
                                    <h3 class="text-sm font-medium text-red-800">No Shares Available</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>This account has no shares available for transfer. Please purchase shares first.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Share Transfer Info -->
                        <div class="mb-6 rounded-md border border-blue-200 bg-blue-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Share Capital Transfer Information</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <p>
                                            Share capital transfers allow exiting members to transfer their ownership stake to other active members.
                                            This is typically done when a member is leaving the SACCO. The receiving member will pay for these shares
                                            and gain the associated ownership rights, voting power, and dividend entitlements.
                                        </p>
                                        <p class="mt-2 font-medium">
                                            Note: This is for Share Capital accounts only. Share Deposits cannot be transferred.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Recipient Selection -->
                            <div>
                                <label for="recipient_member_id" class="block text-sm font-medium text-gray-700"> Transfer To (Member) * </label>
                                <select
                                    id="recipient_member_id"
                                    v-model="form.recipient_member_id"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                                    required
                                    :disabled="account.balance <= 0"
                                >
                                    <option value="">Select a member to transfer shares to</option>
                                    <option v-for="member in members" :key="member.id" :value="member.id">
                                        {{ member.name }} ({{ member.membership_id }})
                                    </option>
                                </select>
                                <div v-if="form.errors.recipient_member_id" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.recipient_member_id }}
                                </div>
                            </div>

                            <!-- Selected Member Info -->
                            <div v-if="selectedMember" class="rounded-md bg-gray-50 p-4">
                                <h3 class="mb-2 text-lg font-medium text-gray-900">Transfer Recipient</h3>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div><span class="font-medium">Name:</span> {{ selectedMember.name }}</div>
                                    <div><span class="font-medium">Membership ID:</span> {{ selectedMember.membership_id }}</div>
                                </div>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700"> Share Value to Transfer (KES) * </label>
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">KES</span>
                                    </div>
                                    <input
                                        id="amount"
                                        v-model.number="form.amount"
                                        type="number"
                                        step="100"
                                        min="100"
                                        :max="account.available_balance"
                                        class="block w-full rounded-md border-gray-300 p-2 pr-12 pl-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.amount > account.available_balance || form.amount % 100 !== 0 }"
                                        placeholder="0.00"
                                        required
                                        :disabled="account.balance <= 0"
                                    />
                                </div>
                                <div v-if="form.errors.amount" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.amount }}
                                </div>
                                <div v-if="form.amount > account.available_balance" class="mt-2 text-sm text-red-600">
                                    Insufficient balance. Maximum transfer: {{ formatCurrency(account.available_balance) }}
                                </div>
                                <div v-if="form.amount > 0 && form.amount % 100 !== 0" class="mt-2 text-sm text-red-600">
                                    Share transfers must be in multiples of KES 100 (one share = KES 100)
                                </div>
                                <div
                                    v-if="form.amount > 0 && form.amount <= account.available_balance && form.amount % 100 === 0"
                                    class="mt-2 text-sm text-gray-600"
                                >
                                    Transferring {{ form.amount / 100 }} share(s) worth {{ formatCurrency(form.amount) }}
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    Maximum transfer: {{ formatCurrency(account.available_balance) }} ({{
                                        Math.floor(account.available_balance / 100)
                                    }}
                                    shares)
                                </div>
                            </div>

                            <!-- Transfer Reason -->
                            <div>
                                <label for="transfer_reason" class="block text-sm font-medium text-gray-700"> Transfer Reason * </label>
                                <select
                                    id="transfer_reason"
                                    v-model="form.transfer_reason"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none"
                                    required
                                    :disabled="account.balance <= 0"
                                >
                                    <option value="">Select transfer reason</option>
                                    <option value="gift">Gift to Family/Friend</option>
                                    <option value="inheritance">Inheritance</option>
                                    <option value="business_transfer">Business Transfer</option>
                                    <option value="loan_security">Loan Security</option>
                                    <option value="other">Other</option>
                                </select>
                                <div v-if="form.errors.transfer_reason" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.transfer_reason }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">
                                    Additional Notes
                                    <span v-if="form.transfer_reason === 'other'" class="text-red-500">*</span>
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 p-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Enter additional details about this transfer"
                                    :disabled="account.balance <= 0"
                                    :required="form.transfer_reason === 'other'"
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Transaction Summary -->
                            <div v-if="isValidTransfer" class="rounded-md border border-purple-200 bg-purple-50 p-4">
                                <h3 class="mb-3 text-sm font-medium text-purple-800">Transfer Summary</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>From Account:</span>
                                        <span class="font-medium">{{ account.account_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>To Member:</span>
                                        <span class="font-medium">{{ selectedMember?.name }} ({{ selectedMember?.membership_id }})</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Current Balance:</span>
                                        <span class="font-medium">{{ formatCurrency(account.balance) }}</span>
                                    </div>
                                    <div class="flex justify-between text-purple-700">
                                        <span>Transfer Amount:</span>
                                        <span class="font-medium">{{ formatCurrency(form.amount) }}</span>
                                    </div>
                                    <div class="flex justify-between text-purple-700">
                                        <span>Shares to Transfer:</span>
                                        <span class="font-medium">{{ form.amount / 100 }} share(s)</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-2 text-base font-semibold text-purple-900">
                                        <span>Remaining Balance:</span>
                                        <span>{{ formatCurrency(account.balance - form.amount) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Transfer Reason:</span>
                                        <span class="font-medium">{{ getTransferReasonLabel(form.transfer_reason) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Important Notice -->
                            <div v-if="isValidTransfer" class="rounded-md border border-yellow-200 bg-yellow-50 p-4">
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
                                        <h3 class="text-sm font-medium text-yellow-800">Important Notice</h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>
                                                This share transfer is irreversible once completed. The recipient will gain all rights associated with
                                                these shares, including voting rights and dividend entitlements. Please ensure all details are correct
                                                before proceeding.
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
                                    :disabled="!isValidTransfer || form.processing || account.balance <= 0"
                                    class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
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
                                    {{ form.processing ? 'Transferring...' : 'Transfer Shares' }}
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
import { computed } from 'vue';

const props = defineProps({
    account: Object,
    members: Array,
});

const form = useForm({
    recipient_member_id: '',
    amount: '',
    transfer_reason: '',
    description: '',
});

const selectedMember = computed(() => {
    if (!form.recipient_member_id) return null;
    return props.members.find((member) => member.id == form.recipient_member_id);
});

const isValidTransfer = computed(() => {
    return (
        form.amount > 0 &&
        form.amount <= props.account.available_balance &&
        form.amount % 100 === 0 &&
        form.recipient_member_id &&
        form.transfer_reason &&
        (form.transfer_reason !== 'other' || form.description)
    );
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(amount || 0);
};

const getTransferReasonLabel = (reason) => {
    const labels = {
        gift: 'Gift to Family/Friend',
        inheritance: 'Inheritance',
        business_transfer: 'Business Transfer',
        loan_security: 'Loan Security',
        other: 'Other',
    };
    return labels[reason] || reason;
};

const submit = () => {
    form.post(route('accounts.share-transfer', props.account.id));
};
</script>
