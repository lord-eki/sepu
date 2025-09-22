<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>

  <Head title="Welcome" />

  <div class="min-h-screen bg-white dark:bg-gray-900 flex items-center justify-center px-4 py-10">
    <div class="max-w-6xl w-full grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
      <!-- Text Section -->
      <div class="space-y-6 text-center lg:text-left">
        <img src="/apple-touch-icon1.png" alt="Logo" class="w-20 md:w-30" />
        <h1 class="text-2xl sm:text-3xl font-extrabold text-blue-900 dark:text-white">
          Welcome <span class="">to </span> <span class="text-yellow-500">SEPU SACCO </span>
        </h1>
        <p class="text-gray-600 dark:text-gray-300 text-base sm:text-lg leading-relaxed">
          Your trusted partner in saving, borrowing, and growing together. <br />
          Join a strong, reliable community where your financial goals are prioritized.
          Whether you're looking to build savings, apply for a loan, or earn dividends, SEPU SACCO is here to support
          your journey.
        </p>

        <div class="flex sm:flex-row items-center justify-center lg:justify-start gap-4">
          <Link :href="route('login')"
            class="bg-blue-600 text-white px-5 py-2 sm:px-6 sm:py-3 text-sm sm:text-base rounded-lg hover:bg-blue-700 transition">
          Log In
          </Link>
          <Link :href="route('register')"
            class="border border-blue-600 text-blue-600 px-5 py-2 sm:px-6 sm:py-3 text-sm sm:text-base rounded-lg hover:bg-blue-50 dark:hover:bg-blue-800 dark:hover:text-white transition">
          Sign Up
          </Link>
        </div>

        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2">
          Click sign up to create an account or choose login to continue to your account
        </p>
      </div>

      <!-- Image Section -->
      <div class="w-full">
        <img src="/sacco.png" alt="SEPU SACCO illustration" class="w-full rounded-xl shadow-lg" />
      </div>
    </div>
  </div>
</template>