<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions', href: '/transactions' }, { title: transaction.transaction_id ?? 'Transaction' }]">
    <Head :title="transaction.transaction_id ? `Transaction ${transaction.transaction_id}` : 'Transaction'" />

    <div class="space-y-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Summary Card -->
        <div class="col-span-1 bg-white rounded-2xl p-6 shadow">
          <div class="flex items-start justify-between">
            <div>
              <h2 class="text-xl font-semibold">{{ transaction.transaction_id }}</h2>
              <p class="text-sm text-slate-500 mt-1">{{ transaction.reference_number ?? '-' }}</p>
            </div>

            <div>
              <span :class="['px-3 py-1 rounded-lg text-sm font-semibold', statusBadge(transaction.status)]">
                {{ capitalize(transaction.status) }}
              </span>
            </div>
          </div>

          <div class="mt-6 space-y-3">
            <div>
              <p class="text-xs text-slate-400">Amount</p>
              <p class="text-2xl font-semibold">KSh {{ formattedNumber(transaction.amount) }}</p>
            </div>

            <div>
              <p class="text-xs text-slate-400">Type</p>
              <p class="font-medium">{{ transactionTypes[transaction.transaction_type] ?? transaction.transaction_type }}</p>
            </div>

            <div>
              <p class="text-xs text-slate-400">Payment Method</p>
              <p class="font-medium">{{ transaction.payment_method ?? '-' }}</p>
            </div>

            <div>
              <p class="text-xs text-slate-400">Processed By</p>
              <p class="font-medium">{{ transaction.processed_by_name ?? (transaction.processedBy?.name ?? '-') }}</p>
            </div>

            <div>
              <p class="text-xs text-slate-400">Created</p>
              <p class="font-medium">{{ formatDateFull(transaction.created_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Right: Details -->
        <div class="col-span-2 space-y-6">
          <div class="bg-white rounded-2xl p-6 shadow">
            <h3 class="text-lg font-semibold mb-3">Transaction Details</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <DetailRow label="Member" :value="memberName" />
              <DetailRow label="Membership ID" :value="transaction.member?.membership_id ?? '-'"/>
              <DetailRow label="Account" :value="transaction.account?.account_number ?? '-'"/>
              <DetailRow label="Balance Before" :value="`KSh ${formattedNumber(transaction.balance_before ?? 0)}`" />
              <DetailRow label="Balance After" :value="`KSh ${formattedNumber(transaction.balance_after ?? 0)}`" />
              <DetailRow label="Payment Reference" :value="transaction.payment_reference ?? '-'" />
              <div class="md:col-span-2">
                <p class="text-xs text-slate-400">Description</p>
                <p class="mt-1 text-sm">{{ transaction.description ?? '-' }}</p>
              </div>
            </div>
          </div>

          <!-- Admin Actions -->
          <div v-if="isAdmin" class="bg-white rounded-2xl p-6 shadow flex items-center gap-3">
            <button v-if="transaction.status === 'pending'" @click="approve" class="px-4 py-2 bg-emerald-600 text-white rounded">Approve</button>
            <button v-if="transaction.status === 'pending'" @click="openReject" class="px-4 py-2 bg-rose-500 text-white rounded">Reject</button>
            <button v-if="transaction.status === 'completed'" @click="openReverse" class="px-4 py-2 bg-amber-500 text-white rounded">Reverse</button>
            <button v-if="transaction.status === 'pending'" @click="deleteTxn" class="px-4 py-2 border rounded">Delete</button>
          </div>
        </div>
      </div>

      <!-- Reject Modal -->
      <div v-if="modals.reject" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md">
          <h3 class="text-lg font-semibold mb-3">Reject Transaction</h3>
          <p class="text-sm text-slate-600 mb-2">Provide a reason for rejecting <span class="font-medium">{{ transaction.transaction_id }}</span></p>

          <textarea v-model="payload.rejection_reason" rows="4" class="w-full border rounded p-2 mb-4" placeholder="Rejection reason"></textarea>

          <div class="flex justify-end gap-2">
            <button @click="closeModals" class="px-4 py-2 border rounded">Cancel</button>
            <button @click="reject" class="px-4 py-2 bg-rose-500 text-white rounded">Reject</button>
          </div>
        </div>
      </div>

      <!-- Reverse Modal -->
      <div v-if="modals.reverse" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md">
          <h3 class="text-lg font-semibold mb-3">Reverse Transaction</h3>
          <p class="text-sm text-slate-600 mb-2">Reason for reversal for <span class="font-medium">{{ transaction.transaction_id }}</span></p>

          <textarea v-model="payload.reversal_reason" rows="4" class="w-full border rounded p-2 mb-4" placeholder="Reversal reason"></textarea>

          <div class="flex justify-end gap-2">
            <button @click="closeModals" class="px-4 py-2 border rounded">Cancel</button>
            <button @click="reverse" class="px-4 py-2 bg-amber-500 text-white rounded">Reverse</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
// import DetailRow from '@/components/DetailRow.vue';
import { useRoute } from 'vue-router';

// fallback detail row if you don't have one
// You can either create /Components/DetailRow.vue or the following thin local component:
const hasDetailRow = true;
try {
  // attempt to import - if not present, will still use fallback
} catch (e) {
  // ignore
}

// Props & route
const route = useRoute();
const params: any = route.params || {};
const transactionIdParam = params.id ?? null;

// reactive transaction
const transaction = reactive<any>({});
const loading = ref(true);

// page-level flags (you can replace isAdmin detection with actual user role from Inertia props)
const page = (window as any).__INITIAL_PAGE__ || (window as any).page || {};
const inertiaProps = (page.props ?? {});
const currentUser = inertiaProps.auth?.user ?? null;
const isAdmin = computed(() => {
  // Replace this with your actual role-check e.g. currentUser?.role === 'admin'
  return currentUser?.is_admin ?? currentUser?.role === 'admin' ?? true;
});

const transactionTypes: Record<string, string> = {
  deposit: 'Deposit',
  withdrawal: 'Withdrawal',
  transfer: 'Transfer',
  loan_disbursement: 'Loan Disbursement',
  loan_repayment: 'Loan Repayment',
  dividend_payment: 'Dividend Payment',
  fee_payment: 'Fee Payment',
  interest_payment: 'Interest Payment',
};

// utilities
function formattedNumber(n: number) {
  return Number(n ?? 0).toLocaleString();
}
function capitalize(s: string) {
  if (!s) return '';
  return s.charAt(0).toUpperCase() + s.slice(1);
}
function formatDateFull(d: string) {
  if (!d) return '-';
  const dt = new Date(d);
  return dt.toLocaleString();
}
function statusBadge(status: string) {
  switch (status) {
    case 'pending': return 'bg-amber-100 text-amber-700';
    case 'completed': return 'bg-emerald-100 text-emerald-700';
    case 'failed': return 'bg-rose-100 text-rose-700';
    case 'reversed': return 'bg-slate-100 text-slate-700';
    default: return 'bg-slate-100 text-slate-700';
  }
}

const modals = reactive({ reject: false, reverse: false });
const payload = reactive({ rejection_reason: '', reversal_reason: '' });

// compute member display
const memberName = computed(() => {
  if (!transaction.member) return '-';
  return `${transaction.member.first_name ?? ''} ${transaction.member.last_name ?? ''}`.trim();
});

// fetch transaction by id (controller returns JSON in your controller's show())
async function fetchTransaction(id: string | number) {
  try {
    loading.value = true;
    const res = await fetch(`/transactions/${id}`, {
      headers: { 'Accept': 'application/json' },
    });
    const json = await res.json();
    if (json?.success && json?.data) {
      Object.assign(transaction, json.data);
    } else {
      console.error('Failed to load transaction', json);
      // optionally redirect back
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  // try to get id either from route or from Inertia props (if provided)
  const id = transactionIdParam ?? (inertiaProps?.transaction?.id ?? null);
  if (!id) {
    // fallback: try parsing from URL (last segment)
    const parts = window.location.pathname.split('/');
    const last = parts[parts.length - 1];
    fetchTransaction(last);
  } else {
    fetchTransaction(id);
  }
});

// Admin action helpers (CSRF)
function csrfToken() {
  const m = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
  return m?.content ?? '';
}

async function approve() {
  if (!transaction.id) return;
  try {
    await fetch(`/transactions/${transaction.id}/approve`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
    }).then(r => r.json());

    // refresh
    await fetchTransaction(transaction.id);
  } catch (e) {
    console.error(e);
    alert('Approve failed');
  }
}

function openReject() {
  payload.rejection_reason = '';
  modals.reject = true;
}
function openReverse() {
  payload.reversal_reason = '';
  modals.reverse = true;
}
function closeModals() {
  modals.reject = modals.reverse = false;
  payload.rejection_reason = '';
  payload.reversal_reason = '';
}

async function reject() {
  if (!transaction.id) return;
  if (!payload.rejection_reason.trim()) return alert('Provide rejection reason');
  try {
    await fetch(`/transactions/${transaction.id}/reject`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ rejection_reason: payload.rejection_reason }),
    }).then(r => r.json());

    closeModals();
    await fetchTransaction(transaction.id);
  } catch (e) {
    console.error(e);
    alert('Reject failed');
  }
}

async function reverse() {
  if (!transaction.id) return;
  if (!payload.reversal_reason.trim()) return alert('Provide reversal reason');
  try {
    await fetch(`/transactions/${transaction.id}/reverse`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ reversal_reason: payload.reversal_reason }),
    }).then(r => r.json());

    closeModals();
    await fetchTransaction(transaction.id);
  } catch (e) {
    console.error(e);
    alert('Reverse failed');
  }
}

async function deleteTxn() {
  if (!transaction.id) return;
  if (!confirm('Delete this pending transaction? This action cannot be undone.')) return;
  try {
    await fetch(`/transactions/${transaction.id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
    }).then(r => r.json());

    // navigate back to transactions list
    router.visit('/transactions');
  } catch (e) {
    console.error(e);
    alert('Delete failed');
  }
}
</script>

<!-- Optional: simple DetailRow component fallback -->
<script lang="ts">
/* If you don't have a DetailRow component, create one quickly at
   resources/js/Components/DetailRow.vue with this content:

<template>
  <div>
    <p class="text-xs text-slate-400">{{ label }}</p>
    <p class="mt-1">{{ value }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{ label: string; value: string | number }>();
</script>
*/
</script>
