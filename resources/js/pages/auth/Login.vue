<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, Eye, EyeOff } from 'lucide-vue-next';
import { ref, watch } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

watch(() => form.login, () => {
    if (form.errors.login) form.clearErrors('login');
});

watch(() => form.password, () => {
    if (form.errors.password) form.clearErrors('password');
});

const showPassword = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your username/email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <!-- Username/Email -->
                <div class="grid gap-2">
                    <Label for="login" class="text-gray-800 dark:text-gray-200">Username or Email</Label>
                    <Input
                        id="login"
                        type="text"
                        required
                        autofocus
                        autocomplete="username"
                        v-model="form.login"
                        placeholder="username or email@example.com"
                    />
                    <InputError :message="form.errors.login" />
                </div>

                <!-- Password -->
                <div class="grid gap-2 relative">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-gray-800 dark:text-gray-200">Password</Label>

                        <TextLink
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm"
                        >
                            Forgot password?
                        </TextLink>
                    </div>

                    <div class="relative">
                        <Input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            autocomplete="current-password"
                            v-model="form.password"
                            placeholder="Password"
                        />

                        <button
                            type="button"
                            @click="togglePassword"
                            class="absolute inset-y-0 right-3 flex items-center text-muted-foreground hover:text-foreground transition"
                            tabindex="-1"
                        >
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>

                    <InputError :message="form.errors.password" />
                </div>

                <!-- Remember -->
                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3 text-gray-800 dark:text-gray-200">
                        <Checkbox id="remember" v-model="form.remember" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <!-- Submit Button -->
                <Button type="submit" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <span v-else>Log in</span>
                </Button>
            </div>

            <!-- Sign up -->
            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <TextLink :href="route('register')">Sign up</TextLink>
            </div>
        </form>
    </AuthBase>
</template>