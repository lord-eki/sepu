<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    notification: {
        type: Object,
        required: true,
    },
    role: {
        type: String,
        default: 'member', // or 'admin'
    },
});

const breadcrumbs = [{ title: 'Notifications', href: route('notifications.index') }, { title: 'View Notification' }];

const form = useForm({});
const deleting = ref(false);
const markingRead = ref(false);

const markAsRead = () => {
    markingRead.value = true;
    form.post(route('notifications.markAsRead', props.notification.id), {
        onFinish: () => (markingRead.value = false),
    });
};

const deleteNotification = () => {
    if (!confirm('Are you sure you want to delete this notification?')) return;
    deleting.value = true;
    form.delete(route('notifications.destroy', props.notification.id), {
        onFinish: () => (deleting.value = false),
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Notification #${notification.id}`" />

        <div class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow">
            <!-- Header -->
            <div class="mb-4 flex items-start justify-between border-b border-gray-200 pb-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">
                        {{ notification.title }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">Sent on {{ new Date(notification.created_at).toLocaleString() }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <span
                        :class="[
                            'rounded-full px-2 py-1 text-xs font-medium',
                            notification.status === 'read' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700',
                        ]"
                    >
                        {{ notification.status }}
                    </span>
                </div>
            </div>

            <!-- Content -->
            <div class="prose mb-6 max-w-none leading-relaxed text-gray-700">
                <p>{{ notification.message }}</p>
            </div>

            <!-- Channels -->
            <div class="mb-6">
                <h3 class="mb-2 text-sm font-semibold text-gray-700">Delivery Channel:</h3>
                <p class="text-sm text-gray-600 capitalize">{{ notification.channel }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <Link :href="route('notifications.index')" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                    ← Back to Notifications
                </Link>

                <button
                    v-if="notification.status !== 'read'"
                    @click="markAsRead"
                    :disabled="markingRead"
                    class="rounded-lg bg-orange-500 px-4 py-2 text-sm text-white transition hover:bg-orange-600 disabled:opacity-50"
                >
                    Mark as Read
                </button>

                <button
                    @click="deleteNotification"
                    :disabled="deleting"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm text-white transition hover:bg-red-700 disabled:opacity-50"
                >
                    Delete
                </button>
            </div>
        </div>
    </AppLayout>
</template>
