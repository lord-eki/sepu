<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { LockKeyhole, ShieldCheck, Eye, EyeOff } from 'lucide-vue-next'
import { ref } from 'vue'

const showPassword = ref(false)
const showConfirm = ref(false)

const form = useForm({
  password: '',
  password_confirmation: ''
})

const passwordStrength = computed(() => {
  const pass = form.password || ''

  if (pass.length < 6) return { text: 'Weak', width: 'w-1/3', color: 'bg-red-500' }
  if (pass.length < 10) return { text: 'Medium', width: 'w-2/3', color: 'bg-yellow-500' }

  return { text: 'Strong', width: 'w-full', color: 'bg-green-500' }
})

const passwordsMatch = computed(() => {
  return (
    form.password &&
    form.password_confirmation &&
    form.password === form.password_confirmation
  )
})

const submit = () => {
  form.post(route('password.change.update'))
}
</script>

<template>
<div class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-blue-100 flex items-center justify-center px-4">

  <div class="w-full max-w-md bg-white/90 backdrop-blur rounded-3xl shadow-2xl border border-white/60 p-8">

    <!-- Header -->
    <div class="text-center mb-8">
      <div class="mx-auto w-16 h-16 rounded-2xl bg-blue-950 flex items-center justify-center shadow-lg">
        <ShieldCheck class="w-8 h-8 text-white" />
      </div>

      <h1 class="mt-4 text-2xl font-bold text-gray-900">
        Change Password
      </h1>

      <p class="mt-2 text-sm text-gray-500 leading-relaxed">
        For security reasons, you must create a new password before accessing your account.
      </p>
    </div>

    <!-- Form -->
    <form @submit.prevent="submit" class="space-y-5">

      <!-- Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          New Password
        </label>

        <div class="relative">
          <LockKeyhole class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" />

          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Enter new password"
            class="w-full rounded-xl border border-gray-300 pl-12 pr-12 py-3 outline-none focus:ring-2 focus:ring-blue-950 focus:border-transparent"
          />

          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-4 top-3 text-gray-400 hover:text-gray-600"
          >
            <Eye v-if="!showPassword" class="w-5 h-5" />
            <EyeOff v-else class="w-5 h-5" />
          </button>
        </div>

        <!-- Strength -->
        <div class="mt-3">
          <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
            <div
              class="h-full transition-all duration-300"
              :class="[passwordStrength.width, passwordStrength.color]"
            />
          </div>

          <p class="text-xs text-gray-500 mt-1">
            Strength: {{ passwordStrength.text }}
          </p>
        </div>

        <p v-if="form.errors.password" class="text-sm text-red-600 mt-2">
          {{ form.errors.password }}
        </p>
      </div>

      <!-- Confirm Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Confirm Password
        </label>

        <div class="relative">
          <LockKeyhole class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" />

          <input
            v-model="form.password_confirmation"
            :type="showConfirm ? 'text' : 'password'"
            placeholder="Confirm password"
            class="w-full rounded-xl border border-gray-300 pl-12 pr-12 py-3 outline-none focus:ring-2 focus:ring-blue-950 focus:border-transparent"
          />

          <button
            type="button"
            @click="showConfirm = !showConfirm"
            class="absolute right-4 top-3 text-gray-400 hover:text-gray-600"
          >
            <Eye v-if="!showConfirm" class="w-5 h-5" />
            <EyeOff v-else class="w-5 h-5" />
          </button>
        </div>

        <p
          v-if="form.password_confirmation"
          class="text-xs mt-2"
          :class="passwordsMatch ? 'text-green-600' : 'text-red-600'"
        >
          {{ passwordsMatch ? 'Passwords match' : 'Passwords do not match' }}
        </p>
      </div>

      <!-- Button -->
      <button
        type="submit"
        :disabled="form.processing"
        class="w-full bg-blue-950 hover:bg-orange-500 transition-all text-white font-semibold py-3 rounded-xl disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
      >
        <svg
          v-if="form.processing"
          class="animate-spin h-5 w-5"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
          />
        </svg>

        {{ form.processing ? 'Updating...' : 'Update Password' }}
      </button>

    </form>

  </div>
</div>
</template>