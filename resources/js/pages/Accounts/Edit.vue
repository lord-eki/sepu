<template>
    <AppLayout :title="`Edit Account ${account.account_number}`">


        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center pb-6">
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            Edit Account
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ account.account_number }} - {{ account.member.first_name }} {{ account.member.last_name
                            }}
                        </p>
                    </div>
                    <Link :href="route('accounts.show', account.id)"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Back to Account
                    </Link>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Current Account Information -->
                            <div class="bg-gray-50 p-4 rounded-md">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Current Account Information</h3>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium">Account Number:</span> {{ account.account_number }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Member:</span> {{ account.member.first_name }} {{
                                        account.member.last_name }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Current Type:</span> {{
                                        getAccountTypeLabel(account.account_type) }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Current Balance:</span> {{
                                        formatCurrency(account.balance) }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Status:</span>
                                        <span :class="account.is_active ? 'text-green-600' : 'text-red-600'">
                                            {{ account.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium">Created:</span> {{ formatDate(account.created_at) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Account Type -->
                            <div>
                                <label for="account_type" class="block text-sm font-medium text-gray-700">
                                    Account Type *
                                </label>
                                <select id="account_type" v-model="form.account_type"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                    <option v-for="(label, value) in accountTypes" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                                <div v-if="form.errors.account_type" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.account_type }}
                                </div>
                            </div>

                            <!-- Account Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Account Status
                                </label>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input v-model="form.is_active" type="checkbox"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-600">Account is active</span>
                                    </label>
                                </div>
                                <div v-if="form.errors.is_active" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.is_active }}
                                </div>
                            </div>

                            <!-- Warning for Account Type Change -->
                            <div v-if="form.account_type !== account.account_type"
                                class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">
                                            Account Type Change Warning
                                        </h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>
                                                You are changing the account type from <strong>{{
                                                    getAccountTypeLabel(account.account_type) }}</strong>
                                                to <strong>{{ getAccountTypeLabel(form.account_type) }}</strong>.
                                                This change may affect account functionality and reporting. Please
                                                ensure this change is intentional.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning for Status Change -->
                            <div v-if="form.is_active !== account.is_active && !form.is_active"
                                class="bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">
                                            Account Deactivation Warning
                                        </h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <p>
                                                You are about to deactivate this account. Deactivated accounts cannot
                                                perform transactions
                                                and will be excluded from most operations. This action can be reversed
                                                later if needed.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Type Description -->
                            <div v-if="form.account_type !== account.account_type" class="bg-blue-50 p-4 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm text-blue-700">
                                            <strong>{{ getAccountTypeLabel(form.account_type) }}:</strong>
                                            {{ getAccountTypeDescription(form.account_type) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-between pt-6 border-t">
                                <Link :href="route('accounts.show', account.id)"
                                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                                </Link>
                                <button type="submit" :disabled="form.processing || !form.isDirty"
                                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    {{ form.processing ? 'Updating...' : 'Update Account' }}
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
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
    account: Object,
    accountTypes: Object,
})

const form = useForm({
    account_type: props.account.account_type,
    is_active: props.account.is_active,
})

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(amount || 0)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const getAccountTypeLabel = (type) => {
    const labels = {
        'savings': 'Savings Account',
        'shares': 'Shares Account',
        'deposits': 'Deposits Account'
    }
    return labels[type] || type
}

const getAccountTypeDescription = (type) => {
    const descriptions = {
        savings: 'A savings account allows members to save money and earn interest on their deposits.',
        shares: 'A shares account represents ownership in the cooperative and entitles members to dividends.',
        deposits: 'A deposits account offers higher interest rates for fixed-term investments.'
    }
    return descriptions[type] || ''
}

const submit = () => {
    form.put(route('accounts.update', props.account.id))
}
</script>