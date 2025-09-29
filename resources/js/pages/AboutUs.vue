<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Users, ShieldCheck, PiggyBank, Handshake, BookOpen } from 'lucide-vue-next'
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'About SACCO', href: '/about-us' }]">

    <Head title="About SACCO" />

    <div class="px-4 sm:px-6 lg:px-12 py-10 space-y-12 bg-gray-50 min-h-screen">
      
      <!-- Hero Section -->
      <section class="text-center space-y-4">
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white bg-blue-900 inline-block px-6 py-3 rounded-xl shadow-lg">
          About SEPU SACCO
        </h1>
        <p class="text-sm md:text-base text-gray-700 max-w-3xl mx-auto mt-3">
          Our SACCO is built on trust, transparency, and community support.
          We empower our members to save, access affordable credit, and
          enjoy sustainable financial growth.
        </p>
      </section>

      <!-- Mission & Vision -->
      <section class="grid md:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-orange-500">
          <h2 class="text-lg sm:text-xl font-semibold text-blue-900 flex items-center gap-2">
            <ShieldCheck class="w-6 h-6 text-orange-500" /> Our Mission
          </h2>
          <p class="mt-3 text-sm md:text-base text-gray-600 leading-relaxed">
            To provide members with reliable financial solutions through
            savings and affordable credit while promoting a culture of
            financial discipline and inclusivity.
          </p>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 border-t-4 border-orange-500">
          <h2 class="text-lg sm:text-xl font-semibold text-blue-900 flex items-center gap-2">
            <BookOpen class="w-6 h-6 text-orange-500" /> Our Vision
          </h2>
          <p class="mt-3 text-sm md:text-base text-gray-600 leading-relaxed">
            To be the most trusted SACCO offering innovative, member-focused
            financial services for sustainable community development.
          </p>
        </div>
      </section>

      <!-- Core Values -->
      <section>
        <h2 class="text-2xl font-semibold text-blue-900 text-center mb-8">
          Our Core Values
        </h2>
        <div class="grid md:grid-cols-3 gap-6">
          <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-900">
            <Users class="w-8 h-8 text-orange-500 mb-3" />
            <h3 class="font-semibold text-gray-800 text-base">Community</h3>
            <p class="text-sm text-gray-600 mt-2">
              We are stronger together. We build financial freedom through
              collective savings and support.
            </p>
          </div>

          <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-900">
            <PiggyBank class="w-8 h-8 text-orange-500 mb-3" />
            <h3 class="font-semibold text-gray-800 text-base">Savings Culture</h3>
            <p class="text-sm text-gray-600 mt-2">
              We promote disciplined savings as the foundation of financial
              security and growth.
            </p>
          </div>

          <div class="bg-white rounded-xl shadow p-6 border-l-4 border-blue-900">
            <Handshake class="w-8 h-8 text-orange-500 mb-3" />
            <h3 class="font-semibold text-gray-800 text-base">Integrity & Trust</h3>
            <p class="text-sm text-gray-600 mt-2">
              We operate with honesty, fairness, and transparency for the
              benefit of all our members.
            </p>
          </div>
        </div>
      </section>

      <!-- Member Info Prompt -->
      <section class="bg-blue-900 text-white rounded-2xl shadow-lg p-8 text-center space-y-4">
        <h2 class="text-lg sm:text-xl font-bold">Empowering Our Members</h2>
        <p class="text-sm md:text-base max-w-2xl mx-auto">
          As a registered member, you are part of a growing community focused on financial empowerment.
          Learn more about how to make the most of your membership through saving, borrowing, and
          participating in SACCO programs.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
          <a href="#"
            class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-3 rounded-lg font-semibold shadow transition text-sm sm:text-base">
            Explore Savings Options
          </a>
          <a href="#"
            class="bg-white border border-orange-500 text-orange-600 hover:bg-orange-50 px-5 py-3 rounded-lg font-semibold shadow transition text-sm sm:text-base">
            View Member Programs
          </a>
        </div>
      </section>

    </div>
  </AppLayout>
</template>
