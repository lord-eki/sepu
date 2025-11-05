<script setup lang="ts">
import { reactive } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

// Reactive form state
const form = useForm({
  name: '',
  email: '',
  phone: '',
  address: '',
  currency: 'KES',
})

// Simulated save action (connect to backend later)
function saveSettings() {
  form.post(route('settings.general.update'))
  
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'General' }]">
    <Head title="General Settings" />

    <div class="bg-white rounded-2xl shadow-md p-6">
      <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-6 border-b pb-3">
        General Settings
      </h1>

      <form @submit.prevent="saveSettings" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700">Organization Name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="e.g., Umoja SACCO"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Email Address</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="e.g., info@umojasacco.com"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Phone Number</label>
          <input
            v-model="form.phone"
            type="text"
            placeholder="e.g., +254 700 123 456"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Physical Address</label>
          <textarea
            v-model="form.address"
            rows="3"
            placeholder="e.g., Nairobi CBD, Kenya"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
          ></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Default Currency</label>
          <select
            v-model="form.currency"
            class="w-full mt-1 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 outline-none"
          >
            <option value="KES">KES (Kenyan Shilling)</option>
            <option value="USD">USD (US Dollar)</option>
            <option value="EUR">EUR (Euro)</option>
          </select>
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
