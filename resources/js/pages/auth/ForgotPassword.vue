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
    <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
        {{ status }}
    </div>

    <form @submit.prevent="submit" class="space-y-5 relative">

        <!-- Email Input -->
        <div class="grid gap-1.5">
            <Label for="email" class="text-sm font-medium text-gray-700">Email address</Label>
            <Input 
                id="email" 
                type="email" 
                name="email" 
                autocomplete="off" 
                v-model="form.email" 
                autofocus 
                placeholder="email@example.com"
                :disabled="form.processing"
                class="h-10 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-300 transition"
            />
            <InputError :message="form.errors.email" />
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-start mt-4">
            <Button 
                class="w-full h-10 rounded-lg text-sm font-semibold" 
                :disabled="form.processing"
            >
                <LoaderCircle 
                    v-if="form.processing" 
                    class="h-4 w-4 animate-spin mr-2" 
                />
                <span v-else>Email password reset link</span>
            </Button>
        </div>

        <!-- Back to Login -->
        <div class="text-center text-sm sm:text-base text-gray-500 pt-2">
            Or, return to 
            <TextLink :href="route('login')" class="text-blue-600 hover:underline font-medium">log in</TextLink>
        </div>

    </form>
</AuthLayout>
</template>