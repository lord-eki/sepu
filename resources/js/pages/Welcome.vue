<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

// Animated Counters
const members = ref(0);
const loans = ref(0);
const years = ref(0);

const animateValue = (target: number, duration: number, callback: (value: number) => void) => {
    let start = 0;
    const increment = target / (duration / 16);

    const timer = setInterval(() => {
        start += increment;

        if (start >= target) {
            callback(target);
            clearInterval(timer);
        } else {
            callback(Math.floor(start));
        }
    }, 16);
};

onMounted(() => {
    animateValue(100, 1800, (val) => (members.value = val));
    animateValue(500, 2200, (val) => (loans.value = val));
    animateValue(30, 1600, (val) => (years.value = val));
});
</script>

<template>
    <PublicLayout>
        <Head title="Welcome" />

        <main class="relative min-h-screen overflow-hidden bg-slate-50 dark:bg-slate-950">
            <!-- Background Effects -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute top-0 left-1/2 h-[900px] w-[900px] -translate-x-1/2 rounded-full bg-blue-500/10 blur-[180px]"></div>

                <div class="absolute right-0 bottom-0 h-[700px] w-[700px] rounded-full bg-orange-500/10 blur-[180px]"></div>

                <div class="absolute top-1/3 left-0 h-[500px] w-[500px] rounded-full bg-indigo-500/10 blur-[180px]"></div>
            </div>

            <!-- Hero -->
            <section class="relative z-10 mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-16 lg:px-8 lg:py-24">
                <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
                    <!-- LEFT -->
                    <div class="order-2 lg:order-1">
                        <!-- Badge -->
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-900 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300"
                        >
                            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                            Trusted Digital SACCO Platform
                        </div>

                        <!-- Heading -->
                        <h1
                            class="mt-6 text-4xl leading-tight font-black tracking-tight text-slate-900 sm:text-5xl md:text-6xl xl:text-7xl dark:text-white"
                        >
                            Building Strong
                            <span class="block bg-gradient-to-r from-blue-600 via-indigo-500 to-orange-500 bg-clip-text text-transparent">
                                Financial Futures
                            </span>
                        </h1>

                        <!-- Description -->
                        <p class="mt-6 max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg lg:text-xl dark:text-slate-300">
                            Experience smarter savings, faster loan processing, secure transactions, and seamless financial management from anywhere
                            with SEPU SACCO.
                        </p>

                        <!-- CTA -->
                        <div class="mt-10 flex gap-4">
                            <Link
                                :href="route('register')"
                                class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-4 font-semibold text-white shadow-xl shadow-blue-500/20 transition-all duration-300 hover:-translate-y-1 hover:from-blue-700 hover:to-indigo-800"
                            >
                                Sign Up
                            </Link>

                            <Link
                                :href="route('login')"
                                class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white/80 px-8 py-4 font-semibold text-slate-700 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:bg-slate-700 hover:text-white dark:border-slate-700 dark:bg-slate-900/80 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                Login
                            </Link>
                        </div>

                        <!-- Stats -->
                        <div class="mt-14 grid grid-cols-3 gap-2 sm:gap-4">
                            <div
                                class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80"
                            >
                                <h3 class="text-xl font-black text-slate-900 sm:text-2xl dark:text-white">{{ members }}+</h3>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Active Members</p>
                            </div>

                            <div
                                class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80"
                            >
                                <h3 class="text-xl font-black text-slate-900 sm:text-2xl dark:text-white">KES {{ loans }}M+</h3>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Loans Issued</p>
                            </div>

                            <div
                                class="rounded-3xl border border-slate-200 bg-white/80 p-5 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80"
                            >
                                <h3 class="text-lg font-black text-slate-900 sm:text-2xl dark:text-white">{{ years }}+</h3>

                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Years Trusted</p>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="relative order-1 lg:order-2">
                        <!-- Main Card -->
                        <div
                            class="relative overflow-hidden rounded-[32px] border border-white/20 bg-white/70 shadow-[0_20px_80px_rgba(0,0,0,0.15)] backdrop-blur-2xl dark:bg-slate-900/70"
                        >
                            <img src="/sacco.png" alt="SEPU SACCO" class="h-[300px] w-full object-cover sm:h-[500px]" />

                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent"></div>

                            <!-- Bottom Label -->
                            <div class="absolute bottom-5 left-5">
                                <div class="rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-xl">
                                    #EmpoweringMembers
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </PublicLayout>
</template>
