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
    <AuthBase title="Log in to your account" description="Enter your username/email and password below to log in">

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <!-- FORM WRAPPER -->
        <form @submit.prevent="submit" class="relative flex flex-col gap-6">
            <!-- LOADING OVERLAY -->
            <div v-if="form.processing" class="absolute inset-0 z-50 flex items-center justify-center rounded-lg
               bg-white/40
               dark:bg-black/40">
                <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                    <LoaderCircle class="h-5 w-5 animate-spin" />
                    Logging in…
                </div>
            </div>

            <div class="grid gap-6">
                <!-- Username / Email -->
                <div class="grid gap-2">
                    <Label for="login">Username or Email</Label>
                    <Input id="login" type="text" required autofocus autocomplete="username" v-model="form.login"
                        placeholder="username or email@example.com" :disabled="form.processing" />
                    <InputError :message="form.errors.login" />
                </div>

                <!-- Password -->
                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>

                        <TextLink v-if="canResetPassword" :href="route('password.request')"
                            :class="form.processing ? 'pointer-events-none opacity-50' : ''" class="text-sm">
                            Forgot password?
                        </TextLink>
                    </div>

                    <div class="relative">
                        <Input id="password" :type="showPassword ? 'text' : 'password'" required
                            autocomplete="current-password" v-model="form.password" placeholder="Password"
                            :disabled="form.processing" />

                        <button type="button" @click="togglePassword" :disabled="form.processing" class="absolute inset-y-0 right-3 flex items-center
                     text-muted-foreground hover:text-foreground
                     transition disabled:opacity-50 disabled:pointer-events-none" tabindex="-1">
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>

                    <InputError :message="form.errors.password" />
                </div>

                <!-- Remember me -->
                <div class="flex items-center justify-between">
                    <Label class="flex items-center space-x-3">
                        <Checkbox v-model="form.remember" :disabled="form.processing" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <!-- Submit -->
                <Button type="submit" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <span v-else>Log in</span>
                </Button>
            </div>

            <!-- Sign up -->
            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <TextLink :href="route('register')" :class="form.processing ? 'pointer-events-none opacity-50' : ''">
                    Sign up
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>
