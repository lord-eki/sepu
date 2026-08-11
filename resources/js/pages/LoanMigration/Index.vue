<script setup lang="ts">
import { computed, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface User {
    id: number
    name: string
}

interface Batch {
    id: number
    batch_number: string
    description: string | null
    status: string
    total_records: number
    valid_records: number
    invalid_records: number
    processed_records: number
    total_original_amount: number | string
    total_amount_paid: number | string
    total_outstanding_balance: number | string
    created_at: string
    creator?: User | null
    submitter?: User | null
    verifier?: User | null
    approver?: User | null
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface PaginatedBatches {
    current_page: number
    data: Batch[]
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

interface Statistics {
    total_batches: number
    draft: number
    validation_failed: number
    submitted: number
    accounts_verified: number
    approved: number
    processed: number
    total_records: number
    total_outstanding_balance: number
}

interface StatusOption {
    value: string
    label: string
}

interface Filters {
    search?: string | null
    status?: string | null
}

const props = defineProps<{
    batches: PaginatedBatches
    statistics: Statistics
    filters: Filters
    statuses: StatusOption[]
}>()

const filters = reactive({
    search: props.filters?.search ?? '',
    status: props.filters?.status ?? '',
})

const showFilters = reactive({
    value: false,
})

const isFiltering = computed(() => {
    return filters.search !== '' || filters.status !== ''
})

const formatCurrency = (value: number | string) => {
    const amount = Number(value ?? 0)

    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number.isNaN(amount) ? 0 : amount)
}

const formatNumber = (value: number | string) => {
    const amount = Number(value ?? 0)

    return new Intl.NumberFormat('en-KE').format(
        Number.isNaN(amount) ? 0 : amount
    )
}

const formatDate = (date: string) => {
    if (!date) return '—'

    return new Intl.DateTimeFormat('en-KE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date))
}

const getStatusLabel = (status: string) => {
    const found = props.statuses.find(item => item.value === status)

    if (found) {
        return found.label
    }

    return status
        .replaceAll('_', ' ')
        .replace(/\b\w/g, letter => letter.toUpperCase())
}

const getStatusClasses = (status: string) => {
    switch (status) {
        case 'draft':
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'

        case 'validation_failed':
            return 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300'

        case 'submitted':
            return 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300'

        case 'accounts_verified':
            return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300'

        case 'approved':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'

        case 'processed':
            return 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300'

        default:
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    }
}

const applyFilters = () => {
    router.get(
        route('loan-migration.index'),
        {
            search: filters.search || undefined,
            status: filters.status || undefined,
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

    router.get(
        route('loan-migration.index'),
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
                title: 'Migration Dashboard'
            }
        ]"
    >
        <Head title="Loan Migration Dashboard" />

    <div class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20"
                        >
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
                            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                                Loan Migration
                            </h1>

                            <p class="text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                                Digitize and manage existing loan records.
                            </p>
                        </div>
                    </div>
                </div>

                <Link
                    :href="route('loan-migration.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950"
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

                    New Migration Batch
                </Link>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8">

                <!-- Total Batches -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Batches
                        </span>

                        <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="text-xl font-bold">
                        {{ formatNumber(statistics.total_batches) }}
                    </p>
                </div>

                <!-- Draft -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Draft
                        </span>

                        <div class="rounded-lg bg-slate-100 p-2 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="text-xl font-bold">
                        {{ formatNumber(statistics.draft) }}
                    </p>
                </div>

                <!-- Validation Failed -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Failed
                        </span>

                        <div class="rounded-lg bg-red-50 p-2 text-red-600 dark:bg-red-950/40 dark:text-red-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.732 16c-.77 1.333.192 3 1.732 3z"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="text-xl font-bold">
                        {{ formatNumber(statistics.validation_failed) }}
                    </p>
                </div>

                <!-- Submitted -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Submitted
                        </span>

                        <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="text-xl font-bold">
                        {{ formatNumber(statistics.submitted) }}
                    </p>
                </div>

                <!-- Verified -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Verified
                        </span>

                        <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622C17.176 19.29 21 14.591 21 9c0-1.042-.133-2.052-.382-3.016z"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="text-xl font-bold">
                        {{ formatNumber(statistics.accounts_verified) }}
                    </p>
                </div>

                <!-- Approved -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Approved
                        </span>

                        <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="text-xl font-bold">
                        {{ formatNumber(statistics.approved) }}
                    </p>
                </div>

                <!-- Processed -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Processed
                        </span>

                        <div class="rounded-lg bg-purple-50 p-2 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="text-xl font-bold">
                        {{ formatNumber(statistics.processed) }}
                    </p>
                </div>

                <!-- Records -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Loan Records
                        </span>

                        <div class="rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 17v-2m3 2v-6m3 6v-4m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="text-xl font-bold">
                        {{ formatNumber(statistics.total_records) }}
                    </p>
                </div>
            </div>

            <!-- Outstanding Balance Summary -->
            <div class="rounded-2xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-5 dark:border-blue-900/50 dark:from-blue-950/30 dark:to-indigo-950/30">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-blue-700 dark:text-blue-300">
                                Total Outstanding Loan Balance
                            </p>

                            <p class="mt-0.5 text-xs text-blue-600/70 dark:text-blue-400/70">
                                Across all migration batches
                            </p>
                        </div>
                    </div>

                    <p class="text-xl font-bold text-blue-700 dark:text-blue-300 sm:text-2xl">
                        {{ formatCurrency(statistics.total_outstanding_balance) }}
                    </p>
                </div>
            </div>

            <!-- Batches Section -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                <!-- Section Header -->
                <div class="border-b border-slate-200 p-4 dark:border-slate-800 sm:p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                        <div>
                            <h2 class="text-base font-bold sm:text-lg">
                                Migration Batches
                            </h2>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Manage and monitor imported loan records.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                @click="showFilters.value = !showFilters.value"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    class="flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] text-white"
                                >
                                    !
                                </span>
                            </button>

                            <Link
                                :href="route('loan-migration.create')"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v16m8-8H4"
                                    />
                                </svg>

                                New Batch
                            </Link>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div
                        v-if="showFilters.value"
                        class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50"
                    >
                        <div class="grid gap-3 md:grid-cols-[1fr_220px_auto]">

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
                                    placeholder="Search batch number or description..."
                                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                    @keyup.enter="applyFilters"
                                />
                            </div>

                            <!-- Status -->
                            <select
                                v-model="filters.status"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                @change="applyFilters"
                            >
                                <option value="">
                                    All statuses
                                </option>

                                <option
                                    v-for="status in statuses"
                                    :key="status.value"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </option>
                            </select>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="flex-1 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 md:flex-none"
                                    @click="applyFilters"
                                >
                                    Search
                                </button>

                                <button
                                    v-if="isFiltering"
                                    type="button"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
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
                    v-if="batches.data.length === 0"
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
                        No migration batches found
                    </h3>

                    <p class="mx-auto mt-1 max-w-md text-xs text-slate-500 dark:text-slate-400">
                        {{
                            isFiltering
                                ? 'No batches match your current search or status filter.'
                                : 'Create your first migration batch to begin digitizing existing loan records.'
                        }}
                    </p>

                    <button
                        v-if="isFiltering"
                        type="button"
                        class="mt-4 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                        @click="clearFilters"
                    >
                        Clear filters
                    </button>

                    <Link
                        v-else
                        :href="route('loan-migration.create')"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>

                        Create Migration Batch
                    </Link>
                </div>

                <!-- Desktop Table -->
                <div
                    v-else
                    class="hidden overflow-x-auto md:block"
                >
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                                <th class="px-5 py-3 font-semibold">
                                    Batch
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Status
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Records
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Original Amount
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Outstanding
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Created By
                                </th>

                                <th class="px-5 py-3 font-semibold">
                                    Date
                                </th>

                                <th class="px-5 py-3 text-right font-semibold">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="batch in batches.data"
                                :key="batch.id"
                                class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-5 py-4">
                                    <div>
                                        <Link
                                            :href="route('loan-migration.show', batch.id)"
                                            class="text-sm font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                        >
                                            {{ batch.batch_number }}
                                        </Link>

                                        <p
                                            v-if="batch.description"
                                            class="mt-1 max-w-[220px] truncate text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            {{ batch.description }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold"
                                        :class="getStatusClasses(batch.status)"
                                    >
                                        {{ getStatusLabel(batch.status) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold">
                                        {{ formatNumber(batch.total_records) }}
                                    </div>

                                    <div class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                        {{ formatNumber(batch.valid_records) }} valid
                                        <span class="mx-1">•</span>
                                        {{ formatNumber(batch.invalid_records) }} invalid
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-sm font-medium">
                                        {{ formatCurrency(batch.total_original_amount) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-sm font-semibold">
                                        {{ formatCurrency(batch.total_outstanding_balance) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-xs text-slate-600 dark:text-slate-300">
                                        {{ batch.creator?.name ?? '—' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ formatDate(batch.created_at) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <Link
                                        :href="route('loan-migration.show', batch.id)"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-2 text-xs font-semibold text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/30"
                                    >
                                        View

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
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div
                    v-if="batches.data.length"
                    class="divide-y divide-slate-100 dark:divide-slate-800 md:hidden"
                >
                    <div
                        v-for="batch in batches.data"
                        :key="batch.id"
                        class="p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <Link
                                    :href="route('loan-migration.show', batch.id)"
                                    class="text-sm font-bold text-blue-600 dark:text-blue-400"
                                >
                                    {{ batch.batch_number }}
                                </Link>

                                <p
                                    v-if="batch.description"
                                    class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ batch.description }}
                                </p>
                            </div>

                            <span
                                class="shrink-0 rounded-full px-2 py-1 text-[9px] font-bold"
                                :class="getStatusClasses(batch.status)"
                            >
                                {{ getStatusLabel(batch.status) }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Records
                                </p>

                                <p class="mt-1 text-sm font-bold">
                                    {{ formatNumber(batch.total_records) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Processed
                                </p>

                                <p class="mt-1 text-sm font-bold">
                                    {{ formatNumber(batch.processed_records) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Original Amount
                                </p>

                                <p class="mt-1 text-xs font-bold">
                                    {{ formatCurrency(batch.total_original_amount) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
                                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Outstanding
                                </p>

                                <p class="mt-1 text-xs font-bold">
                                    {{ formatCurrency(batch.total_outstanding_balance) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] text-slate-400">
                                    Created by {{ batch.creator?.name ?? 'Unknown' }}
                                </p>

                                <p class="mt-0.5 text-[10px] text-slate-400">
                                    {{ formatDate(batch.created_at) }}
                                </p>
                            </div>

                            <Link
                                :href="route('loan-migration.show', batch.id)"
                                class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-600 dark:bg-blue-950/30 dark:text-blue-400"
                            >
                                View Batch

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
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="batches.last_page > 1"
                    class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Showing
                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                            {{ batches.from ?? 0 }}
                        </span>
                        to
                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                            {{ batches.to ?? 0 }}
                        </span>
                        of
                        <span class="font-semibold text-slate-700 dark:text-slate-200">
                            {{ formatNumber(batches.total) }}
                        </span>
                        batches
                    </p>

                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            type="button"
                            :disabled="!batches.prev_page_url"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="goToPage(batches.prev_page_url)"
                        >
                            Previous
                        </button>

                        <template
                            v-for="(link, index) in batches.links.slice(1, -1)"
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
                            :disabled="!batches.next_page_url"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="goToPage(batches.next_page_url)"
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

