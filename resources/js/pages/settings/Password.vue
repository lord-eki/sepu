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
  <AppLayout>
    <Head title="Password Settings" />

    <SettingsLayout>
      <div class="max-w-xl mx-auto">

        <!-- Card Container -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-md rounded-xl p-6 space-y-6">

          <HeadingSmall 
            title="Update Password"
            description="Use a strong password to keep your account secure"
          />

          <form @submit.prevent="updatePassword" class="space-y-5">

            <!-- CURRENT PASSWORD -->
            <div class="grid gap-1">
              <Label for="current_password">Current Password</Label>
              <div class="relative">
                <Lock class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-300" />
                <Input
                  id="current_password"
                  ref="currentPasswordInput"
                  v-model="form.current_password"
                  type="password"
                  class="pl-9 shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  placeholder="Enter current password"
                />
              </div>
              <InputError :message="form.errors.current_password" />
            </div>

            <!-- NEW PASSWORD -->
            <div class="grid gap-1">
              <Label for="password">New Password</Label>
              <div class="relative">
                <Lock class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-300" />
                <Input
                  id="password"
                  ref="passwordInput"
                  v-model="form.password"
                  type="password"
                  class="pl-9 shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  placeholder="Enter new password"
                />
              </div>
              <InputError :message="form.errors.password" />
            </div>

            <!-- CONFIRM PASSWORD -->
            <div class="grid gap-1">
              <Label for="password_confirmation">Confirm Password</Label>
              <div class="relative">
                <Lock class="absolute left-3 top-3 w-4 h-4 text-gray-400 dark:text-gray-300" />
                <Input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  class="pl-9 shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                  placeholder="Confirm new password"
                />
              </div>
              <InputError :message="form.errors.password_confirmation" />
            </div>

            <!-- ACTIONS -->
            <div class="flex items-center gap-4 pt-2">
              <Button type="submit" :disabled="form.processing" class="min-w-[140px]">
                <span v-if="form.processing">Saving...</span>
                <span v-else>Update Password</span>
              </Button>

              <Transition
                enter-active-class="transition duration-300"
                enter-from-class="opacity-0 translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
              >
                <div
                  v-if="form.recentlySuccessful"
                  class="text-green-800 dark:text-green-300 text-sm bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 px-3 py-1 rounded-md"
                >
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