<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Welcome" />

  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-orange-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 
              flex items-center justify-center px-6 py-16">
    <div class="max-w-7xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
      
      <!-- Text Section -->
      <div class="space-y-8 text-center lg:text-left">
        <img src="/apple-touch-icon1.png" alt="Logo" class="w-20 md:w-28 mx-auto lg:mx-0" />
        
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-900 dark:text-white">
          Welcome <span class="">to</span> 
          <span class="text-orange-400"> SEPU SACCO</span>
        </h1>

        <p class="text-gray-600 dark:text-gray-300 text-base sm:text-lg leading-relaxed max-w-xl mx-auto lg:mx-0">
          Your trusted partner in saving, borrowing, and growing together.  
          Join a strong, reliable community where your financial goals are prioritized.
          Whether you're building savings, applying for a loan, or earning dividends, 
          SEPU SACCO is here to support your journey.
        </p>

        <div class="flex items-center justify-center lg:justify-start gap-4">
          <Link :href="route('login')"
            class="bg-blue-900 text-white px-6 py-3 rounded-full shadow hover:scale-105 hover:bg-blue-800 transition-transform">
            Log In
          </Link>
          <Link :href="route('register')"
            class="border border-blue-900 text-blue-900 dark:text-white px-6 py-3 rounded-full hover:bg-blue-50 dark:hover:bg-blue-800 transition">
            Sign Up
          </Link>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">
          Click <span class="font-semibold text-blue-900">Sign Up</span> to create an account, 
          or choose <span class="font-semibold text-blue-900">Log In</span> to continue.
        </p>
      </div>

      <!-- Image Section -->
      <div class="w-full">
        <div class="relative rounded-2xl shadow-xl overflow-hidden">
          <img src="/sacco.png" alt="SEPU SACCO illustration" 
               class="w-full object-cover rounded-2xl transform hover:scale-105 transition duration-700" />
          <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent rounded-2xl"></div>
        </div>
      </div>
    </div>
  </div>
</template>
