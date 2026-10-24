<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    current_password: '',
    new_password: '',
    confirm_password: '',
    two_factor_enabled: false,
    session_timeout: 30, // in minutes
});

const passwordVisible = ref(false);

function saveSecuritySettings() {
    route('settings.security.update');
}
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Settings', href: route('admin.settings.index') }, { title: 'Security' }]">
        <Head title="Security Settings" />

        <div class="rounded-2xl bg-white p-6 shadow-md">
            <h1 class="mb-6 border-b pb-3 text-xl font-semibold text-gray-800 sm:text-2xl">Security Settings</h1>

            <form @submit.prevent="saveSecuritySettings" class="space-y-6">
                <!-- Password Change Section -->
                <div>
                    <h2 class="mb-3 text-lg font-semibold text-gray-700">Change Password</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Current Password</label>
                            <input
                                v-model="form.current_password"
                                type="password"
                                placeholder="Enter current password"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">New Password</label>
                            <input
                                v-model="form.new_password"
                                :type="passwordVisible ? 'text' : 'password'"
                                placeholder="Enter new password"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                            <input
                                v-model="form.confirm_password"
                                :type="passwordVisible ? 'text' : 'password'"
                                placeholder="Confirm new password"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400"
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="show_password"
                                v-model="passwordVisible"
                                class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400"
                            />
                            <label for="show_password" class="text-sm text-gray-700">Show Passwords</label>
                        </div>
                    </div>
                </div>

                <!-- Two-Factor Authentication -->
                <div>
                    <h2 class="mb-3 text-lg font-semibold text-gray-700">Two-Factor Authentication (2FA)</h2>

                    <div class="flex items-center gap-2">
                        <input
                            id="two_factor_enabled"
                            type="checkbox"
                            v-model="form.two_factor_enabled"
                            class="h-4 w-4 rounded border-gray-300 text-sky-600 focus:ring-sky-400"
                        />
                        <label for="two_factor_enabled" class="text-sm text-gray-700"> Enable Two-Factor Authentication </label>
                    </div>

                    <p class="mt-2 text-xs text-gray-500">
                        When enabled, users must verify their identity via a code sent to their email or phone upon login.
                    </p>
                </div>

                <!-- Session Timeout -->
                <div>
                    <h2 class="mb-3 text-lg font-semibold text-gray-700">Session Timeout</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Auto Logout After (minutes)</label>
                            <input
                                v-model="form.session_timeout"
                                type="number"
                                min="5"
                                placeholder="e.g., 30"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 outline-none focus:border-sky-400 focus:ring-2 focus:ring-sky-400"
                            />
                        </div>
                    </div>

                    <p class="mt-2 text-xs text-gray-500">Users will be automatically logged out after inactivity for the specified time.</p>
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
