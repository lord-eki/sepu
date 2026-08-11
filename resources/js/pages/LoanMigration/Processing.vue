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
    processing_status?: string | null
    validation_status?: string | null
    error_message?: string | null
}

interface Props {
    batch: Batch
    records: MigrationRecord[]
    canProcess?: boolean
}

const props = defineProps<Props>()

const processing = ref(false)
const showProcessModal = ref(false)

const processingRemarks = ref('')

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

const statusLabel = computed(() => {
    return props.batch.status
        ? props.batch.status.replaceAll('_', ' ')
        : 'Unknown'
})

const statusClass = computed(() => {
    switch (props.batch.status?.toLowerCase()) {
        case 'approved':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'

        case 'processed':
            return 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300'

        case 'processing':
            return 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300'

        case 'validation_failed':
            return 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300'

        default:
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    }
})

const totalRecords = computed(() => {
    return props.batch.total_records ?? props.records.length
})

const processedRecords = computed(() => {
    return props.batch.processed_records ?? 0
})

const validRecords = computed(() => {
    return props.batch.valid_records ?? 0
})

const invalidRecords = computed(() => {
    return props.batch.invalid_records ?? 0
})

const processingPercentage = computed(() => {
    if (!totalRecords.value) {
        return 0
    }

    return Math.min(
        100,
        Math.round(
            (processedRecords.value / totalRecords.value) * 100,
        ),
    )
})

const canProcessBatch = computed(() => {
    return (
        props.canProcess === true &&
        props.batch.status?.toLowerCase() === 'approved'
    )
})

const processedSuccessfully = computed(() => {
    return props.records.filter(
        (record) =>
            record.processing_status?.toLowerCase() === 'processed',
    ).length
})

const processingFailed = computed(() => {
    return props.records.filter(
        (record) =>
            record.processing_status?.toLowerCase() === 'failed',
    ).length
})

const processBatch = () => {
    if (!canProcessBatch.value || processing.value) {
        return
    }

    processing.value = true

    router.post(
        route('loan-migration.process', props.batch.id),
        {
            remarks: processingRemarks.value || null,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                showProcessModal.value = false
                processingRemarks.value = ''
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
                title: 'Processing'
            }
        ]"
    >
        <Head title="Process Migration Batch" />
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
                    Processing
                </span>
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-600 text-white shadow-lg shadow-purple-600/20">
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
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                                Process Migration Batch
                            </h1>

                            <span
                                :class="statusClass"
                                class="rounded-full px-2.5 py-1 text-[10px] font-bold capitalize"
                            >
                                {{ statusLabel }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                            Create and update the corresponding loan records
                            from this approved migration batch.
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

            <!-- Important Notice -->
            <section
                v-if="canProcessBatch"
                class="rounded-2xl border border-purple-200 bg-purple-50 p-5 dark:border-purple-900/50 dark:bg-purple-950/20"
            >
                <div class="flex items-start gap-3">
                    <svg
                        class="mt-0.5 h-5 w-5 shrink-0 text-purple-600 dark:text-purple-400"
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
                        <p class="text-xs font-bold text-purple-700 dark:text-purple-300">
                            Ready for portfolio processing
                        </p>

                        <p class="mt-1 text-[11px] leading-5 text-purple-600 dark:text-purple-400">
                            This batch has been approved. Processing will
                            create or update the corresponding loan records
                            using the migrated information.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Progress -->
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold">
                            Processing Progress
                        </p>

                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            {{ processedRecords }} of {{ totalRecords }} records processed
                        </p>
                    </div>

                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ processingPercentage }}%
                    </p>
                </div>

                <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div
                        class="h-full rounded-full bg-purple-600 transition-all duration-500"
                        :style="{ width: `${processingPercentage}%` }"
                    />
                </div>

                <div class="mt-3 flex justify-between text-[10px] text-slate-400">
                    <span>
                        {{ processedRecords }} processed
                    </span>

                    <span>
                        {{ totalRecords - processedRecords }} remaining
                    </span>
                </div>
            </section>

            <!-- Statistics -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Total Records
                    </p>

                    <p class="mt-2 text-2xl font-bold">
                        {{ totalRecords }}
                    </p>
                </div>

                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm dark:border-blue-900/50 dark:bg-blue-950/20">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                        Valid Records
                    </p>

                    <p class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300">
                        {{ validRecords }}
                    </p>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        Successfully Processed
                    </p>

                    <p class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-300">
                        {{ processedSuccessfully }}
                    </p>
                </div>

                <div class="rounded-2xl border border-red-200 bg-red-50 p-5 shadow-sm dark:border-red-900/50 dark:bg-red-950/20">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">
                        Failed
                    </p>

                    <p class="mt-2 text-2xl font-bold text-red-700 dark:text-red-300">
                        {{ processingFailed + invalidRecords }}
                    </p>
                </div>
            </div>

            <!-- Batch Summary -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="text-sm font-bold">
                        Batch Summary
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
                            Approved By
                        </p>

                        <p class="mt-1 text-xs font-semibold">
                            {{ batch.approver?.name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[9px] uppercase tracking-wider text-slate-400">
                            Created
                        </p>

                        <p class="mt-1 text-xs font-semibold">
                            {{ formatDate(batch.created_at) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-[9px] uppercase tracking-wider text-slate-400">
                            Last Updated
                        </p>

                        <p class="mt-1 text-xs font-semibold">
                            {{ formatDate(batch.updated_at) }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Records -->
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold">
                            Migration Records
                        </h2>

                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            Review processing status for each migrated loan.
                        </p>
                    </div>
                </div>

                <!-- Desktop -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[1000px] text-left">
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
                                    Outstanding
                                </th>

                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Processing Status
                                </th>

                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Message
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="record in records"
                                :key="record.id"
                                class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4 text-xs font-bold">
                                    {{ record.legacy_loan_number ?? `#${record.id}` }}
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

                                <td class="px-5 py-4 text-xs font-bold">
                                    {{ formatCurrency(record.outstanding_balance) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        v-if="record.processing_status?.toLowerCase() === 'processed'"
                                        class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"
                                    >
                                        Processed
                                    </span>

                                    <span
                                        v-else-if="record.processing_status?.toLowerCase() === 'failed'"
                                        class="rounded-full bg-red-100 px-2.5 py-1 text-[10px] font-bold text-red-700 dark:bg-red-950/50 dark:text-red-300"
                                    >
                                        Failed
                                    </span>

                                    <span
                                        v-else
                                        class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        {{ record.processing_status ?? 'Pending' }}
                                    </span>
                                </td>

                                <td class="max-w-xs px-5 py-4 text-[10px] text-slate-500 dark:text-slate-400">
                                    {{ record.error_message ?? '—' }}
                                </td>
                            </tr>

                            <tr v-if="!records.length">
                                <td
                                    colspan="6"
                                    class="px-5 py-12 text-center"
                                >
                                    <p class="text-sm font-semibold">
                                        No migration records
                                    </p>

                                    <p class="mt-1 text-[11px] text-slate-400">
                                        No records are available for processing.
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
                                </p>
                            </div>

                            <span
                                v-if="record.processing_status?.toLowerCase() === 'processed'"
                                class="rounded-full bg-emerald-100 px-2 py-1 text-[9px] font-bold text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"
                            >
                                Processed
                            </span>

                            <span
                                v-else-if="record.processing_status?.toLowerCase() === 'failed'"
                                class="rounded-full bg-red-100 px-2 py-1 text-[9px] font-bold text-red-700 dark:bg-red-950/50 dark:text-red-300"
                            >
                                Failed
                            </span>

                            <span
                                v-else
                                class="rounded-full bg-slate-100 px-2 py-1 text-[9px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                            >
                                Pending
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50">
                            <div>
                                <p class="text-[9px] uppercase text-slate-400">
                                    Member No.
                                </p>

                                <p class="mt-1 text-xs font-semibold">
                                    {{ record.member_number ?? '—' }}
                                </p>
                            </div>

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
                                    Outstanding
                                </p>

                                <p class="mt-1 text-xs font-bold">
                                    {{ formatCurrency(record.outstanding_balance) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[9px] uppercase text-slate-400">
                                    Record ID
                                </p>

                                <p class="mt-1 text-xs font-semibold">
                                    #{{ record.id }}
                                </p>
                            </div>
                        </div>

                        <p
                            v-if="record.error_message"
                            class="rounded-xl bg-red-50 p-3 text-[10px] leading-5 text-red-600 dark:bg-red-950/20 dark:text-red-400"
                        >
                            {{ record.error_message }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Process Action -->
            <section
                v-if="canProcessBatch"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-bold">
                            Process Approved Batch
                        </p>

                        <p class="mt-1 max-w-2xl text-[10px] leading-5 text-slate-500 dark:text-slate-400">
                            Start the final migration operation. The system
                            will create or update loan records using the
                            approved migration data.
                        </p>
                    </div>

                    <button
                        type="button"
                        :disabled="processing"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-purple-600 px-6 py-3 text-xs font-bold text-white shadow-lg shadow-purple-600/20 transition hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="showProcessModal = true"
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
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>

                        Process Batch
                    </button>
                </div>
            </section>

            <!-- Completed / Unavailable -->
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
                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000 20z"
                        />
                    </svg>

                    <div>
                        <p class="text-xs font-bold">
                            Processing action unavailable
                        </p>

                        <p class="mt-1 text-[10px] leading-5 text-slate-500 dark:text-slate-400">
                            This batch is currently in
                            <span class="font-semibold capitalize">
                                {{ statusLabel }}
                            </span>
                            status or your account does not have permission
                            to process it.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Process Confirmation Modal -->
    <Teleport to="body">
        <div
            v-if="showProcessModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm"
            @click.self="showProcessModal = false"
        >
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">

                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400">
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
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                    </svg>
                </div>

                <h2 class="mt-4 text-lg font-bold">
                    Process Migration Batch?
                </h2>

                <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                    You are about to process
                    <span class="font-semibold text-slate-700 dark:text-slate-200">
                        {{ batch.batch_number }}
                    </span>
                    containing
                    <span class="font-semibold text-slate-700 dark:text-slate-200">
                        {{ totalRecords }}
                    </span>
                    loan record(s).
                </p>

                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/50 dark:bg-amber-950/20">
                    <p class="text-[10px] font-semibold text-amber-700 dark:text-amber-300">
                        Important
                    </p>

                    <p class="mt-1 text-[10px] leading-5 text-amber-600 dark:text-amber-400">
                        Make sure the batch has been fully approved and
                        verified before continuing.
                    </p>
                </div>

                <textarea
                    v-model="processingRemarks"
                    rows="4"
                    placeholder="Optional processing remarks..."
                    class="mt-4 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-xs outline-none transition focus:border-purple-500 focus:ring-2 focus:ring-purple-500/10 dark:border-slate-700 dark:bg-slate-800"
                />

                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="showProcessModal = false"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        :disabled="processing"
                        class="rounded-xl bg-purple-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-purple-700 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="processBatch"
                    >
                        {{ processing ? 'Processing...' : 'Confirm & Process' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</AppLayout>
</template>