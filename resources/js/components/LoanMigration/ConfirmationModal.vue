<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from 'vue'

interface Props {
    show?: boolean
    title?: string
    message?: string
    confirmText?: string
    cancelText?: string
    variant?: 'primary' | 'danger' | 'warning' | 'success'
    processing?: boolean
    closeOnBackdrop?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
    title: 'Confirm Action',
    message: 'Are you sure you want to continue?',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'primary',
    processing: false,
    closeOnBackdrop: true,
})

const emit = defineEmits<{
    confirm: []
    cancel: []
    close: []
}>()

const visible = computed(() => props.show)

const variantClasses = computed(() => {
    const classes = {
        primary: {
            icon: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400',
            button: 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500/30',
        },
        danger: {
            icon: 'bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400',
            button: 'bg-red-600 hover:bg-red-700 focus:ring-red-500/30',
        },
        warning: {
            icon: 'bg-amber-100 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400',
            button: 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500/30',
        },
        success: {
            icon: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
            button: 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500/30',
        },
    }

    return classes[props.variant]
})

const close = () => {
    if (props.processing) {
        return
    }

    emit('close')
    emit('cancel')
}

const confirm = () => {
    if (props.processing) {
        return
    }

    emit('confirm')
}

const handleBackdrop = () => {
    if (props.closeOnBackdrop) {
        close()
    }
}

const handleKeydown = (event: KeyboardEvent) => {
    if (!props.show) {
        return
    }

    if (event.key === 'Escape') {
        close()
    }
}

watch(
    () => props.show,
    (show) => {
        if (show) {
            document.addEventListener('keydown', handleKeydown)
            document.body.style.overflow = 'hidden'
        } else {
            document.removeEventListener('keydown', handleKeydown)
            document.body.style.overflow = ''
        }
    },
    { immediate: true },
)

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleKeydown)
    document.body.style.overflow = ''
})
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                :aria-labelledby="'confirmation-modal-title'"
                @mousedown.self="handleBackdrop"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                />

                <!-- Modal -->
                <Transition
                    appear
                    enter-active-class="duration-200 ease-out"
                    enter-from-class="scale-95 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="duration-150 ease-in"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div
                        v-if="visible"
                        class="relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
                    >
                        <!-- Close button -->
                        <button
                            v-if="!processing"
                            type="button"
                            class="absolute right-4 top-4 z-10 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            aria-label="Close"
                            @click="close"
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
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                        <!-- Content -->
                        <div class="p-5 sm:p-6">
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div
                                    :class="variantClasses.icon"
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                                >
                                    <!-- Danger -->
                                    <svg
                                        v-if="variant === 'danger'"
                                        class="h-5 w-5"
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

                                    <!-- Warning -->
                                    <svg
                                        v-else-if="variant === 'warning'"
                                        class="h-5 w-5"
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

                                    <!-- Success -->
                                    <svg
                                        v-else-if="variant === 'success'"
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

                                    <!-- Primary -->
                                    <svg
                                        v-else
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8.228 9.247a4.5 4.5 0 117.544 0M12 14.25v.008m0 3.742h.008M12 21a9 9 0 100-18 9 9 0 000 18z"
                                        />
                                    </svg>
                                </div>

                                <!-- Text -->
                                <div class="min-w-0 flex-1 pr-5">
                                    <h2
                                        id="confirmation-modal-title"
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ title }}
                                    </h2>

                                    <p
                                        class="mt-2 text-[11px] leading-5 text-slate-500 dark:text-slate-400"
                                    >
                                        {{ message }}
                                    </p>

                                    <!-- Optional custom content -->
                                    <div
                                        v-if="$slots.default"
                                        class="mt-4"
                                    >
                                        <slot />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-4 dark:border-slate-800 dark:bg-slate-800/30 sm:flex-row sm:justify-end"
                        >
                            <button
                                type="button"
                                :disabled="processing"
                                class="inline-flex min-h-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-[10px] font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400/20 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="close"
                            >
                                {{ cancelText }}
                            </button>

                            <button
                                type="button"
                                :disabled="processing"
                                :class="variantClasses.button"
                                class="inline-flex min-h-9 items-center justify-center gap-2 rounded-xl px-4 py-2 text-[10px] font-semibold text-white shadow-sm transition focus:outline-none focus:ring-4 disabled:cursor-not-allowed disabled:opacity-60"
                                @click="confirm"
                            >
                                <svg
                                    v-if="processing"
                                    class="h-3.5 w-3.5 animate-spin"
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

                                {{
                                    processing
                                        ? 'Processing...'
                                        : confirmText
                                }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
