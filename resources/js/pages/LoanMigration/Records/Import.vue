<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Batch {
    id: number
    batch_number: string
    description?: string | null
    status: string
}

interface Props {
    batch: Batch
}

const props = defineProps<Props>()

const file = ref<File | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const isDragging = ref(false)
const processing = ref(false)
const showInstructions = ref(false)

const errors = ref<Record<string, string[]>>({})

const acceptedFormats = ['.csv', '.xlsx', '.xls']

const fileName = computed(() => {
    return file.value?.name ?? ''
})

const fileSize = computed(() => {
    if (!file.value) {
        return ''
    }

    const size = file.value.size

    if (size < 1024) {
        return `${size} B`
    }

    if (size < 1024 * 1024) {
        return `${(size / 1024).toFixed(1)} KB`
    }

    return `${(size / (1024 * 1024)).toFixed(1)} MB`
})

const hasErrors = computed(() => {
    return Object.keys(errors.value).length > 0
})

const openFilePicker = () => {
    fileInput.value?.click()
}

const validateFile = (selectedFile: File | null) => {
    errors.value = {}

    if (!selectedFile) {
        return false
    }

    const extension = `.${selectedFile.name.split('.').pop()?.toLowerCase()}`

    if (!acceptedFormats.includes(extension)) {
        errors.value.file = [
            'Please select a CSV, XLS, or XLSX file.',
        ]

        return false
    }

    const maxSize = 10 * 1024 * 1024

    if (selectedFile.size > maxSize) {
        errors.value.file = [
            'The selected file must not exceed 10 MB.',
        ]

        return false
    }

    return true
}

const selectFile = (selectedFile: File | null) => {
    if (!selectedFile) {
        return
    }

    if (validateFile(selectedFile)) {
        file.value = selectedFile
    } else {
        file.value = null
    }
}

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement

    selectFile(target.files?.[0] ?? null)
}

const handleDrop = (event: DragEvent) => {
    isDragging.value = false

    const droppedFile = event.dataTransfer?.files?.[0] ?? null

    selectFile(droppedFile)
}

const removeFile = () => {
    file.value = null
    errors.value = {}

    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

const submit = () => {
    errors.value = {}

    if (!file.value) {
        errors.value.file = [
            'Please select a file to import.',
        ]

        return
    }

    if (!validateFile(file.value)) {
        return
    }

    processing.value = true

    const formData = new FormData()

    formData.append('file', file.value)

    router.post(
        route('loan-migration.records.import', props.batch.id),
        formData,
        {
            forceFormData: true,

            preserveScroll: true,

            onError: (validationErrors) => {
                errors.value = validationErrors as Record<string, string[]>
            },

            onFinish: () => {
                processing.value = false
            },
        },
    )
}

const downloadTemplate = () => {
    window.location.href = route(
        'loan-migration.records.template',
        props.batch.id,
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
        },
        {
            title: 'Import'
        }
    ]"
>
    <Head title="Import Migration Records" />

    <div class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-6">

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
                    Import Records
                </span>
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
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
                                    d="M12 16V4m0 0l-4 4m4-4l4 4M5 20h14"
                                />
                            </svg>
                        </div>

                        <div>
                            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                                Import Loan Records
                            </h1>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                                Upload existing loan records into
                                <span class="font-semibold">
                                    {{ batch.batch_number }}
                                </span>
                            </p>
                        </div>
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

            <!-- Main -->
            <div class="grid gap-6 lg:grid-cols-3">

                <!-- Upload -->
                <div class="lg:col-span-2">
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                            <h2 class="text-sm font-bold">
                                Upload File
                            </h2>

                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                Select the prepared migration spreadsheet.
                            </p>
                        </div>

                        <div class="p-5">

                            <!-- Error -->
                            <div
                                v-if="hasErrors"
                                class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/20"
                            >
                                <div class="flex gap-3">
                                    <svg
                                        class="h-5 w-5 shrink-0 text-red-600 dark:text-red-400"
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

                                    <div class="space-y-1">
                                        <p class="text-xs font-bold text-red-700 dark:text-red-300">
                                            Upload could not be completed
                                        </p>

                                        <ul class="space-y-1">
                                            <template
                                                v-for="(fieldErrors, field) in errors"
                                                :key="field"
                                            >
                                                <li
                                                    v-for="(error, index) in fieldErrors"
                                                    :key="`${field}-${index}`"
                                                    class="text-[11px] text-red-600 dark:text-red-400"
                                                >
                                                    {{ error }}
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Drop Zone -->
                            <div
                                v-if="!file"
                                :class="isDragging
                                    ? 'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-950/20'
                                    : 'border-slate-300 bg-slate-50 hover:border-blue-400 hover:bg-blue-50/50 dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-blue-500 dark:hover:bg-blue-950/10'"
                                class="group cursor-pointer rounded-2xl border-2 border-dashed p-8 text-center transition sm:p-12"
                                @click="openFilePicker"
                                @dragenter.prevent="isDragging = true"
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                            >
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept=".csv,.xls,.xlsx"
                                    class="hidden"
                                    @change="handleFileChange"
                                />

                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 transition group-hover:scale-105 dark:bg-blue-950/50 dark:text-blue-400">
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
                                            d="M12 16V4m0 0l-4 4m4-4l4 4M5 20h14"
                                        />
                                    </svg>
                                </div>

                                <p class="mt-5 text-sm font-bold">
                                    Drop your migration file here
                                </p>

                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                    or
                                    <span class="font-semibold text-blue-600 dark:text-blue-400">
                                        browse from your computer
                                    </span>
                                </p>

                                <p class="mt-4 text-[10px] text-slate-400">
                                    CSV, XLS, or XLSX • Maximum 10 MB
                                </p>
                            </div>

                            <!-- Selected File -->
                            <div
                                v-else
                                class="rounded-2xl border border-blue-200 bg-blue-50/60 p-5 dark:border-blue-900/50 dark:bg-blue-950/20"
                            >
                                <div class="flex items-start gap-4">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm dark:bg-slate-900 dark:text-blue-400">
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
                                                d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"
                                            />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold">
                                            {{ fileName }}
                                        </p>

                                        <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                            {{ fileSize }}
                                        </p>

                                        <div class="mt-3 flex items-center gap-2 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
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

                                            File ready for import
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="rounded-lg p-2 text-slate-400 transition hover:bg-white hover:text-red-600 dark:hover:bg-slate-900 dark:hover:text-red-400"
                                        @click="removeFile"
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
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                                    @click="downloadTemplate"
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
                                            d="M12 3v12m0 0l4-4m-4 4l-4-4M5 21h14"
                                        />
                                    </svg>

                                    Download Template
                                </button>

                                <button
                                    type="button"
                                    :disabled="!file || processing"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    @click="submit"
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
                                            d="M12 16V4m0 0l-4 4m4-4l4 4M5 20h14"
                                        />
                                    </svg>

                                    {{ processing ? 'Importing...' : 'Import Records' }}
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Instructions -->
                <aside class="space-y-6">

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-5 py-4 text-left"
                            @click="showInstructions = !showInstructions"
                        >
                            <div>
                                <h2 class="text-sm font-bold">
                                    Import Guidelines
                                </h2>

                                <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                                    Prepare your file correctly.
                                </p>
                            </div>

                            <svg
                                :class="showInstructions ? 'rotate-180' : ''"
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
                            v-if="showInstructions"
                            class="border-t border-slate-200 px-5 py-5 dark:border-slate-800"
                        >
                            <ol class="space-y-4">
                                <li class="flex gap-3">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                        1
                                    </span>

                                    <p class="text-[11px] leading-5 text-slate-600 dark:text-slate-400">
                                        Download the official migration template.
                                    </p>
                                </li>

                                <li class="flex gap-3">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                        2
                                    </span>

                                    <p class="text-[11px] leading-5 text-slate-600 dark:text-slate-400">
                                        Enter the existing loan information without
                                        changing the column headings.
                                    </p>
                                </li>

                                <li class="flex gap-3">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                        3
                                    </span>

                                    <p class="text-[11px] leading-5 text-slate-600 dark:text-slate-400">
                                        Verify member numbers, loan amounts,
                                        dates and outstanding balances.
                                    </p>
                                </li>

                                <li class="flex gap-3">
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                        4
                                    </span>

                                    <p class="text-[11px] leading-5 text-slate-600 dark:text-slate-400">
                                        Upload the completed file and review
                                        the validation results.
                                    </p>
                                </li>
                            </ol>
                        </div>
                    </section>

                    <!-- Supported Files -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="text-sm font-bold">
                            Supported Files
                        </h2>

                        <div class="mt-4 space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                                    <span class="text-[10px] font-bold">
                                        CSV
                                    </span>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold">
                                        CSV File
                                    </p>

                                    <p class="text-[10px] text-slate-400">
                                        Comma-separated values
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                                    <span class="text-[10px] font-bold">
                                        XLS
                                    </span>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold">
                                        Excel File
                                    </p>

                                    <p class="text-[10px] text-slate-400">
                                        XLS or XLSX
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Batch -->
                    <section class="rounded-2xl border border-slate-200 bg-slate-900 p-5 text-white shadow-sm dark:border-slate-800">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">
                            Current Batch
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
                            class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-blue-400 transition hover:text-blue-300"
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