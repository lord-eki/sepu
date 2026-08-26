<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
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

interface Props {
    batch: Batch
    acceptedFormats?: string[]
    maxFileSize?: number
}

const props = withDefaults(defineProps<Props>(), {
    acceptedFormats: () => ['xlsx', 'xls', 'csv'],
    maxFileSize: 10240,
})

const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const dragOver = ref(false)

const form = useForm<{
    file: File | null
    remarks: string
}>({
    file: null,
    remarks: '',
})

const statusLabel = computed(() => {
    return props.batch.status
        ? props.batch.status.replaceAll('_', ' ')
        : 'Unknown'
})

const statusClass = computed(() => {
    switch (props.batch.status?.toLowerCase()) {
        case 'draft':
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'

        case 'validation_failed':
            return 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300'

        case 'submitted':
            return 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300'

        case 'accounts_verified':
            return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300'

        case 'approved':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'

        case 'processed':
            return 'bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300'

        default:
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    }
})

const formattedMaxSize = computed(() => {
    if (props.maxFileSize >= 1024) {
        return `${(props.maxFileSize / 1024).toFixed(1)} MB`
    }

    return `${props.maxFileSize} KB`
})

const selectFile = () => {
    fileInput.value?.click()
}

const handleFileInput = (
    event: Event,
) => {
    const target = event.target as HTMLInputElement

    if (!target.files?.length) {
        return
    }

    setFile(target.files[0])
}

const handleDrop = (
    event: DragEvent,
) => {
    dragOver.value = false

    if (!event.dataTransfer?.files?.length) {
        return
    }

    setFile(event.dataTransfer.files[0])
}

const setFile = (
    file: File,
) => {
    selectedFile.value = file
    form.file = file
    form.clearErrors('file')
}

const removeFile = () => {
    selectedFile.value = null
    form.file = null

    if (fileInput.value) {
        fileInput.value.value = ''
    }

    form.clearErrors('file')
}

const formatFileSize = (
    bytes: number,
) => {
    if (bytes < 1024) {
        return `${bytes} B`
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`
    }

    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`
}

const fileExtension = (
    file: File,
) => {
    return file.name
        .split('.')
        .pop()
        ?.toUpperCase() ?? ''
}

const isSupportedFile = (
    file: File,
) => {
    const extension = file.name
        .split('.')
        .pop()
        ?.toLowerCase()

    return !!extension &&
        props.acceptedFormats.includes(extension)
}

const isWithinSizeLimit = (
    file: File,
) => {
    return file.size <= props.maxFileSize * 1024
}

const submit = () => {
    if (!form.file) {
        form.setError(
            'file',
            'Please select a migration file.',
        )

        return
    }

    if (!isSupportedFile(form.file)) {
        form.setError(
            'file',
            `Unsupported file type. Allowed formats: ${props.acceptedFormats.join(', ').toUpperCase()}.`,
        )

        return
    }

    if (!isWithinSizeLimit(form.file)) {
        form.setError(
            'file',
            `The selected file exceeds the maximum size of ${formattedMaxSize.value}.`,
        )

        return
    }

    form.post(
        route(
            'loan-migration.import.store',
            props.batch.id,
        ),
        {
            forceFormData: true,
            preserveScroll: true,
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
                title: 'Import Records',
                href: ''
            }
        ]"
    >
        <Head title="Import Loan Records" />

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
                        d="M9 5l7 7-7 7"
                    />
                </svg>

                <span class="font-medium text-slate-700 dark:text-slate-300">
                    Import
                </span>
            </div>

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
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
                                d="M7 16a4 4 0 01-.88-7.903A5.002 5.002 0 0116.9 6L17 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v9"
                            />
                        </svg>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                                Import Loan Data
                            </h1>

                            <span
                                :class="statusClass"
                                class="rounded-full px-2.5 py-1 text-[10px] font-bold capitalize"
                            >
                                {{ statusLabel }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 sm:text-sm">
                            Upload the legacy loan records for
                            <span class="font-semibold text-slate-700 dark:text-slate-300">
                                {{ batch.batch_number }}
                            </span>.
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
                            d="M10 19l-7-7m0 0l7-7m7 7H3"
                        />
                    </svg>

                    Back to Batch
                </Link>
            </div>

            <!-- Existing Batch Summary -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">
                        Total Records
                    </p>

                    <p class="mt-2 text-xl font-bold">
                        {{ batch.total_records ?? 0 }}
                    </p>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20">
                    <p class="text-[9px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        Valid
                    </p>

                    <p class="mt-2 text-xl font-bold text-emerald-700 dark:text-emerald-300">
                        {{ batch.valid_records ?? 0 }}
                    </p>
                </div>

                <div class="col-span-2 rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm sm:col-span-1 dark:border-red-900/50 dark:bg-red-950/20">
                    <p class="text-[9px] font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">
                        Invalid
                    </p>

                    <p class="mt-2 text-xl font-bold text-red-700 dark:text-red-300">
                        {{ batch.invalid_records ?? 0 }}
                    </p>
                </div>
            </div>

            <!-- Upload Card -->
            <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="text-sm font-bold">
                        Upload Migration File
                    </h2>

                    <p class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                        Select an Excel or CSV file containing the legacy loan records.
                    </p>
                </div>

                <div class="p-5">

                    <!-- Dropzone -->
                    <div
                        class="relative rounded-2xl border-2 border-dashed p-6 text-center transition sm:p-10"
                        :class="
                            dragOver
                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/20'
                                : 'border-slate-300 bg-slate-50 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-800/40'
                        "
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="handleDrop"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            class="hidden"
                            :accept="acceptedFormats.map(format => `.${format}`).join(',')"
                            @change="handleFileInput"
                        />

                        <!-- No File -->
                        <template v-if="!selectedFile">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                                <svg
                                    class="h-7 w-7"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M7 16a4 4 0 01-.88-7.903A5.002 5.002 0 0116.9 6L17 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v9"
                                    />
                                </svg>
                            </div>

                            <h3 class="mt-4 text-sm font-bold">
                                Drop your migration file here
                            </h3>

                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                or click the button below to browse your computer
                            </p>

                            <button
                                type="button"
                                class="mt-5 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700"
                                @click="selectFile"
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

                                Choose File
                            </button>

                            <p class="mt-4 text-[10px] text-slate-400">
                                Supported:
                                {{ acceptedFormats.map(format => format.toUpperCase()).join(', ') }}
                                · Maximum {{ formattedMaxSize }}
                            </p>
                        </template>

                        <!-- Selected File -->
                        <template v-else>
                            <div class="mx-auto flex max-w-lg items-center gap-4 rounded-2xl border border-blue-200 bg-white p-4 text-left shadow-sm dark:border-blue-900/50 dark:bg-slate-900">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                                    <svg
                                        class="h-6 w-6"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M14 2v6h6M8 13h8M8 17h6"
                                        />
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-bold">
                                        {{ selectedFile.name }}
                                    </p>

                                    <div class="mt-1 flex flex-wrap gap-2 text-[10px] text-slate-400">
                                        <span>
                                            {{ fileExtension(selectedFile) }}
                                        </span>

                                        <span>•</span>

                                        <span>
                                            {{ formatFileSize(selectedFile.size) }}
                                        </span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30 dark:hover:text-red-400"
                                    title="Remove file"
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

                            <button
                                type="button"
                                class="mt-5 text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                @click="selectFile"
                            >
                                Choose a different file
                            </button>
                        </template>
                    </div>

                    <!-- File Error -->
                    <p
                        v-if="form.errors.file"
                        class="mt-3 rounded-xl bg-red-50 px-4 py-3 text-[10px] font-medium text-red-600 dark:bg-red-950/20 dark:text-red-400"
                    >
                        {{ form.errors.file }}
                    </p>

                    <!-- Remarks -->
                    <div class="mt-6">
                        <label
                            for="remarks"
                            class="mb-2 block text-xs font-bold"
                        >
                            Import Remarks
                            <span class="font-normal text-slate-400">
                                (Optional)
                            </span>
                        </label>

                        <textarea
                            id="remarks"
                            v-model="form.remarks"
                            rows="4"
                            placeholder="Add any notes about this import..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-xs outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-800"
                        />

                        <p
                            v-if="form.errors.remarks"
                            class="mt-1 text-[10px] text-red-500"
                        >
                            {{ form.errors.remarks }}
                        </p>
                    </div>

                    <!-- Submit -->
                    <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                        <Link
                            :href="route('loan-migration.show', batch.id)"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            Cancel
                        </Link>

                        <button
                            type="button"
                            :disabled="form.processing || !selectedFile"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="submit"
                        >
                            <svg
                                v-if="form.processing"
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
                                    d="M7 16a4 4 0 01-.88-7.903A5.002 5.002 0 0116.9 6L17 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v9"
                                />
                            </svg>

                            {{ form.processing ? 'Uploading...' : 'Import Loan Data' }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- Import Instructions -->
            <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900/50 dark:bg-blue-950/20">
                <div class="flex items-start gap-3">
                    <svg
                        class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400"
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

                    <div class="min-w-0">
                        <h3 class="text-xs font-bold text-blue-700 dark:text-blue-300">
                            Before importing
                        </h3>

                        <ul class="mt-2 space-y-1.5 text-[10px] leading-5 text-blue-600 dark:text-blue-400">
                            <li>
                                • Ensure the file contains the required legacy loan columns.
                            </li>

                            <li>
                                • Member numbers should match existing SACCO members.
                            </li>

                            <li>
                                • Loan numbers should be unique within the migration batch.
                            </li>

                            <li>
                                • Verify original amounts, paid amounts and outstanding balances.
                            </li>

                            <li>
                                • The imported records will be validated before submission.
                            </li>
                        </ul>
                    </div>
                </div>
            </section>
        </div>
    </div>
</AppLayout>
</template>