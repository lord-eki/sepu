<script setup lang="ts">
import { reactive, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Props {
    nextBatchNumber: string
}

const props = defineProps<Props>()

const form = reactive({
    description: '',
    remarks: '',
})

const errors = ref<Record<string, string>>({})
const processing = ref(false)

const submit = () => {
    processing.value = true
    errors.value = {}

    router.post(
        route('loan-migration.store'),
        {
            description: form.description || null,
            remarks: form.remarks || null,
        },
        {
            preserveScroll: true,

            onError: (validationErrors) => {
                errors.value = validationErrors as Record<string, string>
            },

            onFinish: () => {
                processing.value = false
            },
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
                title: 'Create Migration Batch'
            }
        ]"
    >
        <Head title="Create Migration Batch" />

    <div class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">

            <!-- Header -->
            <div class="mb-6">
                <div class="mb-3 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
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

                    <span class="text-slate-700 dark:text-slate-300">
                        New Batch
                    </span>
                </div>

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
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                            Create Migration Batch
                        </h1>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                            Create a batch before capturing existing loan records.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                <!-- Card Header -->
                <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-5 dark:border-slate-800 dark:bg-slate-800/40 sm:px-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-sm font-bold sm:text-base">
                                Batch Information
                            </h2>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Provide basic information about this migration exercise.
                            </p>
                        </div>

                        <!-- Batch Number -->
                        <div class="hidden rounded-xl border border-blue-100 bg-blue-50 px-4 py-2 text-right dark:border-blue-900/50 dark:bg-blue-950/30 sm:block">
                            <p class="text-[10px] font-medium uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                Batch Number
                            </p>

                            <p class="mt-0.5 text-sm font-bold text-blue-700 dark:text-blue-300">
                                {{ props.nextBatchNumber }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form
                    class="space-y-6 p-5 sm:p-6"
                    @submit.prevent="submit"
                >
                    <!-- Mobile Batch Number -->
                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 dark:border-blue-900/50 dark:bg-blue-950/30 sm:hidden">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-medium uppercase tracking-wider text-blue-600 dark:text-blue-400">
                                    Batch Number
                                </p>

                                <p class="mt-1 text-sm font-bold text-blue-700 dark:text-blue-300">
                                    {{ props.nextBatchNumber }}
                                </p>
                            </div>

                            <svg
                                class="h-5 w-5 text-blue-500"
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

                    <!-- Description -->
                    <div>
                        <label
                            for="description"
                            class="mb-2 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Batch Description
                            <span class="font-normal text-slate-400">
                                (Optional)
                            </span>
                        </label>

                        <input
                            id="description"
                            v-model="form.description"
                            type="text"
                            maxlength="255"
                            placeholder="e.g. Legacy loan records migration - Machakos Branch"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                    errors.description,
                            }"
                        />

                        <div class="mt-1.5 flex items-center justify-between">
                            <p
                                v-if="errors.description"
                                class="text-xs text-red-600 dark:text-red-400"
                            >
                                {{ errors.description }}
                            </p>

                            <span
                                v-else
                                class="text-[10px] text-slate-400"
                            >
                                Maximum 255 characters
                            </span>

                            <span class="text-[10px] text-slate-400">
                                {{ form.description.length }}/255
                            </span>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div>
                        <label
                            for="remarks"
                            class="mb-2 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Remarks
                            <span class="font-normal text-slate-400">
                                (Optional)
                            </span>
                        </label>

                        <textarea
                            id="remarks"
                            v-model="form.remarks"
                            rows="6"
                            maxlength="5000"
                            placeholder="Enter any additional information about this migration batch, source documents, branch, migration exercise, or special instructions..."
                            class="w-full resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                            :class="{
                                'border-red-500 focus:border-red-500 focus:ring-red-500/20':
                                    errors.remarks,
                            }"
                        />

                        <div class="mt-1.5 flex items-center justify-between">
                            <p
                                v-if="errors.remarks"
                                class="text-xs text-red-600 dark:text-red-400"
                            >
                                {{ errors.remarks }}
                            </p>

                            <span
                                v-else
                                class="text-[10px] text-slate-400"
                            >
                                You can include source information or migration notes.
                            </span>

                            <span class="text-[10px] text-slate-400">
                                {{ form.remarks.length }}/5000
                            </span>
                        </div>
                    </div>

                    <!-- Information Notice -->
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20">
                        <div class="flex gap-3">
                            <div class="mt-0.5 shrink-0 text-amber-600 dark:text-amber-400">
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
                                        d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-xs font-bold text-amber-800 dark:text-amber-300">
                                    Before you continue
                                </h3>

                                <p class="mt-1 text-xs leading-5 text-amber-700 dark:text-amber-400">
                                    Creating this batch does not migrate any loan records yet.
                                    The batch will initially be saved as
                                    <strong>DRAFT</strong>, after which you can capture or import
                                    the legacy loan records.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 dark:border-slate-800 sm:flex-row sm:justify-end">
                        <Link
                            :href="route('loan-migration.index')"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Cancel
                        </Link>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
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
                                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
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
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>

                            {{ processing ? 'Creating Batch...' : 'Create Migration Batch' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Process Information -->
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400">
                            <span class="text-sm font-bold">1</span>
                        </div>

                        <div>
                            <p class="text-xs font-bold">
                                Create Batch
                            </p>

                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Set up the migration batch.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            <span class="text-sm font-bold">2</span>
                        </div>

                        <div>
                            <p class="text-xs font-bold">
                                Capture Records
                            </p>

                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Enter or import loan records.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            <span class="text-sm font-bold">3</span>
                        </div>

                        <div>
                            <p class="text-xs font-bold">
                                Validate & Process
                            </p>

                            <p class="text-[10px] text-slate-500 dark:text-slate-400">
                                Verify and finalize migration.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
 </AppLayout>
</template>
