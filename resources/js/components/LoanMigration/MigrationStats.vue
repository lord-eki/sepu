<script setup lang="ts">
import { computed } from 'vue'

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

interface Props {
    statistics: Statistics
}

const props = defineProps<Props>()

const formatNumber = (value: number | string | null | undefined): string => {
    const number = Number(value ?? 0)

    return new Intl.NumberFormat('en-KE').format(
        Number.isNaN(number) ? 0 : number,
    )
}

const formatCurrency = (
    value: number | string | null | undefined,
): string => {
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

const validationRate = computed(() => {
    if (!props.statistics.total_records) {
        return 0
    }

    const valid =
        props.statistics.total_records -
        props.statistics.validation_failed

    return Math.max(
        0,
        Math.min(
            100,
            Math.round((valid / props.statistics.total_records) * 100),
        ),
    )
})
</script>

<template>
    <section class="space-y-4">

        <!-- Main Statistics -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <!-- Total Batches -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p
                            class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"
                        >
                            Total Batches
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ formatNumber(statistics.total_batches) }}
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
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
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between text-[10px]">
                    <span class="text-slate-400">
                        Draft
                    </span>

                    <span class="font-semibold text-slate-600 dark:text-slate-300">
                        {{ formatNumber(statistics.draft) }}
                    </span>
                </div>
            </div>

            <!-- Total Records -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p
                            class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"
                        >
                            Total Records
                        </p>

                        <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">
                            {{ formatNumber(statistics.total_records) }}
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400"
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
                                d="M4 6h16M4 10h16M4 14h10M4 18h10"
                            />
                        </svg>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="mb-1 flex items-center justify-between text-[10px]">
                        <span class="text-slate-400">
                            Validation readiness
                        </span>

                        <span class="font-semibold text-blue-600 dark:text-blue-400">
                            {{ validationRate }}%
                        </span>
                    </div>

                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div
                            class="h-full rounded-full bg-blue-600 transition-all duration-500"
                            :style="{ width: `${validationRate}%` }"
                        />
                    </div>
                </div>
            </div>

            <!-- Outstanding Balance -->
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p
                            class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"
                        >
                            Outstanding Balance
                        </p>

                        <p class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                            {{ formatCurrency(statistics.total_outstanding_balance) }}
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400"
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
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 text-[10px] text-slate-400">
                    Across all migration batches
                </div>
            </div>

            <!-- Processed -->
            <div
                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition hover:shadow-md dark:border-emerald-900/50 dark:bg-emerald-950/20"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p
                            class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400"
                        >
                            Processed Batches
                        </p>

                        <p class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-300">
                            {{ formatNumber(statistics.processed) }}
                        </p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white"
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
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 text-[10px] text-emerald-700/70 dark:text-emerald-400/70">
                    Successfully migrated
                </div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
            <div class="mb-5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Batch Status Breakdown
                </h3>

                <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                    Current distribution of migration batches by workflow status.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">

                <!-- Draft -->
                <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/50">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">
                            Draft
                        </span>

                        <span class="h-2 w-2 rounded-full bg-slate-500" />
                    </div>

                    <p class="mt-2 text-lg font-bold">
                        {{ formatNumber(statistics.draft) }}
                    </p>
                </div>

                <!-- Validation Failed -->
                <div class="rounded-xl bg-red-50 p-3 dark:bg-red-950/20">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-medium text-red-600 dark:text-red-400">
                            Failed
                        </span>

                        <span class="h-2 w-2 rounded-full bg-red-500" />
                    </div>

                    <p class="mt-2 text-lg font-bold text-red-700 dark:text-red-300">
                        {{ formatNumber(statistics.validation_failed) }}
                    </p>
                </div>

                <!-- Submitted -->
                <div class="rounded-xl bg-blue-50 p-3 dark:bg-blue-950/20">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-medium text-blue-600 dark:text-blue-400">
                            Submitted
                        </span>

                        <span class="h-2 w-2 rounded-full bg-blue-500" />
                    </div>

                    <p class="mt-2 text-lg font-bold text-blue-700 dark:text-blue-300">
                        {{ formatNumber(statistics.submitted) }}
                    </p>
                </div>

                <!-- Verified -->
                <div class="rounded-xl bg-indigo-50 p-3 dark:bg-indigo-950/20">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-medium text-indigo-600 dark:text-indigo-400">
                            Verified
                        </span>

                        <span class="h-2 w-2 rounded-full bg-indigo-500" />
                    </div>

                    <p class="mt-2 text-lg font-bold text-indigo-700 dark:text-indigo-300">
                        {{ formatNumber(statistics.accounts_verified) }}
                    </p>
                </div>

                <!-- Approved -->
                <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/20">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-medium text-emerald-600 dark:text-emerald-400">
                            Approved
                        </span>

                        <span class="h-2 w-2 rounded-full bg-emerald-500" />
                    </div>

                    <p class="mt-2 text-lg font-bold text-emerald-700 dark:text-emerald-300">
                        {{ formatNumber(statistics.approved) }}
                    </p>
                </div>

                <!-- Processed -->
                <div class="rounded-xl bg-purple-50 p-3 dark:bg-purple-950/20">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-medium text-purple-600 dark:text-purple-400">
                            Processed
                        </span>

                        <span class="h-2 w-2 rounded-full bg-purple-500" />
                    </div>

                    <p class="mt-2 text-lg font-bold text-purple-700 dark:text-purple-300">
                        {{ formatNumber(statistics.processed) }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>