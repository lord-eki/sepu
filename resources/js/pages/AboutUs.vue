<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, Handshake, PiggyBank, ShieldCheck, Users } from 'lucide-vue-next';

const values = [
    {
        title: 'Integrity',
        desc: 'We act with honesty, fairness, and transparency in every transaction.',
        icon: ShieldCheck,
    },
    {
        title: 'Teamwork',
        desc: 'We work together, share knowledge, and build success as a unified community.',
        icon: Users,
    },
    {
        title: 'Innovation',
        desc: 'We continuously improve and embrace technology to serve members better.',
        icon: PiggyBank,
    },
    {
        title: 'Accountability',
        desc: 'We take full responsibility for the trust our members place in us.',
        icon: Handshake,
    },
    {
        title: 'Excellence',
        desc: 'We deliver outstanding services and continuously raise our standards.',
        icon: BookOpen,
    },
    {
        title: 'Community Empowerment',
        desc: 'We support the social and economic growth of our members and society.',
        icon: Users,
    },
];
</script>

<template>
    <PublicLayout>
        <Head title="About Us" />

        <main
            class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900"
        >
            <!-- Soft Background Accents -->
            <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-blue-200 opacity-30 blur-[120px] dark:bg-blue-800/30"></div>
            <div
                class="absolute bottom-0 -left-40 h-[500px] w-[500px] rounded-full bg-orange-200 opacity-30 blur-[120px] dark:bg-orange-700/30"
            ></div>

            <div class="relative mx-auto max-w-7xl space-y-24 px-6 py-10 sm:py-16 lg:py-24">
                <!-- HERO -->
                <section class="mx-auto max-w-3xl space-y-6 text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl dark:text-white">
                        About
                        <span
                            class="bg-gradient-to-r from-blue-600 to-orange-500 bg-clip-text text-3xl text-transparent sm:text-5xl dark:from-blue-400 dark:to-orange-400"
                        >
                            SEPU SACCO
                        </span>
                    </h1>

                    <p class="leading-relaxed text-gray-600 sm:text-lg dark:text-gray-300">
                        Empowering our members through innovative savings solutions, affordable credit, and financial education — building a secure
                        and prosperous community, one member at a time.
                    </p>
                </section>

                <!-- MISSION & VISION -->
                <section class="grid gap-10 md:grid-cols-2">
                    <div
                        class="group rounded-3xl border border-gray-200 bg-white/70 p-10 shadow-xl backdrop-blur-xl transition-all duration-500 hover:-translate-y-1 hover:shadow-2xl dark:border-gray-700 dark:bg-gray-800/60"
                    >
                        <div class="mb-4 flex items-center gap-3">
                            <ShieldCheck class="h-8 w-8 text-orange-500 transition group-hover:scale-110" />
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Our Mission</h2>
                        </div>
                        <p class="leading-relaxed text-gray-600 dark:text-gray-300">
                            To provide reliable, innovative, and affordable financial solutions that promote savings, responsible borrowing, and
                            sustainable community development.
                        </p>
                    </div>

                    <div
                        class="group rounded-3xl border border-gray-200 bg-white/70 p-10 shadow-xl backdrop-blur-xl transition-all duration-500 hover:-translate-y-1 hover:shadow-2xl dark:border-gray-700 dark:bg-gray-800/60"
                    >
                        <div class="mb-4 flex items-center gap-3">
                            <BookOpen class="h-8 w-8 text-blue-600 transition group-hover:scale-110" />
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Our Vision</h2>
                        </div>
                        <p class="leading-relaxed text-gray-600 dark:text-gray-300">
                            To be the most trusted SACCO transforming lives through inclusive, innovative, and ethical financial services.
                        </p>
                    </div>
                </section>

                <!-- CORE VALUES -->
                <section>
                    <h2 class="mb-14 text-center text-3xl font-bold text-gray-900 sm:text-4xl dark:text-white">Our Core Values</h2>

                    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="(value, index) in values"
                            :key="index"
                            class="group rounded-2xl border border-gray-200 bg-white/80 p-8 shadow-md backdrop-blur-lg transition-all duration-500 hover:-translate-y-2 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800/60"
                        >
                            <div class="mb-4 flex items-center gap-3">
                                <component :is="value.icon" class="h-6 w-6 text-orange-500 transition group-hover:scale-110" />
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ value.title }}
                                </h3>
                            </div>

                            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                                {{ value.desc }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- CTA -->
                <section
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900 to-blue-800 p-14 text-center text-white shadow-2xl dark:from-blue-800 dark:to-blue-900"
                >
                    <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-orange-400 opacity-20 blur-3xl dark:bg-orange-700/30"></div>

                    <div class="relative mx-auto max-w-2xl space-y-6">
                        <h2 class="text-3xl font-bold sm:text-4xl">Join SEPU SACCO Today</h2>

                        <p class="leading-relaxed text-gray-200 dark:text-gray-300">
                            Be part of a growing financial community. Save smart, borrow responsibly, and secure your future with confidence.
                        </p>

                        <div class="flex flex-wrap justify-center gap-5 pt-4">
                            <Link
                                :href="route('register')"
                                class="rounded-xl bg-orange-500 px-8 py-3 font-semibold shadow-lg transition-all duration-300 hover:scale-105 hover:bg-orange-600 dark:bg-orange-600 dark:hover:bg-orange-500"
                            >
                                Become a Member
                            </Link>

                            <Link
                                :href="route('login')"
                                class="rounded-xl bg-white px-8 py-3 font-semibold text-blue-900 shadow-lg transition-all duration-300 hover:scale-105 hover:bg-gray-100 dark:bg-gray-700 dark:text-blue-400 dark:hover:bg-gray-600"
                            >
                                Member Login
                            </Link>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </PublicLayout>
</template>
