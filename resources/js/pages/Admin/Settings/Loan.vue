<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    default_interest_rate: '',
    max_loan_amount: '',
    min_loan_amount: '',
    repayment_period: '',
    penalty_rate: '',
    loan_processing_fee: '',
});

function saveLoanSettings() {
    form.post(route('settings.loan.update'));
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'Loan' }]">
        <Head title="Loan Settings" />

        <div class="rounded-2xl bg-white p-6 shadow-md">
            <h1 class="mb-6 border-b pb-3 text-xl font-semibold text-gray-800 sm:text-2xl">Loan Settings</h1>

            <form @submit.prevent="saveLoanSettings" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Default Interest Rate (%)</label>
                    <input
                        v-model="form.default_interest_rate"
                        type="number"
                        step="0.1"
                        placeholder="e.g., 12.5"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400"
                    />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Minimum Loan Amount</label>
                        <input
                            v-model="form.min_loan_amount"
                            type="number"
                            placeholder="e.g., 5000"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Maximum Loan Amount</label>
                        <input
                            v-model="form.max_loan_amount"
                            type="number"
                            placeholder="e.g., 500000"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400"
                        />
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Repayment Period (Months)</label>
                        <input
                            v-model="form.repayment_period"
                            type="number"
                            placeholder="e.g., 12"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Penalty Rate (% per Month)</label>
                        <input
                            v-model="form.penalty_rate"
                            type="number"
                            step="0.1"
                            placeholder="e.g., 2"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Loan Processing Fee (KES)</label>
                    <input
                        v-model="form.loan_processing_fee"
                        type="number"
                        placeholder="e.g., 500"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400"
                    />
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        class="rounded-lg bg-gradient-to-r from-sky-600 to-orange-500 px-6 py-2 text-white shadow-md transition hover:opacity-90"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
