<template>
    <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/transactions' }, { title: transaction.transaction_id ?? 'Transaction' }]">
        <Head :title="transaction.transaction_id ?? 'Transaction'" />

        <div class="space-y-6">
            <!-- HEADER -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Transaction {{ transaction.transaction_id }}</h1>
                    <p class="text-sm text-slate-500">Reference: {{ transaction.reference_number ?? 'N/A' }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        v-if="transaction.status === 'pending'"
                        @click="approve"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-white shadow transition hover:shadow-lg"
                    >
                        Approve
                    </button>

                    <button
                        v-if="transaction.status === 'pending'"
                        @click="openReject"
                        class="rounded-lg bg-rose-500 px-4 py-2 text-white shadow transition hover:shadow-lg"
                    >
                        Reject
                    </button>

                    <button
                        v-if="transaction.status === 'completed'"
                        @click="openReverse"
                        class="rounded-lg bg-amber-500 px-4 py-2 text-white shadow transition hover:shadow-lg"
                    >
                        Reverse
                    </button>

                    <button @click="deleteTxn" class="rounded-lg border border-slate-300 px-4 py-2 hover:bg-slate-100">Delete</button>
                </div>
            </div>

            <!-- GRID -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- SUMMARY -->
                <div class="space-y-5 rounded-2xl bg-white p-6 shadow-md">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm text-slate-400">Amount</p>
                            <p class="text-3xl font-bold text-slate-800">KSh {{ formattedNumber(transaction.amount) }}</p>
                        </div>

                        <span :class="['rounded-lg px-3 py-1 text-sm font-semibold', statusBadge(transaction.status)]">
                            {{ capitalize(transaction.status) }}
                        </span>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Type</span>
                            <span class="font-medium">{{ transaction.transaction_type }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-400">Payment Method</span>
                            <span class="font-medium">{{ transaction.payment_method ?? '-' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-400">Processed By</span>
                            <span class="font-medium">{{ transaction.processedBy?.name ?? '-' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-400">Date</span>
                            <span class="font-medium">{{ formatDate(transaction.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- MEMBER CARD -->
                <div class="rounded-2xl bg-white p-6 shadow-md">
                    <h3 class="mb-4 font-semibold text-slate-700">Member</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Name</span>
                            <span>{{ memberName }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-400">Membership</span>
                            <span>{{ transaction.member?.membership_id ?? '-' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-400">Account</span>
                            <span>{{ transaction.account?.account_number ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- BALANCE FLOW -->
                <div class="rounded-2xl bg-white p-6 shadow-md">
                    <h3 class="mb-4 font-semibold text-slate-700">Balance Flow</h3>

                    <div class="flex items-center justify-between text-sm">
                        <div class="text-center">
                            <p class="text-slate-400">Before</p>
                            <p class="font-semibold">KSh {{ formattedNumber(transaction.balance_before) }}</p>
                        </div>

                        <div class="text-lg text-slate-400">→</div>

                        <div class="text-center">
                            <p class="text-slate-400">Amount</p>
                            <p class="font-semibold text-emerald-600">+ {{ formattedNumber(transaction.amount) }}</p>
                        </div>

                        <div class="text-lg text-slate-400">→</div>

                        <div class="text-center">
                            <p class="text-slate-400">After</p>
                            <p class="font-semibold">KSh {{ formattedNumber(transaction.balance_after) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DESCRIPTION -->
            <div class="rounded-2xl bg-white p-6 shadow-md">
                <h3 class="mb-3 font-semibold text-slate-700">Description</h3>

                <p class="text-sm text-slate-600">
                    {{ transaction.description ?? 'No description provided.' }}
                </p>
            </div>
        </div>

        <!-- MODALS -->

        <div v-if="modals.reject || modals.reverse" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md space-y-4 rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-slate-800">
                    {{ modals.reject ? 'Reject Transaction' : 'Reverse Transaction' }}
                </h3>

                <textarea
                    v-model="payload.reason"
                    rows="4"
                    class="w-full rounded-lg border p-3 focus:ring-2 focus:ring-indigo-500"
                    placeholder="Enter reason..."
                ></textarea>

                <div class="flex justify-end gap-3">
                    <button @click="closeModals" class="rounded-lg border px-4 py-2 hover:bg-slate-100">Cancel</button>

                    <button
                        @click="modals.reject ? reject() : reverse()"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white shadow hover:shadow-lg"
                    >
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps({ transaction: Object });

const transaction = props.transaction;

const page = usePage();
const user = page.props.auth?.user;

const modals = reactive({
    reject: false,
    reverse: false,
});

const payload = reactive({
    reason: '',
});

const memberName = computed(() => {
    if (!transaction.member) return '-';
    return `${transaction.member.first_name ?? ''} ${transaction.member.last_name ?? ''}`;
});

function formattedNumber(n) {
    return Number(n ?? 0).toLocaleString();
}

function capitalize(s) {
    return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
}

function formatDate(d) {
    return d ? new Date(d).toLocaleString() : '-';
}

function statusBadge(status) {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-700';
        case 'completed':
            return 'bg-emerald-100 text-emerald-700';
        case 'failed':
            return 'bg-rose-100 text-rose-700';
        case 'reversed':
            return 'bg-slate-200 text-slate-700';
        default:
            return 'bg-slate-200 text-slate-700';
    }
}

function csrf() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

async function approve() {
    await fetch(`/transactions/${transaction.id}/approve`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf() },
    });
    router.reload();
}

function openReject() {
    payload.reason = '';
    modals.reject = true;
}

function openReverse() {
    payload.reason = '';
    modals.reverse = true;
}

function closeModals() {
    modals.reject = false;
    modals.reverse = false;
}

async function reject() {
    await fetch(`/transactions/${transaction.id}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf(),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ rejection_reason: payload.reason }),
    });
    router.reload();
}

async function reverse() {
    await fetch(`/transactions/${transaction.id}/reverse`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf(),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reversal_reason: payload.reason }),
    });
    router.reload();
}

async function deleteTxn() {
    if (!confirm('Delete this transaction?')) return;

    await fetch(`/transactions/${transaction.id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf() },
    });

    router.visit('/transactions');
}
</script>
