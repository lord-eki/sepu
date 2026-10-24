<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { CreditCard, FileWarning, Phone, RefreshCcw, Shield, Users } from 'lucide-vue-next';

const sections = [
    {
        title: '1. Membership Eligibility',
        icon: Users,
        intro: 'Membership is open to individuals who meet the SACCO’s eligibility criteria. All members must:',
        points: [
            'Provide accurate personal and financial details during registration.',
            'Maintain only one active membership account.',
            'Promptly update any changes in their personal information.',
        ],
    },
    {
        title: '2. Savings and Loan Policies',
        icon: CreditCard,
        intro: 'Members agree to comply with SACCO policies regarding savings contributions and loan management. This includes:',
        points: [
            'Making regular contributions as per agreed schedules.',
            'Submitting truthful loan applications.',
            'Repaying loans on time to avoid penalties or suspension.',
            'Accepting SACCO’s right to recover overdue loans through guarantors or other lawful means.',
        ],
    },
    {
        title: '3. Data Privacy & Security',
        icon: Shield,
        intro: 'SEPU SACCO values your privacy. We commit to:',
        points: [
            'Safeguarding your personal and financial information.',
            'Not sharing data without your consent except where required by law.',
            'Using secure systems to prevent unauthorized access.',
        ],
    },
    {
        title: '4. Amendments to Terms',
        icon: RefreshCcw,
        intro: 'We may revise these terms to reflect policy updates, legal requirements, or service improvements. Continued use of our services constitutes acceptance of the revised terms.',
    },
    {
        title: '5. Termination & Disputes',
        icon: FileWarning,
        intro: 'SEPU SACCO reserves the right to suspend or terminate membership in cases of:',
        points: [
            'Fraudulent activities or misrepresentation.',
            'Persistent default on loan obligations.',
            'Violation of SACCO by-laws or policies.',
            'Unresolved disputes may be referred to the relevant cooperative tribunal.',
        ],
    },
];
</script>

<template>
    <PublicLayout>
        <Head title="Terms of Service" />

        <main
            class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900"
        >
            <!-- Soft Background Accent -->
            <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-blue-200 opacity-30 blur-[120px] dark:bg-blue-800/30"></div>
            <div
                class="absolute bottom-0 -left-40 h-[500px] w-[500px] rounded-full bg-orange-200 opacity-30 blur-[120px] dark:bg-orange-700/30"
            ></div>

            <div class="relative mx-auto max-w-5xl space-y-20 px-6 py-10 sm:py-16 lg:py-24">
                <!-- Header -->
                <section class="space-y-6 text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl dark:text-white">
                        Terms of
                        <span
                            class="bg-gradient-to-r from-blue-600 to-orange-500 bg-clip-text text-transparent dark:from-blue-400 dark:to-orange-400"
                        >
                            Service
                        </span>
                    </h1>

                    <p class="mx-auto max-w-3xl leading-relaxed text-gray-600 sm:text-lg dark:text-gray-300">
                        These terms govern your use of SEPU SACCO’s services. Please review them carefully to understand your rights and obligations
                        as a member.
                    </p>
                </section>

                <!-- Introduction -->
                <section
                    class="rounded-3xl border border-gray-200 bg-white/80 p-10 leading-relaxed text-gray-600 shadow-xl backdrop-blur-xl dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-300"
                >
                    <p>
                        By joining SEPU SACCO and using our services, you agree to abide by the following terms and conditions. These provisions are
                        designed to protect both our members and the SACCO as a whole. If you do not agree, you may choose not to use our services.
                    </p>
                </section>

                <!-- Terms Sections -->
                <div class="space-y-10">
                    <section
                        v-for="(item, index) in sections"
                        :key="index"
                        class="group rounded-3xl border border-gray-200 bg-white/80 p-8 shadow-md backdrop-blur-lg transition-all duration-500 hover:-translate-y-1 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800/60"
                    >
                        <div class="mb-4 flex items-center gap-3">
                            <component :is="item.icon" class="h-6 w-6 text-orange-500 transition group-hover:scale-110" />
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ item.title }}
                            </h2>
                        </div>

                        <p class="mb-4 leading-relaxed text-gray-600 dark:text-gray-300">
                            {{ item.intro }}
                        </p>

                        <ul v-if="item.points" class="ml-6 list-disc space-y-2 text-gray-600 dark:text-gray-300">
                            <li v-for="(point, i) in item.points" :key="i">
                                {{ point }}
                            </li>
                        </ul>
                    </section>
                </div>

                <!-- Contact Section -->
                <section
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900 to-blue-800 p-12 text-white shadow-2xl dark:from-blue-800 dark:to-blue-700"
                >
                    <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-orange-400 opacity-20 blur-3xl dark:bg-orange-600/30"></div>

                    <div class="relative space-y-6 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <Phone class="h-6 w-6 text-orange-400" />
                            <h2 class="text-2xl font-bold">Need Clarification?</h2>
                        </div>

                        <p class="mx-auto max-w-2xl text-gray-200">
                            If you have any questions regarding these terms, please contact us through the details below.
                        </p>

                        <div class="space-y-2 text-sm text-gray-200 sm:text-base">
                            <p>
                                Email:
                                <a href="mailto:support@sepusacco.co.ke" class="text-orange-400 underline hover:text-orange-300">
                                    support@sepusacco.co.ke
                                </a>
                            </p>
                            <p>Phone: +254 712 345 678</p>
                            <p>Office: SEPU SACCO HQ, Nairobi, Kenya</p>
                        </div>
                    </div>
                </section>

                <!-- Footer Note -->
                <p class="text-center text-xs text-gray-500 italic dark:text-gray-400">Last updated: July 2025</p>
            </div>
        </main>
    </PublicLayout>
</template>
