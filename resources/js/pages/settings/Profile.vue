<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'

import DeleteUser from '@/components/DeleteUser.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import { type BreadcrumbItem, type User } from '@/types'

interface Props {
    mustVerifyEmail: boolean
    status?: string
}

defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
]

const page = usePage()
const user = page.props.auth.user as User

// 🔐 ROLE CHECK
const isAdmin = user.role === 'admin'

const form = useForm({
    name: user.username,
    email: user.email,
})

const submit = () => {
    if (!isAdmin) return

    form.patch(route('profile.update'), {
        preserveScroll: true,
    })
}
</script>


<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile Settings" />

        <SettingsLayout>
            <div class="mx-auto max-w-5xl space-y-8">

                <!-- Header -->
                <div
                    class="overflow-hidden rounded-3xl bg-gradient-to-r from-orange-500 via-orange-600 to-amber-500 p-8 text-white shadow-xl">

                    <div class="flex items-center gap-5">

                        <div
                            class="flex h-16 sm:h-20 w-16 sm:w-20 items-center justify-center rounded-3xl bg-white/20 backdrop-blur">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-8 sm:h-10 h-8 sm:w-10"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5.121 17.804A9 9 0 1118.879 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>

                        </div>

                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold">
                                Profile Settings
                            </h1>

                            <p class="mt-2 text-orange-100">
                                Manage your account information and preferences.
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Profile Card -->
                <div
                    class="rounded-3xl border border-gray-200 bg-white p-8 shadow-lg dark:border-gray-700 dark:bg-gray-900">

                    <div class="mb-8 flex items-center justify-between">

                        <div>
                            <HeadingSmall
                                title="Personal Information"
                                description="Update your username and email address." />
                        </div>

                        <span
                            v-if="!isAdmin"
                            class="rounded-full bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            Read Only
                        </span>

                    </div>

                    <form
                        class="space-y-6"
                        @submit.prevent="submit">

                        <!-- Username -->
                        <div class="space-y-2">

                            <Label for="name">
                                Username
                            </Label>

                            <div class="relative">

                                <svg
                                    class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5.121 17.804A9 9 0 1118.879 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z" />

                                </svg>

                                <Input
                                    id="name"
                                    v-model="form.name"
                                    :disabled="!isAdmin"
                                    :readonly="!isAdmin"
                                    placeholder="Username"
                                    autocomplete="name"
                                    class="h-12 rounded-xl pl-12"
                                    :class="!isAdmin
                                        ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed'
                                        : ''" />

                            </div>

                            <InputError :message="form.errors.name" />

                        </div>

                        <!-- Email -->
                        <div class="space-y-2">

                            <Label for="email">
                                Email Address
                            </Label>

                            <div class="relative">

                                <svg
                                    class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 12H8m8 0l-3-3m3 3l-3 3" />

                                </svg>

                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="username"
                                    placeholder="Email Address"
                                    :disabled="!isAdmin"
                                    :readonly="!isAdmin"
                                    class="h-12 rounded-xl pl-12"
                                    :class="!isAdmin
                                        ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed'
                                        : ''" />

                            </div>

                            <InputError :message="form.errors.email" />

                        </div>

                        <!-- Verification -->
                        <div
                            v-if="mustVerifyEmail && !user.email_verified_at"
                            class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 dark:border-yellow-700 dark:bg-yellow-900/20">

                            <div class="space-y-2">

                                <p class="font-medium text-yellow-800 dark:text-yellow-300">
                                    Your email address has not been verified.
                                </p>

                                <Link
                                    :href="route('verification.send')"
                                    method="post"
                                    as="button"
                                    class="text-sm font-semibold text-orange-600 underline hover:text-orange-700 dark:text-orange-400">

                                    Resend verification email

                                </Link>

                                <Transition
                                    enter-active-class="transition duration-300"
                                    enter-from-class="opacity-0"
                                    enter-to-class="opacity-100">

                                    <p
                                        v-if="status === 'verification-link-sent'"
                                        class="text-sm font-medium text-green-600 dark:text-green-400">

                                        ✓ Verification email sent successfully.

                                    </p>

                                </Transition>

                            </div>

                        </div>

                        <!-- Save -->
                        <div
                            v-if="isAdmin"
                            class="flex items-center gap-4 pt-2">

                            <Button
                                :disabled="form.processing"
                                class="rounded-xl px-8">

                                <span v-if="form.processing">
                                    Saving...
                                </span>

                                <span v-else>
                                    Save Changes
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

                                    ✓ Changes saved successfully

                                </div>

                            </Transition>

                        </div>

                    </form>
                </div>

                <!-- Delete Account -->
                <DeleteUser v-if="isAdmin" />

            </div>
        </SettingsLayout>
    </AppLayout>
</template>