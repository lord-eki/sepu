<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Batch {
    id: number
    batch_number: string
    description?: string | null
    status: string
}

interface Member {
    id: number
    member_number?: string | null
    name?: string | null
    phone?: string | null
}

interface LoanProduct {
    id: number
    name?: string | null
    code?: string | null
}

interface User {
    id: number
    name: string
}

interface LoanMigrationRecord {
    id: number
    member_id?: number | null
    member?: Member | null

    loan_product_id?: number | null
    loan_product?: LoanProduct | string | null

    legacy_loan_number?: string | null
    loan_number?: string | null

    original_amount?: number | string | null
    amount_paid?: number | string | null
    outstanding_balance?: number | string | null

    interest_rate?: number | string | null
    repayment_period?: number | string | null

    disbursement_date?: string | null
    maturity_date?: string | null

    loan_status?: string | null
    validation_status?: string | null

    validation_errors?: string[] | Record<string, string> | null
    validation_messages?: string[] | null

    source_reference?: string | null
    remarks?: string | null

    created_at?: string | null
    updated_at?: string | null

    creator?: User | null
}

interface Props {
    batch: Batch
    record: LoanMigrationRecord
}

const props = defineProps<Props>()

const productName = computed(() => {
    if (!props.record.loan_product) {
        return '—'
    }

    if (typeof props.record.loan_product === 'string') {
        return props.record.loan_product
    }

    return props.record.loan_product.name ?? '—'
})

const validationStatus = computed(() => {
    return props.record.validation_status?.toLowerCase() ?? 'pending'
})

const validationLabel = computed(() => {
    switch (validationStatus.value) {
        case 'valid':
        case 'validated':
            return 'Valid'

        case 'invalid':
        case 'failed':
            return 'Validation Failed'

        case 'warning':
        case 'warnings':
            return 'Warnings'

        default:
            return 'Pending Validation'
    }
})

const validationClass = computed(() => {
    switch (validationStatus.value) {
        case 'valid':
        case 'validated':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'

        case 'invalid':
        case 'failed':
            return 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300'

        case 'warning':
        case 'warnings':
            return 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300'

        default:
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    }
})

const validationIconClass = computed(() => {
    switch (validationStatus.value) {
        case 'valid':
        case 'validated':
            return 'text-emerald-600 dark:text-emerald-400'

        case 'invalid':
        case 'failed':
            return 'text-red-600 dark:text-red-400'

        case 'warning':
        case 'warnings':
            return 'text-amber-600 dark:text-amber-400'

        default:
            return 'text-slate-500 dark:text-slate-400'
    }
})

const errors = computed<string[]>(() => {
    const value = props.record.validation_errors

    if (!value) {
        return []
    }

    if (Array.isArray(value)) {
        return value
    }

    return Object.entries(value).map(([field, message]) => {
        return `${field}: ${message}`
    })
})

const warnings = computed(() => {
    return props.record.validation_messages ?? []
})

const formatCurrency = (value: number | string | null | undefined) => {
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

const formatNumber = (value: number | string | null | undefined) => {
    const number = Number(value ?? 0)

    if (Number.isNaN(number)) {
        return '0'
    }

    return new Intl.NumberFormat('en-KE').format(number)
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
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

const formatDateTime = (value?: string | null) => {
    if (!value) {
        return '—'
    }

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return value
    }

    return new Intl.DateTimeFormat('en-KE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date)
}

const statusLabel = computed(() => {
    const status = props.record.loan_status ?? '—'

    return status
        .replaceAll('_', ' ')
        .replace(/\b\w/g, letter => letter.toUpperCase())
})

const balanceDifference = computed(() => {
    const original = Number(props.record.original_amount ?? 0)
    const paid = Number(props.record.amount_paid ?? 0)
    const outstanding = Number(props.record.outstanding_balance ?? 0)

    return original - paid - outstanding
})

const isBalanced = computed(() => {
    return Math.abs(balanceDifference.value) < 0.01
})
</script>

<template>
<AppLayout
    :breadcrumbs="[
        {
            title: 'Loan Migration',
            href: route('loan-migration.index')
        },
        {
            title: 'Migration Records'
        },
        {
            title: 'Record Details'
        }
    ]"
>
    <Head title="Migration Record Details" />

    <div class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-6">

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
                    Loan Record
                </span>
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
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
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6l5 5v11a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                                Loan Migration Record
                            </h1>

                            <span
                                :class="validationClass"
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold"
                            >
                                <span
                                    class="h-1.5 w-1.5 rounded-full bg-current"
                                />

                                {{ validationLabel }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                            Legacy loan record #{{ record.legacy_loan_number ?? record.id }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('loan-migration.records.edit', [
                            batch.id,
                            record.id,
                        ])"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700"
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
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-8.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 7.5-7.5z"
                            />
                        </svg>

                        Edit
                    </Link>

                    <Link
                        :href="route('loan-migration.show', batch.id)"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Back to Batch
                    </Link>
                </div>
            </div>

            <!-- Validation Summary -->
            <div
                :class="validationClass"
                class="rounded-2xl border border-current/10 p-4"
            >
                <div class="flex items-start gap-3">
                    <svg
                        :class="validationIconClass"
                        class="mt-0.5 h-5 w-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            v-if="validationStatus === 'valid' || validationStatus === 'validated'"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />

                        <path
                            v-else-if="validationStatus === 'invalid' || validationStatus === 'failed'"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />

                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"
                        />
                    </svg>

                    <div class="min-w-0">
                        <p class="text-xs font-bold">
                            {{ validationLabel }}
                        </p>

                        <p
                            v-if="validationStatus === 'valid' || validationStatus === 'validated'"
                            class="mt-1 text-[11px] leading-5 opacity-80"
                        >
                            This record has passed the current migration validation checks.
                        </p>

                        <p
                            v-else-if="errors.length"
                            class="mt-1 text-[11px] leading-5 opacity-80"
                        >
                            Please correct the validation errors before this record
                            can proceed.
                        </p>

                        <p
                            v-else
                            class="mt-1 text-[11px] leading-5 opacity-80"
                        >
                            This record is awaiting migration validation.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="grid gap-6 lg:grid-cols-3">

                <!-- Left -->
                <div class="space-y-6 lg:col-span-2">

                    <!-- Member -->
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-sm font-bold">
                                Member Information
                            </h2>
                        </div>

                        <div class="p-5">
                            <div
                                v-if="record.member"
                                class="flex items-center gap-4"
                            >
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-lg font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                    {{
                                        (record.member.name ?? '?')
                                            .charAt(0)
                                            .toUpperCase()
                                    }}
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-bold">
                                        {{ record.member.name ?? 'Unknown Member' }}
                                    </p>

                                    <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-[10px] text-slate-500 dark:text-slate-400">
                                        <span>
                                            Member No:
                                            {{ record.member.member_number ?? '—' }}
                                        </span>

                                        <span v-if="record.member.phone">
                                            {{ record.member.phone }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else
                                class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20"
                            >
                                <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">
                                    Member not linked
                                </p>

                                <p class="mt-1 text-[10px] text-amber-600 dark:text-amber-400">
                                    Member ID:
                                    {{ record.member_id ?? 'Not provided' }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Loan Details -->
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-sm font-bold">
                                Loan Details
                            </h2>
                        </div>

                        <div class="grid gap-px bg-slate-200 dark:bg-slate-800 sm:grid-cols-2">
                            <div class="bg-white p-5 dark:bg-slate-900">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    Legacy Loan Number
                                </p>

                                <p class="mt-1 text-sm font-bold">
                                    {{ record.legacy_loan_number ?? '—' }}
                                </p>
                            </div>

                            <div class="bg-white p-5 dark:bg-slate-900">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    System Loan Number
                                </p>

                                <p class="mt-1 text-sm font-bold">
                                    {{ record.loan_number ?? '—' }}
                                </p>
                            </div>

                            <div class="bg-white p-5 dark:bg-slate-900">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    Loan Product
                                </p>

                                <p class="mt-1 text-sm font-semibold">
                                    {{ productName }}
                                </p>
                            </div>

                            <div class="bg-white p-5 dark:bg-slate-900">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    Status
                                </p>

                                <p class="mt-1 text-sm font-semibold">
                                    {{ statusLabel }}
                                </p>
                            </div>

                            <div class="bg-white p-5 dark:bg-slate-900">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    Interest Rate
                                </p>

                                <p class="mt-1 text-sm font-semibold">
                                    {{
                                        record.interest_rate !== null &&
                                        record.interest_rate !== undefined
                                            ? `${record.interest_rate}%`
                                            : '—'
                                    }}
                                </p>
                            </div>

                            <div class="bg-white p-5 dark:bg-slate-900">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    Repayment Period
                                </p>

                                <p class="mt-1 text-sm font-semibold">
                                    {{
                                        record.repayment_period
                                            ? `${formatNumber(record.repayment_period)} months`
                                            : '—'
                                    }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Financial -->
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-sm font-bold">
                                Financial Summary
                            </h2>
                        </div>

                        <div class="grid gap-4 p-5 sm:grid-cols-3">
                            <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    Original Amount
                                </p>

                                <p class="mt-2 text-lg font-bold">
                                    {{ formatCurrency(record.original_amount) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-950/20">
                                <p class="text-[10px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                    Amount Paid
                                </p>

                                <p class="mt-2 text-lg font-bold text-emerald-700 dark:text-emerald-300">
                                    {{ formatCurrency(record.amount_paid) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-amber-50 p-4 dark:bg-amber-950/20">
                                <p class="text-[10px] uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                    Outstanding
                                </p>

                                <p class="mt-2 text-lg font-bold text-amber-700 dark:text-amber-300">
                                    {{ formatCurrency(record.outstanding_balance) }}
                                </p>
                            </div>
                        </div>

                        <div
                            :class="isBalanced
                                ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-950/20'
                                : 'border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-950/20'"
                            class="mx-5 mb-5 rounded-xl border p-4"
                        >
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold">
                                        Financial Reconciliation
                                    </p>

                                    <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                        Original amount − paid amount − outstanding balance
                                    </p>
                                </div>

                                <span
                                    :class="isBalanced
                                        ? 'text-emerald-700 dark:text-emerald-300'
                                        : 'text-red-700 dark:text-red-300'"
                                    class="text-xs font-bold"
                                >
                                    {{ formatCurrency(balanceDifference) }}
                                </span>
                            </div>

                            <p
                                :class="isBalanced
                                    ? 'text-emerald-700 dark:text-emerald-300'
                                    : 'text-red-700 dark:text-red-300'"
                                class="mt-2 text-[10px] font-medium"
                            >
                                {{
                                    isBalanced
                                        ? 'Amounts reconcile correctly.'
                                        : 'Amounts do not reconcile. Review the financial figures.'
                                }}
                            </p>
                        </div>
                    </section>

                    <!-- Dates -->
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-sm font-bold">
                                Loan Dates
                            </h2>
                        </div>

                        <div class="grid gap-px bg-slate-200 dark:bg-slate-800 sm:grid-cols-2">
                            <div class="bg-white p-5 dark:bg-slate-900">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    Disbursement Date
                                </p>

                                <p class="mt-1 text-sm font-semibold">
                                    {{ formatDate(record.disbursement_date) }}
                                </p>
                            </div>

                            <div class="bg-white p-5 dark:bg-slate-900">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    Maturity Date
                                </p>

                                <p class="mt-1 text-sm font-semibold">
                                    {{ formatDate(record.maturity_date) }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Source -->
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-sm font-bold">
                                Source & Remarks
                            </h2>
                        </div>

                        <div class="space-y-5 p-5">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    Source Reference
                                </p>

                                <p class="mt-1 text-sm font-medium">
                                    {{ record.source_reference ?? '—' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">
                                    Remarks
                                </p>

                                <p class="mt-1 whitespace-pre-wrap text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    {{ record.remarks ?? 'No remarks provided.' }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right -->
                <aside class="space-y-6">

                    <!-- Record Summary -->
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-sm font-bold">
                                Record Summary
                            </h2>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            <div class="flex items-center justify-between gap-4 px-5 py-4">
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Record ID
                                </span>

                                <span class="text-xs font-bold">
                                    #{{ record.id }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4 px-5 py-4">
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Batch
                                </span>

                                <Link
                                    :href="route('loan-migration.show', batch.id)"
                                    class="text-xs font-bold text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    {{ batch.batch_number }}
                                </Link>
                            </div>

                            <div class="flex items-center justify-between gap-4 px-5 py-4">
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Created
                                </span>

                                <span class="text-right text-xs font-medium">
                                    {{ formatDateTime(record.created_at) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-4 px-5 py-4">
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Updated
                                </span>

                                <span class="text-right text-xs font-medium">
                                    {{ formatDateTime(record.updated_at) }}
                                </span>
                            </div>

                            <div
                                v-if="record.creator"
                                class="flex items-center justify-between gap-4 px-5 py-4"
                            >
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Captured By
                                </span>

                                <span class="text-right text-xs font-semibold">
                                    {{ record.creator.name }}
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- Validation Errors -->
                    <section
                        v-if="errors.length"
                        class="overflow-hidden rounded-2xl border border-red-200 bg-white shadow-sm dark:border-red-900/50 dark:bg-slate-900"
                    >
                        <div class="border-b border-red-100 bg-red-50 px-5 py-4 dark:border-red-900/50 dark:bg-red-950/20">
                            <div class="flex items-center gap-2">
                                <svg
                                    class="h-4 w-4 text-red-600 dark:text-red-400"
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

                                <h2 class="text-sm font-bold text-red-700 dark:text-red-300">
                                    Validation Errors
                                </h2>
                            </div>
                        </div>

                        <div class="p-5">
                            <ul class="space-y-3">
                                <li
                                    v-for="(error, index) in errors"
                                    :key="index"
                                    class="flex gap-2 text-xs text-red-600 dark:text-red-400"
                                >
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-current" />
                                    <span>{{ error }}</span>
                                </li>
                            </ul>
                        </div>
                    </section>

                    <!-- Validation Warnings -->
                    <section
                        v-if="warnings.length"
                        class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm dark:border-amber-900/50 dark:bg-slate-900"
                    >
                        <div class="border-b border-amber-100 bg-amber-50 px-5 py-4 dark:border-amber-900/50 dark:bg-amber-950/20">
                            <div class="flex items-center gap-2">
                                <svg
                                    class="h-4 w-4 text-amber-600 dark:text-amber-400"
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

                                <h2 class="text-sm font-bold text-amber-700 dark:text-amber-300">
                                    Validation Warnings
                                </h2>
                            </div>
                        </div>

                        <div class="p-5">
                            <ul class="space-y-3">
                                <li
                                    v-for="(warning, index) in warnings"
                                    :key="index"
                                    class="flex gap-2 text-xs text-amber-700 dark:text-amber-400"
                                >
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-current" />
                                    <span>{{ warning }}</span>
                                </li>
                            </ul>
                        </div>
                    </section>

                    <!-- Batch Information -->
                    <section class="rounded-2xl border border-slate-200 bg-slate-900 p-5 text-white shadow-sm dark:border-slate-800">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">
                            Migration Batch
                        </p>

                        <p class="mt-2 text-lg font-bold">
                            {{ batch.batch_number }}
                        </p>

                        <p
                            v-if="batch.description"
                            class="mt-2 text-xs leading-5 text-slate-400"
                        >
                            {{ batch.description }}
                        </p>

                        <Link
                            :href="route('loan-migration.show', batch.id)"
                            class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-blue-400 hover:text-blue-300"
                        >
                            View batch
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
                                    d="M5 12h14m-5-5l5 5-5 5"
                                />
                            </svg>
                        </Link>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</AppLayout>
</template>