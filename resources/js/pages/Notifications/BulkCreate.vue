<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

const breadcrumbs = [
  { title: 'Notifications', href: route('notifications.index') },
  { title: 'Bulk Notification' }
]

const channels = ['Email', 'SMS', 'System']
const recipientGroups = ['All Members', 'Active Members', 'Loan Defaulters', 'Administrators']

const form = useForm({
  title: '',
  message: '',
  channel: 'Email',
  recipients: [],
})

const sendNotification = () => {
  if (!form.title || !form.message) {
    alert('Please fill in both title and message before sending.')
    return
  }

  form.post(route('notifications.bulk.send'), {
    onSuccess: () => {
      alert('Bulk notification sent successfully!')
      form.reset()
    },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Bulk Notification" />

    <div class="bg-white shadow rounded-2xl p-6 max-w-4xl mx-auto">
      <h1 class="text-2xl font-semibold text-gray-800 mb-6">
        Send Bulk Notification
      </h1>

      <form @submit.prevent="sendNotification" class="space-y-6">
        <!-- Title -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
          <input
            type="text"
            v-model="form.title"
            placeholder="Enter notification title"
            class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
          />
        </div>

        <!-- Message -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
          <textarea
            rows="5"
            v-model="form.message"
            placeholder="Write your message here..."
            class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
          ></textarea>
        </div>

        <!-- Channel -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Channel</label>
          <select
            v-model="form.channel"
            class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
          >
            <option v-for="ch in channels" :key="ch" :value="ch">{{ ch }}</option>
          </select>
        </div>

        <!-- Recipient Groups -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Recipients</label>
          <div class="grid sm:grid-cols-2 gap-2">
            <label
              v-for="group in recipientGroups"
              :key="group"
              class="flex items-center gap-2 border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50"
            >
              <input
                type="checkbox"
                :value="group"
                v-model="form.recipients"
                class="text-blue-600 focus:ring-orange-500 rounded"
              />
              <span class="text-gray-700 text-sm">{{ group }}</span>
            </label>
          </div>
        </div>

        <!-- Submit -->
        <div class="pt-4 flex justify-end">
          <button
            type="submit"
            :disabled="form.processing"
            class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50"
          >
            Send Notification
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
