<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Power, Search, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Account {
    id: number;
    account_code: string;
    account_name: string;
    account_type_label: string;
    current_balance: number;
    parent_name?: string;
    is_active: boolean;
    is_postable: boolean;
    level: number;
}

const props = defineProps<{
    accounts: Account[];
    stats: any;
    filters: any;
    accountTypes: Record<string, string>;
}>();

const search = ref(props.filters?.search ?? '');
const type = ref(props.filters?.type ?? '');
const status = ref(props.filters?.status ?? '');

const showDeleteModal = ref(false);
const deleteTargetId = ref<number | null>(null);

function filter() {
    router.get(
        route('chart-of-accounts.index'),
        {
            search: search.value,
            type: type.value,
            status: status.value,
        },
        { preserveState: true },
    );
}

function toggleActive(id: number) {
    router.post(route('chart-of-accounts.toggle-active', id));
}

function confirmDelete(id: number) {
    deleteTargetId.value = id;
    showDeleteModal.value = true;
}

function deleteAccount() {
    if (deleteTargetId.value) {
        router.delete(route('chart-of-accounts.destroy', deleteTargetId.value));
        showDeleteModal.value = false;
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Chart of Accounts' }]">
        <Head title="Chart of Accounts" />

        <div class="min-h-screen space-y-6 bg-slate-50 p-6">
            <!-- HEADER -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Chart of Accounts</h1>
                    <p class="mt-1 text-sm text-slate-500">Structured financial account hierarchy</p>
                </div>

                <Link
                    :href="route('chart-of-accounts.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-950 px-5 py-2.5 text-white shadow transition hover:bg-slate-800"
                >
                    <Plus class="h-4 w-4" />
                    New Account
                </Link>
            </div>

            <!-- ⭐ MODERN SHINY STATS CARDS (ADDED / IMPROVED) -->
            <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
                <!-- TOTAL -->
                <div
                    class="relative transform overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700 p-6 text-white shadow-lg transition hover:scale-[1.02]"
                >
                    <div class="absolute inset-0 bg-white opacity-20 blur-2xl"></div>
                    <p class="text-sm tracking-widest uppercase opacity-80">Total Accounts</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats.total }}</p>
                </div>

                <!-- ACTIVE -->
                <div
                    class="relative transform overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-600 via-green-600 to-emerald-700 p-6 text-white shadow-lg transition hover:scale-[1.02]"
                >
                    <div class="absolute inset-0 bg-white opacity-20 blur-2xl"></div>
                    <p class="text-sm tracking-widest uppercase opacity-80">Active</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats.active }}</p>
                </div>

                <!-- POSTABLE -->
                <div
                    class="relative transform overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-sky-600 to-blue-700 p-6 text-white shadow-lg transition hover:scale-[1.02]"
                >
                    <div class="absolute inset-0 bg-white opacity-20 blur-2xl"></div>
                    <p class="text-sm tracking-widest uppercase opacity-80">Postable</p>
                    <p class="mt-2 text-3xl font-bold">{{ stats.postable }}</p>
                </div>

                <!-- TYPES -->
                <div
                    class="relative transform overflow-hidden rounded-2xl bg-gradient-to-br from-orange-500 via-amber-500 to-orange-600 p-6 text-white shadow-lg transition hover:scale-[1.02]"
                >
                    <div class="absolute inset-0 bg-white opacity-20 blur-2xl"></div>
                    <p class="text-sm tracking-widest uppercase opacity-80">Types</p>
                    <p class="mt-2 text-3xl font-bold">
                        {{ Object.keys(stats.by_type || {}).length }}
                    </p>
                </div>
            </div>

            <!-- FILTER BAR -->
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="relative w-full md:w-80">
                    <Search class="absolute top-3 left-3 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        @keyup.enter="filter"
                        placeholder="Search code, name..."
                        class="w-full rounded-xl border border-slate-200 py-2 pr-3 pl-9 focus:ring-2 focus:ring-slate-900 focus:outline-none"
                    />
                </div>

                <select v-model="type" @change="filter" class="rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-slate-900">
                    <option value="">All Types</option>
                    <option v-for="(label, key) in accountTypes" :key="key" :value="key">
                        {{ label }}
                    </option>
                </select>

                <select v-model="status" @change="filter" class="rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-slate-900">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- TABLE (UNCHANGED EXACTLY AS YOU HAD IT) -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-blue-950 text-xs tracking-wider text-white uppercase">
                            <tr>
                                <th class="p-4 text-left">Code</th>
                                <th class="p-4 text-left">Account Name</th>
                                <th class="p-4 text-left">Type</th>
                                <th class="p-4 text-left">Parent</th>
                                <th class="p-4 text-left">Balance</th>
                                <th class="p-4 text-left">Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="account in accounts" :key="account.id" class="group transition hover:bg-slate-50">
                                <td class="p-4 font-mono text-xs text-slate-600">
                                    {{ account.account_code }}
                                </td>

                                <td class="p-4">
                                    <div class="flex items-center gap-2" :style="{ marginLeft: (account.level - 1) * 18 + 'px' }">
                                        <span class="h-2.5 w-2.5 rounded-full" :class="account.level === 1 ? 'bg-slate-900' : 'bg-slate-400'"></span>

                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-900">
                                                {{ account.account_name }}
                                            </span>
                                            <span class="text-xs text-slate-400"> Level {{ account.level }} </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-4">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700">
                                        {{ account.account_type_label }}
                                    </span>
                                </td>

                                <td class="p-4 text-slate-500">
                                    {{ account.parent_name ?? '—' }}
                                </td>

                                <td class="p-4 font-semibold text-slate-900">
                                    {{ Number(account.current_balance).toLocaleString() }}
                                </td>

                                <td class="p-4">
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs"
                                        :class="account.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'"
                                    >
                                        {{ account.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="p-4">
                                    <div class="flex justify-end gap-2 opacity-60 transition group-hover:opacity-100">
                                        <Link :href="route('chart-of-accounts.show', account.id)" class="rounded-lg p-2 hover:bg-slate-100">
                                            <Eye class="h-4 w-4 text-slate-600" />
                                        </Link>

                                        <Link :href="route('chart-of-accounts.edit', account.id)" class="rounded-lg p-2 hover:bg-slate-100">
                                            <Pencil class="h-4 w-4 text-slate-600" />
                                        </Link>

                                        <button @click="toggleActive(account.id)" class="rounded-lg p-2 hover:bg-slate-100">
                                            <Power class="h-4 w-4 text-slate-600" />
                                        </button>

                                        <button @click="confirmDelete(account.id)" class="rounded-lg p-2 hover:bg-red-50">
                                            <Trash2 class="h-4 w-4 text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
