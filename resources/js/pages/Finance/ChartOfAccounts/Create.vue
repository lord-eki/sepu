<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Save, Tag, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface ParentAccount {
    id: number;
    label: string;
    type: string;
    level: number;
    account_code: string;
}

interface Category {
    id: number;
    key: string;
    label: string;
    is_system: boolean;
}

const props = defineProps<{
    parentAccounts: ParentAccount[];
    accountTypes: Record<string, string>;
    accountCategories: Category[];
}>();

// ── Local categories list (starts with DB records, grows when user adds one) ──
const categories = ref<Category[]>([...props.accountCategories]);

// ── Form ─────────────────────────────────────────────────────────────────────
const form = useForm({
    account_code: '',
    account_name: '',
    account_type: '',
    account_category: '',
    normal_balance: 'debit',
    parent_account_id: null as number | null,
    description: '',
    opening_balance: 0,
    is_active: true,
});

// ── Auto-generate account code ────────────────────────────────────────────────
const isGeneratingCode = ref(false);

async function generateCode(parentId: number | null) {
    isGeneratingCode.value = true;
    try {
        const url = parentId ? `/chart-of-accounts/api/next-code?parent_id=${parentId}` : `/chart-of-accounts/api/next-code`;
        const res = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (res.ok) {
            const data = await res.json();
            form.account_code = data.next_code;
        }
    } finally {
        isGeneratingCode.value = false;
    }
}

// ── Auto-fill account type from parent ───────────────────────────────────────
watch(
    () => form.parent_account_id,
    (newVal) => {
        generateCode(newVal);

        if (newVal) {
            const parent = props.parentAccounts.find((p) => p.id === newVal);
            if (parent?.type) form.account_type = parent.type;
        } else {
            form.account_type = '';
        }
    },
    { immediate: true },
);

// ── Inline "New Category" mini-form ──────────────────────────────────────────
const showNewCategory = ref(false);
const newCategoryLabel = ref('');
const newCategoryError = ref('');
const isSavingCategory = ref(false);

function openNewCategory() {
    showNewCategory.value = true;
    newCategoryLabel.value = '';
    newCategoryError.value = '';
}

function cancelNewCategory() {
    showNewCategory.value = false;
    newCategoryLabel.value = '';
    newCategoryError.value = '';
}

async function addCategory() {
    const label = newCategoryLabel.value.trim();
    if (!label) {
        newCategoryError.value = 'Category name is required.';
        return;
    }

    isSavingCategory.value = true;
    newCategoryError.value = '';

    try {
        const res = await fetch('/chart-of-accounts/api/categories', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
            body: JSON.stringify({ label }),
        });

        if (!res.ok) {
            const err = await res.json();
            newCategoryError.value = err.message ?? 'Failed to save category.';
            return;
        }

        const category: Category = await res.json();

        if (!categories.value.find((c) => c.key === category.key)) {
            categories.value.push(category);
        }

        form.account_category = category.key;
        cancelNewCategory();
    } catch {
        newCategoryError.value = 'Network error. Please try again.';
    } finally {
        isSavingCategory.value = false;
    }
}

function submit() {
    form.post(route('chart-of-accounts.store'));
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Finance', href: '/finance' },
            { title: 'Chart of Accounts', href: route('chart-of-accounts.index') },
            { title: 'Create', href: route('chart-of-accounts.create') },
        ]"
    >
        <Head title="Create Account" />

        <div class="mx-auto max-w-3xl space-y-6 p-6">
            <!-- HEADER -->
            <div class="flex items-center justify-between">
                <h1 class="text-dark-blue text-2xl font-bold">Create Chart of Account</h1>
                <Link :href="route('chart-of-accounts.index')" class="hover:text-dark-blue flex items-center gap-2 text-gray-600">
                    <ArrowLeft class="h-4 w-4" />
                    Back
                </Link>
            </div>

            <!-- FORM CARD -->
            <form @submit.prevent="submit" class="space-y-6 rounded-xl bg-white p-6 shadow-lg">
                <!-- ROW 1 : Parent Account + Account Code -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Parent Account</label>
                        <select
                            v-model="form.parent_account_id"
                            class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                        >
                            <option :value="null">None (Root Account)</option>
                            <option v-for="p in parentAccounts" :key="p.id" :value="p.id">
                                {{ p.label }}
                            </option>
                        </select>
                        <div v-if="form.errors.parent_account_id" class="mt-1 text-sm text-red-500">
                            {{ form.errors.parent_account_id }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Account Code
                            <span class="ml-1 text-xs font-normal text-orange-500">(auto-generated)</span>
                        </label>
                        <input
                            :value="form.account_code"
                            type="text"
                            readonly
                            :class="[
                                'w-full cursor-not-allowed rounded-lg border bg-gray-50 px-3 py-2 font-mono text-gray-600',
                                isGeneratingCode ? 'animate-pulse' : '',
                            ]"
                            placeholder="Generating…"
                        />
                        <p class="mt-1 text-xs text-gray-400">Determined by the selected parent account.</p>
                        <div v-if="form.errors.account_code" class="mt-1 text-sm text-red-500">
                            {{ form.errors.account_code }}
                        </div>
                    </div>
                </div>

                <!-- ROW 2 : Account Name -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Account Name</label>
                    <input
                        v-model="form.account_name"
                        type="text"
                        class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                    />
                    <div v-if="form.errors.account_name" class="mt-1 text-sm text-red-500">
                        {{ form.errors.account_name }}
                    </div>
                </div>

                <!-- ROW 3 : Account Type + Account Category -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Account Type
                            <span v-if="form.parent_account_id" class="ml-1 text-xs font-normal text-orange-500">(inherited from parent)</span>
                        </label>
                        <select
                            v-model="form.account_type"
                            :disabled="!!form.parent_account_id"
                            :class="[
                                'w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none',
                                form.parent_account_id ? 'cursor-not-allowed bg-gray-50 text-gray-600' : '',
                            ]"
                        >
                            <option value="">Select Type</option>
                            <option v-for="(label, key) in accountTypes" :key="key" :value="key">
                                {{ label }}
                            </option>
                        </select>
                        <div v-if="form.errors.account_type" class="mt-1 text-sm text-red-500">
                            {{ form.errors.account_type }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Account Category</label>

                        <div v-if="!showNewCategory" class="flex gap-2">
                            <select
                                v-model="form.account_category"
                                class="flex-1 rounded-lg border px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                            >
                                <option value="">Select Category</option>
                                <option v-for="cat in categories" :key="cat.key" :value="cat.key">
                                    {{ cat.label }}
                                </option>
                            </select>
                            <button
                                type="button"
                                @click="openNewCategory"
                                title="Add new category"
                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg border border-orange-500 text-orange-500 transition hover:bg-orange-50"
                            >
                                <Plus class="h-4 w-4" />
                            </button>
                        </div>

                        <div v-else class="space-y-2 rounded-lg border border-orange-300 bg-orange-50 p-3">
                            <div class="mb-1 flex items-center justify-between">
                                <span class="flex items-center gap-1 text-xs font-semibold tracking-wide text-orange-700 uppercase">
                                    <Tag class="h-3 w-3" /> New Category
                                </span>
                                <button type="button" @click="cancelNewCategory" class="text-gray-400 hover:text-gray-600">
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                            <input
                                v-model="newCategoryLabel"
                                type="text"
                                placeholder="e.g. Prepaid Expenses"
                                :disabled="isSavingCategory"
                                @keyup.enter="addCategory"
                                @keyup.escape="cancelNewCategory"
                                class="w-full rounded-md border bg-white px-3 py-1.5 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none disabled:opacity-60"
                                autofocus
                            />
                            <div v-if="newCategoryError" class="text-xs text-red-500">{{ newCategoryError }}</div>
                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    @click="cancelNewCategory"
                                    :disabled="isSavingCategory"
                                    class="rounded-md border border-gray-300 px-3 py-1.5 text-xs text-gray-600 transition hover:bg-gray-100 disabled:opacity-60"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    @click="addCategory"
                                    :disabled="isSavingCategory"
                                    class="rounded-md bg-orange-500 px-3 py-1.5 text-xs text-white transition hover:bg-orange-600 disabled:opacity-60"
                                >
                                    {{ isSavingCategory ? 'Saving…' : 'Add & Select' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="form.errors.account_category" class="mt-1 text-sm text-red-500">
                            {{ form.errors.account_category }}
                        </div>
                    </div>
                </div>

                <!-- ROW 4 : Normal Balance + Opening Balance -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Normal Balance</label>
                        <select
                            v-model="form.normal_balance"
                            class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                        >
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Opening Balance</label>
                        <input
                            v-model="form.opening_balance"
                            type="number"
                            step="0.01"
                            class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                        />
                        <div v-if="form.errors.opening_balance" class="mt-1 text-sm text-red-500">
                            {{ form.errors.opening_balance }}
                        </div>
                    </div>
                </div>

                <!-- DESCRIPTION -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                    />
                </div>

                <!-- ACTIVE -->
                <div class="flex items-center gap-2">
                    <input v-model="form.is_active" type="checkbox" class="accent-orange-600" />
                    <label class="text-sm text-gray-700">Active Account</label>
                </div>

                <!-- SUBMIT -->
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-dark-blue hover:bg-dark-blue-dark flex items-center gap-2 rounded-lg px-4 py-2 text-white shadow transition disabled:opacity-60"
                    >
                        <Save class="h-4 w-4" /> Create Account
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.bg-dark-blue {
    background-color: #1e3a8a;
}

.bg-dark-blue-dark {
    background-color: #162864;
}
</style>
