<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface User {
    id: number
    name: string
}

interface Batch {
    id: number
    batch_number: string
    description?: string | null
    remarks?: string | null
    status: string
    total_records?: number
    valid_records?: number
    invalid_records?: number
    processed_records?: number
    total_original_amount?: number | string | null
    total_amount_paid?: number | string | null
    total_outstanding_balance?: number | string | null
    created_at?: string | null
    updated_at?: string | null
    creator?: User | null
    submitter?: User | null
    verifier?: User | null
    approver?: User | null
}

interface MigrationRecord {
    id: number
    legacy_loan_number?: string | null
    member_number?: string | null
    member_name?: string | null
    loan_product?: string | null
    original_amount?: number | string | null
    amount_paid?: number | string | null
    outstanding_balance?: number | string | null
    verification_status?: string | null
    validation_status?: string | null
}

interface ApprovalHistory {
    id: number
    action?: string | null
    remarks?: string | null
    user?: User | null
    created_at?: string | null
}

interface Props {
    batch: Batch
    records: MigrationRecord[]
    approvalHistory?: ApprovalHistory[]
    canApprove?: boolean
}

const props = defineProps<Props>()

const processing = ref(false)
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const showHistory = ref(false)

const approvalRemarks = ref('')
const rejectionReason = ref('')

const formatCurrency = (
    value: number | string | null | undefined,
) => {
    const amount = Number(value ?? 0)

    if (Number.isNaN(amount)) {
        return 'KES 0.00'
    }

    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount)
}

const formatDate = (value?: string | null) => {
    if (!value) {
        return '—'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return value
    }

    return new Intl.DateTimeFormat('en-KE', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date)
}

const statusClass = computed(() => {
    switch (props.batch.status?.toLowerCase()) {
        case 'approved':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'

        case 'submitted':
        case 'accounts_verified':
            return 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300'

        case 'validation_failed':
            return 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300'

        case 'processed':
            return 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300'

        default:
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    }
})

const statusLabel = computed(() => {
    return props.batch.status
        ? props.batch.status.replaceAll('_', ' ')
        : 'Unknown'
})

const canApproveBatch = computed(() => {
    return (
        props.canApprove === true &&
        ['submitted', 'accounts_verified'].includes(
            props.batch.status?.toLowerCase(),
        )
    )
})

const totalRecords = computed(() => {
    return props.batch.total_records ?? props.records.length
})

const outstandingBalance = computed(() => {
    if (props.batch.total_outstanding_balance !== undefined) {
        return Number(props.batch.total_outstanding_balance ?? 0)
    }

    return props.records.reduce(
        (total, record) =>
            total + Number(record.outstanding_balance ?? 0),
        0,
    )
})

const originalAmount = computed(() => {
    if (props.batch.total_original_amount !== undefined) {
        return Number(props.batch.total_original_amount ?? 0)
    }

    return props.records.reduce(
        (total, record) =>
            total + Number(record.original_amount ?? 0),
        0,
    )
})

const amountPaid = computed(() => {
    if (props.batch.total_amount_paid !== undefined) {
        return Number(props.batch.total_amount_paid ?? 0)
    }

    return props.records.reduce(
        (total, record) =>
            total + Number(record.amount_paid ?? 0),
        0,
    )
})

const approveBatch = () => {
    if (!canApproveBatch.value || processing.value) {
        return
    }

    processing.value = true

    router.post(
        route('loan-migration.approve', props.batch.id),
        {
            remarks: approvalRemarks.value || null,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showApproveModal.value = false
                approvalRemarks.value = ''
            },

            onFinish: () => {
                processing.value = false
            },
        },
    )
}

const rejectBatch = () => {
    if (!canApproveBatch.value || processing.value) {
        return
    }

    if (!rejectionReason.value.trim()) {
        return
    }

    processing.value = true

    router.post(
        route('loan-migration.reject', props.batch.id),
        {
            remarks: rejectionReason.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showRejectModal.value = false
                rejectionReason.value = ''
            },

            onFinish: () => {
                processing.value = false
            },
        },
    )
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Loan Migration',
                href: route('loan-migration.index')
            },
            {
                title: 'Approval'
            }
        ]"
    >
        <Head title="Migration Approval" />

    <div class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">

            <!-- Breadcrumb -->
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <Link
                    :href="route('loan-migration.index')"
                    class="transition hover:text-blue-600 dark:hover:text-blue-400"
                >
                    Loan Migration
                </Link>

                <svg
                    class="h-3.5 w-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>

                <Link
                    :href="route('loan-migration.show', batch.id)"
                    class="transition hover:text-blue-600 dark:hover:text-blue-400"
                >
                    {{ batch.batch_number }}
                </Link>

                <svg
                    class="h-3.5 w-3.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                    />
                </svg>

                <span class="font-medium text-slate-700 dark:text-slate-300">
                    Approval
                </span>
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20">
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.292 9 11.622C17.176 19.292 21 14.591 21 9c0-1.018-.127-2.007-.364-2.956z"
                            />
                        </svg>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                                Migration Batch Approval
                            </h1>

                            <span
                                :class="statusClass"
                                class="rounded-full px-2.5 py-1 text-[10px] font-bold capitalize"
                            >
                                {{ statusLabel }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                            Review the migration batch before approving it for processing.
                        </p>
                    </div>
                </div>

                <Link
                    :href="route('loan-migration.show', batch.id)"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>

                    Back to Batch
                </Link>
            </div>

            <!-- Review Warning -->
            <section
                v-if="canApproveBatch"
                class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/20"
            >
                <div class="flex items-start gap-3">
                    <svg
                        class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"
                        />
                    </svg>

                    <div>
                        <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                            Approval requires careful review
                        </p>

                        <p class="mt-1 text-[11px] leading-5 text-amber-600 dark:text-amber-400">
                            Confirm that the migrated loan records, member
                            associations, balances and supporting information
                            are accurate before approving this batch.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Summary -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Records
                    </p>

                    <p class="mt-2 text-2xl font-bold">
                        {{ totalRecords }}
                    </p>
                </div>

                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm dark:border-blue-900/50 dark:bg-blue-950/20">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                        Original Amount
                    </p>

                    <p class="mt-2 text-lg font-bold text-blue-700 dark:text-blue-300">
                        {{ formatCurrency(originalAmount) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Amount Paid
                    </p>

                    <p class="mt-2 text-lg font-bold">
                        {{ formatCurrency(amountPaid) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm dark:border-amber-900/50 dark:bg-amber-950/20">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                        Outstanding
                    </p>

                    <p class="mt-2 text-lg font-bold text-amber-700 dark:text-amber-300">
                        {{ formatCurrency(outstandingBalance) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        Verification
                    </p>

                    <p class="mt-2 text-lg font-bold text-emerald-700 dark:text-emerald-300">
                        Complete
                    </p>
                </div>
            </div>

            <!-- Batch Information -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="text-sm font-bold">
                        Batch Information
                    </h2>
                </div>

                <div class="grid gap-5 p-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-[9px] uppercase tracking-wider text-slate-400">
                            Batch Number
                        </p>

                        <p class="mt-1 text-xs font-bold">
                            {{ batch.batch_number }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[9px] uppercase tracking-wider text-slate-400">
                            Created By
                        </p>

                        <p class="mt-1 text-xs font-semibold">
                            {{ batch.creator?.name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[9px] uppercase tracking-wider text-slate-400">
                            Submitted By
                        </p>

                        <p class="mt-1 text-xs font-semibold">
                            {{ batch.submitter?.name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[9px] uppercase tracking-wider text-slate-400">
                            Verified By
                        </p>

                        <p class="mt-1 text-xs font-semibold">
                            {{ batch.verifier?.name ?? '—' }}
                        </p>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <p class="text-[9px] uppercase tracking-wider text-slate-400">
                            Description
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">
                            {{ batch.description ?? 'No description provided.' }}
                        </p>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <p class="text-[9px] uppercase tracking-wider text-slate-400">
                            Remarks
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-300">
                            {{ batch.remarks ?? 'No remarks provided.' }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Records -->
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold">
                            Records for Approval
                        </h2>

                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            {{ records.length }} migrated loan record(s)
                        </p>
                    </div>

                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                        Verified
                    </span>
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[950px] text-left">
                        <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Loan Number
                                </th>

                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Member
                                </th>

                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Product
                                </th>

                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Original
                                </th>

                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Paid
                                </th>

                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Outstanding
                                </th>

                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Verification
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="record in records"
                                :key="record.id"
                                class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4">
                                    <span class="text-xs font-bold">
                                        {{ record.legacy_loan_number ?? `#${record.id}` }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-xs font-semibold">
                                        {{ record.member_name ?? 'Unknown' }}
                                    </p>

                                    <p class="mt-1 text-[10px] text-slate-400">
                                        {{ record.member_number ?? '—' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-xs">
                                    {{ record.loan_product ?? '—' }}
                                </td>

                                <td class="px-5 py-4 text-xs font-semibold">
                                    {{ formatCurrency(record.original_amount) }}
                                </td>

                                <td class="px-5 py-4 text-xs font-semibold">
                                    {{ formatCurrency(record.amount_paid) }}
                                </td>

                                <td class="px-5 py-4 text-xs font-bold">
                                    {{ formatCurrency(record.outstanding_balance) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                        Verified
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="!records.length">
                                <td
                                    colspan="7"
                                    class="px-5 py-12 text-center"
                                >
                                    <p class="text-sm font-semibold">
                                        No records available
                                    </p>

                                    <p class="mt-1 text-[11px] text-slate-400">
                                        There are no records available for approval.
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile -->
                <div class="divide-y divide-slate-100 md:hidden dark:divide-slate-800">
                    <div
                        v-for="record in records"
                        :key="record.id"
                        class="space-y-4 p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold">
                                    {{ record.legacy_loan_number ?? `#${record.id}` }}
                                </p>

                                <p class="mt-1 text-[10px] text-slate-400">
                                    {{ record.member_name ?? 'Unknown' }}
                                    ·
                                    {{ record.member_number ?? '—' }}
                                </p>
                            </div>

                            <span class="rounded-full bg-emerald-100 px-2 py-1 text-[9px] font-bold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                Verified
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50">
                            <div>
                                <p class="text-[9px] uppercase text-slate-400">
                                    Product
                                </p>

                                <p class="mt-1 truncate text-xs font-semibold">
                                    {{ record.loan_product ?? '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[9px] uppercase text-slate-400">
                                    Original
                                </p>

                                <p class="mt-1 text-xs font-semibold">
                                    {{ formatCurrency(record.original_amount) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[9px] uppercase text-slate-400">
                                    Paid
                                </p>

                                <p class="mt-1 text-xs font-semibold">
                                    {{ formatCurrency(record.amount_paid) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[9px] uppercase text-slate-400">
                                    Outstanding
                                </p>

                                <p class="mt-1 text-xs font-bold">
                                    {{ formatCurrency(record.outstanding_balance) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Approval History -->
            <section
                v-if="approvalHistory?.length"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <button
                    type="button"
                    class="flex w-full items-center justify-between px-5 py-4 text-left"
                    @click="showHistory = !showHistory"
                >
                    <div>
                        <h2 class="text-sm font-bold">
                            Approval History
                        </h2>

                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            Previous actions performed on this batch.
                        </p>
                    </div>

                    <svg
                        :class="showHistory ? 'rotate-180' : ''"
                        class="h-4 w-4 text-slate-400 transition-transform"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </button>

                <div
                    v-if="showHistory"
                    class="border-t border-slate-200 dark:border-slate-800"
                >
                    <div
                        v-for="item in approvalHistory"
                        :key="item.id"
                        class="flex gap-4 border-b border-slate-100 p-5 last:border-0 dark:border-slate-800"
                    >
                        <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500" />

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-xs font-bold capitalize">
                                    {{ item.action?.replaceAll('_', ' ') ?? 'Action' }}
                                </p>

                                <span class="text-[10px] text-slate-400">
                                    {{ formatDate(item.created_at) }}
                                </span>
                            </div>

                            <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                {{ item.user?.name ?? 'System User' }}
                            </p>

                            <p
                                v-if="item.remarks"
                                class="mt-2 text-xs leading-5 text-slate-600 dark:text-slate-300"
                            >
                                {{ item.remarks }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Actions -->
            <section
                v-if="canApproveBatch"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold">
                            Final Decision
                        </p>

                        <p class="mt-1 max-w-2xl text-[10px] leading-5 text-slate-500 dark:text-slate-400">
                            Approving this batch confirms that the migrated
                            loan information is ready to be processed into
                            the live loan portfolio.
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <button
                            type="button"
                            :disabled="processing"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-5 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-900/50 dark:bg-slate-900 dark:text-red-400 dark:hover:bg-red-950/20"
                            @click="showRejectModal = true"
                        >
                            Reject Batch
                        </button>

                        <button
                            type="button"
                            :disabled="processing"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="showApproveModal = true"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>

                            Approve Batch
                        </button>
                    </div>
                </div>
            </section>

            <!-- Already Processed -->
            <section
                v-else
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start gap-3">
                    <svg
                        class="mt-0.5 h-5 w-5 shrink-0 text-slate-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"
                        />
                    </svg>

                    <div>
                        <p class="text-xs font-bold">
                            Approval action unavailable
                        </p>

                        <p class="mt-1 text-[10px] leading-5 text-slate-500 dark:text-slate-400">
                            This batch is currently in
                            <span class="font-semibold capitalize">
                                {{ statusLabel }}
                            </span>
                            status or your account does not have permission
                            to approve it.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Approve Modal -->
    <Teleport to="body">
        <div
            v-if="showApproveModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
            @click.self="showApproveModal = false"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>

                <h2 class="mt-4 text-lg font-bold">
                    Approve Migration Batch?
                </h2>

                <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                    You are approving
                    <span class="font-semibold text-slate-700 dark:text-slate-200">
                        {{ batch.batch_number }}
                    </span>.
                    This confirms that the batch is ready for processing.
                </p>

                <textarea
                    v-model="approvalRemarks"
                    rows="4"
                    placeholder="Optional approval remarks..."
                    class="mt-5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-xs outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-800"
                />

                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="showApproveModal = false"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="processing"
                        class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                        @click="approveBatch"
                    >
                        {{ processing ? 'Approving...' : 'Confirm Approval' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Reject Modal -->
    <Teleport to="body">
        <div
            v-if="showRejectModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
            @click.self="showRejectModal = false"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </div>

                <h2 class="mt-4 text-lg font-bold">
                    Reject Migration Batch?
                </h2>

                <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                    Provide a clear reason for rejecting
                    <span class="font-semibold text-slate-700 dark:text-slate-200">
                        {{ batch.batch_number }}
                    </span>.
                </p>

                <textarea
                    v-model="rejectionReason"
                    rows="5"
                    placeholder="Enter the reason for rejection..."
                    class="mt-5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-xs outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/10 dark:border-slate-700 dark:bg-slate-800"
                />

                <p
                    v-if="!rejectionReason.trim()"
                    class="mt-2 text-[10px] text-slate-400"
                >
                    A rejection reason is required.
                </p>

                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="showRejectModal = false"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="processing || !rejectionReason.trim()"
                        class="rounded-xl bg-red-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="rejectBatch"
                    >
                        {{ processing ? 'Rejecting...' : 'Confirm Rejection' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</AppLayout>
</template>