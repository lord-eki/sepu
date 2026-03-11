<template>
    <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/transactions' }]">
        <Head title="Transactions" />

        <!-- FLASH -->
        <div ref="flashBox" class="mx-auto mt-4 max-w-4xl px-4">
            <transition
                enter-active-class="transition duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="flashMessage"
                    :class="[
                        'flex items-center gap-3 rounded-lg border p-4 text-sm shadow',
                        flashType === 'success' ? 'border-emerald-300 bg-emerald-50 text-emerald-800' : 'border-rose-300 bg-rose-50 text-rose-800',
                    ]"
                >
                    <span>{{ flashMessage }}</span>

                    <button class="ml-auto text-gray-400 hover:text-gray-700" @click="flashMessage = null">✕</button>
                </div>
            </transition>
        </div>

        <!-- PAGE -->
        <div class="min-h-screen space-y-6 bg-[#F4F6FA] p-6">
            <!-- HEADER -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#0B2C5F]">Transactions</h1>
                    <p class="text-sm text-gray-500">Manage and monitor system transactions</p>
                </div>

                <Link href="/transactions/create" class="rounded-lg bg-orange-500 px-4 py-2 text-sm text-white shadow hover:bg-orange-600">
                    + New Transaction
                </Link>
            </div>

            <!-- STATS -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Total Transactions</p>
                    <p class="mt-1 text-2xl font-bold text-[#0B2C5F]">
                        {{ stats.total_transactions ?? 0 }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Total Amount</p>
                    <p class="mt-1 text-2xl font-bold text-[#0B2C5F]">KSh {{ formattedNumber(stats.total_amount ?? 0) }}</p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Pending</p>
                    <p class="mt-1 text-2xl font-bold text-orange-500">
                        {{ stats.pending_count ?? 0 }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Completed</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">
                        {{ stats.completed_count ?? 0 }}
                    </p>
                </div>
            </div>

            <!-- FILTER TOOLBAR -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-3">
                    <input
                        v-model="filters.search"
                        @keyup.enter="applyFilters"
                        placeholder="Search transaction..."
                        class="rounded-lg border px-3 py-2 text-sm"
                    />

                    <select v-model="filters.transaction_type" class="rounded-lg border px-3 py-2 text-sm">
                        <option value="">All Types</option>
                        <option v-for="(label, key) in transactionTypes" :key="key" :value="key">
                            {{ label }}
                        </option>
                    </select>

                    <select v-model="filters.status" class="rounded-lg border px-3 py-2 text-sm">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="reversed">Reversed</option>
                    </select>

                    <input type="date" v-model="filters.start_date" class="rounded-lg border px-3 py-2 text-sm" />

                    <input type="date" v-model="filters.end_date" class="rounded-lg border px-3 py-2 text-sm" />

                    <button @click="applyFilters" class="rounded-lg bg-[#0B2C5F] px-4 py-2 text-sm text-white hover:bg-blue-800">Apply</button>

                    <button @click="resetFilters" class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50">Reset</button>

                    <button class="rounded-lg bg-orange-400 px-4 py-2 text-sm text-white hover:bg-orange-500">Export</button>
                </div>
            </div>

            <!-- TABLE -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-100 text-xs text-gray-900 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Txn ID</th>
                                <th class="px-4 py-3 text-left">Member</th>
                                <th class="px-4 py-3 text-left">Account</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Created</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            <tr v-for="(t, idx) in pageData" :key="t.id" class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-500">
                                    {{ idx + 1 + ((meta?.current_page ?? 1) - 1) * (meta?.per_page ?? pageData.length) }}
                                </td>

                                <td class="px-4 py-2 font-medium text-[#0B2C5F]">
                                    {{ t.transaction_id }}
                                </td>

                                <td class="px-4 py-2">
                                    <div class="font-medium">{{ t.member?.first_name }} {{ t.member?.last_name }}</div>

                                    <div class="text-xs text-gray-400">
                                        {{ t.member?.membership_id }}
                                    </div>
                                </td>

                                <td class="px-4 py-2">
                                    {{ t.account?.account_number ?? '-' }}
                                </td>

                                <td class="px-4 py-2">
                                    <span :class="['rounded-full px-2 py-1 text-xs font-medium', typeBadge(t.transaction_type)]">
                                        {{ transactionTypes[t.transaction_type] ?? t.transaction_type }}
                                    </span>
                                </td>

                                <td class="px-4 py-2 text-right font-semibold">KSh {{ formattedNumber(t.amount) }}</td>

                                <td class="px-4 py-2">
                                    <span :class="['rounded-md px-2 py-1 text-xs font-medium', statusBadge(t.status)]">
                                        {{ capitalize(t.status) }}
                                    </span>
                                </td>

                                <td class="px-4 py-2 text-right text-gray-500">
                                    {{ formatDate(t.created_at) }}
                                </td>

                                <td class="px-4 py-2 text-center">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        <button
                                              @click="viewTransaction(t.id)"
                                              class="text-xs text-blue-600 hover:text-blue-800"
                                          >
                                              View
                                          </button>

                                        <button
                                            v-if="t.status === 'pending'"
                                            @click="openApproveModal(t)"
                                            class="text-xs text-emerald-600 hover:text-emerald-800"
                                        >
                                            Approve
                                        </button>

                                        <button
                                            v-if="t.status === 'pending'"
                                            @click="openRejectModal(t)"
                                            class="text-xs text-red-600 hover:text-red-800"
                                        >
                                            Reject
                                        </button>

                                        <button
                                            v-if="t.status === 'completed'"
                                            @click="openReverseModal(t)"
                                            class="text-xs text-orange-600 hover:text-orange-800"
                                        >
                                            Reverse
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="pageData.length === 0">
                                <td colspan="9" class="py-8 text-center text-gray-400">No transactions found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="border-t p-4">
                    <Pagination :data="transactions" @page-changed="goToPage" />
                </div>
            </div>

            <!-- MODALS -->
            <div v-if="modals.approve || modals.reject || modals.reverse" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                    <h3 class="mb-4 text-lg font-semibold text-[#0B2C5F]">
                        {{ modals.approve ? 'Approve Transaction' : modals.reject ? 'Reject Transaction' : 'Reverse Transaction' }}
                    </h3>

                    <p v-if="modals.approve" class="mb-4 text-sm text-gray-600">
                        Approve
                        <strong>{{ currentTxn.transaction_id }}</strong> ?
                    </p>

                    <textarea
                        v-if="modals.reject"
                        v-model="actionPayload.rejection_reason"
                        rows="4"
                        placeholder="Reason for rejection"
                        class="mb-4 w-full rounded border p-2"
                    />

                    <textarea
                        v-if="modals.reverse"
                        v-model="actionPayload.reversal_reason"
                        rows="4"
                        placeholder="Reason for reversal"
                        class="mb-4 w-full rounded border p-2"
                    />

                    <div class="flex justify-end gap-3">
                        <button @click="closeModals" class="rounded-lg border px-4 py-2 text-sm">Cancel</button>

                        <button
                            @click="modals.approve ? approveTransaction() : modals.reject ? rejectTransaction() : reverseTransaction()"
                            class="rounded-lg bg-orange-500 px-4 py-2 text-sm text-white hover:bg-orange-600"
                            :disabled="loading"
                        >
                            {{ loading ? 'Processing...' : 'Confirm' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, nextTick, reactive, ref, watch } from 'vue';

// Props from Inertia
const page = usePage();
const props = page.props as any;

// Transactions & stats
const stats = computed(() => props.statistics ?? {});
const transactions = ref(props.transactions ?? {});
const transactionTypes = computed(() => props.transactionTypes ?? {});

// Pagination helpers
const meta = computed(() => transactions.value.meta ?? {});
const pageData = computed(() => transactions.value.data ?? []);

// Filters
const filters = reactive({
    search: props.filters?.search ?? '',
    transaction_type: props.filters?.transaction_type ?? '',
    status: props.filters?.status ?? '',
    start_date: props.filters?.start_date ?? '',
    end_date: props.filters?.end_date ?? '',
});

// FLASH HANDLING
const flashMessage = ref(null);
const flashType = ref('success');
const flashBox = ref(null);

watch(
    () => page.props,
    (props) => {
        if (props.flash?.success) {
            flashMessage.value = props.flash.success;
            flashType.value = 'success';
        } else if (props.flash?.error) {
            flashMessage.value = props.flash.error;
            flashType.value = 'error';
        } else if (props.errors?.error) {
            flashMessage.value = props.errors.error;
            flashType.value = 'error';
        }

        if (flashMessage.value) {
            setTimeout(() => (flashMessage.value = null), 5000);
        }
    },
    { immediate: true, deep: true },
);

// Utilities
function formattedNumber(n: number) {
    return Number(n ?? 0).toLocaleString();
}
function capitalize(s: string) {
    return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
}
function formatDate(d: string) {
    return d ? new Date(d).toLocaleString() : '-';
}

function typeBadge(type: string) {
    switch (type) {
        case 'deposit':
            return 'bg-emerald-100 text-emerald-800';
        case 'withdrawal':
            return 'bg-rose-100 text-rose-800';
        case 'transfer':
            return 'bg-indigo-100 text-indigo-800';
        case 'loan_disbursement':
            return 'bg-yellow-50 text-yellow-800';
        default:
            return 'bg-slate-100 text-slate-800';
    }
}

function statusBadge(status: string) {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-700';
        case 'completed':
            return 'bg-emerald-100 text-emerald-700';
        case 'failed':
            return 'bg-rose-100 text-rose-700';
        case 'reversed':
            return 'bg-slate-100 text-slate-700';
        default:
            return 'bg-slate-100 text-slate-700';
    }
}

// Loader & message UI
const loading = ref(false);
const message = reactive({ text: '', type: 'success', visible: false });

function showMessage(text: string, type: 'success' | 'error' = 'success') {
    message.text = text;
    message.type = type;
    message.visible = true;

    nextTick(() => {
        flashBox.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    setTimeout(() => (message.visible = false), 4000);
}

// Pagination
const paginationRange = computed(() => {
    const p = meta.value?.last_page ?? 1;
    const current = meta.value?.current_page ?? 1;
    const range: number[] = [];
    const start = Math.max(1, current - 2);
    const end = Math.min(p, current + 2);
    for (let i = start; i <= end; i++) range.push(i);
    return range;
});

async function viewTransaction(id) {
    router.visit(`/transactions/${id}`)
}

function goToPage(pageNum: number) {
    router.get('/transactions', { ...filters, page: pageNum }, { replace: true, preserveState: true });
}

function applyFilters() {
    router.get('/transactions', { ...filters, page: 1 }, { replace: true });
}

function resetFilters() {
    Object.assign(filters, { search: '', transaction_type: '', status: '', start_date: '', end_date: '' });
    applyFilters();
}

function refreshTransactions() {
    router.get('/transactions', { ...filters, page: meta.value.current_page ?? 1 }, { preserveState: true, replace: true });
}

// Admin modals & actions
const modals = reactive({ approve: false, reject: false, reverse: false });
const currentTxn = reactive<any>({});
const actionPayload = reactive({ rejection_reason: '', reversal_reason: '' });

function openApproveModal(t: any) {
    Object.assign(currentTxn, t);
    modals.approve = true;
}
function openRejectModal(t: any) {
    Object.assign(currentTxn, t);
    actionPayload.rejection_reason = '';
    modals.reject = true;
}
function openReverseModal(t: any) {
    Object.assign(currentTxn, t);
    actionPayload.reversal_reason = '';
    modals.reverse = true;
}
function closeModals() {
    modals.approve = modals.reject = modals.reverse = false;
    Object.keys(currentTxn).forEach((k) => delete currentTxn[k]);
    actionPayload.rejection_reason = '';
    actionPayload.reversal_reason = '';
}

function csrfToken() {
    const m = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
    return m?.content ?? '';
}

// --- Instant status updates ---
function approveTransaction() {
    if (!currentTxn.id) return;
    loading.value = true;

    router.post(
        `/transactions/${currentTxn.id}/approve`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                loading.value = false;
                modals.approve = false;

                // Update status locally
                const txn = pageData.value.find((t) => t.id === currentTxn.id);
                if (txn) txn.status = 'completed';

                showMessage('Transaction approved', 'success');
                Object.keys(currentTxn).forEach((k) => delete currentTxn[k]);
            },
            onError: () => {
                loading.value = false;
            },
        },
    );
}

function rejectTransaction() {
    if (!actionPayload.rejection_reason.trim()) {
        alert('Please provide a rejection reason');
        return;
    }
    loading.value = true;

    router.post(
        `/transactions/${currentTxn.id}/reject`,
        { rejection_reason: actionPayload.rejection_reason },
        {
            preserveScroll: true,
            onSuccess: () => {
                loading.value = false;
                modals.reject = false;

                const txn = pageData.value.find((t) => t.id === currentTxn.id);
                if (txn) txn.status = 'failed';

                showMessage('Transaction rejected', 'success');
                Object.keys(currentTxn).forEach((k) => delete currentTxn[k]);
                actionPayload.rejection_reason = '';
            },
            onError: () => {
                loading.value = false;
            },
        },
    );
}

function reverseTransaction() {
    if (!actionPayload.reversal_reason.trim()) {
        alert('Please provide a reversal reason');
        return;
    }
    loading.value = true;

    router.post(
        `/transactions/${currentTxn.id}/reverse`,
        { reversal_reason: actionPayload.reversal_reason },
        {
            preserveScroll: true,
            onSuccess: () => {
                loading.value = false;
                modals.reverse = false;

                const txn = pageData.value.find((t) => t.id === currentTxn.id);
                if (txn) txn.status = 'reversed';

                showMessage('Transaction reversed', 'success');
                Object.keys(currentTxn).forEach((k) => delete currentTxn[k]);
                actionPayload.reversal_reason = '';
            },
            onError: () => {
                loading.value = false;
            },
        },
    );
}

// Fetch transactions via Axios for table reload after action (optional fallback)
async function fetchTransactions(page = 1) {
    try {
        const { data } = await axios.get('/transactions', { params: { ...filters, page } });
        if (data) {
            transactions.value = {
                data: data.data,
                meta: data.meta,
            };
        }
    } catch (e) {
        console.error(e);
        showMessage('Failed to reload transactions', 'error');
    }
}
</script>

<style scoped>
button:hover {
    cursor: pointer;
}
</style>
