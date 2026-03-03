<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/layouts/PublicLayout.vue'

const menuOpen = ref(false)
</script>

<template>
  <PublicLayout>

    <Head title="Welcome" />

    <!-- Hero Section -->
    <main class="relative flex-grow overflow-hidden 
             bg-gradient-to-br from-slate-50 via-white to-blue-50
             dark:from-gray-900 dark:via-gray-950 dark:to-blue-950">

      <!-- Soft Background Accents -->
      <div class="absolute -top-40 -right-40 w-[500px] h-[500px] 
                  rounded-full blur-[120px] opacity-40 
                  bg-white-200 dark:bg-gray-700/30"></div>
      <div class="absolute bottom-0 -left-40 w-[500px] h-[500px] 
                  rounded-full blur-[120px] opacity-40 
                  bg-orange-200 dark:bg-orange-900/30"></div>

      <div class="relative max-w-7xl mx-auto px-6 py-10 lg:py-20 grid lg:grid-cols-2 gap-16 items-center">

        <!-- LEFT CONTENT -->
        <div class="space-y-8">

          <!-- Heading -->
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight 
                     text-gray-900 dark:text-gray-100">
            Smart Banking for
            <span
              class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-orange-500 dark:from-blue-400 dark:to-orange-400">
              Modern Members
            </span>
          </h1>

          <!-- Description -->
          <p class="sm:text-lg text-gray-600 dark:text-gray-300 leading-relaxed max-w-xl">
            SEPU SACCO empowers you with innovative savings solutions,
            affordable loans, and a transparent financial ecosystem
            designed for sustainable growth.
          </p>

          <!-- Buttons -->
          <div class="flex flex-wrap gap-5 pt-4">
            <Link :href="route('login')" class="px-8 py-3 rounded-xl font-semibold text-white
                         bg-blue-900 shadow-lg shadow-blue-200
                         hover:bg-blue-800 hover:shadow-blue-300
                         dark:bg-blue-700 dark:shadow-blue-800/50 dark:hover:bg-blue-600
                         transition-all duration-300 hover:scale-105">
            Log In
            </Link>

            <Link :href="route('register')" class="px-8 py-3 rounded-xl font-semibold
                         border border-gray-300 bg-white
                         hover:bg-gray-50 hover:border-gray-400
                         dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200
                         dark:hover:bg-gray-700 dark:hover:border-gray-500
                         transition-all duration-300">
            Register
            </Link>
          </div>

          <!-- Stats -->
          <div class="flex gap-5 sm:gap-10 pt-10 text-sm">
            <div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">100+</p>
              <p class="text-gray-500 dark:text-gray-400">Active Members</p>
            </div>
            <div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">KES 500K+</p>
              <p class="text-gray-500 dark:text-gray-400">Loans Issued</p>
            </div>
            <div>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">10+ Years</p>
              <p class="text-gray-500 dark:text-gray-400">Trusted Service</p>
            </div>
          </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="relative">

          <!-- Glass Card -->
          <div class="relative p-6 rounded-3xl bg-white/70 dark:bg-gray-800/60 backdrop-blur-xl
                      border border-gray-200 dark:border-gray-700 shadow-2xl
                      hover:shadow-3xl transition-all duration-700">

            <img src="/sacco.png" alt="SEPU SACCO platform" class="rounded-2xl w-full object-cover" />

            <!-- Floating Tag -->
            <div class="absolute -bottom-6 left-8 px-6 py-3 rounded-xl
                        bg-gradient-to-r from-orange-500 to-orange-400 dark:from-orange-600 dark:to-orange-500
                        text-white text-sm font-semibold shadow-lg">
              #EmpoweringMembers
            </div>
          </div>

          <!-- Badge -->
          <div class="inline-flex items-center gap-2 px-4 mt-10 py-2 rounded-full 
                      bg-white/5 dark:bg-gray-700/20 shadow-sm text-xs sm:text-sm text-gray-600 dark:text-gray-300">
            SEPU SACCO<span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
            Trusted & Secure Cooperative Platform
          </div>

          <!-- Decorative Accent Card -->
          <div class="absolute -top-10 -right-10 w-40 bg-blue-100 dark:bg-blue-700 rounded-3xl blur-2xl opacity-70">
          </div>

        </div>

      </div>
    </main>
  </PublicLayout>
</template>