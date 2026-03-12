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
    <Head title="Profile settings" />

    <SettingsLayout>
      <div class="flex flex-col space-y-8">

        <!-- Profile Info Card -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6 space-y-6 border border-gray-200 dark:border-gray-700">
          <HeadingSmall 
            title="Profile information"
            description="View or update your name and email address"
          />

          <form @submit.prevent="submit" class="space-y-5">

            <!-- USERNAME -->
            <div class="grid gap-1">
              <Label for="name">Username</Label>
              <Input
                id="name"
                v-model="form.name"
                :readonly="!isAdmin"
                :disabled="!isAdmin"
                class="mt-1 block w-full shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                :class="!isAdmin ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed' : ''"
                autocomplete="name"
                placeholder="Enter your username"
              />
              <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <!-- EMAIL -->
            <div class="grid gap-1">
              <Label for="email">Email address</Label>
              <Input
                id="email"
                type="email"
                v-model="form.email"
                :readonly="!isAdmin"
                :disabled="!isAdmin"
                class="mt-1 block w-full shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                :class="!isAdmin ? 'bg-gray-100 dark:bg-gray-800 cursor-not-allowed' : ''"
                autocomplete="username"
                placeholder="Enter your email"
              />
              <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <!-- EMAIL VERIFICATION NOTICE -->
            <div v-if="mustVerifyEmail && !user.email_verified_at" class="mt-1 text-sm">
              <p class="text-yellow-700 dark:text-yellow-300">
                Your email address is unverified.
                <Link 
                  :href="route('verification.send')" 
                  method="post" 
                  as="button"
                  class="underline underline-offset-2 hover:text-yellow-800 dark:hover:text-yellow-400 transition"
                >
                  Click here to resend the verification email.
                </Link>
              </p>

              <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600 dark:text-green-400">
                A new verification link has been sent to your email address.
              </div>
            </div>

            <!-- SAVE BUTTON -->
            <div v-if="isAdmin" class="flex items-center gap-4">
              <Button :disabled="form.processing">Save</Button>
              <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
              >
                <p
                  v-show="form.recentlySuccessful"
                  class="text-sm font-medium text-green-800 dark:text-green-300 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 px-3 py-1 rounded-md"
                >
                  ✓ Saved successfully
                </p>
              </Transition>
            </div>

          </form>
        </div>

        <!-- DELETE ACCOUNT (ADMIN ONLY) -->
        <DeleteUser v-if="isAdmin" />
      </div>
    </SettingsLayout>
  </AppLayout>
</template>