<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Users, CreditCard, Shield, RefreshCcw, FileWarning, Phone } from 'lucide-vue-next'
</script>

<template>

    <Head title="Terms of Service" />

    <AppLayout :breadcrumbs="[{ title: 'Terms of Service', href: '/terms' }]">
        <div class="max-w-4xl mx-auto px-6 py-10 space-y-8">
            <!-- Title -->
            <div class="text-center space-y-2">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 dark:text-blue-200">
                    Terms of Service
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                    Please review these terms carefully before using SEPU SACCO services.
                </p>
            </div>

            <!-- Introduction -->
            <div class="bg-blue-50 dark:bg-blue-900/40 p-5 rounded-xl shadow">
                <p class="text-xs sm:text-sm md:text-base text-gray-700 dark:text-gray-300 leading-relaxed">
                    By joining SEPU SACCO and using our services, you agree to abide by the following
                    terms and conditions. These terms are designed to protect both our members and
                    the SACCO as a whole. If you do not agree, you may choose not to use our services.
                </p>
            </div>

            <!-- Terms Sections -->
            <div class="space-y-6">
                <section class="space-y-2">
                    <h2
                        class="flex items-center gap-2 text-lg sm:text-xl font-semibold text-blue-900 dark:text-blue-100">
                        <Users class="w-5 h-5 text-blue-700" /> 1. Membership Eligibility
                    </h2>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">
                        Membership is open to individuals who meet the SACCO’s eligibility criteria.
                        All members must:
                    </p>
                    <ul
                        class="list-disc ml-6 text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400 space-y-1">
                        <li>Provide accurate personal and financial details during registration.</li>
                        <li>Maintain only one active membership account.</li>
                        <li>Promptly update any changes in their personal information.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2
                        class="flex items-center gap-2 text-lg sm:text-xl font-semibold text-blue-900 dark:text-blue-100">
                        <CreditCard class="w-5 h-5 text-blue-700" /> 2. Savings and Loan Policies
                    </h2>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">
                        Members agree to comply with SACCO policies regarding savings contributions
                        and loan management. This includes:
                    </p>
                    <ul
                        class="list-disc ml-6 text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400 space-y-1">
                        <li>Making regular contributions as per agreed schedules.</li>
                        <li>Submitting truthful loan applications.</li>
                        <li>Repaying loans on time to avoid penalties or suspension.</li>
                        <li>Accepting SACCO’s right to recover overdue loans through guarantors or other means.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2
                        class="flex items-center gap-2 text-lg sm:text-xl font-semibold text-blue-900 dark:text-blue-100">
                        <Shield class="w-5 h-5 text-blue-700" /> 3. Data Privacy & Security
                    </h2>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">
                        SEPU SACCO values your privacy. We commit to:
                    </p>
                    <ul
                        class="list-disc ml-6 text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400 space-y-1">
                        <li>Safeguarding your personal and financial information.</li>
                        <li>Not sharing data with third parties without your consent, except where required by law.</li>
                        <li>Using secure systems to protect against unauthorized access.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2
                        class="flex items-center gap-2 text-lg sm:text-xl font-semibold text-blue-900 dark:text-blue-100">
                        <RefreshCcw class="w-5 h-5 text-blue-700" /> 4. Amendments to Terms
                    </h2>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">
                        We may revise these terms to reflect policy changes, legal requirements, or
                        improvements to our services. Updated terms will be communicated through official
                        SACCO channels, and continued use constitutes acceptance of the new terms.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2
                        class="flex items-center gap-2 text-lg sm:text-xl font-semibold text-blue-900 dark:text-blue-100">
                        <FileWarning class="w-5 h-5 text-blue-700" /> 5. Termination & Disputes
                    </h2>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">
                        SEPU SACCO reserves the right to suspend or terminate membership in cases of:
                    </p>
                    <ul
                        class="list-disc ml-6 text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400 space-y-1">
                        <li>Fraudulent activities or misrepresentation.</li>
                        <li>Persistent default on loan obligations.</li>
                        <li>Violation of SACCO’s by-laws and policies.</li>
                    </ul>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">
                        Disputes will first be resolved internally through mediation. If unresolved,
                        they may be referred to the relevant cooperative tribunal or legal body.
                    </p>
                </section>
            </div>

            <!-- Contact -->
            <div class="bg-blue-50 dark:bg-blue-900/40 p-6 rounded-xl shadow-md space-y-2">
                <h2 class="flex items-center gap-2 text-base sm:text-lg font-semibold text-blue-800 dark:text-blue-100">
                    <Phone class="w-5 h-5 text-blue-600" /> Contact Information
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">
                    For any questions or clarifications about these terms, please reach out:
                </p>
                <ul class="ml-6 text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-400">
                    <li>Email: <a href="mailto:support@sepusacco.org"
                            class="text-blue-700 dark:text-blue-300 underline">support@sepusacco.org</a></li>
                    <li>Phone: +254 712 345 678</li>
                    <li>Office: SEPU SACCO HQ, Nairobi, Kenya</li>
                </ul>
            </div>

            <!-- Last Updated -->
            <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 italic text-center">
                Last updated: July 2025
            </p>
        </div>
    </AppLayout>
</template>