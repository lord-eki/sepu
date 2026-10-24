<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    fiscal_year_start: '2025-01-01',
    tax_rate: '',
    default_account: '',
    currency_format: 'KES',
    rounding: 'nearest',
});

function saveFinancialSettings() {
    // route('settings.financial.update')
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'Financial' }]">
        <Head title="Financial Settings" />

        <div class="rounded-2xl bg-white p-6 shadow-md">
            <h1 class="mb-6 border-b pb-3 text-xl font-semibold text-gray-800 sm:text-2xl">Financial Settings</h1>

            <form @submit.prevent="saveFinancialSettings" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fiscal Year Start Date</label>
                    <input
                        v-model="form.fiscal_year_start"
                        type="date"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-400"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Default Currency</label>
                    <select
                        v-model="form.currency_format"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-400"
                    >
                        <option value="KES">KES (Kenyan Shilling)</option>
                        <option value="USD">USD (US Dollar)</option>
                        <option value="EUR">EUR (Euro)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tax Rate (%)</label>
                    <input
                        v-model="form.tax_rate"
                        type="number"
                        step="0.01"
                        placeholder="e.g., 5"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-400"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Default Account</label>
                    <input
                        v-model="form.default_account"
                        type="text"
                        placeholder="e.g., Main SACCO Account"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-400"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Rounding Method</label>
                    <select
                        v-model="form.rounding"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-400"
                    >
                        <option value="nearest">Nearest Whole Number</option>
                        <option value="up">Round Up</option>
                        <option value="down">Round Down</option>
                    </select>
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
