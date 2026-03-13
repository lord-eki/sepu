<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/transactions' }, { title: transaction.transaction_id ?? 'Transaction' }]">
    <Head :title="transaction.transaction_id ?? 'Transaction'" />

    <div class="space-y-10 mx-8 mt-6">
      <!-- HEADER -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
          <h1 class="text-xl font-bold text-slate-900">Transaction {{ transaction.transaction_id }}</h1>
          <p class="text-sm text-slate-500 mt-1">Reference: {{ transaction.reference_number ?? 'N/A' }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
          <button
            v-if="transaction.status === 'pending'"
            @click="approve"
            class="rounded-lg bg-blue-700 px-6 py-2 text-white shadow-md transition hover:shadow-xl hover:bg-blue-800"
          >
            Approve
          </button>

          <button
            v-if="transaction.status === 'pending'"
            @click="openReject"
            class="rounded-lg bg-orange-600 px-6 py-2 text-white shadow-md transition hover:shadow-xl hover:bg-orange-700"
          >
            Reject
          </button>

          <button
            v-if="transaction.status === 'completed'"
            @click="openReverse"
            class="rounded-lg bg-orange-500 px-6 py-2 text-white shadow-md transition hover:shadow-xl hover:bg-orange-600"
          >
            Reverse
          </button>

          <button
            @click="deleteTxn"
            class="rounded-lg border border-slate-300 px-6 py-2 hover:bg-slate-100 transition"
          >
            Delete
          </button>
        </div>
      </div>

      <!-- GRID -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- SUMMARY CARD -->
        <div class="space-y-5 rounded-2xl bg-white p-6 shadow-lg hover:shadow-xl transition">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-slate-400">Amount</p>
              <p class="text-xl font-bold text-slate-900">KSh {{ formattedNumber(transaction.amount) }}</p>
            </div>
            <span :class="['rounded-full px-3 py-1 text-sm font-semibold', statusBadge(transaction.status)]">
              {{ capitalize(transaction.status) }}
            </span>
          </div>

          <div class="space-y-2 text-sm">
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
        <div class="rounded-2xl bg-white p-6 shadow-lg hover:shadow-xl transition">
          <h3 class="mb-4 text-lg font-semibold text-slate-800">Member Info</h3>
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
        <div class="rounded-2xl bg-white p-6 shadow-lg hover:shadow-xl transition">
          <h3 class="mb-4 text-lg font-semibold text-slate-800">Balance Flow</h3>
          <div class="flex items-center justify-between text-sm">
            <div class="text-center">
              <p class="text-slate-400">Before</p>
              <p class="font-semibold">KSh {{ formattedNumber(transaction.balance_before) }}</p>
            </div>
            <div class="text-lg text-slate-400">→</div>
            <div class="text-center">
              <p class="text-slate-400">Amount</p>
              <p class="font-semibold text-blue-700">+ {{ formattedNumber(transaction.amount) }}</p>
            </div>
            <div class="text-lg text-slate-400">→</div>
            <div class="text-center">
              <p class="text-slate-400">After</p>
              <p class="font-semibold">KSh {{ formattedNumber(transaction.balance_after) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- DESCRIPTION CARD -->
      <div class="rounded-2xl bg-white p-6 shadow-lg hover:shadow-xl transition">
        <h3 class="mb-3 text-lg font-semibold text-slate-800">Description</h3>
        <p class="text-sm text-slate-600">
          {{ transaction.description ?? 'No description provided.' }}
        </p>
      </div>
    </div>

    <!-- MODALS -->
    <div v-if="modals.reject || modals.reverse" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 backdrop-blur-sm p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl space-y-4 animate-fade-in">
        <h3 class="text-lg font-semibold text-slate-900">
          {{ modals.reject ? 'Reject Transaction' : 'Reverse Transaction' }}
        </h3>

        <textarea
          v-model="payload.reason"
          rows="4"
          class="w-full rounded-lg border p-3 focus:ring-2 focus:ring-blue-700 transition"
          placeholder="Enter reason..."
        ></textarea>

        <div class="flex justify-end gap-3">
          <button @click="closeModals" class="rounded-lg border px-4 py-2 hover:bg-slate-100 transition">
            Cancel
          </button>

          <button
            @click="modals.reject ? reject() : reverse()"
            class="rounded-lg bg-blue-700 px-4 py-2 text-white shadow hover:shadow-lg transition hover:bg-blue-800"
          >
            Confirm
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'

const props = defineProps({ transaction: Object })
const transaction = props.transaction

const page = usePage()
const user = page.props.auth?.user

const modals = reactive({ reject: false, reverse: false })
const payload = reactive({ reason: '' })

const memberName = computed(() => {
  if (!transaction.member) return '-'
  return `${transaction.member.first_name ?? ''} ${transaction.member.last_name ?? ''}`
})

function formattedNumber(n) {
  return Number(n ?? 0).toLocaleString()
}

function capitalize(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''
}

function formatDate(d) {
  return d ? new Date(d).toLocaleString() : '-'
}

function statusBadge(status) {
  switch (status) {
    case 'pending': return 'bg-orange-100 text-orange-700'
    case 'completed': return 'bg-blue-100 text-blue-700'
    case 'failed': return 'bg-rose-100 text-rose-700'
    case 'reversed': return 'bg-slate-200 text-slate-700'
    default: return 'bg-slate-200 text-slate-700'
  }
}

function csrf() {
  return document.querySelector('meta[name="csrf-token"]').getAttribute('content')
}

async function approve() {
  await fetch(`/transactions/${transaction.id}/approve`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf() } })
  router.reload()
}

function openReject() { payload.reason = ''; modals.reject = true }
function openReverse() { payload.reason = ''; modals.reverse = true }
function closeModals() { modals.reject = false; modals.reverse = false }

async function reject() {
  await fetch(`/transactions/${transaction.id}/reject`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrf(), 'Content-Type': 'application/json' },
    body: JSON.stringify({ rejection_reason: payload.reason })
  })
  router.reload()
}

async function reverse() {
  await fetch(`/transactions/${transaction.id}/reverse`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrf(), 'Content-Type': 'application/json' },
    body: JSON.stringify({ reversal_reason: payload.reason })
  })
  router.reload()
}

async function deleteTxn() {
  if (!confirm('Delete this transaction?')) return
  await fetch(`/transactions/${transaction.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf() } })
  router.visit('/transactions')
}
</script>

<style>
.animate-fade-in {
  animation: fadeIn 0.25s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>