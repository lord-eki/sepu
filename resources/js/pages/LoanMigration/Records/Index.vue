<script setup lang="ts">
import { computed, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Member {
    id: number
    member_number?: string | null
    name?: string | null
}

interface LoanRecord {
    id: number
    batch_id: number
    member_id?: number | null
    member?: Member | null

    legacy_loan_number?: string | null
    loan_number?: string | null
    loan_product?: string | null

    original_amount: number | string
    amount_paid: number | string
    outstanding_balance: number | string

    interest_rate?: number | string | null
    repayment_period?: number | null

    disbursement_date?: string | null
    maturity_date?: string | null

    status: string

    validation_status?: string | null
    validation_errors?: string[] | null

    created_at: string
    updated_at: string
}

interface Batch {
    id: number
    batch_number: string
    description?: string | null
    status: string
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface PaginatedRecords {
    current_page: number
    data: LoanRecord[]
    first_page_url: string
    from: number | null
    last_page: number
    last_page_url: string
    links: PaginationLink[]
    next_page_url: string | null
    path: string
    per_page: number
    prev_page_url: string | null
    to: number | null
    total: number
}

interface StatusOption {
    value: string
    label: string
}

interface Props {
    batch: Batch
    records: PaginatedRecords
    filters?: {
        search?: string | null
        status?: string | null
        validation_status?: string | null
    }
    statuses?: StatusOption[]
    validationStatuses?: StatusOption[]
}

const props = defineProps<Props>()

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
    validation_status: props.filters?.validation_status ?? '',
})

const showFilters = reactive({
    value: false,
})

const isFiltering = computed(() => {
    return (
        filters.search !== '' ||
        filters.status !== '' ||
        filters.validation_status !== ''
    )
})

const formatNumber = (value: number | string | null | undefined) => {
    const number = Number(value ?? 0)

    return new Intl.NumberFormat('en-KE').format(
        Number.isNaN(number) ? 0 : number
    )
}

const formatCurrency = (value: number | string | null | undefined) => {
    const number = Number(value ?? 0)

    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number.isNaN(number) ? 0 : number)
}

const formatDate = (value: string | null | undefined) => {
    if (!value) return '—'

    const date = new Date(value)

    if (Number.isNaN(date.getTime())) {
        return '—'
    }

    return new Intl.DateTimeFormat('en-KE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date)
}

const getStatusLabel = (status: string | null | undefined) => {
    if (!status) return 'Unknown'

    const found = props.statuses?.find(
        item => item.value === status
    )

    if (found) {
        return found.label
    }

    return status
        .replaceAll('_', ' ')
        .replace(/\b\w/g, letter => letter.toUpperCase())
}

const getStatusClasses = (status: string | null | undefined) => {
    switch (status) {
        case 'valid':
        case 'validated':
        case 'approved':
        case 'processed':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'

        case 'invalid':
        case 'validation_failed':
        case 'rejected':
            return 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300'

        case 'pending':
        case 'draft':
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'

        case 'warning':
            return 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300'

        case 'verified':
            return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300'

        default:
            return 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300'
    }
}

const getValidationClasses = (
    status: string | null | undefined
) => {
    switch (status) {
        case 'valid':
        case 'validated':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'

        case 'invalid':
        case 'validation_failed':
            return 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300'

        case 'warning':
            return 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300'

        default:
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    }
}

const getMemberName = (record: LoanRecord) => {
    if (record.member?.name) {
        return record.member.name
    }

    return 'Unmatched Member'
}

const getMemberNumber = (record: LoanRecord) => {
    return record.member?.member_number ?? '—'
}

const applyFilters = () => {
    router.get(
        route('loan-migration.records.index', props.batch.id),
        {
            search: filters.search || undefined,
            status: filters.status || undefined,
            validation_status:
                filters.validation_status || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const clearFilters = () => {
    filters.search = ''
    filters.status = ''
    filters.validation_status = ''

    router.get(
        route('loan-migration.records.index', props.batch.id),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const goToPage = (url: string | null) => {
    if (!url) return

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        }
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
                title: 'Migration Records'
            }
        ]"
    >
        <Head title="Migration Records" />

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
                    Loan Records
                </span>
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
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
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                                Loan Records
                            </h1>

                            <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                {{ batch.batch_number }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                            Review and manage loan records within this migration batch.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
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

                        Batch Overview
                    </Link>

                    <Link
                        :href="route('loan-migration.records.create', batch.id)"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700"
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
                                d="M12 4v16m8-8H4"
                            />
                        </svg>

                        Add Loan Record
                    </Link>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Total Records
                    </p>

                    <p class="mt-1 text-xl font-bold">
                        {{ formatNumber(records.total) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Current Page
                    </p>

                    <p class="mt-1 text-xl font-bold">
                        {{ formatNumber(records.data.length) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Valid Records
                    </p>

                    <p class="mt-1 text-xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{
                            formatNumber(
                                records.data.filter(
                                    record =>
                                        record.validation_status === 'valid' ||
                                        record.validation_status === 'validated'
                                ).length
                            )
                        }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[10px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Invalid Records
                    </p>

                    <p class="mt-1 text-xl font-bold text-red-600 dark:text-red-400">
                        {{
                            formatNumber(
                                records.data.filter(
                                    record =>
                                        record.validation_status === 'invalid' ||
                                        record.validation_status === 'validation_failed'
                                ).length
                            )
                        }}
                    </p>
                </div>
            </div>

            <!-- Records Card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                <!-- Card Header -->
                <div class="border-b border-slate-200 p-4 dark:border-slate-800 sm:p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                        <div>
                            <h2 class="text-sm font-bold sm:text-base">
                                Migrated Loan Records
                            </h2>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Search, review and correct legacy loan records.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex w-fit items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            @click="showFilters.value = !showFilters.value"
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
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-7.293a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                                />
                            </svg>

                            Filters

                            <span
                                v-if="isFiltering"
                                class="h-2 w-2 rounded-full bg-blue-600"
                            ></span>
                        </button>
                    </div>

                    <!-- Filters -->
                    <div
                        v-if="showFilters.value"
                        class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50"
                    >
                        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1fr_200px_200px_auto]">

                            <!-- Search -->
                            <div class="relative">
                                <svg
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-4.35-4.35m2.35-5.65a8 8 0 11-16 0 8 8 0 0116 0z"
                                    />
                                </svg>

                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Search member or loan number..."
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                    @keyup.enter="applyFilters"
                                />
                            </div>

                            <!-- Record Status -->
                            <select
                                v-model="filters.status"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                @change="applyFilters"
                            >
                                <option value="">
                                    All record statuses
                                </option>

                                <option
                                    v-for="status in statuses ?? []"
                                    :key="status.value"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </option>
                            </select>

                            <!-- Validation Status -->
                            <select
                                v-model="filters.validation_status"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                @change="applyFilters"
                            >
                                <option value="">
                                    All validation statuses
                                </option>

                                <option
                                    v-for="status in validationStatuses ?? []"
                                    :key="status.value"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </option>
                            </select>

                            <!-- Buttons -->
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="flex-1 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-700"
                                    @click="applyFilters"
                                >
                                    Search
                                </button>

                                <button
                                    v-if="isFiltering"
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                                    @click="clearFilters"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="records.data.length === 0"
                    class="px-6 py-16 text-center"
                >
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <svg
                            class="h-7 w-7"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-sm font-bold">
                        No loan records found
                    </h3>

                    <p class="mx-auto mt-1 max-w-md text-xs text-slate-500 dark:text-slate-400">
                        {{
                            isFiltering
                                ? 'No records match the selected search or filters.'
                                : 'No loan records have been captured in this migration batch yet.'
                        }}
                    </p>

                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                        <button
                            v-if="isFiltering"
                            type="button"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="clearFilters"
                        >
                            Clear Filters
                        </button>

                        <Link
                            :href="route('loan-migration.records.create', batch.id)"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-blue-700"
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
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>

                            Add Loan Record
                        </Link>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div
                    v-else
                    class="hidden overflow-x-auto md:block"
                >
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                                <th class="px-5 py-3 font-semibold">
                                    Member / Loan
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Loan Product
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Original Amount
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Paid
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Outstanding
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Validation
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Status
                                </th>

                                <th class="px-5 py-3 text-right font-semibold">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="record in records.data"
                                :key="record.id"
                                class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            >
                                <!-- Member -->
                                <td class="px-5 py-4">
                                    <div>
                                        <p class="text-xs font-bold">
                                            {{ getMemberName(record) }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                            Member:
                                            {{ getMemberNumber(record) }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                            Legacy Loan:
                                            {{ record.legacy_loan_number ?? '—' }}
                                        </p>
                                    </div>
                                </td>

                                <!-- Product -->
                                <td class="px-5 py-4">
                                    <div>
                                        <p class="text-xs font-medium">
                                            {{ record.loan_product ?? '—' }}
                                        </p>

                                        <p
                                            v-if="record.loan_number"
                                            class="mt-1 text-[10px] text-slate-500 dark:text-slate-400"
                                        >
                                            {{ record.loan_number }}
                                        </p>
                                    </div>
                                </td>

                                <!-- Original -->
                                <td class="px-5 py-4">
                                    <span class="text-xs font-semibold">
                                        {{ formatCurrency(record.original_amount) }}
                                    </span>
                                </td>

                                <!-- Paid -->
                                <td class="px-5 py-4">
                                    <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                        {{ formatCurrency(record.amount_paid) }}
                                    </span>
                                </td>

                                <!-- Outstanding -->
                                <td class="px-5 py-4">
                                    <span class="text-xs font-bold">
                                        {{ formatCurrency(record.outstanding_balance) }}
                                    </span>
                                </td>

                                <!-- Validation -->
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-[9px] font-bold"
                                        :class="getValidationClasses(record.validation_status)"
                                    >
                                        {{
                                            getStatusLabel(
                                                record.validation_status
                                            )
                                        }}
                                    </span>

                                    <p
                                        v-if="
                                            record.validation_errors &&
                                            record.validation_errors.length
                                        "
                                        class="mt-1 text-[9px] text-red-500"
                                    >
                                        {{ record.validation_errors.length }}
                                        issue(s)
                                    </p>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2 py-1 text-[9px] font-bold"
                                        :class="getStatusClasses(record.status)"
                                    >
                                        {{ getStatusLabel(record.status) }}
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-1">
                                        <Link
                                            :href="route('loan-migration.records.show', [batch.id, record.id])"
                                            class="rounded-lg px-2.5 py-2 text-xs font-semibold text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/30"
                                        >
                                            View
                                        </Link>

                                        <Link
                                            :href="route('loan-migration.records.edit', [batch.id, record.id])"
                                            class="rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                                        >
                                            Edit
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div
                    v-if="records.data.length"
                    class="divide-y divide-slate-100 dark:divide-slate-800 md:hidden"
                >
                    <div
                        v-for="record in records.data"
                        :key="record.id"
                        class="p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <Link
                                    :href="route('loan-migration.records.show', [batch.id, record.id])"
                                    class="text-sm font-bold text-blue-600 dark:text-blue-400"
                                >
                                    {{ getMemberName(record) }}
                                </Link>

                                <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                    {{ getMemberNumber(record) }}
                                </p>

                                <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                    Legacy Loan:
                                    {{ record.legacy_loan_number ?? '—' }}
                                </p>
                            </div>

                            <span
                                class="shrink-0 rounded-full px-2 py-1 text-[9px] font-bold"
                                :class="getValidationClasses(record.validation_status)"
                            >
                                {{ getStatusLabel(record.validation_status) }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                                <p class="text-[9px] text-slate-500 dark:text-slate-400">
                                    Loan Product
                                </p>

                                <p class="mt-1 truncate text-xs font-semibold">
                                    {{ record.loan_product ?? '—' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                                <p class="text-[9px] text-slate-500 dark:text-slate-400">
                                    Status
                                </p>

                                <span
                                    class="mt-1 inline-flex rounded-full px-2 py-1 text-[9px] font-bold"
                                    :class="getStatusClasses(record.status)"
                                >
                                    {{ getStatusLabel(record.status) }}
                                </span>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                                <p class="text-[9px] text-slate-500 dark:text-slate-400">
                                    Original Amount
                                </p>

                                <p class="mt-1 text-xs font-bold">
                                    {{ formatCurrency(record.original_amount) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                                <p class="text-[9px] text-slate-500 dark:text-slate-400">
                                    Outstanding
                                </p>

                                <p class="mt-1 text-xs font-bold">
                                    {{ formatCurrency(record.outstanding_balance) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            <Link
                                :href="route('loan-migration.records.show', [batch.id, record.id])"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:text-slate-300"
                            >
                                View
                            </Link>

                            <Link
                                :href="route('loan-migration.records.edit', [batch.id, record.id])"
                                class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white"
                            >
                                Edit
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="records.last_page > 1"
                    class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Showing
                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                            {{ records.from ?? 0 }}
                        </span>
                        to
                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                            {{ records.to ?? 0 }}
                        </span>
                        of
                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                            {{ formatNumber(records.total) }}
                        </span>
                        records
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            type="button"
                            :disabled="!records.prev_page_url"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="goToPage(records.prev_page_url)"
                        >
                            Previous
                        </button>

                        <template
                            v-for="(link, index) in records.links.slice(1, -1)"
                            :key="index"
                        >
                            <button
                                v-if="link.url"
                                type="button"
                                class="min-w-9 rounded-lg border px-3 py-2 text-xs font-semibold transition"
                                :class="
                                    link.active
                                        ? 'border-blue-600 bg-blue-600 text-white'
                                        : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800'
                                "
                                @click="goToPage(link.url)"
                                v-html="link.label"
                            />

                            <span
                                v-else
                                class="px-2 text-xs text-slate-400"
                                v-html="link.label"
                            />
                        </template>

                        <button
                            type="button"
                            :disabled="!records.next_page_url"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="goToPage(records.next_page_url)"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
 </AppLayout>
</template>
