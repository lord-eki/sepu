<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/layouts/PublicLayout.vue'

const menuOpen = ref(false)
</script>

<template>
  <PublicLayout>
    <Head title="Welcome" />

    <!-- Modern Light Hero -->
    <main class="relative flex-grow overflow-hidden bg-gradient-to-br from-slate-50 via-white to-blue-50">

      <!-- Soft Background Accent -->
      <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-blue-200 rounded-full blur-[120px] opacity-40"></div>
      <div class="absolute bottom-0 -left-40 w-[500px] h-[500px] bg-orange-200 rounded-full blur-[120px] opacity-40"></div>

      <div class="relative max-w-7xl mx-auto px-6 py-10 lg:py-20 grid lg:grid-cols-2 gap-16 items-center">

        <!-- LEFT CONTENT -->
        <div class="space-y-8">

          <!-- Badge -->
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full 
                      bg-white/20 shadow-sm text-xs sm:text-sm text-gray-600">
            SEPU SACCO<span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
            Trusted & Secure Platform
          </div>

          <!-- Heading -->
          <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight text-gray-900">
            Smart Banking for
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-orange-500">
              Modern Members
            </span>
          </h1>

          <!-- Description -->
          <p class="text-lg text-gray-600 leading-relaxed max-w-xl">
            SEPU SACCO empowers you with innovative savings solutions,
            affordable loans, and a transparent financial ecosystem
            designed for sustainable growth.
          </p>

          <!-- Buttons -->
          <div class="flex flex-wrap gap-5 pt-4">

            <Link
              :href="route('login')"
              class="px-8 py-3 rounded-xl font-semibold text-white
                     bg-blue-900 shadow-lg shadow-blue-200
                     hover:bg-blue-800 hover:shadow-blue-300
                     transition-all duration-300 hover:scale-105"
            >
              Log In
            </Link>

            <Link
              :href="route('register')"
              class="px-8 py-3 rounded-xl font-semibold
                     border border-gray-300 bg-white
                     hover:bg-gray-50 hover:border-gray-400
                     transition-all duration-300"
            >
              Register
            </Link>

          </div>

          <!-- Stats -->
          <div class="flex gap-10 pt-10 text-sm">
            <div>
              <p class="text-2xl font-bold text-gray-900">100+</p>
              <p class="text-gray-500">Active Members</p>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">KES 500K+</p>
              <p class="text-gray-500">Loans Issued</p>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">10+ Years</p>
              <p class="text-gray-500">Trusted Service</p>
            </div>
          </div>

        </div>

        <!-- RIGHT SIDE -->
        <div class="relative">

          <!-- Glass Card -->
          <div class="relative p-6 rounded-3xl bg-white/70 backdrop-blur-xl
                      border border-gray-200 shadow-2xl
                      hover:shadow-3xl transition-all duration-700">

            <img
              src="/sacco.png"
              alt="SEPU SACCO platform"
              class="rounded-2xl w-full object-cover"
            />

            <!-- Floating Tag -->
            <div class="absolute -bottom-6 left-8 px-6 py-3 rounded-xl
                        bg-gradient-to-r from-orange-500 to-orange-400
                        text-white text-sm font-semibold shadow-lg">
              #EmpoweringMembers
            </div>

          </div>

          <!-- Decorative Accent Card -->
          <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-100 rounded-3xl blur-2xl opacity-70"></div>

        </div>

      </div>
    </main>
  </PublicLayout>
</template>