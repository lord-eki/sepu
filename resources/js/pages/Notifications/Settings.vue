<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs = [{ title: 'Notifications', href: route('notifications.index') }, { title: 'Settings' }];

// Default user settings (can be fetched dynamically)
const form = useForm({
    email_notifications: true,
    sms_notifications: false,
    system_notifications: true,
});

const saveSettings = () => {
    form.post(route('notifications.settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Notification Settings" />

        <div class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow">
            <h1 class="mb-6 text-2xl font-semibold text-gray-800">Notification Preferences</h1>

            <form @submit.prevent="saveSettings" class="space-y-6">
                <!-- Email Notifications -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="font-medium text-gray-700">Email Notifications</h2>
                        <p class="text-sm text-gray-500">Receive alerts and updates via your registered email.</p>
                    </div>
                    <input
                        type="checkbox"
                        v-model="form.email_notifications"
                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-orange-500"
                    />
                </div>

                <!-- SMS Notifications -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="font-medium text-gray-700">SMS Notifications</h2>
                        <p class="text-sm text-gray-500">Get instant text message updates on your phone.</p>
                    </div>
                    <input
                        type="checkbox"
                        v-model="form.sms_notifications"
                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-orange-500"
                    />
                </div>

                <!-- System Alerts -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="font-medium text-gray-700">System Alerts</h2>
                        <p class="text-sm text-gray-500">Show notifications directly inside the SACCO system dashboard.</p>
                    </div>
                    <input
                        type="checkbox"
                        v-model="form.system_notifications"
                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-orange-500"
                    />
                </div>

                <!-- Save Button -->
                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-6 py-2 font-medium text-white transition hover:bg-blue-700 disabled:opacity-50"
                    >
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
