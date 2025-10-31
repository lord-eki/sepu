<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

const menuOpen = ref(false)
</script>

<template>
  <div
    class="min-h-screen flex flex-col bg-gradient-to-br from-blue-50 via-white to-orange-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950">

    <!-- Navbar -->
    <header class="w-full py-4 px-6 bg-white/80 dark:bg-gray-900/70 backdrop-blur-md shadow-sm fixed top-0 left-0 z-50">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Link href="/">
            <img src="/apple-touch-icon1.png" alt="SEPU SACCO Logo" class="w-16 sm:w-20 rounded-sm" />
          </Link>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center gap-6 font-medium">
          <Link href="/" class="text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">Home</Link>
          <Link href="/about" class="text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">About Us</Link>
          <Link href="/terms" class="text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">Terms</Link>
          <Link href="/contact" class="text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">Contact</Link>
        </nav>

        <!-- Auth Buttons -->
        <div class="hidden md:flex items-center gap-4">
          <Link href="/login"
            class="px-5 py-2 rounded-full text-white bg-blue-900 hover:bg-blue-800 transition">Log In</Link>
          <Link href="/register"
            class="px-5 py-2 rounded-full border border-blue-900 text-blue-900 hover:bg-blue-50 dark:border-white dark:text-white dark:hover:bg-gray-800 transition">Sign
            Up</Link>
        </div>

        <!-- Mobile Button -->
        <button @click="menuOpen = !menuOpen" class="md:hidden text-blue-900 dark:text-gray-200 focus:outline-none">
          <svg v-if="!menuOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            class="w-8 h-8">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            class="w-8 h-8">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Mobile Menu -->
      <div v-if="menuOpen" class="md:hidden mt-3 bg-white dark:bg-gray-900 rounded-lg shadow-lg p-4 space-y-4">
        <Link href="/" class="block text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">Home</Link>
        <Link href="/about" class="block text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">About Us</Link>
        <Link href="/terms" class="block text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">Terms</Link>
        <Link href="/contact" class="block text-blue-900 dark:text-gray-200 hover:text-orange-500 transition">Contact</Link>

        <div class="flex flex-col gap-3 pt-3 border-t border-gray-200 dark:border-gray-700">
          <Link href="/login"
            class="px-5 py-2 rounded-full text-white bg-blue-900 hover:bg-blue-800 transition text-center">Log In</Link>
          <Link href="/register"
            class="px-5 py-2 rounded-full border border-blue-900 text-blue-900 hover:bg-blue-50 dark:border-white dark:text-white dark:hover:bg-gray-800 transition text-center">Sign
            Up</Link>
        </div>
      </div>
    </header>

    <!-- Page Content -->
    <main class="flex-grow pt-20 px-3 sm:px-6">
      <slot />
    </main>

    <!-- Footer -->
    <footer
      class="bg-white/80 dark:bg-gray-900/70 backdrop-blur-md py-6 text-center border-t border-gray-200 dark:border-gray-700 mt-10">
      <div
        class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-3 px-4 text-sm text-gray-600 dark:text-gray-400">
        <p>© {{ new Date().getFullYear() }} SEPU SACCO — All Rights Reserved.</p>
        <div class="flex gap-4">
          <Link href="/about" class="hover:text-orange-500">About</Link>
          <Link href="/terms" class="hover:text-orange-500">Terms</Link>
          <Link href="/contact" class="hover:text-orange-500">Contact</Link>
        </div>
      </div>
    </footer>
  </div>
</template>
