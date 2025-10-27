<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Welcome" />

  <div class="min-h-screen flex flex-col bg-gradient-to-br from-blue-50 via-white to-orange-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950">
    
    <!-- Navbar -->
    <header class="w-full py-4 px-6 bg-white/80 dark:bg-gray-900/70 backdrop-blur-md shadow-sm fixed top-0 left-0 z-50">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center gap-3">
          <img src="/apple-touch-icon1.png" alt="SEPU SACCO Logo" class="w-32 rounded-sm" />
        </div>

        <!-- Navigation Links -->
        <nav class="hidden md:flex items-center gap-6 font-medium">
          <Link  class="text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">Home</Link>
          <Link :href="route('about')" class="text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">About Us</Link>
          <Link :href="route('terms')" class="text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">Terms</Link>
          <Link  class="text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">Contact</Link>
        </nav>

        <!-- Auth Buttons -->
        <div class="hidden md:flex items-center gap-4">
          <Link
            :href="route('login')"
            class="px-5 py-2 rounded-full text-white bg-blue-900 hover:bg-blue-800 transition"
          >
            Log In
          </Link>
          <Link
            :href="route('register')"
            class="px-5 py-2 rounded-full border border-blue-900 text-blue-900 hover:bg-blue-50 dark:border-white dark:text-white dark:hover:bg-gray-800 transition"
          >
            Sign Up
          </Link>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-grow flex items-center justify-center px-6 py-24 mt-20">
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
        </div>

        <!-- Hero Image -->
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

          <!-- Floating Label -->
          <div
            class="absolute bottom-4 left-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md 
                   text-blue-900 dark:text-gray-100 px-4 py-2 rounded-lg shadow-lg text-sm 
                   font-medium tracking-wide"
          >
            #EmpoweringMembers
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white/80 dark:bg-gray-900/70 backdrop-blur-md py-6 text-center border-t border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3 px-4 text-sm text-gray-600 dark:text-gray-400">
        <p>© {{ new Date().getFullYear() }} SEPU SACCO — All Rights Reserved.</p>
        <div class="flex gap-4">
          <Link :href="route('about')" class="hover:text-orange-500">About</Link>
          <Link :href="route('terms')" class="hover:text-orange-500">Terms</Link>
          <Link  class="hover:text-orange-500">Contact</Link>
        </div>
      </div>
    </footer>

  </div>
</template>
