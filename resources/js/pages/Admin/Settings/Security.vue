<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const form = useForm({
  current_password: '',
  new_password: '',
  confirm_password: '',
  two_factor_enabled: false,
  session_timeout: 30, // in minutes
})

const passwordVisible = ref(false)

function saveSecuritySettings() {
    route('settings.security.update') 
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'Security' }]">
    <Head title="Security Settings" />

    <div class="bg-white rounded-2xl shadow-md p-6">
      <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">
        Security Settings
      </h1>

      <form @submit.prevent="saveSecuritySettings" class="space-y-6">
        <!-- Password Change Section -->
        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-3">Change Password</h2>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Current Password</label>
              <input
                v-model="form.current_password"
                type="password"
                placeholder="Enter current password"
                class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">New Password</label>
              <input
                v-model="form.new_password"
                :type="passwordVisible ? 'text' : 'password'"
                placeholder="Enter new password"
                class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
              <input
                v-model="form.confirm_password"
                :type="passwordVisible ? 'text' : 'password'"
                placeholder="Confirm new password"
                class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
              />
            </div>

            <div class="flex items-center gap-2">
              <input
                type="checkbox"
                id="show_password"
                v-model="passwordVisible"
                class="w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-400"
              />
              <label for="show_password" class="text-sm text-gray-700">Show Passwords</label>
            </div>
          </div>
        </div>

        <!-- Two-Factor Authentication -->
        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-3">Two-Factor Authentication (2FA)</h2>

          <div class="flex items-center gap-2">
            <input
              id="two_factor_enabled"
              type="checkbox"
              v-model="form.two_factor_enabled"
              class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-400"
            />
            <label for="two_factor_enabled" class="text-sm text-gray-700">
              Enable Two-Factor Authentication
            </label>
          </div>

          <p class="text-xs text-gray-500 mt-2">
            When enabled, users must verify their identity via a code sent to their email or phone upon login.
          </p>
        </div>

        <!-- Session Timeout -->
        <div>
          <h2 class="text-lg font-semibold text-gray-700 mb-3">Session Timeout</h2>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Auto Logout After (minutes)</label>
              <input
                v-model="form.session_timeout"
                type="number"
                min="5"
                placeholder="e.g., 30"
                class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-sky-400 focus:border-sky-400 outline-none"
              />
            </div>
          </div>

          <p class="text-xs text-gray-500 mt-2">
            Users will be automatically logged out after inactivity for the specified time.
          </p>
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
