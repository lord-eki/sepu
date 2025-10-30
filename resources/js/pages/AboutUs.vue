<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Users, ShieldCheck, PiggyBank, Handshake, BookOpen } from 'lucide-vue-next'

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
]
</script>

<template>
  <PublicLayout>
    <Head title="About Us" />

    <div class="bg-gradient-to-b from-gray-50 to-white py-16 sm:py-20 px-4 sm:px-6 lg:px-20 space-y-20">

      <!-- Hero Section -->
      <section class="text-center space-y-4">
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-blue-900 tracking-tight">
          About <span class="text-orange-500">SEPU SACCO</span>
        </h1>
        <p class="text-gray-600 max-w-3xl mx-auto text-lg sm:text-xl leading-relaxed">
          Empowering our members through savings, affordable credit, and financial education —
          building a secure and prosperous community, one member at a time.
        </p>
      </section>

      <!-- Mission & Vision -->
      <section class="grid md:grid-cols-2 gap-10">
        <div class="rounded-3xl bg-white p-10 shadow-xl hover:shadow-2xl transition-all border-t-4 border-orange-500">
          <div class="flex items-center gap-3 mb-4">
            <ShieldCheck class="w-8 h-8 text-orange-500" />
            <h2 class="text-2xl font-bold text-blue-900">Our Mission</h2>
          </div>
          <p class="text-gray-700 text-base leading-relaxed">
            To provide reliable, innovative, and affordable financial solutions that promote savings,
            responsible borrowing, and sustainable community development.
          </p>
        </div>

        <div class="rounded-3xl bg-white p-10 shadow-xl hover:shadow-2xl transition-all border-t-4 border-blue-900">
          <div class="flex items-center gap-3 mb-4">
            <BookOpen class="w-8 h-8 text-blue-900" />
            <h2 class="text-2xl font-bold text-blue-900">Our Vision</h2>
          </div>
          <p class="text-gray-700 text-base leading-relaxed">
            To be the most trusted SACCO in transforming lives through inclusive, innovative,
            and ethical financial services.
          </p>
        </div>
      </section>

      <!-- Core Values -->
      <section>
        <h2 class="text-3xl sm:text-4xl font-bold text-center text-blue-900 mb-12">
          Our Core Values
        </h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(value, index) in values"
            :key="index"
            class="group bg-white rounded-2xl shadow-md p-8 border-l-4 hover:-translate-y-1 hover:shadow-lg transition-all duration-300"
            :class="index % 2 === 0 ? 'border-orange-500' : 'border-blue-900'"
          >
            <div class="flex items-center gap-3 mb-3">
              <component :is="value.icon" class="w-6 h-6 text-orange-500 group-hover:scale-110 transition" />
              <h3 class="text-lg font-semibold text-blue-900">{{ value.title }}</h3>
            </div>
            <p class="text-gray-600 text-sm leading-relaxed">{{ value.desc }}</p>
          </div>
        </div>
      </section>

      <!-- Join Section -->
      <section class="bg-gradient-to-r from-blue-900 to-blue-800 text-white rounded-3xl p-12 text-center shadow-xl">
        <h2 class="text-3xl font-bold mb-4">Join SEPU SACCO Today</h2>
        <p class="max-w-2xl mx-auto text-base sm:text-lg mb-8 leading-relaxed text-gray-200">
          Be part of a growing community that empowers your financial journey. Save smart,
          borrow responsibly, and secure your future with SEPU SACCO.
        </p>
        <div class="flex flex-wrap justify-center gap-5">
          <Link
            :href="route('register')"
            class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-full font-semibold shadow-lg transition-transform hover:scale-105"
          >
            Become a Member
          </Link>
          <Link
            :href="route('login')"
            class="bg-white text-blue-900 border border-orange-500 hover:bg-orange-50 px-8 py-3 rounded-full font-semibold shadow-lg transition-transform hover:scale-105"
          >
            Member Login
          </Link>
        </div>
      </section>

    </div>
  </PublicLayout>
</template>
