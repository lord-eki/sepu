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
    <main class="flex-grow flex items-center justify-center mt-5 sm:mt-10 px-3 sm:px-6 py-5 sm:py-10">
      <div class="max-w-7xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

        <!-- Text Content -->
        <div class="space-y-8 text-center lg:text-left">
          <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight text-blue-900 dark:text-white">
            Welcome to <span class="text-orange-500">SEPU SACCO</span>
          </h1>
          <p class="text-gray-700 dark:text-gray-300 text-lg leading-relaxed max-w-xl mx-auto lg:mx-0">
            Empowering members through savings, affordable loans, and financial growth.
            Join a community that values your success — secure, transparent, and built for your future.
          </p>

          <div class="flex justify-center lg:justify-start gap-4">
            <Link :href="route('login')" class="bg-blue-900 text-white px-7 py-3 rounded-full font-medium shadow-md 
                     hover:shadow-lg hover:scale-105 hover:bg-blue-800 transition-all duration-300">
            Log In
            </Link>

            <Link :href="route('register')" class="border border-blue-900 text-blue-900 dark:text-white px-7 py-3 rounded-full 
                     font-medium hover:bg-blue-50 dark:hover:bg-blue-800/60 transition-all duration-300">
            Sign Up
            </Link>
          </div>
        </div>

        <!-- Hero Image -->
        <div class="relative group">
          <div
            class="relative rounded-2xl overflow-hidden shadow-2xl transform transition-all duration-700 group-hover:scale-[1.03]">
            <img src="/sacco.png" alt="SEPU SACCO illustration" class="w-full h-full object-cover rounded-2xl" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
          </div>

          <!-- Floating Label -->
          <div class="absolute bottom-4 left-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md 
                   text-blue-900 dark:text-gray-100 px-4 py-2 rounded-lg shadow-lg text-sm 
                   font-medium tracking-wide">
            #EmpoweringMembers
          </div>
        </div>
      </div>
    </main>
  </PublicLayout>
</template>
