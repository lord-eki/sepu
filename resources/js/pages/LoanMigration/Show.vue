<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
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
    remarks: string | null

    total_records: number
    valid_records: number
    invalid_records: number
    processed_records: number

    total_original_amount: number | string
    total_amount_paid: number | string
    total_outstanding_balance: number | string

    created_at: string
    updated_at: string

    creator?: User | null
    submitter?: User | null
    verifier?: User | null
    approver?: User | null

    submitted_at?: string | null
    verified_at?: string | null
    approved_at?: string | null
    processed_at?: string | null
}

const props = defineProps<{
    batch: Batch
}>()

const batch = computed(() => props.batch)

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

const formatDate = (
    value: string | null | undefined,
    includeTime = false
) => {
    if (!value) return '—'

    return new Intl.DateTimeFormat('en-KE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        ...(includeTime
            ? {
                  hour: '2-digit',
                  minute: '2-digit',
              }
            : {}),
    }).format(new Date(value))
}

const statusLabel = computed(() => {
    return batch.value.status
        .replaceAll('_', ' ')
        .replace(/\b\w/g, letter => letter.toUpperCase())
})

const statusClasses = computed(() => {
    switch (batch.value.status) {
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
})

const validationPercentage = computed(() => {
    if (!batch.value.total_records) return 0

    return Math.round(
        (batch.value.valid_records / batch.value.total_records) * 100
    )
})

const processingPercentage = computed(() => {
    if (!batch.value.total_records) return 0

    return Math.round(
        (batch.value.processed_records / batch.value.total_records) * 100
    )
})

const isDraft = computed(() => batch.value.status === 'draft')

const isValidationFailed = computed(
    () => batch.value.status === 'validation_failed'
)

const isSubmitted = computed(() => batch.value.status === 'submitted')

const isVerified = computed(
    () => batch.value.status === 'accounts_verified'
)

const isApproved = computed(() => batch.value.status === 'approved')

const isProcessed = computed(() => batch.value.status === 'processed')
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            {
                title: 'Loan Migration',
                href: route('loan-migration.index')
            },
            {
                title: batch.batch_number
            }
        ]"
    >
        <Head :title="`Migration Batch ${batch.batch_number}`" />

    <div class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">

            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
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

                <span class="font-medium text-slate-700 dark:text-slate-300">
                    {{ batch.batch_number }}
                </span>
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
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
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.707.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                                {{ batch.batch_number }}
                            </h1>

                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold"
                                :class="statusClasses"
                            >
                                {{ statusLabel }}
                            </span>
                        </div>

                        <p
                            v-if="batch.description"
                            class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm"
                        >
                            {{ batch.description }}
                        </p>

                        <p
                            v-else
                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Loan migration batch
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('loan-migration.index')"
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

                        Back
                    </Link>

                    <!-- Capture Records -->
                    <button
                        v-if="isDraft || isValidationFailed"
                        type="button"
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

                        Capture Records
                    </button>
                </div>
            </div>

            <!-- Workflow -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-5 dark:border-slate-800">
                    <h2 class="text-sm font-bold sm:text-base">
                        Migration Workflow
                    </h2>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Track the progress of this migration batch.
                    </p>
                </div>

                <div class="overflow-x-auto p-5">
                    <div class="flex min-w-[760px] items-start">

                        <!-- Step 1 -->
                        <div class="flex flex-1 flex-col items-center text-center">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold"
                                :class="
                                    batch.status !== 'draft'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-blue-600 text-white'
                                "
                            >
                                <svg
                                    v-if="batch.status !== 'draft'"
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

                                <span v-else>1</span>
                            </div>

                            <p class="mt-2 text-xs font-bold">
                                Draft
                            </p>

                            <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                Batch created
                            </p>
                        </div>

                        <div class="mt-5 h-0.5 flex-1 bg-slate-200 dark:bg-slate-700"></div>

                        <!-- Step 2 -->
                        <div class="flex flex-1 flex-col items-center text-center">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold"
                                :class="
                                    ['submitted', 'accounts_verified', 'approved', 'processed'].includes(batch.status)
                                        ? 'bg-blue-600 text-white'
                                        : isValidationFailed
                                            ? 'bg-red-500 text-white'
                                            : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                "
                            >
                                <svg
                                    v-if="['submitted', 'accounts_verified', 'approved', 'processed'].includes(batch.status)"
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

                                <span v-else>2</span>
                            </div>

                            <p class="mt-2 text-xs font-bold">
                                Validation
                            </p>

                            <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                Validate records
                            </p>
                        </div>

                        <div class="mt-5 h-0.5 flex-1 bg-slate-200 dark:bg-slate-700"></div>

                        <!-- Step 3 -->
                        <div class="flex flex-1 flex-col items-center text-center">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold"
                                :class="
                                    ['accounts_verified', 'approved', 'processed'].includes(batch.status)
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                "
                            >
                                <svg
                                    v-if="['accounts_verified', 'approved', 'processed'].includes(batch.status)"
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

                                <span v-else>3</span>
                            </div>

                            <p class="mt-2 text-xs font-bold">
                                Verification
                            </p>

                            <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                Verify accounts
                            </p>
                        </div>

                        <div class="mt-5 h-0.5 flex-1 bg-slate-200 dark:bg-slate-700"></div>

                        <!-- Step 4 -->
                        <div class="flex flex-1 flex-col items-center text-center">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold"
                                :class="
                                    ['approved', 'processed'].includes(batch.status)
                                        ? 'bg-emerald-600 text-white'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                "
                            >
                                <svg
                                    v-if="['approved', 'processed'].includes(batch.status)"
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

                                <span v-else>4</span>
                            </div>

                            <p class="mt-2 text-xs font-bold">
                                Approval
                            </p>

                            <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                Approve migration
                            </p>
                        </div>

                        <div class="mt-5 h-0.5 flex-1 bg-slate-200 dark:bg-slate-700"></div>

                        <!-- Step 5 -->
                        <div class="flex flex-1 flex-col items-center text-center">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold"
                                :class="
                                    isProcessed
                                        ? 'bg-purple-600 text-white'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                "
                            >
                                <svg
                                    v-if="isProcessed"
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

                                <span v-else>5</span>
                            </div>

                            <p class="mt-2 text-xs font-bold">
                                Processing
                            </p>

                            <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                Create live loans
                            </p>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

                <!-- Total Records -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Total Records
                            </p>

                            <p class="mt-1 text-xl font-bold">
                                {{ formatNumber(batch.total_records) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-blue-50 p-2.5 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400">
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
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.707.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Valid -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Valid Records
                            </p>

                            <p class="mt-1 text-xl font-bold text-emerald-600 dark:text-emerald-400">
                                {{ formatNumber(batch.valid_records) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-emerald-50 p-2.5 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400">
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
                    </div>

                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div
                            class="h-full rounded-full bg-emerald-500 transition-all"
                            :style="{ width: `${validationPercentage}%` }"
                        ></div>
                    </div>

                    <p class="mt-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                        {{ validationPercentage }}% of total records
                    </p>
                </div>

                <!-- Invalid -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Invalid Records
                            </p>

                            <p class="mt-1 text-xl font-bold text-red-600 dark:text-red-400">
                                {{ formatNumber(batch.invalid_records) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-red-50 p-2.5 text-red-600 dark:bg-red-950/30 dark:text-red-400">
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
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.732 16c-.77 1.333.192 3 1.732 3z"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-3 text-[10px] text-slate-500 dark:text-slate-400">
                        Records requiring correction
                    </p>
                </div>

                <!-- Processed -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Processed
                            </p>

                            <p class="mt-1 text-xl font-bold text-purple-600 dark:text-purple-400">
                                {{ formatNumber(batch.processed_records) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-purple-50 p-2.5 text-purple-600 dark:bg-purple-950/30 dark:text-purple-400">
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
                    </div>

                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div
                            class="h-full rounded-full bg-purple-500 transition-all"
                            :style="{ width: `${processingPercentage}%` }"
                        ></div>
                    </div>

                    <p class="mt-1.5 text-[10px] text-slate-500 dark:text-slate-400">
                        {{ processingPercentage }}% processed
                    </p>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-5 dark:border-slate-800">
                    <h2 class="text-sm font-bold sm:text-base">
                        Financial Summary
                    </h2>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Financial position of the loans contained in this batch.
                    </p>
                </div>

                <div class="grid divide-y divide-slate-100 dark:divide-slate-800 sm:grid-cols-3 sm:divide-x sm:divide-y-0">

                    <div class="p-5">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Total Original Amount
                        </p>

                        <p class="mt-2 text-lg font-bold">
                            {{ formatCurrency(batch.total_original_amount) }}
                        </p>
                    </div>

                    <div class="p-5">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Total Amount Paid
                        </p>

                        <p class="mt-2 text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            {{ formatCurrency(batch.total_amount_paid) }}
                        </p>
                    </div>

                    <div class="p-5">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Outstanding Balance
                        </p>

                        <p class="mt-2 text-lg font-bold text-blue-600 dark:text-blue-400">
                            {{ formatCurrency(batch.total_outstanding_balance) }}
                        </p>
                    </div>

                </div>
            </div>

            <!-- Current Action -->
            <div
                v-if="isDraft || isValidationFailed"
                class="rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900/50 dark:bg-blue-950/20"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex gap-3">
                        <div class="shrink-0 text-blue-600 dark:text-blue-400">
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
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-blue-900 dark:text-blue-200">
                                {{ isValidationFailed ? 'Correct Migration Records' : 'Capture Loan Records' }}
                            </h3>

                            <p class="mt-1 text-xs leading-5 text-blue-700 dark:text-blue-300">
                                {{
                                    isValidationFailed
                                        ? 'Some records failed validation. Review and correct them before submitting the batch.'
                                        : 'Add the existing loan records from paper files, loan cards, registers or migration spreadsheets.'
                                }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-blue-700"
                    >
                        {{ isValidationFailed ? 'Review Records' : 'Start Capturing' }}

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
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Submitted -->
            <div
                v-else-if="isSubmitted"
                class="rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900/50 dark:bg-blue-950/20"
            >
                <div class="flex gap-3">
                    <div class="text-blue-600 dark:text-blue-400">
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

                    <div>
                        <h3 class="text-sm font-bold text-blue-900 dark:text-blue-200">
                            Batch Submitted
                        </h3>

                        <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">
                            This batch has been submitted and is awaiting account verification.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Verified -->
            <div
                v-else-if="isVerified"
                class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 dark:border-indigo-900/50 dark:bg-indigo-950/20"
            >
                <div class="flex gap-3">
                    <div class="text-indigo-600 dark:text-indigo-400">
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                fill="currentColor"
                                d="M12 2l7 4v5c0 5-3.5 9.5-7 11-3.5-1.5-7-6-7-11V6l7-4z"
                            />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-indigo-900 dark:text-indigo-200">
                            Accounts Verified
                        </h3>

                        <p class="mt-1 text-xs text-indigo-700 dark:text-indigo-300">
                            Account verification has been completed. The batch is ready for approval.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Approved -->
            <div
                v-else-if="isApproved"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-900/50 dark:bg-emerald-950/20"
            >
                <div class="flex gap-3">
                    <div class="text-emerald-600 dark:text-emerald-400">
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

                    <div>
                        <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-200">
                            Batch Approved
                        </h3>

                        <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">
                            This migration batch has been approved and is ready for processing.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Processed -->
            <div
                v-else-if="isProcessed"
                class="rounded-2xl border border-purple-200 bg-purple-50 p-5 dark:border-purple-900/50 dark:bg-purple-950/20"
            >
                <div class="flex gap-3">
                    <div class="text-purple-600 dark:text-purple-400">
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                fill="currentColor"
                                d="M12 2l2.5 6.5L21 11l-6.5 2.5L12 20l-2.5-6.5L3 11l6.5-2.5L12 2z"
                            />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-purple-900 dark:text-purple-200">
                            Migration Processed
                        </h3>

                        <p class="mt-1 text-xs text-purple-700 dark:text-purple-300">
                            All approved records in this migration batch have been processed into the system.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Audit / People -->
            <div class="grid gap-5 lg:grid-cols-2">

                <!-- Batch Information -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-bold">
                            Batch Information
                        </h2>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Batch Number
                            </span>

                            <span class="text-xs font-semibold">
                                {{ batch.batch_number }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Created
                            </span>

                            <span class="text-xs font-semibold">
                                {{ formatDate(batch.created_at, true) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Last Updated
                            </span>

                            <span class="text-xs font-semibold">
                                {{ formatDate(batch.updated_at, true) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Status
                            </span>

                            <span
                                class="rounded-full px-2.5 py-1 text-[10px] font-bold"
                                :class="statusClasses"
                            >
                                {{ statusLabel }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Users -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="text-sm font-bold">
                            Workflow Officers
                        </h2>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">

                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Created By
                            </span>

                            <span class="text-xs font-semibold">
                                {{ batch.creator?.name ?? '—' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Submitted By
                            </span>

                            <span class="text-xs font-semibold">
                                {{ batch.submitter?.name ?? '—' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Verified By
                            </span>

                            <span class="text-xs font-semibold">
                                {{ batch.verifier?.name ?? '—' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                Approved By
                            </span>

                            <span class="text-xs font-semibold">
                                {{ batch.approver?.name ?? '—' }}
                            </span>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Remarks -->
            <div
                v-if="batch.remarks"
                class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="text-sm font-bold">
                        Remarks
                    </h2>
                </div>

                <div class="px-5 py-5">
                    <p class="whitespace-pre-line text-xs leading-6 text-slate-600 dark:text-slate-300">
                        {{ batch.remarks }}
                    </p>
                </div>
            </div>

        </div>
    </div>
</AppLayout>
</template>
