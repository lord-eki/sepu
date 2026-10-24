<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email_notifications: true,
    sms_notifications: false,
    in_app_notifications: true,
    sender_email: 'noreply@sacco.com',
    sender_name: 'Umoja SACCO',
    sms_sender_id: 'UMOJA',
    loan_approval_template: 'Your loan request has been approved.',
    deposit_confirmation_template: 'Your deposit of {amount} has been received.',
});

function saveNotificationSettings() {
    form.post(route('settings.notification.update'));
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'Notifications' }]">
        <Head title="Notification Settings" />

        <div class="rounded-2xl bg-white p-6 shadow-md">
            <h1 class="mb-6 border-b pb-3 text-xl font-semibold text-gray-800 sm:text-2xl">Notification Settings</h1>

            <form @submit.prevent="saveNotificationSettings" class="space-y-6">
                <!-- Email Settings -->
                <div>
                    <h2 class="mb-3 text-lg font-semibold text-gray-700">Email Settings</h2>

                    <div class="mb-3 flex items-center gap-2">
                        <input
                            id="email_notifications"
                            type="checkbox"
                            v-model="form.email_notifications"
                            class="h-4 w-4 rounded border-gray-300 text-sky-600 focus:ring-sky-400"
                        />
                        <label for="email_notifications" class="text-sm text-gray-700">Enable Email Notifications</label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sender Email</label>
                            <input
                                v-model="form.sender_email"
                                type="email"
                                placeholder="e.g., noreply@sacco.com"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-400"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sender Name</label>
                            <input
                                v-model="form.sender_name"
                                type="text"
                                placeholder="e.g., Umoja SACCO"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-400"
                            />
                        </div>
                    </div>
                </div>

                <!-- SMS Settings -->
                <div>
                    <h2 class="mb-3 text-lg font-semibold text-gray-700">SMS Settings</h2>

                    <div class="mb-3 flex items-center gap-2">
                        <input
                            id="sms_notifications"
                            type="checkbox"
                            v-model="form.sms_notifications"
                            class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400"
                        />
                        <label for="sms_notifications" class="text-sm text-gray-700">Enable SMS Notifications</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">SMS Sender ID</label>
                        <input
                            v-model="form.sms_sender_id"
                            type="text"
                            placeholder="e.g., UMOJA"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400"
                        />
                    </div>
                </div>

                <!-- In-App Notifications -->
                <div>
                    <h2 class="mb-3 text-lg font-semibold text-gray-700">In-App Notifications</h2>
                    <div class="mb-3 flex items-center gap-2">
                        <input
                            id="in_app_notifications"
                            type="checkbox"
                            v-model="form.in_app_notifications"
                            class="h-4 w-4 rounded border-gray-300 text-sky-600 focus:ring-sky-400"
                        />
                        <label for="in_app_notifications" class="text-sm text-gray-700">Enable In-App Notifications</label>
                    </div>
                </div>

                <!-- Templates -->
                <div>
                    <h2 class="mb-3 text-lg font-semibold text-gray-700">Message Templates</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Loan Approval Template</label>
                            <textarea
                                v-model="form.loan_approval_template"
                                rows="2"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-400"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deposit Confirmation Template</label>
                            <textarea
                                v-model="form.deposit_confirmation_template"
                                rows="2"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-400"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        class="rounded-lg bg-gradient-to-r from-sky-600 to-orange-500 px-6 py-2 text-white shadow-md transition hover:opacity-90"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
