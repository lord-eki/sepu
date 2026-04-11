<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

const menuOpen = ref(false)
const page = usePage()

const isActive = (url: string) => page.url === url
</script>

<template>
  <div class="min-h-screen flex flex-col 
           bg-gradient-to-br from-slate-50 via-white to-orange-50
           dark:from-gray-950 dark:via-gray-900 dark:to-black">

    <!-- Navbar -->
    <header class="fixed top-0 left-0 w-full z-50 backdrop-blur-xl
             bg-white/20 dark:bg-gray-900/60 border-b border-white/20 dark:border-gray-800">
      <div class="max-w-7xl mx-auto flex items-center justify-between py-3 px-4 sm:px-6">

        <!-- Logo -->
        <Link href="/" class="flex items-center gap-3 group select-none">

          <!-- Logo Image -->
          <img src="/apple-touch-icon1.png" alt="SEPU Logo"
            class="w-11 sm:w-13 md:w-14 rounded-xl shadow-sm 
                  group-hover:scale-105 transition duration-300" />

          <!-- Brand Text -->
          <div class="leading-tight">

            <!-- Small Devices -->
            <span class="block sm:hidden text-2xl font-extrabold tracking-wide
                        text-gray-900 dark:text-white">
              SEPU <span class="text-orange-500">-SACCO</span>
            </span>

            <!-- Medium & Large Devices -->
            <div class="hidden sm:block">
              <span class="block text-lg md:text-xl font-extrabold tracking-tight
                          text-gray-900 dark:text-white">
                School Equipment Production Unit
              </span>

              <span class="block text-xs md:text-sm
                          text-orange-600">
                Savings and Credit Co-operative Organization
              </span>
            </div>

          </div>
        </Link>

        <!-- Desktop Menu -->
        <nav class="hidden md:flex items-center gap-8 text-sm sm:text-base font-medium">
          <Link href="/" :class="[
            isActive('/') ? 'text-orange-500' : 'text-gray-700 dark:text-gray-300',
            'hover:text-orange-500 transition'
          ]">Home</Link>
          <Link href="/about" :class="[
            isActive('/about') ? 'text-orange-500' : 'text-gray-700 dark:text-gray-300',
            'hover:text-orange-500 transition'
          ]">About</Link>
          <Link href="/terms" :class="[
            isActive('/terms') ? 'text-orange-500' : 'text-gray-700 dark:text-gray-300',
            'hover:text-orange-500 transition'
          ]">Terms</Link>
          <Link href="/contact" :class="[
            isActive('/contact') ? 'text-orange-500' : 'text-gray-700 dark:text-gray-300',
            'hover:text-orange-500 transition'
          ]">Contact</Link>
        </nav>

        <!-- Auth Buttons -->
        <div class="hidden md:flex items-center gap-3">
          <Link href="/login" class="px-5 py-2 rounded-full 
                   bg-gradient-to-r from-blue-900 to-blue-700 text-white shadow-md 
                   hover:shadow-lg hover:scale-105 transition duration-300
                   dark:from-blue-700 dark:to-blue-600 dark:hover:from-blue-600 dark:hover:to-blue-500">
          Log In
          </Link>

          <Link href="/register" class="px-5 py-2 rounded-full border border-gray-300 dark:border-gray-600
                   text-gray-800 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-800 
                   transition duration-300">
          Sign Up
          </Link>
        </div>

        <!-- Mobile Toggle -->
        <button @click="menuOpen = !menuOpen"
          class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
          <svg v-if="!menuOpen" xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-800 dark:text-white"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-gray-800 dark:text-white" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Mobile Menu -->
      <transition name="slide-fade">
        <div v-if="menuOpen" class="md:hidden px-4 pb-4">
          <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-5 space-y-4">
            <Link href="/" class="block hover:text-orange-500 dark:hover:text-orange-400 transition">Home</Link>
            <Link href="/about" class="block hover:text-orange-500 dark:hover:text-orange-400 transition">About</Link>
            <Link href="/terms" class="block hover:text-orange-500 dark:hover:text-orange-400 transition">Terms</Link>
            <Link href="/contact" class="block hover:text-orange-500 dark:hover:text-orange-400 transition">Contact
            </Link>

            <div class="pt-4 border-t dark:border-gray-700 space-y-3">
              <Link href="/login" class="block text-center py-2 rounded-full 
                       bg-blue-900 dark:bg-blue-700 text-white hover:bg-blue-800 dark:hover:bg-blue-600 transition">
              Log In
              </Link>
              <Link href="/register" class="block text-center py-2 rounded-full border dark:border-gray-600 
                       hover:bg-gray-100 dark:hover:bg-gray-800 transition">
              Sign Up
              </Link>
            </div>
          </div>
        </div>
      </transition>
    </header>

    <!-- Page Content -->
    <main class="flex-grow pt-20">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="dark:border-gray-800 bg-white/50 dark:bg-gray-900/60 backdrop-blur-xl">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm
               text-gray-600 dark:text-gray-400">
        <p>© {{ new Date().getFullYear() }} SEPU SACCO. All rights reserved.</p>

        <div class="flex gap-6">
          <Link href="/about" class="hover:text-orange-500 dark:hover:text-orange-400 transition">About</Link>
          <Link href="/terms" class="hover:text-orange-500 dark:hover:text-orange-400 transition">Terms</Link>
          <Link href="/contact" class="hover:text-orange-500 dark:hover:text-orange-400 transition">Contact</Link>
        </div>
      </div>
    </footer>
  </div>
</template>

<style>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>