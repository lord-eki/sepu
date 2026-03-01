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
<AuthLayout 
    title="Forgot password" 
    description="Enter your email to receive a password reset link"
>
    <Head title="Forgot password" />

    <!-- Status Message -->
    <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-400">
        {{ status }}
    </div>

    <form @submit.prevent="submit" class="space-y-5 relative">

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
                class="h-10 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-300 dark:focus:ring-blue-500 transition"
            />
            <InputError :message="form.errors.email" class="dark:text-red-400" />
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-start mt-4">
            <Button 
                class="w-full h-10 rounded-lg text-sm font-semibold bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white transition" 
                :disabled="form.processing"
            >
                <LoaderCircle 
                    v-if="form.processing" 
                    class="h-4 w-4 animate-spin mr-2 inline-block" 
                />
                <span v-else>Email password reset link</span>
            </Button>
        </div>

        <!-- Back to Login -->
        <div class="text-center text-sm sm:text-base text-gray-500 dark:text-gray-400 pt-2">
            Or, return to 
            <TextLink 
                :href="route('login')" 
                class="text-blue-600 dark:text-blue-400 hover:underline font-medium"
            >
                log in
            </TextLink>
        </div>

    </form>
</AuthLayout>
</template>