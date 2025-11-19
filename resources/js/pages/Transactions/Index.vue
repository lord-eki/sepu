<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions' }]">
    <Head title="Transactions" />

    <div class="space-y-8">

      <!-- === STATS CARDS === -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border">
          <p class="text-sm font-medium text-slate-500">Total Transactions</p>
          <p class="mt-2 text-3xl font-semibold">{{ stats.total_transactions ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border">
          <p class="text-sm font-medium text-slate-500">Total Amount</p>
          <p class="mt-2 text-3xl font-semibold">KSh {{ formattedNumber(stats.total_amount ?? 0) }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border">
          <p class="text-sm font-medium text-slate-500">Pending</p>
          <p class="mt-2 text-3xl font-semibold text-amber-600">{{ stats.pending_count ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border">
          <p class="text-sm font-medium text-slate-500">Completed</p>
          <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ stats.completed_count ?? 0 }}</p>
        </div>
      </div>

      <!-- === FILTERS === -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

          <input
            v-model="filters.search"
            @keyup.enter="applyFilters"
            type="text"
            placeholder="Search transaction id / member / ref..."
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-slate-300"
          />

          <select v-model="filters.transaction_type" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-slate-300">
            <option value="">All Types</option>
            <option v-for="(label, key) in transactionTypes" :key="key" :value="key">{{ label }}</option>
          </select>

          <select v-model="filters.status" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-slate-300">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="failed">Failed</option>
            <option value="reversed">Reversed</option>
          </select>

          <div class="flex items-center gap-2">
            <input v-model="filters.start_date" type="date" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-slate-300" />
            <span class="text-slate-400">—</span>
            <input v-model="filters.end_date" type="date" class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-slate-300" />
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex flex-wrap gap-3 pt-2">
          <button @click="applyFilters" class="px-5 py-2.5 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition">
            Apply Filters
          </button>

          <button @click="resetFilters" class="px-5 py-2.5 border rounded-lg hover:bg-slate-50 transition">
            Reset
          </button>

          <inertia-link
            href="/transactions/create"
            class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-500 transition ml-auto"
          >
            New Transaction
          </inertia-link>
        </div>
      </div>

      <!-- === TABLE === -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">#</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Txn ID</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Member</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Account</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Type</th>
              <th class="px-4 py-3 text-right text-sm font-semibold text-slate-700">Amount</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">Status</th>
              <th class="px-4 py-3 text-right text-sm font-semibold text-slate-700">Created</th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-slate-700">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="(t, idx) in pageData"
              :key="t.id"
              class="hover:bg-slate-50 transition"
            >
              <td class="px-4 py-3 text-sm">{{ idx + 1 + (meta.current_page - 1) * meta.per_page }}</td>
              <td class="px-4 py-3 text-sm">{{ t.transaction_id }}</td>

              <td class="px-4 py-3 text-sm">
                <div class="font-medium">{{ t.member?.first_name }} {{ t.member?.last_name }}</div>
                <div class="text-xs text-slate-400">{{ t.member?.membership_id }}</div>
              </td>

              <td class="px-4 py-3 text-sm">{{ t.account?.account_number ?? '-' }}</td>

              <td class="px-4 py-3 text-sm">
                <span
                  :class="['inline-flex px-2 py-1 rounded-full text-xs font-semibold', typeBadge(t.transaction_type)]"
                >
                  {{ transactionTypes[t.transaction_type] ?? t.transaction_type }}
                </span>
              </td>

              <td class="px-4 py-3 text-sm text-right font-medium">
                KSh {{ formattedNumber(t.amount) }}
              </td>

              <td class="px-4 py-3">
                <span :class="['px-2 py-1 rounded-lg text-sm font-medium', statusBadge(t.status)]">
                  {{ capitalize(t.status) }}
                </span>
              </td>

              <td class="px-4 py-3 text-sm text-right">
                {{ formatDate(t.created_at) }}
              </td>

              <td class="px-4 py-3 text-center">
                <div class="flex justify-center gap-2">
                  <Link
                    :href="`/transactions/${t.id}`"
                    class="px-3 py-1.5 bg-slate-100 rounded-md text-sm hover:bg-slate-200 transition"
                  >
                    View
                  </Link>

                  <button
                    v-if="t.status === 'pending'"
                    @click="openApproveModal(t)"
                    class="px-3 py-1.5 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-500 transition"
                  >
                    Approve
                  </button>

                  <button
                    v-if="t.status === 'pending'"
                    @click="openRejectModal(t)"
                    class="px-3 py-1.5 bg-rose-500 text-white rounded-md text-sm hover:bg-rose-400 transition"
                  >
                    Reject
                  </button>

                  <button
                    v-if="t.status === 'completed'"
                    @click="openReverseModal(t)"
                    class="px-3 py-1.5 bg-amber-500 text-white rounded-md text-sm hover:bg-amber-400 transition"
                  >
                    Reverse
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="pageData.length === 0">
              <td colspan="9" class="py-6 text-center text-slate-500">No transactions found.</td>
            </tr>
          </tbody>
        </table>

        <!-- === PAGINATION === -->
        <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
          <div class="text-sm text-slate-500">
            Showing <span class="font-medium">{{ meta.from }}</span> to
            <span class="font-medium">{{ meta.to }}</span> of
            <span class="font-medium">{{ meta.total }}</span>
          </div>

          <div class="flex items-center gap-2">
            <button
              :disabled="!meta.prev_page_url"
              @click="goToPage(meta.current_page - 1)"
              class="px-3 py-1.5 border rounded disabled:opacity-50"
            >
              Prev
            </button>

            <button
              v-for="p in paginationRange"
              :key="p"
              @click="goToPage(p)"
              :class="[
                'px-3 py-1.5 rounded',
                p === meta.current_page
                  ? 'bg-slate-800 text-white'
                  : 'border hover:bg-slate-50'
              ]"
            >
              {{ p }}
            </button>

            <button
              :disabled="!meta.next_page_url"
              @click="goToPage(meta.current_page + 1)"
              class="px-3 py-1.5 border rounded disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      <!-- === MODALS (unchanged logic, improved UI) === -->
      <!-- Approve -->
      <div
        v-if="modals.approve"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      >
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
          <h3 class="text-lg font-semibold mb-3">Approve Transaction</h3>
          <p class="text-sm text-slate-600 mb-4">
            Are you sure you want to approve
            <span class="font-medium">{{ currentTxn.transaction_id }}</span>?
          </p>

          <div class="flex justify-end gap-2">
            <button @click="closeModals" class="px-4 py-2 border rounded hover:bg-slate-50">Cancel</button>
            <button @click="approveTransaction" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-500">
              Approve
            </button>
          </div>
        </div>
      </div>

      <!-- Reject -->
      <div
        v-if="modals.reject"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      >
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
          <h3 class="text-lg font-semibold mb-3">Reject Transaction</h3>
          <p class="text-sm text-slate-600 mb-2">Provide a reason for rejecting <span class="font-medium">{{ currentTxn.transaction_id }}</span></p>

          <textarea v-model="actionPayload.rejection_reason" rows="4" class="w-full border rounded p-2 mb-4"></textarea>

          <div class="flex justify-end gap-2">
            <button @click="closeModals" class="px-4 py-2 border rounded hover:bg-slate-50">Cancel</button>
            <button @click="rejectTransaction" class="px-4 py-2 bg-rose-500 text-white rounded hover:bg-rose-400">Reject</button>
          </div>
        </div>
      </div>

      <!-- Reverse -->
      <div
        v-if="modals.reverse"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
      >
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
          <h3 class="text-lg font-semibold mb-3">Reverse Transaction</h3>
          <p class="text-sm text-slate-600 mb-2">Reason for reversal for <span class="font-medium">{{ currentTxn.transaction_id }}</span></p>

          <textarea v-model="actionPayload.reversal_reason" rows="4" class="w-full border rounded p-2 mb-4"></textarea>

          <div class="flex justify-end gap-2">
            <button @click="closeModals" class="px-4 py-2 border rounded hover:bg-slate-50">Cancel</button>
            <button @click="reverseTransaction" class="px-4 py-2 bg-amber-500 text-white rounded hover:bg-amber-400">Reverse</button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>


<script setup lang="ts">
import { ref, computed, reactive } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Props from Inertia
const page = usePage();
const props = page.props as any;

// Transactions & stats
const stats = computed(() => props.statistics ?? {});
const transactionsProp = computed(() => props.transactions ?? {});

// Pagination helpers
const meta = computed(() => transactionsProp.value.meta ?? {});
const pageData = computed(() => transactionsProp.value.data ?? []);

// Filters
const filters = reactive({
  search: (page.props as any).filters?.search ?? '',
  transaction_type: (page.props as any).filters?.transaction_type ?? '',
  status: (page.props as any).filters?.status ?? '',
  start_date: (page.props as any).filters?.start_date ?? '',
  end_date: (page.props as any).filters?.end_date ?? '',
});

// Transaction type labels
const transactionTypes = {
  deposit: 'Deposit',
  withdrawal: 'Withdrawal',
  transfer: 'Transfer',
  loan_disbursement: 'Loan Disbursement',
  loan_repayment: 'Loan Repayment',
  dividend_payment: 'Dividend Payment',
  fee_payment: 'Fee Payment',
  interest_payment: 'Interest Payment',
};

// Utilities
function formattedNumber(n: number) { return Number(n ?? 0).toLocaleString(); }
function capitalize(s: string) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
function formatDate(d: string) { return d ? new Date(d).toLocaleString() : '-'; }
function typeBadge(type: string) {
  switch (type) {
    case 'deposit': return 'bg-emerald-100 text-emerald-800';
    case 'withdrawal': return 'bg-rose-100 text-rose-800';
    case 'transfer': return 'bg-indigo-100 text-indigo-800';
    case 'loan_disbursement': return 'bg-yellow-50 text-yellow-800';
    default: return 'bg-slate-100 text-slate-800';
  }
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
function goToPage(pageNum: number) { router.get('/transactions', { ...filters, page: pageNum }, { replace: true, preserveState: true }); }
function applyFilters() { router.get('/transactions', { ...filters, page: 1 }, { replace: true, preserveState: true }); }
function resetFilters() { Object.assign(filters, { search: '', transaction_type: '', status: '', start_date: '', end_date: '' }); applyFilters(); }

// Admin modals & actions
const modals = reactive({ approve: false, reject: false, reverse: false });
const currentTxn = reactive<any>({});
const actionPayload = reactive({ rejection_reason: '', reversal_reason: '' });

function openApproveModal(t: any) { Object.assign(currentTxn, t); modals.approve = true; }
function openRejectModal(t: any) { Object.assign(currentTxn, t); actionPayload.rejection_reason = ''; modals.reject = true; }
function openReverseModal(t: any) { Object.assign(currentTxn, t); actionPayload.reversal_reason = ''; modals.reverse = true; }
function closeModals() { modals.approve = modals.reject = modals.reverse = false; Object.keys(currentTxn).forEach(k => delete currentTxn[k]); actionPayload.rejection_reason = ''; actionPayload.reversal_reason = ''; }

function csrfToken() { const m = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement; return m?.content ?? ''; }

// === Axios admin actions ===
async function approveTransaction() {
  try {
    await axios.post(`/transactions/${currentTxn.id}/approve`, {}, { headers: { 'X-CSRF-TOKEN': csrfToken() } });
    closeModals();
    router.reload();
  } catch (e: any) { console.error(e); alert(e.response?.data?.message ?? 'Approve failed'); }
}

async function rejectTransaction() {
  if (!actionPayload.rejection_reason.trim()) return alert('Please provide a rejection reason');
  try {
    await axios.post(`/transactions/${currentTxn.id}/reject`, { rejection_reason: actionPayload.rejection_reason }, { headers: { 'X-CSRF-TOKEN': csrfToken() } });
    closeModals();
    router.reload();
  } catch (e: any) { console.error(e); alert(e.response?.data?.message ?? 'Reject failed'); }
}

async function reverseTransaction() {
  if (!actionPayload.reversal_reason.trim()) return alert('Please provide a reversal reason');
  try {
    await axios.post(`/transactions/${currentTxn.id}/reverse`, { reversal_reason: actionPayload.reversal_reason }, { headers: { 'X-CSRF-TOKEN': csrfToken() } });
    closeModals();
    router.reload();
  } catch (e: any) { console.error(e); alert(e.response?.data?.message ?? 'Reverse failed'); }
}
</script>

