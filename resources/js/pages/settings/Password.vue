<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'

import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'

import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

import { Lock } from 'lucide-vue-next'

const passwordInput = ref<HTMLInputElement | null>(null)
const currentPasswordInput = ref<HTMLInputElement | null>(null)

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const updatePassword = () => {
  form.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: (errors: any) => {
      if (errors.password) {
        form.reset('password', 'password_confirmation')
        passwordInput.value?.focus()
      }
      if (errors.current_password) {
        form.reset('current_password')
        currentPasswordInput.value?.focus()
      }
    },
  })
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/password',
    },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Password Settings" />

        <SettingsLayout>
            <div class="mx-auto max-w-5xl space-y-8">

                <!-- Header -->
                <div
                    class="overflow-hidden rounded-3xl bg-gradient-to-r from-orange-500 via-orange-600 to-amber-500 p-8 text-white shadow-xl">

                    <div class="flex items-center gap-5">

                        <div
                            class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/20 backdrop-blur">

                            <Lock class="h-8 sm:10 w-8 sm:w-10" />

                        </div>

                        <div>

                            <h1 class="text-2xl sm:text-3xl font-bold">
                                Password & Security
                            </h1>

                            <p class="mt-2 text-orange-100">
                                Keep your account secure by using a strong password.
                            </p>

                        </div>

                    </div>

                </div>

                <!-- Main Card -->
                <div
                    class="rounded-3xl border border-gray-200 bg-white p-8 shadow-lg dark:border-gray-700 dark:bg-gray-900">

                    <HeadingSmall
                        title="Update Password"
                        description="Choose a strong password that you haven't used before." />

                    <form
                        class="mt-8 space-y-6"
                        @submit.prevent="updatePassword">

                        <!-- Current Password -->
                        <div class="space-y-2">

                            <Label for="current_password">
                                Current Password
                            </Label>

                            <div class="relative">

                                <Lock
                                    class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />

                                <Input
                                    id="current_password"
                                    ref="currentPasswordInput"
                                    v-model="form.current_password"
                                    type="password"
                                    class="h-12 rounded-xl pl-12"
                                    placeholder="Enter current password" />

                            </div>

                            <InputError :message="form.errors.current_password" />

                        </div>

                        <!-- New Password -->
                        <div class="space-y-2">

                            <Label for="password">
                                New Password
                            </Label>

                            <div class="relative">

                                <Lock
                                    class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />

                                <Input
                                    id="password"
                                    ref="passwordInput"
                                    v-model="form.password"
                                    type="password"
                                    class="h-12 rounded-xl pl-12"
                                    placeholder="Enter new password" />

                            </div>

                            <InputError :message="form.errors.password" />

                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">

                            <Label for="password_confirmation">
                                Confirm Password
                            </Label>

                            <div class="relative">

                                <Lock
                                    class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />

                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="h-12 rounded-xl pl-12"
                                    placeholder="Confirm your new password" />

                            </div>

                            <InputError
                                :message="form.errors.password_confirmation" />

                        </div>

                        <!-- Password Tips -->
                        <div
                            class="rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-700 dark:bg-blue-900/20">

                            <h3
                                class="mb-3 font-semibold text-blue-900 dark:text-blue-300">

                                Password Tips

                            </h3>

                            <ul
                                class="space-y-2 text-sm text-blue-700 dark:text-blue-300">

                                <li>• Use at least 8 characters.</li>

                                <li>• Include uppercase and lowercase letters.</li>

                                <li>• Add numbers and special characters.</li>

                                <li>• Avoid using personal information.</li>

                            </ul>

                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-4 pt-2">

                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-xl px-8">

                                <span v-if="form.processing">
                                    Updating...
                                </span>

                                <span v-else>
                                    Update Password
                                </span>

                            </Button>

                            <Transition
                                enter-active-class="transition-all duration-300"
                                enter-from-class="translate-y-2 opacity-0"
                                enter-to-class="translate-y-0 opacity-100"
                                leave-active-class="transition-all duration-200"
                                leave-to-class="opacity-0">

                                <div
                                    v-show="form.recentlySuccessful"
                                    class="rounded-xl bg-green-100 px-4 py-2 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">

                                    ✓ Password updated successfully

                                </div>

                            </Transition>

                        </div>

                    </form>

                </div>

            </div>
        </SettingsLayout>
    </AppLayout>
</template>