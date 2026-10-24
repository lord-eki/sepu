<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Power } from 'lucide-vue-next';

const props = defineProps({
    account: Object,
    children: Array,
    accountTypes: Object,
});

function toggleActive(id: number) {
    router.post(route('chart-of-accounts.toggle-active', id));
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Finance', href: '/finance' },
            { title: 'Chart of Accounts', href: route('chart-of-accounts.index') },
            { title: props.account.account_code + ' – ' + props.account.account_name, href: route('chart-of-accounts.show', props.account.id) },
        ]"
    >
        <Head title="Account Details" />

        <div class="mx-auto max-w-4xl space-y-6 p-6">
            <!-- HEADER -->
            <div class="flex items-center justify-between">
                <h1 class="text-dark-blue text-2xl font-bold">Account: {{ account.account_code }} – {{ account.account_name }}</h1>
                <Link :href="route('chart-of-accounts.index')" class="hover:text-dark-blue flex items-center gap-2 text-gray-600">
                    <ArrowLeft class="h-4 w-4" /> Back
                </Link>
            </div>

            <!-- ACCOUNT INFO CARD -->
            <div class="grid grid-cols-2 gap-4 rounded-xl bg-white p-6 shadow-lg">
                <div>
                    <p class="text-sm text-gray-500">Account Code</p>
                    <p class="font-semibold">{{ account.account_code }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Account Name</p>
                    <p class="font-semibold">{{ account.account_name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Account Type</p>
                    <p class="font-semibold">{{ account.account_type_label }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Account Category</p>
                    <p class="font-semibold">{{ account.account_category }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Normal Balance</p>
                    <p class="font-semibold">{{ account.normal_balance }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Parent Account</p>
                    <p class="font-semibold">{{ account.parent_name ?? '-' }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Opening Balance</p>
                    <p class="font-semibold">{{ account.opening_balance.toLocaleString() }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Current Balance</p>
                    <p class="font-semibold">{{ account.current_balance.toLocaleString() }}</p>
                </div>

                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Description</p>
                    <p class="font-semibold">{{ account.description ?? '-' }}</p>
                </div>

                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Status</p>
                    <p :class="account.is_active ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                        {{ account.is_active ? 'Active' : 'Inactive' }}
                    </p>
                </div>
            </div>

            <!-- CHILD ACCOUNTS TABLE -->
            <div v-if="children.length" class="rounded-xl bg-white p-6 shadow-lg">
                <h2 class="text-dark-blue mb-4 text-lg font-bold">Child Accounts</h2>

                <table class="w-full text-sm">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="p-3">Code</th>
                            <th class="p-3">Account</th>
                            <th class="p-3">Type</th>
                            <th class="p-3">Parent</th>
                            <th class="p-3">Balance</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="child in children" :key="child.id">
                            <tr class="border-t transition hover:bg-gray-50">
                                <td class="p-3 font-mono">{{ child.account_code }}</td>
                                <td class="p-3" :style="{ paddingLeft: child.level * 20 + 'px' }">{{ child.account_name }}</td>
                                <td class="p-3">{{ child.account_type_label }}</td>
                                <td class="p-3">{{ child.parent_name ?? '-' }}</td>
                                <td class="p-3 font-semibold">{{ child.current_balance.toLocaleString() }}</td>
                                <td class="p-3">
                                    <span v-if="child.is_active" class="font-semibold text-green-600">Active</span>
                                    <span v-else class="font-semibold text-red-600">Inactive</span>
                                </td>
                                <td class="flex justify-end gap-3 p-3">
                                    <Link :href="route('chart-of-accounts.edit', child.id)" class="text-amber-600 hover:text-orange-600">
                                        <Pencil class="h-4 w-4" />
                                    </Link>
                                    <button @click="toggleActive(child.id)" class="text-gray-600 transition hover:text-red-600">
                                        <Power class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>

                            <!-- Grandchildren recursively -->
                            <template v-if="child.children && child.children.length" v-for="grandchild in child.children" :key="grandchild.id">
                                <tr class="border-t transition hover:bg-gray-50">
                                    <td class="p-3 font-mono">{{ grandchild.account_code }}</td>
                                    <td class="p-3" :style="{ paddingLeft: grandchild.level * 20 + 'px' }">{{ grandchild.account_name }}</td>
                                    <td class="p-3">{{ grandchild.account_type_label }}</td>
                                    <td class="p-3">{{ grandchild.parent_name ?? '-' }}</td>
                                    <td class="p-3 font-semibold">{{ grandchild.current_balance.toLocaleString() }}</td>
                                    <td class="p-3">
                                        <span v-if="grandchild.is_active" class="font-semibold text-green-600">Active</span>
                                        <span v-else class="font-semibold text-red-600">Inactive</span>
                                    </td>
                                    <td class="flex justify-end gap-3 p-3">
                                        <Link :href="route('chart-of-accounts.edit', grandchild.id)" class="text-amber-600 hover:text-orange-600">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                        <button @click="toggleActive(grandchild.id)" class="text-gray-600 transition hover:text-red-600">
                                            <Power class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.text-dark-blue {
    color: #1e3a8a;
}
.hover\:text-dark-blue:hover {
    color: #1e3a8a;
}
.hover\:text-orange-600:hover {
    color: #f97316;
}
</style>
