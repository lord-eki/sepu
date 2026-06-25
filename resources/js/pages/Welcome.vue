<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/layouts/PublicLayout.vue'
import { ref, onMounted } from 'vue'

// Animated Counters
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

    <main
      class="relative min-h-screen overflow-hidden bg-slate-50 dark:bg-slate-950"
    >
      <!-- Background Effects -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div
          class="absolute top-0 left-1/2 -translate-x-1/2
          w-[900px] h-[900px]
          bg-blue-500/10 blur-[180px] rounded-full"
        ></div>

        <div
          class="absolute bottom-0 right-0
          w-[700px] h-[700px]
          bg-orange-500/10 blur-[180px] rounded-full"
        ></div>

        <div
          class="absolute top-1/3 left-0
          w-[500px] h-[500px]
          bg-indigo-500/10 blur-[180px] rounded-full"
        ></div>
      </div>

      <!-- Hero -->
      <section
        class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 lg:py-24"
      >
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">

          <!-- LEFT -->
          <div class="order-2 lg:order-1">

            <!-- Badge -->
            <div
              class="inline-flex items-center gap-2
              px-4 py-2 rounded-full
              bg-blue-50 dark:bg-blue-950/40
              border border-blue-200 dark:border-blue-900
              text-blue-900 dark:text-blue-300
              text-sm font-medium"
            >
              <span
                class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"
              ></span>
              Trusted Digital SACCO Platform
            </div>

            <!-- Heading -->
            <h1
              class="mt-6 font-black tracking-tight
              text-4xl sm:text-5xl md:text-6xl xl:text-7xl
              leading-tight text-slate-900 dark:text-white"
            >
              Building Strong
              <span
                class="block bg-gradient-to-r
                from-blue-600
                via-indigo-500
                to-orange-500
                bg-clip-text text-transparent"
              >
                Financial Futures
              </span>
            </h1>

            <!-- Description -->
            <p
              class="mt-6 max-w-2xl
              text-base sm:text-lg lg:text-xl
              leading-relaxed
              text-slate-600 dark:text-slate-300"
            >
              Experience smarter savings, faster loan processing,
              secure transactions, and seamless financial management
              from anywhere with SEPU SACCO.
            </p>

            <!-- CTA -->
            <div class="flex gap-4 mt-10">

              <Link
                :href="route('register')"
                class="inline-flex justify-center items-center
                px-8 py-4 rounded-2xl
                bg-gradient-to-r
                from-blue-600
                to-indigo-700
                hover:from-blue-700
                hover:to-indigo-800
                text-white font-semibold
                shadow-xl shadow-blue-500/20
                transition-all duration-300
                hover:-translate-y-1"
              >
                Sign Up
              </Link>

              <Link
                :href="route('login')"
                class="inline-flex justify-center items-center
                px-8 py-4 rounded-2xl
                border border-slate-300 dark:border-slate-700
                bg-white/80 dark:bg-slate-900/80
                backdrop-blur-xl
                text-slate-700 dark:text-slate-200
                font-semibold
                hover:bg-slate-700 hover:text-white dark:hover:bg-slate-800
                transition-all duration-300
                hover:-translate-y-1"
              >
                Login
              </Link>

            </div>

            <!-- Stats -->
            <div
              class="grid grid-cols-3 gap-2 sm:gap-4 mt-14"
            >
              <div
                class="rounded-3xl p-5
                bg-white/80 dark:bg-slate-900/80
                backdrop-blur-xl
                border border-slate-200 dark:border-slate-800
                shadow-sm"
              >
                <h3
                  class="text-xl sm:text-2xl font-black
                  text-slate-900 dark:text-white"
                >
                  {{ members }}+
                </h3>

                <p
                  class="mt-1 text-sm
                  text-slate-500 dark:text-slate-400"
                >
                  Active Members
                </p>
              </div>

              <div
                class="rounded-3xl p-5
                bg-white/80 dark:bg-slate-900/80
                backdrop-blur-xl
                border border-slate-200 dark:border-slate-800
                shadow-sm"
              >
                <h3
                  class="text-xl sm:text-2xl font-black
                  text-slate-900 dark:text-white"
                >
                  KES {{ loans }}M+
                </h3>

                <p
                  class="mt-1 text-sm
                  text-slate-500 dark:text-slate-400"
                >
                  Loans Issued
                </p>
              </div>

              <div
                class="rounded-3xl p-5
                bg-white/80 dark:bg-slate-900/80
                backdrop-blur-xl
                border border-slate-200 dark:border-slate-800
                shadow-sm"
              >
                <h3
                  class="text-lg sm:text-2xl font-black
                  text-slate-900 dark:text-white"
                >
                  {{ years }}+
                </h3>

                <p
                  class="mt-1 text-sm
                  text-slate-500 dark:text-slate-400"
                >
                  Years Trusted
                </p>
              </div>
            </div>

          </div>

          <!-- RIGHT -->
          <div class="relative order-1 lg:order-2">

            <!-- Main Card -->
            <div
              class="relative overflow-hidden rounded-[32px]
              border border-white/20
              bg-white/70 dark:bg-slate-900/70
              backdrop-blur-2xl
              shadow-[0_20px_80px_rgba(0,0,0,0.15)]"
            >
              <img
                src="/sacco.png"
                alt="SEPU SACCO"
                class="w-full h-[300px] sm:h-[500px] object-cover"
              />

              <div
                class="absolute inset-0
                bg-gradient-to-t
                from-slate-950/70
                via-transparent
                to-transparent"
              ></div>

              <!-- Bottom Label -->
              <div
                class="absolute bottom-5 left-5"
              >
                <div
                  class="px-4 py-2 rounded-xl
                  bg-white/10 backdrop-blur-xl
                  border border-white/20
                  text-white text-sm font-medium"
                >
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