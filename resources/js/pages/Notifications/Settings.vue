<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const breadcrumbs = [
  { title: 'Notifications', href: route('notifications.index') },
  { title: 'Settings' }
]

// Default user settings (can be fetched dynamically)
const form = useForm({
  email_notifications: true,
  sms_notifications: false,
  system_notifications: true
})

const saveSettings = () => {
  form.post(route('notifications.settings.update'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Notification Settings" />

    <div class="bg-white shadow rounded-2xl p-6 max-w-3xl mx-auto">
      <h1 class="text-2xl font-semibold text-gray-800 mb-6">
        Notification Preferences
      </h1>

      <form @submit.prevent="saveSettings" class="space-y-6">
        <!-- Email Notifications -->
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
          <div>
            <h2 class="text-gray-700 font-medium">Email Notifications</h2>
            <p class="text-sm text-gray-500">
              Receive alerts and updates via your registered email.
            </p>
          </div>
          <input
            type="checkbox"
            v-model="form.email_notifications"
            class="h-5 w-5 text-blue-600 rounded focus:ring-orange-500 border-gray-300"
          />
        </div>

        <!-- SMS Notifications -->
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
          <div>
            <h2 class="text-gray-700 font-medium">SMS Notifications</h2>
            <p class="text-sm text-gray-500">
              Get instant text message updates on your phone.
            </p>
          </div>
          <input
            type="checkbox"
            v-model="form.sms_notifications"
            class="h-5 w-5 text-blue-600 rounded focus:ring-orange-500 border-gray-300"
          />
        </div>

        <!-- System Alerts -->
        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
          <div>
            <h2 class="text-gray-700 font-medium">System Alerts</h2>
            <p class="text-sm text-gray-500">
              Show notifications directly inside the SACCO system dashboard.
            </p>
          </div>
          <input
            type="checkbox"
            v-model="form.system_notifications"
            class="h-5 w-5 text-blue-600 rounded focus:ring-orange-500 border-gray-300"
          />
        </div>

        <!-- Save Button -->
        <div class="pt-4 flex justify-end">
          <button
            type="submit"
            :disabled="form.processing"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50"
          >
            Save Preferences
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
