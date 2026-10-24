<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const menuOpen = ref(false);
const page = usePage();

const isActive = (url: string) => page.url === url;
</script>

<template>
    <div class="flex min-h-screen flex-col bg-[#f8fafc] dark:bg-[#020617]">
        <!-- NAVBAR -->
        <header class="fixed top-0 right-0 left-0 z-50 border-b border-white/10 bg-white/70 backdrop-blur-2xl dark:bg-slate-950/70">
            <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-8">
                <!-- Logo -->
                <Link href="/" class="group flex items-center gap-4">
                    <img
                        src="/apple-touch-icon1.png"
                        alt="SEPU Logo"
                        class="h-12 w-12 rounded-2xl shadow-lg transition duration-300 group-hover:scale-105"
                    />

                    <div class="leading-tight">
                        <h1 class="text-lg font-black tracking-tight text-slate-900 md:text-xl dark:text-white">SEPU SACCO</h1>

                        <p class="hidden text-xs text-slate-500 sm:block dark:text-slate-400">Smart Cooperative Banking</p>
                    </div>
                </Link>

                <!-- Desktop Nav -->
                <nav class="hidden items-center gap-8 md:flex">
                    <Link
                        href="/"
                        :class="[
                            isActive('/') ? 'text-blue-800 dark:text-blue-400' : 'text-slate-600 dark:text-slate-300',
                            'font-medium transition hover:text-blue-900',
                        ]"
                    >
                        Home
                    </Link>

                    <Link
                        href="/about"
                        :class="[
                            isActive('/about') ? 'text-blue-800 dark:text-blue-400' : 'text-slate-600 dark:text-slate-300',
                            'font-medium transition hover:text-blue-900',
                        ]"
                    >
                        About
                    </Link>

                    <Link
                        href="/terms"
                        :class="[
                            isActive('/terms') ? 'text-blue-800 dark:text-blue-400' : 'text-slate-600 dark:text-slate-300',
                            'font-medium transition hover:text-blue-900',
                        ]"
                    >
                        Terms
                    </Link>

                    <Link
                        href="/contact"
                        :class="[
                            isActive('/contact') ? 'text-blue-800 dark:text-blue-400' : 'text-slate-600 dark:text-slate-300',
                            'font-medium transition hover:text-blue-900',
                        ]"
                    >
                        Contact
                    </Link>
                </nav>

                <!-- Actions -->
                <div class="hidden items-center gap-4 md:flex">
                    <!-- Login (FIXED VISIBILITY) -->
                    <Link
                        href="/login"
                        class="rounded-xl border border-slate-700 px-5 py-2.5 font-semibold text-blue-700 shadow-sm transition-all duration-300 hover:border-slate-800 hover:bg-slate-700 hover:text-white hover:shadow-md dark:text-blue-300"
                    >
                        Login
                    </Link>

                    <!-- Primary CTA -->
                    <Link
                        href="/register"
                        class="rounded-xl bg-gradient-to-r from-blue-700 to-blue-900 px-5 py-2.5 font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-300 hover:-translate-y-0.5 hover:from-blue-800 hover:to-blue-950"
                    >
                        Sign Up
                    </Link>
                </div>
                <!-- Mobile Toggle -->
                <button @click="menuOpen = !menuOpen" class="rounded-xl p-2 transition hover:bg-slate-100 md:hidden dark:hover:bg-slate-800">
                    <svg
                        v-if="!menuOpen"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7 text-slate-800 dark:text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>

                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7 text-slate-800 dark:text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <transition name="fade-slide">
                <div v-if="menuOpen" class="px-6 pb-6 md:hidden">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                        <nav class="flex flex-col gap-5">
                            <Link href="/">Home</Link>
                            <Link href="/about">About</Link>
                            <Link href="/terms">Terms</Link>
                            <Link href="/contact">Contact</Link>
                        </nav>

                        <div class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-6 dark:border-slate-800">
                            <!-- Login -->
                            <Link
                                href="/login"
                                class="w-full rounded-xl border border-blue-800 py-3 text-center font-semibold text-blue-700 transition hover:bg-slate-600 hover:text-white dark:text-blue-300"
                            >
                                Login
                            </Link>

                            <!-- Register -->
                            <Link
                                href="/register"
                                class="w-full rounded-xl bg-blue-800 py-3 text-center font-semibold text-white shadow-md hover:bg-blue-700"
                            >
                                Sign Up
                            </Link>
                        </div>
                    </div>
                </div>
            </transition>
        </header>

        <!-- CONTENT -->
        <main class="flex-1 pt-20">
            <slot />
        </main>

        <!-- FOOTER -->
        <footer class="border-t border-slate-200 bg-white/70 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/70">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-5 px-6 py-8 md:flex-row lg:px-8">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">SEPU SACCO</h3>

                    <p class="text-sm text-slate-500 dark:text-slate-400">© {{ new Date().getFullYear() }} All rights reserved.</p>
                </div>

                <div class="flex items-center gap-6 text-sm text-slate-500 dark:text-slate-400">
                    <Link href="/about" class="transition hover:text-blue-600"> About </Link>

                    <Link href="/terms" class="transition hover:text-blue-600"> Terms </Link>

                    <Link href="/contact" class="transition hover:text-blue-600"> Contact </Link>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.25s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
