<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/layouts/PublicLayout.vue'
import { ref, onMounted } from 'vue'

// Animated counters
const members = ref(0)
const loans = ref(0)
const years = ref(0)

const animateValue = (
  target: number,
  duration: number,
  callback: (value: number) => void
) => {
  let start = 0
  const increment = target / (duration / 16)

  const timer = setInterval(() => {
    start += increment

    if (start >= target) {
      callback(target)
      clearInterval(timer)
    } else {
      callback(Math.floor(start))
    }
  }, 16)
}

onMounted(() => {
  animateValue(100, 1800, (val) => (members.value = val))
  animateValue(500, 2200, (val) => (loans.value = val))
  animateValue(30, 1600, (val) => (years.value = val))
})
</script>

<template>
  <PublicLayout>

    <Head title="Welcome" />

    <main class="relative overflow-hidden min-h-screen
      bg-[#f8fafc] dark:bg-[#020617]">
      <!-- Background Effects -->
      <div class="absolute top-0 left-1/2 -translate-x-1/2
        w-[900px] h-[900px]
        bg-blue-500/10 blur-3xl rounded-full"></div>

      <div class="absolute bottom-0 right-0
        w-[500px] h-[500px]
        bg-orange-500/10 blur-3xl rounded-full"></div>

      <section class="relative max-w-7xl mx-auto
        px-6 lg:px-8
        pt-10 sm:pt-16 lg:pt-24
        pb-24">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
          <!-- LEFT -->
          <div>
            <!-- Badge -->
            <div class="inline-flex items-center gap-2
              px-4 py-2 mb-8
              rounded-full
              border border-blue-100 dark:border-blue-900
              bg-white/20 dark:bg-slate-900/70
              backdrop-blur-xl
              text-xs text-orange-500 dark:text-slate-300">
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              Trusted SACCO Platform
            </div>

            <!-- Heading -->
            <h1 class="text-5xl lg:text-6xl
              font-black tracking-tight
              leading-[1.10]
              text-slate-900 dark:text-white">
              Banking built for
              <span class="bg-gradient-to-r
                from-blue-600 via-blue-500 to-orange-500
                bg-clip-text text-transparent">
                modern members !
              </span>
            </h1>

            <!-- Description -->
            <p class="mt-8 max-w-2xl
              text-lg sm:text-xl
              leading-relaxed
              text-slate-600 dark:text-slate-300">
              SEPU SACCO delivers secure savings, affordable loans,
              and intelligent financial tools that help members grow
              with confidence.
            </p>

            <!-- CTA -->
            <div class="flex flex-wrap gap-4 mt-10">

              <!-- Primary Button -->
              <Link :href="route('register')" class="px-8 py-4 rounded-2xl
  bg-gradient-to-r from-blue-600 to-blue-800
  hover:from-blue-700 hover:to-blue-900
  text-white font-semibold
  shadow-lg shadow-blue-500/30
  transition-all duration-300
  hover:-translate-y-1">
              Get Started
              </Link>

              <!-- Secondary Button (FIXED LOGIN) -->
              <Link :href="route('login')" class="px-8 py-4 rounded-2xl
  border-2 border-blue-600
  bg-white dark:bg-slate-900
  text-blue-700 dark:text-white
  font-semibold

  hover:bg-blue-600
  hover:text-white
  hover:border-blue-600

  shadow-sm hover:shadow-lg
  transition-all duration-300
  hover:-translate-y-1">
              Login
              </Link>

            </div>
            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 sm:gap-8
              mt-16 pt-10
              border-t border-slate-200 dark:border-slate-800">
              <div>
                <h3 class="text-2xl sm:text-3xl font-black
                  text-slate-900 dark:text-white">
                  {{ members }}+
                </h3>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                  Active Members
                </p>
              </div>

              <div>
                <h3 class="text-2xl sm:text-3xl font-black
                  text-slate-900 dark:text-white">
                  KES {{ loans }}M+
                </h3>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                  Loans Issued
                </p>
              </div>

              <div>
                <h3 class="text-2xl sm:text-3xl font-black
                  text-slate-900 dark:text-white">
                  {{ years }}+
                </h3>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                  Years Trusted
                </p>
              </div>
            </div>
          </div>

          <!-- RIGHT -->
          <div class="relative lg:pl-10">
            <!-- Floating Card -->
            <div class="relative
              rounded-[2rem]
              overflow-hidden
              border border-white/20
              bg-white/60 dark:bg-slate-900/60
              backdrop-blur-2xl
              shadow-2xl">
              <img src="/sacco.png" alt="SEPU SACCO" class="w-full h-full object-cover" />

              <!-- Overlay -->
              <div class="absolute inset-0
                bg-gradient-to-t
                from-slate-950/50
                via-transparent
                to-transparent"></div>

              <!-- Bottom Info -->
              <div class="absolute bottom-0 left-0 right-0
                p-6">
                <div class="inline-flex items-center gap-2
                  px-4 py-2 rounded-xl
                  bg-white/10 backdrop-blur-xl
                  border border-white/20
                  text-white text-sm">
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