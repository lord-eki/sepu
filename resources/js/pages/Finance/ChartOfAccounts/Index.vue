<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import {
    Plus,
    Search,
    Eye,
    Pencil,
    Power,
    Trash2,
    X,
} from 'lucide-vue-next'
import Modal from '@/components/Modal.vue' 

interface Account {
    id: number
    account_code: string
    account_name: string
    account_type: string
    account_type_label: string
    account_category: string
    normal_balance: string
    current_balance: number
    parent_name?: string
    is_active: boolean
    is_postable: boolean
    level: number
}

const props = defineProps<{
    accounts: Account[]
    tree: any[] | null
    stats: any
    filters: any
    accountTypes: Record<string, string>
}>()

const search = ref(props.filters?.search ?? '')
const type = ref(props.filters?.type ?? '')
const status = ref(props.filters?.status ?? '')

// Modal state
const showDeleteModal = ref(false)
const deleteTargetId = ref<number | null>(null)

function filter() {
    router.get(route('chart-of-accounts.index'), {
        search: search.value,
        type: type.value,
        status: status.value
    }, { preserveState: true })
}

function toggleActive(id: number) {
    router.post(route('chart-of-accounts.toggle-active', id))
}

function confirmDelete(id: number) {
    deleteTargetId.value = id
    showDeleteModal.value = true
}

function deleteAccount() {
    if (deleteTargetId.value) {
        router.delete(route('chart-of-accounts.destroy', deleteTargetId.value))
        showDeleteModal.value = false
    }
}
</script>

<template>
<AppLayout :breadcrumbs="[{ title: 'Chart of Accounts', href: route('chart-of-accounts.index') }]">
    <Head title="Chart of Accounts" />

    <div class="space-y-6 p-6">

        <!-- HEADER -->
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-dark-blue">Chart of Accounts</h1>
            <Link :href="route('chart-of-accounts.create')"
                  class="flex items-center gap-2 bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg shadow">
                <Plus class="w-4 h-4" /> New Account
            </Link>
        </div>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-dark-blue text-white rounded-xl shadow-lg p-6 flex flex-col justify-center items-center hover:scale-105 transform transition">
                <p class="text-sm uppercase tracking-wide">Total Accounts</p>
                <p class="text-2xl font-bold mt-2">{{ stats.total }}</p>
            </div>
            <div class="bg-green-600 text-white rounded-xl shadow-lg p-6 flex flex-col justify-center items-center hover:scale-105 transform transition">
                <p class="text-sm uppercase tracking-wide">Active</p>
                <p class="text-2xl font-bold mt-2">{{ stats.active }}</p>
            </div>
            <div class="bg-blue-600 text-white rounded-xl shadow-lg p-6 flex flex-col justify-center items-center hover:scale-105 transform transition">
                <p class="text-sm uppercase tracking-wide">Postable</p>
                <p class="text-2xl font-bold mt-2">{{ stats.postable }}</p>
            </div>
            <div class="bg-orange-600 text-white rounded-xl shadow-lg p-6 flex flex-col justify-center items-center hover:scale-105 transform transition">
                <p class="text-sm uppercase tracking-wide">Types</p>
                <p class="text-2xl font-bold mt-2">{{ Object.keys(stats.by_type || {}).length }}</p>
            </div>
        </div>

        <!-- FILTERS -->
        <div class="bg-white rounded-xl shadow-lg p-4 flex flex-wrap gap-4 items-center">
            <div class="relative w-64">
                <Search class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                <input v-model="search" @keyup.enter="filter" placeholder="Search accounts..."
                       class="pl-9 border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-orange-500 focus:outline-none" />
            </div>
            <select v-model="type" @change="filter" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500">
                <option value="">All Types</option>
                <option v-for="(label, key) in accountTypes" :key="key" :value="key">{{ label }}</option>
            </select>
            <select v-model="status" @change="filter" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-orange-500">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <!-- ACCOUNTS TABLE -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <table class="w-full text-sm">
                <thead class="bg-dark-blue text-white">
                    <tr>
                        <th class="p-3 text-left">Code</th>
                        <th class="p-3 text-left">Account</th>
                        <th class="p-3 text-left">Type</th>
                        <th class="p-3 text-left">Parent</th>
                        <th class="p-3 text-left">Balance</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="account in accounts" :key="account.id"
                        class="border-t hover:bg-gray-50 transition-colors duration-200">
                        <td class="p-3 font-mono">{{ account.account_code }}</td>
                        <td class="p-3" :style="{ paddingLeft: (account.level - 1) * 20 + 'px' }">{{ account.account_name }}</td>
                        <td class="p-3">{{ account.account_type_label }}</td>
                        <td class="p-3 text-gray-500">{{ account.parent_name ?? '-' }}</td>
                        <td class="p-3 font-semibold">{{ account.current_balance.toLocaleString() }}</td>
                        <td class="p-3">
                            <span :class="account.is_active ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                                {{ account.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="p-3">
                            <div class="flex justify-end gap-3">
                                <Link :href="route('chart-of-accounts.show', account.id)" class="text-blue-600 hover:text-blue-800">
                                    <Eye class="w-4 h-4" />
                                </Link>
                                <Link :href="route('chart-of-accounts.edit', account.id)" class="text-amber-600 hover:text-amber-800">
                                    <Pencil class="w-4 h-4" />
                                </Link>
                                <button @click="toggleActive(account.id)" class="text-gray-600 hover:text-gray-800">
                                    <Power class="w-4 h-4" />
                                </button>
                                <button @click="confirmDelete(account.id)" class="text-red-600 hover:text-red-800">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- DELETE MODAL -->
        <Modal v-if="showDeleteModal" @close="showDeleteModal = false">
            <template #title>Delete Account</template>
            <template #body>
                Are you sure you want to delete this account? This action cannot be undone.
            </template>
            <template #footer>
                <button @click="showDeleteModal = false"
                        class="px-4 py-2 rounded-lg bg-gray-300 hover:bg-gray-400">
                    Cancel
                </button>
                <button @click="deleteAccount"
                        class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                    Delete
                </button>
            </template>
        </Modal>

    </div>
</AppLayout>
</template>

<style scoped>
.bg-dark-blue { background-color: #1e3a8a; }
</style>