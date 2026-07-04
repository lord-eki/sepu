<script setup lang="ts">
import InputError from '@/components/InputError.vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthBase from '@/layouts/AuthLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { LoaderCircle, Eye, EyeOff } from 'lucide-vue-next'
import { ref, watch } from 'vue'

defineProps<{
    status?: string
    error?: string
    canResetPassword: boolean
}>()

const form = useForm({
    login: '',
    password: '',
    remember: false,
})

watch(() => form.login, () => {
    if (form.errors.login) form.clearErrors('login')
})

watch(() => form.password, () => {
    if (form.errors.password) form.clearErrors('password')
})

const showPassword = ref(false)

const togglePassword = () => {
    if (form.processing) return
    showPassword.value = !showPassword.value
}

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <AuthBase title="Welcome Back" description="Access your SEPU SACCO account">

        <Head title="Log in" />

        <!-- Status -->
        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-400">
            {{ status }}
        </div>

        <div
            v-if="error"
            class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300"
        >
            {{ error }}
        </div>

        <!-- FORM -->
        <form @submit.prevent="submit" class="relative space-y-5">

            <!-- Loading Overlay -->
            <div v-if="form.processing" class="absolute inset-0 z-50 flex items-center justify-center 
            rounded-sm bg-white/20 h-full dark:bg-gray-900/40 backdrop-blur-sm">
                <LoaderCircle class="h-6 w-6 animate-spin text-blue-600 dark:text-blue-400" />
            </div>

            <!-- Login -->
            <div class="space-y-1.5">
                <Label for="login" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                    Username or Email
                </Label>
                <Input id="login" type="text" required autofocus autocomplete="username" v-model="form.login"
                    placeholder="username or email@example.com" :disabled="form.processing" class="h-10 rounded-lg border border-gray-300 dark:border-gray-600
                       bg-white/80 dark:bg-gray-800/60 px-3 text-sm placeholder-gray-400 dark:placeholder-gray-500
                       text-gray-900 dark:text-gray-100
                       focus:outline-none focus:ring-1 focus:ring-blue-500
                       disabled:opacity-50 disabled:cursor-not-allowed" />
                <InputError :message="form.errors.login" />
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        Password
                    </Label>

                    <TextLink v-if="canResetPassword" :href="route('password.request')"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        Forgot password?
                    </TextLink>
                </div>

                <div class="relative">
                    <Input id="password" :type="showPassword ? 'text' : 'password'" required
                        autocomplete="current-password" v-model="form.password" placeholder="Enter your password"
                        :disabled="form.processing" class="h-10 rounded-lg border border-gray-300 dark:border-gray-600 pr-10
                           bg-white/80 dark:bg-gray-800/60 px-3 text-sm placeholder-gray-400 dark:placeholder-gray-500
                           text-gray-900 dark:text-gray-100
                           focus:outline-none focus:ring-1 focus:ring-blue-500
                           disabled:opacity-50 disabled:cursor-not-allowed" />

                    <button type="button" @click="togglePassword" :disabled="form.processing"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-400 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition"
                        tabindex="-1">
                        <Eye v-if="!showPassword" class="h-4 w-4" />
                        <EyeOff v-else class="h-4 w-4" />
                    </button>
                </div>

                <InputError :message="form.errors.password" />
            </div>

            <!-- Remember -->
            <div class="flex items-center">
                <Label class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-300">
                    <Checkbox v-model="form.remember" :disabled="form.processing" />
                    <span>Remember me</span>
                </Label>
            </div>

            <!-- Submit -->
            <Button type="submit" :disabled="form.processing" class="w-full h-10 mt-5 rounded-lg text-sm font-semibold
                   bg-blue-900 dark:bg-blue-700 text-white hover:bg-blue-800 dark:hover:bg-blue-600 transition">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-1.5" />
                <span v-else>Log in</span>
            </Button>

            <!-- Sign Up -->
            <div class="text-center sm:text-base text-sm text-gray-500 dark:text-gray-400 pt-3">
                Don’t have an account?
                <TextLink :href="route('register')"
                    class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                    Sign up
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>