<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    parentAccounts: Array,
    accountTypes: Object,
    accountCategories: Object
})

const form = useForm({
    account_code: '',
    account_name: '',
    account_type: '',
    account_category: '',
    normal_balance: 'debit',
    parent_account_id: null,
    description: '',
    opening_balance: 0,
    is_active: true
})

function submit() {
    form.post(route('chart-of-accounts.store'))
}
</script>

<template>
<AppLayout :breadcrumbs="[{ title: 'Finance', href: '/finance' }, { title: 'Chart of Accounts', href: route('chart-of-accounts.index') }, { title: 'Create', href: route('chart-of-accounts.create') }]">
    <Head title="Create Account" />

    <div class="p-6 max-w-3xl mx-auto space-y-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-dark-blue">Create Chart of Account</h1>
            <Link :href="route('chart-of-accounts.index')" class="flex items-center gap-2 text-gray-600 hover:text-dark-blue">
                <ArrowLeft class="w-4 h-4" />
                Back
            </Link>
        </div>

        <!-- FORM CARD -->
        <form @submit.prevent="submit" class="bg-white shadow-lg rounded-xl p-6 space-y-6">

            <!-- ACCOUNT CODE -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700">Account Code</label>
                <input v-model="form.account_code" type="text"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none" />
                <div v-if="form.errors.account_code" class="text-red-500 text-sm mt-1">{{ form.errors.account_code }}</div>
            </div>

            <!-- ACCOUNT NAME -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700">Account Name</label>
                <input v-model="form.account_name" type="text"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none" />
                <div v-if="form.errors.account_name" class="text-red-500 text-sm mt-1">{{ form.errors.account_name }}</div>
            </div>

            <!-- ACCOUNT TYPE & CATEGORY -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Account Type</label>
                    <select v-model="form.account_type" 
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        <option value="">Select Type</option>
                        <option v-for="(label, key) in accountTypes" :key="key" :value="key">{{ label }}</option>
                    </select>
                    <div v-if="form.errors.account_type" class="text-red-500 text-sm mt-1">{{ form.errors.account_type }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Account Category</label>
                    <select v-model="form.account_category"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        <option value="">Select Category</option>
                        <option v-for="(label, key) in accountCategories" :key="key" :value="key">{{ label }}</option>
                    </select>
                    <div v-if="form.errors.account_category" class="text-red-500 text-sm mt-1">{{ form.errors.account_category }}</div>
                </div>

            </div>

            <!-- NORMAL BALANCE & PARENT ACCOUNT -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Normal Balance</label>
                    <select v-model="form.normal_balance"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Parent Account</label>
                    <select v-model="form.parent_account_id"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        <option :value="null">None (Root Account)</option>
                        <option v-for="p in parentAccounts" :key="p.id" :value="p.id">{{ p.label }}</option>
                    </select>
                    <div v-if="form.errors.parent_account_id" class="text-red-500 text-sm mt-1">{{ form.errors.parent_account_id }}</div>
                </div>

            </div>

            <!-- OPENING BALANCE -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700">Opening Balance</label>
                <input v-model="form.opening_balance" type="number" step="0.01"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none" />
                <div v-if="form.errors.opening_balance" class="text-red-500 text-sm mt-1">{{ form.errors.opening_balance }}</div>
            </div>

            <!-- DESCRIPTION -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700">Description</label>
                <textarea v-model="form.description" rows="3"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
            </div>

            <!-- ACTIVE -->
            <div class="flex items-center gap-2">
                <input v-model="form.is_active" type="checkbox" class="accent-orange-600"/>
                <label class="text-sm text-gray-700">Active Account</label>
            </div>

            <!-- BUTTON -->
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing"
                        class="flex items-center gap-2 bg-dark-blue hover:bg-dark-blue-dark text-white px-4 py-2 rounded-lg shadow transition">
                    <Save class="w-4 h-4" /> Create Account
                </button>
            </div>

        </form>

    </div>
</AppLayout>
</template>

<style scoped>
.bg-dark-blue { background-color: #1e3a8a; }
.bg-dark-blue-dark { background-color: #162864; }
</style>