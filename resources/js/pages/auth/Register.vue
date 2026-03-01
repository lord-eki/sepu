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
  <AuthBase title="Create an account" description="Enter your details below to register">

    <Head title="Register" />

    <!-- REGISTER FORM -->
    <form @submit.prevent="submit" class="relative flex flex-col gap-4">
      <!-- LOADING OVERLAY -->
      <div v-if="form.processing" class="absolute inset-0 z-50 flex items-center justify-center rounded-lg
             bg-white/30 dark:bg-gray-900/50 backdrop-blur-sm">
        <LoaderCircle class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" />
      </div>

      <div class="grid gap-4">
        <!-- Name -->
        <div class="grid gap-1.5">
          <Label for="name" class="text-sm font-medium text-gray-700 dark:text-gray-200">Full name</Label>
          <Input id="name" type="text" required autofocus autocomplete="name" v-model="form.name"
            placeholder="Firstname and Lastname" :disabled="form.processing" class="h-10 rounded-lg border border-gray-300 dark:border-gray-600
                 bg-white/80 dark:bg-gray-800/60 px-3 text-sm placeholder-gray-400 dark:placeholder-gray-500
                 text-gray-900 dark:text-gray-100
                 focus:outline-none focus:ring-1 focus:ring-blue-500
                 disabled:opacity-50 disabled:cursor-not-allowed" />
          <InputError :message="form.errors.name" />
        </div>

        <!-- Email -->
        <div class="grid gap-1.5">
          <Label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-200">Email address</Label>
          <Input id="email" type="email" required autocomplete="email" v-model="form.email"
            placeholder="email@example.com" :disabled="form.processing" class="h-10 rounded-lg border border-gray-300 dark:border-gray-600
                 bg-white/80 dark:bg-gray-800/60 px-3 text-sm placeholder-gray-400 dark:placeholder-gray-500
                 text-gray-900 dark:text-gray-100
                 focus:outline-none focus:ring-1 focus:ring-blue-500
                 disabled:opacity-50 disabled:cursor-not-allowed" />
          <InputError :message="form.errors.email" />
        </div>

        <!-- Phone -->
        <div class="grid gap-1.5">
          <Label for="phone" class="text-sm font-medium text-gray-700 dark:text-gray-200">Phone number</Label>
          <div class="flex overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600
                 focus-within:ring-1 focus-within:ring-blue-500
                 bg-white/80 dark:bg-gray-800/60" :class="form.processing ? 'opacity-50 pointer-events-none' : ''">
            <!-- Country code -->
            <select v-model="selectedCode"
              class="px-3 py-1.5 text-sm bg-white/80 dark:bg-gray-800/60 text-gray-900 dark:text-gray-100 focus:outline-none">
              <option v-for="c in countryCodes" :key="c.code" :value="c.code">
                {{ c.flag }} {{ c.code }}
              </option>
            </select>

            <!-- Phone input -->
            <input id="phone" type="tel" required autocomplete="tel" v-model="form.phone" placeholder="712345678"
              pattern="^[0-9]{9,10}$" maxlength="10" class="flex-1 px-3 py-1.5 text-sm bg-white/80 dark:bg-gray-800/60 text-gray-900 dark:text-gray-100
                   placeholder:text-muted-foreground dark:placeholder:text-gray-500 border-0 rounded-none
                   focus:outline-none" />
          </div>
          <InputError :message="form.errors.phone" />
        </div>

        <!-- Password -->
        <div class="grid gap-1.5">
          <Label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-200">Password</Label>
          <Input id="password" type="password" required autocomplete="new-password" v-model="form.password"
            placeholder="Password" :disabled="form.processing" class="h-10 rounded-lg border border-gray-300 dark:border-gray-600
                 bg-white/80 dark:bg-gray-800/60 px-3 text-sm placeholder-gray-400 dark:placeholder-gray-500
                 text-gray-900 dark:text-gray-100
                 focus:outline-none focus:ring-1 focus:ring-blue-500
                 disabled:opacity-50 disabled:cursor-not-allowed" />
          <InputError :message="form.errors.password" />
        </div>

        <!-- Confirm Password -->
        <div class="grid gap-1.5">
          <Label for="password_confirmation" class="text-sm font-medium text-gray-700 dark:text-gray-200">
            Confirm password
          </Label>
          <Input id="password_confirmation" type="password" required autocomplete="new-password"
            v-model="form.password_confirmation" placeholder="Confirm password" :disabled="form.processing" class="h-10 rounded-lg border border-gray-300 dark:border-gray-600
                 bg-white/80 dark:bg-gray-800/60 px-3 text-sm placeholder-gray-400 dark:placeholder-gray-500
                 text-gray-900 dark:text-gray-100
                 focus:outline-none focus:ring-1 focus:ring-blue-500
                 disabled:opacity-50 disabled:cursor-not-allowed" />
          <InputError :message="form.errors.password_confirmation" />
        </div>

        <!-- Submit -->
        <Button type="submit" class="mt-5 w-full h-10 rounded-lg text-sm font-semibold
               bg-blue-900 dark:bg-blue-700 text-white hover:bg-blue-800 dark:hover:bg-blue-600 transition"
          :disabled="form.processing">
          <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-1.5 text-white" />
          <span v-else>Create account</span>
        </Button>
      </div>

      <!-- Login -->
      <div class="text-center text-sm sm:text-base pt-2 text-gray-500 dark:text-gray-400">
        Already have an account?
        <TextLink :href="route('login')" :class="form.processing ? 'pointer-events-none opacity-50' : ''"
          class="underline underline-offset-4 dark:text-blue-400">
          Log in
        </TextLink>
      </div>
    </form>
  </AuthBase>
</template>