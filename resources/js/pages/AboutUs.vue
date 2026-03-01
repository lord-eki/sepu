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

    <main class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-blue-50">

      <!-- Soft Background Accents -->
      <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-blue-200 rounded-full blur-[120px] opacity-30">
      </div>
      <div class="absolute bottom-0 -left-40 w-[500px] h-[500px] bg-orange-200 rounded-full blur-[120px] opacity-30">
      </div>

      <div class="relative max-w-7xl mx-auto px-6 py-10 sm:py-16 lg:py-24 space-y-24">

        <!-- HERO -->
        <section class="text-center space-y-6 max-w-3xl mx-auto">
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900">
            About
            <span class="text-3xl sm:text-5xl text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-orange-500">
              SEPU SACCO
            </span>
          </h1>

          <p class="text-gray-600 sm:text-lg leading-relaxed">
            Empowering our members through innovative savings solutions,
            affordable credit, and financial education — building a secure
            and prosperous community, one member at a time.
          </p>
        </section>

        <!-- MISSION & VISION -->
        <section class="grid md:grid-cols-2 gap-10">
          <div class="group rounded-3xl bg-white/70 backdrop-blur-xl p-10
                   border border-gray-200 shadow-xl
                   hover:shadow-2xl hover:-translate-y-1
                   transition-all duration-500">
            <div class="flex items-center gap-3 mb-4">
              <ShieldCheck class="w-8 h-8 text-orange-500 group-hover:scale-110 transition" />
              <h2 class="text-2xl font-bold text-gray-900">Our Mission</h2>
            </div>
            <p class="text-gray-600 leading-relaxed">
              To provide reliable, innovative, and affordable financial solutions
              that promote savings, responsible borrowing, and sustainable
              community development.
            </p>
          </div>

          <div class="group rounded-3xl bg-white/70 backdrop-blur-xl p-10
                   border border-gray-200 shadow-xl
                   hover:shadow-2xl hover:-translate-y-1
                   transition-all duration-500">
            <div class="flex items-center gap-3 mb-4">
              <BookOpen class="w-8 h-8 text-blue-600 group-hover:scale-110 transition" />
              <h2 class="text-2xl font-bold text-gray-900">Our Vision</h2>
            </div>
            <p class="text-gray-600 leading-relaxed">
              To be the most trusted SACCO transforming lives through inclusive,
              innovative, and ethical financial services.
            </p>
          </div>
        </section>

        <!-- CORE VALUES -->
        <section>
          <h2 class="text-3xl sm:text-4xl font-bold text-center text-gray-900 mb-14">
            Our Core Values
          </h2>

          <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="(value, index) in values" :key="index" class="group rounded-2xl bg-white/80 backdrop-blur-lg
                     border border-gray-200 p-8 shadow-md
                     hover:shadow-xl hover:-translate-y-2
                     transition-all duration-500">
              <div class="flex items-center gap-3 mb-4">
                <component :is="value.icon" class="w-6 h-6 text-orange-500 group-hover:scale-110 transition" />
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ value.title }}
                </h3>
              </div>

              <p class="text-gray-600 text-sm leading-relaxed">
                {{ value.desc }}
              </p>
            </div>
          </div>
        </section>

        <!-- CTA -->
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900 to-blue-800
                 p-14 text-center text-white shadow-2xl">
          <div class="absolute -top-20 -right-20 w-64 h-64 bg-orange-400 rounded-full blur-3xl opacity-20"></div>

          <div class="relative space-y-6 max-w-2xl mx-auto">
            <h2 class="text-3xl sm:text-4xl font-bold">
              Join SEPU SACCO Today
            </h2>

            <p class="text-gray-200 leading-relaxed">
              Be part of a growing financial community. Save smart,
              borrow responsibly, and secure your future with confidence.
            </p>

            <div class="flex flex-wrap justify-center gap-5 pt-4">
              <Link :href="route('register')" class="px-8 py-3 rounded-xl font-semibold
                       bg-orange-500 hover:bg-orange-600
                       shadow-lg transition-all duration-300 hover:scale-105">
              Become a Member
              </Link>

              <Link :href="route('login')" class="px-8 py-3 rounded-xl font-semibold
                       bg-white text-blue-900
                       hover:bg-gray-100
                       shadow-lg transition-all duration-300 hover:scale-105">
              Member Login
              </Link>
            </div>
          </div>
        </section>

      </div>
    </main>
  </PublicLayout>
</template>