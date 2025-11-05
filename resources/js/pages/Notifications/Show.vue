<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  notification: {
    type: Object,
    required: true
  },
  role: {
    type: String,
    default: 'member' // or 'admin'
  }
})

const breadcrumbs = [
  { title: 'Notifications', href: route('notifications.index') },
  { title: 'View Notification' }
]

const form = useForm({})
const deleting = ref(false)
const markingRead = ref(false)

const markAsRead = () => {
  markingRead.value = true
  form.post(route('notifications.markAsRead', props.notification.id), {
    onFinish: () => (markingRead.value = false)
  })
}

const deleteNotification = () => {
  if (!confirm('Are you sure you want to delete this notification?')) return
  deleting.value = true
  form.delete(route('notifications.destroy', props.notification.id), {
    onFinish: () => (deleting.value = false)
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head :title="`Notification #${notification.id}`" />

    <div class="bg-white shadow rounded-2xl p-6 max-w-3xl mx-auto">
      <!-- Header -->
      <div class="flex justify-between items-start border-b border-gray-200 pb-4 mb-4">
        <div>
          <h1 class="text-2xl font-semibold text-gray-800">
            {{ notification.title }}
          </h1>
          <p class="text-sm text-gray-500 mt-1">
            Sent on {{ new Date(notification.created_at).toLocaleString() }}
          </p>
        </div>

        <div class="flex items-center gap-3">
          <span
            :class="[
              'px-2 py-1 text-xs rounded-full font-medium',
              notification.status === 'read'
                ? 'bg-green-100 text-green-700'
                : 'bg-yellow-100 text-yellow-700'
            ]"
          >
            {{ notification.status }}
          </span>
        </div>
      </div>

      <!-- Content -->
      <div class="prose max-w-none text-gray-700 leading-relaxed mb-6">
        <p>{{ notification.message }}</p>
      </div>

      <!-- Channels -->
      <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Delivery Channel:</h3>
        <p class="text-sm text-gray-600 capitalize">{{ notification.channel }}</p>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3">
        <Link
          :href="route('notifications.index')"
          class="text-blue-600 hover:text-blue-700 text-sm font-medium"
        >
          ← Back to Notifications
        </Link>

        <button
          v-if="notification.status !== 'read'"
          @click="markAsRead"
          :disabled="markingRead"
          class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-orange-600 transition disabled:opacity-50"
        >
          Mark as Read
        </button>

        <button
          @click="deleteNotification"
          :disabled="deleting"
          class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition disabled:opacity-50"
        >
          Delete
        </button>
      </div>
    </div>
  </AppLayout>
</template>
