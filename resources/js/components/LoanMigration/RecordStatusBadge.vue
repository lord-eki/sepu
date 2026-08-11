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
        pending: 'Pending',
        valid: 'Valid',
        invalid: 'Invalid',
        verified: 'Verified',
        processed: 'Processed',
        failed: 'Failed',
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
        pending:
            'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',

        valid:
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',

        invalid:
            'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300',

        verified:
            'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',

        processed:
            'bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300',

        failed:
            'bg-orange-100 text-orange-700 dark:bg-orange-950/40 dark:text-orange-300',
    }

    return (
        classes[normalizedStatus.value] ??
        'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
    )
})

const dotClasses = computed(() => {
    const dots: Record<string, string> = {
        pending: 'bg-slate-500',
        valid: 'bg-emerald-500',
        invalid: 'bg-red-500',
        verified: 'bg-blue-500',
        processed: 'bg-purple-500',
        failed: 'bg-orange-500',
    }

    return dots[normalizedStatus.value] ?? 'bg-slate-500'
})

const icon = computed(() => {
    const icons: Record<string, string> = {
        valid: 'check',
        invalid: 'warning',
        verified: 'shield',
        processed: 'check',
        failed: 'warning',
    }

    return icons[normalizedStatus.value] ?? 'dot'
})
</script>

<template>
    <span
        :class="badgeClasses"
        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-semibold leading-none"
    >
        <!-- Check -->
        <svg
            v-if="icon === 'check'"
            class="h-3 w-3"
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

        <!-- Warning -->
        <svg
            v-else-if="icon === 'warning'"
            class="h-3 w-3"
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

        <!-- Shield -->
        <svg
            v-else-if="icon === 'shield'"
            class="h-3 w-3"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-9.618 3.04A12.02 12.02 0 003 12c0 5.591 3.824 10.29 9 11.622C17.176 22.29 21 17.591 21 12c0-1.268-.196-2.49-.562-3.635"
            />
        </svg>

        <!-- Default dot -->
        <span
            v-else
            :class="dotClasses"
            class="h-1.5 w-1.5 rounded-full"
        />

        {{ label }}
    </span>
</template>
