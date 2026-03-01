<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Phone, Mail, MapPin, Clock } from 'lucide-vue-next'
</script>

<template>
  <PublicLayout>

    <Head title="Contact Us" />

    <main class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-blue-50">

      <!-- Soft Background Accents -->
      <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-blue-200 rounded-full blur-[120px] opacity-30">
      </div>
      <div class="absolute bottom-0 -left-40 w-[500px] h-[500px] bg-orange-200 rounded-full blur-[120px] opacity-30">
      </div>

      <div class="relative max-w-7xl mx-auto px-6 py-16 lg:py-24 space-y-20">

        <!-- Header -->
        <section class="text-center space-y-6 max-w-3xl mx-auto">
          <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900">
            Get in
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-orange-500">
              Touch
            </span>
          </h1>

          <p class="text-gray-600 sm:text-lg leading-relaxed">
            Have questions or need assistance? Our team is ready to support you.
            Reach out through any of the options below or send us a message directly.
          </p>
        </section>

        <!-- Contact Info -->
        <section class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">

          <div class="group rounded-2xl bg-white/80 backdrop-blur-lg
                     border border-gray-200 p-8 text-center
                     shadow-md hover:shadow-xl hover:-translate-y-2
                     transition-all duration-500">
            <Phone class="w-8 h-8 text-orange-500 mx-auto mb-4 group-hover:scale-110 transition" />
            <h3 class="font-semibold text-gray-900 text-lg">Call Us</h3>
            <p class="text-gray-600 text-sm mt-2">+254 712 345 678</p>
          </div>

          <div class="group rounded-2xl bg-white/80 backdrop-blur-lg
                     border border-gray-200 p-8 text-center
                     shadow-md hover:shadow-xl hover:-translate-y-2
                     transition-all duration-500">
            <Mail class="w-8 h-8 text-orange-500 mx-auto mb-4 group-hover:scale-110 transition" />
            <h3 class="font-semibold text-gray-900 text-lg">Email</h3>
            <a href="mailto:support@sepusacco.co.ke" class="text-blue-600 text-sm mt-2 block hover:underline">
              support@sepusacco.co.ke
            </a>
          </div>

          <div class="group rounded-2xl bg-white/80 backdrop-blur-lg
                     border border-gray-200 p-8 text-center
                     shadow-md hover:shadow-xl hover:-translate-y-2
                     transition-all duration-500">
            <MapPin class="w-8 h-8 text-orange-500 mx-auto mb-4 group-hover:scale-110 transition" />
            <h3 class="font-semibold text-gray-900 text-lg">Location</h3>
            <p class="text-gray-600 text-sm mt-2">
              SEPU SACCO HQ, Nairobi, Kenya
            </p>
          </div>

          <div class="group rounded-2xl bg-white/80 backdrop-blur-lg
                     border border-gray-200 p-8 text-center
                     shadow-md hover:shadow-xl hover:-translate-y-2
                     transition-all duration-500">
            <Clock class="w-8 h-8 text-orange-500 mx-auto mb-4 group-hover:scale-110 transition" />
            <h3 class="font-semibold text-gray-900 text-lg">Working Hours</h3>
            <p class="text-gray-600 text-sm mt-2">
              Mon – Fri: 8:00 AM – 5:00 PM
            </p>
          </div>

        </section>

        <!-- Contact Form -->
        <section class="rounded-3xl bg-white/80 backdrop-blur-xl
                        border border-gray-200 shadow-xl p-10">

          <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">
            Send Us a Message
          </h2>

          <form class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Full Name
              </label>
              <input type="text" placeholder="Your name" class="w-full border border-gray-300 rounded-xl px-4 py-3
                       focus:ring-2 focus:ring-orange-500 focus:border-orange-500
                       outline-none transition" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Email
              </label>
              <input type="email" placeholder="you@example.com" class="w-full border border-gray-300 rounded-xl px-4 py-3
                       focus:ring-2 focus:ring-orange-500 focus:border-orange-500
                       outline-none transition" />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Message
              </label>
              <textarea rows="5" placeholder="Write your message here..." class="w-full border border-gray-300 rounded-xl px-4 py-3
                       focus:ring-2 focus:ring-orange-500 focus:border-orange-500
                       outline-none transition"></textarea>
            </div>

            <div class="md:col-span-2 text-center pt-4">
              <button type="submit" class="px-10 py-3 rounded-xl font-semibold text-white
                       bg-gradient-to-r from-orange-500 to-orange-600
                       shadow-lg hover:shadow-xl
                       transition-all duration-300 hover:scale-105">
                Send Message
              </button>
            </div>

          </form>
        </section>

        <!-- Map -->
        <section class="rounded-3xl overflow-hidden shadow-2xl border border-gray-200">
          <iframe class="w-full h-96" frameborder="0" allowfullscreen
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.857688611686!2d36.7596649!3d-1.2996543!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1a68dc564e65%3A0x5b2831f1a654bf2b!2sSchool%20Equipment%20Production%20Unit!5e0!3m2!1sen!2ske!4v1730995200000!5m2!1sen!2ske">
          </iframe>
        </section>

      </div>
    </main>
  </PublicLayout>
</template>