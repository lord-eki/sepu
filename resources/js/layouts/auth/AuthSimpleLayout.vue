<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <!-- Container -->
    <div
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-50 via-white to-blue-50 px-6 py-12 dark:from-gray-950 dark:via-gray-900 dark:to-blue-950"
    >
        <!-- Decorative Blobs -->
        <div
            class="pointer-events-none absolute -top-32 -right-32 h-[400px] w-[400px] rounded-full bg-blue-200 opacity-40 blur-[120px] dark:bg-blue-800"
        ></div>
        <div
            class="pointer-events-none absolute -bottom-32 -left-32 h-[400px] w-[400px] rounded-full bg-orange-200 opacity-40 blur-[120px] dark:bg-orange-900"
        ></div>

        <!-- Glass Card -->
        <div
            class="relative w-full max-w-md space-y-8 rounded-3xl border border-gray-200 bg-white/80 p-8 shadow-2xl backdrop-blur-xl md:p-10 dark:border-gray-700 dark:bg-gray-800/60 dark:shadow-black/20"
        >
            <!-- Logo + Header -->
            <div class="flex flex-col items-center gap-4">
                <Link
                    :href="route('home')"
                    class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-200/10 p-1 text-white shadow-sm shadow-blue-100 transition-transform hover:scale-105 dark:bg-blue-100/20 dark:shadow-blue-900"
                >
                    <AppLogoIcon class="size-6 fill-current" />
                </Link>

                <div class="space-y-2 text-center">
                    <h1 v-if="title" class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ title }}
                    </h1>
                    <p v-if="description" class="text-sm leading-relaxed text-gray-500 dark:text-gray-300">
                        {{ description }}
                    </p>
                </div>
            </div>

            <!-- Slot Content (Login/Register Form) -->
            <div>
                <slot />
            </div>
        </div>
    </div>
</template>
