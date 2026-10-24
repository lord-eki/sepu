<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs = [{ title: 'Notifications', href: route('notifications.index') }, { title: 'Bulk Notification' }];

const channels = ['Email', 'SMS', 'System'];
const recipientGroups = ['All Members', 'Active Members', 'Loan Defaulters', 'Administrators'];

const form = useForm({
    title: '',
    message: '',
    channel: 'Email',
    recipients: [],
});

const sendNotification = () => {
    if (!form.title || !form.message) {
        alert('Please fill in both title and message before sending.');
        return;
    }

    form.post(route('notifications.bulk.send'), {
        onSuccess: () => {
            alert('Bulk notification sent successfully!');
            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Bulk Notification" />

        <div class="mx-auto max-w-4xl rounded-2xl bg-white p-6 shadow">
            <h1 class="mb-6 text-2xl font-semibold text-gray-800">Send Bulk Notification</h1>

            <form @submit.prevent="sendNotification" class="space-y-6">
                <!-- Title -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Title</label>
                    <input
                        type="text"
                        v-model="form.title"
                        placeholder="Enter notification title"
                        class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                    />
                </div>

                <!-- Message -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Message</label>
                    <textarea
                        rows="5"
                        v-model="form.message"
                        placeholder="Write your message here..."
                        class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                    ></textarea>
                </div>

                <!-- Channel -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Delivery Channel</label>
                    <select v-model="form.channel" class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                        <option v-for="ch in channels" :key="ch" :value="ch">{{ ch }}</option>
                    </select>
                </div>

                <!-- Recipient Groups -->
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Recipients</label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label
                            v-for="group in recipientGroups"
                            :key="group"
                            class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 hover:bg-gray-50"
                        >
                            <input type="checkbox" :value="group" v-model="form.recipients" class="rounded text-blue-600 focus:ring-orange-500" />
                            <span class="text-sm text-gray-700">{{ group }}</span>
                        </label>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-end pt-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-blue-600 px-6 py-2 font-medium text-white transition hover:bg-blue-700 disabled:opacity-50"
                    >
                        Send Notification
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
