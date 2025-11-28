<template>
  <AppLayout :breadcrumbs="[{ title: 'Transactions' }]">
    <Head title="Transactions" />
    <!-- GLOBAL MESSAGE -->
        <div 
          v-if="message.visible" 
          :class="message.type === 'success' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'" 
          class="fixed top-5 left-1/2 transform -translate-x-1/2 py-3 px-6 rounded shadow-lg z-9999 flex items-center justify-between gap-3 min-w-[250px] max-w-sm transition-opacity duration-300"
        >
          <span>{{ message.text }}</span>
          <button @click="message.visible = false" class="text-gray-500 hover:text-gray-800 font-bold">&times;</button>
        </div>

    <div class="space-y-8 bg-[#F4F6F8] min-h-screen p-2 sm:p-4">

      <!-- === STATS CARDS === -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl p-5 shadow bg-white border-l-4 border-[#0A2342]">
          <p class="text-sm font-medium text-gray-600">Total Transactions</p>
          <p class="mt-2 text-2xl font-bold text-[#0A2342]">{{ stats.total_transactions ?? 0 }}</p>
        </div>

        <div class="rounded-2xl p-5 shadow bg-white border-l-4 border-[#F97316]">
          <p class="text-sm font-medium text-gray-600">Total Amount</p>
          <p class="mt-2 text-2xl font-bold text-[#0A2342]">
            KSh {{ formattedNumber(stats.total_amount ?? 0) }}
          </p>
        </div>

        <div class="rounded-2xl p-5 shadow bg-white border-l-4 border-amber-500">
          <p class="text-sm font-medium text-gray-600">Pending</p>
          <p class="mt-2 text-2xl font-bold text-amber-600">{{ stats.pending_count ?? 0 }}</p>
        </div>

        <div class="rounded-2xl p-5 shadow bg-white border-l-4 border-emerald-500">
          <p class="text-sm font-medium text-gray-600">Completed</p>
          <p class="mt-2 text-2xl font-bold text-emerald-600">{{ stats.completed_count ?? 0 }}</p>
        </div>
      </div>

      <!-- === FILTERS === -->
      <div class="bg-white rounded-2xl p-6 shadow space-y-4 border border-gray-100">
        <h2 class="text-lg font-semibold text-[#0A2342]">Filter Transactions</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <input
            v-model="filters.search"
            @keyup.enter="applyFilters"
            type="text"
            placeholder="Search by ID / member / ref..."
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342] focus:border-[#0A2342]"
          />

          <select
            v-model="filters.transaction_type"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]"
          >
            <option value="">All Types</option>
            <option v-for="(label, key) in transactionTypes" :key="key" :value="key">
              {{ label }}
            </option>
          </select>

          <select
            v-model="filters.status"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]"
          >
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
            <option value="failed">Failed</option>
            <option value="reversed">Reversed</option>
          </select>

          <div class="col-span-1 sm:col-span-2 lg:col-span-2 flex flex-col sm:flex-row gap-2">
            <input
              v-model="filters.start_date"
              type="date"
              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]"
            />
            <span class="text-gray-400 hidden sm:flex items-center">—</span>
            <input
              v-model="filters.end_date"
              type="date"
              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0A2342]"
            />
          </div>


        </div>

        <!-- Buttons -->
        <div class="flex flex-wrap gap-3 pt-2">
          <button
            @click="applyFilters"
            class="px-5 py-2.5 bg-[#0A2342] text-white rounded-lg hover:bg-[#0c2d54] transition"
          >
            Apply Filters
          </button>

          <button
            @click="resetFilters"
            class="px-5 py-2.5 border rounded-lg hover:bg-gray-50 transition"
          >
            Reset
          </button>

           <button
            @click=""
            class="px-5 py-2.5 bg-orange-300 border rounded-lg hover:bg-orange-400 transition"
          >
            Export
          </button>

          <Link-Inertia
            href="/transactions/create"
            class="px-5 py-2.5 bg-[#F97316] text-white rounded-lg hover:bg-orange-500 transition ml-auto"
          >
            New Transaction
          </Link-Inertia>
        </div>
      </div>

     <!-- === TABLE === -->
      <div class="bg-white rounded-2xl p-6 shadow border border-gray-100 overflow-x-auto w-full">
        <table class="min-w-max divide-y divide-gray-200">
          <thead class="bg-[#0A2342]/10">
            <tr>
              <th class="px-4 py-3 text-left text-sm font-semibold text-[#0A2342]">#</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-[#0A2342]">Txn ID</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-[#0A2342]">Member</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-[#0A2342]">Account</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-[#0A2342]">Type</th>
              <th class="px-4 py-3 text-right text-sm font-semibold text-[#0A2342]">Amount</th>
              <th class="px-4 py-3 text-left text-sm font-semibold text-[#0A2342]">Status</th>
              <th class="px-4 py-3 text-right text-sm font-semibold text-[#0A2342]">Created</th>
              <th class="px-4 py-3 text-center text-sm font-semibold text-[#0A2342]">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="(t, idx) in pageData"
              :key="t.id"
              class="hover:bg-[#0A2342]/5 transition"
            >
              <td class="px-4 py-3 text-sm">
                {{ idx + 1 + ((meta?.current_page ?? 1) - 1) * (meta?.per_page ?? pageData.length) }}
              </td>

              <td class="px-4 py-3 text-sm">{{ t.transaction_id }}</td>

              <td class="px-4 py-3 text-sm">
                <div class="font-medium text-[#0A2342]">
                  {{ t.member?.first_name }} {{ t.member?.last_name }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ t.member?.membership_id }}
                </div>
              </td>

              <td class="px-2 py-3 text-sm">
                {{ t.account?.account_number ?? '-' }}
              </td>

              <td class="px-2 py-3 text-sm">
                <span
                  :class="[
                    'inline-flex px-2 py-1 rounded-full text-xs font-semibold',
                    typeBadge(t.transaction_type)
                  ]"
                >
                  {{ transactionTypes[t.transaction_type] ?? t.transaction_type }}
                </span>
              </td>

              <td class="px-2 py-3 text-sm text-right font-semibold">
                KSh {{ formattedNumber(t.amount) }}
              </td>

              <td class="px-4 py-3">
                <span
                  :class="[
                    'px-2 py-1 rounded-lg text-xs font-semibold',
                    statusBadge(t.status)
                  ]"
                >
                  {{ capitalize(t.status) }}
                </span>
              </td>

              <td class="px-4 py-3 text-sm text-right">
                {{ formatDate(t.created_at) }}
              </td>

              <td class="px-4 py-3 text-center">
                <div class="flex justify-center gap-2">
                  <!-- VIEW -->
                  <Link-Inertia
                    :href="`/transactions/${t.id}`"
                    class="px-3 py-1.5 bg-gray-100 text-[#0A2342] rounded-md text-sm hover:bg-gray-200 transition"
                  >
                    View
                  </Link-Inertia>

                  <!-- APPROVE -->
                  <button
                    v-if="t.status === 'pending'"
                    @click="openApproveModal(t)"
                    class="px-3 py-1.5 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-500 transition"
                  >
                    Approve
                  </button>

                  <!-- REJECT -->
                  <button
                    v-if="t.status === 'pending'"
                    @click="openRejectModal(t)"
                    class="px-3 py-1.5 bg-rose-500 text-white rounded-md text-sm hover:bg-rose-400 transition"
                  >
                    Reject
                  </button>

                  <!-- REVERSE -->
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
              <td colspan="9" class="py-6 text-center text-gray-500">
                No transactions found.
              </td>
            </tr>
          </tbody>
        </table>

      
        <!-- PAGINATION -->
        <Pagination :data="transactionsProp" @page-changed="goToPage" />

      </div>

      <!-- === MODALS UPDATED UI === -->

      <!-- APPROVE -->
      <div v-if="modals.approve"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-lg">
          <h3 class="text-lg font-semibold text-[#0A2342] mb-3">Approve Transaction</h3>
          <p class="text-sm text-gray-600 mb-4">
            Are you sure you want to approve
            <span class="font-medium">{{ currentTxn.transaction_id }}</span>?
          </p>

          <div class="flex justify-end gap-2">
            <button @click="closeModals" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
            <button 
                  @click="approveTransaction" 
                  class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-500 flex items-center gap-2"
                  :disabled="loading"
                >
                  <span v-if="loading && modals.approve" class="animate-spin">⏳</span>
                  <span>{{ loading && modals.approve ? 'Approving...' : 'Approve' }}</span>
                </button>
          </div>
        </div>
      </div>

      <!-- REJECT -->
      <div v-if="modals.reject"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-lg">
          <h3 class="text-lg font-semibold text-[#0A2342] mb-3">Reject Transaction</h3>
          <p class="text-sm text-gray-600 mb-2">
            Provide a reason for rejecting
            <span class="font-semibold">{{ currentTxn.transaction_id }}</span>
          </p>

          <textarea v-model="actionPayload.rejection_reason" rows="4" class="w-full border rounded p-2 mb-4"></textarea>

          <div class="flex justify-end gap-2">
            <button @click="closeModals" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
            <button 
                @click="rejectTransaction" 
                class="px-4 py-2 bg-rose-500 text-white rounded hover:bg-rose-400 flex items-center gap-2"
                :disabled="loading"
              >
                <span v-if="loading && modals.reject" class="animate-spin">⏳</span>
                <span>{{ loading && modals.reject ? 'Rejecting...' : 'Reject' }}</span>
              </button>
          </div>
        </div>
      </div>

      <!-- REVERSE -->
      <div v-if="modals.reverse"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-lg">
          <h3 class="text-lg font-semibold text-[#0A2342] mb-3">Reverse Transaction</h3>
          <p class="text-sm text-gray-600 mb-2">
            Reason for reversal for
            <span class="font-semibold">{{ currentTxn.transaction_id }}</span>
          </p>

          <textarea v-model="actionPayload.reversal_reason" rows="4" class="w-full border rounded p-2 mb-4"></textarea>

          <div class="flex justify-end gap-2">
            <button @click="closeModals" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
            <button 
                @click="reverseTransaction" 
                class="px-4 py-2 bg-amber-500 text-white rounded hover:bg-amber-400 flex items-center gap-2"
                :disabled="loading"
              >
                <span v-if="loading && modals.reverse" class="animate-spin">⏳</span>
                <span>{{ loading && modals.reverse ? 'Reversing...' : 'Reverse' }}</span>
              </button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>


<script setup lang="ts">
import { ref, computed, reactive } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue'; 
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
  search: props.filters?.search ?? '',
  transaction_type: props.filters?.transaction_type ?? '',
  status: props.filters?.status ?? '',
  start_date: props.filters?.start_date ?? '',
  end_date: props.filters?.end_date ?? '',
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

// Loader & message UI
const loading = ref(false);
const message = reactive({ text: '', type: 'success', visible: false });

function showMessage(text: string, type: 'success' | 'error' = 'success') {
  message.text = text;
  message.type = type;
  message.visible = true;
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

function goToPage(pageNum: number) {
  router.get('/transactions', { ...filters, page: pageNum }, { replace: true, preserveState: true });
}

function applyFilters() {
  router.get('/transactions', { ...filters, page: 1 }, { replace: true, preserveState: true });
}

function resetFilters() {
  Object.assign(filters, { search: '', transaction_type: '', status: '', start_date: '', end_date: '' });
  applyFilters();
}

// Admin modals & actions
const modals = reactive({ approve: false, reject: false, reverse: false });
const currentTxn = reactive<any>({});
const actionPayload = reactive({ rejection_reason: '', reversal_reason: '' });

function openApproveModal(t: any) { Object.assign(currentTxn, t); modals.approve = true; }
function openRejectModal(t: any) { Object.assign(currentTxn, t); actionPayload.rejection_reason = ''; modals.reject = true; }
function openReverseModal(t: any) { Object.assign(currentTxn, t); actionPayload.reversal_reason = ''; modals.reverse = true; }
function closeModals() { 
  modals.approve = modals.reject = modals.reverse = false; 
  Object.keys(currentTxn).forEach(k => delete currentTxn[k]); 
  actionPayload.rejection_reason = ''; 
  actionPayload.reversal_reason = ''; 
}

function csrfToken() { 
  const m = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement; 
  return m?.content ?? ''; 
}

// === Axios admin actions with loader & message UI ===
async function approveTransaction() {
  loading.value = true;
  try {
    const { data } = await axios.post(`/transactions/${currentTxn.id}/approve`, {}, { headers: { 'X-CSRF-TOKEN': csrfToken() } });
    showMessage(data.message ?? 'Transaction approved successfully!', 'success');
    closeModals();
    await fetchTransactions();
  } catch (e: any) { 
    console.error(e); 
    showMessage(e.response?.data?.message ?? 'Approve failed', 'error'); 
  } finally { loading.value = false; }
}

async function rejectTransaction() {
  if (!actionPayload.rejection_reason.trim()) return showMessage('Please provide a rejection reason', 'error');
  loading.value = true;
  try {
    const { data } = await axios.post(`/transactions/${currentTxn.id}/reject`, { rejection_reason: actionPayload.rejection_reason }, { headers: { 'X-CSRF-TOKEN': csrfToken() } });
    showMessage(data.message ?? 'Transaction rejected successfully!', 'success');
    closeModals();
    await fetchTransactions();
  } catch (e: any) { 
    console.error(e); 
    showMessage(e.response?.data?.message ?? 'Reject failed', 'error'); 
  } finally { loading.value = false; }
}

async function reverseTransaction() {
  if (!actionPayload.reversal_reason.trim()) return showMessage('Please provide a reversal reason', 'error');
  loading.value = true;
  try {
    const { data } = await axios.post(`/transactions/${currentTxn.id}/reverse`, { reversal_reason: actionPayload.reversal_reason }, { headers: { 'X-CSRF-TOKEN': csrfToken() } });
    showMessage(data.message ?? 'Transaction reversed successfully!', 'success');
    closeModals();
    await fetchTransactions();
  } catch (e: any) { 
    console.error(e); 
    showMessage(e.response?.data?.message ?? 'Reverse failed', 'error'); 
  } finally { loading.value = false; }
}

// Fetch transactions via Axios for table reload after action
async function fetchTransactions(page = 1) {
  try {
    const { data } = await axios.get('/transactions', { params: { ...filters, page } });
    if (data.data) {
      transactionsProp.value.data = data.data.data;
      transactionsProp.value.meta = data.data.meta;
    }
  } catch (e) { console.error(e); }
}
</script>


<style scoped>
button:hover {
cursor: pointer;
}
</style>


