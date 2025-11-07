<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const form = useForm({
  email_notifications: true,
  sms_notifications: false,
  in_app_notifications: true,
  sender_email: 'noreply@sacco.com',
  sender_name: 'Umoja SACCO',
  sms_sender_id: 'UMOJA',
  loan_approval_template: 'Your loan request has been approved.',
  deposit_confirmation_template: 'Your deposit of {amount} has been received.',
})

function saveNotificationSettings() {
  form.post(route('settings.notification.update')) 
  
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'Notifications' }]">
    <Head title="Notification Settings" />

    <div class="bg-white rounded-2xl shadow-md p-6">
      <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">
        Notification Settings
      </h1>

      <form @submit.prevent="saveNotificationSettings" class="space-y-6">
        <!-- Email Settings -->
        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-3">Email Settings</h2>

          <div class="flex items-center gap-2 mb-3">
            <input id="email_notifications" type="checkbox" v-model="form.email_notifications" class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-400" />
            <label for="email_notifications" class="text-sm text-gray-700">Enable Email Notifications</label>
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Sender Email</label>
              <input
                v-model="form.sender_email"
                type="email"
                placeholder="e.g., noreply@sacco.com"
                class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 outline-none"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Sender Name</label>
              <input
                v-model="form.sender_name"
                type="text"
                placeholder="e.g., Umoja SACCO"
                class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 outline-none"
              />
            </div>
          </div>
        </div>

        <!-- SMS Settings -->
        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-3">SMS Settings</h2>

          <div class="flex items-center gap-2 mb-3">
            <input id="sms_notifications" type="checkbox" v-model="form.sms_notifications" class="w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-400" />
            <label for="sms_notifications" class="text-sm text-gray-700">Enable SMS Notifications</label>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">SMS Sender ID</label>
            <input
              v-model="form.sms_sender_id"
              type="text"
              placeholder="e.g., UMOJA"
              class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
            />
          </div>
        </div>

        <!-- In-App Notifications -->
        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-3">In-App Notifications</h2>
          <div class="flex items-center gap-2 mb-3">
            <input id="in_app_notifications" type="checkbox" v-model="form.in_app_notifications" class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-400" />
            <label for="in_app_notifications" class="text-sm text-gray-700">Enable In-App Notifications</label>
          </div>
        </div>

        <!-- Templates -->
        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-3">Message Templates</h2>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Loan Approval Template</label>
              <textarea
                v-model="form.loan_approval_template"
                rows="2"
                class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 outline-none"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Deposit Confirmation Template</label>
              <textarea
                v-model="form.deposit_confirmation_template"
                rows="2"
                class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 outline-none"
              ></textarea>
            </div>
          </div>
        </div>

        <div class="flex justify-end pt-4">
          <button
            type="submit"
            class="px-6 py-2 bg-gradient-to-r from-sky-600 to-orange-500 text-white rounded-lg shadow-md hover:opacity-90 transition"
          >
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
