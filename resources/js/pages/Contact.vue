<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Phone, Mail, MapPin, Clock } from 'lucide-vue-next'
</script>

<template>
  <PublicLayout>
    <Head title="Contact Us" />

    <div class="min-h-screen bg-gray-50 py-6 sm:py-12 px-3 sm:px-6 lg:px-12 space-y-12">
      
      <!-- Page Header -->
      <section class="text-center space-y-3">
        <h1 class="text-3xl sm:text-4xl font-bold text-blue-900">
          Get in Touch with Us
        </h1>
        <p class="text-gray-600 max-w-2xl mx-auto text-sm sm:text-base">
          Have questions, feedback, or need assistance? We’d love to hear from you.
          Reach out through any of the options below or send us a message directly.
        </p>
      </section>

      <!-- Contact Info Cards -->
      <section class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white shadow rounded-xl p-6 text-center border-t-4 border-orange-500">
          <Phone class="w-8 h-8 text-orange-600 mx-auto mb-3" />
          <h3 class="font-semibold text-blue-900 text-lg">Call Us</h3>
          <p class="text-gray-600 text-sm mt-1">+254 712 345 678</p>
        </div>

        <div class="bg-white shadow rounded-xl p-6 text-center border-t-4 border-orange-500">
          <Mail class="w-8 h-8 text-orange-600 mx-auto mb-3" />
          <h3 class="font-semibold text-blue-900 text-lg">Email</h3>
          <p class="text-gray-600 text-sm mt-1">
            <a href="mailto:support@sepusacco.co.ke" class="text-blue-700 underline">
              support@sepusacco.co.ke
            </a>
          </p>
        </div>

        <div class="bg-white shadow rounded-xl p-6 text-center border-t-4 border-orange-500">
          <MapPin class="w-8 h-8 text-orange-600 mx-auto mb-3" />
          <h3 class="font-semibold text-blue-900 text-lg">Our Location</h3>
          <p class="text-gray-600 text-sm mt-1">SEPU SACCO HQ, Nairobi, Kenya</p>
        </div>

        <div class="bg-white shadow rounded-xl p-6 text-center border-t-4 border-orange-500">
          <Clock class="w-8 h-8 text-orange-600 mx-auto mb-3" />
          <h3 class="font-semibold text-blue-900 text-lg">Working Hours</h3>
          <p class="text-gray-600 text-sm mt-1">Mon - Fri: 8:00 AM - 5:00 PM</p>
        </div>
      </section>

      <!-- Contact Form -->
      <section class="bg-white rounded-2xl shadow-lg p-8 border border-gray-200">
        <h2 class="text-2xl font-semibold text-blue-900 mb-6 text-center">Send Us a Message</h2>

        <form class="grid md:grid-cols-2 gap-6 max-w-3xl mx-auto">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
            <input
              type="text"
              placeholder="Your name"
              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input
              type="email"
              placeholder="you@example.com"
              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"
            />
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
            <textarea
              rows="5"
              placeholder="Write your message here..."
              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none"
            ></textarea>
          </div>

          <div class="md:col-span-2 text-center">
            <button
              type="submit"
              class="bg-orange-600 hover:bg-orange-700 text-white font-semibold px-8 py-3 rounded-lg shadow transition"
            >
              Send Message
            </button>
          </div>
        </form>
      </section>

      <!-- Map Section -->
      <section class="rounded-2xl overflow-hidden shadow-lg border border-gray-200">
        <iframe
          class="w-full h-80"
          frameborder="0"
          allowfullscreen
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1994.9720129857338!2d36.82194641591565!3d-1.2920651990605285!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f10f8164c37d9%3A0x6a2b05a9832f39d!2sNairobi%20City%2C%20Kenya!5e0!3m2!1sen!2ske!4v1697658322187"
        ></iframe>
      </section>

    </div>
  </PublicLayout>
</template>
