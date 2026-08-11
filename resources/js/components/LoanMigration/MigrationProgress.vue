<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    totalRecords: number
    validRecords: number
    invalidRecords: number
    processedRecords: number
    status?: string
}

const props = withDefaults(defineProps<Props>(), {
    status: 'draft',
})

const total = computed(() => Number(props.totalRecords) || 0)
const valid = computed(() => Number(props.validRecords) || 0)
const invalid = computed(() => Number(props.invalidRecords) || 0)
const processed = computed(() => Number(props.processedRecords) || 0)

const validationPercentage = computed(() => {
    if (total.value === 0) {
        return 0
    }

    return Math.min(
        100,
        Math.round(
            ((valid.value + invalid.value) / total.value) * 100,
        ),
    )
})

const processedPercentage = computed(() => {
    if (total.value === 0) {
        return 0
    }

    return Math.min(
        100,
        Math.round((processed.value / total.value) * 100),
    )
})

const validPercentage = computed(() => {
    if (total.value === 0) {
        return 0
    }

    return Math.min(
        100,
        Math.round((valid.value / total.value) * 100),
    )
})

const invalidPercentage = computed(() => {
    if (total.value === 0) {
        return 0
    }

    return Math.min(
        100,
        Math.round((invalid.value / total.value) * 100),
    )
})

const statusLabel = computed(() => {
    const labels: Record<string, string> = {
        draft: 'Draft',
        validation_failed: 'Validation Failed',
        submitted: 'Submitted',
        accounts_verified: 'Accounts Verified',
        approved: 'Approved',
        processed: 'Processed',
    }

    return (
        labels[props.status?.toLowerCase()] ??
        props.status?.replaceAll('_', ' ') ??
        'Draft'
    )
})

const overallPercentage = computed(() => {
    if (props.status === 'processed') {
        return processedPercentage.value
    }

    return validationPercentage.value
})

const progressLabel = computed(() => {
    if (props.status === 'processed') {
        return 'Migration completed'
    }

    if (props.status === 'validation_failed') {
        return 'Validation requires attention'
    }

    if (props.status === 'submitted') {
        return 'Awaiting verification'
    }

    if (props.status === 'accounts_verified') {
        return 'Awaiting approval'
    }

    if (props.status === 'approved') {
        return 'Ready for processing'
    }

    return 'Validation progress'
})

const formatNumber = (value: number) => {
    return new Intl.NumberFormat('en-KE').format(value)
})
</script>

<template>
    <section
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"
    >
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                        Migration Progress
                    </h2>

                    <span
                        class="rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                    >
                        {{ statusLabel }}
                    </span>
                </div>

                <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                    {{ progressLabel }}
                </p>
            </div>

            <div class="text-left sm:text-right">
                <p class="text-2xl font-bold text-slate-900 dark:text-white">
                    {{ overallPercentage }}%
                </p>

                <p class="text-[9px] text-slate-400">
                    Overall progress
                </p>
            </div>
        </div>

        <!-- Overall Progress -->
        <div class="mt-5">
            <div class="mb-2 flex items-center justify-between">
                <span class="text-[10px] font-semibold text-slate-600 dark:text-slate-300">
                    Overall
                </span>

                <span class="text-[10px] text-slate-400">
                    {{ formatNumber(processed) }} /
                    {{ formatNumber(total) }} processed
                </span>
            </div>

            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div
                    class="h-full rounded-full bg-indigo-600 transition-all duration-500"
                    :style="{ width: `${overallPercentage}%` }"
                />
            </div>
        </div>

        <!-- Statistics -->
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

            <!-- Total -->
            <div
                class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
            >
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Total
                    </p>

                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
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
                                d="M4 6h16M4 10h16M4 14h10M4 18h10"
                            />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xl font-bold">
                    {{ formatNumber(total) }}
                </p>

                <p class="mt-1 text-[9px] text-slate-400">
                    Migration records
                </p>
            </div>

            <!-- Valid -->
            <div
                class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/20"
            >
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        Valid
                    </p>

                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-600 text-white">
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
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xl font-bold text-emerald-700 dark:text-emerald-300">
                    {{ formatNumber(valid) }}
                </p>

                <div class="mt-2">
                    <div class="h-1 overflow-hidden rounded-full bg-emerald-100 dark:bg-emerald-900/40">
                        <div
                            class="h-full rounded-full bg-emerald-600 transition-all duration-500"
                            :style="{ width: `${validPercentage}%` }"
                        />
                    </div>
                </div>
            </div>

            <!-- Invalid -->
            <div
                class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/20"
            >
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">
                        Invalid
                    </p>

                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-600 text-white">
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
                                d="M12 9v2m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3h15.64a2 2 0 001.71-3l-7.82-13a2 2 0 00-3.42 0z"
                            />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xl font-bold text-red-700 dark:text-red-300">
                    {{ formatNumber(invalid) }}
                </p>

                <div class="mt-2">
                    <div class="h-1 overflow-hidden rounded-full bg-red-100 dark:bg-red-900/40">
                        <div
                            class="h-full rounded-full bg-red-600 transition-all duration-500"
                            :style="{ width: `${invalidPercentage}%` }"
                        />
                    </div>
                </div>
            </div>

            <!-- Processed -->
            <div
                class="rounded-xl border border-purple-200 bg-purple-50 p-4 dark:border-purple-900/50 dark:bg-purple-950/20"
            >
                <div class="flex items-center justify-between">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400">
                        Processed
                    </p>

                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-600 text-white">
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
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>
                </div>

                <p class="mt-3 text-xl font-bold text-purple-700 dark:text-purple-300">
                    {{ formatNumber(processed) }}
                </p>

                <div class="mt-2">
                    <div class="h-1 overflow-hidden rounded-full bg-purple-100 dark:bg-purple-900/40">
                        <div
                            class="h-full rounded-full bg-purple-600 transition-all duration-500"
                            :style="{ width: `${processedPercentage}%` }"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Validation Status -->
        <div
            v-if="invalid > 0"
            class="mt-5 flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20"
        >
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
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
                        d="M12 9v2m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3h15.64a2 2 0 001.71-3l-7.82-13a2 2 0 00-3.42 0z"
                    />
                </svg>
            </div>

            <div>
                <p class="text-xs font-bold text-amber-800 dark:text-amber-300">
                    Validation attention required
                </p>

                <p class="mt-1 text-[10px] leading-5 text-amber-700 dark:text-amber-400">
                    {{ formatNumber(invalid) }}
                    record(s) contain validation errors and must be corrected before
                    the batch can be submitted.
                </p>
            </div>
        </div>

        <!-- Completed -->
        <div
            v-else-if="status === 'processed'"
            class="mt-5 flex gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/20"
        >
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white">
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
            </div>

            <div>
                <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300">
                    Migration completed successfully
                </p>

                <p class="mt-1 text-[10px] leading-5 text-emerald-700 dark:text-emerald-400">
                    All available records in this batch have been processed.
                </p>
            </div>
        </div>
    </section>
</template>
