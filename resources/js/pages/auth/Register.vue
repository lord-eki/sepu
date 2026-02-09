<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthBase from '@/layouts/AuthLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { LoaderCircle } from 'lucide-vue-next'
import { ref } from 'vue'

const countryCodes = [
  { code: '+254', flag: '🇰🇪', country: 'Kenya' },
  { code: '+255', flag: '🇹🇿', country: 'Tanzania' },
  { code: '+256', flag: '🇺🇬', country: 'Uganda' },
  { code: '+257', flag: '🇧🇮', country: 'Burundi' },
  { code: '+250', flag: '🇷🇼', country: 'Rwanda' },
]

const selectedCode = ref('+254')

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  country_code: selectedCode.value,
})

const submit = () => {
  form.country_code = selectedCode.value
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <AuthBase
    title="Create an account"
    description="Enter your details below to create your account"
  >
    <Head title="Register" />

    <form
      @submit.prevent="submit"
      class="relative flex flex-col gap-6"
    >
      <!-- LOADING OVERLAY -->
      <div
        v-if="form.processing"
        class="absolute inset-0 z-50 flex items-center justify-center rounded-lg
               bg-white/40
               dark:bg-black/40"
      >
        <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
          <LoaderCircle class="h-5 w-5 animate-spin" />
          Creating account…
        </div>
      </div>

      <div class="grid gap-6">
        <!-- Name -->
        <div class="grid gap-2">
          <Label for="name">Full name</Label>
          <Input
            id="name"
            type="text"
            required
            autofocus
            autocomplete="name"
            v-model="form.name"
            placeholder="Firstname and Lastname"
            :disabled="form.processing"
          />
          <InputError :message="form.errors.name" />
        </div>

        <!-- Email -->
        <div class="grid gap-2">
          <Label for="email">Email address</Label>
          <Input
            id="email"
            type="email"
            required
            autocomplete="email"
            v-model="form.email"
            placeholder="email@example.com"
            :disabled="form.processing"
          />
          <InputError :message="form.errors.email" />
        </div>

        <!-- Phone -->
        <div class="grid gap-2">
          <Label for="phone">Phone number</Label>

          <div
            class="flex overflow-hidden rounded-md border border-input bg-background
                   focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]"
            :class="form.processing ? 'opacity-60 pointer-events-none' : ''"
          >
            <!-- Country code -->
            <select
              v-model="selectedCode"
              class="px-3 py-2 text-sm bg-background text-foreground focus:outline-none"
            >
              <option
                v-for="c in countryCodes"
                :key="c.code"
                :value="c.code"
              >
                {{ c.flag }} {{ c.code }}
              </option>
            </select>

            <!-- Phone input -->
            <input
              id="phone"
              type="tel"
              required
              autocomplete="tel"
              v-model="form.phone"
              placeholder="712345678"
              pattern="^[0-9]{9,10}$"
              maxlength="10"
              class="flex-1 px-3 py-2 text-sm bg-background text-foreground
                     placeholder:text-muted-foreground border-0 rounded-none focus:outline-none"
            />
          </div>

          <InputError :message="form.errors.phone" />
        </div>

        <!-- Password -->
        <div class="grid gap-2">
          <Label for="password">Password</Label>
          <Input
            id="password"
            type="password"
            required
            autocomplete="new-password"
            v-model="form.password"
            placeholder="Password"
            :disabled="form.processing"
          />
          <InputError :message="form.errors.password" />
        </div>

        <!-- Confirm Password -->
        <div class="grid gap-2">
          <Label for="password_confirmation">Confirm password</Label>
          <Input
            id="password_confirmation"
            type="password"
            required
            autocomplete="new-password"
            v-model="form.password_confirmation"
            placeholder="Confirm password"
            :disabled="form.processing"
          />
          <InputError :message="form.errors.password_confirmation" />
        </div>

        <!-- Submit -->
        <Button
          type="submit"
          class="mt-2 w-full"
          :disabled="form.processing"
        >
          <LoaderCircle
            v-if="form.processing"
            class="h-4 w-4 animate-spin"
          />
          <span v-else>Create account</span>
        </Button>
      </div>

      <!-- Login -->
      <div class="text-center text-sm">
        Already have an account?
        <TextLink
          :href="route('login')"
          :class="form.processing ? 'pointer-events-none opacity-50' : ''"
          class="underline underline-offset-4"
        >
          Log in
        </TextLink>
      </div>
    </form>
  </AuthBase>
</template>
