<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    status: string
}

const props = defineProps<Props>()

const normalizedStatus = computed(() =>
    props.status?.toLowerCase().trim() || 'unknown',
)

const label = computed(() => {
    const labels: Record<string, string> = {
        draft: 'Draft',
        validation_failed: 'Validation Failed',
        submitted: 'Submitted',
        accounts_verified: 'Accounts Verified',
        approved: 'Approved',
        processed: 'Processed',
    }

    return (
        labels[normalizedStatus.value] ??
        normalizedStatus.value
            .replaceAll('_', ' ')
            .replace(/\b\w/g, (char) => char.toUpperCase())
    )
})

const badgeClasses = computed(() => {
    const classes: Record<string, string> = {
        draft:
            'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',

        validation_failed:
            'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300',

        submitted:
            'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',

        accounts_verified:
            'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300',

        approved:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',

        processed:
            'bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300',
    }

    return (
        classes[normalizedStatus.value] ??
        'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    )
})

const dotClasses = computed(() => {
    const classes: Record<string, string> = {
        draft: 'bg-slate-500',
        validation_failed: 'bg-red-500',
        submitted: 'bg-blue-500',
        accounts_verified: 'bg-indigo-500',
        approved: 'bg-emerald-500',
        processed: 'bg-purple-500',
    }

    return classes[normalizedStatus.value] ?? 'bg-slate-500'
})
</script>

<template>
    <span
        :class="badgeClasses"
        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-semibold leading-none"
    >
        <span
            :class="dotClasses"
            class="h-1.5 w-1.5 rounded-full"
        />

        {{ label }}
    </span>
</template>

