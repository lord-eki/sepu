<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Users, ShieldCheck, PiggyBank, Handshake, BookOpen } from 'lucide-vue-next'
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'About SACCO', href: '/about-us' }]">

        <Head title="About SACCO" />

        <div class="px-4 sm:px-6 lg:px-12 py-8 space-y-12 bg-gray-50 min-h-screen">
            <!-- Hero Section -->
            <section class="text-center space-y-4">
                <h1 class="text-xl sm:text-2xl md:text-4xl font-bold text-gray-800">
                    About SEPU SACCO
                </h1>
                <p class="text-xs sm:text-sm md:text-base text-gray-600 max-w-2xl mx-auto">
                    Our SACCO is built on trust, transparency, and community support.
                    We empower our members to save, access affordable credit, and
                    enjoy sustainable financial growth.
                </p>
            </section>

            <!-- Mission & Vision -->
            <section class="grid md:grid-cols-2 gap-8">
                <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
                    <h2 class="text-lg sm:text-xl font-semibold text-blue-900 flex items-center gap-2">
                        <ShieldCheck class="w-5 h-5 text-green-600" /> Our Mission
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm md:text-base text-gray-600">
                        To provide members with reliable financial solutions through
                        savings and affordable credit while promoting a culture of
                        financial discipline and inclusivity.
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
                    <h2 class="text-lg sm:text-xl font-semibold text-blue-900 flex items-center gap-2">
                        <BookOpen class="w-5 h-5 text-indigo-700" /> Our Vision
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm md:text-base text-gray-600">
                        To be the most trusted SACCO offering innovative, member-focused
                        financial services for sustainable community development.
                    </p>
                </div>
            </section>

            <!-- Core Values -->
            <section>
                <h2 class="text-xl sm:text-2xl font-semibold text-blue-900 text-center mb-6">
                    Our Core Values
                </h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-5 shadow">
                        <Users class="w-8 h-8 text-blue-700 mb-3" />
                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Community</h3>
                        <p class="text-xs sm:text-sm text-gray-600 mt-2">
                            We are stronger together. We build financial freedom through
                            collective savings and support.
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-5 shadow">
                        <PiggyBank class="w-8 h-8 text-green-700 mb-3" />
                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Savings Culture</h3>
                        <p class="text-xs sm:text-sm text-gray-600 mt-2">
                            We promote disciplined savings as the foundation of financial
                            security and growth.
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-5 shadow">
                        <Handshake class="w-8 h-8 text-yellow-700 mb-3" />
                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">Integrity & Trust</h3>
                        <p class="text-xs sm:text-sm text-gray-600 mt-2">
                            We operate with honesty, fairness, and transparency for the
                            benefit of all our members.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Member Info Prompt -->
            <section class="bg-blue-100 text-blue-900 rounded-2xl shadow-lg p-8 text-center space-y-4">
                <h2 class="text-lg sm:text-xl font-bold">Empowering Our Members</h2>
                <p class="text-xs sm:text-sm md:text-base max-w-2xl mx-auto">
                    As a registered member, you are part of a growing community focused on financial empowerment.
                    Learn more about how to make the most of your membership through saving, borrowing, and
                    participating in SACCO programs.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="#"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-semibold shadow transition text-sm sm:text-base">
                        Explore Savings Options
                    </a>
                    <a href="#"
                        class="bg-white border border-blue-600 text-blue-600 hover:bg-blue-50 px-5 py-3 rounded-lg font-semibold shadow transition text-sm sm:text-base">
                        View Member Programs
                    </a>
                </div>
            </section>

        </div>
    </AppLayout>
</template>