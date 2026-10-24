<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout title="Forgot password" description="Enter your email to receive a password reset link">
        <Head title="Forgot password" />

        <!-- Status Message -->
        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-400">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="relative space-y-5">
            <!-- Email Input -->
            <div class="grid gap-1.5">
                <Label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    autocomplete="off"
                    v-model="form.email"
                    autofocus
                    placeholder="email@example.com"
                    :disabled="form.processing"
                    class="h-10 rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:ring-1 focus:ring-blue-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:ring-blue-500"
                />
                <InputError :message="form.errors.email" class="dark:text-red-400" />
            </div>

            <!-- Submit Button -->
            <div class="mt-4 flex items-center justify-start">
                <Button
                    class="h-10 w-full rounded-lg bg-blue-600 text-sm font-semibold text-white transition hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700"
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="mr-2 inline-block h-4 w-4 animate-spin" />
                    <span v-else>Email password reset link</span>
                </Button>
            </div>

            <!-- Back to Login -->
            <div class="pt-2 text-center text-sm text-gray-500 sm:text-base dark:text-gray-400">
                Or, return to
                <TextLink :href="route('login')" class="font-medium text-blue-600 hover:underline dark:text-blue-400"> log in </TextLink>
            </div>
        </form>
    </AuthLayout>
</template>
