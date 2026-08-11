<script setup lang="ts">
import { computed } from 'vue'

interface ValidationError {
    field?: string | null
    message: string
    row?: number | null
    value?: string | number | null
}

interface Props {
    errors?: ValidationError[] | Record<string, string | string[]>
    title?: string
    collapsible?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    errors: () => [],
    title: 'Validation Errors',
    collapsible: true,
})

const isOpen = defineModel<boolean>('open', {
    default: true,
})

const normalizedErrors = computed<ValidationError[]>(() => {
    if (Array.isArray(props.errors)) {
        return props.errors
    }

    return Object.entries(props.errors).flatMap(
        ([field, messages]) => {
            const values = Array.isArray(messages)
                ? messages
                : [messages]

            return values.map((message) => ({
                field,
                message,
            }))
        },
    )
})

const errorCount = computed(() => normalizedErrors.value.length)

const hasErrors = computed(() => errorCount.value > 0)

const formatField = (field?: string | null) => {
    if (!field) {
        return 'General'
    }

    return field
        .replace(/\[(\d+)\]/g, ' $1')
        .replaceAll('_', ' ')
        .replace(/\./g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase())
}

const toggle = () => {
    if (!props.collapsible) {
        return
    }

    isOpen.value = !isOpen.value
}
</script>

<template>
    <div
        v-if="hasErrors"
        class="overflow-hidden rounded-2xl border border-red-200 bg-white shadow-sm dark:border-red-900/50 dark:bg-slate-900"
    >
        <!-- Header -->
        <button
            v-if="collapsible"
            type="button"
            class="flex w-full items-center justify-between gap-4 bg-red-50 px-4 py-3.5 text-left transition hover:bg-red-100 dark:bg-red-950/20 dark:hover:bg-red-950/30"
            @click="toggle"
        >
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400"
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
                            d="M12 9v2m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3h15.64a2 2 0 001.71-3l-7.82-13a2 2 0 00-3.42 0z"
                        />
                    </svg>
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-xs font-bold text-red-800 dark:text-red-300">
                            {{ title }}
                        </h3>

                        <span
                            class="rounded-full bg-red-100 px-2 py-0.5 text-[9px] font-bold text-red-700 dark:bg-red-900/50 dark:text-red-300"
                        >
                            {{ errorCount }}
                        </span>
                    </div>

                    <p class="mt-0.5 text-[9px] text-red-600/80 dark:text-red-400/80">
                        Please correct the affected records before continuing.
                    </p>
                </div>
            </div>

            <svg
                class="h-4 w-4 shrink-0 text-red-500 transition-transform duration-200"
                :class="{ 'rotate-180': isOpen }"
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

        <!-- Non-collapsible Header -->
        <div
            v-else
            class="flex items-center gap-3 bg-red-50 px-4 py-3.5 dark:bg-red-950/20"
        >
            <div
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400"
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
                        d="M12 9v2m0 4h.01M10.29 3.86l-7.82 13a2 2 0 001.71 3h15.64a2 2 0 00-1.71-3l-7.82-13a2 2 0 00-3.42 0z"
                    />
                </svg>
            </div>

            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-xs font-bold text-red-800 dark:text-red-300">
                        {{ title }}
                    </h3>

                    <span
                        class="rounded-full bg-red-100 px-2 py-0.5 text-[9px] font-bold text-red-700 dark:bg-red-900/50 dark:text-red-300"
                    >
                        {{ errorCount }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Error List -->
        <div
            v-show="isOpen || !collapsible"
            class="divide-y divide-red-100 dark:divide-red-900/30"
        >
            <div
                v-for="(error, index) in normalizedErrors"
                :key="`${error.field ?? 'general'}-${error.row ?? index}-${index}`"
                class="flex gap-3 px-4 py-3 transition hover:bg-red-50/50 dark:hover:bg-red-950/10"
            >
                <!-- Number -->
                <div
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-[9px] font-bold text-red-600 dark:bg-red-900/40 dark:text-red-400"
                >
                    {{ index + 1 }}
                </div>

                <div class="min-w-0 flex-1">
                    <!-- Field / Row -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[9px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{ formatField(error.field) }}
                        </span>

                        <span
                            v-if="error.row"
                            class="rounded-md bg-amber-100 px-1.5 py-0.5 text-[9px] font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300"
                        >
                            Row {{ error.row }}
                        </span>
                    </div>

                    <!-- Message -->
                    <p class="mt-1.5 text-[10px] leading-5 text-red-700 dark:text-red-300">
                        {{ error.message }}
                    </p>

                    <!-- Value -->
                    <p
                        v-if="
                            error.value !== null &&
                            error.value !== undefined &&
                            error.value !== ''
                        "
                        class="mt-1 text-[9px] text-slate-400"
                    >
                        Value:
                        <span class="font-medium text-slate-500 dark:text-slate-400">
                            {{ error.value }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div
            v-show="isOpen || !collapsible"
            class="border-t border-red-100 bg-red-50/50 px-4 py-3 dark:border-red-900/30 dark:bg-red-950/10"
        >
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[9px] text-red-600/80 dark:text-red-400/80">
                    {{ errorCount }}
                    {{ errorCount === 1 ? 'error requires' : 'errors require' }}
                    attention.
                </p>

                <button
                    v-if="collapsible && isOpen"
                    type="button"
                    class="text-left text-[9px] font-semibold text-red-700 hover:underline dark:text-red-300 sm:text-right"
                    @click="isOpen = false"
                >
                    Collapse errors
                </button>
            </div>
        </div>
    </div>
</template>