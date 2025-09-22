<template>
  <AppLayout>
    <Head title="Complete Profile" />

    <!-- messages -->
    <div class="mx-[5%] sm:mx-[20%]">
      <div
        v-if="flash.success"
        class="bg-green-100 w-fit px-6 text-green-800 p-3 rounded mb-4 transition-opacity duration-500"
      >
        {{ flash.success }}
      </div>
      <div
        v-if="flash.error"
        class="bg-red-100 text-red-800 p-3 rounded mb-4 transition-opacity duration-500"
      >
        {{ flash.error }}
      </div>
    </div>

    <div class="py-16 mx-2 flex flex-col items-center text-center space-y-6">
      <!-- Icon -->
      <div class="bg-yellow-100 dark:bg-yellow-900 p-4 sm:p-6 rounded-full">
        <svg
          class="w-12 sm:w-16 h-12 sm:h-16 text-yellow-500 dark:text-yellow-300"
          fill="none"
          stroke="currentColor"
          stroke-width="1.5"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      </div>

      <!-- Title -->
      <h2
        class="max-sm:px-6 text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100"
      >
        Your Profile is Awaiting Activation
      </h2>

      <!-- Message -->
      <p
        class="max-sm:text-sm max-w-xl text-gray-600 dark:text-gray-400"
      >
        Thank you for completing your registration.  
        Admin is reviewing your details. Once your account is activated, you will be able to access all features.
      </p>

      <!-- Estimated time -->
      <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
        This process usually takes
        <span class="font-medium">24–48 hours</span>.
      </p>

      <!-- Action button -->
      <div class="pt-6">
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="px-5 py-2 hover:cursor-pointer rounded-xl bg-blue-800 text-white hover:bg-blue-700 
                dark:bg-gray-200 dark:text-gray-900 dark:hover:bg-gray-300 transition"
        >
          Logout
        </Link>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, usePage, Link } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const page = usePage()

// local flash state
const flash = ref({ ...page.props.flash })

// watch for changes to page.props.flash
watch(
  () => page.props.flash,
  (newFlash) => {
    flash.value = { ...newFlash }

    if (flash.value.success || flash.value.error) {
      setTimeout(() => {
        flash.value = {}
      }, 5000) // auto-hide after 5 seconds
    }
  },
  { immediate: true }
)
</script>
