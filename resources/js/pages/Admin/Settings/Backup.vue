<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@layouts/AppLayout.vue'

const breadcrumbs = [{ title: 'Settings' }, { title: 'Backup Settings' }]

// Backup form state
const form = useForm({
  auto_backup: true,
  backup_frequency: 'Daily',
  last_backup_date: '2025-11-04 10:00 AM'
})

const frequencies = ['Daily', 'Weekly', 'Monthly']

const createBackup = () => {
  alert('Backup started successfully (dummy action)')
  // form.post(route('settings.backup.create'))
}

const saveSettings = () => {
  form.post(route('settings.backup.update'))
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'Backup' }]">
    <Head title="Backup Settings" />

    <div class="bg-white shadow rounded-2xl p-6 max-w-3xl mx-auto">
      <h1 class="text-2xl font-semibold text-gray-800 mb-6">
        Backup Settings
      </h1>

      <div class="space-y-6">
        <!-- Last Backup Info -->
        <div class="border border-gray-200 rounded-xl p-4 bg-gray-50">
          <h2 class="text-lg font-medium text-gray-700 mb-2">Last Backup</h2>
          <p class="text-sm text-gray-600">
            Last backup completed on
            <span class="font-semibold text-blue-700">
              {{ form.last_backup_date }}
            </span>
          </p>
          <div class="mt-3">
            <button
              type="button"
              @click="createBackup"
              class="bg-orange-500 text-white px-5 py-2 rounded-lg hover:bg-orange-600 transition"
            >
              Create New Backup
            </button>
          </div>
        </div>

        <!-- Automatic Backup Settings -->
        <form @submit.prevent="saveSettings" class="space-y-4">
          <div class="flex items-center justify-between">
            <label class="text-sm font-medium text-gray-700">Enable Automatic Backup</label>
            <input
              v-model="form.auto_backup"
              type="checkbox"
              class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-orange-500"
            />
          </div>

          <div v-if="form.auto_backup">
            <label class="block text-sm font-medium text-gray-700 mb-1">Backup Frequency</label>
            <select
              v-model="form.backup_frequency"
              class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
            >
              <option v-for="option in frequencies" :key="option" :value="option">
                {{ option }}
              </option>
            </select>
          </div>

          <div class="pt-4">
            <button
              type="submit"
              :disabled="form.processing"
              class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition"
            >
              Save Settings
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
