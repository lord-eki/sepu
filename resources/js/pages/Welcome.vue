<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Welcome" />

  <div
    class="min-h-screen flex items-center justify-center px-6 py-16 
           bg-gradient-to-br from-blue-50 via-white to-orange-50 
           dark:from-gray-950 dark:via-gray-900 dark:to-gray-950"
  >
    <div class="max-w-7xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
      
      <!-- Text Section -->
      <div class="space-y-8 text-center lg:text-left">
        <div class="flex justify-center lg:justify-start">
          <div class="rounded-full p-2">
          <img
            src="/apple-touch-icon1.png"
            alt="Logo"
            class="w-24 shadow-lg rounded-sm hover:scale-110 transition-transform duration-500"
          />
          </div>
        </div>

        <h1
          class="text-3xl sm:text-4xl font-extrabold leading-tight tracking-tight 
                 text-blue-900 dark:text-white"
        >
          Welcome to <span class="text-orange-500">SEPU SACCO</span>
        </h1>

        <p
          class="text-gray-600 dark:text-gray-300 text-base sm:text-lg leading-relaxed 
                 max-w-xl mx-auto lg:mx-0"
        >
          Empowering members through savings, affordable loans, and financial growth.  
          Join a community that values your success — secure, transparent, and built for your future.
        </p>

        <!-- Buttons -->
        <div class="flex items-center justify-center lg:justify-start gap-4">
          <Link
            :href="route('login')"
            class="bg-blue-900 text-white px-7 py-3 rounded-full font-medium shadow-md 
                   hover:shadow-lg hover:scale-105 hover:bg-blue-800 transition-all duration-300"
          >
            Log In
          </Link>

          <Link
            :href="route('register')"
            class="border border-blue-900 text-blue-900 dark:text-white px-7 py-3 rounded-full 
                   font-medium hover:bg-blue-50 dark:hover:bg-blue-800/60 transition-all duration-300"
          >
            Sign Up
          </Link>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">
          <span class="font-semibold text-blue-900 dark:text-blue-300">Sign Up</span> to create a new account,
          or <span class="font-semibold text-blue-900 dark:text-blue-300">Log In</span> to continue.
        </p>
      </div>

      <!-- Image Section -->
      <div class="relative group">
        <div
          class="relative rounded-2xl overflow-hidden shadow-2xl transform transition-all duration-700 group-hover:scale-[1.03]"
        >
          <img
            src="/sacco.png"
            alt="SEPU SACCO illustration"
            class="w-full h-full object-cover rounded-2xl"
          />
          <div
            class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"
          ></div>
        </div>

        <!-- Subtle floating label -->
        <div
          class="absolute bottom-4 left-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md 
                 text-blue-900 dark:text-gray-100 px-4 py-2 rounded-lg shadow-lg text-sm 
                 font-medium tracking-wide"
        >
          #EmpoweringMembers
        </div>
      </div>
    </div>
  </div>
</template>
