<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ArrowLeft, Save } from 'lucide-vue-next'

const props = defineProps({
    account: Object,
    parentAccounts: Array,
    accountTypes: Object,
    accountCategories: Object
})

const form = useForm({
    account_code: props.account.account_code,
    account_name: props.account.account_name,
    account_type: props.account.account_type,
    account_category: props.account.account_category,
    normal_balance: props.account.normal_balance,
    parent_account_id: props.account.parent_account_id,
    description: props.account.description,
    opening_balance: props.account.opening_balance,
    is_active: props.account.is_active
})

function submit() {
    form.put(route('chart-of-accounts.update', props.account.id))
}
</script>

<template>
<AppLayout :breadcrumbs="[
    { title: 'Finance', href: '/finance' },
    { title: 'Chart of Accounts', href: route('chart-of-accounts.index') },
    { title: 'Edit', href: route('chart-of-accounts.edit', props.account.id) }
]">
    <Head title="Edit Account" />

    <div class="p-6 max-w-3xl mx-auto space-y-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-dark-blue">Edit Account</h1>
            <Link :href="route('chart-of-accounts.index')" class="flex items-center gap-2 text-gray-600 hover:text-dark-blue">
                <ArrowLeft class="w-4 h-4" /> Back
            </Link>
        </div>

        <!-- SYSTEM ACCOUNT NOTICE -->
        <div v-if="account.is_system_account" class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-3 rounded-lg">
            This is a system account. Only the name, description, and status can be edited.
        </div>

        <!-- FORM CARD -->
        <form @submit.prevent="submit" class="bg-white shadow-lg rounded-xl p-6 space-y-6">

            <!-- ACCOUNT CODE -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700">Account Code</label>
                <input v-model="form.account_code" type="text" :disabled="account.is_system_account"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none disabled:bg-gray-100"/>
                <div v-if="form.errors.account_code" class="text-red-500 text-sm mt-1">{{ form.errors.account_code }}</div>
            </div>

            <!-- ACCOUNT NAME -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700">Account Name</label>
                <input v-model="form.account_name" type="text"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"/>
                <div v-if="form.errors.account_name" class="text-red-500 text-sm mt-1">{{ form.errors.account_name }}</div>
            </div>

            <!-- TYPE & CATEGORY -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Account Type</label>
                    <select v-model="form.account_type" :disabled="account.is_system_account"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none disabled:bg-gray-100">
                        <option v-for="(label, key) in accountTypes" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Account Category</label>
                    <select v-model="form.account_category" :disabled="account.is_system_account"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none disabled:bg-gray-100">
                        <option v-for="(label, key) in accountCategories" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
            </div>

            <!-- NORMAL BALANCE & PARENT ACCOUNT -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Normal Balance</label>
                    <select v-model="form.normal_balance" :disabled="account.is_system_account"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none disabled:bg-gray-100">
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-700">Parent Account</label>
                    <select v-model="form.parent_account_id" :disabled="account.is_system_account"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none disabled:bg-gray-100">
                        <option :value="null">None</option>
                        <option v-for="p in parentAccounts" :key="p.id" :value="p.id">{{ p.label }}</option>
                    </select>
                </div>
            </div>

            <!-- OPENING BALANCE -->
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700">Opening Balance</label>
                <input v-model="form.opening_balance" type="number" step="0.01" :disabled="account.is_system_account"
                       class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none disabled:bg-gray-100"/>
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
                    <Save class="w-4 h-4"/> Update Account
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