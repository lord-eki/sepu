<template>
    <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/transactions' }, { title: transaction.transaction_id ?? 'Transaction' }]">
        <Head :title="transaction.transaction_id ?? 'Transaction'" />

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

        <div class="mx-8 mt-6 space-y-10">
            <!-- HEADER -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <button @click="goBack" class="mb-3 text-lg text-blue-600 hover:underline">← Back to Transactions</button>

                    <h1 class="text-xl font-bold text-slate-900">Transaction {{ transaction.transaction_id }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Reference: {{ transaction.reference_number ?? 'N/A' }}</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <!-- ✅ CHANGED: now opens modal -->
                    <button
                        v-if="transaction.status === 'pending'"
                        @click="openApprove"
                        class="rounded-lg bg-blue-700 px-6 py-2 text-white shadow-md hover:bg-blue-800"
                    >
                        Approve
                    </button>

                    <button
                        v-if="transaction.status === 'pending'"
                        @click="openReject"
                        class="rounded-lg bg-orange-600 px-6 py-2 text-white shadow-md hover:bg-orange-700"
                    >
                        Reject
                    </button>

                    <button
                        v-if="transaction.status === 'completed'"
                        @click="openReverse"
                        class="rounded-lg bg-orange-500 px-6 py-2 text-white shadow-md hover:bg-orange-600"
                    >
                        Reverse
                    </button>

                    <button @click="deleteTxn" class="rounded-lg border border-slate-300 px-6 py-2 hover:bg-slate-100">Delete</button>
                </div>
            </div>

            <!-- GRID (UNCHANGED) -->
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-lg">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm text-slate-400">Amount</p>
                            <p class="text-xl font-bold text-slate-900">KSh {{ formattedNumber(transaction.amount) }}</p>
                        </div>
                        <span :class="['h-fit rounded-full px-3 py-1 text-sm', statusBadge(transaction.status)]">
                            {{ capitalize(transaction.status) }}
                        </span>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-lg">
                    <h3 class="mb-3 font-semibold">Member</h3>
                    <p>{{ memberName }}</p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-lg">
                    <h3 class="mb-3 font-semibold">Balance Flow</h3>
                    <p>KSh {{ formattedNumber(transaction.balance_before) }} → KSh {{ formattedNumber(transaction.balance_after) }}</p>
                </div>
            </div>
        </div>

        <div v-if="modals.approve || modals.reject || modals.reverse" class="fixed inset-0 flex items-center justify-center bg-black/40">
            <div class="w-full max-w-md rounded-xl bg-white p-6">
                <h3 class="mb-4 font-semibold">
                    {{ modals.approve ? 'Approve Transaction' : modals.reject ? 'Reject Transaction' : 'Reverse Transaction' }}
                </h3>

                <p v-if="modals.approve" class="mb-4 text-sm text-gray-600">Are you sure you want to approve this transaction?</p>

                <textarea v-if="modals.reject" v-model="payload.reason" class="mb-4 w-full rounded border p-2" placeholder="Enter rejection reason" />

                <div v-if="modals.reverse">
                    <textarea v-model="payload.reason" class="mb-2 w-full rounded border p-2" placeholder="Enter reversal reason" />
                    <p v-if="!payload.reason.trim()" class="text-xs text-red-500">Reason is required</p>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button @click="closeModals" class="rounded border px-4 py-2">Cancel</button>

                    <button
                        @click="handleAction"
                        :disabled="(modals.reverse && !payload.reason.trim()) || loading"
                        class="rounded bg-blue-700 px-4 py-2 text-white disabled:opacity-50"
                    >
                        {{ loading ? 'Processing...' : 'Confirm' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const props = defineProps({ transaction: Object });
const transaction = props.transaction;

const loading = ref(false);

const flashMessage = ref(null);
const flashType = ref('success');

const modals = reactive({ approve: false, reject: false, reverse: false });
const payload = reactive({ reason: '' });

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

function statusBadge(status) {
    return status === 'completed'
        ? 'bg-emerald-100 text-emerald-700'
        : status === 'pending'
          ? 'bg-orange-100 text-orange-700'
          : 'bg-slate-200 text-slate-700';
}

function goBack() {
    router.visit('/transactions');
}

function openApprove() {
    modals.approve = true;
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
    modals.approve = modals.reject = modals.reverse = false;
}

function handleAction() {
    if (modals.approve) approve();
    else if (modals.reject) reject();
    else reverse();
}

function approve() {
    loading.value = true;

    router.post(
        `/transactions/${transaction.id}/approve`,
        {},
        {
            onSuccess: () => {
                loading.value = false;
                closeModals();
                transaction.status = 'completed';
                flashMessage.value = 'Transaction approved';
                flashType.value = 'success';
            },
        },
    );
}

function reject() {
    router.post(
        `/transactions/${transaction.id}/reject`,
        {
            rejection_reason: payload.reason,
        },
        {
            onSuccess: () => {
                closeModals();
                transaction.status = 'failed';
                flashMessage.value = 'Transaction rejected';
            },
        },
    );
}

function reverse() {
    if (!payload.reason.trim()) return;

    router.post(
        `/transactions/${transaction.id}/reverse`,
        {
            reversal_reason: payload.reason,
        },
        {
            onSuccess: () => {
                closeModals();
                transaction.status = 'reversed';
                flashMessage.value = 'Transaction reversed';
            },
        },
    );
}

function deleteTxn() {
    if (!confirm('Delete this transaction?')) return;

    router.delete(`/transactions/${transaction.id}`, {
        onSuccess: () => {
            router.visit('/transactions');
        },
    });
}
</script>
