<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs = [{ title: 'Settings' }, { title: 'Backup Settings' }];

// Backup form state
const form = useForm({
    auto_backup: true,
    backup_frequency: 'Daily',
    last_backup_date: '2025-11-04 10:00 AM',
});

const frequencies = ['Daily', 'Weekly', 'Monthly'];

const createBackup = () => {
    alert('Backup started successfully (dummy action)');
    // form.post(route('settings.backup.create'))
};

const saveSettings = () => {
    form.post(route('settings.backup.update'));
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'Backup' }]">
        <Head title="Backup Settings" />

        <div class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow">
            <h1 class="mb-6 text-2xl font-semibold text-gray-800">Backup Settings</h1>

            <div class="space-y-6">
                <!-- Last Backup Info -->
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <h2 class="mb-2 text-lg font-medium text-gray-700">Last Backup</h2>
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
                            class="rounded-lg bg-orange-500 px-5 py-2 text-white transition hover:bg-orange-600"
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
                            class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-orange-500"
                        />
                    </div>

                    <div v-if="form.auto_backup">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Backup Frequency</label>
                        <select
                            v-model="form.backup_frequency"
                            class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
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
                            class="rounded-lg bg-blue-600 px-6 py-2 text-white transition hover:bg-blue-700"
                        >
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
