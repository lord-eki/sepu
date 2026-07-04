<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'

const menuOpen = ref(false)
const page = usePage()

const isActive = (url: string) => page.url === url
</script>

<template>
  <div class="min-h-screen flex flex-col
    bg-[#f8fafc] dark:bg-[#020617]">

    <!-- NAVBAR -->
    <header class="fixed top-0 left-0 right-0 z-50
      border-b border-white/10
      bg-white/70 dark:bg-slate-950/70
      backdrop-blur-2xl">

      <div class="max-w-7xl mx-auto
        px-6 lg:px-8
        h-20
        flex items-center justify-between">

        <!-- Logo -->
        <Link href="/" class="flex items-center gap-4 group">

        <img src="/apple-touch-icon1.png" alt="SEPU Logo" class="w-12 h-12 rounded-2xl
            shadow-lg
            group-hover:scale-105
            transition duration-300" />

        <div class="leading-tight">

          <h1 class="text-lg md:text-xl
              font-black tracking-tight
              text-slate-900 dark:text-white">
            SEPU SACCO
          </h1>

          <p class="hidden sm:block
              text-xs text-slate-500 dark:text-slate-400">
            Smart Cooperative Banking
          </p>

        </div>
        </Link>

        <!-- Desktop Nav -->
        <nav class="hidden md:flex
          items-center gap-8">

          <Link href="/" :class="[
            isActive('/')
              ? 'text-blue-800 dark:text-blue-400'
              : 'text-slate-600 dark:text-slate-300',
            'font-medium hover:text-blue-900 transition'
          ]">
          Home
          </Link>

          <Link href="/about" :class="[
            isActive('/about')
              ? 'text-blue-800 dark:text-blue-400'
              : 'text-slate-600 dark:text-slate-300',
            'font-medium hover:text-blue-900 transition'
          ]">
          About
          </Link>

          <Link href="/terms" :class="[
            isActive('/terms')
              ? 'text-blue-800 dark:text-blue-400'
              : 'text-slate-600 dark:text-slate-300',
            'font-medium hover:text-blue-900 transition'
          ]">
          Terms
          </Link>

          <Link href="/contact" :class="[
            isActive('/contact')
              ? 'text-blue-800 dark:text-blue-400'
              : 'text-slate-600 dark:text-slate-300',
            'font-medium hover:text-blue-900 transition'
          ]">
          Contact
          </Link>

        </nav>

       <!-- Actions -->
      <div class="hidden md:flex items-center gap-4">

        <!-- Login (FIXED VISIBILITY) -->
        <Link
          href="/login"
          class="px-5 py-2.5 rounded-xl
          border border-slate-700
          text-blue-700 dark:text-blue-300
          font-semibold

          hover:bg-slate-700 hover:text-white
          hover:border-slate-800

          shadow-sm hover:shadow-md
          transition-all duration-300"
        >
          Login
        </Link>

        <!-- Primary CTA -->
        <Link
          href="/register"
          class="px-5 py-2.5 rounded-xl
          bg-gradient-to-r from-blue-700 to-blue-900
          hover:from-blue-800 hover:to-blue-950

          text-white font-semibold
          shadow-lg shadow-blue-500/25

          transition-all duration-300
          hover:-translate-y-0.5"
        >
          Sign Up
        </Link>

      </div>
        <!-- Mobile Toggle -->
        <button @click="menuOpen = !menuOpen" class="md:hidden p-2 rounded-xl
          hover:bg-slate-100 dark:hover:bg-slate-800
          transition">

          <svg v-if="!menuOpen" xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-800 dark:text-white"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>

          <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-800 dark:text-white" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>

        </button>

      </div>

      <!-- Mobile Menu -->
      <transition name="fade-slide">
        <div v-if="menuOpen" class="md:hidden px-6 pb-6">

          <div class="rounded-3xl
            border border-slate-200 dark:border-slate-800
            bg-white dark:bg-slate-900
            p-6 shadow-2xl">

            <nav class="flex flex-col gap-5">

              <Link href="/">Home</Link>
              <Link href="/about">About</Link>
              <Link href="/terms">Terms</Link>
              <Link href="/contact">Contact</Link>

            </nav>

            <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-800 flex flex-col gap-3">
            <!-- Login -->
            <Link href="/login"
              class="w-full py-3 rounded-xl text-center
              border border-blue-800
              text-blue-700 dark:text-blue-300
              font-semibold
              hover:bg-slate-600 hover:text-white
              transition"
            >
              Login
            </Link>

            <!-- Register -->
            <Link href="/register"
              class="w-full py-3 rounded-xl text-center
              bg-blue-800 hover:bg-blue-700
              text-white font-semibold
              shadow-md"
            >
              Sign Up
            </Link>

          </div>

          </div>

        </div>
      </transition>

    </header>

    <!-- CONTENT -->
    <main class="flex-1 pt-20">
      <slot />
    </main>

    <!-- FOOTER -->
    <footer class="border-t border-slate-200 dark:border-slate-800
      bg-white/70 dark:bg-slate-950/70
      backdrop-blur-xl">

      <div class="max-w-7xl mx-auto
        px-6 lg:px-8
        py-8
        flex flex-col md:flex-row
        items-center justify-between gap-5">

        <div>
          <h3 class="font-bold text-slate-900 dark:text-white">
            SEPU SACCO
          </h3>

          <p class="text-sm text-slate-500 dark:text-slate-400">
            © {{ new Date().getFullYear() }} All rights reserved.
          </p>
        </div>

        <div class="flex items-center gap-6
          text-sm text-slate-500 dark:text-slate-400">
          <Link href="/about" class="hover:text-blue-600 transition">
          About
          </Link>

          <Link href="/terms" class="hover:text-blue-600 transition">
          Terms
          </Link>

          <Link href="/contact" class="hover:text-blue-600 transition">
          Contact
          </Link>
        </div>

      </div>
    </footer>

  </div>
</template>

<style>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.25s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>