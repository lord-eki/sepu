<template>
    <AppLayout :breadcrumbs="[{ title: 'Loans', href: isMemberRole ? '/my-loans' : route('loans.index') }, { title: 'Loan Details' }]">
        <Head title="Loan Details" />

        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-3 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-3"
        >
            <div
                v-if="visible"
                class="fixed top-5 left-1/2 z-[9999] flex w-[92%] max-w-md -translate-x-1/2 items-start gap-3 rounded-2xl border border-white/60 bg-white/90 px-5 py-4 shadow-2xl backdrop-blur-xl"
            >
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl font-bold text-white shadow"
                    :class="type === 'success' ? 'bg-emerald-500' : type === 'error' ? 'bg-rose-500' : 'bg-slate-500'"
                >
                    {{ type === 'success' ? '✓' : '!' }}
                </div>

                <div class="flex-1">
                    <p class="text-sm font-semibold text-slate-800">{{ message }}</p>
                </div>

                <button @click="close" class="text-slate-400 hover:text-slate-800">✕</button>
            </div>
        </transition>

        <!-- ================= PAGE ================= -->
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50">
            <!-- ================= HERO HEADER ) ================= -->
            <div class="border-b border-white/50 bg-white/70 backdrop-blur-xl">
                <div class="mx-auto max-w-7xl px-6 py-5 sm:py-7">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <!-- LEFT -->
                        <div class="flex items-start gap-3 sm:gap-4">
                            <div
                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 shadow-xl sm:h-12 sm:w-12 sm:rounded-3xl"
                            >
                                <svg class="h-6 w-6 text-white sm:h-7 sm:w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3" />
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <p class="mb-1 text-[10px] tracking-[0.2em] text-slate-400 uppercase sm:text-xs">Loan Account</p>

                                <h1 class="text-lg font-black break-words text-slate-900 sm:text-2xl">
                                    {{ loan.loan_number }}
                                </h1>

                                <div class="mt-2 flex flex-wrap gap-2 sm:mt-3">
                                    <span class="rounded-full bg-blue-100 px-2 py-1 text-[10px] font-semibold text-blue-700 sm:px-3 sm:text-xs">
                                        {{ loan.loan_product?.name || 'Loan Product' }}
                                    </span>

                                    <span class="rounded-full bg-slate-200 px-2 py-1 text-[10px] font-semibold text-slate-700 sm:px-3 sm:text-xs">
                                        Applied {{ formatDate(loan.application_date || loan.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT -->
                        <div class="flex w-full items-center justify-between gap-3 lg:flex-col lg:items-end">
                            <!-- Member info (left on mobile) -->
                            <div class="min-w-0">
                                <p class="text-[10px] tracking-[0.2em] text-slate-400 uppercase sm:text-xs">Member</p>

                                <h2 class="mt-1 truncate text-base font-bold text-slate-900 sm:text-xl">
                                    {{ loan.member?.first_name }} {{ loan.member?.last_name }}
                                </h2>
                            </div>

                            <!-- Back button (right on mobile) -->
                            <div class="flex-shrink-0">
                                <Link
                                    :href="isMemberRole ? route('my-loans') : route('loans.index')"
                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 sm:rounded-2xl sm:px-4 sm:text-sm"
                                >
                                    Back
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= STATUS BAR ================= -->
            <div class="mx-auto max-w-7xl px-6 pt-2">
                <div
                    class="flex flex-col justify-between gap-4 rounded-3xl border border-white bg-white/90 p-5 shadow-xl backdrop-blur-xl md:flex-row md:items-center"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl text-lg font-bold"
                            :class="getStatusBannerClass(loan.status)"
                        >
                            !
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Current Status</p>
                            <p class="text-sm font-bold text-slate-900">
                                {{ formatStatus(loan.status) }}
                            </p>
                        </div>
                    </div>

                    <p class="max-w-xl text-sm text-slate-500">
                        {{ getStatusDescription(loan.status) }}
                    </p>
                </div>
            </div>

            <!-- ================= QUICK ACTIONS ================= -->
            <div v-if="hasQuickActions" class="mx-auto mt-6 max-w-7xl px-6">
                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                    <h3 class="mb-4 font-semibold text-slate-900">Quick Actions</h3>

                    <div class="flex flex-wrap gap-3">
                        <button
                            v-if="canApprove"
                            @click="openApprovalModal"
                            class="rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700"
                        >
                            Approve
                        </button>

                        <button
                            v-if="canReject"
                            @click="openRejectModal"
                            class="rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-rose-700"
                        >
                            Reject
                        </button>

                        <button
                            v-if="canDisburse"
                            @click="openDisbursementModal"
                            class="rounded-2xl bg-blue-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-800"
                        >
                            Disburse
                        </button>

                        <Link
                            v-if="canViewSchedule"
                            :href="route('loans.schedule', loan.id)"
                            class="rounded-2xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200"
                        >
                            Schedule
                        </Link>

                        <Link
                            v-if="canViewRepayments"
                            :href="route('loans.repayments', loan.id)"
                            class="rounded-2xl bg-orange-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-orange-600"
                        >
                            Repayments
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ================= BODY ================= -->
            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-6 py-8 xl:grid-cols-3">
                <!-- LEFT -->
                <div class="space-y-6 xl:col-span-2">
                    <!-- LOAN INFO -->
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-lg font-bold text-slate-900">Loan Information</h2>

                            <span class="text-xs tracking-widest text-slate-400 uppercase"> Overview </span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs text-slate-500">Applied Amount</p>
                                <p class="mt-1 text-xl font-bold text-slate-900">KES {{ formatCurrency(loan.applied_amount) }}</p>
                            </div>

                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs text-blue-600">Approved Amount</p>
                                <p class="mt-1 text-xl font-bold text-blue-800">KES {{ formatCurrency(loan.approved_amount) }}</p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs text-emerald-600">Disbursed Amount</p>
                                <p class="mt-1 text-xl font-bold text-emerald-700">KES {{ formatCurrency(loan.disbursed_amount) }}</p>
                            </div>

                            <div class="rounded-2xl bg-orange-50 p-4">
                                <p class="text-xs text-orange-600">Interest Rate</p>
                                <p class="mt-1 text-xl font-bold text-orange-700">{{ loan.interest_rate }}%</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs text-slate-500">Repayment Term</p>
                                <p class="mt-1 text-xl font-bold text-slate-900">{{ loan.term_months }} Months</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs text-slate-500">Purpose</p>
                                <p class="mt-1 font-semibold text-slate-900">
                                    {{ loan.purpose || 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- GUARANTORS -->
                    <div v-if="loan.guarantors?.length" class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h2 class="mb-5 text-lg font-bold text-slate-900">Guarantors</h2>

                        <div class="space-y-3">
                            <div
                                v-for="g in loan.guarantors"
                                :key="g.id"
                                class="flex items-center justify-between rounded-2xl border border-slate-100 p-4"
                            >
                                <div>
                                    <p class="font-semibold text-slate-900">
                                        {{ g.guarantor_member.first_name }}
                                        {{ g.guarantor_member.last_name }}
                                    </p>

                                    <p class="text-sm text-slate-500">
                                        {{ g.guarantor_member.membership_id }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="font-bold text-slate-900">KES {{ formatCurrency(g.guaranteed_amount) }}</p>

                                    <span class="text-xs text-slate-500">
                                        {{ g.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUPPORTING DOCUMENTS -->
                <div v-if="loan.documents?.length" class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                    <!-- HEADER -->
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-900">Supporting Documents</h2>

                        <span class="text-xs tracking-widest text-slate-400 uppercase"> {{ loan.documents.length }} File(s) </span>
                    </div>

                    <div class="space-y-3">
                        <!-- PREVIEW (first 3) -->
                        <div
                            v-for="(doc, index) in loan.documents.slice(0, 3)"
                            :key="index"
                            class="flex items-center justify-between rounded-2xl border border-slate-200 p-4 transition hover:bg-slate-50"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700">📄</div>

                                <div>
                                    <p class="font-semibold text-slate-900">
                                        {{ doc.name || `Document ${index + 1}` }}
                                    </p>
                                    <p class="text-sm text-slate-500">Supporting attachment</p>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a
                                    :href="doc.url || doc"
                                    target="_blank"
                                    class="rounded-xl bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700"
                                >
                                    View
                                </a>

                                <a
                                    :href="doc.url || doc"
                                    download
                                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
                                >
                                    Download
                                </a>
                            </div>
                        </div>

                        <!-- VIEW ALL BUTTON -->
                        <div v-if="loan.documents.length > 3" class="flex justify-center pt-2">
                            <button @click="showDocsModal = true" class="rounded-xl bg-slate-900 px-5 py-2 text-sm text-white hover:bg-slate-800">
                                View All Documents ({{ loan.documents.length }})
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ===================== -->
                <!-- DOCUMENTS MODAL -->
                <!-- ===================== -->
                <div v-if="showDocsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-lg">
                        <!-- CLOSE -->
                        <button @click="showDocsModal = false" class="absolute top-3 right-3 text-slate-500 hover:text-slate-800">✕</button>

                        <h2 class="mb-4 text-lg font-bold text-slate-900">All Supporting Documents</h2>

                        <!-- LIST -->
                        <div class="max-h-[70vh] space-y-3 overflow-y-auto">
                            <div
                                v-for="(doc, index) in loan.documents"
                                :key="index"
                                class="flex items-center justify-between rounded-2xl border p-4 transition hover:bg-slate-50"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700">📄</div>

                                    <div>
                                        <p class="font-semibold text-slate-900">
                                            {{ doc.name || `Document ${index + 1}` }}
                                        </p>
                                        <p class="text-sm text-slate-500">Supporting attachment</p>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <a
                                        :href="route('loans.documents.show', [loan.id, index])"
                                        target="_blank"
                                        class="rounded-xl bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700"
                                    >
                                        View
                                    </a>

                                    <a
                                        :href="route('loans.documents.show', [loan.id, index]) + '?download=1'"
                                        class="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
                                    >
                                        Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h2 class="mb-5 text-lg font-bold text-slate-900">Balance Summary</h2>

                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Outstanding</span>
                                <span class="font-bold text-rose-600"> KES {{ formatCurrency(loan.outstanding_balance) }} </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-slate-500">Principal</span>
                                <span class="font-semibold text-slate-900"> KES {{ formatCurrency(loan.principal_balance) }} </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-slate-500">Interest</span>
                                <span class="font-semibold text-slate-900"> KES {{ formatCurrency(loan.interest_balance) }} </span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                        <h2 class="mb-5 text-lg font-bold text-slate-900">Charges & Fees</h2>

                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Processing Fee</span>
                                <span class="font-semibold text-slate-900"> KES {{ formatCurrency(loan.processing_fee) }} </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-slate-500">Insurance Fee</span>
                                <span class="font-semibold text-slate-900"> KES {{ formatCurrency(loan.insurance_fee) }} </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showApprovalModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
                <h2 class="mb-5 text-xl font-bold text-slate-900">Approve Loan</h2>

                <div class="mb-4 rounded-2xl bg-slate-50 p-4">
                    <p class="text-sm text-slate-500">Applied Amount</p>
                    <p class="text-2xl font-bold text-slate-900">KES {{ formatCurrency(loan.applied_amount) }}</p>
                </div>

                <div class="mb-4">
                    <label class="text-sm font-semibold text-slate-700"> Amount To Approve </label>

                    <input
                        v-model="approvalForm.approved_amount"
                        type="number"
                        class="mt-2 w-full rounded-2xl border p-3"
                        placeholder="Enter approved amount"
                    />
                </div>

                <div class="mb-4 rounded-2xl bg-blue-50 p-4">
                    <p class="text-sm text-blue-600">Monthly Repayment</p>
                    <p class="text-xl font-bold text-blue-800">KES {{ formatCurrency(monthlyPreview) }}</p>
                </div>

                <div class="mb-4 rounded-2xl bg-orange-50 p-4">
                    <p class="text-sm text-orange-600">Total Interest</p>
                    <p class="text-xl font-bold text-orange-700">KES {{ formatCurrency(totalInterestPreview) }}</p>
                </div>

                <div class="mb-4 rounded-2xl bg-slate-50 p-4">
                    <p class="text-sm text-slate-600">Net Disbursement</p>
                    <p class="text-xl font-bold text-slate-900">KES {{ formatCurrency(netDisbursementPreview) }}</p>
                </div>

                <textarea
                    v-model="approvalForm.approval_notes"
                    class="mb-5 w-full rounded-2xl border p-3"
                    placeholder="Approval Notes (optional)"
                ></textarea>

                <div class="flex justify-end gap-3">
                    <button @click="closeApprovalModal" class="rounded-xl border px-4 py-2">Cancel</button>

                    <button
                        @click="approveLoan"
                        :disabled="loading.approve"
                        class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2 text-white"
                    >
                        <span v-if="loading.approve" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        <span>{{ loading.approve ? 'Processing...' : 'Approve Loan' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showRejectionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <h2 class="mb-4 text-xl font-bold text-red-600">Reject Loan</h2>

                <textarea
                    v-model="rejectionForm.rejection_reason"
                    required
                    class="mb-4 w-full rounded-2xl border p-3"
                    placeholder="Reason for rejection"
                ></textarea>

                <div class="flex justify-end gap-3">
                    <button @click="closeRejectModal" class="rounded-xl border px-4 py-2">Cancel</button>

                    <button @click="rejectLoan" :disabled="loading.reject" class="flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2 text-white">
                        <span v-if="loading.reject" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        <span>{{ loading.reject ? 'Processing...' : 'Reject' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showDisbursementModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl">
                <h2 class="mb-5 text-xl font-bold text-slate-900">Disburse Loan</h2>

                <div class="mb-4 rounded-2xl bg-blue-50 p-4">
                    <p class="text-sm text-blue-600">Approved Amount</p>
                    <p class="text-2xl font-bold text-blue-700">KES {{ formatCurrency(loan.approved_amount) }}</p>
                </div>

                <div class="mb-6 rounded-2xl bg-emerald-50 p-4">
                    <p class="text-sm text-emerald-600">Net Disbursement</p>
                    <p class="text-2xl font-bold text-emerald-700">
                        KES
                        {{
                            formatCurrency(
                                loan.approved_amount -
                                    ((loan.approved_amount * (loan.loan_product?.processing_fee_rate || 0)) / 100 +
                                        (loan.approved_amount * (loan.loan_product?.insurance_rate || 0)) / 100),
                            )
                        }}
                    </p>
                </div>

                <select
                    v-model="disbursementForm.disbursement_method"
                    class="mb-4 w-full rounded-2xl border p-3 outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Select Method</option>
                    <option value="cash">Cash</option>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>

                <input
                    v-model="disbursementForm.disbursement_reference"
                    class="mb-5 w-full rounded-2xl border p-3 outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Reference Number"
                />

                <div class="flex justify-end gap-3">
                    <button @click="closeDisbursementModal" class="rounded-xl border px-4 py-2 hover:bg-slate-50">Cancel</button>

                    <button
                        @click="disburseLoan"
                        :disabled="loading.disburse"
                        class="flex items-center gap-2 rounded-xl bg-blue-700 px-5 py-2 text-white hover:bg-blue-800 disabled:opacity-60"
                    >
                        <span v-if="loading.disburse" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>

                        <span>
                            {{ loading.disburse ? 'Processing...' : 'Confirm Disbursement' }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, reactive, ref, watch } from 'vue';
import { loanPreview } from '@/lib/loanCalc';

const props = defineProps({
    loan: Object,
    auth: Object,
    message: String,
    type: { type: String, default: 'success' },
    duration: { type: Number, default: 5000 },
});

const loan = computed(() => props.loan);
const isMemberRole = computed(() => props.auth.user.role === 'member');

const showApprovalModal = ref(false);
const showRejectionModal = ref(false);
const showDisbursementModal = ref(false);

function openApprovalModal() {
    resetValidation();

    const applied = props.loan.applied_amount || 0;

    approvalForm.approved_amount = applied;
    approvalForm.approval_notes = '';

    showApprovalModal.value = true;
}

function openRejectModal() {
    resetValidation();
    showRejectionModal.value = true;
}

function openDisbursementModal() {
    resetValidation();
    disbursementForm.disbursed_amount = props.loan.approved_amount || 0;
    showDisbursementModal.value = true;
}

function closeApprovalModal() {
    showApprovalModal.value = false;
}
function closeRejectModal() {
    showRejectionModal.value = false;
}
function closeDisbursementModal() {
    showDisbursementModal.value = false;
}

const approvalForm = reactive({
    approved_amount: 0,
    net_amount: 0,
    approval_notes: '',
});

const rejectionForm = reactive({
    rejection_reason: '',
});

const disbursementForm = reactive({
    disbursed_amount: props.loan.approved_amount || 0,
    disbursement_method: '',
    disbursement_reference: '',
});

const validationErrors = reactive({});
const loading = reactive({ approve: false, reject: false, disburse: false });

function resetValidation() {
    for (const k in validationErrors) delete validationErrors[k];
}

const totalFeeRate = computed(() => ((loan.value.loan_product.processing_fee_rate || 0) + (loan.value.loan_product.insurance_rate || 0)) / 100);
const processingFeePreview = computed(() =>
    approvalForm.approved_amount ? (approvalForm.approved_amount * (loan.value.loan_product.processing_fee_rate ?? 0)) / 100 : 0,
);
const insuranceFeePreview = computed(() =>
    approvalForm.approved_amount ? (approvalForm.approved_amount * (loan.value.loan_product.insurance_rate ?? 0)) / 100 : 0,
);
const netDisbursementPreview = computed(() =>
    approvalForm.approved_amount ? approvalForm.approved_amount - processingFeePreview.value - insuranceFeePreview.value : 0,
);
const preview = computed(() => loanPreview(
  approvalForm.approved_amount,
  loan.value.interest_rate ?? loan.value.loan_product?.interest_rate,
  loan.value.term_months,
  loan.value.loan_product?.processing_fee_rate,
  loan.value.loan_product?.insurance_rate,
))

const monthlyPreview = computed(() => preview.value.monthly)
const totalInterestPreview = computed(() => preview.value.totalInterest)
const totalRepaymentPreview = computed(() => preview.value.totalRepayable)

const canEdit = computed(() => props.loan.status === 'pending' && ['admin', 'loan_officer', 'management'].includes(props.auth.user.role));
const canApprove = computed(() => props.loan.status === 'pending' && ['admin', 'management'].includes(props.auth.user.role));
const canReject = computed(() => ['pending', 'approved'].includes(props.loan.status) && ['admin', 'management'].includes(props.auth.user.role));
const canDisburse = computed(() => props.loan.status === 'approved' && ['admin', 'accountant'].includes(props.auth.user.role));
const canViewSchedule = computed(() => ['disbursed', 'active', 'completed'].includes(props.loan.status));
const canViewRepayments = computed(() => ['disbursed', 'active', 'completed'].includes(props.loan.status));
const hasQuickActions = computed(() => canApprove.value || canReject.value || canDisburse.value || canViewSchedule.value || canViewRepayments.value);

const formatCurrency = (amount) => new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0);
const formatDate = (date) => {
    if (!date) return '-';
    try {
        return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: '2-digit' });
    } catch {
        return date;
    }
};
const formatStatus = (status = '') => status.replace('_', ' ').toUpperCase();

const getStatusBannerClass = (status) =>
    ({
        pending: 'bg-yellow-50 text-yellow-800 border border-yellow-200',
        pending_guarantor_approval: 'bg-orange-50 text-orange-800 border border-orange-200',
        approved: 'bg-green-50 text-green-800 border border-green-200',
        disbursed: 'bg-blue-50 text-blue-800 border border-blue-200',
        active: 'bg-blue-100 text-blue-800 border border-blue-200',
        completed: 'bg-green-50 text-slate-800 border border-green-200',
        rejected: 'bg-red-50 text-red-800 border border-red-200',
        defaulted: 'bg-gray-50 text-gray-800 border border-gray-200',
    })[status] || 'bg-slate-50 text-slate-800 border border-slate-200';

const getStatusDescription = (status) =>
    ({
        pending: 'This loan application is awaiting review and approval.',
        pending_guarantor_approval: 'This loan application is awaiting guarantor acceptance.',
        approved: 'This loan has been approved and is ready for disbursement.',
        disbursed: 'This loan has been disbursed to the member.',
        active: 'This loan is active with ongoing repayments.',
        completed: 'This loan has been fully repaid.',
        rejected: 'This loan application has been rejected.',
        cancelled: 'This loan application has been cancelled.',
    })[status] || 'Unknown status';

const getGuarantorStatusClass = (status) =>
    ({
        pending: 'bg-yellow-100 text-yellow-800',
        accepted: 'bg-emerald-100 text-emerald-800',
        rejected: 'bg-red-100 text-red-800',
    })[status] || 'bg-slate-100 text-slate-800';

const visible = ref(Boolean(props.message));
const message = ref(props.message || '');
const type = ref(props.type || 'success');
let toastTimer = null;
const toastAppearance = computed(() => (type.value === 'success' ? 'bg-white/95 border border-slate-200' : 'bg-white/95 border border-red-200'));
const close = () => {
    visible.value = false;
    if (toastTimer) {
        clearTimeout(toastTimer);
        toastTimer = null;
    }
};

if (visible.value && props.duration > 0) {
    toastTimer = setTimeout(() => {
        visible.value = false;
        toastTimer = null;
    }, props.duration);
}

function showToast(txt = '', t = 'success', duration = props.duration) {
    message.value = txt;
    type.value = t;
    visible.value = true;
    if (toastTimer) clearTimeout(toastTimer);
    if (duration > 0)
        toastTimer = setTimeout(() => {
            visible.value = false;
            toastTimer = null;
        }, duration);
}

function firstError(err) {
    if (!err) return '';
    if (Array.isArray(err)) return err[0];
    if (typeof err === 'object') return Object.values(err)[0];
    return String(err);
}


const approveLoan = async () => {
    resetValidation();
    if (approvalForm.approved_amount > props.loan.applied_amount) {
        validationErrors.approved_amount = ['Cannot approve more than applied amount'];
        return;
    }

    if (!approvalForm.approved_amount || approvalForm.approved_amount <= 0) {
        validationErrors.approved_amount = ['Enter a valid amount'];
        return;
    }

    if (approvalForm.approved_amount > props.loan.applied_amount) {
        validationErrors.approved_amount = ['Cannot approve more than applied amount'];
        return;
    }
    loading.approve = true;
    try {
        const { data } = await axios.post(route('loans.approve', props.loan.id), approvalForm);
        showToast(data.message || 'Loan approved', 'success');
        showApprovalModal.value = false;
        router.visit(route('loans.show', props.loan.id), {
            preserveScroll: true,
            preserveState: false,
        });
    } catch (error) {
        const res = error.response;
        if (res?.data?.errors) Object.assign(validationErrors, res.data.errors);
        showToast(res?.data?.message || 'Approval failed', 'error');
    } finally {
        loading.approve = false;
    }
};

const rejectLoan = async () => {
    resetValidation();
    if (!rejectionForm.rejection_reason || rejectionForm.rejection_reason.trim().length < 3) {
        validationErrors.rejection_reason = ['Please provide a reason for rejection (min 3 characters).'];
        return;
    }
    loading.reject = true;
    try {
        const response = await axios.post(`/loans/${props.loan.id}/reject`, rejectionForm);
        showToast(response.data.message || 'Loan rejected', 'success');
        showRejectionModal.value = false;
        router.reload();
    } catch (err) {
        const res = err.response;
        if (res?.data?.errors) Object.assign(validationErrors, res.data.errors);
        showToast(res?.data?.message || 'Failed to reject loan', 'error');
    } finally {
        loading.reject = false;
    }
};

const disburseLoan = async () => {
    resetValidation();
    if (!disbursementForm.disbursement_method) {
        validationErrors.disbursement_method = ['Select a disbursement method.'];
        return;
    }
    loading.disburse = true;
    try {
        const response = await axios.post(`/loans/${props.loan.id}/disburse`, {
            disbursement_method: disbursementForm.disbursement_method,
            disbursement_reference: disbursementForm.disbursement_reference,
        });
        showToast(response.data.message || 'Loan disbursed successfully', 'success');
        showDisbursementModal.value = false;
        router.reload();
    } catch (err) {
        const res = err.response;
        if (res?.data?.errors) Object.assign(validationErrors, res.data.errors);
        showToast(res?.data?.message || 'Failed to disburse loan', 'error');
    } finally {
        loading.disburse = false;
    }
};

watch(showApprovalModal, (val) => {
    if (!val) {
        approvalForm.approved_amount = null;
        approvalForm.approval_notes = '';
    }
});

const showDocsModal = ref(false);
</script>
