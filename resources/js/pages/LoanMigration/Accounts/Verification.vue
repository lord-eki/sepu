<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Batch {
    id: number
    batch_number: string
    description?: string | null
    status: string
    total_records?: number
    valid_records?: number
    invalid_records?: number
}

interface Member {
    id: number
    member_number?: string | null
    name?: string | null
}

interface LoanProduct {
    id: number
    name?: string | null
}

interface MigrationRecord {
    id: number
    member_id?: number | null
    member?: Member | null
    loan_product?: LoanProduct | string | null

    legacy_loan_number?: string | null
    original_amount?: number | string | null
    amount_paid?: number | string | null
    outstanding_balance?: number | string | null

    validation_status?: string | null
    validation_errors?: string[] | Record<string, string> | null
    validation_messages?: string[] | null
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface PaginatedRecords {
    data: MigrationRecord[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
    links?: PaginationLink[]
}

interface Props {
    batch: Batch
    records: PaginatedRecords
    summary?: {
        total: number
        valid: number
        invalid: number
        warnings: number
        pending: number
    }
}

const props = defineProps<Props>()

const processing = computed(() => {
    return router.page.props.processing === true
})

const statistics = computed(() => {
    return props.summary ?? {
        total: props.batch.total_records ?? props.records.total,
        valid: props.batch.valid_records ?? 0,
        invalid: props.batch.invalid_records ?? 0,
        warnings: 0,
        pending: 0,
    }
})

const validationPercentage = computed(() => {
    if (!statistics.value.total) {
        return 0
    }

    return Math.round(
        ((statistics.value.valid + statistics.value.invalid) /
            statistics.value.total) *
            100,
    )
})

const statusClass = (status?: string | null) => {
    switch (status?.toLowerCase()) {
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
}

const statusLabel = (status?: string | null) => {
    if (!status) {
        return 'Pending'
    }

    switch (status.toLowerCase()) {
        case 'valid':
        case 'validated':
            return 'Valid'

        case 'invalid':
        case 'failed':
            return 'Invalid'

        case 'warning':
        case 'warnings':
            return 'Warning'

        default:
            return status.replaceAll('_', ' ')
    }
}

const memberName = (record: MigrationRecord) => {
    return record.member?.name ?? 'Member not linked'
}

const memberNumber = (record: MigrationRecord) => {
    return record.member?.member_number ?? '—'
}

const productName = (record: MigrationRecord) => {
    if (!record.loan_product) {
        return '—'
    }

    if (typeof record.loan_product === 'string') {
        return record.loan_product
    }

    return record.loan_product.name ?? '—'
}

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

const getErrors = (record: MigrationRecord): string[] => {
    if (!record.validation_errors) {
        return []
    }

    if (Array.isArray(record.validation_errors)) {
        return record.validation_errors
    }

    return Object.entries(record.validation_errors).map(
        ([field, message]) => `${field}: ${message}`,
    )
}

const getWarnings = (record: MigrationRecord): string[] => {
    return record.validation_messages ?? []
}

const runValidation = () => {
    router.post(
        route('loan-migration.records.validate', props.batch.id),
        {},
        {
            preserveScroll: true,
        },
    )
}

const submitBatch = () => {
    if (statistics.value.invalid > 0) {
        return
    }

    router.post(
        route('loan-migration.submit', props.batch.id),
        {},
        {
            preserveScroll: true,
        },
    )
}

const pageUrl = (url: string | null) => {
    if (!url) {
        return '#'
    }

    return url
}
</script>

<template> 
<AppLayout :breadcrumbs="[ { title: 'Loan Migration', href: route('loan-migration.index') }, { title: 'Account Verification' } ]" > 
<Head title="Account Verification" />

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
                        d="M9 5l7 7-7 7"
                    />
                </svg>

                <span class="font-medium text-slate-700 dark:text-slate-300">
                    Validation
                </span>
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-lg shadow-amber-500/20">
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
                                Validate Migration Records
                            </h1>

                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                {{ batch.batch_number }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                            Review the imported records before submitting the batch.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('loan-migration.records.import', batch.id)"
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
                                d="M12 16V4m0 0l-4 4m4-4l4 4M5 20h14"
                            />
                        </svg>

                        Import More
                    </Link>

                    <button
                        type="button"
                        :disabled="processing"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-xs font-semibold text-white shadow-lg shadow-amber-500/20 transition hover:bg-amber-600 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="runValidation"
                    >
                        <svg
                            v-if="processing"
                            class="h-4 w-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                            />
                        </svg>

                        <svg
                            v-else
                            class="h-4 w-4"
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

                        {{ processing ? 'Validating...' : 'Run Validation' }}
                    </button>
                </div>
            </div>

            <!-- Progress -->
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-bold">
                            Validation Progress
                        </p>

                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                            {{ statistics.valid + statistics.invalid }}
                            of
                            {{ statistics.total }}
                            records checked
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-xl font-bold">
                            {{ validationPercentage }}%
                        </p>
                    </div>
                </div>

                <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div
                        class="h-full rounded-full bg-amber-500 transition-all duration-500"
                        :style="{ width: `${validationPercentage}%` }"
                    />
                </div>
            </section>

            <!-- Statistics -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[10px] uppercase tracking-wider text-slate-400">
                        Total
                    </p>

                    <p class="mt-2 text-xl font-bold">
                        {{ statistics.total }}
                    </p>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20">
                    <p class="text-[10px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        Valid
                    </p>

                    <p class="mt-2 text-xl font-bold text-emerald-700 dark:text-emerald-300">
                        {{ statistics.valid }}
                    </p>
                </div>

                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm dark:border-red-900/50 dark:bg-red-950/20">
                    <p class="text-[10px] uppercase tracking-wider text-red-600 dark:text-red-400">
                        Invalid
                    </p>

                    <p class="mt-2 text-xl font-bold text-red-700 dark:text-red-300">
                        {{ statistics.invalid }}
                    </p>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-900/50 dark:bg-amber-950/20">
                    <p class="text-[10px] uppercase tracking-wider text-amber-600 dark:text-amber-400">
                        Warnings
                    </p>

                    <p class="mt-2 text-xl font-bold text-amber-700 dark:text-amber-300">
                        {{ statistics.warnings }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[10px] uppercase tracking-wider text-slate-400">
                        Pending
                    </p>

                    <p class="mt-2 text-xl font-bold">
                        {{ statistics.pending }}
                    </p>
                </div>
            </div>

            <!-- Records -->
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold">
                            Validation Results
                        </h2>

                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                            Review each imported loan record.
                        </p>
                    </div>

                    <span class="text-[10px] text-slate-400">
                        {{ records.from ?? 0 }}–{{ records.to ?? 0 }}
                        of
                        {{ records.total }}
                    </span>
                </div>

                <!-- Desktop Table -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[900px] text-left">
                        <thead class="border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-5 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Loan
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
                                    Status
                                </th>

                                <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="record in records.data"
                                :key="record.id"
                                class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4">
                                    <p class="text-xs font-bold">
                                        {{ record.legacy_loan_number ?? `#${record.id}` }}
                                    </p>

                                    <p class="mt-1 text-[10px] text-slate-400">
                                        Record #{{ record.id }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-xs font-semibold">
                                        {{ memberName(record) }}
                                    </p>

                                    <p class="mt-1 text-[10px] text-slate-400">
                                        {{ memberNumber(record) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-xs">
                                        {{ productName(record) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-xs font-semibold">
                                        {{ formatCurrency(record.outstanding_balance) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        :class="statusClass(record.validation_status)"
                                        class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold"
                                    >
                                        {{ statusLabel(record.validation_status) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <Link
                                        :href="route('loan-migration.records.show', [
                                            batch.id,
                                            record.id,
                                        ])"
                                        class="inline-flex items-center gap-1.5 text-[10px] font-bold text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400"
                                    >
                                        Review

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
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="!records.data.length">
                                <td
                                    colspan="6"
                                    class="px-5 py-12 text-center"
                                >
                                    <div class="mx-auto max-w-sm">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                                            <svg
                                                class="h-6 w-6"
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

                                        <p class="mt-3 text-sm font-semibold">
                                            No migration records found
                                        </p>

                                        <p class="mt-1 text-[11px] text-slate-400">
                                            Import records into this batch first.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile -->
                <div class="divide-y divide-slate-100 md:hidden dark:divide-slate-800">
                    <div
                        v-for="record in records.data"
                        :key="record.id"
                        class="space-y-4 p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold">
                                    {{ record.legacy_loan_number ?? `#${record.id}` }}
                                </p>

                                <p class="mt-1 text-[10px] text-slate-400">
                                    {{ memberName(record) }}
                                </p>
                            </div>

                            <span
                                :class="statusClass(record.validation_status)"
                                class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold"
                            >
                                {{ statusLabel(record.validation_status) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-[9px] uppercase tracking-wider text-slate-400">
                                    Member No.
                                </p>

                                <p class="mt-1 text-xs font-medium">
                                    {{ memberNumber(record) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[9px] uppercase tracking-wider text-slate-400">
                                    Product
                                </p>

                                <p class="mt-1 truncate text-xs font-medium">
                                    {{ productName(record) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[9px] uppercase tracking-wider text-slate-400">
                                    Outstanding
                                </p>

                                <p class="mt-1 text-xs font-bold">
                                    {{ formatCurrency(record.outstanding_balance) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[9px] uppercase tracking-wider text-slate-400">
                                    Errors
                                </p>

                                <p class="mt-1 text-xs font-bold">
                                    {{ getErrors(record).length }}
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="route('loan-migration.records.show', [
                                batch.id,
                                record.id,
                            ])"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-[10px] font-bold text-blue-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-blue-400 dark:hover:bg-slate-800"
                        >
                            Review Record

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

                        <div
                            v-if="getErrors(record).length"
                            class="rounded-xl border border-red-200 bg-red-50 p-3 dark:border-red-900/50 dark:bg-red-950/20"
                        >
                            <p class="text-[10px] font-bold text-red-700 dark:text-red-300">
                                Validation errors
                            </p>

                            <ul class="mt-2 space-y-1">
                                <li
                                    v-for="(error, index) in getErrors(record).slice(0, 3)"
                                    :key="index"
                                    class="text-[10px] text-red-600 dark:text-red-400"
                                >
                                    • {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="records.links?.length && records.last_page > 1"
                    class="flex flex-wrap items-center justify-center gap-1 border-t border-slate-200 px-4 py-4 dark:border-slate-800"
                >
                    <template
                        v-for="(link, index) in records.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="pageUrl(link.url)"
                            :class="link.active
                                ? 'bg-blue-600 text-white'
                                : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'"
                            class="min-w-8 rounded-lg px-2.5 py-2 text-center text-[10px] font-semibold"
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="min-w-8 rounded-lg px-2.5 py-2 text-center text-[10px] text-slate-300 dark:text-slate-600"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </section>

            <!-- Submit -->
            <section
                :class="statistics.invalid > 0
                    ? 'border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-950/20'
                    : 'border-emerald-200 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-950/20'"
                class="rounded-2xl border p-5"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <svg
                            class="mt-0.5 h-5 w-5 shrink-0"
                            :class="statistics.invalid > 0
                                ? 'text-red-600 dark:text-red-400'
                                : 'text-emerald-600 dark:text-emerald-400'"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                v-if="statistics.invalid > 0"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v4m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"
                            />

                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>

                        <div>
                            <p
                                :class="statistics.invalid > 0
                                    ? 'text-red-700 dark:text-red-300'
                                    : 'text-emerald-700 dark:text-emerald-300'"
                                class="text-xs font-bold"
                            >
                                {{
                                    statistics.invalid > 0
                                        ? 'Batch cannot be submitted yet'
                                        : 'Batch is ready for submission'
                                }}
                            </p>

                            <p
                                :class="statistics.invalid > 0
                                    ? 'text-red-600 dark:text-red-400'
                                    : 'text-emerald-600 dark:text-emerald-400'"
                                class="mt-1 text-[10px]"
                            >
                                {{
                                    statistics.invalid > 0
                                        ? `${statistics.invalid} record(s) must be corrected before submission.`
                                        : 'All currently validated records have passed validation.'
                                }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        :disabled="statistics.invalid > 0 || !statistics.total || processing"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-xs font-semibold text-white shadow-lg transition disabled:cursor-not-allowed disabled:opacity-50"
                        :class="statistics.invalid > 0
                            ? 'bg-slate-400 shadow-none'
                            : 'bg-emerald-600 shadow-emerald-600/20 hover:bg-emerald-700'"
                        @click="submitBatch"
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

                        Submit Batch
                    </button>
                </div>
            </section>
        </div>
    </div>
</AppLayout> 
</template>