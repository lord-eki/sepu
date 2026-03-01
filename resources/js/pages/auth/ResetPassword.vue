<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout title="Reset password" description="Please enter your new password below">

        <Head title="Reset password" />

        <form @submit.prevent="submit"
            class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-lg dark:shadow-gray-800 transition-colors">
            <div class="grid gap-6">

                <!-- Email -->
                <div class="grid gap-2">
                    <Label for="email" class="dark:text-gray-300">Email</Label>
                    <Input id="email" type="email" name="email" autocomplete="email" v-model="form.email"
                        class="mt-1 block w-full dark:bg-gray-800 dark:text-white dark:border-gray-700 dark:placeholder-gray-400"
                        readonly />
                    <InputError :message="form.errors.email" class="mt-2 dark:text-red-400" />
                </div>

                <!-- Password -->
                <div class="grid gap-2">
                    <Label for="password" class="dark:text-gray-300">Password</Label>
                    <Input id="password" type="password" name="password" autocomplete="new-password"
                        v-model="form.password"
                        class="mt-1 block w-full dark:bg-gray-800 dark:text-white dark:border-gray-700 dark:placeholder-gray-400"
                        autofocus placeholder="Password" />
                    <InputError :message="form.errors.password" class="dark:text-red-400" />
                </div>

                <!-- Confirm Password -->
                <div class="grid gap-2">
                    <Label for="password_confirmation" class="dark:text-gray-300">Confirm Password</Label>
                    <Input id="password_confirmation" type="password" name="password_confirmation"
                        autocomplete="new-password" v-model="form.password_confirmation"
                        class="mt-1 block w-full dark:bg-gray-800 dark:text-white dark:border-gray-700 dark:placeholder-gray-400"
                        placeholder="Confirm password" />
                    <InputError :message="form.errors.password_confirmation" class="dark:text-red-400" />
                </div>

                <!-- Submit -->
                <Button type="submit"
                    class="mt-4 w-full bg-orange-500 hover:bg-orange-600 dark:bg-orange-500 dark:hover:bg-orange-600 text-white"
                    :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2 inline-block" />
                    Reset password
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>