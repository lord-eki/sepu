<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Clock, Mail, MapPin, Phone } from 'lucide-vue-next';

const props = defineProps<{
    settings: Record<string, any>;
}>();

const s = props.settings;
</script>

<template>
    <PublicLayout>
        <Head title="Contact Us" />

        <main
            class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900"
        >
            <!-- Soft Background Accents -->
            <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-blue-200 opacity-30 blur-[120px] dark:bg-blue-800/30"></div>
            <div
                class="absolute bottom-0 -left-40 h-[500px] w-[500px] rounded-full bg-orange-200 opacity-30 blur-[120px] dark:bg-orange-700/30"
            ></div>

            <div class="relative mx-auto max-w-7xl space-y-20 px-6 py-16 lg:py-24">
                <!-- Header -->
                <section class="mx-auto max-w-3xl space-y-6 text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl dark:text-white">
                        Get in
                        <span
                            class="bg-gradient-to-r from-blue-600 to-orange-500 bg-clip-text text-transparent dark:from-blue-400 dark:to-orange-400"
                        >
                            Touch
                        </span>
                    </h1>

                    <p class="leading-relaxed text-gray-600 sm:text-lg dark:text-gray-300">
                        Have questions or need assistance? Our team is ready to support you. Reach out through any of the options below or send us a
                        message directly.
                    </p>
                </section>

                <!-- Contact Info -->
                <section class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        class="group rounded-2xl border border-gray-200 bg-white/80 p-8 text-center shadow-md backdrop-blur-lg transition-all duration-500 hover:-translate-y-2 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800/60"
                    >
                        <Phone class="mx-auto mb-4 h-8 w-8 text-orange-500 transition group-hover:scale-110" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Call Us</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ s.sacco_phone?.value }}</p>
                    </div>

                    <div
                        class="group rounded-2xl border border-gray-200 bg-white/80 p-8 text-center shadow-md backdrop-blur-lg transition-all duration-500 hover:-translate-y-2 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800/60"
                    >
                        <Mail class="mx-auto mb-4 h-8 w-8 text-orange-500 transition group-hover:scale-110" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Email</h3>
                        <a :href="`mailto:${s.sacco_email?.value}`" class="mt-2 block text-sm text-blue-600 hover:underline dark:text-blue-400">
                            {{ s.sacco_email?.value }}
                        </a>
                    </div>

                    <div
                        class="group rounded-2xl border border-gray-200 bg-white/80 p-8 text-center shadow-md backdrop-blur-lg transition-all duration-500 hover:-translate-y-2 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800/60"
                    >
                        <MapPin class="mx-auto mb-4 h-8 w-8 text-orange-500 transition group-hover:scale-110" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Location</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">SEPU SACCO HQ, Nairobi, Kenya</p>
                    </div>

                    <div
                        class="group rounded-2xl border border-gray-200 bg-white/80 p-8 text-center shadow-md backdrop-blur-lg transition-all duration-500 hover:-translate-y-2 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800/60"
                    >
                        <Clock class="mx-auto mb-4 h-8 w-8 text-orange-500 transition group-hover:scale-110" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Working Hours</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Mon – Fri: 8:00 AM – 5:00 PM</p>
                    </div>
                </section>

                <!-- Contact Form -->
                <section
                    class="rounded-3xl border border-gray-200 bg-white/80 p-10 shadow-xl backdrop-blur-xl dark:border-gray-700 dark:bg-gray-800/60"
                >
                    <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white">Send Us a Message</h2>

                    <form class="mx-auto grid max-w-4xl gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Full Name </label>
                            <input
                                type="text"
                                placeholder="Your name"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 transition outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Email </label>
                            <input
                                type="email"
                                placeholder="you@example.com"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 transition outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Message </label>
                            <textarea
                                rows="5"
                                placeholder="Write your message here..."
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 transition outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                            ></textarea>
                        </div>

                        <div class="pt-4 text-center md:col-span-2">
                            <button
                                type="submit"
                                class="rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-10 py-3 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl dark:from-orange-600 dark:to-orange-500"
                            >
                                Send Message
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Map -->
                <section class="overflow-hidden rounded-3xl border border-gray-200 shadow-2xl dark:border-gray-700">
                    <iframe
                        class="h-96 w-full"
                        frameborder="0"
                        allowfullscreen
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.857688611686!2d36.7596649!3d-1.2996543!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1a68dc564e65%3A0x5b2831f1a654bf2b!2sSchool%20Equipment%20Production%20Unit!5e0!3m2!1sen!2ske!4v1730995200000!5m2!1sen!2ske"
                    >
                    </iframe>
                </section>
            </div>
        </main>
    </PublicLayout>
</template>
