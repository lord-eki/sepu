<script setup lang="ts">
import { computed } from 'vue'
import RecordStatusBadge from './RecordStatusBadge.vue'

interface ImportRecord {
    id?: number | null
    row_number?: number | null
    member_number?: string | null
    member_name?: string | null
    legacy_loan_number?: string | null
    loan_product?: string | null
    loan_date?: string | null
    original_amount?: number | string | null
    amount_paid?: number | string | null
    outstanding_balance?: number | string | null
    status?: string | null
    validation_errors?: string[] | null
}

interface Props {
    records?: ImportRecord[]
    loading?: boolean
    selectable?: boolean
    selected?: number[]
    maxHeight?: string
}

const props = withDefaults(defineProps<Props>(), {
    records: () => [],
    loading: false,
    selectable: false,
    selected: () => [],
    maxHeight: '600px',
})

const emit = defineEmits<{
    'update:selected': [ids: number[]]
    select: [record: ImportRecord]
    view: [record: ImportRecord]
}>()

const selectedIds = computed(() => new Set(props.selected))

const validCount = computed(
    () =>
        props.records.filter(
            (record) =>
                record.status?.toLowerCase() === 'valid' ||
                record.status?.toLowerCase() === 'verified',
        ).length,
)

const invalidCount = computed(
    () =>
        props.records.filter(
            (record) =>
                record.status?.toLowerCase() === 'invalid' ||
                record.status?.toLowerCase() === 'failed',
        ).length,
)

const pendingCount = computed(
    () =>
        props.records.filter(
            (record) =>
                !['valid', 'verified', 'invalid', 'failed'].includes(
                    record.status?.toLowerCase() ?? '',
                ),
        ).length,
)

const allSelectableIds = computed(() =>
    props.records
        .filter((record) => record.id !== null && record.id !== undefined)
        .map((record) => record.id as number),
)

const allSelected = computed(() => {
    return (
        allSelectableIds.value.length > 0 &&
        allSelectableIds.value.every((id) =>
            selectedIds.value.has(id),
        )
    )
})

const someSelected = computed(() => {
    return (
        !allSelected.value &&
        allSelectableIds.value.some((id) =>
            selectedIds.value.has(id),
        )
    )
})

const formatCurrency = (value: number | string | null | undefined) => {
    const amount = Number(value) || 0

    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount)
}

const formatDate = (value: string | null | undefined) => {
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

const toggleRecord = (record: ImportRecord) => {
    if (
        !props.selectable ||
        record.id === null ||
        record.id === undefined
    ) {
        return
    }

    const id = record.id
    const next = new Set(props.selected)

    if (next.has(id)) {
        next.delete(id)
    } else {
        next.add(id)
    }

    emit('update:selected', Array.from(next))
    emit('select', record)
}

const toggleAll = () => {
    if (!props.selectable) {
        return
    }

    if (allSelected.value) {
        emit('update:selected', [])
        return
    }

    emit('update:selected', [...allSelectableIds.value])
}

const isSelected = (record: ImportRecord) => {
    if (record.id === null || record.id === undefined) {
        return false
    }

    return selectedIds.value.has(record.id)
}
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
    >
        <!-- Header -->
        <div
            class="border-b border-slate-200 bg-slate-50/80 px-4 py-4 dark:border-slate-800 dark:bg-slate-800/40 sm:px-5"
        >
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400"
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
                                    d="M9 17v-2m3 2v-6m3 6v-4m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white">
                                Import Preview
                            </h2>

                            <p class="text-[9px] text-slate-500 dark:text-slate-400">
                                Review migrated loan records before processing.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[9px] font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                        {{ validCount }} Valid
                    </span>

                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-1 text-[9px] font-semibold text-red-700 dark:bg-red-950/40 dark:text-red-300"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-red-500" />
                        {{ invalidCount }} Invalid
                    </span>

                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400" />
                        {{ pendingCount }} Pending
                    </span>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div
            v-if="loading"
            class="flex min-h-[250px] items-center justify-center"
        >
            <div class="flex flex-col items-center gap-3">
                <svg
                    class="h-7 w-7 animate-spin text-indigo-600"
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

                <p class="text-[10px] text-slate-500 dark:text-slate-400">
                    Loading import preview...
                </p>
            </div>
        </div>

        <!-- Empty -->
        <div
            v-else-if="records.length === 0"
            class="flex min-h-[250px] flex-col items-center justify-center px-5 text-center"
        >
            <div
                class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800"
            >
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.7"
                        d="M9 13h6m-6 4h6m2 4H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
            </div>

            <h3 class="mt-4 text-xs font-semibold text-slate-700 dark:text-slate-200">
                No records to preview
            </h3>

            <p class="mt-1 max-w-sm text-[10px] leading-5 text-slate-400">
                Imported or captured loan records will appear here for review.
            </p>
        </div>

        <!-- Table -->
        <div
            v-else
            class="overflow-auto"
            :style="{ maxHeight }"
        >
            <table class="min-w-[1250px] w-full border-collapse text-left">
                <thead
                    class="sticky top-0 z-10 border-b border-slate-200 bg-slate-50 text-[9px] font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400"
                >
                    <tr>
                        <!-- Checkbox -->
                        <th
                            v-if="selectable"
                            class="w-10 px-3 py-3"
                        >
                            <input
                                type="checkbox"
                                :checked="allSelected"
                                :indeterminate="someSelected"
                                class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                                @change="toggleAll"
                            />
                        </th>

                        <th class="px-3 py-3">
                            #
                        </th>

                        <th class="px-3 py-3">
                            Member
                        </th>

                        <th class="px-3 py-3">
                            Legacy Loan
                        </th>

                        <th class="px-3 py-3">
                            Product
                        </th>

                        <th class="px-3 py-3">
                            Loan Date
                        </th>

                        <th class="px-3 py-3 text-right">
                            Original Amount
                        </th>

                        <th class="px-3 py-3 text-right">
                            Paid
                        </th>

                        <th class="px-3 py-3 text-right">
                            Outstanding
                        </th>

                        <th class="px-3 py-3">
                            Status
                        </th>

                        <th class="px-3 py-3 text-right">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody
                    class="divide-y divide-slate-100 dark:divide-slate-800"
                >
                    <tr
                        v-for="(record, index) in records"
                        :key="record.id ?? index"
                        class="group transition hover:bg-slate-50 dark:hover:bg-slate-800/40"
                        :class="{
                            'bg-indigo-50/50 dark:bg-indigo-950/10':
                                isSelected(record),
                        }"
                    >
                        <!-- Checkbox -->
                        <td
                            v-if="selectable"
                            class="px-3 py-3"
                        >
                            <input
                                type="checkbox"
                                :checked="isSelected(record)"
                                :disabled="
                                    record.id === null ||
                                    record.id === undefined
                                "
                                class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800"
                                @change="toggleRecord(record)"
                            />
                        </td>

                        <!-- Row -->
                        <td class="px-3 py-3">
                            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                                {{ record.row_number ?? index + 1 }}
                            </span>
                        </td>

                        <!-- Member -->
                        <td class="px-3 py-3">
                            <div>
                                <p class="text-[10px] font-semibold text-slate-700 dark:text-slate-200">
                                    {{ record.member_name || 'Unknown member' }}
                                </p>

                                <p
                                    v-if="record.member_number"
                                    class="mt-0.5 text-[9px] text-slate-400"
                                >
                                    {{ record.member_number }}
                                </p>
                            </div>
                        </td>

                        <!-- Legacy Loan -->
                        <td class="px-3 py-3">
                            <span class="font-mono text-[10px] font-medium text-slate-700 dark:text-slate-300">
                                {{ record.legacy_loan_number || '—' }}
                            </span>
                        </td>

                        <!-- Product -->
                        <td class="px-3 py-3">
                            <span class="text-[10px] text-slate-600 dark:text-slate-400">
                                {{ record.loan_product || '—' }}
                            </span>
                        </td>

                        <!-- Date -->
                        <td class="px-3 py-3">
                            <span class="whitespace-nowrap text-[10px] text-slate-600 dark:text-slate-400">
                                {{ formatDate(record.loan_date) }}
                            </span>
                        </td>

                        <!-- Original -->
                        <td class="px-3 py-3 text-right">
                            <span class="whitespace-nowrap text-[10px] font-medium text-slate-700 dark:text-slate-300">
                                {{ formatCurrency(record.original_amount) }}
                            </span>
                        </td>

                        <!-- Paid -->
                        <td class="px-3 py-3 text-right">
                            <span class="whitespace-nowrap text-[10px] text-slate-600 dark:text-slate-400">
                                {{ formatCurrency(record.amount_paid) }}
                            </span>
                        </td>

                        <!-- Outstanding -->
                        <td class="px-3 py-3 text-right">
                            <span class="whitespace-nowrap text-[10px] font-semibold text-slate-800 dark:text-slate-200">
                                {{ formatCurrency(record.outstanding_balance) }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="px-3 py-3">
                            <RecordStatusBadge
                                :status="record.status || 'pending'"
                            />

                            <div
                                v-if="
                                    record.validation_errors &&
                                    record.validation_errors.length
                                "
                                class="mt-1"
                            >
                                <span class="text-[8px] font-medium text-red-500">
                                    {{ record.validation_errors.length }}
                                    validation
                                    {{
                                        record.validation_errors.length === 1
                                            ? 'issue'
                                            : 'issues'
                                    }}
                                </span>
                            </div>
                        </td>

                        <!-- Action -->
                        <td class="px-3 py-3 text-right">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-lg px-2 py-1.5 text-[9px] font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/30"
                                @click="emit('view', record)"
                            >
                                <svg
                                    class="h-3 w-3"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                    />
                                </svg>

                                View
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div
            v-if="records.length > 0"
            class="flex flex-col gap-2 border-t border-slate-200 bg-slate-50/50 px-4 py-3 dark:border-slate-800 dark:bg-slate-800/20 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-[9px] text-slate-400">
                Showing
                <span class="font-semibold text-slate-600 dark:text-slate-300">
                    {{ records.length }}
                </span>
                {{ records.length === 1 ? 'record' : 'records' }}
            </p>

            <p
                v-if="selectable"
                class="text-[9px] text-slate-400"
            >
                <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                    {{ selected.length }}
                </span>
                selected
            </p>
        </div>
    </div>
</template>